<?php
namespace Contactus\ContactForm\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Submission extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('contact_form_submissions', 'id');
    }
}


