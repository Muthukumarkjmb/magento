<?php
namespace Burstonline\Saleablefilter\Model\ResourceModel;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Module\Manager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Inventory extends AbstractDb
{
    private $moduleManager;
    private $stockRegistry;
    private $productRepository;
    private $layout;
    private $scopeConfig;
    private $stockIds = [];
    private $skuRelations = [];

    public function __construct(
        Manager $moduleManager,
        StockRegistryInterface $stockRegistry,
        ProductRepositoryInterface $productRepository,
        LayoutInterface $layout,
        ScopeConfigInterface $scopeConfig,
        Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->layout = $layout;
        $this->scopeConfig = $scopeConfig;
    }

    protected function _construct()
    {
        $this->_init('custom_inventory_table', 'entity_id');  // Replace with your actual table and primary key if applicable
    }

    public function getStockStatus($productSku, $websiteCode): int
    {
        $stockStatus = 1;

        try {
            // Load product by SKU
            $product = $this->productRepository->get($productSku);
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            $stockQty = $stockItem ? $stockItem->getQty() : null;

            // Get custom block and config
            $blockObj = $this->layout->createBlock('Burstonline\Customconfig\Block\Customconfig');
            $priceConfig = $blockObj ? $blockObj->getSaleLimit() : ['enabled' => 0, 'saleLimit' => 0];

            // Get custom attributes
            $isohlq = $product->getCustomAttribute('isohlq') ? $product->getCustomAttribute('isohlq')->getValue() : 0;
            $bottleIcon = $product->getCustomAttribute('bottleicon') ? $product->getCustomAttribute('bottleicon')->getValue() : null;

            // Get low-level quantity from config
            $storeScope = ScopeInterface::SCOPE_STORE;
            $lowLevelQty = $this->scopeConfig->getValue('burstonline_customconfig/product_price_config/low_level_qty', $storeScope);

            // Determine stock availability status
            if ($isohlq) {
                $stock_out_status = $bottleIcon <= -1 ? "Out of Stock" :
                                    ($bottleIcon == 0 ? "Low Stock" : "In Stock");
            } else {
                $stock_out_status = is_null($stockQty) ? "Not available for sale online" :
                                    ($stockQty <= 0 ? "Out of Stock" :
                                    ($stockQty > 0 && $stockQty <= $lowLevelQty ? "Low Stock" : "In Stock"));
            }

            // Determine final stock status based on price and custom config
            if ($priceConfig['enabled'] && $product->getPrice() > $priceConfig['saleLimit']) {
                $stockStatus = 1;
            } elseif ($product->isSaleable() && $stock_out_status != "Out of Stock") {
                $stockStatus = 0;
            }
        } catch (NoSuchEntityException $e) {
            // Log the exception or handle it accordingly
            $stockStatus = 1;
        }

        return (int)$stockStatus;
    }

    public function getStockId(string $websiteCode)
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int)$this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    public function saveRelation(array $entityIds): Inventory
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('catalog_product_entity'),
            ['entity_id', 'sku']
        )->where('entity_id IN (?)', $entityIds);

        $this->skuRelations = $this->getConnection()->fetchPairs($select);

        return $this;
    }

    public function clearRelation()
    {
        $this->skuRelations = null;
    }
    
    public function getSkuRelation(int $entityId): string
    {
        return $this->skuRelations[$entityId] ?? '';
    }
}
