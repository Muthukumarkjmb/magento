<?php    
namespace Epi\EpiPay\Plugins;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
// use Vendor\Wishlist\Helper\Data as dataHelper;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Epi\EpiPay\Logger\Logger;
/**
 * Class AddWishlist
 * @package Vendor\Wishlist\Plugin
 */
class AddWishlist
{

    /**
     * @var ManagerInterface
    */
    protected $messageManager;

    protected $redirect;

    /**
     * @var ProductRepositoryInterface
    */
    protected $productRepository;
    protected $resultRedirectFactory;
    protected $request;
    protected $logger;
    protected $zendClient;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        // dataHelper $dataHelper,
        \Magento\Framework\App\Action\Context $context,
        ProductRepositoryInterface $productRepository,
        Logger $logger,
        \Zend\Http\Client $zendClient
    ) {  
        // $this->dataHelper         = $dataHelper;
        $this->messageManager     = $messageManager;
        $this->redirect           = $redirect;
        $this->productRepository  = $productRepository;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->request = $context->getRequest();
        $this->logger = $logger;
        $this->zendClient = $zendClient;
    }

    /**
     * Plugin for restrict wishlist 
    */
    public function aroundExecute(\Magento\Wishlist\Controller\Index\Add $subject, \Closure $proceed)
    {              
        $this->logger->info('----ADD TO WISHLIST PLUGIN----');
        #get MID from customer session
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
        $mid=$customerSession->getMid();

        $requestParams = $this->request->getParams();
        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;
        $this->logger->info('Product Id->'.print_r($productId,true));
        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }
        try {
            $product = $this->productRepository->getById($productId);
            $sku=$product->getSku();
            $this->logger->info('Product Id->'.print_r($product->getSku(),true));
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if(isset($sku)){
           #------------------------API
            #include Epi.php class to make post calls
            $ds = DIRECTORY_SEPARATOR;
            include __DIR__ . "$ds..$ds/lib/Api.php";
            #initialize Epi class object				
            $api = new \Api();

            #send request to create order in payment server
            $response = $api->checkInventory($mid,$sku);
            $this->logger->info('Qty in XT->'.print_r($response,true));
            #------------------------API
        }
        else{
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addNotice(__('Item not available in current store'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->redirect->getRefererUrl());
            return $resultRedirect;
        }
         
        #check if the item is available for the MID
        if($response->getStatusCode() == 200){
        $formattedResponse=json_decode($response->getBody(),true);
        $newQty=$formattedResponse[0]['QtyOnHand'];
        $this->logger->info('Qty in XT->'.$newQty);
            if(isset($newQty)){
                return $proceed();
            }
            else{         
                $resultRedirect = $this->resultRedirectFactory->create();
                $this->messageManager->addNotice(__('Item not available in current store'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($this->redirect->getRefererUrl());
                return $resultRedirect; 
            }
    }
    else{
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->messageManager->addNotice(__('Item not available in current store'));
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->redirect->getRefererUrl());
        return $resultRedirect; 
    }



    return $proceed();
    }
}
