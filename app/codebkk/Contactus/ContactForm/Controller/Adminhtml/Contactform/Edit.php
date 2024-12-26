<?php

namespace Contactus\ContactForm\Controller\Adminhtml\Contactform;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Contactus\ContactForm\Model\ContactFactory;
use Psr\Log\LoggerInterface;

class Edit extends Action
{
    const ADMIN_RESOURCE = 'Contactus_ContactForm::edit';

    protected $resultPageFactory;
    protected $coreRegistry;
    protected $contactFactory;
      protected $logger;

    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        ContactFactory $contactFactory,
         LoggerInterface $logger
         
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->contactFactory = $contactFactory;
         $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->logger->info('controller calls.');

        $editId = $this->getRequest()->getParam('id');
        $this->logger->info('Edit ID: ' . $editId);
        $model = $this->contactFactory->create();

        if ($editId) {
            $model->load($editId);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This record no longer exists.'));
                return $this->_redirect('*/*/');
            }
        }

        $this->_coreRegistry->register('contact_data', $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Contact'));
        return $resultPage;
    }

  protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

}
