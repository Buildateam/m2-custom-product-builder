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
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Model\Plugin;

use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Quote\Model\Quote\Item;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;
use \Magento\Framework\Serialize\SerializerInterface;

/**
 * Class QuoteItem
 * @package Buildateam\CustomProductBuilder\Model\Plugin
 */
class QuoteItem
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_shareLinksFactory;

    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * QuoteItem constructor.
     * @param ShareableLinksFactory $factory
     * @param ProductMetadataInterface $productMetadata
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ShareableLinksFactory $factory,
        ProductMetadataInterface $productMetadata,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->_shareLinksFactory = $factory;
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
    }

    /**
     * Removed check if $code is in $this->notRepresentOptions
     * so if $byRequest options are different, we will have a new quote item
     *
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param callable $proceed
     * @param $options1
     * @param $options2
     * @return bool|callable
     */
    public function aroundCompareOptions(
        \Magento\Quote\Model\Quote\Item $subject,
        callable $proceed,
        $options1,
        $options2
    ) {
        foreach ($options1 as $option) {
            if ($option->getCode() == 'info_buyRequest') {
                $code = $option->getCode();
                if ($this->_isJsonInfoByRequest) {
                    $value = json_decode($option->getValue(), true);
                } else {
                    $value = $this->serializer->unserialize($option->getValue());
                }

                if (!isset($value['technicalData'])) {
                    continue;
                }

                if ($this->_isJsonInfoByRequest) {
                    $value2 = json_decode($options2[$code]->getValue(), true);
                } else {
                    $value2 = $this->serializer->unserialize($options2[$code]->getValue());
                }

                if (!isset($options2[$code]) || $value2['technicalData'] != $value['technicalData']) {
                    return false;
                }
            }
        }
        return $proceed($options1, $options2);
    }

    /**
     * Change product image for configurable product
     *
     * @param Item $subject
     * @param $result
     * @return mixed
     */
    public function afterSetProduct(Item $subject, $result)
    {
        if ($buyRequest = $subject->getProduct()->getCustomOption('info_buyRequest') != null) {
            $buyRequest = $subject->getProduct()->getCustomOption('info_buyRequest')->getValue();
        }

        if ($this->_isJsonInfoByRequest) {
            $productInfo = json_decode($buyRequest, true);
        } else {
            $productInfo = $this->serializer->unserialize($buyRequest);
        }

        if (isset($productInfo['configid'])) {
            $configModel = $this->_shareLinksFactory->create()->loadByVariationId($productInfo['configid']);
            if ($configModel->getId()) {
                $product = clone $result->getProduct();
                $product->setImage($configModel->getImage())
                    ->setSmallImage($configModel->getImage())
                    ->setThumbnail($configModel->getImage());
                $result->setData('product', $product);
            }
        }
        return $result;
    }
}
