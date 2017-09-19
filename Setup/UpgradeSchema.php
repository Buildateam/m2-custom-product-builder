<?php

namespace Buildateam\CustomProductBuilder\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->_changeAttributeColumnType($setup);
        }

        $setup->endSetup();
    }

    /**
     * Change attribute value column type
     *
     * @param SchemaSetupInterface $setup
     */
    private function _changeAttributeColumnType(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('catalog_product_entity_text'),
            'value',
            'value',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => '16M',
                'nullable' => true,
                'comment' => 'Value'
            ]
        );
    }
}