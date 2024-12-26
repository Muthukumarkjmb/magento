<?php
namespace Magento\Quote\Model\ValidationRules\ShippingAddressValidationRule;

/**
 * Interceptor class for @see \Magento\Quote\Model\ValidationRules\ShippingAddressValidationRule
 */
class Interceptor extends \Magento\Quote\Model\ValidationRules\ShippingAddressValidationRule implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Validation\ValidationResultFactory $validationResultFactory, string $generalMessage = '')
    {
        $this->___init();
        parent::__construct($validationResultFactory, $generalMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(\Magento\Quote\Model\Quote $quote) : array
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'validate');
        return $pluginInfo ? $this->___callPlugins('validate', func_get_args(), $pluginInfo) : parent::validate($quote);
    }
}
