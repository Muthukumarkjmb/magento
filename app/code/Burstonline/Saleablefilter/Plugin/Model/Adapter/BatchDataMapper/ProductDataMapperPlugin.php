<?php

namespace Burstonline\Saleablefilter\Plugin\Model\Adapter\BatchDataMapper;

use Magento\Elasticsearch\Model\Adapter\BatchDataMapper\ProductDataMapper;
use Burstonline\Saleablefilter\Model\Elasticsearch\Adapter\DataMapper\AttributesToSort as AttrDataMapper;
use Burstonline\Saleablefilter\Model\ResourceModel\Inventory;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductDataMapperPlugin
{
    protected $attrDataMapper;
    protected $inventory;

    public function __construct(AttrDataMapper $attrDataMapper, Inventory $inventory)
    {
        $this->attrDataMapper = $attrDataMapper;
        $this->inventory = $inventory;
    }

    public function afterMap(
        ProductDataMapper $subject,
        $documents,
        $documentData,
        $storeId,
        $context
    ) {
        $this->inventory->saveRelation(array_keys($documents));

        foreach ($documents as $productId => $document) {
            //@codingStandardsIgnoreLine
            $document = array_merge($document, $this->attrDataMapper->map($productId, $storeId));
            $documents[$productId] = $document;
        }

        $this->inventory->clearRelation();

        return $documents;
    }
}