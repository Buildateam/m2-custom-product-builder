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

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class CheckoutCartSaveAfter implements ObserverInterface
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_factory;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serializer;

    /**
     * @param ShareableLinksFactory $factory
     */
    public function __construct(ShareableLinksFactory $factory, \Magento\Framework\Serialize\Serializer\Json $serializer)
    {
        $this->_factory = $factory;
        $this->_serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $quote = $observer->getCart()->getQuote();
        $items = $quote->getAllItems();
        $lastAddedItem = array_slice($items, -1, 1);

        $buyRequest = $lastAddedItem[0]->getProduct()->getCustomOption('info_buyRequest')->getValue();
        $value = @unserialize($buyRequest);
        if ($buyRequest !== 'b:0;' && $value === false) {
            $value = $this->_serializer->unserialize($buyRequest);
        }

        $techData = json_encode($value['technicalData']);

        $configModel = $this->_factory->create();
        if (isset($value['configid']) && empty($configModel->loadByConfigId($value['configid'])->getData())) {
            $configModel->setData(array(
                'technical_data' => $techData,
                'config_id' => $value['configId']
            ));
            $configModel->save();
        }
    }
}