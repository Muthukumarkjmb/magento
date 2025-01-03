<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Model\ResourceModel\Options;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Amasty\Storelocator\Model\Options::class,
            \Amasty\Storelocator\Model\ResourceModel\Options::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
