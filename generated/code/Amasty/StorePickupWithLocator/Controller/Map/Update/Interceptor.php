<?php
namespace Amasty\StorePickupWithLocator\Controller\Map\Update;

/**
 * Interceptor class for @see \Amasty\StorePickupWithLocator\Controller\Map\Update
 */
class Interceptor extends \Amasty\StorePickupWithLocator\Controller\Map\Update implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Serialize\SerializerInterface $jsonEncoder)
    {
        $this->___init();
        parent::__construct($context, $jsonEncoder);
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
