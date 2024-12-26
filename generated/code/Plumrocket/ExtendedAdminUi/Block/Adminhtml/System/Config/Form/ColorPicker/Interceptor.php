<?php
namespace Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\ColorPicker;

/**
 * Interceptor class for @see \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\ColorPicker
 */
class Interceptor extends \Plumrocket\ExtendedAdminUi\Block\Adminhtml\System\Config\Form\ColorPicker implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Stdlib\ArrayManager $arrayManager, \Magento\Framework\Serialize\SerializerInterface $serializer, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $arrayManager, $serializer, $data);
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
