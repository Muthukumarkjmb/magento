<?php

namespace Burstonline\Epipayment\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Session\SessionManagerInterface;

class SavePaymentData extends Action
{
    protected $resultJsonFactory;
    protected $session;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SessionManagerInterface $session
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        //echo "<pre>"; print_r($data); die;
        if ($data) {
            $this->session->setPaymentFormData($data);
            return $result->setData(['success' => true]);
        }

        return $result->setData(['success' => false]);
    }
}
