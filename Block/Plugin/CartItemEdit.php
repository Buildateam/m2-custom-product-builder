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

namespace Buildateam\CustomProductBuilder\Block\Plugin;

use \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Edit;
use \Magento\Framework\App\ProductMetadataInterface;

class CartItemEdit
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
     * @param Edit $subject
     * @param $result
     * @return string
     */
    public function afterGetConfigureUrl(Edit $subject, $result)
    {
        $buyRequest = $subject->getItem()->getProduct()->getCustomOption('info_buyRequest')->getValue();

        if ($this->_isJsonInfoByRequest) {
            $productInfo = json_decode($buyRequest);
        } else {
            $productInfo = @unserialize($buyRequest);
        }

        if (isset($productInfo['configid'])) {
            return $result . '#configid=' . $productInfo['configid'];
        }

        return $result;
    }
}