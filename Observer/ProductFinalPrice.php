<?php
/**
 * cpb
 *
 * NOTICE OF LICENSE
 *
 * Copyright 2016 Profit Soft (http://profit-soft.pro/)
 *
 * Licensed under the Apache License, Version 2.0 (the “License”);
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an “AS IS” BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @package    cpb
 * @author     Denis Kopylov <dv.kopylov@profit-soft.pro>
 * @copyright  Copyright (c) 2016 Profit Soft (http://profit-soft.pro/)
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0 (Apache-2.0)
 */

namespace Buildateam\CustomProductBuilder\Observer;

use \Buildateam\CustomProductBuilder\Helper\Data as Helper;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;

class ProductFinalPrice implements ObserverInterface
{
    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var array
     */
    protected $_jsonConfig = [];

    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    /**
     * ProductFinalPrice constructor.
     * @param ProductRepository $productRepository
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductRepository $productRepository, ProductMetadataInterface $productMetadata)
    {
        $this->_productRepository = $productRepository;
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        if (is_null($product->getCustomOption('info_buyRequest'))) {
            return;
        }

        $finalPrice = $product->getPrice();

        /* Retrieve technical data of product that was added to cart */
        $buyRequest = $product->getCustomOption('info_buyRequest')->getData('value');
        if ($this->_isJsonInfoByRequest) {
            $productInfo = json_decode($buyRequest);
        } else {
            $productInfo = @unserialize($buyRequest);
        }

        if (!isset($productInfo['technicalData'])) {
            return;
        }
        $technicalData = $productInfo['technicalData'];

        if (!isset($this->_jsonConfig[$product->getId()])) {
            $productRepo = $this->_productRepository->getById($product->getId());
            $this->_jsonConfig[$product->getId()] = json_decode($productRepo->getData(Helper::JSON_ATTRIBUTE));
        }
        $jsonConfig = $this->_jsonConfig[$product->getId()];
        if (is_null($jsonConfig)) {
            return;
        }

        foreach ($jsonConfig->data->panels as $panel) {
            foreach ($technicalData as $techData) {
                if ($panel->id == $techData['panel']) {
                    foreach ($panel->categories as $category) {
                        if ($category->id == $techData['category']) {
                            foreach ($category->options as $option) {
                                if ($option->id == $techData['option']) {
                                    $finalPrice += $option->price;
                                }
                            }
                        }
                    }
                }
            }
        }

        $product->setFinalPrice($finalPrice);
    }
}
