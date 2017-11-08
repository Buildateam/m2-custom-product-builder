<?php

namespace Buildateam\CustomProductBuilder\Setup;

use \Magento\Framework\Setup\UpgradeSchemaInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\SchemaSetupInterface;

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

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->_createCpbProductConfigTable($setup);
        }

        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->_addImagePathToProductConfigTable($setup);
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

    private function _createCpbProductConfigTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable('cpb_product_config'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'config_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['nullable' => false],
                'Config ID'
            )
            ->addColumn(
                'technical_data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '16M',
                ['nullable' => true],
                'Technical data'
            )
            ->setComment('Custom Product Builder Product Config Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Add image path column
     *
     * @param SchemaSetupInterface $setup
     */
    private function _addImagePathToProductConfigTable($setup)
    {
        $setup->getConnection()->dropColumn($setup->getTable('cpb_product_config'), 'image');
        $setup->getConnection()->addColumn(
            $setup->getTable('cpb_product_config'),
            'image',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Image path'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('cpb_product_config'),
            'config_id',
            'config_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Config ID'
            ]
        );
    }
}