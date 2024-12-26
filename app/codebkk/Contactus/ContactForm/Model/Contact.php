<?php

namespace Contactus\ContactForm\Model;

use Magento\Framework\Model\AbstractModel;

class Contact extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Contactus\ContactForm\Model\ResourceModel\Contact::class);
    }
}
