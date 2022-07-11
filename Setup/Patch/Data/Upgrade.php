<?php
namespace  Buildateam\CustomProductBuilder\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\ValueFactory;

class Upgrade implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ValueFactory $configValueFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ValueFactory $configValueFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->configValueFactory = $configValueFactory;
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->setupNewProductType();
        $this->changeProductType();
        $this->removeOldAttributes();
        $this->removeEmptyConfig();
        $this->updateTaxClassId();
        return $this;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function setupNewProductType()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
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
    private function changeProductType()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select();
        $select->from($this->moduleDataSetup->getTable('cpb_product_configuration'), ['product_id'])
            ->where('LENGTH(`configuration`) > 1256');

        $productIds = $connection->fetchCol($select);
        if ($productIds && is_array($productIds)) {
            foreach ($productIds as $productId) {
                $connection->update(
                    $this->moduleDataSetup->getTable('catalog_product_entity'),
                    ['type_id' => \Buildateam\CustomProductBuilder\Model\Product\Type::TYPE_CODE],
                    'entity_id = ' . $productId
                );
            }
        }
    }

    private function updateTaxClassId()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $productEntityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $applyTo = explode(',', $eavSetup->getAttribute($productEntityTypeId, 'tax_class_id', 'apply_to'));
        if (!in_array('custom', $applyTo)) {
            $applyTo[] = 'custom';
            $eavSetup->updateAttribute($productEntityTypeId, 'tax_class_id', 'apply_to', implode(',', $applyTo));
        }
    }

    private function removeOldAttributes()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'json_configuration');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'cpb_enabled');
    }

    private function removeEmptyConfig()
    {
        $builderJsConfig = $this->configValueFactory->create()
            ->load(\Buildateam\CustomProductBuilder\Helper\Data::XPATH_BUILDER_JS, 'path');
        if (empty($builderJsConfig->getValue())) {
            $builderJsConfig->delete();
        }

        $builderThemeConfig = $this->configValueFactory->create()
            ->load(\Buildateam\CustomProductBuilder\Helper\Data::XPATH_BUILDER_THEME_JS, 'path');
        if (empty($builderThemeConfig->getValue())) {
            $builderThemeConfig->delete();
        }
    }

    /**
     * @inheritDoc
     */
    public function revert()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $fieldList = [
            'price',
            'special_from_date',
            'special_to_date',
            'cost',
            'tier_price',
            'weight',
            'tax_class_id'
        ];
        $newType = \Buildateam\CustomProductBuilder\Model\Product\Type::TYPE_CODE;
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (in_array($newType, $applyTo)) {
                unset($applyTo[array_search($newType, $applyTo)]);
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
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
