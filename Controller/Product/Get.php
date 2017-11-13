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
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;
use \Magento\Framework\Controller\ResultFactory;
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