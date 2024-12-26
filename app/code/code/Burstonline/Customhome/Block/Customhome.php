<?php
namespace Burstonline\Customhome\Block;
use Magento\Framework\View\Element\Template\Context;

class Customhome extends \Magento\Framework\View\Element\Template
{
    public function __construct(
		Context $context,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Framework\App\Filesystem\DirectoryList $directory_list
		
    ) {
        
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_filesystem = $directory_list;
		$this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context);
    }
    
    public function getCategories()
    {
		$configPath = 'burstonline_customhome/general/categories';
		$value =  $this->scopeConfig->getValue(
			$configPath,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		//print_r($value);die;
		$catDetails=array();
		if(!empty($value)){
			$catids = explode(',', $value);
			$catDetails=array();$i=0;
			foreach($catids as $categoryId){
				$category = $this->_categoryFactory->create()->load($categoryId);
				$catDetails[$i]['id']=$categoryId;
				$catDetails[$i]['name']=$category->getName();
				$catDetails[$i]['img']=$category->getImageUrl();
				$catDetails[$i]['url']=$category->getUrl();
				//print_r($category->getData());die;
				//$categoryName = $category->getName();
				$i++;
			}
		}
		return $catDetails;
		/*$categories = $this->categoryCollectionFactory->create()                              
			->addAttributeToSelect('*')
			->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
		$i=0;
		foreach ($categories as $category){
			$catDetails[$i]['name']=$category->getName();
			$catDetails[$i]['img']=$category->getImageUrl();
			$i++;
		}
		return $catDetails;*/
		
		/*$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

$category_model = $objectManager->get('Magento\Catalog\Model\Category');

$category_id = 5;//default category

$category = $category_model->load($category_id);
$subcategories = $category->getChildrenCategories();
$subcat_array = array();
$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
$connection = $resource->getConnection();
$i=0;
foreach($subcategories as $key => $subcategory) {
		$category = $this->_categoryFactory->create()->load($subcategory->getId());
		$catDetails[$i]['name']=$category->getName();
		$catDetails[$i]['img']=$category->getImageUrl();
		$i++;
}
return $catDetails;*/
		
		//print_r($catids);die;
	}
	 public function getShopimages()
    {
		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$configPath1 = 'burstonline_customhome/general/first_image';
		$configPath2 = 'burstonline_customhome/general/second_image';
		$images[0]['img'] =  $mediaUrl."customhome/".$this->scopeConfig->getValue(
			$configPath1,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$images[1]['img'] =  $mediaUrl."customhome/".$this->scopeConfig->getValue(
			$configPath2,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$images[0]['url'] = $this->scopeConfig->getValue(
			'burstonline_customhome/general/first_image_url',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$images[1]['url'] = $this->scopeConfig->getValue(
			'burstonline_customhome/general/second_image_url',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		
		return $images;
			
	}
	
	public function getBottombanner()
    {
		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$configPath = 'burstonline_customhome/general/bottom_image';
		$configPathDes = 'burstonline_customhome/general/bottom_img_description';
		
		$images['img'] =  $mediaUrl."customhome/".$this->scopeConfig->getValue(
			$configPath,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		$images['des'] =  $this->scopeConfig->getValue(
			$configPathDes,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
		return $images;
			
	}
	
}
