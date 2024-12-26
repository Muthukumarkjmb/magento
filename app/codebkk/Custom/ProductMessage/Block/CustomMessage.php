<?php

namespace Custom\ProductMessage\Block;

use Magento\Framework\View\Element\Template;

class CustomMessage extends Template
{
    public function getCustomMessage()
    {
        return __('This is a custom message from the ProductMessage module!');
    }
}
