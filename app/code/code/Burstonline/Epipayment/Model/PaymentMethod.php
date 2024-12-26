<?php

namespace Burstonline\Epipayment\Model;
use Magento\Framework\Event\ManagerInterface;
use Burstonline\Epipayment\Model\EpiLogFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Pay In Store payment method model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'epipayment';
    protected $validator;
    protected $_canAuthorize                = true;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCancel                   = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_supportedCurrencyCodes = array('USD','GBP','EUR');
    protected $eventManager;
    protected $scopeConfig;
    protected $storeManager;
    protected $session;
    protected $encryptor;
    protected $logger;
    
    public function __construct(
        ManagerInterface $eventManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $testpaymentFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        EpiLogFactory $modelEpiLogFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Burstonline\Epipayment\Model\PaymentMethodValidator $validator,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SessionManagerInterface $session,
        EncryptorInterface $encryptor,
        array $data = array()
    ) {
        $this->eventManager = $eventManager;
        $this->_modelEpiLogFactory = $modelEpiLogFactory;
        $this->validator = $validator;
        $this->scopeConfig = $scopeConfig;
	$this->storeManager = $storeManager;
	$this->logger = $logger;
        $this->session = $session;
        $this->encryptor = $encryptor;
        parent::__construct(
            $context,
            $registry,
            $testpaymentFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
	    null,
            null,
            $data
        );
    }
    /**
     * Authorizes specified amount.
     *
     * @param InfoInterface $payment
     * @param float         $amount
     *
     * @return $this
     *
     * @throws LocalizedException
     */

    /*public function getConfigData($configPath, $scope = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($configPath, $scope, $scopeCode);
    }

    public function getTokenSecret()
    {
        $configPath = 'payment/epipayment/token_secret';
        $scope = ScopeInterface::SCOPE_STORE;
        $scopeCode = $this->storeManager->getStore()->getId(); // Get the store ID as scope code

        $encryptedValue = $this->scopeConfig->getValue('payment/epipayment/token_secret',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());

        //$encryptedValue = $this->getConfigData($configPath, $scope, $scopeCode);

        return $this->encryptor->decrypt($encryptedValue);
    }*/


    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        // Retrieve data from session
        $additionalData = $this->session->getPaymentFormData();
        $this->session->unsPaymentFormData(); // Clear session data after retrieving
        if(empty($additionalData)){
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__('Please fill credit card details')); return $this; die;
        }
        if (is_array($additionalData)) {
            $infoInstance = $this->getInfoInstance();

            if (!empty($additionalData['cc_customer_name'])) {
                $infoInstance->setAdditionalInformation('cc_customer_name', $additionalData['cc_customer_name']);
            }
            if (!empty($additionalData['cc_type'])) {
                $infoInstance->setAdditionalInformation('cc_type', $additionalData['cc_type']);
            }
            if (!empty($additionalData['cc_number']) && $this->validateCardNumber($additionalData['cc_number'])) {
                $infoInstance->setAdditionalInformation('cc_number', $additionalData['cc_number']);
            }
            else{
                throw new \Magento\Framework\Exception\CouldNotDeleteException(__('Please enter a valid card number')); return $this; die;
            }/**/
            if (!empty($additionalData['cc_exp_month'])) {
                $infoInstance->setAdditionalInformation('cc_exp_month', $additionalData['cc_exp_month']);
            }
            if (!empty($additionalData['cc_exp_year'])) {
                $infoInstance->setAdditionalInformation('cc_exp_year', $additionalData['cc_exp_year']);
            }
            if (!empty($additionalData['cc_cid']) && $this->validateCVV($additionalData['cc_cid'])) {
                $infoInstance->setAdditionalInformation('cc_cid', $additionalData['cc_cid']);
            }
            else{
                throw new \Magento\Framework\Exception\CouldNotDeleteException(__('Please enter a valid CVV number')); return $this; die;
            }
            if (!$this->validateExpiryDate($additionalData['cc_exp_month'], $additionalData['cc_exp_year'])) {
                throw new \Magento\Framework\Exception\CouldNotDeleteException(__('Please enter a valid expiry date')); return $this; die;
            }/*
            */
        }

        return $this;
    }
    
    public function isAvailable(CartInterface $quote = null)
    {
        if ($quote)
        {
            $isorderVal = $this->validator->isApplicable($this, $quote);
            if(!empty($isorderVal)) {
                //$min_order_total =  $this->getConfigData('min_order_total');
                //$max_order_total =  $this->getConfigData('max_order_total');
                $min_order_total=$this->scopeConfig->getValue("burstonline_customconfig/order_config/min_order_total",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());
                $max_order_total=$this->scopeConfig->getValue("burstonline_customconfig/order_config/max_order_total",\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->storeManager->getStore()->getStoreId());
                if($isorderVal == 'min'){
                    throw new \Magento\Framework\Exception\LocalizedException(__('The minimum online order value is $'.$min_order_total.'.'));
                }
                else if($isorderVal == 'max')
                {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The maximum online order value is $'.$max_order_total.'.')); 
                }
                return false;
            }
        }
        return parent::isAvailable($quote);
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

        //$order = $payment->getOrder();
        $this->eventManager->dispatch(
            'sales_order_payment_authorize',
            ['payment' => $payment, 'amount' => $amount]
        );
        $order = $payment->getOrderDetails();
        $ccCustomerName = $payment->getAdditionalInformation('cc_customer_name');
        $ccType = $payment->getAdditionalInformation('cc_type');
        $ccNumber = $payment->getAdditionalInformation('cc_number');
        $ccExpMonth = $payment->getAdditionalInformation('cc_exp_month');
        $ccExpYear = $payment->getAdditionalInformation('cc_exp_year');
        $ccCid = $payment->getAdditionalInformation('cc_cid');
        $expDate = $ccExpYear.$ccExpMonth;
        //throw new \Magento\Framework\Exception\LocalizedException(__(' Payment Data :: '.$ccCustomerName.' :: '.$ccNumber.' :: '.$ccCid)); return $this; die;
        // Call your custom payment API
        $Apiresponse = $this->callCustomPaymentApi($order, $amount);
        
        if(!empty($Apiresponse)){
            $response = json_decode($Apiresponse,TRUE);
            if(!empty($response['paymentOrderId'])){
                $payment->setTransactionId($response['paymentOrderId']);
                $payment->setIsTransactionClosed(0);

                // Capture Process
                $CompletionResponse = $this->capturePayment($order,$response['paymentOrderId'], $amount, $ccCustomerName, $ccNumber, $ccCid, $expDate);
                
                if(!empty($CompletionResponse)){
                    $Cresponse = json_decode($CompletionResponse,TRUE);
                    if(!empty($Cresponse['response']['paymentOrderId'])){ 
                        $payment->setTransactionId($Cresponse['response']['paymentOrderId']);
                        return $this;
                    }
                    else {
                       throw new \Magento\Framework\Exception\LocalizedException(__(' Payment Completion error')); return $this; die;
                    }
                }
            }
            else {
                throw new \Magento\Framework\Exception\CouldNotDeleteException(__(' Payment authorization error :: Transcation ID not found.')); return $this; die;
            }
        }
        else{
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__('Payment authorization error.'));
        }
        return $this;
    }

    protected function getTokenApi($api_url){
        /* Get token from API */
        return $this->curlCall('integration/auth/token', "", "");
        /* Get token from API */
    }

    protected function callCustomPaymentApi($order, $amount)
    {
        $response = $tokenresponse = array();
        $api_url =  $this->getConfigData('api_url'); //'https://epipaymentgateway.com:8081/';
        $tokenresponse = $this->getTokenApi($api_url);
        
        $array = json_decode($tokenresponse,TRUE);

        if(!empty($array)){
            if(!empty($array['token'])){
                $this->setEpilog($order->getIncrementId(),'integration/auth/token',$tokenresponse,"No post data",1);
                $token = $array['token'];
                
                /* Create Order API */

                $createPostData = '{
                    "orderId": "'.$order->getIncrementId().'",
                    "amount": '.$amount.',
                    "redirectUrl": "https://josephsbeverage-teststore.epicommercestore.com/",
                    "mid": "RCTST0000058180",
                    "termId": "00000001",
                    "txnType": "Authorization"
                }';
                
                $response = $this->curlCall('createOrder', $token, $createPostData);
                
                if(!empty($response)){
                    $Apiresponse = json_decode($response,TRUE);
                    if(!empty($Apiresponse['paymentOrderId'])){
                        $this->setEpilog($order->getIncrementId(),'create order',$response,$createPostData,1);
                    }
                    else{
                        $this->setEpilog($order->getIncrementId(),'create order',$response,$createPostData,0);
                    }
                }
            }
            else{
                $this->setEpilog($order->getIncrementId(),'integration/auth/token',$tokenresponse,"No post data",0);
            }
        }
        return $response;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    { 
        $payment->setIsTransactionClosed(true);
        return $this;
    }

    protected function capturePayment($order,$transactionId, $amount, $ccCustomerName, $ccNumber, $ccCid, $expDate)
    { 
        // Example of API call using cURL
        $tokenresponse = array(); $response = array();

        $orderId = $order->getIncrementId();
        $customerName = $order->getCustomerFirstname();

        $api_url =  $this->getConfigData('api_url'); //'https://epipaymentgateway.com:8081/';

        /* Tokenize card  using tokenex api */
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $this->getConfigData('tokenex_api_url'), //'https://test-api.tokenex.com/v2/Pci/Tokenize',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "data":'.$ccNumber.',
            "cvv":'.$ccCid.'
        }',
        CURLOPT_HTTPHEADER => array(
            'tx-token-scheme: '.$this->getConfigData('token_scheme'),
            'tx-tokenex-id: '.$this->getConfigData('token_id'),
            'tx-apikey: '.$this->encryptor->decrypt($this->getConfigData('token_api_key')),
            'Content-Type: application/json'
        ),
        ));

        $tokenizeresponse = curl_exec($curl);

        curl_close($curl);

        /* Tokenize card  using tokenex api */

        /* Get token from API */

        $tokenresponse = $this->getTokenApi($api_url);
        
        /* Get token from API */
        $tokenArray = json_decode($tokenizeresponse,TRUE);
        $array = json_decode($tokenresponse,TRUE);
        if(!empty($array) && !empty($tokenArray)){
            if(!empty($array['token']) && !empty($tokenArray['token'])){
                $token = $array['token'];
                $tokencard = $tokenArray['token'];
                $this->setEpilog($orderId,'tokenex_api',$tokenizeresponse,"Secured data",1);

                /* Payment Authorization */
                
                $authReqData = '{
                    "paymentOrderId": "'.$transactionId.'",
                    "name": "'.$ccCustomerName.'",
                    "cardNumber": "'.$tokencard.'",
                    "expiryDate": "'.$expDate.'",
                    "cardType": "Visa",
                    "eCommTxnInd": "03",
		    "isCCVPresent": true
                }';

                $Authresponse = $this->curlCall('auth', $token, $authReqData);
                
                /* Payment Authorization */
                $array = json_decode($Authresponse,TRUE);
                if(!empty($array)){
                    if(!empty($array['response']['transactionId'])){

                        $this->setEpilog($orderId,'payment auth',$Authresponse,$authReqData,1);

                        $transactionId = $array['response']['transactionId'];

                        /* Payment completion API */

                        $orderCompleteReq = '{
                            "transactionId": "'.$transactionId.'"
                        }';

                        $response = $this->curlCall('completion', $token, $orderCompleteReq);

                        if(!empty($response)){
                            $Cresponse = json_decode($response,TRUE);
                            if(!empty($Cresponse['response']['paymentOrderId'])){  
                               $this->setEpilog($orderId,"Order Completion",$response,$orderCompleteReq,1);
                            }
                            else{
                                $this->setEpilog($orderId,"Order Completion",$response,$orderCompleteReq,0);
                            }
                        }

                        /* Payment completion API */
                    }
                    else{
                        $this->setEpilog($orderId,'payment auth',$Authresponse,"No transaction ID",0);
                        throw new \Magento\Framework\Exception\LocalizedException(__("Not getting payment auth data")); return $this;
                    }
                    return $response;
                }
            }
            else{
                $this->setEpilog($orderId,'tokenex_api',$tokenizeresponse,"Secured data",0);
                throw new \Magento\Framework\Exception\LocalizedException(__("Not getting tokenize data")); return $this;
            }
        }
        else{
            throw new \Magento\Framework\Exception\LocalizedException(__("Not getting tokenize Array.")); return $this;
        }
    }
    /* Refund API */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        // Get the transaction ID
        $transactionId = $payment->getParentTransactionId();

        $order = $payment->getOrder();
        $orderID = $order->getIncrementId();
        
        // Call your custom payment API to refund the payment
        $response = $this->callRefundApi($orderID, $amount); 
	if(!empty($response)){
            $Apiresponse = json_decode($response,TRUE);
            if(!empty($Apiresponse['response']['paymentOrderId'])){
                $payment->setTransactionId($Apiresponse['response']['paymentOrderId']);
                $payment->setIsTransactionClosed(1);
                $payment->setShouldCloseParentTransaction(1);
            } else {
                 throw new \Magento\Framework\Exception\LocalizedException(__('Refund error: ' . $response['message']));
		// throw new \Magento\Framework\Exception\LocalizedException(__('Refund error: ' . var_dump($response)));
            }
        }
        
        return $this;
    }

    protected function callRefundApi($orderID, $amount)
    {
        $response = $tokenresponse = array();
        $paymentOrderId = 0;
        $epiLogCollection = $this->_modelEpiLogFactory->create()->getCollection()
        ->addFieldToSelect('*')
        ->addFieldToFilter('orderID', $orderID)
        ->addFieldToFilter('apiAction', 'payment auth');
        $epiData = $epiLogCollection->getData();

        if (!empty($epiData)) {
            if ($epiData[0]['responseStatus'] == 1 && !empty($epiData[0]['requestData'])) {
                $paymentData = json_decode($epiData[0]['requestData'], TRUE);
                $paymentOrderId = $paymentData['paymentOrderId']; 

                $api_url =  $this->getConfigData('api_url'); //'https://epipaymentgateway.com:8081/';
                $tokenresponse = $this->getTokenApi($api_url);

                /* Get token from API */
                $array = json_decode($tokenresponse,TRUE);
                if(!empty($array)){
                    if(!empty($array['token'])){
                        $token = $array['token'];
                        $refundData = '{
                            "paymentOrderId": "'.$paymentOrderId.'",
                            "eCommTxnInd": "03",
                            "refundAmount": '.$amount.'
                        }';
                        $response = $this->curlCall('refund', $token, $refundData);
                        if(!empty($response)){
                            $Apiresponse = json_decode($response,TRUE);
                            if(!empty($Apiresponse['response']['paymentOrderId'])){
                                $this->setEpilog($orderID,'refund',$response,$refundData,1);
                            }
                            else{
                                $this->setEpilog($orderID,'refund',$response,$refundData,0);
                            }
                        }
                    }
                }
            }
        }
        return $response;
    }

    /* Cancel Order */

    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        // Get the order associated with the payment
        $order = $payment->getOrder();
        $orderID = $order->getIncrementId();
        $amount = $order->getGrandTotal();
        
        // Call your custom payment API to cancel the payment
        $response = $this->callCancelApi($orderID, $amount);
        
        if(!empty($response)){
            $Apiresponse = json_decode($response,TRUE);
            if(!empty($Apiresponse['response']['paymentOrderId'])){

                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
                ->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
            
                // Add a comment to the order history
                $order->addStatusHistoryComment(__('Order has been cancelled.'));
            
                // Save the order
                $order->save();
            }
        }
        return $this;
    }

    protected function callCancelApi($orderID, $amount)
    {
        $response = $tokenresponse = array();
        $epiLogCollection = $this->_modelEpiLogFactory->create()->getCollection()
        ->addFieldToSelect('*')
        ->addFieldToFilter('orderID', $orderID)
        ->addFieldToFilter('apiAction', 'Order Completion');
        $epiData = $epiLogCollection->getData();
        if (!empty($epiData)) {
            if ($epiData[0]['responseStatus'] == 1 && !empty($epiData[0]['requestData'])) {
                $paymentData = json_decode($epiData[0]['requestData'], TRUE);
                $transactionId = $paymentData['transactionId']; 
            }
        }
        $api_url =  $this->getConfigData('api_url'); //'https://epipaymentgateway.com:8081/';

        $tokenresponse = $this->getTokenApi($api_url);
        
        /* Get token from API */
        $array = json_decode($tokenresponse,TRUE);
        if(!empty($array)){
            if(!empty($array['token'])){
                $token = $array['token'];

                $cancelReqData = '{
                    "transactionId": "'.$transactionId.'",
                    "eCommTxnInd": "03",
                    "txnAmt": '.$amount.',
                    "eCommUrl": "www.abc.com",
                    "txnType": "Authorization"
                }';

                $response = $this->curlCall('void', $token, $cancelReqData);
                if(!empty($response)){
                    $Apiresponse = json_decode($response,TRUE);
                    if(!empty($Apiresponse['response']['paymentOrderId'])){
                        $this->setEpilog($orderID,'Cancel',$response,$cancelReqData,1);
                    }
                    else{
                        $this->setEpilog($orderID,'Cancel',$response,$cancelReqData,0);
                    }
                }
            }
        }
        return $response;
    }

    /* Cancel Order */
    public function canCancel()
{
    // Implement your custom logic to determine if the payment can be cancelled
    return true;
}

    public function setEpilog($orderID,$apiAction,$responseData,$requestData,$responseStatus)
    {
        $EpiLogModel = $this->_modelEpiLogFactory->create();
        $EpiLogModel->setData('orderID', $orderID);
        $EpiLogModel->setData('apiAction', $apiAction);
        $EpiLogModel->setData('requestData', $requestData);
        $EpiLogModel->setData('returnData', $responseData);
        $EpiLogModel->setData('responseStatus', $responseStatus);
        $EpiLogModel->save();
    }

    public function curlCall($apiAction, $headerToken, $postData){
        
        $api_url =  $this->getConfigData('api_url'); //'https://epipaymentgateway.com:8081/';
        if($apiAction == 'integration/auth/token'){
            
            $encrypted_token_key = $this->getConfigData('token_key');
            $token_key =  $this->encryptor->decrypt($encrypted_token_key);

            $encrypted_token_secrete = $this->getConfigData('token_secret');
            $token_secret =  $this->encryptor->decrypt($encrypted_token_secrete);
            
            $headerData = array(
                'x-auth-id: '.$token_key,
                'x-auth-secret: '.$token_secret
            );
        }
        else
        {
            $headerData = array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$headerToken
            );
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $api_url.$apiAction,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headerData,
        ));

        $response = curl_exec($curl);
        
        curl_close($curl);
        return $response;
    }
    public function validateCardNumber($cardNumber) {
        $cardNumber = preg_replace('/\D/', '', $cardNumber); // Remove non-digit characters
        $sum = 0;
        $alt = false;
    
        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $n = (int) $cardNumber[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $alt = !$alt;
        }
    
        return ($sum % 10 === 0);
    }
    public function validateExpiryDate($expiryMonth, $expiryYear) {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
    
        if ($expiryYear < $currentYear || ($expiryYear == $currentYear && $expiryMonth < $currentMonth)) {
            return false;
        }
    
        return true;
    }
    public function validateCVV($cvv, $cardType = 'generic') {
        if (!ctype_digit($cvv)) {
            return false;
        }
    
        $cvvLength = strlen($cvv);
    
        // Basic check for common card types
        if ($cardType === 'amex') {
            return $cvvLength === 4;
        }
    
        return $cvvLength === 3;
    }
            
}
