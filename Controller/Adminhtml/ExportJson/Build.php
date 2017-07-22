<?php

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\ExportJson;


class Build extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    protected $storeManager;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory

    )
    {
        parent::__construct($context);
        $this->resultFactory = $resultJsonFactory;
    }

    public function execute()
    {
        //$params         = $this->getRequest()->getParams();
        $fileName       = 'product-builder.json';
        $product_id     = 2047;
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $fileFactory    = $objectManager->create('\Magento\Framework\App\Response\Http\FileFactory');
        $dir            = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
        $varDir         = $dir->getDefaultConfig();
        $product        = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
        $productConfig  = $product->getData('json_configuration');

        $fileFactory    ->create(
            $fileName,
            $productConfig,
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
            'application/json'
        );

        return $fileFactory;

    }

    protected function saveJsonDir($varDir, $productConfig)
    {
        echo file_put_contents('./'.$varDir['var']['path'].'/product-builder.json',$productConfig);
    }

}