<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created By : Rohan Hapani
 */
namespace Burstonline\Importproduct\Model\ResourceModel\Importlog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Burstonline\Importproduct\Model\Importlog', 'Burstonline\Importproduct\Model\ResourceModel\Importlog');
    }
}
