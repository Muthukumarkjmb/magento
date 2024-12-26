<?php

namespace Contactus\ContactForm\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Contact extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('contact_form_submissions', 'id'); // Table name and primary key
    }
}
