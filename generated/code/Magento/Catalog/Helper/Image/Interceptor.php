<?php
namespace Magento\Catalog\Helper\Image;

/**
 * Interceptor class for @see \Magento\Catalog\Helper\Image
 */
class Interceptor extends \Magento\Catalog\Helper\Image implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Catalog\Model\Product\ImageFactory $productImageFactory, \Magento\Framework\View\Asset\Repository $assetRepo, \Magento\Framework\View\ConfigInterface $viewConfig, ?\Magento\Catalog\Model\View\Asset\PlaceholderFactory $placeholderFactory = null, ?\Magento\Catalog\Model\Config\CatalogMediaConfig $mediaConfig = null)
    {
        $this->___init();
        parent::__construct($context, $productImageFactory, $assetRepo, $viewConfig, $placeholderFactory, $mediaConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultPlaceholderUrl($placeholder = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getDefaultPlaceholderUrl');
        return $pluginInfo ? $this->___callPlugins('getDefaultPlaceholderUrl', func_get_args(), $pluginInfo) : parent::getDefaultPlaceholderUrl($placeholder);
    }
}
