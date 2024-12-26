<?php

namespace Epi\ShippingRestriction\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;

class UsStates implements ArrayInterface
{
    protected $regionCollectionFactory;

    public function __construct(RegionCollectionFactory $regionCollectionFactory)
    {
        $this->regionCollectionFactory = $regionCollectionFactory;
    }

    public function toOptionArray()
    {
        $options = [];
        $regions = $this->regionCollectionFactory->create()->addCountryFilter('US')->load();
        
        foreach ($regions as $region) {
            $options[] = [
                'value' => $region->getDefaultName(),
                'label' => $region->getDefaultName(),
            ];
        }

        return $options;
    }
}
