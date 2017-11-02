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
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;
use Magento\Framework\Data\Form\FormKey\Validator;

class Share extends Action
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_shareLinksFactory;

    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @var ResultFactory
     */
    protected $_resultFactory;

    /**
     * @var
     */
    protected $_jsonHelper;

    /**
     * Share constructor.
     *
     * @param Context $context
     * @param ShareableLinksFactory $factory
     * @param Validator $validator
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        Context $context,
        ShareableLinksFactory $factory,
        Validator $validator,
        JsonHelper $jsonHelper)
    {
        $this->_formKeyValidator = $validator;
        $this->_shareLinksFactory = $factory;
        $this->_resultFactory = $context->getResultFactory();
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_validateRequest() !== true) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $request = $this->getRequest()->getParams();
        $productId = $request['product'];

        $techData = $request['technicalData'];
        $configModel = $this->_shareLinksFactory->create();

        $configModel->setData(array(
            'product_id' => $productId,
            'technical_data' => $this->_jsonHelper->jsonEncode($techData),
            'config_id' => $request['configid']
        ));

        $configModel->save();
        $response = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setJsonData(
            $this->_jsonHelper->jsonEncode(
                [
                    'success' => true,
                    'message' => __('Product configuration successfully saved.'),
                ]
            )
        );

        return $response;
    }

    /**
     * @return mixed
     */
    protected function _validateRequest()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return false;
        }

        $requestParams = $this->getRequest()->getParams();
        foreach (['product', 'configid'] as $keyParam) {
            if (!isset($requestParams[$keyParam])) {
                return false;
            }
        }

        return true;
    }

}