<?php
/**
 * 
 */
namespace Epi\EpiPay\lib;
// include_once __DIR__ . DIRECTORY_SEPARATOR . "curl.php";
// include_once __DIR__ . DIRECTORY_SEPARATOR . "ValidationException.php";

use \ValidationException as ValidationException;
use \Exception as Exception;
use \Zend\Json\Json;
use \Zend\Http\Client;

Class Tax
{
	private $ini;
    protected $zendClient;

	 function __construct( Client $zendClient)
	{
        $this->zendClient = $zendClient;
        $this->ini = parse_ini_file('config.ini');
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/taxes.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer);
		
	}
	public function getDataFromXT($url){
        $this->zendClient->reset();
        $this->zendClient->setUri($url);
        $this->zendClient->setOptions(array('timeout'=>30));
        $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET); 
        $this->zendClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Auth-Key' => $this->ini['Auth-Key']
        ]);
        $this->zendClient->send();
        $response = $this->zendClient->getResponse();
        $this->logger->info('Response Code from Exatouch Api->'.print_r($response->getStatusCode(),true));
        $formattedResponse=json_decode($response->getBody(),true);
        $this->logger->info('Response from Exatouch Api->'.print_r($formattedResponse,true));
        if($response->getStatusCode()==200 && count($formattedResponse)!=0)
        {
            $formattedResponse=$formattedResponse[0];
        }
        else{
            $formattedResponse=[];
        }
        return $formattedResponse;
    }
    public function calculateTax($taxData,$itemPrice){
        $taxAmount=0;
        if($taxData['GeneralTaxA']){
            // $this->logger->info('total tax calculated A before->'.print_r($taxAmount,true));
            $taxAmount+=($taxData['GeneralTaxA']*$itemPrice);
            $this->logger->info('total tax calculated A->'.print_r($taxAmount,true));
        }
        else if($taxData['GeneralTaxB'] && $taxData['GeneralTaxBDollar']){
            $taxAmount+=$taxData['GeneralTaxB'];
            $this->logger->info('total tax calculated B->'.print_r($taxAmount,true));
        }
        else if($taxData['GeneralTaxB'] && !$taxData['GeneralTaxBDollar']){
            $taxAmount+=($taxData['GeneralTaxB']*$itemPrice);
            $this->logger->info('total tax calculated B%->'.print_r($taxAmount,true));
        }
        else if($taxData['GeneralTaxC'] && $taxData['GeneralTaxCDollar']){
            $taxAmount+=$taxData['GeneralTaxC'];
            $this->logger->info('total tax calculated C->'.print_r($taxAmount,true));
        }
        else if($taxData['GeneralTaxC'] && !$taxData['GeneralTaxCDollar']){
            $taxAmount+=($taxData['GeneralTaxC']*$itemPrice);
            $this->logger->info('total tax calculated C%->'.print_r($taxAmount,true));
        }
        else if($taxData['GeneralTaxD'] && $taxData['GeneralTaxDDollar']){
            $taxAmount+=$taxData['GeneralTaxD'];
            $this->logger->info('total tax calculated D->'.print_r($taxAmount,true));
        }
        else if($taxData['GeneralTaxD'] && !$taxData['GeneralTaxDDollar']){
            $taxAmount+=($taxData['GeneralTaxD']*$itemPrice);
            $this->logger->info('total tax calculated D%->'.print_r($taxAmount,true));
        }
        return $taxAmount;
    }
}
