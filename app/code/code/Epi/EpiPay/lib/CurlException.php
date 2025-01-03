<?php
/**
 * CurlException
 * used to throw the description of problem while connecting to epi server.
 * this e
 * xception throws when cURL not able to properly execute the request.
 */
namespace Epi\EpiPay\lib;
Class CurlException extends \Exception
{
	private $object;
	function __construct($message,$curlObject)
	{
		parent::__construct($message,0);
		
		
		$this->object = $curlObject;
		
	}
	
	public function __toString()
	{
		# will return curl object from curl.php in string manner.
		return "ERROR at Processing cURL request".PHP_EOL.(string)$this->object;
	}
	
}