<?php

namespace Buildateam\CustomProductBuilder\Model\Plugin;

use \Magento\Catalog\Model\Product;
use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Framework\DataObject;

class CatalogProductTypeAll
{
    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    public function __construct(ProductMetadataInterface $productMetadata)
    {
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
    }

    /**
     * @param $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareForCartAdvanced($subject, array $result)
    {
        $this->addOptions($subject, $result);

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

    /**
     * @param $subject
     * @param callable $proceed
     * @param DataObject $buyRequest
     * @param $product
     * @param $processMode
     * @return mixed
     */
    public function aroundProcessConfiguration($subject, callable $proceed, DataObject $buyRequest, $product)
    {
        $products = $proceed($buyRequest, $product);
        $this->addOptions($subject, $products);

        return $products;
    }

    /**
     * @param $subject
     * @param $result
     */
    public function addOptions($subject, $result)
    {
        /** @var Product $product */
        foreach ($result as &$product) {
            if (is_null($product->getCustomOption('info_buyRequest'))) {
                continue;
            }

            /* Retrieve technical data of product that was added to cart */
            $buyRequest = $product->getCustomOption('info_buyRequest')->getData('value');
            if ($this->_isJsonInfoByRequest) {
                $productInfo = json_decode($buyRequest);
            } else {
                $productInfo = @unserialize($buyRequest);
            }

            if (!isset($productInfo['properties']) || $product->getCustomOption('additional_options')) {
                continue;
            }

            $addOptions = [];
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
            $product->addCustomOption('additional_options', $this->_isJsonInfoByRequest ? json_encode($addOptions) : serialize($addOptions));
        }
    }
}