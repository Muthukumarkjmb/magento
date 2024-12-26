<?php
namespace Burstonline\Feeconfig\Controller\Adminhtml\Store\Delete;

/**
 * Interceptor class for @see \Burstonline\Feeconfig\Controller\Adminhtml\Store\Delete
 */
class Interceptor extends \Burstonline\Feeconfig\Controller\Adminhtml\Store\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Burstonline\Feeconfig\Model\FeeconfigFactory $feeFactory)
    {
        $this->___init();
        parent::__construct($context, $coreRegistry, $feeFactory);
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
