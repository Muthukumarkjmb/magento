<?php
namespace Burstonline\Epipayment\Model\ResourceModel\EpiLog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
  protected $_idFieldName = 'id';
  protected $_eventPrefix = 'epi_log_collection';
  protected $_eventObject = 'epi_log_collection';

  /**
   * Define resource model
   *
   * @return void
   */
  protected function _construct()
  {
    $this->_init('Burstonline\Epipayment\Model\EpiLog', 'Burstonline\Epipayment\Model\ResourceModel\EpiLog');
  }

}