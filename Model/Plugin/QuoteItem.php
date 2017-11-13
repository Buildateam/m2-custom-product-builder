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

namespace Buildateam\CustomProductBuilder\Model\Plugin;

use \Magento\Quote\Model\Quote\Item;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class QuoteItem
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
    public function aroundCompareOptions(\Magento\Quote\Model\Quote\Item $subject, callable $proceed, $options1, $options2)
    {
        foreach ($options1 as $option) {
            if ($option->getCode() == 'info_buyRequest') {
                $code = $option->getCode();
                $value = unserialize($option->getValue());
                if (!isset($value['technicalData'])) {
                    continue;
                }

                if (!isset($options2[$code]) || unserialize($options2[$code]->getValue())['technicalData'] != $value['technicalData']) {
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
        $productInfo = unserialize($subject->getProduct()->getCustomOption('info_buyRequest')->getValue());
        if (isset($productInfo['configid'])) {
            $configModel = $this->_shareLinksFactory->create()->loadByConfigId($productInfo['configid']);
            if ($configModel->getId()) {
                $result->getProduct()
                    ->setImage($configModel->getImage())
                    ->setSmallImage($configModel->getImage())
                    ->setThumbnail($configModel->getImage());
            }
        }
        return $result;
    }
}
