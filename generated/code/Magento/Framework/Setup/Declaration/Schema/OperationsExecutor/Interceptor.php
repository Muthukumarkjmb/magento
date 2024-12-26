<?php
namespace Magento\Framework\Setup\Declaration\Schema\OperationsExecutor;

/**
 * Interceptor class for @see \Magento\Framework\Setup\Declaration\Schema\OperationsExecutor
 */
class Interceptor extends \Magento\Framework\Setup\Declaration\Schema\OperationsExecutor implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(array $operations, array $dataSaviorsCollection, \Magento\Framework\Setup\Declaration\Schema\Sharding $sharding, \Magento\Framework\App\ResourceConnection $resourceConnection, \Magento\Framework\Setup\Declaration\Schema\Db\StatementFactory $statementFactory, \Magento\Framework\Setup\Declaration\Schema\Db\DbSchemaWriterInterface $dbSchemaWriter, \Magento\Framework\Setup\Declaration\Schema\Db\StatementAggregatorFactory $statementAggregatorFactory, \Magento\Framework\Setup\Declaration\Schema\DryRunLogger $dryRunLogger)
    {
        $this->___init();
        parent::__construct($operations, $dataSaviorsCollection, $sharding, $resourceConnection, $statementFactory, $dbSchemaWriter, $statementAggregatorFactory, $dryRunLogger);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Setup\Declaration\Schema\Diff\DiffInterface $diff, array $requestData)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'execute');
        return $pluginInfo ? $this->___callPlugins('execute', func_get_args(), $pluginInfo) : parent::execute($diff, $requestData);
    }
}
