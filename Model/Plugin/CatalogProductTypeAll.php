<?php
/**
 * Copyright © 2017 Indigo Geeks, Inc. All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions
 * are met:
 *
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * All advertising materials mentioning features or use of this software must display the following acknowledgement:
 * This product includes software developed by the the organization.
 * Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this
 * software without specific prior written permission.
 *
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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