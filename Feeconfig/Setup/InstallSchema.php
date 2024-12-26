<?php

namespace Burstonline\Feeconfig\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('burstonline_feesconfiguration')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('burstonline_feesconfiguration')
            )
                ->addColumn('fee_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                ], 'Fee ID')
                ->addColumn('enabled', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => 0], 'Enabled')
                ->addColumn('title', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
                ->addColumn('sort_order', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'Sort Order')
                ->addColumn('amount', Table::TYPE_DECIMAL, '12,4', ['nullable' => true], 'Amount')
                ->addColumn('fee_type', Table::TYPE_TEXT, 255, ['nullable' => false], 'Fee Type')
                ->addColumn('application_method', Table::TYPE_TEXT, 255, ['nullable' => false], 'Application Method')
                ->addColumn('applies_to', Table::TYPE_TEXT, 255, ['nullable' => false], 'Applies To')
                ->addColumn('mapping', Table::TYPE_TEXT, 255, ['nullable' => false], 'Mapping')
                ->setComment('Fees Table');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
