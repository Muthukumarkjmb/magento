<?php

namespace Contactus\ContactForm\Controller\Adminhtml\Contactform;

use Magento\Backend\App\Action;
use Contactus\ContactForm\Model\ContactFactory;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Contactus_ContactForm::menu_item';

    protected $contactFactory;

    public function __construct(
        Action\Context $context,
        ContactFactory $contactFactory
    ) {
        $this->contactFactory = $contactFactory;
        parent::__construct($context);
    }

    public function execute()
    {   
        // Retrieve the 'id' and 'form_key' from the POST request
        $id = $this->getRequest()->getParam('id');       

        if ($id) {
            try {
                // Load the model and delete the record
                $model = $this->contactFactory->create();
                $model->load($id);
                $model->delete();

                // Send success response
                if ($this->getRequest()->isAjax()) {
                    $response = ['status' => 'success', 'message' => __('The record has been deleted.')];
                    $this->getResponse()->setHeader('Content-Type', 'application/json');
                    $this->getResponse()->setBody(json_encode($response));
                    return;
                }

                // Redirect if it's not an AJAX request
                $this->messageManager->addSuccessMessage(__('The record has been deleted.'));
                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                // Send error response if deletion fails
                if ($this->getRequest()->isAjax()) {
                    $response = ['status' => 'error', 'message' => __('Error while deleting the record.')];
                    $this->getResponse()->setHeader('Content-Type', 'application/json');
                    $this->getResponse()->setBody(json_encode($response));
                    return;
                }

                // Display error message
                $this->messageManager->addErrorMessage(__('Error while deleting the record.'));
                return $this->_redirect('*/*/');
            }
        }

        return 'fail';
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
