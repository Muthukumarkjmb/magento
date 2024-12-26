<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Burstonline\Epipayment\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
class EpiLog extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('epi_log', 'id');
    }
}