<?php

namespace Burstonline\Checkoutdisclaimer\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Checkout\Model\Session;

class Disclaimer extends Template
{
	protected $_session;
	protected $cart;
	protected $product;
    protected $categoryRepository;
     public function __construct(
        Context $context,
        Session $session,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Cart $cart,
        array $data = []
    ) {
		$this->_session = $session;
		$this->cart = $cart;
		$this->product = $product;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }
    public function getContent() 
    {
        return 'Dummy content';
    }
    public function isAlcoholExist() 
    {
		$categoriesIds = $categoryID = array();
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $item) {
            $productid = $item->getProductId();
            $product = $this->product->load($productid);
            $categoryIds = $product->getCategoryIds();
            $categoryParent = $this->categoryRepository->get($categoryId = $categoryIds[0]); // Load category
            $categoryID = ($categoryParent->getParentId() > 1) ? array($categoryParent->getParentId()) : $product->getCategoryIds();
            $categoriesIds = array_merge($categoriesIds, $categoryID);
        }
           // print_r($categoriesIds);die;
        
        $disclaimercategoryids = $this->getCategories();
        
        if(!empty($disclaimercategoryids)){
            $commonIDs = array_intersect($disclaimercategoryids, $categoriesIds);
            //return json_encode($commonIDs); die;
            if (!empty($commonIDs)){
                return true;
            }
        }
        return false;
    }

    public function getCategories()
    {
		$configPath = 'burstonline_checkoutdisclaimer/general/categories';
		$value =  $this->scopeConfig->getValue(
			$configPath,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		//print_r($value);die;
		$catDetails = explode(',', $value);
		return $catDetails;
	}
    public function getDisclaimercontent()
    {
		$configPath = 'burstonline_checkoutdisclaimer/general/disclaimercontent';
		$disclaimercontent =  $this->scopeConfig->getValue(
			$configPath,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		return $disclaimercontent;
	}
}
