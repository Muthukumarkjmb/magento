<?php

namespace Contactus\ContactForm\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Contactus\ContactForm\Model\SubmissionFactory;

class Submit extends Action
{
    protected $submissionFactory;
    protected $redirectFactory;

    public function __construct(
        Context $context,
        SubmissionFactory $submissionFactory,
        RedirectFactory $redirectFactory
    ) {
        $this->submissionFactory = $submissionFactory;
        $this->redirectFactory = $redirectFactory;
        parent::__construct($context);
    }

    public function execute()
    {

    	// print_r($this->getRequest()->getPostValue());

    	
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $submission = $this->submissionFactory->create();
            $submission->setData($data);
            $submission->save();

            $this->messageManager->addSuccessMessage(__('Your message has been submitted.'));
        }

        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath('/');
    }
}
