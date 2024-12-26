<?php
use Epi\EpiPay\Logger\Logger;

Class Api{
    function __construct(
    ){
        $this->zendClient = new  \Zend\Http\Client;
        // $this->logger = $logger;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/epipay.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
		$this->logger->info('COnstructor initialised');
    }

    public function checkInventory($mid,$sku){
        $response;

        // $path = '/home/ubuntu/test/magento/app/code/Epi/EpiPay/lib';
        // $ds = DIRECTORY_SEPARATOR;
        // include __DIR__ . "/config.ini";
        // set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        $ini = parse_ini_file(__DIR__ . "/config.ini");

                # -------------------------------ZEND---------------------
        #Api call to exatouch to check the item availability
        try 
        {
            $this->logger->info(print_r($ini['ExaTouchItemsRestAPI'].$ini['MID']."/sku/$sku",true));

            $this->zendClient->reset();
            $this->zendClient->setUri($ini['ExaTouchItemsRestAPI'].$ini['MID']."/sku/$sku");
            $this->zendClient->setOptions(array('timeout'=>30));
            // $this->zendClient->setUri("https://services-dev.poscloud.com/ExatouchRestAPI/api/items/liquorcart/999999999000090/sku/7504678548657");
            // $this->logger->info('URL->'.print_r("https://services-dev.poscloud.com/ExatouchRestAPI/api/items/liquorcart/".$mid."/sku/$sku",true));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Auth-Key' => '3rzonriaG1IJcgk/+blNjsvWLVuyp0oZAsIeeAJ6ZmzCBwBIYAZbeKBdQb2oZRjygs8KQE1aq4fV0idWnp4CpqmJFTAREJkLDV34mxEvqB0='
            ]);
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response from Exatouch Inventory Api->'.print_r($response->getBody(),true));
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Exatouch Api->'.print_r($runtimeException->getMessage(),true));
        }
        #--------------------------------ZEND---------------
        return $response;
    }
}