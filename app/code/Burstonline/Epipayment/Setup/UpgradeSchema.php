<?php

namespace Burstonline\Epipayment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            // Your table creation or modification logic here
            $this->createYourTable($setup);
            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
			$setup->startSetup();

			$setup->getConnection()
				->addColumn(
					$setup->getTable('epi_log'),
					'requestData',
					[
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'comment' => 'requestData'
					]
				);

			$setup->endSetup();
		}

        
    }

    protected function createYourTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('epi_log'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )
            ->addColumn(
                'orderID',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                125,
                ['nullable' => false],
                'orderID'
            )
			->addColumn(
                'apiAction',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'apiAction'
            )
			->addColumn(
                'returnData',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'returnData'
            )->addColumn(
                'responseStatus',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                125,
                ['nullable' => false],
                'responseStatus'
            )
            // Add more columns as needed
            ->setComment('Your Table Description');

        $setup->getConnection()->createTable($table);
    }
}
