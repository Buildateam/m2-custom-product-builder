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

class Export extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_jsonHelper          = $jsonHelper;
        $this->_resultPageFactory   = $resultFactory;
        $this->_productRepository   = $productRepository;
        $this->resultRawFactory     = $resultRawFactory;
        $this->fileFactory          = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Return Json_Configuration from a specific product
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $productId     = (int)$this->getRequest()->getParam('id', 0);
        $product       = $this->_productRepository->getById($productId);
        $productConfig = $product->getData('json_configuration');

        if (!$productConfig) $productConfig = $this->_getBaseConfig($product);

        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setHeader("Content-Type", 'application/json');
        $resultRaw->setContents($productConfig);

        return $resultRaw;

    }
    
    protected function _getBaseConfig($product)
    {
        $name = json_encode($product->getName());
        $price = json_encode((float)$product->getPrice());

        return <<<JSON
{
  "settings": {
    "isAdmin": true,
    "theme": {
      "id": "alpine-white"
    },
    "views": {
      "front": true,
      "back": false,
      "left": false,
      "right": false,
      "top": false,
      "bottom": false
    },
    "currentView": "front",
    "defaultView": "front",
    "viewControls": "arrows",
    "hasSummary": true,
    "layout": "col-tabs",
    "currency": "USD",
    "cdnPath": "https://magento.thecustomproductbuilder.com/media/"
  },
  "data": {
    "name": null,
    "base": {
      "price": null,
      "image": {}
    },
    "panels": [],
    "layers": [],
    "price": null,
    "isFetchingCategories": true
  }
}
JSON;
        
    }

}