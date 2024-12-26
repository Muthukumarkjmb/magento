<?php
namespace Epi\EpiPay\Block;

use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;	
use Epi\EpiPay\Logger\Logger;
use Magento\Framework\App\Response\Http;
use Magento\Sales\Model\Order\Payment\Transaction\Builder as TransactionBuilder;
use Magento\Sales\Api\Data\OrderInterface as OrderInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;

#Test
class Main extends  \Magento\Framework\View\Element\Template
{
	 protected $_objectmanager;
	 protected $checkoutSession;
	 protected $orderFactory;
	 protected $urlBuilder;
	 private $logger;
	 protected $response;
	 protected $config;
	 protected $_messageManager;
	 protected $transactionBuilder;
	 protected $inbox;
	//  protected $_redirect;
	 public function __construct(Context $context,
			Session $checkoutSession,
			OrderFactory $orderFactory,
			Logger $logger,
			Http $response,
			TransactionBuilder $tb,
			 \Magento\AdminNotification\Model\Inbox $inbox,
			 \Magento\Framework\App\Response\RedirectInterface $redirect,
			 \Magento\Framework\Message\ManagerInterface $messageManager,
			 \Magento\Checkout\Model\Cart $cart,
			 \Magento\Framework\Session\SessionManagerInterface $customerSession,
			 \Magento\Framework\App\ResourceConnection $resourceConnection,
			 CookieManagerInterface $cookieManager
		) {

		// $this->_redirect = $redirect;
		$this->checkoutSession = $checkoutSession;
		$this->orderFactory = $orderFactory;
		$this->response = $response;
		$this->config = $context->getScopeConfig();
		$this->transactionBuilder = $tb;
		$this->logger = $logger;					
		$this->inbox = $inbox;					
		$this->cart = $cart;
		$this->customerSession=$customerSession;
		$this->resourceConnection = $resourceConnection;
		$this->_messageManager = $messageManager;
		$this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()
							->get('Magento\Framework\UrlInterface');
		parent::__construct($context);
		$this->cookieManager = $cookieManager;
    }

