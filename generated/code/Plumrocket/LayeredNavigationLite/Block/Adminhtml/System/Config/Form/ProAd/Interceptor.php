<?php
namespace Plumrocket\LayeredNavigationLite\Block\Adminhtml\System\Config\Form\ProAd;

/**
 * Interceptor class for @see \Plumrocket\LayeredNavigationLite\Block\Adminhtml\System\Config\Form\ProAd
 */
class Interceptor extends \Plumrocket\LayeredNavigationLite\Block\Adminhtml\System\Config\Form\ProAd implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Plumrocket\Base\Model\IsModuleInMarketplace $isModuleInMarketplace, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $isModuleInMarketplace, $data);
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
