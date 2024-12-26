<?php
namespace Amasty\Base\Console\Command\RegisterLicenceKey;

/**
 * Interceptor class for @see \Amasty\Base\Console\Command\RegisterLicenceKey
 */
class Interceptor extends \Amasty\Base\Console\Command\RegisterLicenceKey implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Amasty\Base\Model\SysInfo\Command\LicenceService\RegisterLicenceKey $registerLicenceKey, ?string $name = null)
    {
        $this->___init();
        parent::__construct($registerLicenceKey, $name);
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
