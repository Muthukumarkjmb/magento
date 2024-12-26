<?php

namespace Burstonline\Saleablefilter\Plugin;

class Config
{
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        $newOptions = ['stock' => __('In Stock')];
        $options = array_merge($options, $newOptions);
        return $options;
    }
}