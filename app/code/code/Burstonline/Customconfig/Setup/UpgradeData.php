<?php
namespace Burstonline\Customconfig\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;
 
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
    
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		
		if (version_compare($context->getVersion(), '1.0.3', '<')) {
		
			$eavSetup->addAttribute(
				Category::ENTITY,
				'xt_categoryname',
				[
					'group' => 'Custom Category Group',
					'type' => 'text',
					'label' => 'XT Category name',
					'input' => 'text',
					'required' => false,
					'sort_order' => 100,
					'global' => ScopedAttributeInterface::SCOPE_STORE,
					'visible' => true,
					'user_defined' => true,
					'backend' => ''
				]
			);
			$eavSetup->addAttribute(
				Category::ENTITY,
				'xt_subcategory',
				[
					'group' => 'Custom Category Group',
					'type' => 'text',
					'label' => 'XT Sub Category',
					'input' => 'text',
					'required' => false,
					'sort_order' => 100,
					'global' => ScopedAttributeInterface::SCOPE_STORE,
					'visible' => true,
					'user_defined' => true,
					'backend' => ''
				]
			);
		}
    }
}
