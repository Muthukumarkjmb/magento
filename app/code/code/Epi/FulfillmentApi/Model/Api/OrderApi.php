<?php
namespace Epi\FulfillmentApi\Model\Api;

use Magento\Framework\Exception\InvalidArgumentException as InvalidArgumentException;
use \Epi\FulfillmentApi\Model\OrderResponse;
use \Magento\Sales\Api\OrderRepositoryInterface; 

class OrderApi{

    public function __construct(
        OrderRepositoryInterface $orderRepository
    )
    {    
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/Fulfillment.log');
        $this->logger = new \Zend_Log();
        $this->logger->addWriter($writer); 
        $this->response = new OrderResponse();
        $this->orderRepository=$orderRepository;
    }

    public function getOrderDetails($entityId) {
        $this->logger->info('<----- Get Order Details ----->');
        try {
            if(!isset($entityId)||!is_numeric($entityId)){
                throw new InvalidArgumentException(__("Invalid order id"));
            }
            $order=$this->orderRepository->get($entityId);
            
            $items=[];
            foreach ($order->getAllVisibleItems() as $item) {
                array_push($items,["name"=>$item->getName(),"qty_ordered"=>(int)$item->getQtyOrdered(),"price_incl_tax"=>(float)$item->getPriceInclTax()]);
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderOne = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order->getIncrementId());
            $orderStatus=$orderOne->getStatus();
            $this->logger->info("Items-->".print_r($items,true));
            $this->response->setItems($items); 
            $this->response->setIncrementId($order->getIncrementId()); 
            $this->response->setTaxAmount($order->getTaxAmount()); 
            $this->response->setShippingAmount($order->getShippingAmount()); 
            $this->response->setServiceFee($order->getPaymentFee()); 
            $this->response->setTotalAmount($order->getGrandTotal()); 
            $this->response->setSubtotal($order->getSubtotal());
            $this->response->setMessage("Success"); 
            $this->response->setSuccess(true);  
            $this->response->setOrderStatus($orderStatus); 
            return $this->response;  

        } catch (\Exception $e) {  
            throw($e);
        }
    }
}