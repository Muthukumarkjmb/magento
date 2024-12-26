<?php
namespace Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal;

/**
 * Interceptor class for @see \Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal
 */
class Interceptor extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Decimal implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, $connectionName = null)
    {
        $this->___init();
        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    public function getMinMax(\Magento\Catalog\Model\Layer\Filter\FilterInterface $filter)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getMinMax');
        return $pluginInfo ? $this->___callPlugins('getMinMax', func_get_args(), $pluginInfo) : parent::getMinMax($filter);
    }
}
