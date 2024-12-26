<?php
/**
 * @author  Burstonline
 */

namespace Burstonline\Epipayment\Plugin;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Burstonline\Epipayment\Model\EpiLogFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Burstonline\Epipayment\Model\Ordertoxt;


class OrderManagement
{
    protected $epiLogFactory;
    protected $orderRepository;
    protected $productRepository;
    protected $categoryRepository;
    protected $encryptor;
    protected $Ordertoxt;
    
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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Ordertoxt $Ordertoxt
    ) {
        $this->epiLogFactory = $epiLogFactory;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->_scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->Ordertoxt = $Ordertoxt;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $result
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $result) 
    {
        // Get Order Details
        $orderIncId = $result->getIncrementId();
        $orderId = $result->getEntityId();
        $this->Ordertoxt->excuteorderxt($orderId);
        return $result;
    }

}
