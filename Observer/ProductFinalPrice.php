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
use \Magento\Framework\App\RequestInterface;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;

class ProductFinalPrice implements ObserverInterface
{
    protected $_request;
    protected $_checkoutSession;
    protected $_productRepository;
    protected $_jsonConfig = [];

    public function __construct(
        RequestInterface $request,
        ProductRepository $productRepository
    )
    {
        $this->_request = $request;
        $this->_productRepository = $productRepository;
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
        $productInfo = unserialize($product->getCustomOption('info_buyRequest')->getData('value'));
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
