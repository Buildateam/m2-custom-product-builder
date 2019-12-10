<?php

namespace Buildateam\CustomProductBuilder\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateJsonConfiguration implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * MigrateJsonConfiguration constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetup]);
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $oldAttribute = $this->eavSetup->getAttribute(Product::ENTITY, 'json_configuration');
        if ($oldAttribute && isset($oldAttribute['attribute_id'])) {
            $select = clone $this->moduleDataSetup->getConnection()->select();
            $select->from(
                'catalog_product_entity_text',
                ['entity_id', 'value']
            )->where("attribute_id = {$oldAttribute['attribute_id']}");
            $this->moduleDataSetup->getConnection()->insertFromSelect($select, 'cpb_product_configuration', ['product_id', 'configuration']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
