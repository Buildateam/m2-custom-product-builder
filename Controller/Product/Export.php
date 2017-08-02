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
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->_jsonHelper          = $jsonHelper;
        $this->_resultPageFactory   = $resultFactory;
        $this->_productRepository   = $productRepository;
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

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $response->setHeader('Content-type', 'text/plain');

        if(!isset($productConfig) || empty($productConfig)) {
            $response->setContents(
                $this->_jsonHelper->jsonEncode(
                    [
                        'success'       => false,
                        'configuration' => 'This product has no specific settings',
                    ]
                )
            );
            return $response;
        }

        $response->setContents(
            $this->_jsonHelper->jsonEncode(
                [
                    'success'       => true,
                    'configuration' => $productConfig,
                ]
            )
        );

        return $response;

    }

}