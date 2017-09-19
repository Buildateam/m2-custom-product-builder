<?php

namespace Buildateam\CustomProductBuilder\Model\Plugin;

use \Magento\Catalog\Model\Product;

class CatalogProductTypeAll
{
    /**
     * @param $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareForCartAdvanced($subject, array $result)
    {
        /** @var Product $product */
        foreach ($result as &$product) {
            if (is_null($product->getCustomOption('info_buyRequest'))) {
                continue;
            }

            /* Retrieve technical data of product that was added to cart */
            $productInfo = unserialize($product->getCustomOption('info_buyRequest')->getData('value'));
            if (!isset($productInfo['properties'])) {
                continue;
            }

            $addOptions = $product->getCustomOption('additional_options') ?? [];
            if (is_string($addOptions)) {
                $addOptions = unserialize($addOptions);
            }

            foreach ($productInfo['properties'] as $propertyName => $propertyValue) {
                $propertyValue = preg_replace('/(.*)(\s+\(.*\))/', '$1', $propertyValue);
                $addOptions[] = [
                    'label' => __($propertyName)->getText(),
                    'value' => $propertyValue,
                    'print_value' => $propertyValue,
                    'option_id' => null,
                    'option_type' => 'text',
                    'custom_view' => false,
                ];
            };
            $product->addCustomOption('additional_options', serialize($addOptions));
        }
        return $result;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param Product $product
     * @return mixed
     */
    public function aroundGetOrderOptions($subject, callable $proceed, Product $product)
    {
        $optionArr = $proceed($product);
        if ($additionalOptions = $product->getCustomOption('additional_options')) {
            $optionArr['additional_options'] = unserialize($additionalOptions->getValue());
        }

        return $optionArr;
    }
}