<?php
namespace Burstonline\Importproduct\Controller\Index;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class Importproduct extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected $productFactory;
	protected $productRepository;
	protected $stockRegistry;
	protected $indexerFactory;
	protected $categoryFactory;
	protected $importlogFactory;
	protected $_resourceConnection;
	protected $connectionFactory;
	protected $productCollectionFactory;
	protected $directoryList;
    protected $file;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory, 
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		\Magento\Indexer\Model\IndexerFactory $indexerFactory,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Burstonline\Importproduct\Model\ImportlogFactory $importlogFactory,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		DirectoryList $directoryList,
        File $file
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->productFactory = $productFactory;
		$this->productRepository = $productRepository;
		$this->stockRegistry = $stockRegistry;
		$this->indexerFactory = $indexerFactory;
		$this->categoryFactory = $categoryFactory;
		$this->importlogFactory = $importlogFactory;
		$this->_resourceConnection = $resourceConnection;
		$this->connectionFactory = $connectionFactory;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->directoryList = $directoryList;
        $this->file = $file;
		return parent::__construct($context);
	}
	
	protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
    }

	public function execute()
	{
		$db = $this->connectionFactory->create(array(
            'host' => 'localhost',
            'dbname' => 'liqimport',
            'username' => 'root',
            'password' => 'AmqR7duk',
            'active' => '1',    
        ));
		
	    $parentId = 5;

        $parentCategory = $this->categoryFactory->create()->load($parentId);
				
        // Let's test the DB with a query

        $productservicetable = 'productservice';

        $productserviceselect = $db->select()
            ->from($productservicetable, '*');
            
        $inventorytable = 'inventory';        
        $priceapitable = 'priceapi';

       $prod_collection = $this->productCollectionFactory->create();

            
		
		$i=0;$createditemids=array();$notcreateditemids=array();
        if ($results = $db->fetchAll($productserviceselect)) {
			foreach($results as $res){
				$i++;
				if($i<=4700){continue;}
				$product_details=array();
				$prod_collection->addFieldToFilter('sku',$res['itemId']);
				$p = $prod_collection->getFirstItem();

				/*if (!$p->getId()) {
					echo $res['itemId']." Product already exist\n";
					continue;
				} */
								
				$product_details['sku']=$res['itemId'];
				$product_details['itemId']=$res['itemId'];
				$product_details['name']=$res['productName'];
				
				$collection = $this->categoryFactory->create()->getCollection()->addFieldToFilter('name', ['in' => $res['category']]);
				$categoryIds=array();
				if ($collection->getSize()) {
					$categoryIds[] = $collection->getFirstItem()->getId();
				}else{
					$category = $this->categoryFactory->create();
					$category->setPath($parentCategory->getPath())
								->setParentId($parentId)
								->setName($res['category'])
								->setIsActive(true);
					$newcategory=$category->save();
					$categoryIds[]=$newcategory->getId();
					//echo 
				}
				
				$product_details['category']=$categoryIds;
				$product_details['type']=$res['type'];
				$product_details['subType']=$res['subType'];
				$product_details['descriptionShort']=$res['descriptionShort'];
				$product_details['volumeMetric']=$res['volumeMetric'];
				$product_details['imgThumb']=$res['imgThumb'];
				$product_details['imgFront']=$res['imgFront'];
				$product_details['categoryName']=$res['category'];
				
				$priceapiselect = $db->select()
					->from($priceapitable, '*')->where("itemid = '".$res['itemId']."'");
				 if ($priceapicol = $db->fetchAll($priceapiselect)) {
					 $product_details['description']=$priceapicol[0]['description'];
					 $product_details['categoryName']=$priceapicol[0]['categoryName'];
					 $product_details['bottleLimit']=$priceapicol[0]['bottleLimit'];
					 $product_details['solStatusCode']=$priceapicol[0]['solStatusCode'];
					 $product_details['solProof']=$priceapicol[0]['solProof'];
					 $product_details['websitevis']=$priceapicol[0]['websitevis'];
					 $product_details['ounces']=$priceapicol[0]['ounces'];
					 $product_details['upc']=$priceapicol[0]['upc'];
					 $product_details['currenteffectivedate']=$priceapicol[0]['currenteffectivedate'];
					 $product_details['currentprice']=$priceapicol[0]['currentprice'];
					 $product_details['nexteffectivedate']=$priceapicol[0]['nexteffectivedate'];
					 $product_details['nexteffectiveprice']=$priceapicol[0]['nexteffectiveprice'];
				 }else{
					 $notcreateditemids[]=$product_details['sku'];
				 }
				 $inventoryselect = $db->select()
					->from($inventorytable, '*')->where("productId = '".$res['itemId']."'");
				if ($inventorycol = $db->fetchAll($inventoryselect)) {
					$product_details['quantity']=$inventorycol[0]['quantity'];
					$product_details['bottleIcon']=$inventorycol[0]['bottleIcon'];
				}
				echo "<pre>";
				print_r($product_details);
				echo "</pre>";//die;
				
				/* Import data */
				
					//print_r($_prod);die;
					
					if($i==4801){break;}
					
					$productSku=$product_details['sku'];
					
					$product = $this->productFactory->create();
					$product->setSku($productSku);
					$product->setName($product_details['name']);
					if (array_key_exists("description",$product_details)){$product->setDescription($product_details['description']);}
					$product->setType($product_details['type']);
					$product->setSubType($product_details['subType']);
					$product->setXtCategoryname($product_details['categoryName']);
					$product->setShortDescription($product_details['descriptionShort']);
					$product->setItemId($product_details['itemId']);
					$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
					$product->setVisibility(4);
					if (array_key_exists("currentprice",$product_details)){$product->setPrice($product_details['currentprice']);}
					$product->setCategoryIds($product_details['category']);
					$product->setAttributeSetId(4); 
					$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
					if (array_key_exists("upc",$product_details)){$product->setUpc($product_details['upc']);}
					$product->setVolumeMetric($product_details['volumeMetric']);
					if (array_key_exists("bottleLimit",$product_details)){$product->setBottleLimit($product_details['bottleLimit']);}
					if (array_key_exists("solStatusCode",$product_details)){$product->setSolStatusCode($product_details['solStatusCode']);}
					if (array_key_exists("solProof",$product_details)){$product->setSolProof($product_details['solProof']);}
					if (array_key_exists("websitevis",$product_details)){$product->setWebsitevis($product_details['websitevis']);}
					if (array_key_exists("ounces",$product_details)){$product->setOunces($product_details['ounces']);}
					if (array_key_exists("currenteffectivedate",$product_details)){$product->setCurrenteffectivedate($product_details['currenteffectivedate']);}
					if (array_key_exists("nexteffectivedate",$product_details)){$product->setNexteffectivedate($product_details['nexteffectivedate']);}
					if (array_key_exists("nexteffectiveprice",$product_details)){$product->setNexteffectiveprice($product_details['nexteffectiveprice']);}
					
					$url = preg_replace('#[^0-9a-z]+#i', '-', $product_details['name'].'-'.$productSku);
					$url = strtolower($url);
					$product ->setUrlKey($url);
					
					if (array_key_exists("bottleIcon",$product_details)){$product->setBottleIcon($product_details['bottleIcon']);}
					if (array_key_exists("quantity",$product_details)){
						$product->setStockData(['qty' => $product_details['quantity'], 'is_in_stock' => 1]);
						$product->setQuantityAndStockStatus(['qty' => $product_details['quantity'], 'is_in_stock' => 1]);
					}else{
					}
					
					$taxClassId=0;
					$product->setCustomAttribute('tax_class_id', $taxClassId);
					
					
					
					if($product_details['imgFront']){
						$tmpDir = $this->getMediaDirTmpDir();
						$this->file->checkAndCreateFolder($tmpDir);
						$newFileName = $tmpDir . baseName($product_details['imgFront']);
						$result = $this->file->read($product_details['imgFront'], $newFileName);
						if ($result) {
							$product->addImageToMediaGallery($newFileName, array('image', 'small_image'), false, false);
						}
					}
					if($product_details['imgThumb']){
						$tmpDir = $this->getMediaDirTmpDir();
						$this->file->checkAndCreateFolder($tmpDir);
						$newFileName = $tmpDir . baseName($product_details['imgThumb']);
						$result = $this->file->read($product_details['imgThumb'], $newFileName);
						if ($result) {
							$product->addImageToMediaGallery($newFileName, array('thumbnail'), false, false);
						}
					}
					//if($product_details['imgThumb']){$product->addImageToMediaGallery($product_details['imgThumb'], array('thumbnail'), false, false);}
					
					try {
						$product = $this->productRepository->save($product); 
						$createditemids[]=$product_details['sku'];
						echo "product created successfully \n";
						/*foreach ($indexerIds as $indexerId) {
							$indexer = $this->indexerFactory->create();
							$indexer->load($indexerId);
							$indexer->reindexRow($productSku);
						}
						echo "Reindexed product with SKU:".$productSku."\n";*/
					}
					catch(\Exception $e){
						echo "****** Exception Throw ************* \n";
						echo $e->getMessage() . " \n";
					}
					
					//$i++;
				
				
				
			}
			echo "Imported Products - ";
			print_r($createditemids);
			echo "Not Imported Products - ";
			print_r($notcreateditemids);
        }
        else {
            echo 'The query was empty.';
        }die;
		
		
        
		//$indexerIds = ['catalog_category_product','catalog_product_category','catalogsearch_fulltext','catalog_product_attribute','catalogrule_product', 'catalog_product_price','cataloginventory_stock'];
		//$indexerIds = ['catalog_category_product','catalog_product_category','catalogsearch_fulltext','catalog_product_attribute','catalogrule_product', 'catalog_product_price','cataloginventory_stock'];
		 $indexerIds = [
                    'catalog_category_product',
                    'catalog_product_category',
                    'catalog_product_attribute',
                    'cataloginventory_stock',
                    'inventory',
                    'catalogsearch_fulltext',
                    'catalog_product_price',
                    'catalogrule_product',
                    'catalogrule_rule'
                ];
		print_r($indexerIds);die;
		$i=1;
		
	}
}
