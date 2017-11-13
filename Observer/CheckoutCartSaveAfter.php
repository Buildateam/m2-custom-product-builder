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

use \Magento\Framework\App\ProductMetadataInterface;
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
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    /**
     * @param ShareableLinksFactory $factory
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ShareableLinksFactory $factory, ProductMetadataInterface $productMetadata)
    {
        $this->_factory = $factory;
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
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
        if ($this->_isJsonInfoByRequest) {
            $value = json_decode($buyRequest);
        } else {
            $value = @unserialize($buyRequest);
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