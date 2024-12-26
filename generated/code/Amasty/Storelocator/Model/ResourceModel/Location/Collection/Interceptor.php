<?php
namespace Amasty\Storelocator\Model\ResourceModel\Location\Collection;

/**
 * Interceptor class for @see \Amasty\Storelocator\Model\ResourceModel\Location\Collection
 */
class Interceptor extends \Amasty\Storelocator\Model\ResourceModel\Location\Collection implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\App\RequestInterface $request, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ScopeConfigInterface $scope, \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest, \Magento\Catalog\Api\ProductRepositoryInterface $productRepository, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository, \Amasty\Base\Model\Serializer $serializer, \Amasty\Geoip\Model\Geolocation $geolocation, \Amasty\Storelocator\Model\ConfigProvider $configProvider, \Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface $locationProductValidator, ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null, ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null, ?\Amasty\Storelocator\Model\DataCollector\Location\CompositeCollector $compositeCollector = null)
    {
        $this->___init();
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $storeManager, $request, $registry, $scope, $httpRequest, $productRepository, $categoryRepository, $serializer, $geolocation, $configProvider, $locationProductValidator, $connection, $resource, $compositeCollector);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLocationsByProduct($productId, $storeIds = 0)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'filterLocationsByProduct');
        return $pluginInfo ? $this->___callPlugins('filterLocationsByProduct', func_get_args(), $pluginInfo) : parent::filterLocationsByProduct($productId, $storeIds);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLocationsByCategory($categoryId, $storeIds = 0)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'filterLocationsByCategory');
        return $pluginInfo ? $this->___callPlugins('filterLocationsByCategory', func_get_args(), $pluginInfo) : parent::filterLocationsByCategory($categoryId, $storeIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurPage($displacement = 0)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCurPage');
        return $pluginInfo ? $this->___callPlugins('getCurPage', func_get_args(), $pluginInfo) : parent::getCurPage($displacement);
    }
}
