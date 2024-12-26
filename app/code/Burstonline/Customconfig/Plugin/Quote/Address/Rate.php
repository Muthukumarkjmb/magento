<?php
namespace Burstonline\Customconfig\Plugin\Quote\Address;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
class Rate
{
	protected $customconfig;
	protected $dataHelper;
	protected $logger;
	 public function __construct(
	 \Burstonline\Customconfig\Block\Customconfig $customconfig,
	 \Burstonline\Customconfig\Helper\Data $dataHelper,
	 LoggerInterface $logger,
	)
    {
        $this->customconfig = $customconfig;
		$this->dataHelper = $dataHelper;
		$this->logger = $logger;
    }
    
    public function afterImportShippingRate(\Magento\Quote\Model\Quote\Address\Rate $subject, $result, $rate){
		
		$extraFee=0;
		$nonLmpConfig=$this->customconfig->getNonLmpConfig();
		if($nonLmpConfig['enabled']==1){
			if($nonLmpConfig['price_type']=='percentage'){ $extraFee = $nonLmpConfig['subTotal'] * ($this->dataHelper->getSumExtrafee()/100); }
			if($nonLmpConfig['price_type']=='fixed'){ $extraFee = $this->dataHelper->getSumExtrafee(); }
		}
		
		if ($rate instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
			if($result->getCode() == 'flatrate_flatrate') {
				$result->setCode('flatrate_flatrate')->setPrice($result->getPrice() + $extraFee);
			   
			}
		}
		return $result;
	}
}
