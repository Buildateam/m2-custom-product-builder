<?php

namespace Buildateam\CustomProductBuilder\Controller\Product;

use Magento\Framework\Controller\ResultFactory;
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
    ) {
        $this->_auth       = $adminContext->getBackendUrl();
        $this->_jsonHelper          = $jsonHelper;
        $this->_resultPageFactory   = $resultFactory;
        $this->_productRepository   = $productRepository;
        $this->_helper              = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $productId         = (int)$this->getRequest()->getParam('id',0);
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        //$secureCall     = 'XXX';
        //if (!$this->_auth->isLoggedIn())
        //{
        //    echo "ok";
        //}

        $response       = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'application/json');

        if (!$productId) {
            $this->setSendValidProductidResponse($response);
            return $response;
        }

        $product        = $this->_productRepository->getById($productId);
        $jsonData       = file_get_contents('php://input');

        if (!empty($jsonData) && $this->_helper->validate($jsonData)=="") {
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
                    'success'       => false,
                    'message'       => $validate,
                ]
            )
        );
    }

    protected function setSuccessResponse($response)
    {
        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success'       => true,
                    'message'       => 'Custom json configuration added with success!',
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
                    'success'       => true,
                    'message'       => 'Please, send a id param.',
                ]
            )
        );
    }

}