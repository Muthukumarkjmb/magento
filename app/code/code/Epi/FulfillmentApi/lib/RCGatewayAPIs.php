<?php
/**
 * 
 */
include_once __DIR__ . DIRECTORY_SEPARATOR . "curl.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "ValidationException.php";

use \ValidationException as ValidationException;
use \Exception as Exception;


Class RCGatewayAPIs
{
	private $api_endpoint;
	private $auth_endpoint;
	private $auth_headers;
	private $access_token;
	private $client_id;
	private $client_secret;
	private $ini;
	
	 function __construct()
	{
		$path = '/home/ubuntu/test/magento/app/code/Epi/EpiPay/lib';
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        $this->ini = parse_ini_file('config.ini');
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/token.log');
		$this->logger = new \Zend_Log();
		$this->logger->addWriter($writer);
		$this->curl = new Epi\FulfillmentApi\lib\Curl();
		$this->curl->setCacert(__DIR__."/cacert.pem");		
		$this->api_endpoint  = $this->ini['paymentUrl'];
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
	public function createOrder($data){  
		$token = $this->getBearerToken();
		$endpoint = $this->api_endpoint."createOrder";
	
			$result = $this->curl->post($endpoint,$data,$token);
			$result =json_decode($result);
			if(isset($result->paymentOrderId)==false){
				throw new Exception("Create Order Failed");
			}
		return $result;
	}
	
	public function getOrderById($id)
	{
		$endpoint = $this->api_endpoint."getOrderById";
		$api_data['paymentOrderId'] =$id;
		$token = $this->getBearerToken();
		$result = $this->curl->post($endpoint,$api_data,$token);
		
		$result = json_decode($result);
		if(isset($result->success))
			return $result;
		else
			throw new Exception("Unable to Fetch Payment Request id:'$id' Server Responds ".print_R($result,true));
	}

	public function doRefund($data)
	{
		$token = $this->getBearerToken();
		$endpoint = $this->api_endpoint."refund";
		// $payload=json_encode($data);
		$result = $this->curl->post($endpoint,$data,$token);
		$result =json_decode($result);
		if(isset($result)==false){
			throw new Exception("Refund Failed");
		}
		return $result;
	}

	public function updateTheSaleAsRefunded($data)
	{
		$token = $this->getBearerToken();
		$endpoint = $this->api_endpoint."updateTheSaleAsRefunded";
		// $payload=json_encode($data);
		$result = $this->curl->post($endpoint,$data,$token);
		$result =json_decode($result);
		if(isset($result)==false){
			throw new Exception("Failed to update refund status");
		}
		return $result;
	}
	
}