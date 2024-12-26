<?php

namespace Burstonline\Epipayment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Psr\Log\LoggerInterface;

class GenerateInvoiceAfterOrderPlaced implements ObserverInterface
{
    protected $invoiceService;
    protected $transaction;
    protected $invoiceSender;
    protected $logger;

    public function __construct(
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender,
        LoggerInterface $logger
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->canInvoice()) {
            try {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->register();
                $invoice->save();

                $transactionSave = $this->transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();

                // Send invoice email
                $this->invoiceSender->send($invoice);

                // Set order status to processing or complete
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
                      ->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);

                $order->save();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
