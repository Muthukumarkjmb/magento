<?php
namespace Amasty\StorePickupWithLocator\Controller\Paypal\SaveShippingAddress;

/**
 * Interceptor class for @see \Amasty\StorePickupWithLocator\Controller\Paypal\SaveShippingAddress
 */
class Interceptor extends \Amasty\StorePickupWithLocator\Controller\Paypal\SaveShippingAddress implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Quote\Model\ShippingAddressManagementInterface $shippingAddressManagement, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Amasty\Storelocator\Model\ResourceModel\Location $locationResource, \Amasty\Storelocator\Model\LocationFactory $locationFactory, \Amasty\StorePickupWithLocator\Model\Sales\AddressResolver $addressResolver)
    {
        $this->___init();
        parent::__construct($context, $shippingAddressManagement, $checkoutSession, $quoteRepository, $locationResource, $locationFactory, $addressResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
