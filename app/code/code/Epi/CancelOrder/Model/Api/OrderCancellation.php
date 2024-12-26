<?php
namespace Epi\CancelOrder\Model\Api;

use Epi\CancelOrder\Api\OrderCancellationInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Epi\CancelOrder\Model\OrderCancellationResponse;
use Magento\Framework\Exception\InvalidArgumentException as InvalidArgumentException;
use \Zend\Json\Json;
use Magento\Sales\Model\Order;
use Magento\Directory\Model\RegionFactory;
use \Exception as Exception;
use Magento\Framework\Exception\NotFoundException as NotFoundException;
use Magento\Framework\Exception\RunTimeException as RuntimeException;

class OrderCancellation 
{
    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * OrderCancellation constructor.
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        OrderManagementInterface $orderManagement,
         \Magento\Framework\App\ResourceConnection $resourceConnection,
         \Zend\Http\Client $zendClient
    ) { 
        $ds = DIRECTORY_SEPARATOR;
        $this->orderManagement = $orderManagement;
        $this->resourceConnection = $resourceConnection;
        $this->zendClient = $zendClient;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/OrderCancellation.log');
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
            $this->logger->info('Response from Order update SMS -->'.print_r($response->getBody(),true));
            return $response->getBody();
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Order update SMS-->'.print_r($runtimeException->getMessage(),true));
            return $runtimeException->getMessage();
        }
    }

    public function getCustomerMobile ($orderId) {
        
        $query = 'SELECT sales_order_address.telephone FROM sales_order_address WHERE parent_id="'.$orderId.'" AND address_type="billing"';
        $result = $this->resourceConnection->getConnection()->fetchAll($query);
        $this->logger->info('getCustomerMobile result in void -> '.print_r($result,true));
        if (empty($result)) {
            throw new InvalidArgumentException(__("Invalid orderId."));
        }
        $customerMobile = $result[0]['telephone'];

        return $customerMobile;
    }

    public function cancelOrder($data)
    {   
        $response = new OrderCancellationResponse();
        $this->logger->info('<------------------------Cancel Order Api ------------------------->');
        try {
            
            $this->logger->info('The Data is'.print_r($data,true));
            if (!isset($data['storeOrderId'])) {
                throw new InvalidArgumentException(__("Invalid data- store order id missing."));
            }
            $storeOrderId = $data['storeOrderId'];

            $this->logger->info('storeOrderId is: -'.$storeOrderId);
          
            
            $query = 'SELECT entity_id,increment_id FROM sales_order WHERE store_order_id="'.$storeOrderId.'"';
            $result = $this->resourceConnection->getConnection()->fetchAll($query);
            
            $this->logger->info('<---This is result from the query-------->'.print_r($result,true));
            if (empty($result)) {
                throw new InvalidArgumentException(__("Invalid orderId."));
            }
            $this->logger->info('<------------------------After invalid orderId check ------------------------->');
            $entityId = $result[0]['entity_id'];
            $incrementId = $result[0]['increment_id'];
            $this->logger->info('this is entity id '.$entityId);
            
            $ds = DIRECTORY_SEPARATOR;
            include __DIR__ . "$ds..$ds..$ds/lib/Epi.php";
            #initialize Epi class object				
            $api = new \Epi();
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($entityId);
            $orderArray = json_decode(json_encode($order->getData()));
            $this->logger->info("Order Object: " . print_r($orderArray,true));

            $transactionAmount = round($order->getPayment()->getAmountOrdered(),2);
            $this->logger->info('transaction amount -> '.print_r($transactionAmount,true));
            $transactionId= $order->getPayment()->getAdditionalInformation('transactionId');
            $this->logger->info('This is transaction Id'.$transactionId);

            $api_data['transactionId']=$transactionId;
            $api_data['txnType']='Authorization';
            $api_data['txnAmt']=$transactionAmount;
            $api_data['eCommUrl']="www.abc.com";
            $api_data['eCommTxnInd']="03";
            $this->logger->info('api_data -> '.print_r($api_data,true));
            
            $customerMobile = $this->getCustomerMobile($entityId);
            
            $this->logger->info('This is API data.......'.print_r($api_data,true));
            
            if($order->getState() != 'canceled' && $order->getState() != 'closed'){
                if($order->getPayment()->getMethod()=='epi'){
                    $responseFromRapidConnect = $api->voidPayment($api_data);
                    $this->logger->info("Response from RapidConnect void=>".print_r($responseFromRapidConnect , true));	
                    if (count(get_object_vars($responseFromRapidConnect)) == 0) {
                        throw new Exception("Unable to void transaction.", 500);
                    }
                    if($responseFromRapidConnect->response->RespCode==000){
                        $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                        $order->save();
                        if (isset($customerMobile)) {
                            $customerMessage='Order #'.$incrementId.' has been cancelled by merchant. Contact store for details.';
                            $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                            $this->logger->info("Response from void sms api for customer".print_r($customerSMSResponse,true));
                        } 

                    } else {
                        throw new Exception("Void failed.", 500);
                    }
                } else{
                    $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                    $order->save();
                    if (isset($customerMobile)) {
                        $customerMessage='Order #'.$incrementId.' has been cancelled by merchant. Contact store for details.';
                        $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                        $this->logger->info("Response from void sms api for customer (Non-epi)".print_r($customerSMSResponse,true));	
                    }
                }
                $response->setSuccess(true);
                $response->setMessage('Order has been cancelled successfully.');
            }
            else{
                throw new Exception("Order has already closed/cancelled.", 500);
            }
            
        } catch (\Exception $e) {
                $response->setSuccess(false);
                $response->setMessage($e->getMessage());
        }
         return $response;
    }
}
