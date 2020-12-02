<?php
namespace Buildateam\CustomProductBuilder\Model\Product;

class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'custom';
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {

    }
}
