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
namespace Buildateam\CustomProductBuilder\Model\Plugin\Order;

use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class Item
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_shareLinksFactory;

    public function __construct(
        ShareableLinksFactory $factory
    )
    {
        $this->_shareLinksFactory = $factory;
    }

    public function afterGetProduct(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        $buyRequest = $subject->getProductOptionByCode('info_buyRequest');

        if (isset($buyRequest['configid'])) {
            $configModel = $this->_shareLinksFactory->create()->loadByVariationId($buyRequest['configid']);
            if ($configModel->getId()) {
                $result->setImage($configModel->getImage())
                    ->setSmallImage($configModel->getImage())
                    ->setThumbnail($configModel->getImage());
            }
        }
        return $result;
    }
}