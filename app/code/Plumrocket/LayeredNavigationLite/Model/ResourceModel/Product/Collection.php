<?php
/**
 * @package     Plumrocket_LayeredNavigationLite
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\LayeredNavigationLite\Model\ResourceModel\Product;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{

    /**
     * @var \Plumrocket\LayeredNavigationLite\Helper\Config
     */
    protected $config;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory                $entityFactory
     * @param \Psr\Log\LoggerInterface                                        $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface    $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                       $eventManager
     * @param \Magento\Eav\Model\Config                                       $eavConfig
     * @param \Magento\Framework\App\ResourceConnection                       $resource
     * @param \Magento\Eav\Model\EntityFactory                                $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper                     $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory                   $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Framework\Module\Manager                               $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State               $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface              $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory                    $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url                        $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface            $localeDate
     * @param \Magento\Customer\Model\Session                                 $customerSession
     * @param \Magento\Framework\Stdlib\DateTime                              $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface                  $groupManagement
     * @param \Magento\Search\Model\QueryFactory                              $catalogSearchData
     * @param \Magento\Framework\Search\Request\Builder                       $requestBuilder
     * @param \Magento\Search\Model\SearchEngine                              $searchEngine
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory
     * @param \Plumrocket\LayeredNavigationLite\Helper\Config                 $config
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null             $connection
     * @param                                                                 $searchRequestName
     * @param \Magento\Framework\Api\Search\SearchResultFactory|null          $searchResultFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Search\Model\QueryFactory $catalogSearchData,
        \Magento\Framework\Search\Request\Builder $requestBuilder,
        \Magento\Search\Model\SearchEngine $searchEngine,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Plumrocket\LayeredNavigationLite\Helper\Config $config,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        string $searchRequestName = 'catalog_view_container',
        \Magento\Framework\Api\Search\SearchResultFactory $searchResultFactory = null
    ) {
        $this->config = $config;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $catalogSearchData,
            $requestBuilder,
            $searchEngine,
            $temporaryStorageFactory,
            $connection,
            $searchRequestName,
            $searchResultFactory
        );
    }

    /**
     * @inheritDoc
     */
    public function getSize()
    {
        if (!$this->config->isModuleEnabled()) {
            return parent::getSize();
        }

        $sql = $this->getSelectCountSql();
        $this->_totalRecords = $this->getConnection()->fetchOne($sql, $this->_bindParams);
        return (int) $this->_totalRecords;
    }
}
