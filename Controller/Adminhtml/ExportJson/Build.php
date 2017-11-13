<?php
/**
 * Copyright © 2017 Indigo Geeks, Inc. All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions
 * are met:
 *
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * All advertising materials mentioning features or use of this software must display the following acknowledgement:
 * This product includes software developed by the the organization.
 * Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this
 * software without specific prior written permission.
 *
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

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