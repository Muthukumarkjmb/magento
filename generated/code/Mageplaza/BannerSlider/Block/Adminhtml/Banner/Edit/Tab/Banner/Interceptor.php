<?php
namespace Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner;

/**
 * Interceptor class for @see \Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner
 */
class Interceptor extends \Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mageplaza\BannerSlider\Model\Config\Source\Type $typeOptions, \Mageplaza\BannerSlider\Model\Config\Source\Template $template, \Magento\Config\Model\Config\Source\Enabledisable $statusOptions, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Mageplaza\BannerSlider\Helper\Image $imageHelper, \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory, \Magento\Framework\Convert\DataObject $objectConverter, \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, array $data = [])
    {
        $this->___init();
        parent::__construct($typeOptions, $template, $statusOptions, $context, $registry, $formFactory, $imageHelper, $fieldFactory, $objectConverter, $wysiwygConfig, $data);
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
