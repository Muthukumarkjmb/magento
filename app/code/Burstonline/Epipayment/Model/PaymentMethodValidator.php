<?php
namespace Burstonline\Epipayment\Model;

use Magento\Quote\Model\Quote;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Checks\SpecificationInterface;

class PaymentMethodValidator implements SpecificationInterface
{
    protected $customconfig;
    
	public function __construct(
	 \Burstonline\Customconfig\Block\Customconfig $customconfig
	) {
	 $this->customconfig = $customconfig;
	}
    public function isApplicable(MethodInterface $paymentMethod, Quote $quote)
    {
        $total = $quote->getBaseGrandTotal();
        $orderConfig=$this->customconfig->getOrderConfig();
		
		if($orderConfig['enabled']==1){
			if (!empty($orderConfig['minTotal']) && $total < $orderConfig['minTotal']) {
				return "min";
			}
			if (!empty($orderConfig['maxTotal']) && $total > $orderConfig['maxTotal']) {
				return "max";
			}
		}
        return false;
    }
}
?>
