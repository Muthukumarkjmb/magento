<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epi\EpiPay\Model;


class EpiPayPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

	protected $_isInitializeNeeded      = false;
    protected $redirect_uri;
    protected $_code = 'epi';
 	protected $_canOrder = true;
	protected $_isGateway = true; 
	
    public function getOrderPlaceRedirectUrl() {
	   return \Magento\Framework\App\ObjectManager::getInstance()
							->get('Magento\Framework\UrlInterface')->getUrl("epi/redirect");
   } 
}
