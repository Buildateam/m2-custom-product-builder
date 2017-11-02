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

namespace Buildateam\CustomProductBuilder\Controller\Product;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class Get extends Action
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_factory;

    public function __construct(
        Context $context,
        ShareableLinksFactory $factory
    )
    {
        $this->_factory = $factory;
        parent::__construct($context);
    }

    public function execute()
    {
        $config_id = $this->getRequest()->getParam('configid');
        $sharebleLink = $this->_factory->create()->loadByConfigId($config_id);
        $techData = $sharebleLink->getData('technical_data');

        $this->getResponse()->setBody($techData);
    }
}