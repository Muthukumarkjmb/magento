<?php

namespace Burstonline\Importproduct\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Importlog extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('burstonline_import_log', 'log_id');
    }
}
