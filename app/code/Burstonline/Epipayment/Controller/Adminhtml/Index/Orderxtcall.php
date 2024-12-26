<?php
namespace Burstonline\Epipayment\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Burstonline\Epipayment\Model\Ordertoxt;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;

class Orderxtcall extends Action
{
    protected $Ordertoxt;
    protected $logger;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Ordertoxt $Ordertoxt,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        $this->Ordertoxt = $Ordertoxt;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    { 
        $orderId = $this->getRequest()->getParam('orderId');
        $this->Ordertoxt->excuteorderxt($orderId);
        $result = $this->resultJsonFactory->create();

        $data = ['success' => 'Synch Order data to XT.'];

        return $result->setData($data);
    }
}

