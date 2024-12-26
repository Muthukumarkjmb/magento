<?php
/**
 * 
 */
include_once __DIR__ . DIRECTORY_SEPARATOR . "curl.php";
include_once __DIR__ . DIRECTORY_SEPARATOR . "ValidationException.php";

use \ValidationException as ValidationException;
use \Exception as Exception;


Class Epi
{
	private $api_endpoint;
	private $access_token;
	private $client_id;
	private $client_secret;
	private $ini;
	private $token;
	
	//  function __construct($client_id,$client_secret,$test_mode)
	 function __construct()
	{
		$path = '/home/ubuntu/test/magento/app/code/Epi/CancelOrder/lib';
        set_include_path(get_include_path() . PATH_SEPARATOR . $path);
        $this->ini = parse_ini_file('config.ini');
		$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/token.log');
		$this->logger = new \Zend_Log();
		$this->logger->addWriter($writer);
        $this->ini = parse_ini_file('config.ini');
		$this->curl = new Curl();
		$this->curl->setCacert(__DIR__."/cacert.pem");	
		$this->api_endpoint  = $this->ini['paymentUrl'];
		$this->token=$this->getBearerToken();
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

	
	public function voidPayment($data)
	{
		$header=array('headers'=>array('Authorization:Bearer '.$this->token));
		$endpoint = $this->api_endpoint."void";
		// $payload=json_encode($data);
		$result = $this->curl->post($endpoint,$data,$header);
		$result =json_decode($result);
		if(isset($result)==false){
			throw new Exception("Create Order Failed");
		}
		return $result;
	}

}
