<?php

namespace Burstonline\Feeconfig\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Feeconfig extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('burstonline_feesconfiguration', 'id');
    }
}
