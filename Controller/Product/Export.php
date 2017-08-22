<?php

namespace Buildateam\CustomProductBuilder\Controller\Product;

use Magento\Framework\Controller\ResultFactory;

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
        //$fileName = 'product-builder.json';
        //$this->fileFactory->create(
        //    $fileName,
        //    $productConfig
        //);

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
    "cdnPath": `https://magento.thecustomproductbuilder.com/media/`
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