<?php
namespace Burstonline\Customconfig\Block;
use Magento\Framework\View\Element\Template\Context;

class Customconfig extends \Magento\Framework\View\Element\Template
{
	protected $scopeConfig;
    protected $storeManager;
    protected $cart;
	protected $product;
    public function __construct(
		Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\Product $product,
        \Magento\Checkout\Model\Cart $cart
		
    ) {
        $this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->cart = $cart;
		$this->product = $product;
      
        parent::__construct($context);
    }
    
    public function getSaleLimit()
    {
		$storeId=$this->storeManager->getStore()->getStoreId();
		$isEnabled=$this->isModuleEnabled();
		$isPriceConfigEnabled=$this->scopeConfig->getValue("burstonline_customconfig/product_price_config/active",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
		
		$priceConfig['enabled']=0;
		if($isEnabled && $isPriceConfigEnabled){
			$saleLimit=$this->scopeConfig->getValue("burstonline_customconfig/product_price_config/sale_limit",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
			$priceConfig['enabled']=1;
			$priceConfig['saleLimit']=$saleLimit;
			return $priceConfig;
		}
		return $priceConfig;
	}
	
	public function getOrderConfig()
    {
		$storeId=$this->storeManager->getStore()->getStoreId();
		$isEnabled=$this->isModuleEnabled();
		$isOrderConfigEnabled=$this->scopeConfig->getValue("burstonline_customconfig/order_config/active",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        
        $orderConfig['enabled']=0;
        if($isEnabled && $isOrderConfigEnabled){
			$minTotal=$this->scopeConfig->getValue("burstonline_customconfig/order_config/min_order_total",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
			$maxTotal=$this->scopeConfig->getValue("burstonline_customconfig/order_config/max_order_total",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
			
			$orderConfig['minTotal']=$minTotal;
			$orderConfig['maxTotal']=$maxTotal;
			$orderConfig['enabled']=1;
		}
		
		return $orderConfig;
			
	}
	public function getNonLmpConfig()
    {
		$storeId=$this->storeManager->getStore()->getStoreId();
		$isEnabled=$this->isModuleEnabled();
		$isNonLmpExist=$this->isNonLmpExist();
		$isOrderConfigEnabled=$this->scopeConfig->getValue("burstonline_customconfig/product_non_lmp/active",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        
        $nonLmpConfig['enabled']=0;
        if($isEnabled && $isOrderConfigEnabled & $isNonLmpExist){
			$minTotal=$this->scopeConfig->getValue("burstonline_customconfig/product_non_lmp/price_type",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
			$maxTotal=$this->scopeConfig->getValue("burstonline_customconfig/product_non_lmp/price",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
			
			$subTotal = $this->cart->getQuote()->getSubtotal();
			$grandTotal = $this->cart->getQuote()->getGrandTotal();
			
			$nonLmpConfig['price_type']=$minTotal;
			$nonLmpConfig['price']=$maxTotal;
			$nonLmpConfig['subTotal']=$subTotal;
			$nonLmpConfig['grandTotal']=$grandTotal;
			$nonLmpConfig['enabled']=1;
		}
		
		return $nonLmpConfig;
			
	}
	public function isNonLmpExist() 
    {
		$categoriesIds=array();
        $items = $this->cart->getQuote()->getAllItems();
        foreach ($items as $item) {
           
            $productid = $item->getProductId();
            $product = $this->product->load($productid);
            $categoriesIds = array_merge($categoriesIds,$product->getCategoryIds());
           // print_r($categoriesIds);die;
        }
        if (in_array(104, $categoriesIds)){
			return true;
		}
        else{return false;}
    }
    
    public function getSplashImage() 
    {
		$storeId=$this->storeManager->getStore()->getStoreId();
		$isEnabled=$this->isModuleEnabled();
		$isSplashConfigEnabled=$this->scopeConfig->getValue("burstonline_customconfig/splash_config/active",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        
        $splashConfig['enabled']=0;
        if($isEnabled && $isSplashConfigEnabled){
			$mediaUrl = $this ->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$splashConfig['splash_image']=$mediaUrl.'burstonline/'.$this->scopeConfig->getValue("burstonline_customconfig/splash_config/splash_image",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
			$splashConfig['enabled']=1;
		}
		
		return $splashConfig;
    }
    public function isSundayAllowed() 
    {
		$storeId=$this->storeManager->getStore()->getStoreId();
		$isEnabled=$this->isModuleEnabled();
		$isOrderConfigEnabled=$this->scopeConfig->getValue("burstonline_customconfig/order_config/active",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
        
        $isSundayAllowed=0;
        if($isEnabled && $isOrderConfigEnabled){
			$isSundayAllowed=$this->scopeConfig->getValue("burstonline_customconfig/order_config/sunday_sale",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
		}
		
		return $isSundayAllowed;
    }
    
	public function isModuleEnabled(){
		$storeId=$this->storeManager->getStore()->getStoreId();
		$isEnabled=$this->scopeConfig->getValue("burstonline_customconfig/general/active",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$storeId);
		return $isEnabled;
	}
	
}
