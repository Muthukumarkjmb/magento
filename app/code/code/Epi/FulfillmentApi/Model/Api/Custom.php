<?php
namespace Epi\FulfillmentApi\Model\Api;
use Psr\Log\LoggerInterface;
use \Zend\Json\Json;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderInterface as OrderInterface;
use \Exception as Exception;
use Magento\Framework\Exception\NotFoundException as NotFoundException;
use Magento\Framework\Exception\InvalidArgumentException as InvalidArgumentException;
use \Epi\EpiPay\lib\Tax;
use Magento\Directory\Model\RegionFactory;
use \Epi\FulfillmentApi\Model\LCAPIResponse;

class Custom
{
    protected $regionFactory;
    protected $resourceConnection;
    private $tax;

    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \Zend\Http\Client $zendClient,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        RegionFactory $regionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Tax $tax
    )
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->request = $request;
        $this->zendClient = $zendClient;
        $this->timezone = $timezone;
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Fulfillment.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $this->regionFactory = $regionFactory;
        $this->ini = parse_ini_file(__DIR__ ."$ds..$ds..$ds/lib/config.ini");
        $this->resourceConnection=$resourceConnection;
        $this->tax=$tax;
    }
    /**
     * @inheritdoc
     */
    private function paymentApiCall($route,$payload){
        // $this->logger->info('Inside shipmentApiCall');
        // $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/api.log');
        // $this->logger = new \Zend_Log();
        // $this->logger->addWriter($writer);
        // -------------------------------ZEND---------------------
        try 
        {
            $this->zendClient->reset();
            $this->zendClient->setUri($this.ini['paymentUrl'].$route);
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
            $this->zendClient->setHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            ]);
            // $this->logger->info(Json::encode($payload));
            $this->zendClient->setRawBody(Json::encode($payload));
            // $this->zendClient->setRawBody($payload);
            $this->logger->info('Client->'.print_r($this->zendClient,true));
            
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $response=$response->getBody();
            $response=json_decode($response);  			
            $this->logger->info('Response From POST Call->'.print_r($response,true));
            
            return $response;
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Completion POST call->'.print_r($runtimeException->getMessage(),true));
            return false;
        }
        // --------------------------------ZEND---------------
    }

    private function sendOrderUpdateSMS($mobile,$message){
        try{
            $this->logger->info('send order update sms');
            $this->zendClient->reset();
            $this->zendClient->setUri($this->ini['fulfilmentAPIURL']);
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
            $apiData['mobile']=$mobile;
            $apiData['message']=$message;
            $this->zendClient->setRawBody(Json::encode($apiData));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response from Order update SMS Api-->'.print_r($response->getBody(),true));
            return $response->getBody();
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            $this->logger->info('Error in Order update SMS Api->'.print_r($runtimeException->getMessage(),true));
            return $runtimeException->getMessage();
        }
    }
    private function getOrderDetailsFromXT($order){
        $this->logger->info('get order details from XT function==>'.print_r($order->getState(),true));
        $items = $order->getItemsCollection();
        $xtmid = $order->getXtmid();
        $ItemDetaislArray = [];
        foreach($items as $item){
            $url = $this->ini['ExaTouchItemsRestAPI'].$xtmid."/"."sku/";
            array_push($ItemDetaislArray,$this->callXTGetItemDetaislAPI($url.$item->getSku()));
        }
        return $ItemDetaislArray;
    }
    private function callXTGetItemDetaislAPI($url){
        try{
                $this->logger->info('get order details from XT function==>'.$url);
                $this->zendClient->reset();
                $this->zendClient->setUri($url);
                $this->zendClient->setOptions(array('timeout'=>30));
                $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET); 
                $this->zendClient->setHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Auth-Key' => '3rzonriaG1IJcgk/+blNjsvWLVuyp0oZAsIeeAJ6ZmzCBwBIYAZbeKBdQb2oZRjygs8KQE1aq4fV0idWnp4CpqmJFTAREJkLDV34mxEvqB0='
                ]);
                $this->zendClient->send();
                $response = $this->zendClient->getResponse();
                $this->logger->info('Response Code from Exatouch Api->'.print_r($response->getStatusCode(),true));
                $formattedResponse=json_decode($response->getBody(),true);
                if($response->getStatusCode()!=200 || count($formattedResponse)===0)
                {
                    $formattedResponse=[];
                }
                else{
                    $formattedResponse=$formattedResponse[0];
                }
                $this->logger->info('Formatted response->'.print_r($formattedResponse,true));
                return $formattedResponse;
            }
            catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
            {
                $this->logger->info('Error in Exatouch Api->'.print_r($runtimeException->getMessage(),true));
            }
    }
    private function getObjectToSendOrderDataToXT($order, $XTItemsDetails,$orderedItemData,$totalTaxAmt){
        try{
            $this->logger->info('get object to send data to xt function==>');
            $orderType=2; //For pickup
            if($order->getShippingMethod()=='flatrate_flatrate'){
                $orderType=3; //For delivery
            }
            $dataObject = [];
            $dataObject["asap"] = true;
            $dataObject["orderno"] =  $order->getIncrementId();//set magento order id
            $dataObject["merchantNumber"] = $order->getXtmid();
            $dataObject["datetime"] = $order->getCreatedAt();
            $dataObject["ordertype"] = $orderType;
            $dataObject["receiptNumber"] = "";
            $dataObject["subtotal"] = round($order->getSubtotal(),2);
            $dataObject["deliveryFee"] = (float)$order->getShippingAmount();
            $dataObject["serviceFee"] = round($order->getPaymentFee(),2);
            $dataObject["convenienceFee"] = 0.00;
            $dataObject["tip"] = 0.00;
            $dataObject["totalAmount"] = round($order->getGrandTotal(),2);
            $dataObject["deliveryInstructions"] = "";
            $dataObject["items"] = $this->getItemsArray($XTItemsDetails, $order,$orderedItemData);
            // $dataObject["paymethods"] = $this->getPaymentMethodArray($order);
            $dataObject["customerInformation"] = $this->getCustomerInformationArray($order);
            $dataObject["orderSource"]="LiquorCart";
            $dataObject["tax"]=round((float)$totalTaxAmt,2);

            return $dataObject;
        }
        catch(Exception $e){
            $this->logger->info('Error in Making Object to send to XT->'.print_r($e->getMessage(),true));
        }
    }
    private function getItemsArray($XTItemsDetails, $order,$orderedItemData){
        try{
            // $this->logger->info('get items array ==>'.print_r($XTItemsDetails).'<==order==>'.print_r($order->getState()));
            $objects = [];
            foreach($XTItemsDetails as $itemDetails){  
                $qty=1;
                $taxAmnt=0;
                $taxCode=null;
                foreach($orderedItemData as $orderedItem) {
                    if($orderedItem['sku']==$itemDetails['SKU']){
                        $qty=$orderedItem['qty'];
                        $taxAmnt=$orderedItem['tax'];
                        $taxCode=$orderedItem['taxCode'];
                    }
                }    
                array_push($objects,
                    (object) [
                        "itemid"=> (int)$itemDetails["ItemID"],
                        "categoryid"=> $itemDetails["CatID"],
                        "price"=> (float)$itemDetails["Price"],
                        "cost"=> (float)$itemDetails["Cost"],
                        "qty"=> (int)$qty,
                        "maxqty"=> 99,
                        "minqty"=> (int)$itemDetails["MinQty"],
                        "item86"=> false,
                        "title"=> $itemDetails["Description"],
                        "desc"=> $itemDetails["Description"],
                        "comments"=> null,
                        "taxAmount"=> round((float)$taxAmnt,2),
                        "taxCode"=>$taxCode,
                        "taxPercent"=> 0,
                        "ageVerificationText"=> "",
                        "ageVerificationRequired"=> false,
                        "minimumAgeRequired"=> 0,
                        "sides"=> [],
                        "options"=> [],
                        "modifiers"=> [],
                        "side_groups"=> [],
                        "seat_number"=> 1
                    ]
                );
            }
            // $this->logger->info('get items array ==>'.print_r($objects));
            return $objects;
        }
        catch(Exception $e){
            $this->logger->info('Error in Getting Items Array->'.print_r($e->getMessage(),true));
        }
    }

    private function addPaymentDetailsToOrderDetails($order, $orderDetailsObject, $paymentDetails=null){
        // $this->logger->info('Get payment details'.print_r($paymentDetails));

        try{
            $objects = [];
            $cardHolderName = explode(" ",$paymentDetails->response->cardHolderName,2);
            
            if ($paymentDetails!=null) {
                $objects[] = (object) [
                    "firstname"=> $cardHolderName[0],
                    "lastname"=> $cardHolderName[1],
                    "amount"=> round($order->getGrandTotal(),2),
                    "cardnumber"=> $paymentDetails->response->cardNumber,
                    "cc_type"=> $paymentDetails->response->cardType,
                    "transactionIdentifer"=> $order->getPayment()->getAdditionalInformation('transactionId'),
                    "approval_code"=> $paymentDetails->response->AuthID,
                    "token"=> "",
                    "batchNumber"=> "01"
                ];
            } else {
                $objects[] = (object) [
                    "firstname"=> $cardHolderName[0],
                    "lastname"=> $cardHolderName[1],
                    "amount"=> round($order->getGrandTotal(),2),
                    "cardnumber"=> "",
                    "cc_type"=> "",
                    "transactionIdentifer"=> $order->getPayment()->getAdditionalInformation('transactionId'),
                    "approval_code"=> "",
                    "token"=> "",
                    "batchNumber"=> "01"
                ];
            }

            $orderDetailsObject["paymethods"] = $objects;
            // $this->logger->info('get payment method items array ==>'.print_r($objects));
            return $orderDetailsObject;
        }
        catch(Exception $e){
            $this->logger->info('Error in get payment method array->'.print_r($e->getMessage(),true));
        }
    }

    private function getCustomerInformationArray($order){
         try{
            // $this->logger->info('get customer information array ==>'.print_r($order->getState()));
            $objects = [];
            $shippingaddress = $order->getShippingAddress();             
            $shippingtelephone = $shippingaddress->getTelephone();
            $billing = $order->getBillingAddress();

            $billingAddress = $order->getBillingAddress()->getData();
            $region = $this->regionFactory->create()->load($billingAddress['region_id']);
            $regionData=$region->getData();
            // $this->logger->info("Region->".print_r($regionData['code'], true));
            
             //fetching delivery distance from the database
            $deliveryDistance = 0;
            if($order->getShippingMethod() != "amstorepickup_amstorepickup"){
                $incrementId = $order->getIncrementId();
                $distanceFetchQuery = 'SELECT distance FROM delivery_distance WHERE order_increment_id ='.$incrementId;
                $deliveryDistance = $this->resourceConnection->getConnection()->fetchAll($distanceFetchQuery)[0]['distance'];
            } 
            $this->logger->info('The delivery distance is '.print_r($deliveryDistance,true));
            
            $objects[] = (object) [
                "_id"=> "",
                "firstname"=> $order->getCustomerFirstname(),
                "lastname"=> $order->getCustomerLastname(),
                "phone"=> $shippingtelephone,
                "street1"=> $billing->getStreetLine(1),
                "street2"=> $billing->getStreetLine(2),
                "city"=> $billing->getCity(),
                "state"=> $regionData['code'], //$billing->getRegion()
                "postalcode"=> $billing->getPostcode(),
                "carrier"=> "",
                "email"=> $billing->getEmail(),
                "deliveryDistance"=> $deliveryDistance
            ];
            // $this->logger->info('get customer information array ==>'.print_r($objects[0]));
            return $objects[0];
        }
        catch(Exception $e){
            $this->logger->info('Error in get customer information array->'.print_r($e->getMessage(),true));
        }
    }

    private function sendOrderDetailsToXT($orderDetailsObject){
        try{
            $xtmid = $orderDetailsObject["merchantNumber"];
            $this->logger->info('send order details to xt  ==>'.print_r(Json::encode($orderDetailsObject), true));
            $this->zendClient->reset();
            $this->zendClient->setUri($this->ini['XTOrderPlacementAPI'].$xtmid);
            $this->zendClient->setOptions(array('timeout'=>30));
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST); 
                $this->zendClient->setHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Auth-Key' => '3rzonriaG1IJcgk/+blNjsvWLVuyp0oZAsIeeAJ6ZmzCBwBIYAZbeKBdQb2oZRjygs8KQE1aq4fV0idWnp4CpqmJFTAREJkLDV34mxEvqB0='
                ]);
            $this->zendClient->setRawBody(Json::encode($orderDetailsObject));
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            $this->logger->info('Response From send order details to xt->'.print_r($response->getBody(),true));
            // $this->logger->info('Response From send order details to xt->'.print_r(gettype($response->getBody()),true));
            return $response->getBody();
        }
        catch(Exception $e){
            $this->logger->info('Error in send Order details to XT->'.print_r($e->getMessage(),true));
            return NULL;
        }
    }

    private function getAllPhoneNumbers($primaryNumber, $AllSecondaryNumbers, $sender) {
        $phoneNumberArr = [$primaryNumber];

        if (isset($AllSecondaryNumbers) and trim($AllSecondaryNumbers)!="") {
            $secondaryNumbers = explode(",",$AllSecondaryNumbers);

            # checking if replied number is in secondary numbers, if yes, filter it out.
            $filteredSecondaryNumbers = array_filter($secondaryNumbers,function($number) {
                return $sender!=trim($number);
            });

            $phoneNumberArr = array_merge($phoneNumberArr,$filteredSecondaryNumbers);
        }

        return $phoneNumberArr;
    }

    private function sendSMSToMerchant($phoneNumbers, $message) {
        $this->logger->info('Logging paramters of sendSMSToMerchant--> '.print_r($phoneNumbers, true));
        $this->logger->info('Logging paramters of sendSMSToMerchant--> '.print_r($message, true));
        
        for ($numIndex=0; $numIndex<count($phoneNumbers); $numIndex++){
            $SMSResponse=$this->sendOrderUpdateSMS($phoneNumbers[$numIndex],$message);
            $this->logger->info("Response from sendSMSToMerchant for sms api ".print_r($SMSResponse,true));
        }

    }

    public function getTablename($tableName)
    {
        /* Create Connection */
        $connection  = $this->resourceConnection->getConnection();
        $tableName   = $connection->getTableName($tableName);
        return $tableName;
    }

    public function getPost($data)
    {
        $this->logger->info('<-----Fulfillment Api----->');
        // $response = ['success' => false];
        try {
            // Your Code here
            if (!isset($data[0]['orderId'])) {
                throw new InvalidArgumentException(__("OrderId not found."));
            }

            if (!isset($data[0]['merchantResponse'])) {
                throw new InvalidArgumentException(__("Merchant response not found."));
            }

            if ($data[0]['merchantResponse'] != 'C' and $data[0]['merchantResponse'] != 'R') {
                throw new InvalidArgumentException(__("Invalid merchant response."));
            }

            if (!isset($data[0]['sender'])) {
                throw new InvalidArgumentException(__("Merchant Number not found"));
            }
            
            $orderId=$data[0]['orderId'];
            $this->logger->info('OrderID->'.print_r($data[0],true));
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
            $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
            $this->logger->info('Order->'.print_r(count(get_object_vars($order)),true));
            if (!$order->getState()) {
                throw new NotFoundException(__("Order not found."));
            }
            $orderStatus=$order->getStatus();
            if ( $orderStatus == 'complete' ||  $orderStatus == 'closed' ||  $orderStatus == 'canceled' ||  $orderStatus == 'confirmed') {
                throw new Exception("This order has already marked as ".$orderStatus.".");
            }

            $paymentMethod = $order->getPayment()->getMethod();
            $transactionId = $order->getPayment()->getAdditionalInformation('transactionId');
            $txnAmount=round($order->getPayment()->getAmountOrdered(),2);

            //Get merchant and customer phone number
            $merchantId=$order->getMid();
            $amastyTableName = $this->getTableName('amasty_amlocator_location');
            $amastyStoreAttributeTableName = $this->getTableName('amasty_amlocator_store_attribute');
            $query='SELECT '.$amastyTableName.'.phone, '.$amastyTableName.'.secondary_phone FROM '.$amastyTableName.' WHERE '.$amastyTableName.'.id=(SELECT  '.$amastyStoreAttributeTableName.'.store_id FROM  '.$amastyStoreAttributeTableName.' WHERE  '.$amastyStoreAttributeTableName.'.value="'.$merchantId.'")';
            $merchantMobile = $this->resourceConnection->getConnection()->fetchAll($query)[0]['phone'];
            $merchantSecondaryPhoneNumbers = $this->resourceConnection->getConnection()->fetchAll($query)[0]['secondary_phone'];
            $customerMobile=$order->getShippingAddress()->getTelephone();
            $this->logger->info('Merchant phone->'.print_r($merchantMobile,true));
            $this->logger->info('Merchant Secondary phone->'.print_r($merchantSecondaryPhoneNumbers,true));
            $this->logger->info('Customer Phone->'.print_r($customerMobile,true));

            $this->logger->info('Order state->'.print_r($order->getState(),true));
            $this->logger->info('Order payment method->'.print_r($paymentMethod,true));
            $this->logger->info('Order payment transactionId->'.print_r($transactionId,true));
            $this->logger->info('get xtmid in fulfillment ->'.print_r($order->getXtmid(), true));
           
            $ds = DIRECTORY_SEPARATOR;
            include __DIR__ . "$ds..$ds..$ds/lib/Epi.php";
            #initialize Epi class object				
            $api = new \Epi();

            $tax_api_url=$this->ini['ExaTouchTaxRestAPI'].$order->getXtmid()."/"."itemId/";
            
            if($data[0]['merchantResponse']=='C' and $order->getState() != 'complete')
            {   
                $api_data['transactionId']=$transactionId;
                $api_data['txnAmt']=$txnAmount;
                $api_data['eCommUrl']="www.abc.com";
                $api_data['eCommTxnInd']="03";	
                // $this->paymentApiCall('completion',$api_data);

                if($order->getPayment()->getMethod()=='epi'){
                    $responseFromRapidConnect = $api->completePayment($api_data);
                    $this->logger->info("Response from RapidConnect Completion=>".print_r($responseFromRapidConnect , true));
                    if (count(get_object_vars($responseFromRapidConnect)) == 0) {
                        throw new Exception("Could not complete payment.", 500);
                    }
                    $responseCode=$responseFromRapidConnect->response->RespCode;
                    $orderedItemData=[];
                    $totalTaxAmt=0;
                    if($responseCode == 000)
                    {
                        $XTItemsDetails = $this->getOrderDetailsFromXT($order);
                        $this->logger->info("Response from XT getItem details api call=>".print_r($XTItemsDetails,true));	
                        if(count($XTItemsDetails) > 0){
                            $validQty=true;
                            $validResponse=true;                           
                            foreach($XTItemsDetails as $XTItem){
                                if(count($XTItem)===0){
                                    $validResponse=false;
                                }
                                else if($XTItem['QtyOnHand']<=0){
                                    $validQty=false;
                                }  
                                else if(count($XTItem)!=0)   {
                                    $itemTaxData=$this->tax->getDataFromXt($tax_api_url.$XTItem['ItemID']."/tax");
                                    if(count($itemTaxData)!=0){
                                        $itemTaxAmnt=$this->tax->calculateTax($itemTaxData,$XTItem['Price']);
                                        $orderedItems=$order->getAllItems();
                                        $itemQty=1;
                                        $sku=$XTItem['SKU'];
                                        foreach ($orderedItems as $orderedItem){
                                            if($orderedItem->getSku()==$sku){
                                                $itemQty=$orderedItem->getQtyOrdered();
                                            }
                                        }
                                        $itemTotalTax=$itemTaxAmnt*$itemQty;
                                        $totalTaxAmt+=$itemTotalTax;
                                        array_push($orderedItemData,['sku'=>$sku,'tax'=>$itemTotalTax,'qty'=>$itemQty,'taxCode'=>$itemTaxData['TaxCode']]);
                                    }
                                }                        
                            }
                            if($validQty && $validResponse){
                                
                                $orderDetailsObject = $this->getObjectToSendOrderDataToXT($order, $XTItemsDetails,$orderedItemData,$totalTaxAmt);
                                $orderDetailsObject = $this->addPaymentDetailsToOrderDetails($order, $orderDetailsObject, $responseFromRapidConnect);
                                $this->logger->info("Log Order Detail Object".print_r($orderDetailsObject,true));
                                $xtResponse = $this->sendOrderDetailsToXT($orderDetailsObject);
                                $xtResponse = json_decode($xtResponse);
                                if ($xtResponse->Success == 1 && isset($xtResponse->OrderID)) {
                                    
                                    $storeOrderId = $xtResponse->OrderID;
                                    $order->setStoreOrderId($storeOrderId);

                                    if(isset($customerMobile)){
                                        $customerMessage='Merchant has accepted your order #'.$orderId.'(Store Order id:#'.$xtResponse->OrderID.').🍻';
                                        $this->logger->info('This is customer message...'.$customerMessage);
                                        $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                                        $this->logger->info("Response from order confirm sms api for customer".print_r($customerSMSResponse,true));
                                    } 
    
                                    if (isset($merchantMobile)) {
                                        # getting all the phone number of the merchant
                                        $phoneNumberArr = $this->getAllPhoneNumbers($merchantMobile, $merchantSecondaryPhoneNumbers, $data[0]['sender']);
                                        $this->logger->info("Filerted Merchant Phone numbers in Custom for confirmed order->".print_r($phoneNumberArr , true));
    
                                        $message = 'The Order #'.$orderId.'(Store Order id:#'.$xtResponse->OrderID.')has been accepted.';
                                        $this->sendSMSToMerchant($phoneNumberArr, $message);
                                    }
                                }
                                $order->setState(Order::STATE_HOLDED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
                               
                            }
                            else if(!$validQty){
                                $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                                if(isset($customerMobile)){
                                    $customerMessage="Order #'.$orderId.' not placed. Contact store for details.";
                                    $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                                    $this->logger->info("Response from order update sms api for customer".print_r($customerSMSResponse,true));	
                                }
                                if(isset($merchantMobile)){
                                    $merchantMessage="Order #'.$orderId.' not placed. Check inventory level.";
                                    $merchantSMSResponse=$this->sendOrderUpdateSMS($merchantMobile,$merchantMessage);
                                    $this->logger->info("Response from order update sms api for merchant (insufficient qty)".print_r($merchantSMSResponse,true));	
                                }
                                
                            }
                            else if(!$validResponse){
                                $order->setState(Order::STATE_HOLDED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
                                if(isset($merchantMobile)){
                                    $merchantMessage="Order #'.$orderId.' processed online but unable to place order on POS. Verify and process accordingly.";
                                    $merchantSMSResponse=$this->sendOrderUpdateSMS($merchantMobile,$merchantMessage);
                                    $this->logger->info("Response from order update sms api for merchant (invalid response)".print_r($merchantSMSResponse,true));	
                                }
                            }
                        
                        } else {
                            throw new Exception("No response from XT, order has not been placed on XT", 500);
                        }
                        
                        $order->save();
                        $response = new LCAPIResponse();
                        $response->setSuccess(true);
                        $response->setMessage('Order has been confirmed.');
                        // $response = ['success' => true, 'message' => 'Order has been confirmed.'];
                    }
                }
                else{
                        $XTItemsDetails = $this->getOrderDetailsFromXT($order);

                        $this->logger->info("Response from XT getItem details api call=>".print_r($XTItemsDetail_____s,true));	
                        if(count($XTItemsDetails) > 0){
                            $validQty=true;
                            $validResponse=true;
                            foreach($XTItemsDetails as $XTItem){
                                if(count($XTItem)===0){
                                    $validResponse=false;
                                }
                                else if($XTItem['QtyOnHand']<=0){
                                    $validQty=false;
                                }  
                                else if(count($XTItem)!=0)   {
                                    $itemTaxData=$this->tax->getDataFromXt($tax_api_url.$XTItem['ItemID']."/tax");
                                    if(count($itemTaxData)!=0){
                                        $itemTaxAmnt=$this->tax->calculateTax($itemTaxData,$XTItem['Price']);
                                        $orderedItems=$order->getAllItems();
                                        $itemQty=1;
                                        $sku=$XTItem['SKU'];
                                        foreach ($orderedItems as $orderedItem){
                                            if($orderedItem->getSku()==$sku){
                                                $itemQty=$orderedItem->getQtyOrdered();
                                            }
                                        }
                                        $itemTotalTax=$itemTaxAmnt*$itemQty;
                                        $totalTaxAmt+=$itemTotalTax;
                                        array_push($orderedItemData,['sku'=>$sku,'tax'=>$itemTotalTax,'qty'=>$itemQty,'taxCode'=>$itemTaxData['TaxCode']]);
                                    }
                                }                        
                            }
                            if($validQty && $validResponse){
                                $orderDetailsObject = $this->getObjectToSendOrderDataToXT($order, $XTItemsDetails,$orderedItemData,$totalTaxAmt);
                                $orderDetailsObject = $this->addPaymentDetailsToOrderDetails($order, $orderDetailsObject);
                                $xtResponse = $this->sendOrderDetailsToXT($orderDetailsObject);
                                if ($xtResponse->Success == 1 && isset($xtResponse->OrderID)) {
                                    $xtResponse = json_decode($xtResponse);
                                    $storeOrderId = $xtResponse->OrderID;
                                    $order->setStoreOrderId($storeOrderId);

                                    if(isset($customerMobile)){
                                        $customerMessage='Merchant has accepted your order #'.$orderId.'(Store Order id:#'.$xtResponse->OrderID.').🍻';
                                        $this->logger->info('This is customer message...'.$customerMessage);
                                        $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                                        $this->logger->info("Response from order confirm sms api for customer".print_r($customerSMSResponse,true));
                                    } 
    
                                    if (isset($merchantMobile)) {
                                        # getting all the phone number of the merchant
                                        $phoneNumberArr = $this->getAllPhoneNumbers($merchantMobile, $merchantSecondaryPhoneNumbers, $data[0]['sender']);
                                        $this->logger->info("Filerted Merchant Phone numbers in Custom for confirmed order->".print_r($phoneNumberArr , true));
    
                                        $message = 'The Order #'.$orderId.'(Store Order id:#'.$xtResponse->OrderID.')has been accepted.';
                                        $this->sendSMSToMerchant($phoneNumberArr, $message);
                                    }
                                }
                                $order->setState(Order::STATE_HOLDED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
                            }
                            else if(!$validQty){
                                $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                                if(isset($customerMobile)){
                                    $customerMessage="Your order #'.$orderId.' was cancelled due to insufficient stock. Sorry for the inconvinience";
                                    $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                                    $this->logger->info("Response from order update sms api for customer".print_r($customerSMSResponse,true));	
                                }
                                if(isset($merchantMobile)){
                                    $merchantMessage="Hi Merchant, the previous order #'.$orderId.' was cancelled due to insufficient stock as informed by Exatouch";
                                    $merchantSMSResponse=$this->sendOrderUpdateSMS($merchantMobile,$merchantMessage);
                                    $this->logger->info("Response from order update sms api for merchant (insufficient qty)".print_r($merchantSMSResponse,true));	
                                }
                                
                            }
                            else if(!$validResponse){
                                $order->setState(Order::STATE_HOLDED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_HOLDED));
                                if(isset($merchantMobile)){
                                    $merchantMessage="Hi Merchant, the previous order #'.$orderId.' was not placed on the Exatouch due to technical issue but is placed on the website as you have confirmed the order with us.";
                                    $merchantSMSResponse=$this->sendOrderUpdateSMS($merchantMobile,$merchantMessage);
                                    $this->logger->info("Response from order update sms api for merchant (invalid response)".print_r($merchantSMSResponse,true));	
                                }
                            }
                        } else {
                            throw new Exception("No response from XT, order has not been placed on XT", 500);
                        }
                        $order->save();
                        $response = new LCAPIResponse();
                        $response->setSuccess(true);
                        $response->setMessage('Order has been confirmed.');
                        // $response = ['success' => true, 'message' => 'Order has been confirmed.'];
                    }
            }
            else if($data[0]['merchantResponse']=='R' and $order->getState() != 'canceled'){
                $api_data['transactionId']=$transactionId;
                $api_data['txnType']='Authorization';
                $api_data['txnAmt']=$txnAmount;
                $api_data['eCommUrl']="www.abc.com";
                $api_data['eCommTxnInd']="03";

                if(isset($customerMobile)){
                    $customerMessage="Merchant is unable to fulfill your order #'.$orderId.'.\nSorry for the inconvenience 😔";
                    $customerSMSResponse=$this->sendOrderUpdateSMS($customerMobile,$customerMessage);
                    $this->logger->info("Response from order rejected sms api for customer".print_r($customerSMSResponse,true));	
                }

                if (isset($merchantMobile)) {
                    # getting all the phone number of the merchant
                    $phoneNumberArr = $this->getAllPhoneNumbers($merchantMobile, $merchantSecondaryPhoneNumbers, $data[0]['sender']);
                    $this->logger->info("Filerted Merchant Phone numbers in Custom for rejected order->".print_r($phoneNumberArr , true));

                    $message = 'The Order #'.$orderId.' has been rejected.';

                    $this->sendSMSToMerchant($phoneNumberArr, $message);
                }

                if($order->getPayment()->getMethod()=='epi'){
                    $response = new LCAPIResponse();
                    $responseFromRapidConnect = $api->voidPayment($api_data);
                    $this->logger->info("Response from RapidConnect void=>".print_r($responseFromRapidConnect , true));	
                    if (count(get_object_vars($responseFromRapidConnect)) == 0) {
                        throw new Exception("Could not void payment.", 500);
                    }
                    if($responseFromRapidConnect->response->RespCode==000){
                        $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                       
                        $order->save();
                        // $response = new LCAPIResponse();
                        $response->setSuccess(true);
                        $response->setMessage('Order has been rejected.');
                        // $response = ['success' => true, 'message' => 'Order has been rejected.'];
                    }else{
                        $response->setSuccess(false);
                        $response->setMessage('Unable to cancel/void order');
                    }
                }
                else{
                    $order->setState(Order::STATE_CANCELED)->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_CANCELED));
                    $order->save();
                    $response = new LCAPIResponse();
                    $response->setSuccess(true);
                    $response->setMessage('Order has been rejected.');
                    // $response = ['success' => true, 'message' => 'Order has been rejected.'];
                }
            }
            // $response = ['success' => true, 'message' => 'OK'];
            // $returnArray = json_encode($response);
            return $response; 
        } catch (\Exception $e) {
            throw($e);
            // $response = ['success' => false, 'message' => $e->getMessage()];
            $response = new LCAPIResponse();
            $response->setSuccess(true);
            $response->setMessage($e->getMessage());
            $this->logger->info($e->getMessage());
        }
   }
}