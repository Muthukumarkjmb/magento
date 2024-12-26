<?php

namespace Contactus\ContactForm\Controller\Adminhtml\Contactform;

use Magento\Backend\App\Action;
use Contactus\ContactForm\Model\ContactFactory;

class Save extends Action
{
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
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = $data['id'] ?? null;
            $model = $this->contactFactory->create();

            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('The record has been saved.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error while saving record.'));
            }
        }

        return $this->_redirect('*/*/');
    }
}
