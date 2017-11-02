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
namespace Buildateam\CustomProductBuilder\Pricing\Plugin;

use \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use \Magento\Framework\Event\ManagerInterface;

class ConfiguredPrice
{
    /**
     * @var null|ItemInterface
     */
    protected $_item;

    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    public $_eventManager;

    /**
     * ConfiguredPrice constructor.
     * @param ManagerInterface $eventManager
     */
    public function __construct(ManagerInterface $eventManager)
    {
        $this->_eventManager = $eventManager;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param ItemInterface $item
     * @return $this
     */
    public function aroundSetItem($subject, callable $proceed, ItemInterface $item)
    {
        $this->_item = $item;

        return $proceed($item);
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return float
     */
    public function aroundGetValue($subject, callable $proceed)
    {
        $product = $this->_item->getProduct();
        $qty = $this->_item->getQty();

        return $product->getFinalPrice($qty);
    }
}