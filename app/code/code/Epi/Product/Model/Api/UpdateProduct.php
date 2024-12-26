<?php
namespace Epi\Product\Model\Api;

use \Exception as Exception;
use Magento\Framework\Exception\NotFoundException as NotFoundException;
use Magento\Framework\Exception\InvalidArgumentException as InvalidArgumentException;
use Magento\Framework\Exception\RunTimeException as RuntimeException;
use \Epi\FulfillmentApi\Model\LCAPIResponse;
use \Magento\Catalog\Model\Product;
use \Epi\EpiPay\lib\Tax;
use \Magento\Catalog\Model\CategoryFactory;
use \Magento\Catalog\Api\CategoryLinkManagementInterface;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Model\ProductFactory;

class UpdateProduct{

    private $product;
    private $mappedKeys=[];

    public function __construct(
        Product $product,
        Tax $xtapi,
        CategoryFactory $categoryFactory,
        CategoryLinkManagementInterface $categoryLinkManagementInterface,
        CategoryLinkRepository $categoryLinkRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductRepository $productRepository,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        \Zend\Http\Client $zendClient
    )
    {
        $ds = DIRECTORY_SEPARATOR;    
        $this->zendClient = $zendClient;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/ProductApi.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $this->ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
        $this->product=$product;
        $this->xtapi=$xtapi;
        $this->categoryFactory=$categoryFactory;
        $this->categoryLinkRepository=$categoryLinkRepository;
        $this->productRepository=$productRepository;
        $this->productFactory=$productFactory;
        $this->categoryLinkManagementInterface=$categoryLinkManagementInterface;
        $this->categoryRepository=$categoryRepository;
        $this->storeManager=$storeManager;
        $this->response = new LCAPIResponse();
        $this->mappedKeys=array("Description"=>"Name","Price"=>"Price","QtyOnHand"=>"Qty","Active"=>"Status");    
    }

    private function getMappedData($data){
        $mappedData=[];
        foreach($this->mappedKeys as $dataKey => $mappedKey){
            if(isset($data[$dataKey])){
                $mappedData[$mappedKey]=$data[$dataKey];
            }
        }
        $this->logger->info("Mapped array".print_r($mappedData,true));
        return $mappedData;
    }

    private function validateData($data){
        //Data validation
        if((isset($data['CatID']) && !is_numeric($data['CatID']))||(isset($data['SubCatID'])&&!is_numeric($data['SubCatID']))
        ||(isset($data['Price']) && !is_numeric($data['Price']))||(isset($data['QtyOnHand'])&&!is_numeric($data['QtyOnHand']))
        ||(isset($data['CatID']) && !isset($data['SubCatID'])) || (!isset($data['CatID']) && isset($data['SubCatID']))
        ||(isset($data['PackUnit'])&&!is_numeric($data['PackUnit']))||(isset($data['PackUnit'])&&$data['PackUnit']!=0 &&!isset($data['Unit']))
        ||(isset($data['Unit']) && is_numeric($data['Unit']))||(isset($data['Unit']) && !isset($data['PackUnit'])) ||(isset($data['Unit']) && isset($data['PackUnit']) && $data['PackUnit']==0)  ){
            throw new InvalidArgumentException(__("Invalid data"));
        }
    }

    public function getCategoryIdByNameAndParentId($categoryName,$parentId){
        //Search category by name and parent id in database
        $categoryCollection= $this->categoryFactory->create()->getCollection()->addAttributeToFilter('name',$categoryName)->addAttributeToFilter('parent_id',$parentId);
        $categoryId=null;
        if($categoryCollection->getSize()){
            $categoryId=$categoryCollection->getFirstItem()->getId();         
        }
        return $categoryId;
    }

    private function getCategoryNameAndId($url,$parentId){
        //Fetch cat/subcat data from XT
        $categoryData=$this->xtapi->getDataFromXt($url);
        if(count($categoryData)!=0){
            $categoryName=$categoryData['Description'];
        }
        else{
            throw new NotFoundException(__("Could not find category/subcategory data", 400));
        }
        $this->logger->info('Cat name'.print_r($categoryName,true));
        if($categoryName=='None'){
            return[null,$categoryName];
        }
        $categoryId=$this->getCategoryIdByNameAndParentId($categoryName,$parentId);
        //If new category
        if(!$categoryId){
            //Save new category
            $category=$this->categoryFactory->create();
            $category->setName($categoryName);
            $category->setParentId($parentId);
            $category->setIsActive(true);
            $category->setIncludeInMenu(1);
            $this->categoryRepository->save($category);

            $categoryId=$this->getCategoryIdByNameAndParentId($categoryName,$parentId);
        }
        $this->logger->info('Cat id'.print_r($categoryId,true));
        return [$categoryId,$categoryName];
    }

    public function isProductExistInStore($sku,$storeId){
        $product=$this->productRepository->get($sku,true);
        if($product->getStoreId()!=$storeId){
            return false;
        }
        return true;
    }

