<?php
namespace Plumrocket\Base\Block\Adminhtml\System\Config\Form\Serial;

/**
 * Interceptor class for @see \Plumrocket\Base\Block\Adminhtml\System\Config\Form\Serial
 */
class Interceptor extends \Plumrocket\Base\Block\Adminhtml\System\Config\Form\Serial implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Block\Template\Context $context, \Plumrocket\Base\Model\IsModuleInMarketplace $isModuleInMarketplace, \Plumrocket\Base\Api\ExtensionAuthorizationRepositoryInterface $extensionAuthorizationRepository, \Plumrocket\Base\Model\Extension\Authorization\Key $authorizationKey, \Plumrocket\Base\Model\Extension\Authorization\Factory $extensionAuthorizationFactory, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $isModuleInMarketplace, $extensionAuthorizationRepository, $authorizationKey, $extensionAuthorizationFactory, $data);
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
