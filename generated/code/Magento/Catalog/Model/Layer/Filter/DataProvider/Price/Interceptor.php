<?php
namespace Magento\Catalog\Model\Layer\Filter\DataProvider\Price;

/**
 * Interceptor class for @see \Magento\Catalog\Model\Layer\Filter\DataProvider\Price
 */
class Interceptor extends \Magento\Catalog\Model\Layer\Filter\DataProvider\Price implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\Layer $layer, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price $resource)
    {
        $this->___init();
        parent::__construct($layer, $coreRegistry, $scopeConfig, $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function validateFilter($filter)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'validateFilter');
        return $pluginInfo ? $this->___callPlugins('validateFilter', func_get_args(), $pluginInfo) : parent::validateFilter($filter);
    }
}
