<?php
namespace Magento\Customer\CustomerData\Section\Identifier;

/**
 * Interceptor class for @see \Magento\Customer\CustomerData\Section\Identifier
 */
class Interceptor extends \Magento\Customer\CustomerData\Section\Identifier implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Stdlib\Cookie\PhpCookieManager $cookieManager)
    {
        $this->___init();
        parent::__construct($cookieManager);
    }

    /**
     * {@inheritdoc}
     */
    public function markSections(array $sectionsData, $sectionNames = null, $forceNewTimestamp = false)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'markSections');
        return $pluginInfo ? $this->___callPlugins('markSections', func_get_args(), $pluginInfo) : parent::markSections($sectionsData, $sectionNames, $forceNewTimestamp);
    }
}
