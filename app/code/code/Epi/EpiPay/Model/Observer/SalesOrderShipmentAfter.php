<?php

namespace Epi\EpiPay\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Zend\Json\Json;
use Epi\EpiPay\Logger\Logger;

class SalesOrderShipmentAfter implements ObserverInterface
{	
	protected $_messageManager;
	protected $logger;
	public function __construct(
		ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Message\ManagerInterface $messageManager, 
		\Zend\Http\Client $zendClient,
		Logger $logger
		) 
		{
			// $this->config = $scopeConfig;
			// $this->zendClient = $zendClient;
			// $this->_messageManager = $messageManager;
			// $this->writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ship.log');
			// $this->logger= new \Zend_Log();
			// $this->logger->addWriter($this->writer);
			$this->logger = $logger;

        }

		private function shipmentApiCall($url,$payload){
			$this->logger->info('Inside shipmentApiCall');

			// -------------------------------ZEND---------------------
			try 
			{
				$this->zendClient->reset();
				$this->zendClient->setUri($url.'updateShippedStock');
				$this->zendClient->setOptions(array('timeout'=>30));
				$this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
					$this->zendClient->setHeaders([
					'Content-Type' => 'application/json',
					'Accept' => 'application/json',
					]);
	
				$this->zendClient->setRawBody(Json::encode($payload));
				$this->zendClient->send();
				$response = $this->zendClient->getResponse();
				$response=$response->getBody();
				// $response=json_decode($response);  			
				$this->logger->info('Response From POST Call->'.print_r($response,true));
				
				return $response;
				// if($response=='success'){
				// 	$this->logger->info('Shipped Stock updated.');
				// 	return $response
				// }
				// else{
				// 	$this->logger->info('failed in inventory shipped stock api call');
				// 	$this->_messageManager->addError(__('Out of stock.'));
				// 	$this->logger->info('Completion failed->'.print_r($response->response->respcode,true));
				// 	return false;
				// }
			}
			catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
			{
				$this->logger->info('Error in Completion POST call->'.print_r($runtimeException->getMessage(),true));
				return false;
			}
			// --------------------------------ZEND---------------
	}

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		// $path = '/home/ubuntu/test/magento/app/code/Epi/EpiPay/lib';
        // set_include_path(get_include_path() . PATH_SEPARATOR . $path);
		$ds = DIRECTORY_SEPARATOR;
        $ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
		
		$this->logger->info('<###################################################################>');
		$shipment = $observer->getEvent()->getShipment();
		$orderShip = $shipment->getOrder();
		$orderId=$orderShip->getIncrementId();
		//$items = $order->getAllItems();
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		// $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
		$order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
		// $this->logger->info('Order Details->'.print_r($order->getData(),true));
		
		$orderItems = $order->getAllItems();
		$itemsData = array();
		foreach($orderItems as $item){
			if ($item->getData() and $item->getQtyShipped()!= 0) {
				$itemsData[] = [
					'sku'=>$item->getSku(),
					'qtyShipped'=>$item->getQtyShipped()
				];
			}
			else if ($item->getData() and $item->getQtyToShip()) {
				$itemsData[] = [
					'sku'=>$item->getSku(),
					'qtyShipped'=>$item->getQtyToShip()
				];
			}


		}
		$this->logger->info('Items in order->'.print_r($itemsData,true));

		$paymentData = $order->getPayment()->getData();
		$paymentMethod = $order->getPayment()->getMethod();
		$transactionId = $order->getPayment()->getAdditionalInformation('transactionId');
		//$txnAmount=$order->getPayment()->getAmountPaid();
		$txnAmount=$order->getPayment()->getAmountOrdered();

		
		$this->logger->info(print_r($orderId, true));
		$this->logger->info("Payment Method=>".print_r($paymentMethod , true));
		$this->logger->info("Transaction Id from EPI Pay=>".print_r($transactionId, true));
		// $this->logger->info("transaction Info=>".print_r($paymentData , true));
		$this->logger->info("GrandTotal=>".print_r( $order->getGrandTotal(), true));
		$this->logger->info("Transaction Amount=>".print_r($txnAmount, true));
	

		if($paymentMethod=='epi'){
			$this->logger->info('Payment method is EpiPay');
			// $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
			// $client_id = $this->config->getValue("payment/epi/client_id",$storeScope);
			// $client_secret = $this->config->getValue("payment/epi/client_secret",$storeScope);
			// $testmode = $this->config->getValue("payment/epi/epi_testmode",$storeScope);
			// $this->logger->info("Client ID: $client_id | Client Secret : $client_secret | Testmode: $testmode");
			$api_data['transactionId']=$transactionId;
			$api_data['txnAmt']=$txnAmount;
			$api_data['eCommUrl']="www.abc.com";
			$api_data['eCommTxnInd']="03";	
			$ds = DIRECTORY_SEPARATOR;
			include __DIR__ . "$ds..$ds..$ds/lib/Epi.php";
			
			$api = new \Epi();

			$response = $api->completePayment($api_data);
			$this->logger->info("Response from RapidConnect Completion=>".print_r($response , true));	
			// if($response->response->RespCode==000){
			// 	$inventoryResponse= $this->shipmentApiCall($ini['inventoryApi'],$itemsData);
			// }
		}
		else{
			$this->logger->info('Payment method is not EpiPay');
			// return $this->shipmentApiCall($ini['inventoryApi'],$itemsData);
		}
    }


}