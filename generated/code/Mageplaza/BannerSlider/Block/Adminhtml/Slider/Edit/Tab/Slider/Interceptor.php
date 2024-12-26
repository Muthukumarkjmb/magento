<?php
namespace Mageplaza\BannerSlider\Block\Adminhtml\Slider\Edit\Tab\Slider;

/**
 * Interceptor class for @see \Mageplaza\BannerSlider\Block\Adminhtml\Slider\Edit\Tab\Slider
 */
class Interceptor extends \Mageplaza\BannerSlider\Block\Adminhtml\Slider\Edit\Tab\Slider implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Config\Model\Config\Source\Enabledisable $statusOptions, \Mageplaza\BannerSlider\Model\Config\Source\Location $location, \Magento\Customer\Api\GroupRepositoryInterface $groupRepository, \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder, \Magento\Framework\Convert\DataObject $objectConverter, \Magento\Store\Model\System\Store $systemStore, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $statusOptions, $location, $groupRepository, $searchCriteriaBuilder, $objectConverter, $systemStore, $data);
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
