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
use Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Json\Helper\Data as JsonHelper;

class Get extends Action
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_factory;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;
    protected $_jsonHelper;

    /**
     * Get constructor.
     *
     * @param Context $context
     * @param ShareableLinksFactory $factory
     */
    public function __construct(
        Context $context,
        ShareableLinksFactory $factory,
        JsonHelper $jsonHelper
    )
    {
        $this->_factory = $factory;
        $this->_resultFactory = $context->getResultFactory();
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $configId = $this->getRequest()->getParam('configid');
        $sharableLink = $this->_factory->create()->loadByConfigId($configId);

        $response = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
        if ($sharableLink->getId()) {
            $linkData = [
                'configid' => $sharableLink->getConfigId(),
                'technicalData' => $this->_jsonHelper->jsonDecode($sharableLink->getTechnicalData())
            ];
            $response->setData($linkData);
        } else {
            $response->setData(
                [
                    'success' => false,
                    'message' => __('Couldn\'t load share configuration')
                ]
            );
        }

        return $response;
    }
}