<?php
namespace Magento\Paypal\Model\PayflowExpress;

/**
 * Interceptor class for @see \Magento\Paypal\Model\PayflowExpress
 */
class Interceptor extends \Magento\Paypal\Model\PayflowExpress implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Payment\Model\Method\Logger $logger, \Magento\Paypal\Model\ProFactory $proFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\UrlInterface $urlBuilder, \Magento\Paypal\Model\CartFactory $cartFactory, \Magento\Checkout\Model\Session $checkoutSession, \Magento\Framework\Exception\LocalizedExceptionFactory $exception, \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository, \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder, \Magento\Paypal\Model\InfoFactory $paypalInfoFactory, ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $proFactory, $storeManager, $urlBuilder, $cartFactory, $checkoutSession, $exception, $transactionRepository, $transactionBuilder, $paypalInfoFactory, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'order');
        return $pluginInfo ? $this->___callPlugins('order', func_get_args(), $pluginInfo) : parent::order($payment, $amount);
    }
}
