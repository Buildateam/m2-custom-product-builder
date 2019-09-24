<?php
/**
 * dailypromo
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
 * @package    dailypromo
 * @author     Denis Kopylov <dv.kopylov@profit-soft.pro>
 * @copyright  Copyright (c) 2016 Profit Soft (http://profit-soft.pro/)
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0 (Apache-2.0)
 */

namespace Buildateam\CustomProductBuilder\CustomerData\Plugin;

use Magento\Checkout\CustomerData\AbstractItem as BaseAbstractItem;
use \Magento\Framework\App\ProductMetadataInterface;
use Magento\Quote\Model\Quote\Item;
use \Magento\Framework\Serialize\SerializerInterface;

class AbstractItem
{
    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * AbstractItem constructor.
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Quote\Model\Quote $quote
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        \Magento\Quote\Model\Quote $quote,
        SerializerInterface $serializer
    ) {
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
        $this->serializer = $serializer;
    }

    public function aroundGetItemData(BaseAbstractItem $subject, callable $proceed, Item $item)
    {
        $buyRequest = $item->getProduct()->getCustomOption('info_buyRequest')->getValue();

        if ($this->_isJsonInfoByRequest) {
            $productInfo = json_decode($buyRequest, true);
        } else {
            $productInfo = $this->serializer->unserialize($buyRequest);
        }

        $result = $proceed($item);
        if (isset($productInfo['configid'])) {
            $result['configure_url'] = $result['configure_url'] . '#configid=' . $productInfo['configid'];
        }

        return $result;
    }
}
