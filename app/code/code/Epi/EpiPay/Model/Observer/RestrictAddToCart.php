<?php
namespace Epi\EpiPay\Model\Observer;
 
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Message\ManagerInterface;
use Epi\EpiPay\Logger\Logger;
class RestrictAddToCart implements ObserverInterface
{
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
 
    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
 
    /**
     * add to cart event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */

      /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * RestrictAddToCart constructor.
     *
     * @param ProductRepository $productRepository
     * @param ManagerInterface $messageManager
     * */

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Zend\Http\Client $zendClient,
        ProductRepository $productRepository,
        // ManagerInterface $messageManager,
        Logger $logger
    )
    {
        $this->_messageManager = $messageManager;
        $this->zendClient = $zendClient;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('-------Inventory Check--------');

        // #get product sku using product id from product repository
        $productId = $observer->getRequest()->getParam('product');
        $product=$this->productRepository->getById($productId);
        $sku=$product->getSku();

        // #get MID from customer session
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
        $mid=$customerSession->getMid();
        if(isset($mid)==false){
            $this->_messageManager->addError(__('Session timed out.'));
            $observer->getRequest()->setParam('product', false);
            return $this;
        }

        #------------------------API
            #include Epi.php class to make post calls
            $ds = DIRECTORY_SEPARATOR;
            include __DIR__ . "$ds..$ds..$ds/lib/Api.php";
            #initialize Epi class object				
            $api = new \Api();

            #send request to create order in payment server
            $response = $api->checkInventory($mid,$sku);
            $this->logger->info('Qty in XT->'.print_r($response,true));
        #------------------------API

            #check if the item is available for the MID
            $formattedResponse=json_decode($response->getBody(),true);
            if($response->getStatusCode() == 200 && isset($formattedResponse[0]['ItemID'])){
                $newQty=$formattedResponse[0]['QtyOnHand'];
                $this->logger->info('Qty in XT->'.$newQty);
                if(isset($newQty)==false){
                    //set false if you not want to add product to cart
                    // $observer->getRequest()->setParam('product', true);
                    // return $this;
                    $this->_messageManager->addError(__('Item not available'));
                    $observer->getRequest()->setParam('product', false);
                    return $this;
                }
            
            }
            else{
                // $this->_messageManager->addError(__('Item not available'));
                // $observer->getRequest()->setParam('product', false);
                return $this; 
            }
        return $this;
    }
}
