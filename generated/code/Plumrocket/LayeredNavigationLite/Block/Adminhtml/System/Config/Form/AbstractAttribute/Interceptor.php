<?php
namespace Plumrocket\LayeredNavigationLite\Block\Adminhtml\System\Config\Form\AbstractAttribute;

/**
 * Interceptor class for @see \Plumrocket\LayeredNavigationLite\Block\Adminhtml\System\Config\Form\AbstractAttribute
 */
class Interceptor extends \Plumrocket\LayeredNavigationLite\Block\Adminhtml\System\Config\Form\AbstractAttribute implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Plumrocket\LayeredNavigationLite\Model\FilterList $filterableAttributes, \Magento\Framework\App\ResourceConnection $resourceConnection, \Magento\Backend\Block\Template\Context $context, array $data = [])
    {
        $this->___init();
        parent::__construct($filterableAttributes, $resourceConnection, $context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'render');
        return $pluginInfo ? $this->___callPlugins('render', func_get_args(), $pluginInfo) : parent::render($element);
    }
}
