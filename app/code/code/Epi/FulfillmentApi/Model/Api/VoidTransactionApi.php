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

class VoidTransactionApi {

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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Fulfillment.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $this->ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
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
            $this->logger->info('Response from Order update SMS Api in void-->'.print_r($response->getBody(),true));
            return $response->getBody();
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Order update SMS Api in void->'.print_r($runtimeException->getMessage(),true));
            return $runtimeException->getMessage();
        }
    }

    public function getTablename($tableName)
    {
        /* Create Connection */
        $connection  = $this->resourceConnection->getConnection();
        $tableName   = $connection->getTableName($tableName);
        return $tableName;
    }

    public function getPaymentOrderIdUsingOrderId ($orderId) {
        $tableName = $this->getTableName('sales_order');
        $query = 'SELECT sales_order.payment_order_id FROM ' . $tableName . ' WHERE entity_id="'.$orderId.'"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('getPaymentOrderIdUsingOrderId result -> '.print_r($result,true));
        if (empty($result)) {
            throw new InvalidArgumentException(__("Invalid orderId."));
        }
        $paymentOrderId = $result[0]['payment_order_id'];

        return $paymentOrderId;
    }

    public function getCustomerMobile ($orderId) {
        $tableName = $this->getTableName('sales_order_address');
        $query = 'SELECT sales_order_address.telephone FROM ' . $tableName . ' WHERE parent_id="'.$orderId.'" AND address_type="billing"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('getCustomerMobile result in void -> '.print_r($result,true));
        if (empty($result)) {
            throw new InvalidArgumentException(__("Invalid orderId."));
        }
        $customerMobile = $result[0]['telephone'];

        return $customerMobile;
    }

    public function voidTransaction($data) {
        $this->logger->info('<----- Void Transaction ----->');
        $isRefunded = 0;

        try {
            if (!isset($data['transactionId'])) {
                throw new InvalidArgumentException(__("TransactionId not found."));
            }
            $voidReason = '';
            if (isset($data['reason'])) {
                $voidReason = $data['reason'];
            } else {
                $voidReason = 'No reason found.';
            }

            $transactionId = $data['transactionId'];
            $this->logger->info('transactionId -> '.print_r($transactionId,true));

            $tableName = $this->getTableName('sales_payment_transaction');
            $query = 'SELECT sales_payment_transaction.order_id,sales_payment_transaction.payment_id FROM ' . $tableName . ' WHERE txn_id="'.$transactionId.'"';
            $results = $this->resourceConnection->getConnection()->fetchAll($query);
            $this->logger->info('results -> '.print_r($results,true));

            if (empty($results)) {
                throw new InvalidArgumentException(__("Invalid transactionId."));
            }            

            $orderId = $results[0]['order_id'];
            $customerMobile = $this->getCustomerMobile($orderId);

            $paymentOrderId = $this->getPaymentOrderIdUsingOrderId($orderId);
            $ds = DIRECTORY_SEPARATOR;
            include __DIR__ . "$ds..$ds..$ds/lib/Epi.php";
            #initialize Epi class object				
            $api = new \Epi();

            if (isset($paymentOrderId)) {
                $responseFromGetOrderById = $api->getOrderById($paymentOrderId);
                $this->logger->info("Response from getOrderBy->".print_r($responseFromGetOrderById,true));

                if (!$responseFromGetOrderById->success) {
                    throw new NotFoundException(__("Order Not Found.", 400));
                }

                if (isset($responseFromGetOrderById->transDetails[0]->IsRefunded) && ($responseFromGetOrderById->transDetails[0]->IsRefunded)){
                    throw new Exception("Order already refunded, cannot void.", 500);
                }

            } else {
                $this->logger->info('Order with ID'. $paymentOrderId.' not found. Quitting.');
                throw new NotFoundException(__("Order not found."));
            }


            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);

            $transactionAmount = round($order->getPayment()->getAmountOrdered(),2);
            $this->logger->info('transaction amount -> '.print_r($transactionAmount,true));
            
            $api_data['transactionId']=$transactionId;
            $api_data['txnType']='Authorization';
            $api_data['txnAmt']=$transactionAmount;
            $api_data['eCommUrl']="www.abc.com";
            $api_data['eCommTxnInd']="03";
            $this->logger->info('api_data -> '.print_r($api_data,true));

            

            if($order->getState() != 'canceled' && $order->getState() != 'closed'){
                if($order->getPayment()->getMethod()=='epi'){
                    $responseFromRapidConnect = $api->voidPayment($api_data);
                    $this->logger->info("Response from RapidConnect void=>".print_r($responseFromRapidConnect , true));	
                    if (count(get_object_vars($responseFromRapidConnect)) == 0) {
                        throw new Exception("Order not found.", 500);
                    }
                    if($responseFromRapidConnect->response->RespCode==000){
                        $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                        $order->setVoidReason($voidReason);
                        $order->save();
                        if (isset($customerMobile)) {
                            $customerMessage="Order has been cancelled. Contact store for details.";
                            $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                            $this->logger->info("Response from void sms api for customer".print_r($customerSMSResponse,true));
                        } 

                    } else {
                        throw new Exception("Void failed.", 500);
                    }
                } else{
                    $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                    $order->setVoidReason($voidReason);
                    $order->save();
                    if (isset($customerMobile)) {
                        $customerMessage="Order has been cancelled. Contact store for details.";
                        $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                        $this->logger->info("Response from void sms api for customer (Non-epi)".print_r($customerSMSResponse,true));	
                    }
                }
                $response = new LCAPIResponse();
                $response->setSuccess(true);
                $response->setMessage('Transaction has been made void');

                // $response = ['success' => true, 'message' => 'Transaction has been made void'];
                // $returnJSONResponse = json_encode($response);
                return $response;
                
            } else {
                throw new Exception("Order has been closed/cancelled and cannot be voided.", 500);
            }
        } catch (\Exception $e) {
            throw($e);
            $response = new LCAPIResponse();
            $response->setSuccess(false);
            $response->setMessage($e->getMessage());
            // $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }
    }
}
