<?php


namespace Buildateam\CustomProductBuilder\Controller\Product;

use \Magento\Backend\App as AdminApp;

class Uploadimage extends \Magento\Framework\App\Action\Action
{


    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonProductContent;
    protected $_auth;
    protected $_fileSystem;
    protected $_storeInterface;
    protected $_helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Filesystem $fileSystem,
        AdminApp\Action\Context $adminContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Buildateam\CustomProductBuilder\Helper\Data $helper
    ) {
        $this->_auth                = $adminContext->getBackendUrl();
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

        $imageUrl   =  $this->_storeInterface->getStore()->getBaseUrl();
        $mediaPath  = $this->_fileSystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath('customproductbuilder');


        if (!file_exists($mediaPath)) {
            mkdir($mediaPath, 0777, true);
        }
        $media      = $mediaPath . 'custom/';
        $fileName = (time()+microtime(true)).'.'.$this->_request->getParam('type');
        file_put_contents("$mediaPath/$fileName",base64_decode(file_get_contents('php://input')));

        $imageBaseUrl = $this->_storeInterface->getStore()->getBaseUrl('media')."customproductbuilder/$fileName";
        $body           = json_encode($imageBaseUrl);
        $result         = $this->resultFactory->create('raw');
        $result->setHeader("Content-Type", 'application/json');
        $result->setContents($body);

        return $result;
        
    }

}