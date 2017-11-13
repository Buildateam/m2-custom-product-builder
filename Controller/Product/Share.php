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

namespace Buildateam\CustomProductBuilder\Controller\Product;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Controller\ResultFactory;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;
use \Magento\Framework\Data\Form\FormKey\Validator;

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
     * Share constructor.
     *
     * @param Context $context
     * @param ShareableLinksFactory $factory
     * @param Validator $validator
     */
    public function __construct(
        Context $context,
        ShareableLinksFactory $factory,
        Validator $validator)
    {
        $this->_formKeyValidator = $validator;
        $this->_shareLinksFactory = $factory;
        $this->_resultFactory = $context->getResultFactory();
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

        $configModel = $this->_shareLinksFactory->create()->loadByConfigId($this->getRequest()->getParam('configid'));
        if ($configModel->getId()) {
            $result = [
                'success' => true,
                'message' => __('Product configuration successfully saved.'),
                'configid' => $configModel->getConfigId()
            ];
        } else {
            $result = [
                'success' => false,
                'message' => __('Config didn\'t save. Please, try again later.'),
            ];
        }

        $response = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData($result);

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