<?php
namespace Amasty\Base\Console\Command\SendSysInfo;

/**
 * Interceptor class for @see \Amasty\Base\Console\Command\SendSysInfo
 */
class Interceptor extends \Amasty\Base\Console\Command\SendSysInfo implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Amasty\Base\Model\SysInfo\Command\LicenceService\SendSysInfo $sendSysInfo, ?string $name = null)
    {
        $this->___init();
        parent::__construct($sendSysInfo, $name);
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
