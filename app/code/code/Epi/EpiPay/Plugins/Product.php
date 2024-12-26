<?php

namespace Epi\EpiPay\Plugins;
use Epi\EpiPay\Logger\Logger;

/**
 * Plugin to turn off add to cart buttons
 *
 */
class Product
{
    /**
     * Override Product->isSaleable() to disable add to cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $result
     * @return bool
     */
    public function __construct(
        \Zend\Http\Client $zendClient ){
        $this->zendClient = $zendClient;
      
    }
    public function afterIsSalable(
        \Magento\Catalog\Model\Product $subject,
        $result
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/product.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $sku=$subject->getSku();
        $this->logger->info('SKU->'.print_r($sku,true));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
        $mid=$customerSession->getMid();
        try 
        {
            $this->zendClient->reset();
            $this->zendClient->setUri(ini['ExaTouchItemsRestAPI'].$ini['MID']."/sku/$sku");
            $this->zendClient->setOptions(array('timeout'=>30));
            // $this->zendClient->setUri("https://services-dev.poscloud.com/ExatouchRestAPI/api/items/liquorcart/".$mid."/sku/796363011318");
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
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Exatouch Api->'.print_r($runtimeException->getMessage(),true));
        }
    // --------------------------------ZEND---------------
    if($response->getStatusCode() == 200){
        $formattedResponse=json_decode($response->getBody(),true);
        $newQty=$formattedResponse[0]['QtyOnHand'];
        $this->logger->info('Qty in XT->'.$newQty);
        if($newQty>0){
            return true;
        }
        else{
            return false;
        }
    }
    else{
        return false;

    }
    }
}
