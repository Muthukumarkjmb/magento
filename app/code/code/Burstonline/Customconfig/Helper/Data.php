<?php

namespace Burstonline\Customconfig\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Burstonline\Feeconfig\Model\ResourceModel\Feeconfig\CollectionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * Custom fee config path
     */
    protected $collectionFactory;
    protected $logger;
    protected $checkoutSession;
    protected $orderRepository;
    protected $quoteRepository;

    const CONFIG_CUSTOM_IS_ENABLED = 'burstonline_customconfig/product_non_lmp/active';
    const CONFIG_FEE_LABEL = 'Extrafee/Extrafee/name';
    const CONFIG_MINIMUM_ORDER_AMOUNT = 'Extrafee/Extrafee/minimum_order_amount';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return mixed
     */
    public function isModuleEnabled()
    {

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::CONFIG_CUSTOM_IS_ENABLED, $storeScope);
    }

    /**
     * Get custom fee
     *
     * @return mixed
     */
    public function getExtrafee()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote && $quote->getId()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingMethod = $shippingAddress->getShippingMethod();
            $this->logger->info('Shipping method :'.$shippingAddress->getShippingMethod());

            $FeeDetails = $this->getFeeCollection();
            $i = 0; $j=0; $amount = array();
            foreach ($FeeDetails as $feeDetail) {
                $extraAmount = $this->calculateExtraAmount($quote, $feeDetail->getApplicationMethod(),'quote');
                if($this->shouldApplyFee($shippingMethod, $feeDetail->getAppliesTo())){
                    if($feeDetail->getFeeType() == 'percentage'){ $amount[$i] = number_format((float)($extraAmount * ($feeDetail->getAmount()/100)), 2, '.', '');}
                    if($feeDetail->getFeeType() == 'flat'){ $amount[$i] = number_format((float)$feeDetail->getAmount(), 2, '.', ''); } 
                    $i++;
                }
            }
        }
        return implode('|',$amount);

    }

    public function getSumExtrafee()
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        $fees = 0;
        $this->logger->info('Quote ID :: '.$quoteId);
        if (!empty($quoteId)) {
            if($quote = $this->quoteRepository->get($quoteId)){
                $shippingAddress = $quote->getShippingAddress();
                $shippingMethod = $shippingAddress->getShippingMethod();
                $subTotal = $quote->getSubtotal();
                $this->logger->info('Shipping method :'.$shippingAddress->getShippingMethod());

                $FeeDetails = $this->getFeeCollection();
                
                foreach ($FeeDetails as $feeDetail) {
                    $extraAmount = $this->calculateExtraAmount($quote, $feeDetail->getApplicationMethod(),'quote');
                    if($this->shouldApplyFee($shippingMethod, $feeDetail->getAppliesTo())){
                        if($feeDetail->getFeeType() == 'percentage'){ $fees = $fees + ($extraAmount * ($feeDetail->getAmount()/100)); }
                        if($feeDetail->getFeeType() == 'flat'){ $fees = $fees + $feeDetail->getAmount(); }
                    }
                }
            }
        }
        $this->logger->info('Fee Amount : ' . number_format((float)$fees, 2, '.', ''));
        return $fees;

    }

    public function getFeeLabel()
    {
        $quote = $this->checkoutSession->getQuote();
        $title = array();
        if ($quote && $quote->getId()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingMethod = $shippingAddress->getShippingMethod();
            $this->logger->info('Shipping method :'.$shippingAddress->getShippingMethod());
        
            $FeeDetails = $this->getFeeCollection();
            $i = 0; $j=0;
            foreach ($FeeDetails as $feeDetail) {
                if($this->shouldApplyFee($shippingMethod, $feeDetail->getAppliesTo())){
                    $title[$i] = $feeDetail->getTitle();
                    $i++;
                }
            }
        }
        return implode('|',$title);
    }

    public function getFeeMapping($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $mapping = array();        
        
        if ($order) {
            $shippingMethod = strtolower($order->getShippingMethod());
            
            $FeeDetails = $this->getFeeCollection();

            foreach ($FeeDetails as $feeDetail) {
                $extraAmount = $this->calculateExtraAmount($order, $feeDetail->getApplicationMethod(),'order');

                if($this->shouldApplyFee($shippingMethod, $feeDetail->getAppliesTo())){
                    if($feeDetail->getFeeType() == 'percentage'){ 
                        $mapping[$feeDetail->getMapping()] = number_format((float)($extraAmount * ($feeDetail->getAmount()/100)), 2, '.', ''); 
                    }
                    if($feeDetail->getFeeType() == 'flat'){ 
                        $mapping[$feeDetail->getMapping()] = number_format((float)$feeDetail->getAmount(), 2, '.', ''); 
                    } 
                }
            }
        }
        $this->logger->info('Mapping values : ' . json_encode($mapping));
        return json_encode($mapping);
    }

    public function getFeeCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('enabled', ['eq' => 1]);
        $collection->setOrder('sort_order', 'ASC');

        return $collection;
    }

    private function shouldApplyFee($shippingMethod, $appliesTo)
    {
        if (empty($shippingMethod) || $shippingMethod === 'flatrate_flatrate') {
            return $appliesTo === 'delivery' || $appliesTo === 'both';
        }

        return $appliesTo === 'pickup' || $appliesTo === 'both';
    }

    private function calculateExtraAmount($data, $applicationMethod, $calcFor)
    {
        if ($applicationMethod === 'applied_to_cart') {
            return $data->getSubtotal();
        }
        $extraAmount = 0;

        $items = ($calcFor === 'quote') ? $data->getAllVisibleItems() : $data->getItems();

        foreach ($items as $item) {
            $qty = ($calcFor === 'quote') ? $item->getQty() : $item->getQtyOrdered();
            $extraAmount += $item->getPrice() * $qty;
        }

        return $extraAmount;
    }
    
    /**
     * @return mixed
     */
    public function getMinimumOrderAmount()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::CONFIG_MINIMUM_ORDER_AMOUNT, $storeScope);
    }
}
