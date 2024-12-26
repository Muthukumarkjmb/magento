<?php
namespace Magento\Config\Block\System\Config\Tabs;

/**
 * Interceptor class for @see \Magento\Config\Block\System\Config\Tabs
 */
class Interceptor extends \Magento\Config\Block\System\Config\Tabs implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Config\Model\Config\Structure $configStructure, \Magento\Backend\Helper\Data $backendHelper, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $configStructure, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'toHtml');
        return $pluginInfo ? $this->___callPlugins('toHtml', func_get_args(), $pluginInfo) : parent::toHtml();
    }
}
