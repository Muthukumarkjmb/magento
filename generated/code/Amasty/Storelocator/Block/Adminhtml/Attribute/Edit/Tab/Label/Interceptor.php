<?php
namespace Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Label;

/**
 * Interceptor class for @see \Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Label
 */
class Interceptor extends \Amasty\Storelocator\Block\Adminhtml\Attribute\Edit\Tab\Label implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Config\Model\Config\Source\Yesno $yesno, \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $yesno, $inputTypeFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getForm');
        return $pluginInfo ? $this->___callPlugins('getForm', func_get_args(), $pluginInfo) : parent::getForm();
    }
}
