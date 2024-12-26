<?php
namespace Amasty\Storelocator\Controller\Router;

/**
 * Interceptor class for @see \Amasty\Storelocator\Controller\Router
 */
class Interceptor extends \Amasty\Storelocator\Controller\Router implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\ActionFactory $actionFactory, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager, \Amasty\Storelocator\Model\ResourceModel\Location $locationResource, \Amasty\Storelocator\Model\ConfigProvider $configProvider)
    {
        $this->___init();
        parent::__construct($actionFactory, $scopeConfig, $storeManager, $locationResource, $configProvider);
    }

    /**
     * {@inheritdoc}
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'match');
        return $pluginInfo ? $this->___callPlugins('match', func_get_args(), $pluginInfo) : parent::match($request);
    }
}
