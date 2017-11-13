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
