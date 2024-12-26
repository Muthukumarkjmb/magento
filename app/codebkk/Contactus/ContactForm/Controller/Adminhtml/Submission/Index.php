<?php
namespace Contactus\ContactForm\Controller\Adminhtml\Submission;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
      private $logger;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
            $this->logger = $logger;
    }
    public function execute()
    {

        $this->logger->info('New entry pointed.');
        //Call page factory to render layout and page content
        $resultPage = $this->resultPageFactory->create();
        //Set the menu which will be active for this page
        $resultPage->setActiveMenu('Contactus_ContactForm::menu_item');
        
        //Set the header title of grid
        $resultPage->getConfig()->getTitle()->prepend(__('Contact us'));
        //Add bread crumb
        $resultPage->addBreadcrumb(__('Contactus'), __('Contactus'));
        $resultPage->addBreadcrumb(__('ContactForm'), __('Entries'));
        return $resultPage;
    }
}
