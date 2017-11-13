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

use \Buildateam\CustomProductBuilder\Helper\Data;
use \Magento\Backend\App as AdminApp;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Filesystem;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Store\Model\StoreManagerInterface;

class UploadImage extends \Magento\Framework\App\Action\Action
{


    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonProductContent;
    protected $_auth;
    protected $_fileSystem;
    protected $_storeInterface;
    protected $_helper;

    public function __construct(
        Context $context,
        PageFactory $resultFactory,
        ProductRepository $productRepository,
        Filesystem $fileSystem,
        AdminApp\Action\Context $adminContext,
        StoreManagerInterface $storeManager,
        Data $helper
    )
    {
        $this->_auth = $adminContext->getBackendUrl();
        $this->_resultPageFactory = $resultFactory;
        $this->_productRepository = $productRepository;
        $this->_fileSystem = $fileSystem;
        $this->_storeInterface = $storeManager;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $imagePath = $this->_helper->uploadImage(file_get_contents('php://input'));
        $imageBaseUrl = $this->_storeInterface->getStore()->getBaseUrl('media') . $imagePath;
        $body = json_encode($imageBaseUrl);
        $result = $this->resultFactory->create('raw');
        $result->setHeader("Content-Type", 'application/json');
        $result->setContents($body);

        return $result;

    }

}