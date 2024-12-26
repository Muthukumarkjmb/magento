<?php
namespace Mageplaza\GoogleRecaptcha\Console\Adminhtml\Command\Disable;

/**
 * Interceptor class for @see \Mageplaza\GoogleRecaptcha\Console\Adminhtml\Command\Disable
 */
class Interceptor extends \Mageplaza\GoogleRecaptcha\Console\Adminhtml\Command\Disable implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Mageplaza\GoogleRecaptcha\Helper\Data $helperData, \Magento\Framework\App\Config\Storage\WriterInterface $configWriter, $name = null)
    {
        $this->___init();
        parent::__construct($helperData, $configWriter, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function run(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) : int
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'run');
        return $pluginInfo ? $this->___callPlugins('run', func_get_args(), $pluginInfo) : parent::run($input, $output);
    }
}
