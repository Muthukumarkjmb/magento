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

        if (!$setup->tableExists('contact_form_submissions')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('contact_form_submissions')
            )
                ->addColumn('id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                ], 'Fee ID')
        
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
                ->addColumn('email', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
                ->addColumn('message', Table::TYPE_TEXT, 255, ['nullable' => false], 'Title')
                ->setComment('Fees Table');
            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
