<?php

namespace Burstonline\RestrictAddToCart\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;

class RestrictAddToCartHelper
{
    protected $productRepository;
    protected $logger;
    protected $messageManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        ManagerInterface $messageManager
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * Validates the product limit for the cart or quote.
     *
     * @param Quote $quote
     * @param Product $product
     * @param array|int $requestInfo
     * @throws LocalizedException
     */
    public function validateProductLimit($quote, $product, $requestInfo, $whereFrom)
    {
       // Custom limit validation logic
        $customLimit = $product->getCustomAttribute('bottlelimit') ? $product->getCustomAttribute('bottlelimit')->getValue() : null;
        $this->logger->critical(
            __('Product custom limit is %1', $customLimit)
        );
        if ($customLimit) {
            $quoteItems = $quote->getAllItems();
            $qtyInCart = 0;
            $this->logger->info('Product custom limit check ');
            foreach ($quoteItems as $item) {
                if ($item->getProductId() == $product->getId()) {
                    $qtyInCart += $item->getQty();
                }
            }
            $requestedQty = is_array($requestInfo) ? ($requestInfo['qty'] ?? 1) : 1;
            if($whereFrom ==  "fromplugin")
            {
                if ($qtyInCart + $requestedQty > $customLimit) {
                    $this->logger->critical(
                        __('Product %1 exceeds the custom limit of %2', $product->getId(), $customLimit)
                    );
                    return $customLimit;
                }
            }
            if($whereFrom ==  "fromobserver")
            {
                if ($customLimit > 0 && ($requestedQty > $customLimit)) {
                    $this->logger->critical(
                        __('Product %1 exceeds the custom limit of %2', $product->getId(), $customLimit)
                    );
                    return $customLimit; 
                }
            }
        }

        return false;
    }
}
