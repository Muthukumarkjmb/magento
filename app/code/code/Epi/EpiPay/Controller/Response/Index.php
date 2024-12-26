<?php
namespace Epi\EpiPay\Controller\Response;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Epi\EpiPay\Logger\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Model\Order\Payment\Transaction;
use \Zend\Json\Json;

class Index extends  \Magento\Framework\App\Action\Action
{
	protected $_objectmanager;
	protected $_checkoutSession;
	protected $_orderFactory;
	protected $urlBuilder;
	private $logger;
	protected $response;
	protected $config;
	protected $messageManager;
	protected $transactionRepository;
	protected $cart;
	protected $inbox;
	 
	public function __construct( Context $context,
			Session $checkoutSession,
			OrderFactory $orderFactory,
			Logger $logger,
			ScopeConfigInterface $scopeConfig,
			Http $response,
			TransactionBuilder $tb,
			 \Magento\Checkout\Model\Cart $cart,
			 \Magento\AdminNotification\Model\Inbox $inbox,
			 \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
			 \Zend\Http\Client $zendClient
		) {

      
        $this->checkoutSession = $checkoutSession;
        $this->orderFactory = $orderFactory;
        $this->response = $response;
        $this->config = $scopeConfig;
        $this->transactionBuilder = $tb;
		$this->logger = $logger;					
        $this->cart = $cart;
        $this->inbox = $inbox;
        $this->transactionRepository = $transactionRepository;
		$this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
		$this->zendClient = $zendClient;
		parent::__construct($context);
    }

