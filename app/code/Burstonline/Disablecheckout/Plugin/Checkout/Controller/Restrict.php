<?php
 
namespace Burstonline\Disablecheckout\Plugin\Checkout\Controller;
 
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlFactory;
use Magento\Checkout\Controller\Index\Index;
 
class Restrict
{
    private $urlModel;
    private $resultRedirectFactory;
    private $messageManager;
    protected $customconfig;
 
    public function __construct(
        UrlFactory $urlFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        \Burstonline\Customconfig\Block\Customconfig $customconfig
    ) {
    
        $this->urlModel = $urlFactory;
        $this->resultRedirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->customconfig = $customconfig;
    }
 
    public function aroundExecute(
        Index $subject,
        \Closure $proceed
    ) {
    
        $this->urlModel = $this->urlModel->create();
        date_default_timezone_set('US/Eastern');
		$currenttime = date('H');//die;
		
		$currentDay = date('D');
		
		$isSundayAllowed=$this->customconfig->isSundayAllowed();
		if(!$isSundayAllowed && $currentDay=="Sun"){
			$this->messageManager->addErrorMessage(__('Sales not allowed on sunday'));
			$defaultUrl = $this->urlModel->getUrl('checkout/cart/', ['_secure' => true]);
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setUrl($defaultUrl);
		}
		
		/*if($currenttime < 9 || $currenttime >=23){
			$this->messageManager->addErrorMessage(__('Orders can only be placed between the hours of 9 AM and 11 PM.'));
			$defaultUrl = $this->urlModel->getUrl('checkout/cart/', ['_secure' => true]);
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setUrl($defaultUrl);
		}*/
          
        return $proceed();
    }
}
