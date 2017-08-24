<?php


namespace Buildateam\CustomProductBuilder\Controller\Product;

use \Magento\Backend\App as AdminApp;

class Uploadimage extends \Magento\Framework\App\Action\Action
{


    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonHelper;
    protected $_jsonProductContent;
    protected $_auth;
    protected $_fileSystem;
    protected $_storeInterface;
    protected $_helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Filesystem $fileSystem,
        AdminApp\Action\Context $adminContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Buildateam\CustomProductBuilder\Helper\Data $helper
    ) {
        $this->_auth                = $adminContext->getBackendUrl();
        $this->_jsonHelper          = $jsonHelper;
        $this->_resultPageFactory   = $resultFactory;
        $this->_productRepository   = $productRepository;
        $this->_fileSystem          = $fileSystem;
        $this->_storeInterface      = $storeManager;
        $this->_helper              = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $i=1;
        //$imageUrl= "http://i.imgur.com/fHyEMsl.jpg";
        //$fileName = rand() . $this->_helper->getDataByKey($_FILES['image'], 'name');
        $imageUrl   =  $this->_storeInterface->getStore()->getBaseUrl();
        $mediaPath  = $this->_fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
        $media      = $mediaPath . 'custom/';
        $fileName   = $this->_helper->getDataByKey($_FILES['image'], 'name');
        $fileTmp    = $this->_helper->getDataByKey($_FILES['image'], 'tmp_name');

        $this->_helper->createFolder($media);
        $imageBaseUrl   =  $this->_moveUploadedFile($fileTmp, $media, $fileName, $imageUrl);
        $body           = json_encode($imageBaseUrl);
        $result         = $this->resultFactory->create('raw');
        $result->setHeader("Content-Type", 'application/json');
        $result->setContents($body);

        return $result;
        
    }

    protected function _moveUploadedFile($fileTmp, $media, $fileName, $imageUrl)
    {
        if (move_uploaded_file($fileTmp, $media . $fileName)) {
            return $imageUrl . 'pub/media/custom/' . $fileName;
        }
    }

}