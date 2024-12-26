<?php
namespace Amasty\Storelocator\Controller\Adminhtml\Reviews\MassAction;

/**
 * Interceptor class for @see \Amasty\Storelocator\Controller\Adminhtml\Reviews\MassAction
 */
class Interceptor extends \Amasty\Storelocator\Controller\Adminhtml\Reviews\MassAction implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Amasty\Storelocator\Api\ReviewRepositoryInterface $reviewRepository, \Amasty\Storelocator\Model\ResourceModel\Review\CollectionFactory $collectionFactory, \Magento\Ui\Component\MassAction\Filter $filter, \Psr\Log\LoggerInterface $logger)
    {
        $this->___init();
        parent::__construct($context, $reviewRepository, $collectionFactory, $filter, $logger);
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
