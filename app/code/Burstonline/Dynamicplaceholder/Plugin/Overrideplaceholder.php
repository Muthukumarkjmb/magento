<?php

namespace Burstonline\Dynamicplaceholder\Plugin;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class OverridePlaceholder
{
    protected $urlBuilder;
    protected $scopeConfig;
    protected $categoryCollectionFactory;
    protected $request;
    protected $logger;

    public function __construct(
        UrlInterface $urlBuilder,
        ScopeConfigInterface $scopeConfig,
        CategoryCollectionFactory $categoryCollectionFactory,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function aroundGetDefaultPlaceholderUrl(
        ImageHelper $subject,
        callable $proceed,
        $imageType = null
    ) {
        $this->logger->info('Dynamic placeholder plugin executed. :: 1');
        // Check if the dynamic placeholder is enabled
        $isEnabled = $this->scopeConfig->isSetFlag(
            'burstonline_dynamicplaceholder/burstonline_dynamicplaceholder/isenabled',
            ScopeInterface::SCOPE_STORE
        );

        if (!$isEnabled) {
            return $proceed($imageType); // Fallback to default if disabled
        }
        $this->logger->info('Dynamic placeholder plugin executed. :: 2');
        // Retrieve configured categories for which the custom placeholder should be applied
        $selectedCategories = $this->scopeConfig->getValue(
            'burstonline_dynamicplaceholder/burstonline_dynamicplaceholder/categories',
            ScopeInterface::SCOPE_STORE
        );
        $selectedCategories = explode(',', $selectedCategories);

        // Retrieve the custom placeholder image path
        $placeholderImagePath = $this->scopeConfig->getValue(
            'burstonline_dynamicplaceholder/burstonline_dynamicplaceholder/placeholder_image',
            ScopeInterface::SCOPE_STORE
        );

        if (!$placeholderImagePath) {
            return $proceed($imageType); // Fallback to default if no custom image set
        }
        $this->logger->info('Dynamic placeholder Observer executed. :: 3');
        // Get current category ID if available
        $currentCategoryId = $this->request->getParam('id');
        if ($currentCategoryId && in_array($currentCategoryId, $selectedCategories)) { $this->logger->info('Dynamic placeholder Observer executed. :: 4');
            // Construct the full URL to the custom placeholder image
            return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . 'burstonline/' . $placeholderImagePath;
        }
        $this->logger->info('Dynamic placeholder Observer executed. :: 5');
        // Fallback to the default placeholder if no matching category is found
        return $proceed($imageType);
    }
}
