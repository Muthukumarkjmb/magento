<?php
namespace Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Notice;

/**
 * Interceptor class for @see \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Notice
 */
class Interceptor extends \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Notice implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [], ?\Magento\Framework\View\Helper\SecureHtmlRenderer $secureRenderer = null)
    {
        $this->___init();
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'render');
        return $pluginInfo ? $this->___callPlugins('render', func_get_args(), $pluginInfo) : parent::render($element);
    }
}
