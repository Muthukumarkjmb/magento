<?php

namespace Contactus\ContactForm\Block\Adminhtml\Index;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;
use Magento\Framework\App\ResourceConnection;

class Grid extends Template
{
    protected $_resourceConnection;
    protected $_formKey;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        \Magento\Framework\Data\Form\FormKey $formKey,
    ) {
        $this->_formKey = $formKey;
        parent::__construct($context);
        $this->_resourceConnection = $resourceConnection;
    }

    public function getTableData()
    {
        $connection = $this->_resourceConnection->getConnection();
        $tableName = $this->_resourceConnection->getTableName('contact_form_submissions');
        $select = $connection->select()->from($tableName);

        return $connection->fetchAll($select);
    }

    public function getFormKey()
{
    return $this->_formKey->getFormKey();
}


}
