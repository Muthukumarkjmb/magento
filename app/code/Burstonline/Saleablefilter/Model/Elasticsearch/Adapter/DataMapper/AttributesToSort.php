<?php

namespace Burstonline\Saleablefilter\Model\Elasticsearch\Adapter\DataMapper;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Burstonline\Saleablefilter\Model\ResourceModel\Inventory;

class AttributesToSort
{
    private $inventory;
    private $storeManager;

    public function __construct(
        Inventory $inventory,
        StoreManagerInterface $storeManager
    ) {
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
    }

    public function map($entityId, $storeId)
    {
        $attrToSort = [];
        $sku = $this->inventory->getSkuRelation((int)$entityId);
        if ($sku) {
            $websiteCode = $this->storeManager->getStore($storeId)->getWebsite()->getCode();
            $attrToSort['stock'] = $this->inventory->getStockStatus($sku, $websiteCode);
        } else {
            $attrToSort['stock'] = 1;
        }

        return $attrToSort;
    }
}