    public function getSkuByXTItemId($itemId){
        $productCollection= $this->productFactory->create()->getCollection()->addAttributeToFilter('item_id',$itemId);
        $sku=null;
        if($productCollection->getSize()){
            $sku=$productCollection->getFirstItem()->getSku();
        }
        return $sku;
    }

    public function updateProductStatus($sku,$storeId,$status){
        $product=$this->productRepository->get($sku,true);
        $product->setStoreId($storeId);
        $product->setStatus($status); 
        $this->productRepository->save($product);
    }

    private function discontinueProduct($sku,$storeId){
        $this->logger->info("Discontinue Product");
        if(!$sku){
            throw new NotFoundException(__("Product does not exist"));
        }
        if(!$this->isProductExistInStore($sku,$storeId)){
            throw new NotFoundException(__("Product does not exist in the store. Try a different store"));
        }
        $this->updateProductStatus($sku,$storeId,0);
        $this->response->setMessage('Product discontinued');           
    }

    // private function changeProductActiveStatus($data){
    //     $this->logger->info("Activate/Deactivate Product");
    //     $activeStatus=$data['Active'];
    //     $storeId= $this->storeManager->getStore()->getStoreId(); 
    //     $sku=$this->getSkuByXTItemId($data['ItemID']);
    //     if(!$sku){
    //         throw new NotFoundException(__("Product does not exist"));
    //     }
    //     if(!$this->isProductExistInStore($sku,$storeId)){
    //         throw new NotFoundException(__("Product does not exist in the store. Try a different store"));
    //     }
    //     $product=$this->productRepository->get($sku,true);
    //     if($activeStatus==1 && $product->getStatus()==1){
    //         $data['SKU']=$sku;
    //         $this->editProduct($data);          
    //     }   
    //     else if($activeStatus==0 && $product->getStatus()==0){
    //         throw new InvalidArgumentException(__("Product already Inactive. Update Product not allowed"));
    //     }
    //     else{
    //         $this->updateProductStatus($sku,$storeId,$activeStatus);      
    //         $this->response->setMessage('Product active status changed');
    //     }
             
    // }

    public function editProduct($data){
        $this->logger->info("Edit Product");
        $this->validateData($data);
        $sku=$data['SKU'];
        $itemId=$data['ItemID'];
        $store=$this->storeManager->getStore();
        $storeId= $store->getStoreId();              
        $rootCategoryId=$store->getRootCategoryId();  
        $websiteId= $store->getWebsiteId();

        //Check if Discontinued true  
        if(isset($data['Discontinued'])){
            $discontinued=$data['Discontinued'];
            if($discontinued==1){
                $this->discontinueProduct($sku,$storeId); 
                return;
            }
            else if($discontinued!=1 && $discontinued!=0){
                throw new InvalidArgumentException(__("Invalid value for Discontinued attribute"));
            }
        }

        //Check if category id and sub category are to be updated
        if(isset($data['CatID']) && isset($data['SubCatID']) ){
            $catId=$data['CatID'];
            $subcatId=$data['SubCatID'];
            $categoryIds=[];

            $category_url=$this->ini['XTAPIURL']."Categories/inventory/".$this->ini['MID']."/"."categoryId/".$catId;
            [$categoryId,$categoryName]=$this->getCategoryNameAndId($category_url,$rootCategoryId);
            if($categoryId){
                array_push($categoryIds,$categoryId);
            }         
    
            $sub_category_url=$this->ini['XTAPIURL']."SubCategories/inventory/".$this->ini['MID']."/"."subCatId/".$subcatId;
            [$subCategoryId,$subCategoryName]=$this->getCategoryNameAndId($sub_category_url,$categoryId);
            if($subCategoryId){
                array_push($categoryIds,$subCategoryId);
            }

            //Add product to category and subcategory
            $this->categoryLinkManagementInterface->assignProductToCategories($sku,$categoryIds);
        }

        //Get mapped data
        $mappedData=$this->getMappedData($data);        
        date_default_timezone_set('America/New_York');

        $product=$this->productRepository->get($sku,true);
        //If product disabled
        if(!$product->getStatus() && isset($mappedData['Status']) && $mappedData['Status']!=1){
            throw new InvalidArgumentException(__("Updating Inactive Product Not Allowed"));
        }

        $product->setStoreId($storeId);  
        foreach($mappedData as $mappedKey=>$mappedValue){
            $mappedFunctionName='set'.$mappedKey;
            $product->$mappedFunctionName($mappedValue);
        }   
        if(isset($mappedData['Name'])){
            $product->setCustomAttribute('description',$mappedData['Name']);  
        }
        $product->setWebsiteIds([$websiteId]);   
        if(isset($data['PackUnit']) && isset($data['Unit']))
        {
            $product->setCustomAttribute('size',$data['PackUnit'].$data['Unit']);   
        }                        
                      
        $product->setUpdatedAt(date('d-m-y h:i:s'));
        $this->productRepository->save($product);  
        $this->response->setMessage('Existing Product updated'); 
    }
    
