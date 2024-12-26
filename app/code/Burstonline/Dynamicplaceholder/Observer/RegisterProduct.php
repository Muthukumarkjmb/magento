<?php

namespace Burstonline\Dynamicplaceholder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class RegisterProduct implements ObserverInterface
{
    protected $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function execute(Observer $observer)
    {
        // Register the product for global access
        $product = $observer->getProduct();
        $this->registry->register('current_product', $product, true);
    }
}
