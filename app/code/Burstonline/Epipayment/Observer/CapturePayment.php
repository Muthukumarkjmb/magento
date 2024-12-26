<?php

namespace Burstonline\Epipayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Psr\Log\LoggerInterface;

class CapturePayment implements ObserverInterface
{
    protected $logger;
    protected $orderPaymentRepository;

    public function __construct(
        LoggerInterface $logger,
        OrderPaymentRepositoryInterface $orderPaymentRepository
    ) {
        $this->logger = $logger;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->logger->info("CapturePayment observer constructed.");
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();
        
        $payment->setOrderDetails($order);
        
        try {
            $payment->capture();
           // $this->orderPaymentRepository->save($payment);
            $this->logger->info("Payment captured for order " . json_encode($payment->getData()));
        } catch (\Exception $e) {
            $this->logger->error("Error capturing payment for order " . $order->getIncrementId() . ": " . $e->getMessage());
        }
    }
}