	protected function _prepareLayout()
	{
		$this->logger->info('<----------Main Block---------->');
		$method_data = array();
		#get OrderId 
		$orderId = $this->checkoutSession->getLastOrderId();
		$quote = $this->checkoutSession->getQuote();
		#get shipping amount
		$shippingAmount = $quote->getShippingAddress()->getShippingAmount();
		
		#load order from database based on orderId
		$order = $this->orderFactory->create()->load($orderId);
		$this->logger->info('Creating Order for orderId->'.print_r($order->getRealOrderId(),true));
		if ($order)
		{	

			# setting xtmid
			$order->setXtmid($this->customerSession->getMxtmid());
			$this->logger->info('fetching xtmid from order object -> '.print_r($order->getXtmid(),true));

			#Set mid
			$order->setMid($this->customerSession->getMid());
			$this->logger->info('fetching mid from order object -> '.print_r($order->getMid(),true));

			#get billingAddress from order
			$billing = $order->getBillingAddress();
			#get payment method from order
			$payment = $order->getPayment();
			$this->logger->info("Payment method in Main Block->". print_r($payment->getMethod(),true));
		
			#set Transaction id to -1 , so we will be able to fetch Transaction data in response from payment frontend
			$payment->setTransactionId("-1");
			  $payment->setAdditionalInformation(  
				[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => array("Transaction is yet to complete")]
			);
			#set transaction type to sale
			$trn = $payment->addTransaction(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH,null,true);
			$trn->setIsClosed(0)->save();
			$payment->addTransactionCommentsToOrder(
                $trn,
               "The transaction is yet to complete."
            );

			$payment->setParentTransactionId(null);
			$payment->save();
			$order->save();

			$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/splash.log');
			$loggg = new \Zend_Log();
			$loggg->addWriter($writer);
			// $loggg->info('get xtmid in main ->'.print_r($order->getData(OrderInterface::CUSTOMER_NOTE), true));
			$loggg->info('get xtmid in main ->'.print_r($order->getXtmid(), true));
 
			try{
				#get store scope to fetch details from admin config
				$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
				// $mid = $this->config->getValue("payment/epi/merchant_id",$storeScope);
				// $client_secret = $this->config->getValue("payment/epi/client_secret",$storeScope);
				// $testmode = $this->config->getValue("payment/epi/epi_testmode",$storeScope);
				// $tid=$this->config->getValue("payment/epi/terminal_id",$storeScope);
				$this->logger->info($this->config->getValue("payment/epi/merchant_id",$storeScope));
				$this->logger->info($this->config->getValue("payment/epi/terminal_id",$storeScope));


				// $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();        
				// $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
				// $storeId=$storeManager->getStore()->getStoreId();
				$storeId=$this->cookieManager->getCookie('storeId');
				$tableName = $this->getTableName('amasty_amlocator_store_attribute');
				$this->logger->info('the store id is '.$storeId);
				// $query = 'SELECT (SELECT amasty_amlocator_store_attribute.value FROM '.$tableName.' amasty_amlocator_store_attribute.store_id='.$storeId.' AND amasty_amlocator_store_attribute.attribute_id=5) AS RC_MID (SELECT amasty_amlocator_store_attribute.value FROM '.$tableName.' amasty_amlocator_store_attribute.store_id='.$storeId.' AND amasty_amlocator_store_attribute.attribute_id=6) AS termId';
				$query= 'SELECT amasty_amlocator_store_attribute.value  FROM amasty_amlocator_store_attribute WHERE amasty_amlocator_store_attribute.store_id='.$storeId .' AND amasty_amlocator_store_attribute.attribute_id IN (3,4)';
				$results = $this->resourceConnection->getConnection()->fetchAll($query);
				$this->logger->info('this is sql query'.print_r($results, true));

				#preapre payload data to create order in payment server
				$api_data['orderId'] =$order->getRealOrderId();
				$api_data['phone'] = $billing->getTelephone();
				$api_data['email'] = $billing->getEmail();
				$api_data['name'] = $billing->getFirstname() ." ". $billing->getLastname();
				$api_data['amount'] = round($order->getGrandTotal(),2);
				$api_data['currency'] = "USD";
				$api_data['redirectUrl'] = $this->urlBuilder->getUrl("epi/response");
				$api_data['streetAddress']=$billing->getStreetLine(1)." ".$billing->getStreetLine(2)." ".$billing->getStreetLine(3);
				$api_data['city']=$billing->getCity();
				$api_data['state']=$billing->getRegion();
				$api_data['country']=$billing->getCountryId();
				$api_data['postCode']=$billing->getPostcode();
				$api_data['mid']=$results[0]['value'];//RC Gateway mid
				$api_data['termId']=$results[1]['value'];
				$api_data['txnType']='Authorization';
				$api_data['ShippingRate']=$shippingAmount;	
				$items = $order->getAllVisibleItems();
				$itemsData = array();
				foreach ($items as $item) {
					if ($item->getData()) {
						$itemsData[] = [
							'name'=>$item->getName(),
							'sku'=>$item->getSku(),
							'itemId'=>$item->getItemId(),
							'qty'=>$item->getQtyOrdered(),
							'productType'=>$item->getProductType(),
							'weight'=>$item->getWeight(),
							'price'=>$item->getPrice()
						];
					}
				}
				$api_data['orderItems']=json_encode($itemsData, JSON_FORCE_OBJECT);
				$this->logger->info("Data sent for creating order ".print_r($api_data,true));
				// $this->logger->warning('This is a log warning! ^_^ ');
				// $this->logger->error('This is a log error! ^_^ ');
				if($order->getShippingMethod() != "amstorepickup_amstorepickup"){
						$increment_id = $order->getRealOrderId();
						$entity_id = $order->getId();

						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$cache = $objectManager->get('Magento\Framework\App\CacheInterface');
						$distance =unserialize($cache->load('delivery_distance'));
                      
						if($distance != null){
							$sql = 'INSERT INTO delivery_distance ( distance,order_entity_id,order_increment_id) values ('.$distance.','.$entity_id.','.$increment_id.')';
							$this->logger->info("This is the sql query to insert data into the delivery_distance.".$sql);
							$this->logger->info($sql);
							$this->resourceConnection->getConnection()->query($sql);
							$this->logger->info("Data has inserted successfully inside the table....");
							$cache->remove('delivery_distance');
							
						}
					}
				#include Epi.php class to make post calls
				$ds = DIRECTORY_SEPARATOR;
				include __DIR__ . "$ds..$ds/lib/Epi.php";
				#initialize Epi class object				
				// $api = new \Epi($mid,$client_secret,$testmode);
				$api = new \Epi\EpiPay\lib\Epi();
				$accessToken = $api->getFrontendToken();
				$this->setToken($accessToken['token']);
				$this->setExpireAt($accessToken['expire_at']);
				#send request to create order in payment server
				$response = $api->createOrderPayment($api_data);
				$this->logger->info("Response from server after creating order->". print_r($response,true));
				if(isset($response->paymentOrderId))
				{
					#if response has paymentOrderId
					#set payment frontend url and orderId to redirect
					$this->setAction($response->payment_url);
					$this->setOrderId($response->paymentOrderId);
					$this->checkoutSession->setPaymentRequestId($response->paymentOrderId);	
				}
				else{
					// throw new Exception("Create Order Failed");
					try{
						$items = $order->getItemsCollection();
						foreach($items as $item)
						$this->cart->addOrderItem($item);
						$this->cart->save();
					}catch(Exception $e){
						$this->logger->error("Not able to add items to cart . Exception Message $e");
					}
					$order->cancel();
					$payment->setParentTransactionId(null);
					$payment->save();
					$order->save();
					$this->logger->error("Redirect to payment page failed");
					$this->setAction($this->urlBuilder->getUrl('checkout/cart',  ['_fragment'=>'payment']));
					$this->_messageManager->addError(__('Payment Failed. Please Try Again.'));
				}
			}catch(\CurlException $e){
				# handle exception related to connection to the sever
				$this->logger->error("<------Curl Exception---------->");
				$this->logger->error((string)$e);
				// $method_data['errors'][] = $e->getMessage();
				$method_data['errors'][] = "Technical Issues. Please try again.";
			}catch(\ValidationException $e){
				# handle exceptions related to response from the server.
				$this->logger->error($e->getMessage()." with ");
				if(stristr($e->getMessage(),"Authorization"))
				{
					$inbox->addCritical("Epi Authorization Error",$e->getMessage());
				}
				$this->logger->error(print_r($e->getResponse(),true)."");
				$method_data['errors'] = $e->getErrors();			
			}catch(\Exception $e){ 
				#handled common exception messages which will not get caught above.
				$this->logger->error("<----------Exception--------->");
				$method_data['errors'][] = $e->getMessage();
				$this->logger->error('Error While Creating Order : ' . $e->getMessage());
			}	
		}
		else
		{
			$this->logger->error('Order with ID'. $orderId.' not found. Quitting.');
		}
			$showPhoneBox = false;
			if(isset($method_data['errors']) and is_array($method_data['errors']))
			{
			$this->setMessages($method_data['errors']);
			}
			// if($showPhoneBox)
			// $this->setTelephone($api_data['phone']);
			// $this->setShowPhoneBox($showPhoneBox);
	}
}
