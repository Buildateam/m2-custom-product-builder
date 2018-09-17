<?php
/**
 * Copyright (c) 2017 Indigo Geeks, Inc. All rights reserved.
 *
 * General.
 * The custom product builder software and documentation accompanying this License
 * whether on disk, in read only memory, on any other media or in any other form (collectively
 * the “Software”) are licensed, not sold, to you by copyright holder, Indigo Geeks, Inc.
 * (“Buildateam”) for use only under the terms of this License, and Buildateam reserves all rights
 * not expressly granted to you. The rights granted herein are limited to Buildateam’s intellectual
 * property rights in the Buildateam Software and do not include any other patents or
 * intellectual property rights. You own the media on which the Buildateam Software is
 * recorded but Buildateam and/or Buildateam’s licensor(s) retain ownership of the Software
 * itself.
 *
 * Permitted License Uses and Restrictions.
 * This License allows you to install and use one (1) copy of the Software.
 * This License does not allow the Software to exist on more than one production domain.
 * Except as and only to the extent expressly permitted in this License or by applicable
 * law, you may not copy, decompile, reverse engineer, disassemble, attempt to derive
 * the source code of, modify, or create derivative works of the Software or any part
 * thereof. Any attempt to do so is a violation of the rights of Buildateam and its licensors of
 * the Software. If you breach this restriction, you may be subject to prosecution and
 * damages.
 *
 * Transfer.
 * You may not rent, lease, lend or sublicense the Software.
 *
 * Termination.
 * This License is effective until terminated. Your rights under this
 * License will terminate automatically without notice from Buildateam if you fail to comply
 * with any term(s) of this License. Upon the termination of this License, you shall cease
 * all use of the Buildateam Software and destroy all copies, full or partial, of the Buildateam
 * Software.
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Checkout\Exception;
use \Magento\Framework\Message\ManagerInterface;
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
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * ProductFinalPrice constructor.
     * @param ProductRepository $productRepository
     * @param ProductMetadataInterface $productMetadata
     * @param ManagerInterface $massageManager
     */
    public function __construct(
        ProductRepository $productRepository,
        ProductMetadataInterface $productMetadata,
        ManagerInterface $massageManager
    )
    {
        $this->_productRepository = $productRepository;
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
        $this->_messageManager = $massageManager;
    }

    /**
     * @param EventObserver $observer
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();
        if (is_null($product->getCustomOption('info_buyRequest'))) {
            return;
        }
        /* Retrieve technical data of product that was added to cart */
        $buyRequest = $product->getCustomOption('info_buyRequest')->getData('value');
        if ($this->_isJsonInfoByRequest) {
            $productInfo = json_decode($buyRequest, true);
        } else {
            $productInfo = @unserialize($buyRequest);
        }

        if (!isset($productInfo['technicalData'])) {
            return;
        }

        if (!isset($this->_jsonConfig[$product->getId()])) {
            $productRepo = $this->_productRepository->getById($product->getId());
            $this->_jsonConfig[$product->getId()] = json_decode($productRepo->getData(Helper::JSON_ATTRIBUTE), true);
        }
        $jsonConfig = $this->_jsonConfig[$product->getId()];
        if (is_null($jsonConfig)) {
            return;
        }

        if (isset($productInfo['properties']['Item Customization - Colors'])) {
            $property = $productInfo['properties']['Item Customization - Colors'];
        } elseif (isset($productInfo['properties']['Colors'])) {
            $property = $productInfo['properties']['Colors'];
        } else {
            $property = '';
        }
        if ($property != '') {
            $parts = explode(' ', $property);
            $sku = trim(end($parts), '[]');
        }

        if (isset($productInfo['properties']['Item Customization - Order'])) {
            $printMethod = $productInfo['properties']['Item Customization - Order'];
        } elseif (isset($productInfo['properties']['Order'])) {
            $printMethod = $productInfo['properties']['Order'];
        } else {
            $printMethod = 'blank';
        }

        $printMethod = trim(strtolower($printMethod));

        if ($printMethod == 'blank') {
            $type = 'Blank';
        } elseif ($printMethod == 'with logo') {
            $type = 'Decorated';
        } elseif ($printMethod == 'sample') {
            $type = 'Sample';
        }

        $availablePrices = [];
        if (isset($sku)) {
            foreach ($jsonConfig['data']['prices'] as $price) {
                if ($price['sku'] == $sku && $price['type'] == $type) {
                    $availablePrices[] = $price;
                }
            }
            usort($availablePrices, function ($a, $b) {
                return $a['minQty'] - $b['minQty'];
            });

            $maxQty = 0;
            foreach ($jsonConfig['data']['inventory'] as $inventory) {
                if ($sku == $inventory['sku']) {
                    $maxQty = $inventory['qty'];
                    break;
                }
            }

            if ($printMethod == 'sample' && $observer->getQty() > 1) {
                throw new Exception(__('Requested quantity is not available'));
            }

            if ($observer->getQty() <= $maxQty) {
                foreach ($availablePrices as $key => $value) {
                    if ($observer->getQty() == $value['minQty']) {
                        $finalPrice = $value['price'];
                        break;
                    }
                    if ($observer->getQty() < $value['minQty']) {
                        if (isset($availablePrices[$key - 1])) {
                            $finalPrice = $availablePrices[$key - 1]['price'];
                            break;
                        } else {
                            throw new Exception(__('Requested quantity is not available'));
                        }
                    }
                }
                if (!isset($finalPrice) && $observer->getQty() > end($availablePrices)['minQty']) {
                    $finalPrice = end($availablePrices)['price'];
                }
            } else {
                throw new Exception(__('Requested quantity is not available'));
            }
        }

        if (isset($finalPrice)) {
            $product->setFinalPrice($finalPrice);
        }
    }
}
