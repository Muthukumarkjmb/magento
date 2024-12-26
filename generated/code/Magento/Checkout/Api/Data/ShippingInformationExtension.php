<?php
namespace Magento\Checkout\Api\Data;

/**
 * Extension class for @see \Magento\Checkout\Api\Data\ShippingInformationInterface
 */
class ShippingInformationExtension extends \Magento\Framework\Api\AbstractSimpleObject implements ShippingInformationExtensionInterface
{
    /**
     * @return \Amasty\StorePickupWithLocator\Api\Data\QuoteInterface|null
     */
    public function getAmPickup()
    {
        return $this->_get('am_pickup');
    }

    /**
     * @param \Amasty\StorePickupWithLocator\Api\Data\QuoteInterface $amPickup
     * @return $this
     */
    public function setAmPickup(\Amasty\StorePickupWithLocator\Api\Data\QuoteInterface $amPickup)
    {
        $this->setData('am_pickup', $amPickup);
        return $this;
    }

    /**
     * @return integer|null
     */
    public function getFee()
    {
        return $this->_get('fee');
    }

    /**
     * @param integer $fee
     * @return $this
     */
    public function setFee($fee)
    {
        $this->setData('fee', $fee);
        return $this;
    }
}
