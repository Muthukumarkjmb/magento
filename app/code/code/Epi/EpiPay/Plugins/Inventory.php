<?php

namespace Epi\EpiPay\Plugins;
// $ds = DIRECTORY_SEPARATOR;
// include_once __DIR__. "$ds..$ds../lib/curl.php";
// include_once __DIR__. "$ds..$ds../lib/ValidationException.php";
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use \Zend\Json\Json;
use Epi\EpiPay\Logger\Logger;
class Inventory{
    protected $productrepository;  
    private $logger;
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        Logger $logger,
        \Zend\Http\Client $zendClient) {
        $this->productrepository = $productrepository;
        $this->stockRegistry = $stockRegistry;
        $this->_scopeConfig = $scopeConfig;
        $this->config = $scopeConfig;
        $this->zendClient = $zendClient;
        $this->logger = $logger;
    }
  
    public function aroundcheckQty(
        \Magento\CatalogInventory\Model\StockStateProvider $stock,
        $result,
        StockItemInterface $stockItem,
        $qty
        )
        {
        #include config file
        // $path = '/home/ubuntu/test/magento/app/code/Epi/EpiPay/lib';
        // set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        $ds = DIRECTORY_SEPARATOR;
        $ini = parse_ini_file(__DIR__."$ds../lib/config.ini");
        
        #initialize Logger
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Inventory.log');
        // $logger = new \Zend_Log();
        // $logger->addWriter($writer);

        $productid=$stockItem->getProductId();
        $product=$this->productrepository->getById($productid);
        $this->logger->info('<----------Inventory Plugin Called---------->');
        // $this->logger->info($stockItem->getProductId());
        $this->logger->info('SKU->'.print_r($product->getSku(),true));
        $this->logger->info('Product Name->'.print_r($stockItem->getProductName(),true));

        #get sku from product
        $sku=$product->getSku();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
        $mid=$customerSession->getMid();
        $this->logger->info('MID->'.print_r($mid,true));
        // $this->logger->info('URL->'.print_r("https://services-dev.poscloud.com/ExatouchRestAPI/api/items/liquorcart/".$mid."sku/$sku",true));
        // $api_data['sku']=$sku;
        // $api_data['mid']="530961270020423";
    // -------------------------------ZEND---------------------
    try 
        {
            $this->zendClient->reset();
            $this->zendClient->setUri(ini['ExaTouchItemsRestAPI'].$ini['MID']."/sku/$sku");
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Auth-Key' => '3rzonriaG1IJcgk/+blNjsvWLVuyp0oZAsIeeAJ6ZmzCBwBIYAZbeKBdQb2oZRjygs8KQE1aq4fV0idWnp4CpqmJFTAREJkLDV34mxEvqB0='
            ]);
            // $this->zendClient->setRawBody(Json::encode($api_data));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response from Exatouch Api->'.print_r($response->getBody(),true));
            $newQty=json_decode($response->getBody(),true);
            $newQty=$newQty[0];
            $newQty=$newQty['QtyOnHand'];
            $this->logger->info('Qty->'.print_r($newQty,true));
            if(isset($newQty)){
                #update stock in magento's inventory if qty is returned
                $stockItemFormRegistory = $this->stockRegistry->getStockItemBySku($sku);
                $stockItemFormRegistory->setQty($newQty);
                $stockItemFormRegistory->setIsInStock((bool)$newQty);
                $this->stockRegistry->updateStockItemBySku($sku, $stockItemFormRegistory);
            }
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Exatouch Api->'.print_r($runtimeException->getMessage(),true));
        }
    // --------------------------------ZEND---------------
        return true;
    }

}