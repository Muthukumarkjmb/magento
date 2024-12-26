<?php
namespace Burstonline\RestrictAddToCart\Observer;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Burstonline\RestrictAddToCart\Helper\RestrictAddToCartHelper;
use Psr\Log\LoggerInterface;

class RestrictAddToCartObserver implements ObserverInterface
{
    protected $productRepository;
    protected $helper;
    protected $logger;
    protected $messageManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        RestrictAddToCartHelper $helper,
        LoggerInterface $logger,
        ManagerInterface $messageManager // Inject MessageManagerInterface
    ) {
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        $items = $observer->getEvent()->getQuoteItem();
        
        // Fallback to all items if the direct event quote item is empty
        if (empty($items)) {
            $items = $observer->getEvent()->getCart()->getQuote()->getAllItems();
        }
        
        foreach ($items as $quoteItem) {
            if ($quoteItem) {
                $product = $quoteItem->getProduct();
                $quote = $observer->getEvent()->getCart()->getQuote();
                $qty = $quoteItem->getQty();  // Current cart quantity for the product

                // Fetching the product from the repository
                $product = $this->productRepository->getById($product->getId());

                // Use requestInfo for requested quantity (assuming it's passed in the event)
                $requestInfo = ['qty' => $qty];

                // Call the helper to validate custom limits
                $customLimit = $this->helper->validateProductLimit($quote, $product, $requestInfo, "fromobserver");

                if (!empty($customLimit)) {
                    throw new LocalizedException(__("You can only add up to %1 of this product to the cart.", $customLimit));
                }
            }
        }
    }
}
