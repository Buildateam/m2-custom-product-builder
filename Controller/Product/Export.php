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
        $this->resultRawFactory      = $resultRawFactory;
        $this->fileFactory           = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Return Json_Configuration from a specific product
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $params         = $this->getRequest()->getParams('product_id');
        $product        = $this->_productRepository->getById($params['product_id']);
        $productConfig  = $product->getData('json_configuration');

        $fileName = 'product-builder.json';
        $this->fileFactory->create(
            $fileName,
            $productConfig
        );

        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw;

    }

}