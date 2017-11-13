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

use \Magento\Framework\Controller\ResultFactory;
use \Magento\Backend\App as AdminApp;


class Import extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonHelper;
    protected $_helper;
    protected $_jsonProductContent;
    protected $_auth;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Buildateam\CustomProductBuilder\Helper\Data $helper,
        AdminApp\Action\Context $adminContext
    )
    {
        $this->_auth = $adminContext->getBackendUrl();
        $this->_jsonHelper = $jsonHelper;
        $this->_resultPageFactory = $resultFactory;
        $this->_productRepository = $productRepository;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id', 0);
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'application/json');

        if (!$productId) {
            $this->setSendValidProductidResponse($response);
            return $response;
        }

        $product = $this->_productRepository->getById($productId);
        $jsonData = file_get_contents('php://input');

        if (!empty($jsonData) && $this->_helper->validate($jsonData) == "") {
            $product->setJsonConfiguration($jsonData);
            $product->save();
            $this->setSuccessResponse($response);
        } else {
            $this->setErrorResponse($response, $this->_helper->validate($jsonData));
        }

        return $response;

    }

    protected function setErrorResponse($response, $validate)
    {
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success' => false,
                    'message' => $validate,
                ]
            )
        );
    }

    protected function setSuccessResponse($response)
    {
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success' => true,
                    'message' => 'Custom json configuration added with success!',
                ]
            )
        );
    }

    protected function setSendValidProductidResponse($response)
    {
        $response->setStatusHeader(404);
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success' => true,
                    'message' => 'Please, send a id param.',
                ]
            )
        );
    }

}