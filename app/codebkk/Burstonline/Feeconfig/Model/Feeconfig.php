<?php

namespace Burstonline\Feeconfig\Model;

use Magento\Framework\Model\AbstractModel;

class Feeconfig extends \Magento\Framework\Model\AbstractModel {

	
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'feeconfig';
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [])
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct() {
        $this->_init(\Burstonline\Feeconfig\Model\ResourceModel\Feeconfig::class);
    }
}
