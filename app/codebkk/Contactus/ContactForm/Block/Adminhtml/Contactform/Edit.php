<?php

namespace Contactus\ContactForm\Block\Adminhtml\Contactform;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;

class Edit extends Template
{

     protected $_useFormKey = false;
    protected $coreRegistry;

    public function __construct(
        Template\Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    public function getContactData()
    {
        return $this->coreRegistry->registry('contact_data');
    }


    public function getSaveUrl()
{
    return $this->getUrl('contactus/contactform/save', ['id' => $this->getContactData()->getId()]);
}


}
