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

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    private $configValueFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productMetadata = $productMetadata;
        $this->configValueFactory = $configValueFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.10', '<')) {
            $this->moveJsonConfigurations($setup);
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->setupNewProductType($setup);
        }

        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            $this->changeProductType($setup);
            $this->removeOldAttributes($setup);
        }

        if (version_compare($context->getVersion(), '1.1.13', '<')) {
            $this->removeEmptyConfig($setup);
        }

        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $this->updateTaxClassId($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function moveJsonConfigurations(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $connection = $setup->getConnection();
        $oldAttribute = $eavSetup->getAttribute(Product::ENTITY, 'json_configuration');
        if ($oldAttribute && isset($oldAttribute['attribute_id'])) {
            $select = clone $connection->select();
            if ($this->productMetadata->getEdition() == 'Community') {
                $select->from(
                    $setup->getTable('catalog_product_entity_text'),
                    ['entity_id', 'value']
                );
            } else { // workaround for EE
                $select->from(
                    $setup->getTable('catalog_product_entity_text'),
                    ['row_id', 'value']
                );
            }
            $select->where("attribute_id = {$oldAttribute['attribute_id']}");
            $insert = $connection->insertFromSelect(
                $select,
                $setup->getTable('cpb_product_configuration'),
                ['product_id', 'configuration']
            );
            $connection->query($insert);
            $delete = $connection->deleteFromSelect($select, $setup->getTable('catalog_product_entity_text'));
            $connection->query($delete);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function setupNewProductType(ModuleDataSetupInterface $setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $fieldList = [
            'price',
            'special_from_date',
            'special_to_date',
            'cost',
            'tier_price',
            'weight',
        ];
        $newType = \Buildateam\CustomProductBuilder\Model\Product\Type::TYPE_CODE;
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array($newType, $applyTo)) {
                $applyTo[] = $newType;
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function changeProductType(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select = $connection->select();
        $select->from($setup->getTable('cpb_product_configuration'), ['product_id'])
            ->where('LENGTH(`configuration`) > 1256');

        $productIds = $connection->fetchCol($select);
        if ($productIds && is_array($productIds)) {
            foreach($productIds as $productId) {
                $connection->update(
                    $setup->getTable('catalog_product_entity'),
                    array('type_id' => \Buildateam\CustomProductBuilder\Model\Product\Type::TYPE_CODE),
                    'entity_id = ' . $productId
                );
            }
        }
    }

    private function updateTaxClassId(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $productEntityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $applyTo = explode(',', $eavSetup->getAttribute($productEntityTypeId, 'tax_class_id', 'apply_to'));
        if (!in_array('custom', $applyTo)) {
            $applyTo[] = 'custom';
            $eavSetup->updateAttribute($productEntityTypeId, 'tax_class_id', 'apply_to', implode(',', $applyTo));
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function removeOldAttributes(ModuleDataSetupInterface $setup)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'json_configuration');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cpb_enabled');
    }

    private function removeEmptyConfig()
    {
        $bulderJsConfig = $this->configValueFactory->create()
            ->load(\Buildateam\CustomProductBuilder\Helper\Data::XPATH_BUILDER_JS, 'path');
        if (empty($bulderJsConfig->getValue())) {
            $bulderJsConfig->delete();
        }

        $bulderThemeConfig = $this->configValueFactory->create()
            ->load(\Buildateam\CustomProductBuilder\Helper\Data::XPATH_BUILDER_THEME_JS, 'path');
        if (empty($bulderThemeConfig->getValue())) {
            $bulderThemeConfig->delete();
        }
    }
}
