<?php
namespace PayPal\Braintree\Block\Adminhtml\System\Config\Preview;

/**
 * Interceptor class for @see \PayPal\Braintree\Block\Adminhtml\System\Config\Preview
 */
class Interceptor extends \PayPal\Braintree\Block\Adminhtml\System\Config\Preview implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \PayPal\Braintree\Gateway\Config\PayPal\Config $config, \PayPal\Braintree\Gateway\Config\PayPalCredit\Config $payPalCreditConfig, \PayPal\Braintree\Gateway\Config\PayPalPayLater\Config $payPalPayLaterConfig, \PayPal\Braintree\Gateway\Config\Config $braintreeConfig, \PayPal\Braintree\Model\Ui\ConfigProvider $configProvider, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $config, $payPalCreditConfig, $payPalPayLaterConfig, $braintreeConfig, $configProvider, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'render');
        return $pluginInfo ? $this->___callPlugins('render', func_get_args(), $pluginInfo) : parent::render($element);
    }
}
