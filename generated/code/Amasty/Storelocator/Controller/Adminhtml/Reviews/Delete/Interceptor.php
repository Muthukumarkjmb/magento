<?php
namespace Amasty\Storelocator\Controller\Adminhtml\Reviews\Delete;

/**
 * Interceptor class for @see \Amasty\Storelocator\Controller\Adminhtml\Reviews\Delete
 */
class Interceptor extends \Amasty\Storelocator\Controller\Adminhtml\Reviews\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Psr\Log\LoggerInterface $logger, \Amasty\Storelocator\Api\ReviewRepositoryInterface $reviewRepository)
    {
        $this->___init();
        parent::__construct($context, $logger, $reviewRepository);
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
