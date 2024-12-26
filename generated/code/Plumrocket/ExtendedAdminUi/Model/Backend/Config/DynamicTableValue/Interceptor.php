<?php
namespace Plumrocket\ExtendedAdminUi\Model\Backend\Config\DynamicTableValue;

/**
 * Interceptor class for @see \Plumrocket\ExtendedAdminUi\Model\Backend\Config\DynamicTableValue
 */
class Interceptor extends \Plumrocket\ExtendedAdminUi\Model\Backend\Config\DynamicTableValue implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ScopeConfigInterface $config, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, \Magento\Framework\Serialize\SerializerInterface $serializer, ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null)
    {
        $this->___init();
        parent::__construct($context, $registry, $config, $cacheTypeList, $serializer, $resource, $resourceCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterSave');
        return $pluginInfo ? $this->___callPlugins('afterSave', func_get_args(), $pluginInfo) : parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'save');
        return $pluginInfo ? $this->___callPlugins('save', func_get_args(), $pluginInfo) : parent::save();
    }
}
