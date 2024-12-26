<?php
namespace Plumrocket\Base\Controller\Adminhtml\Call\Index;

/**
 * Interceptor class for @see \Plumrocket\Base\Controller\Adminhtml\Call\Index
 */
class Interceptor extends \Plumrocket\Base\Controller\Adminhtml\Call\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\App\Config\Storage\WriterInterface $configWriter, \Magento\Framework\App\ProductMetadataInterface $productMetadata, \Plumrocket\Base\Api\GetModuleVersionInterface $getModuleVersion, \Plumrocket\Base\Model\Utils\GetEnabledStoresUrls $getEnabledStoresUrls)
    {
        $this->___init();
        parent::__construct($context, $resultJsonFactory, $configWriter, $productMetadata, $getModuleVersion, $getEnabledStoresUrls);
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
