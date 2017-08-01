<?php


namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Product;


class Export extends \Magento\Backend\App\Action
{

    public function execute()
    {
        $product_id     = $this->getRequest()->getParam('product_id');
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $product        = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
        $productConfig  = $product->getData('json_configuration');

        return $productConfig;
    }

}