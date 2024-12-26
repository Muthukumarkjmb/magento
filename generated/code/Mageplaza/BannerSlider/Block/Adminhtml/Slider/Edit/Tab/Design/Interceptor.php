<?php
namespace Mageplaza\BannerSlider\Block\Adminhtml\Slider\Edit\Tab\Design;

/**
 * Interceptor class for @see \Mageplaza\BannerSlider\Block\Adminhtml\Slider\Edit\Tab\Design
 */
class Interceptor extends \Mageplaza\BannerSlider\Block\Adminhtml\Slider\Edit\Tab\Design implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Mageplaza\BannerSlider\Model\Config\Source\Effect $effect, \Magento\Config\Model\Config\Source\Yesno $yesno, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $effect, $yesno, $data);
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
