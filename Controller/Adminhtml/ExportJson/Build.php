<?php

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\ExportJson;
use \Magento\Framework\App\Filesystem\DirectoryList;

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
        $params         = $this->getRequest()->getParams();
        $paramsUrl      = explode('/',$params['productid']) ;
        $product_id     = $paramsUrl[6] ;
        $fileName       = 'product-builder-'.$product_id.'.json';
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $fileFactory    = $objectManager->create('\Magento\Framework\App\Response\Http\FileFactory');
        $ioAdapter      = $objectManager->create('Magento\Framework\Filesystem\Io\File');
        $dir            = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
        $request        = $objectManager->create('\Magento\Framework\App\Request\Http');
        $varDir         = $dir->getDefaultConfig();
        $product        = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
        $productConfig  = $product->getData('json_configuration');

        $ioAdapter->open(array('path'=>DirectoryList::VAR_DIR));
        $ioAdapter->write($fileName, $productConfig, 0777);

        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$fileName."");
        header("Content-Transfer-Encoding: binary");
        header("Content-Type: application/json");
        readfile(DirectoryList::VAR_DIR.'/'.$fileName);

        $ioAdapter->rm($fileName);
    }

    protected function saveJsonDir($varDir, $productConfig)
    {
        echo file_put_contents('./'.$varDir['var']['path'].'/product-builder.json',$productConfig);
    }

}