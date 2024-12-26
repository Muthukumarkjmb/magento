<?php
namespace Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider;

/**
 * Interceptor class for @see \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider
 */
class Interceptor extends \Magento\CatalogSearch\Model\Layer\Category\ItemCollectionProvider implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($collectionFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCollection');
        return $pluginInfo ? $this->___callPlugins('getCollection', func_get_args(), $pluginInfo) : parent::getCollection($category);
    }
}
