<?php
namespace Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Editor;

/**
 * Interceptor class for @see \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Editor
 */
class Interceptor extends \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\Editor implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $wysiwygConfig, $data);
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
