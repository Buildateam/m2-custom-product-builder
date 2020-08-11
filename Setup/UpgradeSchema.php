<?php
/**
 * Copyright (c) 2017 Indigo Geeks, Inc. All rights reserved.
 *
 * General.
 * The custom product builder software and documentation accompanying this License
 * whether on disk, in read only memory, on any other media or in any other form (collectively
 * the “Software”) are licensed, not sold, to you by copyright holder, Indigo Geeks, Inc.
 * (“Buildateam”) for use only under the terms of this License, and Buildateam reserves all rights
 * not expressly granted to you. The rights granted herein are limited to Buildateam’s intellectual
 * property rights in the Buildateam Software and do not include any other patents or
 * intellectual property rights. You own the media on which the Buildateam Software is
 * recorded but Buildateam and/or Buildateam’s licensor(s) retain ownership of the Software
 * itself.
 *
 * Permitted License Uses and Restrictions.
 * This License allows you to install and use one (1) copy of the Software.
 * This License does not allow the Software to exist on more than one production domain.
 * Except as and only to the extent expressly permitted in this License or by applicable
 * law, you may not copy, decompile, reverse engineer, disassemble, attempt to derive
 * the source code of, modify, or create derivative works of the Software or any part
 * thereof. Any attempt to do so is a violation of the rights of Buildateam and its licensors of
 * the Software. If you breach this restriction, you may be subject to prosecution and
 * damages.
 *
 * Transfer.
 * You may not rent, lease, lend or sublicense the Software.
 *
 * Termination.
 * This License is effective until terminated. Your rights under this
 * License will terminate automatically without notice from Buildateam if you fail to comply
 * with any term(s) of this License. Upon the termination of this License, you shall cease
 * all use of the Buildateam Software and destroy all copies, full or partial, of the Buildateam
 * Software.
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            $this->changeAttributeColumnType($setup);
        }

        if (version_compare($context->getVersion(), '0.1.4', '<')) {
            $this->createCpbProductConfigTable($setup);
        }

        if (version_compare($context->getVersion(), '0.1.5', '<')) {
            $this->addImagePathToProductConfigTable($setup);
        }

        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            $this->changeVariationColumnName($setup);
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $this->createNewConfigurationTable($setup);
        }

        if (version_compare($context->getVersion(), '1.0.11', '<')) {
            $this->removeProductIdAutoincrement($setup);
        }

        if (version_compare($context->getVersion(), '1.0.12', '<')) {
            $this->addUniqueProductIdIndex($setup);
        }

        $setup->endSetup();
    }

    /**
     * Change attribute value column type
     *
     * @param SchemaSetupInterface $setup
     */
    private function changeAttributeColumnType(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('catalog_product_entity_text'),
            'value',
            'value',
            [
                'type' => Table::TYPE_TEXT,
                'length' => '16M',
                'nullable' => true,
                'comment' => 'Value'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function createCpbProductConfigTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('cpb_product_config');
        $connection->dropTable($tableName);
        $table = $connection->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'config_id',
                Table::TYPE_TEXT,
                20,
                ['nullable' => false],
                'Config ID'
            )
            ->addColumn(
                'technical_data',
                Table::TYPE_TEXT,
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
    private function addImagePathToProductConfigTable($setup)
    {
        $setup->getConnection()->dropColumn($setup->getTable('cpb_product_config'), 'image');
        $setup->getConnection()->addColumn(
            $setup->getTable('cpb_product_config'),
            'image',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Image path'
            ]
        );

        $setup->getConnection()->changeColumn(
            $setup->getTable('cpb_product_config'),
            'config_id',
            'config_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Config ID'
            ]
        );
    }

    /**
     * Change variation column name
     *
     * @param SchemaSetupInterface $setup
     */
    private function changeVariationColumnName($setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('cpb_product_config'),
            'config_id',
            'variation_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => false,
                'comment' => 'Config ID'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function createNewConfigurationTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('cpb_product_configuration');
        $connection->dropTable($tableName);
        $table = $connection->newTable(
            $tableName
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'unsigned' => true,
            ],
            'Product ID'
        )->addColumn(
            'configuration',
            Table::TYPE_TEXT,
            '2M',
            [
                'nullable' => true
            ],
            'Product Configuration'
        )->addForeignKey(
            $setup->getFkName(
                'cpb_product_configuration',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable('cpb_product_configuration'),
                'product_id',
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'product_id',
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function removeProductIdAutoincrement(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->changeColumn(
            $setup->getTable('cpb_product_configuration'),
            'product_id',
            'product_id',
            [
                'type' => Table::TYPE_INTEGER,
                'auto_increment' => false,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Product ID'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('cpb_product_configuration'),
            'config_id',
            [
                'type' => Table::TYPE_INTEGER,
                'auto_increment' => true,
                'primary' => true,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Config ID'
            ]
        );
        $setup->getConnection()->dropForeignKey(
            $setup->getTable('cpb_product_configuration'),
            'CPB_PRD_CONFIGURATION_PRD_ID_CAT_PRD_ENTT_ENTT_ID'
        );
        $setup->getConnection()
            ->dropIndex(
                $setup->getTable('cpb_product_configuration'),
                'CPB_PRODUCT_CONFIGURATION_PRODUCT_ID'
            );
        $setup->getConnection()->addForeignKey(
            $setup->getFkName(
                'cpb_product_configuration',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            $setup->getTable('cpb_product_configuration'),
            'product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addUniqueProductIdIndex(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addIndex(
            $setup->getTable('cpb_product_configuration'),
            $setup->getIdxName(
                $setup->getTable('cpb_product_configuration'),
                'product_id',
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'product_id',
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
