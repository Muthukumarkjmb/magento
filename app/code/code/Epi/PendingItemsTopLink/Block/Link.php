<?php
namespace Epi\PendingItemsTopLink\Block;
 
class Link extends \Magento\Framework\View\Element\Html\Link
{
/**
* Render block HTML.
*
* @return string
*/
/**      * @param \Magento\Framework\View\Element\Template\Context $context      */
public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, 
    \Magento\Checkout\Model\Session $checkoutSession
    )
{
    $this->_checkoutSession = $checkoutSession;
    parent::__construct($context);
}
protected function _toHtml()
    {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
     if (false != $this->getTemplate()) {
     return parent::_toHtml();
     }
     $checkoutData = $this->_checkoutSession->getData();
     if(count($checkoutData)>0){
        $orderId = array_key_exists("last_order_id",$checkoutData)?$checkoutData["last_order_id"]:"";
        if($orderId!=""){
            $order = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderId);
            // $orderStatus = $checkoutData["last_order_status"];
            if(isset($order)){  
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $methodTitle = $method->getTitle();

            $orderStatus =  $order->getStatus();
            // var_dump($checkoutData);
            // $orderStatus = array_key_exists("last_order_status",$checkoutData)?$checkoutData["last_order_status"]:"";
            if($orderStatus == "pending" && $methodTitle!= "Pay on Delivery/Pickup"){
                return '<li class="link pending-order"><a ' . $this->getLinkAttributes() . ' >' . $this->escapeHtml($this->getLabel()) . '</a></li>';
            }
        }
        
    }
}
    return '<li></li>';
    }
}