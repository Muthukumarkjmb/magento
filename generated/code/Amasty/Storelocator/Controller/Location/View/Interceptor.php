<?php
namespace Amasty\Storelocator\Controller\Location\View;

/**
 * Interceptor class for @see \Amasty\Storelocator\Controller\Location\View
 */
class Interceptor extends \Amasty\Storelocator\Controller\Location\View implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Amasty\Storelocator\Model\Location $locationModel, \Magento\Framework\Registry $coreRegistry)
    {
        $this->___init();
        parent::__construct($context, $locationModel, $coreRegistry);
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
