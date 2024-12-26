<?php
namespace Magiccart\Magicmenu\Block\Adminhtml\Menu\Edit\Tab\Form;

/**
 * Interceptor class for @see \Magiccart\Magicmenu\Block\Adminhtml\Menu\Edit\Tab\Form
 */
class Interceptor extends \Magiccart\Magicmenu\Block\Adminhtml\Menu\Edit\Tab\Form implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Framework\DataObjectFactory $objectFactory, \Magento\Store\Model\System\Store $systemStore, \Magiccart\Magicmenu\Model\Magicmenu $magicmenu, \Magiccart\Magicmenu\Model\System\Config\Blocks $blocks, \Magiccart\Magicmenu\Model\System\Config\Category $category, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $objectFactory, $systemStore, $magicmenu, $blocks, $category, $data);
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
