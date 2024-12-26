<?php

namespace Burstonline\Customconfig\Model\Config\Source;

class Pricetype implements \Magento\Framework\Data\OptionSourceInterface
{
    
    const FIXED = 1;

    const PERCENTAGE = 0;
	
	public function toOptionArray(){
	  return [
		['value' => 'percentage', 'label' => __('% (Percentage)')],
		['value' => 'fixed', 'label' => __('$ (Fixed)')],
	  ];
	 }
}
