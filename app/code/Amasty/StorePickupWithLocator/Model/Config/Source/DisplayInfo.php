<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Pickup with Locator for Magento 2
 */

namespace Amasty\StorePickupWithLocator\Model\Config\Source;

class DisplayInfo implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Value From Config For Shipping Address Area
     */
    public const SHIPPING_ADDRESS_AREA = 1;

    /**
     * Value From Config For Shipping Method Area
     */
    public const SHIPPING_METHOD_AREA = 0;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SHIPPING_ADDRESS_AREA, 'label' => __('Shipping Address Area')],
            ['value' => self::SHIPPING_METHOD_AREA, 'label' => __('Shipping Methods Area')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SHIPPING_METHOD_AREA => __('Shipping Methods Area'),
            self::SHIPPING_ADDRESS_AREA => __('Shipping Address Area')
        ];
    }
}
