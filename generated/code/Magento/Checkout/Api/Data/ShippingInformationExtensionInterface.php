<?php
namespace Magento\Checkout\Api\Data;

/**
 * ExtensionInterface class for @see \Magento\Checkout\Api\Data\ShippingInformationInterface
 */
interface ShippingInformationExtensionInterface extends \Magento\Framework\Api\ExtensionAttributesInterface
{
    /**
     * @return \Amasty\StorePickupWithLocator\Api\Data\QuoteInterface|null
     */
    public function getAmPickup();

    /**
     * @param \Amasty\StorePickupWithLocator\Api\Data\QuoteInterface $amPickup
     * @return $this
     */
    public function setAmPickup(\Amasty\StorePickupWithLocator\Api\Data\QuoteInterface $amPickup);

    /**
     * @return integer|null
     */
    public function getFee();

    /**
     * @param integer $fee
     * @return $this
     */
    public function setFee($fee);
}
