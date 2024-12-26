<?php
/**
 * 
 */
namespace Epi\EpiPay\lib;
include_once __DIR__ . DIRECTORY_SEPARATOR . "curl.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "ValidationException.php";

use \ValidationException as ValidationException;
use \Exception as Exception;

Class Epi
{
	private $api_endpoint;
	private $auth_endpoint;
	private $auth_headers;
	private $access_token;
	private $client_id;
	private $client_secret;
	private $ini;
	private $token;

	public function __construct(
		
	)
	{
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/token.log');
		$this->logger = new \Zend_Log();
		$this->logger->addWriter($writer);
        $this->ini = parse_ini_file('config.ini');
		$this->curl = new Curl();
		$this->curl->setCacert(__DIR__."/cacert.pem");		
		$this->api_endpoint  = $this->ini['paymentUrl'];
		$this->token=$this->getBearerToken();		
	
	}
      
	public function getToken(){
		return $this->token;
	}
	public function getBearerToken(){
		$this->logger->info('<----------------------------Bearer token got executed ----------------------------->');

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$cache = $objectManager->get('Magento\Framework\App\CacheInterface');
		$token = unserialize($cache->load('RC_access_token'));

		if($token != null){
			return $token['token'];
		}
		$this->logger->info('this is request for token generation... ');
		$endpoint = $this->api_endpoint."integration/auth/token";
		$header=array('headers'=>array('x-auth-id:'.$this->ini['EPIClientID'],'x-auth-secret:'.$this->ini['EPIClientSecret']));
		$result = $this->curl->post($endpoint,array(),$header);
		$result =json_decode($result);
		if(isset($result->token)==false){
			throw new Exception("Invalid Client");
		}
		$access_token = array(
			'token'=>$result->token,
			'token_expiry'=>$result->token_expiry
		);
		$expiration=floor(($result->token_expiry - round(microtime(true) * 1000))/1000);
		$this->logger->info('This is expiration time'.$expiration);
		$cache->save(serialize($access_token), 'RC_access_token', [], $expiration);		
        return $result->token;	
	 }
	 public function getFrontendToken(){
		$this->logger->info('<----------------------------Frontend Token  ----------------------------->');

		$this->logger->info('this is request for token generation... ');
		$endpoint = $this->api_endpoint."/integration/auth/getFrontendTokenAndOTP";
		$header=array('headers'=>array('x-auth-id:'.$this->ini['EPIClientID'],'x-auth-secret:'.$this->ini['EPIClientSecret']));
		$result = $this->curl->post($endpoint,array(),$header);
		$result =json_decode($result);
		if(isset($result->token)==false){
			throw new Exception("Invalid Client");
		}
		$access_token = array(
			'token'=>$result->token,
			'expire_at'=>$result->expire_at
		);
		
		return $access_token;	
	}
	public function createOrderPayment($data)
	{
		$endpoint = $this->api_endpoint."createOrder";
		// $payload=json_encode($data);
		$this->logger->info('this is the data for createOrderApi'.print_r($data,true));
		$header = array('headers'=>array('Authorization:Bearer '.$this->token));
		$result = $this->curl->post($endpoint,$data,$header);
			$result =json_decode($result);
			if(isset($result->paymentOrderId)==false){
				throw new Exception("Create Order Failed");
			}
			return $result;
	
	
		// if(isset($result->success))
		// return $result;
		// else
	
	}
	
	public function completePayment($data)
	{
		$header = array('headers'=>array('Authorization:Bearer '.$this->token));
		$endpoint = $this->api_endpoint."completion";
		// $payload=json_encode($data);
		$result = $this->curl->post($endpoint,$data,$header);
		$result =json_decode($result);
		if(isset($result)==false){
			throw new Exception("Create Order Failed");
		}
		return $result;
	}

	public function getStockUpdate($data)
	{
		$header = array('headers'=>array('Authorization:Bearer '.$thid->token));
		$endpoint = $this->api_endpoint."getStockUpdate";
		$result = $this->curl->post($endpoint,$data,$header);
		$result =json_decode($result);
		// if(isset($result)){
		// 	throw new Exception("getStockUpdate Failed");
		// }
		return $result;
	}


	
	
	public function getOrderById($id)
	{
		// $endpoint = $this->api_endpoint."gateway/orders/id:$id/";
		// $result = $this->curl->get($endpoint,array("headers"=>$this->auth_headers));
		$endpoint = $this->api_endpoint."getOrderById";
		$api_data['paymentOrderId'] =$id;
		$header = array('headers'=>array('Authorization:Bearer '.$this->token));
		$result = $this->curl->post($endpoint,$api_data,$header);
				
		$result = json_decode($result);
		if(isset($result->success))
			return $result;
		else
			throw new Exception("Unable to Fetch Payment Request id:'$id' Server Responds ".print_R($result,true));
	}

	public function getPaymentStatus($payment_id, $payments){
		foreach($payments as $payment){
		    if($payment->id == $payment_id){
			    return $payment->status;
		    }
		}
	}
	
}
