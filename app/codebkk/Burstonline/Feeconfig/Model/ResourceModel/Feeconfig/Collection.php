<?php
namespace Burstonline\Feeconfig\Model\ResourceModel\Feeconfig;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'feeconfig_collection';
	protected $_eventObject = 'feeconfig_collection';

	/**
	* Define resource model
	*
	* @return void
	*/
	protected function _construct()
	{
		$this->_init('Burstonline\Feeconfig\Model\Feeconfig', 'Burstonline\Feeconfig\Model\ResourceModel\Feeconfig');
		
	}
	
	public function addStatusFilter()
    {
        return $this->addFieldToFilter('enabled', 1);
    }
    
    public function addStoreFilter($store, $withAdmin = true)
    {
		//$this->addFieldToFilter('store', ['in' => [0,1]]);
		return $this;
	}
	
	public function prepareForList($page)
    {
        //Set collection page size
        $this->setPageSize(8);
        //Set current page
        $this->setCurPage($page);
        
        return $this;
    }
}
