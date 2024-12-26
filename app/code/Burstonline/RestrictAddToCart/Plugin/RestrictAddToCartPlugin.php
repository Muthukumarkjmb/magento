<?php
namespace Burstonline\RestrictAddToCart\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;
use Burstonline\RestrictAddToCart\Helper\RestrictAddToCartHelper;

class RestrictAddToCartPlugin
{
    protected $productRepository;
    protected $helper;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RestrictAddToCartHelper $helper,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function aroundAddProduct(Cart $subject, \Closure $proceed, $productInfo, $requestInfo = null)
    {
        $product = $this->getProduct($productInfo);
        $this->validateProductLimit($subject->getQuote(), $product, $requestInfo);
        return $proceed($productInfo, $requestInfo);
    }

    public function aroundUpdateItems(Cart $subject, \Closure $proceed, array $data)
    {
        $this->logger->info('Starting aroundUpdateItems', ['data' => $data]);
        foreach ($data as $itemId => $itemInfo) {
            $quoteItem = $subject->getQuote()->getItemById($itemId);
            if ($quoteItem) {
                $product = $quoteItem->getProduct();
                $this->logger->info('Product info before validation', ['product' => $product->getData()]);
                $this->validateProductLimit($subject->getQuote(), $product, $itemInfo);
            }
        }

        return $proceed($data);
    }

    public function aroundUpdateItem($subject, \Closure $proceed, $itemId, $qty)
    {
        //$this->logger->info('aroundUpdateItem called for item ID ' . $itemId . ' with qty ' . $qty);
        if (!$subject instanceof \Magento\Quote\Model\Quote\Item) {
            return $proceed($itemId, $qty);
        }

        // Continue with your custom logic
        $product = $subject->getProduct();
        $this->validateProductLimit($subject->getQuote(), $product, ['qty' => $qty]);

        return $proceed($qty);
    }

    protected function validateProductLimit($quote, Product $product, $requestInfo = null)
    {
        $customLimit = $this->helper->validateProductLimit($quote, $product, $requestInfo, "fromplugin");
        if(!empty($customLimit)){
            throw new LocalizedException(__("You can only add up to %1 of this product to the cart.", $customLimit));
        }        
    }

    protected function getProduct($productInfo)
    {
        if ($productInfo instanceof Product) {
            return $productInfo;
        }

        if (is_numeric($productInfo)) {
            return $this->productRepository->getById($productInfo);
        }

        if (is_string($productInfo)) {
            return $this->productRepository->get($productInfo);
        }

        throw new LocalizedException(__('Invalid product identifier.'));
    }
}
