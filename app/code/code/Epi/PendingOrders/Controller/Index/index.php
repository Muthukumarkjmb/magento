<?php
namespace EPI\PendingOrders\Controller\Index;
use Epi\PendingOrders\Logger\Logger;
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
    protected $urlBuilder;
    private $logger;
    /**      * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Data\Form\FormKey $FormKey,
        \Magento\Checkout\Model\Cart $cart,
        Logger $logger,
        \Magento\Catalog\Model\Product $Product
        )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->logger = $logger;
        $this->formKey = $FormKey;
        $this->product = $Product;
		$this->urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        parent::__construct($context);
    }
    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->logger->info('<----------Inside Pending Payment---------->');

        $resultPage = $this->resultPageFactory->create();
        // var_dump($this->_checkoutSession->getData());
        $checkoutData = $this->_checkoutSession->getData();
        // $productInfo = $this->cart->getQuote()->getItemsCollection();

        $this->logger->info("Checkout Data ->".print_r($checkoutData,true));
        // $this->logger->info("Product Data ->".print_r($productInfo,true));


        // var_dump($checkoutData);
        if(count($checkoutData)>0){
            $orderId = array_key_exists("last_order_id",$checkoutData)?$checkoutData["last_order_id"]:"";
            if($orderId!=""){
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
            // $orderStatus = $checkoutData["last_order_status"];
            if(isset($order)){
            $orderStatus =  $order->getStatus();

            $orderRealId = $checkoutData["last_real_order_id"];
            $paymentRequestId = $checkoutData["payment_request_id"];
            $this->logger->info("Order status-->".$orderStatus);
            if($orderStatus == "pending"){
                try{
                    $this->logger->info("Order real ID ->".$orderRealId);
                    $product;
                    $resultPage->getConfig()->getTitle()->prepend(__('Last Pending Order Items'));
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
                    $orderItems = $order->getAllItems();
                    $items = $order->getItemsCollection();
                    foreach($items as $item){
                        $this->cart->addOrderItem($item); 
                    }
                    $this->cart->save();
                    // $this->logger->info("Cart Saved ->".print_r($this->cart,true));
                    $order->cancel();
                    // $this->logger->info("Order Cancelled ->".$orderRealId);
                    $order->save();
                    $this->_redirect($this->urlBuilder->getUrl('checkout/cart',  ['_secure' => true]));

                }catch(Exception $e){
                    // var_dump($e);
                    $this->logger->info("Exception Occured ->".print_r($e,true));
                }

            }
            else{
                $resultPage->getConfig()->getTitle()->prepend(__('There are no pending order items available.'));
                // $this->_redirect($this->urlBuilder->getUrl('http://devstore.com/pr-splash-page/account/login/',  ['_secure' => true]));
            }
        }
        else{
            $resultPage->getConfig()->getTitle()->prepend(__('There are no pending order items available.'));
            // $this->_redirect($this->urlBuilder->getUrl('http://devstore.com/pr-splash-page/account/login/',  ['_secure' => true]));
        }
    }
}
        $this->logger->info("Exiting From PendingOrders ");
        return $resultPage;
    }
}