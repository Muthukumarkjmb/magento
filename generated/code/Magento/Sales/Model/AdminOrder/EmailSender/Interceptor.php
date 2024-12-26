<?php
namespace Magento\Sales\Model\AdminOrder\EmailSender;

/**
 * Interceptor class for @see \Magento\Sales\Model\AdminOrder\EmailSender
 */
class Interceptor extends \Magento\Sales\Model\AdminOrder\EmailSender implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Message\ManagerInterface $messageManager, \Psr\Log\LoggerInterface $logger, \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender, \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender)
    {
        $this->___init();
        parent::__construct($messageManager, $logger, $orderSender, $invoiceSender);
    }

    /**
     * {@inheritdoc}
     */
    public function send(\Magento\Sales\Model\Order $order)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'send');
        return $pluginInfo ? $this->___callPlugins('send', func_get_args(), $pluginInfo) : parent::send($order);
    }
}
