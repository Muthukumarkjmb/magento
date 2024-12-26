<?php
namespace Burstonline\Customconfig\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Quote\Model\Quote;

class ExtrafeeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Burstonline\Customconfig\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $taxHelper;

    /**
     * @param \Burstonline\Customconfig\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Burstonline\Customconfig\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Burstonline\Customconfig\Helper\Tax $helperTax

    )
    {
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->taxHelper = $helperTax;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $ExtrafeeConfig = [];
        $enabled = $this->dataHelper->isModuleEnabled();
        //$minimumOrderAmount = $this->dataHelper->getMinimumOrderAmount();
        $feeLabels = explode('|', $this->dataHelper->getFeeLabel());
        $ExtrafeeConfig['fee_labels'] = $feeLabels;
        $ExtrafeeConfig['fee_label'] = $this->dataHelper->getFeeLabel();
        
        $quote = $this->checkoutSession->getQuote();
        $subtotal = $quote->getSubtotal();

        $feeAmounts = explode('|', $this->dataHelper->getExtrafee());
        $ExtrafeeConfig['custom_fee_amounts'] = $feeAmounts;

        $ExtrafeeConfig['fee_details'] = array_combine($feeLabels, $feeAmounts);


        $ExtrafeeConfig['custom_fee_amount'] = $this->dataHelper->getSumExtrafee();
        if ($this->taxHelper->isTaxEnabled() && $this->taxHelper->displayInclTax()) {
            $address = $this->_getAddressFromQuote($quote);
            $ExtrafeeConfig['custom_fee_amount'] = $this->dataHelper->getSumExtrafee() + $address->getFeeTax();
        }
        if ($this->taxHelper->isTaxEnabled() && $this->taxHelper->displayBothTax()) {

            $address = $this->_getAddressFromQuote($quote);
            $ExtrafeeConfig['custom_fee_amount'] = $this->dataHelper->getSumExtrafee();
            $ExtrafeeConfig['custom_fee_amount_inc'] = $this->dataHelper->getSumExtrafee() + $address->getFeeTax();

        }
        $ExtrafeeConfig['displayInclTax'] = $this->taxHelper->displayInclTax();
        $ExtrafeeConfig['displayExclTax'] = $this->taxHelper->displayExclTax();
        $ExtrafeeConfig['displayBoth'] = $this->taxHelper->displayBothTax();
        $ExtrafeeConfig['exclTaxPostfix'] = __('Excl. Tax');
        $ExtrafeeConfig['inclTaxPostfix'] = __('Incl. Tax');
        $ExtrafeeConfig['TaxEnabled'] = $this->taxHelper->isTaxEnabled();
        $ExtrafeeConfig['show_hide_Extrafee_block'] = ($enabled && $quote->getFee()) ? true : false;
        $ExtrafeeConfig['show_hide_Extrafee_shipblock'] = ($enabled ) ? true : false;
        return $ExtrafeeConfig;
    }

    protected function _getAddressFromQuote(Quote $quote)
    {
        return $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
    }
}
