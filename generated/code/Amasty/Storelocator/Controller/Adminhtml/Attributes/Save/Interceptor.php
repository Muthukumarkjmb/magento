<?php
namespace Amasty\Storelocator\Controller\Adminhtml\Attributes\Save;

/**
 * Interceptor class for @see \Amasty\Storelocator\Controller\Adminhtml\Attributes\Save
 */
class Interceptor extends \Amasty\Storelocator\Controller\Adminhtml\Attributes\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Psr\Log\LoggerInterface $logInterface, \Magento\Backend\Model\View\Result\ForwardFactory $forwardFactory, \Amasty\Storelocator\Model\AttributeFactory $attributeFactory, \Amasty\Storelocator\Model\OptionsFactory $optionsFactory, \Amasty\Storelocator\Model\ResourceModel\Attribute $attributeResourceModel, \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection, \Amasty\Storelocator\Model\ResourceModel\Options $optionsResourceModel, \Magento\Framework\Registry $coreRegistry, \Amasty\Base\Model\Serializer $serializer, \Magento\Ui\Component\MassAction\Filter $filter)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $logInterface, $forwardFactory, $attributeFactory, $optionsFactory, $attributeResourceModel, $attributeCollection, $optionsResourceModel, $coreRegistry, $serializer, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
