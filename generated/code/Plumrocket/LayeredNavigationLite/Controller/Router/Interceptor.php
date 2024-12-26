<?php
namespace Plumrocket\LayeredNavigationLite\Controller\Router;

/**
 * Interceptor class for @see \Plumrocket\LayeredNavigationLite\Controller\Router
 */
class Interceptor extends \Plumrocket\LayeredNavigationLite\Controller\Router implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Plumrocket\LayeredNavigationLite\Helper\Config $config, \Plumrocket\LayeredNavigationLite\Api\GetUrlVariablesInterface $getUrlVariables, \Plumrocket\LayeredNavigationLite\Model\Variable\Value $variableValue, \Plumrocket\LayeredNavigationLite\Model\Variable\Path\Processor $pathProcessor, \Plumrocket\LayeredNavigationLite\Model\Variable\Registry $variableRegistry, \Plumrocket\LayeredNavigationLite\Model\Variable\Params\Processor $paramsProcessor, \Plumrocket\LayeredNavigationLite\Model\AjaxRequestLocator $ajaxRequestLocator, \Plumrocket\LayeredNavigationLite\Helper\Config\Seo $seoConfig)
    {
        $this->___init();
        parent::__construct($config, $getUrlVariables, $variableValue, $pathProcessor, $variableRegistry, $paramsProcessor, $ajaxRequestLocator, $seoConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function match(\Magento\Framework\App\RequestInterface $request) : void
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'match');
        $pluginInfo ? $this->___callPlugins('match', func_get_args(), $pluginInfo) : parent::match($request);
    }
}
