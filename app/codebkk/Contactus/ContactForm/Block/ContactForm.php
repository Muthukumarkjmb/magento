<?php

namespace Contactus\ContactForm\Block;

use Magento\Framework\View\Element\Template;

class ContactForm extends Template
{
    public function getFormAction()
    {
        return $this->getUrl('contactform/index/submit');
    }
}
