<?php
namespace Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab\Attribute;

/**
 * Interceptor class for @see \Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab\Attribute
 */
class Interceptor extends \Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab\Attribute implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection, \Amasty\Base\Model\Serializer $serializer, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $attributeCollection, $serializer, $data);
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
