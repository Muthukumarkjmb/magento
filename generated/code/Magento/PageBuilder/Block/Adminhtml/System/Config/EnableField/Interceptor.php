<?php
namespace Magento\PageBuilder\Block\Adminhtml\System\Config\EnableField;

/**
 * Interceptor class for @see \Magento\PageBuilder\Block\Adminhtml\System\Config\EnableField
 */
class Interceptor extends \Magento\PageBuilder\Block\Adminhtml\System\Config\EnableField implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Serialize\Serializer\Json $json, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $json, $data);
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
