<?php
/**
 * Copyright (c) 2017 Indigo Geeks, Inc. All rights reserved.
 *
 * General.
 * The custom product builder software and documentation accompanying this License
 * whether on disk, in read only memory, on any other media or in any other form (collectively
 * the “Software”) are licensed, not sold, to you by copyright holder, Indigo Geeks, Inc.
 * (“Buildateam”) for use only under the terms of this License, and Buildateam reserves all rights
 * not expressly granted to you. The rights granted herein are limited to Buildateam’s intellectual
 * property rights in the Buildateam Software and do not include any other patents or
 * intellectual property rights. You own the media on which the Buildateam Software is
 * recorded but Buildateam and/or Buildateam’s licensor(s) retain ownership of the Software
 * itself.
 *
 * Permitted License Uses and Restrictions.
 * This License allows you to install and use one (1) copy of the Software.
 * This License does not allow the Software to exist on more than one production domain.
 * Except as and only to the extent expressly permitted in this License or by applicable
 * law, you may not copy, decompile, reverse engineer, disassemble, attempt to derive
 * the source code of, modify, or create derivative works of the Software or any part
 * thereof. Any attempt to do so is a violation of the rights of Buildateam and its licensors of
 * the Software. If you breach this restriction, you may be subject to prosecution and
 * damages.
 *
 * Transfer.
 * You may not rent, lease, lend or sublicense the Software.
 *
 * Termination.
 * This License is effective until terminated. Your rights under this
 * License will terminate automatically without notice from Buildateam if you fail to comply
 * with any term(s) of this License. Upon the termination of this License, you shall cease
 * all use of the Buildateam Software and destroy all copies, full or partial, of the Buildateam
 * Software.
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Controller\Product;

use \Magento\Framework\Controller\ResultFactory;
use \Magento\Backend\App as AdminApp;

/**
 * Class Import
 * @package Buildateam\CustomProductBuilder\Controller\Product
 */
class Import extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    /**
     * @var \Buildateam\CustomProductBuilder\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_auth;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Buildateam\CustomProductBuilder\Helper\Data $helper
     * @param AdminApp\Action\Context $adminContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Buildateam\CustomProductBuilder\Helper\Data $helper,
        AdminApp\Action\Context $adminContext
    ) {
        $this->_auth = $adminContext->getBackendUrl();
        $this->_jsonHelper = $jsonHelper;
        $this->_resultPageFactory = $resultFactory;
        $this->_productRepository = $productRepository;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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
            $jsonData = json_decode($jsonData);
            $jsonData->data->name = $product->getName();
            $jsonData->data->base->price = $product->getPrice();
            $jsonData = json_encode($jsonData);
            $this->_helper->saveJsonConfiguration($product->getId(), $jsonData);
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
