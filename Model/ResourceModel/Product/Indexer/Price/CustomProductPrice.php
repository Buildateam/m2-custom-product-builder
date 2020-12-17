<?php
namespace Buildateam\CustomProductBuilder\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query\BaseFinalPrice;

class CustomProductPrice extends Price\SimpleProductPrice
{
    /**
     * @param BaseFinalPrice $baseFinalPrice
     * @param IndexTableStructureFactory $indexTableStructureFactory
     * @param TableMaintainer $tableMaintainer
     * @param BasePriceModifier $basePriceModifier
     * @param string $productType
     */
    public function __construct(
        BaseFinalPrice $baseFinalPrice,
        Price\IndexTableStructureFactory $indexTableStructureFactory,
        TableMaintainer $tableMaintainer,
        Price\BasePriceModifier $basePriceModifier,
        $productType = \Buildateam\CustomProductBuilder\Model\Product\Type::TYPE_CODE
    ) {
        parent::__construct(
            $baseFinalPrice, $indexTableStructureFactory, $tableMaintainer, $basePriceModifier, $productType
        );
    }
}
