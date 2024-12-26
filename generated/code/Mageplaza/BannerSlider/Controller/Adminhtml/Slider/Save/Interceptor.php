<?php
namespace Mageplaza\BannerSlider\Controller\Adminhtml\Slider\Save;

/**
 * Interceptor class for @see \Mageplaza\BannerSlider\Controller\Adminhtml\Slider\Save
 */
class Interceptor extends \Mageplaza\BannerSlider\Controller\Adminhtml\Slider\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\Helper\Js $jsHelper, \Mageplaza\BannerSlider\Model\SliderFactory $sliderFactory, \Magento\Framework\Registry $registry, \Magento\Backend\App\Action\Context $context, \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter, \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor)
    {
        $this->___init();
        parent::__construct($jsHelper, $sliderFactory, $registry, $context, $dateFilter, $dataPersistor);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute();
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        return $pluginInfo ? $this->___callPlugins('dispatch', func_get_args(), $pluginInfo) : parent::dispatch($request);
    }
}
