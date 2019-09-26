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

namespace Buildateam\CustomProductBuilder\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\View\Page\Config;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\View\Context;
use \Magento\Framework\Registry;

/**
 * Class BeforeProductView
 * @package Buildateam\CustomProductBuilder\Observer
 */
class BeforeProductView implements ObserverInterface
{
    /**
     * @var Config
     */
    protected $_pageConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * BeforeProductView constructor.
     * @param Config $pageConfig
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Config $pageConfig, Context $context, Registry $coreRegistry)
    {
        $this->_pageConfig = $pageConfig;
        $this->_logger = $context->getLogger();
        $this->_coreRegistry = $coreRegistry;
    }

    public function execute(Observer $observer)
    {
        try {
            if (null !== $product = $this->_coreRegistry->registry('product')) {
                if ($product->getData('json_configuration')) {
                    $this->_pageConfig->setElementAttribute(
                        'html',
                        'printable',
                        "true"
                    );
                }
            }
        } catch (LocalizedException $e) {
            $this->_logger->critical($e->getMessage());
        }
    }
}
