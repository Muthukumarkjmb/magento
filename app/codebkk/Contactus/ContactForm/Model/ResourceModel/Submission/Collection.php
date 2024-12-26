<?php
namespace Contactus\ContactForm\Model\ResourceModel\Submission;

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
        $this->_init('Contactus\ContactForm\Model\Submission', 'Contactus\ContactForm\Model\ResourceModel\Submission');
        
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
