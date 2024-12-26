<?php
namespace Plumrocket\Base\Block\Adminhtml\System\Config\Form\Version;

/**
 * Interceptor class for @see \Plumrocket\Base\Block\Adminhtml\System\Config\Form\Version
 */
class Interceptor extends \Plumrocket\Base\Block\Adminhtml\System\Config\Form\Version implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Plumrocket\Base\Api\GetModuleVersionInterface $getModuleVersion, \Plumrocket\Base\Api\GetExtensionInformationInterface $getExtensionInformation, \Plumrocket\Base\Model\IsModuleInMarketplace $isModuleInMarketplace, \Plumrocket\Base\Model\Extension\Updates\Get $getUpdates, \Magento\Framework\Serialize\SerializerInterface $serializer, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $getModuleVersion, $getExtensionInformation, $isModuleInMarketplace, $getUpdates, $serializer, $data);
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
