<?php
namespace Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\CustomisableField;

/**
 * Interceptor class for @see \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\CustomisableField
 */
class Interceptor extends \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\CustomisableField implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\View\Asset\Repository $viewAssetRepository, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $viewAssetRepository, $data);
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
