<?php
namespace Contactus\ContactForm\Controller\Adminhtml\Submission;

use Magento\Backend\App\Action;
use Contactus\ContactForm\Model\SubmissionFactory;

class Edit extends Action
{
    protected $submissionFactory;

    public function __construct(
        Action\Context $context,
        SubmissionFactory $submissionFactory
    ) {
        parent::__construct($context);
        $this->submissionFactory = $submissionFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $submission = $this->submissionFactory->create()->load($id);

        if ($submission->getId()) {
            // Load edit form and pass submission data to it
        } else {
            $this->messageManager->addErrorMessage(__('This submission no longer exists.'));
            $this->_redirect('*/*/');
        }
    }
}
