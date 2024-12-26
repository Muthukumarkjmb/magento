<?php
Namespace Epi\Product\Cron;

use \Exception as Exception;
use \Magento\Catalog\Model\Product;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use \Magento\Catalog\Api\BasePriceStorageInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use \Magento\Framework\Filesystem\Io\File as FilesystemIo;
use \Zend\Json\Json;

class updatePrices {

    private $errorMessages;
    private $successMessage;
    protected $_file;
    /** @var ReadInterface */
    private $readInterface;

    public function __construct(
        Product $product,
        ProductRepository $productRepository,
        BasePriceStorageInterface $priceInterface,
        StoreManagerInterface $storeManager,
        \Zend\Http\Client $zendClient,
        File $file,
        Filesystem $filesystem,
        FilesystemIo $filesystemIo
    ) {
        $this->product=$product;  
        $this->zendClient = $zendClient;   
        $ds = DIRECTORY_SEPARATOR;
        $this->ini = parse_ini_file(__DIR__ ."$ds../lib/config.ini");
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/PriceUpdateCronJob.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);           
        $this->errorMessages=[];
        $this->successMessage=''; 
        $this->_file =$file;
        $this->filesystemIo=$filesystemIo;
        $this->productRepository=$productRepository;
        $this->priceInterface=$priceInterface;
        $this->storeManager=$storeManager;
        $this->readInterface =$filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
       
    }
    
    public function getProduct($sku){
        $product;
        try{
            $product=$this->productRepository->get($sku,true);
        }
        catch (NoSuchEntityException $e) {
            $product=false; 
        }
        return $product;
    }

    public function editPrices($data){       
              
        $store=$this->storeManager->getStore();
        $storeId= $store->getStoreId();              
        $websiteId= $store->getWebsiteId();    
        date_default_timezone_set('America/New_York');
        $i=0;    
        $skuArray=[];
        try{    
            $this->logger->info("Bulk update");         
            foreach ($data as $product){
                if(!isset($product['sku'])|| !isset($product['newPrice'])){
                    throw new InvalidArgumentException(__("Invalid data"));
                }
                if(strlen($product['sku'])==0 || strlen($product['newPrice'])==0){
                    throw new InvalidArgumentException(__("One or more empty data found"));
                }
                array_push($skuArray,$product['sku']);
            }
            $oldPrices=$this->priceInterface->get($skuArray);
            $this->logger->info("OLD".print_r(count($oldPrices),true)); 
            $numberOfProductsNotFound=count($data)-count($oldPrices);
            if($numberOfProductsNotFound!=0){
                throw new Exception(_("Some of the products don't exist"));
            }
            $newPrices=$oldPrices;
            foreach($newPrices as $price){
                $sku=$price->getSku();
                $this->logger->info(print_r($sku,true));
                $searchedProduct= array_filter($data, function ($newPriceData) use ($sku) {
                    return $newPriceData['sku'] == $sku;
                });
                $searchedProduct=array_values($searchedProduct);
                $this->logger->info(print_r($searchedProduct,true));
                $newPrice=$searchedProduct[0]['newPrice'];                
                $price->setPrice($newPrice);
                $price->setStoreId($storeId);
            }
            $updatedPrices=$this->priceInterface->update($newPrices);
            $this->logger->info("NEW".print_r(count($updatedPrices),true)); 
            $this->successMessage=count($oldPrices). "Products updated with new prices";

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $indexerFactory = $objectManager->get('Magento\Indexer\Model\IndexerFactory');
            $indexerIds = array(
                'catalog_category_product',
                'catalog_product_category',
                'catalog_product_price',
                'catalog_product_attribute',
                'cataloginventory_stock',
                'catalogrule_product',
                'catalogsearch_fulltext',
            );
            foreach ($indexerIds as $indexerId) {
                $this->logger->info(" create index: ".print_r($indexerId,true));
                $indexer = $indexerFactory->create();
                $indexer->load($indexerId);
                $indexer->reindexAll();
            }
        }
        catch(\Exception $e){
            $this->logger->info("One by one update".print_r($e->getMessage(),true));  
            // array_push($errorMessages,$e->getMessage());
            foreach ($data as $priceData){ 
                if(!isset($priceData['sku'])|| !isset($priceData['newPrice'])){
                    // throw new InvalidArgumentException(__("Invalid data"));
                    array_push($this->errorMessages,["SKU"=>'',"Error"=>"Invalid data"]);
                }
                if(strlen($product['sku'])==0 || strlen($product['newPrice'])==0){
                    array_push($this->errorMessages,["SKU"=>'',"Error"=>"Empty data found"]);
                }
                $sku=$priceData['sku'];
                $price=$priceData['newPrice'];
                $this->logger->info([$sku ,$price]);      
                try{        
                    // $product=$this->productRepository->get($sku,true);
                    $product=$this->getProduct($sku);
                    // $this->logger->info($product); 
                    if(!$product){
                        array_push($this->errorMessages,["SKU"=>$sku,"Error"=>"Product does not exist"]);
                        continue;
                    }
                    if($product->getStoreId()!=$storeId){
                        array_push($this->errorMessages,["SKU"=>$sku,"Error"=>"Product does not exist in this store"]);
                        continue;
                    }
                    $product->setStoreId($storeId); 
                    $product->setPrice($price);
                    $product->setUpdatedAt(date('d-m-y h:i:s'));
                    $this->productRepository->save($product);     
                }    
                catch(\Exception $e){
                    array_push($this->errorMessages,["SKU"=>$sku,"Error"=>$e->getMessage()]);
                    continue;
                }  
            } 
            $this->successMessage=(count($data)-count($this->errorMessages))."Products updated successfully";
        }
       
    }

    private function sendEmailToXT($apiData){
        try{
           $this->logger->info("Send email".print_r($this->ini['fulfilmentEmailAPIURL'],true));
            // $this->zendClient->reset();
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
            $this->zendClient->setUri($this->ini['fulfilmentEmailAPIURL']);
            $this->zendClient->setRawBody(Json::encode($apiData));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response from Fulfillment Bot->'.print_r($response->getBody(),true));
            return;
        }
        catch (\Exception $runtimeException) 
        {
            $this->logger->info('Error in Email sending process ->'.print_r($runtimeException->getMessage(),true));
        }
    }
    
   /**
    * Write to system.log
    *
    * @return void
    */
    public function execute() {
        try{
            $this->logger->info('Price update cron task');
            $originalFilePath = $this->readInterface->getAbsolutePath('webapidocuments/ItemsPrice.json');                     
            if($this->readInterface->isExist($originalFilePath)){
                $filePath = $this->readInterface->getAbsolutePath('webapidocuments/ItemsPriceCronJob.json'); 
                rename($originalFilePath,$filePath);
                $this->logger->info(print_r($filePath,true));
                $fileContent=$this->readInterface->readFile($filePath);
                $parsedData=json_decode($fileContent, true);
                if(count($parsedData)==0){
                    array_push($this->errorMessages,["SKU"=>"","Error"=>"File empty"]);
                }
                $this->editPrices($parsedData['items']);
                $this->logger->info("Updated");
                $apiData['message']=$this->successMessage.'Errors Occured- '.print_r($this->errorMessages,true);
                $this->logger->info(print_r($apiData['message'],true));
                $apiData['email']='epidev@electronicpayments.com';
                $apiData['bcc']="epiqa@electronicpayments.com,jyoti.saha@metadesignsoftware.com";
                $apiData['subject']="Joseph's Beverages Center-Product prices bulk update response";
                $this->sendEmailToXT($apiData);
                $this->_file->deleteFile($filePath);
                $this->logger->info('Done');
            }
        }
        catch(\Exception $e){
            $this->logger->info($e->getMessage());
        }
    }
}