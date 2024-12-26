<?php
/**
 * @author  Burstonline
 */

namespace Burstonline\Epipayment\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Burstonline\Epipayment\Model\EpiLogFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;


class Ordertoxt
{
    protected $epiLogFactory;
    protected $orderRepository;
    protected $productRepository;
    protected $categoryRepository;
    protected $encryptor;
    
    protected $_scopeConfig;

    const XML_PATH_BUSINESS_NAME		= 'payment/epipayment/business_name';
    const XML_PATH_BATCH_NUMBER     	= 'payment/epipayment/batch_no';
    const XML_PATH_XT_APIURL    	= 'payment/epipayment/xtapi_url';
    const XML_PATH_XT_AUTHKEY    	= 'payment/epipayment/xtauth_key';
    const XML_PATH_SERVICE_FEE     	= 'payment/epipayment/service_fee';
    const XML_PATH_CONVENIENCE_FEE          = 'burstonline_customconfig/product_non_lmp/price';    

    public function __construct(
        EpiLogFactory $epiLogFactory,
        OrderRepositoryInterface $orderRepository,
        ProductRepository $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig 
    ) {
        $this->epiLogFactory = $epiLogFactory;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function excuteorderxt($orderId) 
    {
        // Get Order Details
       // $orderIncId = $result->getIncrementId();
       // $orderId = $result->getEntityId();
        $order = $this->orderRepository->get($orderId);
        $items = $order->getItems();
        $orderIncId = $order->getIncrementId();	
        $shippingMethod = (strtolower($order->getShippingMethod()) == 'amstorepickup_amstorepickup') ? 3 : 2;
        
        $business_name=$this->_scopeConfig->getValue(self::XML_PATH_BUSINESS_NAME,ScopeInterface::SCOPE_STORE);
        $batchno=$this->_scopeConfig->getValue(self::XML_PATH_BATCH_NUMBER,ScopeInterface::SCOPE_STORE);
        $xtApiURL=$this->_scopeConfig->getValue(self::XML_PATH_XT_APIURL,ScopeInterface::SCOPE_STORE);
        $xtAuthKey=$this->_scopeConfig->getValue(self::XML_PATH_XT_AUTHKEY,ScopeInterface::SCOPE_STORE);
        $servicefee=$this->_scopeConfig->getValue(self::XML_PATH_SERVICE_FEE,ScopeInterface::SCOPE_STORE);
	    $conveniencefee=$this->_scopeConfig->getValue(self::XML_PATH_CONVENIENCE_FEE,ScopeInterface::SCOPE_STORE);

    
        $itemsData = [];
        foreach ($items as $item) {
            $itemId = $item->getItemId();
            $productId = $item->getProductId();
            $product = $this->productRepository->getById($productId);
            $categoryIds = $product->getCategoryIds();

            /*$categories = [];
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryRepository->get($categoryId);
                    $categories[] = [
                        'id' => $category->getId(),
                        'name' => $category->getName()
                    ];
            }*/
            //$xt_categoryname = $product->getData('xt_categoryname');
            
            $itemsData[] = [
                "itemid" => $product->getData('xt_item_id'),
                "categoryid" => !empty($product->getData('xt_category_id')) ? $product->getData('xt_category_id') : 0,
                "subcategoryid" => !empty($product->getData('xt_subcategory_id')) ? $product->getData('xt_subcategory_id') : 0,
		"taxcode" => !empty($product->getData('xt_external_id')) ? $product->getData('xt_external_id') : null,
                "price" => $item->getPrice(),
                "qty" => (int)$item->getQtyOrdered(),
                "title" => $item->getName(),
                "allowSpecialInstructions" => true,
                "specialInstruction" => "",
                "side_groups" => [],
                "option_groups" => []
            ];
        }

        // Get Log Details
        $epiLogCollection = $this->epiLogFactory->create()->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('orderID', $orderIncId)
            ->addFieldToFilter('apiAction', 'payment auth');

        $epiData = $epiLogCollection->getData();
        $returnData = json_decode($epiData[0]['returnData']);
        $cardNumber = $returnData->response->cardNumber;
        $transactionId = $returnData->response->transactionId;
        $paymentOrderId = $returnData->response->paymentOrderId;

        // Get Date with format
        $date = new \DateTime();
        $currentDate = $date->format('Y-m-d\TH:i:s.v\Z');
        $shippingAddress = $order->getShippingAddress();
        
        //echo "<pre>"; print_r($order->getShippingData()); die;
        $data = [
            "items" => $itemsData,
            "paymethods" => [
                [
                    "responseText" => "eCommUrl",
                    "industryType" => "2",
                    "amount" => $order->getGrandTotal(),
                    "transactionIdentifer" => $transactionId,
                    "paymentId" => $paymentOrderId,
                    "token" => $cardNumber,
                    "batchNumber" => $batchno,
                    "datetime" => $currentDate
                ]
            ],
            "createtime" => [
                "date" => $currentDate
            ],
            "modifieddate" => [
                "date" => $currentDate
            ],
            "businessName" => $business_name,
            "datetime" => $date->format('F j, Y g:i A'),
            "orderstatus" => 5,
            "orderSource" => "LiquorCart",
            "ordertype" => $shippingMethod,
            "orderno" => $orderId,
            "subtotal" => $order->getSubtotal(),
            "taxrate" => $order->getTaxPercent() ?: 0,
            "tax" => $order->getTaxAmount(),
            "serviceFee" => $servicefee,
            "deliveryFee" => $order->getShippingAmount(),
            "convenienceFee" => $conveniencefee,
            "tip" => 0.00,
            "totalAmount" => $order->getGrandTotal(),
            "customerInformation" => [
                "firstName" => $order->getCustomerFirstname(),
                "lastName" => $order->getCustomerLastname(),
                "street1" => $order->getShippingAddress()->getStreetLine(1),
                "street2" => "",
                "city" => $order->getShippingAddress()->getCity(),
                "state" => $shippingAddress->getRegionCode(),
                "postalcode" => $order->getShippingAddress()->getPostcode(),
                "country" => $order->getShippingAddress()->getCountryId(),
                "phone" => empty($order->getShippingAddress()->getTelephone()) ? $order->getBillingAddress()->getTelephone() : $order->getShippingAddress()->getTelephone(),
                "email" => $order->getCustomerEmail(),
                "deliveryDistance" => 200
            ],
            "deviceInfo" => []
        ];
 
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $xtApiURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Auth-Key: '.$this->encryptor->decrypt($xtAuthKey),
            'Content-Type: application/json'
            ],
        ]);


	// Ignore certificate errors
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);
        //print_r($response); die;
        curl_close($curl);
        
        if ($orderId) {
            $epiLogModel = $this->epiLogFactory->create();
            $epiLogModel->setData('orderID', $orderIncId);
            $epiLogModel->setData('apiAction', 'Send Data to XT');
            $epiLogModel->setData('requestData', json_encode($data));
            $epiLogModel->setData('returnData', $response);
            $epiLogModel->setData('responseStatus', 1);
            $epiLogModel->save();
        }
        return true;
    }

}
