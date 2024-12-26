<?php
namespace Burstonline\Businesshours\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;

class Deliveryhour extends Template
{
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getOpenTime($business_type,$whichday)
    {
        $whichday = strtolower($whichday);
        if(strtolower($business_type) == 'delivery') 
        { 
            $business_type ="burstonline_businesshours/business_hours/";
        } 
        else 
        {  
            $whichday = $business_type.'_'.$whichday; 
            $business_type = 'burstonline_'.$business_type.'hours/'.$business_type.'_business_hours/';
        }
        
        if ($this->isDayEnabled($business_type,$whichday)) {
            return $this->convertTime($this->scopeConfig->getValue(
                $business_type.$whichday.'_open',
                ScopeInterface::SCOPE_STORE
            )) . ' - '. $this->convertTime($this->scopeConfig->getValue(
                $business_type.$whichday.'_close',
                ScopeInterface::SCOPE_STORE
            ));
        } else {
            return " Closed ";
        }
    }

    public function isDayEnabled($business_type, $whichday)
    {
        return $this->scopeConfig->getValue(
            $business_type.$whichday.'_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    function convertTime($timeString) {
        $timeString = str_replace(',', ':', $timeString);
        
        $time = \DateTime::createFromFormat('H:i:s', $timeString);
        
        return $time->format('g A');
    }
}
