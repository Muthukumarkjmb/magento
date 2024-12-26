<?php
namespace Contactus\ContactForm\Controller\Adminhtml\Submission;

use Magento\Backend\App\Action;
use Contactus\ContactForm\Model\SubmissionFactory;

class Delete extends Action
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
            try {
                $submission->delete();
                $this->messageManager->addSuccessMessage(__('Submission has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error deleting submission.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Submission does not exist.'));
        }

        $this->_redirect('*/*/');
    }
}
