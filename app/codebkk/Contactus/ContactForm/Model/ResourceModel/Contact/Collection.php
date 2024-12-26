<?php

namespace Contactus\ContactForm\Model\ResourceModel\Contact;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \Contactus\ContactForm\Model\Contact::class,
            \Contactus\ContactForm\Model\ResourceModel\Contact::class
        );
    }
}
