<?php
namespace Burstonline\Feeconfig\Controller\Adminhtml\Store;

class Index extends \Magento\Backend\App\Action
{
	protected $resultPageFactory = false;
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}
	public function execute()
	{
		//Call page factory to render layout and page content
		$resultPage = $this->resultPageFactory->create();
		//Set the menu which will be active for this page
		$resultPage->setActiveMenu('Burstonline_Feeconfig::menu_item');
		
		//Set the header title of grid
		$resultPage->getConfig()->getTitle()->prepend(__('Fee Configuration'));
		//Add bread crumb
		$resultPage->addBreadcrumb(__('Burstonline'), __('Burstonline'));
		$resultPage->addBreadcrumb(__('Feeconfig'), __('Entries'));
		return $resultPage;
	}
}
