<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epi\Custom\Block;

use Magento\Customer\Model\Context;
use Magento\Sales\Model\Order;
use Burstonline\Epipayment\Model\EpiLogFactory;

/**
 * One page checkout success page
 *
 * @api
 * @since 100.0.2
 */
class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    protected $transactionSearchResult;
    protected $_modelEpilogFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    private $logger;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        EpilogFactory $modelEpilogFactory,
        \Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory $transactionSearchResult,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->_modelEpilogFactory = $modelEpilogFactory;
        $this->transactionSearchResult = $transactionSearchResult;
    }

    /**
     * Render additional order information lines and return result html
     *
     * @return string
     */
    public function getAdditionalInfoHtml()
    {
        return $this->_layout->renderElement('order.success.additional.info');
    }

    /**
     * Initialize data and prepare it for output
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        $this->prepareBlockData();
        return parent::_beforeToHtml();
    }

    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        
        $this->addData(
            [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_id'  => $order->getIncrementId()
            ]
        );
    }

    /**
     * Is order visible
     *
     * @param Order $order
     * @return bool
     */
    protected function isVisible(Order $order)
    {
        return !in_array(
            $order->getStatus(),
            $this->_orderConfig->getInvisibleOnFrontStatuses()
        );
    }

    /**
     * Can view order
     *
     * @param Order $order
     * @return bool
     */
    protected function canViewOrder(Order $order)
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH)
            && $this->isVisible($order);
    }

    /**
     * @return string
     * @since 100.2.0
     */
    public function getContinueUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }
    public function getTransactionId(){
		
		$order = $this->_checkoutSession->getLastRealOrder();
		$transaction=$this->transactionSearchResult->create()->addOrderIdFilter($order->getEntityId())->getFirstItem();
        return $transaction->getData('txn_id');
        
        //return $this->_checkoutSession->getTransactionId();
    }

    public function getApidata()
    {
        $apiAction = 'Send Data to XT';
        $order = $this->_checkoutSession->getLastRealOrder();
        $orderIncId = $order->getIncrementId();
        $epiLogCollection = $this->_modelEpilogFactory->create()->getCollection()
        ->addFieldToSelect('*')
        ->addFieldToFilter('orderID', $orderIncId)
        ->addFieldToFilter('apiAction', $apiAction);
        $apiData = $epiLogCollection->getData();
        if(!empty($apiData)){
            $apiresponseData = !empty($apiData[0]['returnData']) ? json_decode($apiData[0]['returnData'], TRUE) : "";
            return $apiresponseData['OrderID'];
        }
        return "nodata";
    }
}
