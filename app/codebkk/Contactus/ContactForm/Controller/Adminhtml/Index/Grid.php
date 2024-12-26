<?php

namespace Contactus\ContactForm\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Grid extends Action
{
    const ADMIN_RESOURCE = 'Contactus_ContactForm::menu_item';

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
    
        // Return the page
        return $this->resultPageFactory->create();
    }
}


