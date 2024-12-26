<?php
namespace Epi\FulfillmentApi\Api\Data;

/**
 * Interface which represents associative array item.
 */
interface OrderItemsArrayInterface
{
    /**
     * Get key
     * 
     * @return string
     */
    public function getName();

    /**
     * Set key
     * 
     * @return $this
     */
    public function setName($name);

    /**
     * Get value
     * 
     * @return int
     */
    public function getQtyOrdered();

    /**
     * Set value
     * 
     * @return $this
     */
    public function setQtyOrdered($qty);

    /**
     * Get value
     * 
     * @return float
     */
    public function getPriceInclTax();

    /**
     * Set value
     * 
     * @return $this
     */
    public function setPriceInclTax($price);
}