    public function addProduct($data){
        $this->logger->info("Add Product");
        //Validate SKU
        if(!isset($data['SKU'])||!is_string($data['SKU'])){
            throw new InvalidArgumentException(__("Item does not exist. To add a new product, provide a valid SKU."));
        }
        //Validate other data
        if(!isset($data['CatID'])||!is_numeric($data['CatID'])||!isset($data['SubCatID'])||!is_numeric($data['SubCatID'])
        ||!isset($data['Description'])||!isset($data['Price'])
        ||!is_numeric($data['Price'])||!isset($data['QtyOnHand'])||!is_numeric($data['QtyOnHand'])
        ||(isset($data['PackUnit'])&&!is_numeric($data['PackUnit']))||(isset($data['PackUnit'])&&$data['PackUnit']!=0 &&!isset($data['Unit']))
        ||(isset($data['Unit']) && is_numeric($data['Unit']))||(isset($data['Unit']) && !isset($data['PackUnit'])) ||(isset($data['Unit']) && isset($data['PackUnit']) && $data['PackUnit']==0) ){
            throw new InvalidArgumentException(__("Invalid data"));
        }   

        date_default_timezone_set('America/New_York');

        $sku=$data['SKU'];
        $itemId=$data['ItemID'];
        $name=$data['Description'];
        $price=$data['Price'];
        $qty=$data['QtyOnHand'];
        $catId=$data['CatID'];
        $subcatId=$data['SubCatID'];
        $store=$this->storeManager->getStore();
        $storeId= $store->getStoreId();              
        $rootCategoryId=$store->getRootCategoryId();  
        $websiteId= $store->getWebsiteId();
        $categoryIds=[]; 
        $size=0;
        if(isset($data['PackUnit']) && isset($data['Unit']))
        {
            $size=$data['PackUnit'].$data['Unit'];
        }        

        $category_url=$this->ini['XTAPIURL']."Categories/inventory/".$this->ini['MID']."/"."categoryId/".$catId;
        [$categoryId,$categoryName]=$this->getCategoryNameAndId($category_url,$rootCategoryId);
        if($categoryId){
            array_push($categoryIds,$categoryId);
        }         

        $sub_category_url=$this->ini['XTAPIURL']."SubCategories/inventory/".$this->ini['MID']."/"."subCatId/".$subcatId;
        [$subCategoryId,$subCategoryName]=$this->getCategoryNameAndId($sub_category_url,$categoryId);
        if($subCategoryId){
            array_push($categoryIds,$subCategoryId);
        }

        $this->product->setSku($sku);                
        $this->product->setStoreId($storeId);        
        $this->product->setName($name);
        $this->product->setPrice($price);
        $this->product->setQty($qty);            
        $this->product->setStatus(1);  
        $this->product->setWebsiteIds([$websiteId]);  
        $this->product->setUrlKey($categoryName."/".$subCategoryName."/".$name."/".$sku); 
        $this->product->setCustomAttribute('size',$size);            
        $this->product->setCustomAttribute('description',$name);
        $this->product->setAttributeSetId(4);
        $this->product->setWeight(0); 
        $this->product->setVisibility(4); 
        $this->product->setTaxClassId(2);
        $this->product->setTypeId('simple');
        $this->product->setCreatedAt(date('d-m-y h:i:s'));                          
        $this->product->setUpdatedAt(date('d-m-y h:i:s'));      
        $this->product->save();      

        //So that it checks for unique vale for item_id attribute
        $product=$this->productRepository->get($sku,true);
        $product->setCustomAttribute('item_id',$itemId);
        $this->productRepository->save($product);    

        //Add product to category and subcategory
        $this->categoryLinkManagementInterface->assignProductToCategories($sku,$categoryIds);
        $this->response->setMessage('New Product added'); 
    }

    public function updateProduct($data) {
        $this->logger->info('<----- Update Product ----->');
        try {
            //Item Id validation
            if(!isset($data['ItemID'])||!is_numeric($data['ItemID'])){
                throw new InvalidArgumentException(__("Invalid Item ID"));
            }
                      
            // //Check if dicontinued
            // if(isset($data['Discontinued']))
            // {
            //     $this->discontinueProduct($data);                
                             
            // }
            // //Check if deactivated
            // else if(isset($data['Active']))
            // {
            //     $this->changeProductActiveStatus($data);               
                      
            // }
            //Check if existing Product
            if($sku=$this->getSkuByXTItemId($data['ItemID'])){
                $data['SKU']=$sku;
                $this->editProduct($data);
               
            }
            //New Product
            else{
                $this->addProduct($data);
            
            }         

            $this->response->setSuccess(true);  
            return $this->response;  

        } catch (\Exception $e) {   
            //Delete product if item id not unique exception
            if($e->getMessage()=="The value of the \"item_id\" attribute isn't unique. Set a unique value and try again."){
                $this->productRepository->deleteById($data['SKU']);
            }
            throw($e);
        }
    }
}