<?php
namespace Amasty\Geoip\Block\Adminhtml\Settings\Import;

/**
 * Interceptor class for @see \Amasty\Geoip\Block\Adminhtml\Settings\Import
 */
class Interceptor extends \Amasty\Geoip\Block\Adminhtml\Settings\Import implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Amasty\Geoip\Model\Import $import, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $import, $data);
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
