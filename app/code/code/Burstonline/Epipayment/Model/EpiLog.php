<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Burstonline\Epipayment\Model;
use Magento\Framework\Model\AbstractModel;

class EpiLog extends \Magento\Framework\Model\AbstractModel {

    protected function _construct() {
        $this->_init('Burstonline\Epipayment\Model\ResourceModel\EpiLog');
    }
}