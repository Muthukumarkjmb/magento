<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Pickup with Locator for Magento 2
 */

namespace Amasty\StorePickupWithLocator\Model\Location;

class FilterIntersectLocations
{
    /**
     * @param array $productIdentifiers
     * @param array $productsWithLocations
     * @return array
     */
    public function filter(array $productIdentifiers, array $productsWithLocations): array
    {
        $intersectLocationIds = null;
        foreach ($productIdentifiers as $productIdentifier) {
            $productLocationIds = $productsWithLocations[$productIdentifier] ?? [];
            if ($intersectLocationIds === null) {
                $intersectLocationIds = $productLocationIds;
                continue;
            }

            $intersectLocationIds = array_intersect($intersectLocationIds, $productLocationIds);

            if (!$intersectLocationIds) {
                break;
            }
        }

        return $intersectLocationIds === null ? [] : $this->filterIds($intersectLocationIds);
    }

    /**
     * @param string[] $ids
     * @return int[]
     */
    private function filterIds(array $ids): array
    {
        $ids = array_values($ids);

        foreach ($ids as &$id) {
            $id = (int)$id;
        }

        return $ids;
    }
}
