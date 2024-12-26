<?php
namespace Magento\Framework\Config\FileResolverByModule;

/**
 * Interceptor class for @see \Magento\Framework\Config\FileResolverByModule
 */
class Interceptor extends \Magento\Framework\Config\FileResolverByModule implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader, \Magento\Framework\Filesystem $filesystem, \Magento\Framework\Config\FileIteratorFactory $iteratorFactory, \Magento\Framework\Component\ComponentRegistrar $componentRegistrar, \Magento\Framework\Filesystem\Driver\File $driver)
    {
        $this->___init();
        parent::__construct($moduleReader, $filesystem, $iteratorFactory, $componentRegistrar, $driver);
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'get');
        return $pluginInfo ? $this->___callPlugins('get', func_get_args(), $pluginInfo) : parent::get($filename, $scope);
    }
}
