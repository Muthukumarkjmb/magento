<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epi\PayLater\Model;


class PayLaterPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

	protected $_isInitializeNeeded      = false;
    protected $redirect_uri;
    protected $_code = 'epipaylater';
 	protected $_canOrder = true;
	protected $_isGateway = true; 
}
