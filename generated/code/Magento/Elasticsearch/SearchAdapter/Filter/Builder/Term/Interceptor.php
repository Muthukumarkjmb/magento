<?php
namespace Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;

/**
 * Interceptor class for @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term
 */
class Interceptor extends \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface $fieldMapper, \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeProvider $attributeAdapterProvider, array $integerTypeAttributes = [])
    {
        $this->___init();
        parent::__construct($fieldMapper, $attributeAdapterProvider, $integerTypeAttributes);
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilter(\Magento\Framework\Search\Request\FilterInterface $filter)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'buildFilter');
        return $pluginInfo ? $this->___callPlugins('buildFilter', func_get_args(), $pluginInfo) : parent::buildFilter($filter);
    }
}
