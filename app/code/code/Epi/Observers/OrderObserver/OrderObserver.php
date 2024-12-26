<?php
namespace Epi\Observers\OrderObserver;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use \Zend\Json\Json;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
class OrderObserver implements ObserverInterface
{
    protected $urlBuilder;
    protected $scopeConfig;
    protected $orderCommentSender;
    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order $order
     */
    public function __construct(Context $context,
    \Magento\Sales\Model\Order $order,
    \Zend\Http\Client $zendClient,
    OrderFactory $orderFactory,
    \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    OrderCommentSender $orderCommentSender
    ){
        $this->order = $order;
        $this->zendClient = $zendClient;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        // $this->config = $context->getScopeConfig();
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/observers.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
        $ds = DIRECTORY_SEPARATOR;
        $this->ini = parse_ini_file(__DIR__ ."$ds../lib/config.ini");
        $this->scopeConfig = $scopeConfig;
        $this->orderCommentSender = $orderCommentSender;
    }
    private function getPaymentMethod($order) {
        $paymentMethod = '';
        if ($order->getPayment()->getMethod() == 'epi') {
            $paymentMethod = 'EpiPay';
        } else {
            $paymentMethod = 'Pay on delivery';
        }

        return $paymentMethod;
    }

    private function sendEmailToMerchant($apiData){
        try{
           
            $this->zendClient->reset();
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
            $this->zendClient->setUri($this->ini['fulfilmentEmailAPIURL']);
            $this->zendClient->setRawBody(Json::encode($apiData));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response from Fulfillment Bot->'.print_r($response->getBody(),true));
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Email sending process ->'.print_r($runtimeException->getMessage(),true));
        }
    }

    private function sendSMSToMerchant($phoneNumbers,$message) {

        # looping through all the phone number of the merchant and sending the SMS for order being placed
        try{
          
            $this->zendClient->reset();
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
            $this->zendClient->setUri($this->ini['fulfilmentAPIURL']);
            for ($numIndex=0; $numIndex<count($phoneNumbers); $numIndex++){
                $apiData['mobile']=trim($phoneNumbers[$numIndex]);
                $encodedNumber = urlencode(trim($phoneNumbers[$numIndex]));
                $apiData['message'] = $message.$encodedNumber;
                $this->logger->info('apiData from sendSMSToMerchant function'.print_r($apiData,true));
            
                $this->zendClient->setRawBody(Json::encode($apiData));
                $this->zendClient->send();
                $response = $this->zendClient->getResponse();
                $this->logger->info('Response from Fulfillment Bot->'.print_r($apiData,true));
                $this->logger->info('Response from Fulfillment Bot->'.print_r($response->getBody(),true));
            }
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in SMS sending process ->'.print_r($runtimeException->getMessage(),true));
        }

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $ds = DIRECTORY_SEPARATOR;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
       
		$this->logger->info('<----------Order Observer---------->');
		$this->logger->info('<----------Order Observer'.$this->ini['fulfilmentAPIURL']);

  
        $order = $observer->getEvent()->getOrder();
        $orderStatus=$order->getStatus();

        $this->logger->info('this is order state'.$order->getOrigData('state')); 
        if (($orderStatus == 'complete' && $order->getOrigData('state')!='complete') ||  ($orderStatus == 'closed' && $order->getOrigData('state')!='closed') ||  ($orderStatus == 'canceled' && $order->getOrigData('state')!='canceled') ||  ($orderStatus == 'confirmed' && $order->getOrigData('state')!='holded')) {
            $this->orderCommentSender->send($order, true);
            }
        
        $orderId = $order->getIncrementId();
		$this->logger->info('order ID->'.print_r($orderId,true));
        $transactionId = $order->getPayment()->getAdditionalInformation('transactionId');
		$paymentMethod = $order->getPayment()->getMethod();
        $this->logger->info('Transaction ID->'.print_r($transactionId,true));
		$this->logger->info('State->'.print_r($order->getState(),true));
		$this->logger->info('EntityId->'.print_r($order->getEntityId(),true));

        if(($order->getState()=='new' and $order->getOrigData('state')!='new' and $paymentMethod=='epipaylater')or ($order->getState()=='processing' and $order->getOrigData('state')!='processing' and $paymentMethod=='epi')){
            try{
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $customerSession = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
                $merchantPrimaryPhone=$customerSession->getMphone();
                $merchantSecondaryPhoneNumbers = $customerSession->getMsecondaryphone();
                $merchantEmail=$customerSession->getMemail();

                $phoneNumberArr = [$merchantPrimaryPhone];

                if (isset($merchantSecondaryPhoneNumbers) and trim($merchantSecondaryPhoneNumbers)!="") {
                    $secondaryNumbers = explode(",",$merchantSecondaryPhoneNumbers);
                    $phoneNumberArr = array_merge($phoneNumberArr,$secondaryNumbers);
                }
                

                // $itemCollection = $order->getAllItems();
                // #itemsData array to store sku and quantity
                // $itemsData = array();
                // #loop through each items in items collection to extract sku and quantity
                // foreach($itemCollection as $item){
                //     $this->logger->info('Item->'.print_r($item->getSku(),true));
                //     if ($item->getData()) {
                       
                //         $itemsData[] = [
                //             'sku'=>$item->getSku(),
                //             'qtyOrdered'=>$item->getQtyOrdered(),
                //             'name'=>$item->getName()
                            
                //         ];
                //     }
                // }

                
                $paymentMethod = $this->getPaymentMethod($order);
                $entityId = $order->getEntityId();
                $this->logger->info('Response from Fulfillment Bot->'.print_r($entityId,true));
                
                $message = "Hi Merchant, this is Todays order #".$orderId." Total Amount $".round($order->getPayment()->getAmountOrdered(),2).". Payment Method:".$paymentMethod." from liquorCart 🍻 store please confirm or delete the order by visiting https://josephsbeverage-teststore.epicommercestore.com:8080/confirmationPage?entity_id=".$entityId."&number=";
                $this->sendSMSToMerchant($phoneNumberArr,$message);


                $encodedNumber = urlencode(trim($merchantPrimaryPhone));
                $apiData['message'] = $message.$encodedNumber;
                
                $apiData['email']=$merchantEmail;
                $apiData['bcc']="kriti.tripathi@remo-sys.com,sonal.kashyap@remo-sys.com,jyoti.saha@metadesignsoftware.com,adam@electronicpayments.com,sharon@electronicpayments.com,supriya.kirasur@remosys.in";
                $this->sendEmailToMerchant($apiData);
                // $order->save();
                // $this->_redirect($this->urlBuilder->getUrl('checkout/onepage/success/',  ['_secure' => true]))
        
            }
            catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
                $this->logger->info('Error in Exatouch Api->'.print_r($runtimeException->getMessage(),true));
            }
        }

     }
}