<?php
namespace Epi\FulfillmentApi\Model\Api;
use Psr\Log\LoggerInterface;
use \Zend\Json\Json;
use Magento\Sales\Model\Order;
use Magento\Directory\Model\RegionFactory;
use \Exception as Exception;
use Magento\Framework\Exception\NotFoundException as NotFoundException;
use Magento\Framework\Exception\InvalidArgumentException as InvalidArgumentException;
use Magento\Framework\Exception\RunTimeException as RuntimeException;
use \Epi\FulfillmentApi\Model\LCAPIResponse;

class RefundTransactionApi{

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Zend\Http\Client $zendClient
    )
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
        $this->zendClient = $zendClient;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/RefundTransaction.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $this->ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
    }

    public function getTablename($tableName)
    {
        /* Create Connection */
        $connection  = $this->resourceConnection->getConnection();
        $tableName   = $connection->getTableName($tableName);
        return $tableName;
    }

    public function getOrderIdUsingTransactionId ($transactionId) {
        $tableName = $this->getTableName('sales_payment_transaction');
        $query = 'SELECT sales_payment_transaction.order_id,sales_payment_transaction.payment_id FROM ' . $tableName . ' WHERE txn_id="'.$transactionId.'"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('getOrderIdUsingTransactionId result -> '.print_r($result,true));

        if (empty($result)) {
            throw new InvalidArgumentException(__("Invalid transactionId."));
        }

        $orderId = $result[0]['order_id'];

        return $orderId;
    }

    public function getOrderDetailsUsingOrderId ($orderId) {
        $tableName = $this->getTableName('sales_order');
        $query = 'SELECT sales_order.payment_order_id, sales_order.status FROM ' . $tableName . ' WHERE entity_id="'.$orderId.'"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('getPaymentOrderIdUsingOrderId result -> '.print_r($result,true));
        if (empty($result)) {
            throw new InvalidArgumentException(__("Invalid orderId."));
        }
        $paymentOrderId = $result[0]['payment_order_id'];
        $orderStatus = $result[0]['status'];

        return [$paymentOrderId, $orderStatus];
    }

    public function getCustomerMobile ($orderId) {
        $tableName = $this->getTableName('sales_order_address');
        $query = 'SELECT sales_order_address.telephone FROM ' . $tableName . ' WHERE parent_id="'.$orderId.'" AND address_type="billing"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('getCustomerMobile result -> '.print_r($result,true));
        if (empty($result)) {
            throw new InvalidArgumentException(__("Invalid orderId."));
        }
        $customerMobile = $result[0]['telephone'];

        return $customerMobile;
    }

    private function sendOrderUpdateSMS($mobile,$message){
        try{
            $this->zendClient->reset();
            $this->zendClient->setUri($this->ini['fulfilmentAPIURL']);
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
            $apiData['mobile']=$mobile;
            $apiData['message']=$message;
            $this->zendClient->setRawBody(Json::encode($apiData));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response from Order update SMS Api in rufund-->'.print_r($response->getBody(),true));
            return $response->getBody();
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Order update SMS Api in refund->'.print_r($runtimeException->getMessage(),true));
            return $runtimeException->getMessage();
        }
    }

    public function setRefundReason($orderId, $reason) {
        $tableName = $this->getTableName('sales_order');
        $query = 'SELECT sales_order.refund_reason FROM ' .$tableName. ' WHERE entity_id="'.$orderId.'"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('refund result from database -> '.print_r($result,true));
        $reasonForRefund = $result[0]['refund_reason'];
        $decodedResponse = json_decode($reasonForRefund, true);
        if (is_countable($decodedResponse)){
            $decodedResponse['refund'.strval(count($decodedResponse)+1)] = $reason;
        } else {
            $decodedResponse['refund1'] = $reason;
        }
        $encodedResponse = json_encode($decodedResponse);
        
        return $encodedResponse;
    }


    public function refundTransaction($data) {
        $this->logger->info('<----- Refund Transaction ----->');


        try {

            if (isset($data['transactionId']) && isset($data['refundAmount'])) {

                if (!is_numeric($data['refundAmount']) || $data['refundAmount'] <= 0) {
                    throw new InvalidArgumentException(__("Refund Amount is invalid"));
                }

                // Only allowing Refund if no or all the card details are available, else throwing exception.
                if (
                    (isset($data['cardNumber']) && (!isset($data['cardType']) || !isset($data['expiryDate'])))
                    || (isset($data['cardType']) && (!isset($data['cardNumber']) || !isset($data['expiryDate'])))
                    || (isset($data['expiryDate']) && (!isset($data['cardNumber']) || !isset($data['cardType'])))
                ) {
                    throw new InvalidArgumentException(__("Please provide all the card details (Card Number, Card Type and Expiry Date)"));
                }

                $refundReason = '';
                if (isset($data['reason'])) {
                    $refundReason = $data['reason'];
                } else {
                    $refundReason = 'No reason found.';
                }

                $transactionId = $data['transactionId'];
                $refundAmount = $data['refundAmount'];
                $this->logger->info('data -> '.print_r($data,true));
                $orderId = $this->getOrderIdUsingTransactionId($transactionId);
                $customerMobile = $this->getCustomerMobile($orderId);
                $orderInfo = $this->getOrderDetailsUsingOrderId($orderId);
                $paymentOrderId = $orderInfo[0];
                $orderStatus = $orderInfo[1];

                if ($orderStatus == 'closed') {
                    throw new Exception('Order has been closed and cannot be rufunded.', 500);
                }

                if ($orderStatus == 'processing') {
                    throw new Exception('Order has not been completed, cannot process the refund.', 500);
                }
                $this->logger->info('Order status-> ' .$orderStatus);

                $orderDetails;
                $response;

                # use epi library
                $ds = DIRECTORY_SEPARATOR;
                include __DIR__ . "$ds..$ds..$ds/lib/RCGatewayAPIs.php";
                $api = new \RCGatewayAPIs();

                if (isset($paymentOrderId)) {
                    $setDataForRefund = NULL;
                    $setDataForRefund['paymentOrderId'] = $paymentOrderId;
                    $setDataForRefund['refundAmount'] = $refundAmount;
                    $setDataForRefund['eCommTxnInd'] = "03";
                    $setDataForRefund['cardNumber'] = isset($data['cardNumber']) ? $data['cardNumber'] : "";
                    $setDataForRefund['cardType'] = isset($data['cardType']) ? $data['cardType'] : "";
                    $setDataForRefund['expiryDate'] = isset($data['expiryDate']) ? $data['expiryDate'] : "";
                    

                    $this->logger->info("Refund data ->".print_r($setDataForRefund,true));
                    $responseFromDoRefund = $api->doRefund($setDataForRefund);
                    $this->logger->info("Response from Refund ->".print_r($responseFromDoRefund,true));
                    
                    if (isset($responseFromDoRefund->response)) {
                        if ($responseFromDoRefund->response->RespCode == "000" && $responseFromDoRefund->response->error == false) {
                            # Updating order status
                            $updatedResponse = $this->setRefundReason($orderId, $refundReason);
                            $tableName = $this->getTableName('sales_order');
                            $data = ["state"=>"closed", "status"=>"closed", "refund_reason"=>$updatedResponse];
                            $where = ["entity_id = ?" => strval($orderId)];
    
                            $updateQuery = $this->resourceConnection->getConnection()->update($tableName, $data, $where);
                            $this->logger->info("Update Query result->".print_r($updateQuery,true));
    
                            if ($updateQuery == 0) {
                                throw new Exception('Order refunded but could not update order status.', 500);
                            }
    
                            if (isset($customerMobile)) {
                                $customerMessage="Refund processed successfully. Contact store for details.";
                                $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                                $this->logger->info("Response from refund sms api for customer".print_r($customerSMSResponse,true));
                            } 
    
                            $response = new LCAPIResponse();
                            $response->setSuccess(true);
                            $response->setMessage('Transaction has been refunded');
    
                            // $returnJSONResponse = json_encode($responseFromUpdateTheSaleAsRefunded);
                            return $response;
                        } else {
                            $response = $responseFromDoRefund;
                            $this->logger->error('response from responseFromDoRefund with: '.$paymentOrderId.' not found ' .$responseFromDoRefund);
                            throw new NotFoundException(__("Refund Failed."));
                        }
                    } else {
                        $this->logger->info("Reponse from Refund Api in else ->".print_r($responseFromDoRefund,true));
                        $response = new LCAPIResponse();
                        $response->setSuccess(false);
                        $response->setMessage($responseFromDoRefund->message);
                        return $response;
                    }
                    

                }  else {
                    $this->logger->info('Order with ID'. $paymentOrderId.' not found. Quitting.');
                    throw new NotFoundException(__("Order not found."));
                }


            } else {
                throw new InvalidArgumentException(__("Transaction or Refund Amount not found."));
            }
        } catch (\Exception $e) {
            
            throw($e);
            $response = new LCAPIResponse();
            $response->setSuccess(false);
            $response->setMessage($e->getMessage());

            $this->logger->info($e->getMessage());
        }
    }
}
