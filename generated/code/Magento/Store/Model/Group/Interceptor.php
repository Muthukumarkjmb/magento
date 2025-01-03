<?php
namespace Magento\Store\Model\Group;

/**
 * Interceptor class for @see \Magento\Store\Model\Group
 */
class Interceptor extends \Magento\Store\Model\Group implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Config\Model\ResourceModel\Config\Data $configDataResource, \Magento\Store\Model\ResourceModel\Store\CollectionFactory $storeListFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [], ?\Magento\Framework\Event\ManagerInterface $eventManager = null, ?\Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface $pillPut = null, ?\Magento\Store\Model\Validation\StoreValidator $modelValidator = null)
    {
        $this->___init();
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $configDataResource, $storeListFactory, $storeManager, $resource, $resourceCollection, $data, $eventManager, $pillPut, $modelValidator);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getIdentities');
        return $pluginInfo ? $this->___callPlugins('getIdentities', func_get_args(), $pluginInfo) : parent::getIdentities();
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'delete');
        return $pluginInfo ? $this->___callPlugins('delete', func_get_args(), $pluginInfo) : parent::delete();
    }
}
