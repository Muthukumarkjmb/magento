<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Pickup with Locator for Magento 2
 */

namespace Amasty\StorePickupWithLocator\Model\Location\Filter;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\StorePickupWithLocator\Api\Filter\LocationProductFilterInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class NoConditionFilter implements LocationProductFilterInterface
{
    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $productIds
     * @param int $storeId
     */
    public function apply(SearchCriteriaBuilder $searchCriteriaBuilder, array $productIds, int $storeId): void
    {
        $searchCriteriaBuilder->addFilter(LocationInterface::CONDITION_TYPE, ConditionType::NO_CONDITIONS);
    }
}
