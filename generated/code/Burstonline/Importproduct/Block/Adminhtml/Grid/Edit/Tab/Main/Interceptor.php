<?php
namespace Burstonline\Importproduct\Block\Adminhtml\Grid\Edit\Tab\Main;

/**
 * Interceptor class for @see \Burstonline\Importproduct\Block\Adminhtml\Grid\Edit\Tab\Main
 */
class Interceptor extends \Burstonline\Importproduct\Block\Adminhtml\Grid\Edit\Tab\Main implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Backend\Model\Auth\Session $adminSession, \Burstonline\Importproduct\Model\Status $status, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $formFactory, $adminSession, $status, $data);
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
