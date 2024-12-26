<?php

namespace welcome\SimpleModule\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    public function execute()
    {
        echo 'Hello from SimpleModule!';
        exit;
    }
}
