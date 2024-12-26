<?php

namespace Burstonline\Dynamicplaceholder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;

class SetDynamicplaceholder implements ObserverInterface
{
    protected $scopeConfig;
    protected $categoryRepository;
    protected $logger;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CategoryRepository $categoryRepository,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('Dynamic placeholder Observer executed. :: 1');
        // Check if the dynamic placeholder is enabled
        $isEnabled = $this->scopeConfig->isSetFlag(
            'burstonline_dynamicplaceholder/burstonline_dynamicplaceholder/isenabled',
            ScopeInterface::SCOPE_STORE
        );
        $this->logger->info('Dynamic placeholder Observer executed. :: 2');
        if (!$isEnabled) {
            return;
        }
        $this->logger->info('Dynamic placeholder Observer executed. :: 3');
        // Get selected categories and placeholder image path
        $selectedCategories = $this->scopeConfig->getValue(
            'burstonline_dynamicplaceholder/burstonline_dynamicplaceholder/categories',
            ScopeInterface::SCOPE_STORE
        );
        $placeholderImagePath = $this->scopeConfig->getValue(
            'burstonline_dynamicplaceholder/burstonline_dynamicplaceholder/placeholder_image',
            ScopeInterface::SCOPE_STORE
        );
        $this->logger->info('Dynamic placeholder Observer executed. :: 4');
        // Convert selected categories to an array
        $selectedCategories = explode(',', $selectedCategories);

        // Retrieve the product collection from the observer
        $productCollection = $observer->getEvent()->getData('collection');
        $this->logger->info('Dynamic placeholder Observer executed. :: 5');
        foreach ($productCollection as $product) {
            $this->logger->info('Dynamic placeholder Observer executed. :: 6');
            $categoryIds = $product->getCategoryIds();

            // Check if any category ID of the product matches the selected categories
            foreach ($categoryIds as $categoryId) {
                $this->logger->info('Dynamic placeholder Observer executed. :: 7');
                if (in_array($categoryId, $selectedCategories)) {
                    $this->logger->info('Dynamic placeholder Observer executed. :: 8');
                    // Apply the custom placeholder image if it exists
                    if ($placeholderImagePath) {
                        $this->logger->info('Dynamic placeholder Observer executed. :: 9');
                        $product->setData('media_gallery', [
                            'images' => [
                                [
                                    'file' => 'burstonline/default/no_image_available.jpg',
                                    'position' => 1,
                                    'disabled' => false,
                                    'label' => 'Custom Placeholder'
                                ]
                            ]
                        ]);
                    }
                    break; // Stop checking further categories once a match is found
                }
            }
        }
    }
}
