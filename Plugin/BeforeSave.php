<?php

namespace Buildateam\CustomProductBuilder\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Save as Subject;

/**
 * Class BeforeSave
 * @package Buildateam\CustomProductBuilder\Plugin
 */
class BeforeSave
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productRepository;

    /**
     * BeforeSave constructor.
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    public function beforeExecute(Subject $subject)
    {
        $productId = $subject->getRequest()->getParam('id');
        if (isset($productId)) {
            $params = $subject->getRequest()->getPostValue();
            $product = $this->productRepository->getById($productId);
            $params['product']['json_configuration'] = $product->getJsonConfiguration();
            $subject->getRequest()->setPostValue('product', $params['product']);
        }
    }
}
