<?php
namespace Burstonline\Epipayment\Controller\Adminhtml\Index\Orderxtcall;

/**
 * Interceptor class for @see \Burstonline\Epipayment\Controller\Adminhtml\Index\Orderxtcall
 */
class Interceptor extends \Burstonline\Epipayment\Controller\Adminhtml\Index\Orderxtcall implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Burstonline\Epipayment\Model\Ordertoxt $Ordertoxt, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Psr\Log\LoggerInterface $logger)
    {
        $this->___init();
        parent::__construct($context, $Ordertoxt, $resultJsonFactory, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
