<?php
namespace Burstonline\Feeconfig\Block\Adminhtml\Store\Edit\Tab\Store;

/**
 * Interceptor class for @see \Burstonline\Feeconfig\Block\Adminhtml\Store\Edit\Tab\Store
 */
class Interceptor extends \Burstonline\Feeconfig\Block\Adminhtml\Store\Edit\Tab\Store implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, \Magento\Store\Model\System\Store $systemStore, \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Burstonline\Feeconfig\Model\FeeconfigFinish $feeconfigFinish, array $data = [])
    {
        $this->___init();
        parent::__construct($wysiwygConfig, $systemStore, $context, $registry, $formFactory, $feeconfigFinish, $data);
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
