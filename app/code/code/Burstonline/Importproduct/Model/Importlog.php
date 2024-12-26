<?php

namespace Burstonline\Importproduct\Model;

class Importlog extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Burstonline\Importproduct\Model\ResourceModel\Importlog');
    }
}
