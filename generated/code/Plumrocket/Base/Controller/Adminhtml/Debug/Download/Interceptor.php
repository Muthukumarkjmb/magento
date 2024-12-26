<?php
namespace Plumrocket\Base\Controller\Adminhtml\Debug\Download;

/**
 * Interceptor class for @see \Plumrocket\Base\Controller\Adminhtml\Debug\Download
 */
class Interceptor extends \Plumrocket\Base\Controller\Adminhtml\Debug\Download implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Plumrocket\Base\Model\Debug\Export $exportDebugInfo)
    {
        $this->___init();
        parent::__construct($context, $exportDebugInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function execute() : \Magento\Framework\Controller\ResultInterface
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
