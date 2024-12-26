<?php
namespace Plumrocket\Base\Block\Adminhtml\System\Config\Developer\MagentoInfo;

/**
 * Interceptor class for @see \Plumrocket\Base\Block\Adminhtml\System\Config\Developer\MagentoInfo
 */
class Interceptor extends \Plumrocket\Base\Block\Adminhtml\System\Config\Developer\MagentoInfo implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\App\Filesystem\DirectoryList $directoryList, \Magento\Framework\Stdlib\DateTime\DateTime $dateTime, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $directoryList, $dateTime, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'render');
        return $pluginInfo ? $this->___callPlugins('render', func_get_args(), $pluginInfo) : parent::render($element);
    }
}