	public function execute()
	{
		$ds = DIRECTORY_SEPARATOR;
		$ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
		$this->logger->info('<----------Response After Epi Payment---------->');
		$transactionId = $this->getRequest()->getParam('transactionId');
		$paymentOrderId = $this->getRequest()->getParam('paymentOrderId'); 
		$storedPaymentRequestId = $this->checkoutSession->getPaymentRequestId();
		$this->checkoutSession->setTransactionId($transactionId);
		
		if (isset($transactionId) and isset($paymentOrderId))
		{  
			$this->logger->info("Callback called with transaction ID: $transactionId and payment request ID : $paymentOrderId ");
	  
			if($paymentOrderId != $storedPaymentRequestId)
			{
				$this->logger->info("Payment Request ID not matched payment request stored in session with Get Request ID $paymentOrderId.");
				$this->_redirect($this->urlBuilder->getBaseUrl());
			}	  
     
			try {
				
				# get Client credentials from configurations.
				$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
				// $mid = $this->config->getValue("payment/epi/merchant_id",$storeScope);
				// $client_secret = $this->config->getValue("payment/epi/client_secret",$storeScope);
				// $testmode = $this->config->getValue("payment/epi/epi_testmode",$storeScope);
				// $tid=$this->config->getValue("payment/epi/terminal_id",$storeScope);
				// $this->logger->info("MID: $mid | TID: $tid | Testmode: $testmode");
				

				# use epi library
				$ds = DIRECTORY_SEPARATOR;
				include __DIR__ . "$ds..$ds..$ds/lib/Epi.php";
				// $api = new \Epi($mid,$client_secret,$testmode);
				$api = new \Epi\EpiPay\lib\Epi();

				try{
					# fetch transaction status from payment server.
					$response = $api->getOrderById($paymentOrderId);
					$this->logger->info("Response from getOrderById->".print_r($response,true));
				}catch(\Exception $e){
					$this->logger->info("Transaction not found for $paymentOrderId->".print_r($e,true));
				}
				// $this->logger->info("Response from server for PaymentRequest ID $paymentOrderId ".PHP_EOL .print_r($response,true));
				
				#get payment status from response
				$payment_status = $response->success;
				$this->logger->info("Payment status for $transactionId is $payment_status");
				if($payment_status == '1' and !empty($response->transDetails))
				{
					$responseCode=end($response->transDetails)->RespCode;
					$this->logger->info("Response from server is $payment_status.");
					if( $responseCode == 000){
						#get transaction details from response
						$transactionArray=$response->transDetails;
						#get Order Number from transaction details
						$orderId = end($transactionArray)->OrderNum;
					}
					else{
						#using RefNum as OrderNum is same
						#get transaction details from response
						$transactionArray=$response->transDetails;
						#get Order Number from transaction details
						$orderId = end($transactionArray)->RefNum;
					}
					
					$this->logger->info("Extracted order id from trasaction_id: ".$orderId);
					# get order from orderId
					$order = $this->orderFactory->create()->loadByIncrementId($orderId);

					#get payment data from order details
					$payment = $order->getPayment();				
				
					if($order)
					{

						$order->setPaymentOrderId($paymentOrderId);
						$this->logger->info("Payment Order Id ".$paymentOrderId);

						if($payment_status == '1' and $responseCode == 000)
						{
							//$payment->setTransactionId($transactionId);
							//$trn = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH,null,true);
							
							#set order state as PROCESSING since we are doing AUTH 
							$order->setState(Order::STATE_PROCESSING)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));

							#set order state as COMPLETE since we are doing SALE 
							// $order->setState(Order::STATE_COMPLETE)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_COMPLETE));

							// $pId=$payment->getId();
							// $oId=$order->getId();
							//$payment->setAdditionalInformation();
							$payment->setAdditionalInformation('transactionId', $transactionId);
							$this->logger->info("payment additional Id".PHP_EOL .print_r($payment->getAdditionalInformation(),true));
							//$payment->setLastTransId($transactionId);
							
							#find transaction using paymentId and orderId
							$transaction = $this->transactionRepository->getByTransactionId(
									"-1",
									$payment->getId(),
									$order->getId()
							);
							
							if($transaction)
							{
								$transaction->setTxnId($transactionId);
								$transaction->setAdditionalInformation(  
									"EpiPayTransactionId",$transactionId
								);
								$transaction->setAdditionalInformation(  
									"status","successful"
								) ;
								$transaction->setIsClosed(1);
								$transaction->save(); 
								$this->logger->info("Transaction found.");

								// try{
								// 		// $items = $order->getItemsCollection();
								// 		$itemCollection = $order->getItemsCollection();
								// 		#itemsData array to store sku and quantity
								// 		$itemsData = array();
								// 		#loop through each items in items collection to extract sku and quantity
								// 		foreach($itemCollection as $item){
								// 			if ($item->getData()) {
								// 				$itemsData[] = [
								// 					'sku'=>$item->getSku(),
								// 					'qtyOrdered'=>$item->getQtyOrdered(),
								// 					'name'=>$item->getName()
								// 				];
								// 			}
								// 		}
								// 		$shippingaddress = $order->getShippingAddress();             
								// 		$shippingtelephone = $shippingaddress->getTelephone();

								// 	$this->zendClient->reset();
								// 	$this->zendClient->setUri("http://127.0.0.1:3002/sendOrder");
								// 	$this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
								// 	$this->zendClient->setHeaders([
								// 		'Content-Type' => 'application/json',
								// 		'Accept' => 'application/json',
								// 	]);
								// 	$apiData['customerName']=$order->getCustomerFirstname() ." ". $order->getCustomerLastname();
								// 	$apiData['customerMobile']=$shippingtelephone;
								// 	// $apiData['mobile']='+15614195599';
								// 	// $apiData['mobile']='+918149114289';
								// 	$apiData['mobile']=$this->config->getValue("payment/epi/fulfillment_merchant_mobile",$storeScope);
								// 	$apiData['orderId']=$orderId;
								// 	$apiData['orderedItems']=$itemsData;
								// 	$apiData['cartAmount']=$order->getPayment()->getAmountOrdered();

								// 	$this->zendClient->setRawBody(Json::encode($apiData));
								// 	$this->zendClient->send();
								// 	$response = $this->zendClient->getResponse();
								// 	$this->logger->info('Response from Fulfillment Bot->'.print_r($response->getBody(),true));
								// 	// $newQty=json_decode($response->getBody(),true);
								// }
								// catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
								// {
								// 	$this->logger->info('Error in Exatouch Api->'.print_r($runtimeException->getMessage(),true));
								// }
							}
							// exit;
							
							$payment->addTransactionCommentsToOrder($transaction,"Transaction is completed successfully");
							$payment->setParentTransactionId(null); 
							
							# send new email
							$order->setCanSendNewEmailFlag(true);
							$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
							$objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
							
							$payment->save();
							$order->save();

							$this->logger->info("Payment for $transactionId was credited.");
							$this->_redirect($this->urlBuilder->getUrl('checkout/onepage/success/',  ['_secure' => true]));
						}
						else if($responseCode != 000)
						{
						
							$transaction = $this->transactionRepository->getByTransactionId(
									"-1",
									$payment->getId(),
									$order->getId()
							);
							if($transaction)
							{
								$transaction->setTxnId($transactionId);
								$transaction->setAdditionalInformation(  
										"EpiTransactionId",$transactionId
									);
								$transaction->setAdditionalInformation(  
										"status","failed"
									);
								$transaction->setIsClosed(1);
								$transaction->save();
								$payment->addTransactionCommentsToOrder(
									$transaction,
									"The transaction is failed"
							);
							}
							// $this->logger->info("Transaction details - failed:".print_r($transaction->getIsClosed(),true) );
						
							try{
								$items = $order->getItemsCollection();
								foreach($items as $item)
									$this->cart->addOrderItem($item);
								$this->cart->save();
						
							}catch(Exception $e){
								$message = $e->getMessage();
								$this->logger->error("Not able to add Items to cart Exception MEssage".$message);
							}
							$order->cancel();
							$payment->setParentTransactionId(null);
							$payment->save();
							$order->save();
							$this->logger->error("Payment for $transactionId failed.");
							$this->_redirect($this->urlBuilder->getUrl('checkout/cart',  ['_secure' => true]));
						}
						
					}else
						$this->logger->warning("Order not found with order id $orderId");
				}
				else {
					$this->logger->warning("Transaction details missing.");
					$this->_redirect($this->urlBuilder->getBaseUrl());
				}
			}catch(CurlException $e){
				$this->logger->error($e);
					$this->_redirect($this->urlBuilder->getBaseUrl());
			}catch(ValidationException $e){
				// handle exceptions related to response from the server.
				$this->logger->error($e->getMessage()." with ");
				# add message into inbox of admin if authorization error.
				if(stristr($e->getMessage(),"Authorization"))
				{
					$this->inbox->addCritical("Epi Authorization Error","Please contact to Epi for troubleshooting. ".$e->getMessage());
				}
				$this->logger->error(print_r($e->getResponse(),true)."");
				$method_data['errors'] = $e->getErrors();			
			}catch(Exception $e){
				$this->logger->error($e->getMessage());
				$this->logger->error("Payment for $transactionId was not credited.");
				$this->_redirect($this->urlBuilder->getBaseUrl());
			}	 
		}else {
			$this->logger->warning("Callback called with no transactionId or paymentOrderId.");
			$this->_redirect($this->urlBuilder->getBaseUrl());
		}
	}
}
