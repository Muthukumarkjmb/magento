<?php
namespace Burstonline\Businesshours\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class TimePicker extends Field
{
    public function render(AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat("HH:mm a"); //set date and time as per your need
        return parent::render($element);
    }
}
