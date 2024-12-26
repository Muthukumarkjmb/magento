<?php

namespace Burstonline\Categoryhome\Block;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface; // Import the PriceCurrencyInterface
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Product;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as ReviewSummaryCollectionFactory;

class FeaturedProducts extends Template
{
    protected $categoryRepository;
    protected $productCollectionFactory;
    protected $imageHelper;
    protected $reviewFactory;
    protected $priceCurrency;
    protected $scopeConfig;
    protected $storeManager;
    protected $reviewSummaryCollectionFactory;

    public function __construct(
        Template\Context $context,
        CategoryRepository $categoryRepository,
        CollectionFactory $productCollectionFactory,
        ImageHelper $imageHelper,
        ReviewFactory $reviewFactory,
        PriceCurrencyInterface $priceCurrency,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ReviewSummaryCollectionFactory $reviewSummaryCollectionFactory,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->reviewFactory = $reviewFactory;
        $this->priceCurrency = $priceCurrency;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->reviewSummaryCollectionFactory = $reviewSummaryCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getFeaturedProducts()
    {
        try {
            $categoryId = $this->scopeConfig->getValue('burstonline_categoryhome/general/categories', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (!$categoryId) {
                return null;
            }

            $total_products_count = $this->scopeConfig->getValue('burstonline_categoryhome/general/total_products_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            if (!$total_products_count) {
                $total_products_count = 15;
            }

            $category = $this->categoryRepository->get($categoryId);
            $categoryName = $category->getName(); // Get category name

            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addCategoryFilter($category);
            $collection->addAttributeToFilter(
					array(
						array('attribute' => 'image','neq' => 'no_selection'),
						array('attribute' => 'small_image','neq' => 'no_selection'),
						array('attribute' => 'thumbnail','neq' => 'no_selection'),
					)
				);
                $collection->getSelect()->join(
                    ['stock_status' => 'cataloginventory_stock_status'],
                    'e.entity_id = stock_status.product_id AND stock_status.stock_status = 1',
                    []
                );
            $collection->getSelect()->order('RAND()');
            $collection->setPageSize($total_products_count);
            $collection->load();

            $allProducts = $collection->getItems();

            return [
                'categoryName' => $categoryName,
                'collection' => $collection,
                'products' => $allProducts,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    public function getReviewsAndAverageRating(Product $product)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $productId = $product->getId();

        // Fetch the review collection
        $reviewCollection = $this->reviewFactory->create()->getCollection()
            ->addStoreFilter($storeId)
            ->addEntityFilter('product', $productId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED);

        $reviewCount = $reviewCollection->getSize();
        $averageRating = 0;

        // Fetch review summary for the product
        $reviewSummaryCollection = $this->reviewSummaryCollectionFactory->create()
            ->addFieldToFilter('entity_pk_value', $productId)
            ->addFieldToFilter('store_id', $storeId);

        if ($reviewSummaryCollection->getSize() > 0) {
            $totalRating = 0;
            $totalCount = 0;

            foreach ($reviewSummaryCollection as $summary) {
                $totalRating += $summary->getRatingSummary() * $summary->getReviewsCount();
                $totalCount += $summary->getReviewsCount();
            }

            if ($totalCount > 0) {
                $averageRating = $totalRating / $totalCount / 20; // Convert percentage to rating out of 5
            }
        }

        return [
            'reviews_count' => $reviewCount,
            'average_rating' => round($averageRating, 1) // Round to one decimal place
        ];
    }

    public function getDefaultCurrencySymbol()
    {
        // Get default currency symbol
        return $this->priceCurrency->getCurrencySymbol();
    }

    public function getBottombanner()
    {
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $configPath = 'burstonline_customhome/general/bottom_image';
        $configPathDes = 'burstonline_customhome/general/bottom_img_description';

        $images['img'] =  $mediaUrl . "customhome/" . $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $images['des'] =  $this->scopeConfig->getValue(
            $configPathDes,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $images;
    }

    public function getProductCount()
    {
        $productCount = $this->scopeConfig->getValue('burstonline_categoryhome/general/product_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$productCount) {
            $productCount = 4;
        }
        return $productCount;
    }
}
