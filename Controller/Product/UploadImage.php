<?php


namespace Buildateam\CustomProductBuilder\Controller\Product;

use \Buildateam\CustomProductBuilder\Helper\Data;
use \Magento\Backend\App as AdminApp;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\Filesystem;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Store\Model\StoreManagerInterface;

class UploadImage extends \Magento\Framework\App\Action\Action
{


    protected $_resultPageFactory;
    protected $_productRepository;
    protected $_jsonProductContent;
    protected $_auth;
    protected $_fileSystem;
    protected $_storeInterface;
    protected $_helper;

    public function __construct(
        Context $context,
        PageFactory $resultFactory,
        ProductRepository $productRepository,
        Filesystem $fileSystem,
        AdminApp\Action\Context $adminContext,
        StoreManagerInterface $storeManager,
        Data $helper
    )
    {
        $this->_auth = $adminContext->getBackendUrl();
        $this->_resultPageFactory = $resultFactory;
        $this->_productRepository = $productRepository;
        $this->_fileSystem = $fileSystem;
        $this->_storeInterface = $storeManager;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $imagePath = $this->_helper->uploadImage(file_get_contents('php://input'));
        $imageBaseUrl = $this->_storeInterface->getStore()->getBaseUrl('media') . $imagePath;
        $body = json_encode($imageBaseUrl);
        $result = $this->resultFactory->create('raw');
        $result->setHeader("Content-Type", 'application/json');
        $result->setContents($body);

        return $result;

    }

}