<?php
namespace Amasty\Storelocator\Block\Adminhtml\Review\Edit\Form;

/**
 * Interceptor class for @see \Amasty\Storelocator\Block\Adminhtml\Review\Edit\Form
 */
class Interceptor extends \Amasty\Storelocator\Block\Adminhtml\Review\Edit\Form implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Store\Model\System\Store $systemStore, \Amasty\Storelocator\Model\Repository\ReviewRepository $reviewRepository, \Amasty\Storelocator\Model\ReviewFactory $reviewFactory, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, \Amasty\Storelocator\Model\Config\Source\ReviewStatuses $reviewStatuses, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $systemStore, $reviewRepository, $reviewFactory, $customerRepository, $reviewStatuses, $data);
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
