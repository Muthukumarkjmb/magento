<?php

namespace Burstonline\Disablecheckout\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Disablejs extends Template
{

     public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
   
}
