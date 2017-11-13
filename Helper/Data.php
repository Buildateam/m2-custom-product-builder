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

namespace Buildateam\CustomProductBuilder\Helper;


use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\Filesystem;
use \Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const JSON_ATTRIBUTE = 'json_configuration';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;
    protected $_storeManager;

    public function __construct(
        Context $context,
        Filesystem $fileSystem,
        StoreManagerInterface $storeManager)
    {
        parent::__construct($context);
        $this->_fileSystem = $fileSystem;
        $this->_storeManager = $storeManager;
    }

    /**
     * retrieve JsonData decoded
     */
    public function getJsonDataDecoded($data)
    {
        $dataJson = json_decode($data);
        return $dataJson;
    }

    /**
     * Return Custom Builder Product file
     */
    public function getJsonBuilderFile()
    {
        $params = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();

        /** get json content from product */
        echo file_put_contents(Mage::getBaseDir('var') . '/test.json', "Hello World. Testing!");

        $handle = fopen("./var/product-builder.json", "w+");
        header('Access-Control-Allow-Origin: *');
        header('Content-type: application/json');

        echo json_encode($handle);
    }

    /**
     * validating json format
     * @param $string
     * @return mixed
     */
    public function validate($string)
    {
        // decode the JSON data
        $result = json_decode($string);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            // throw the Exception or exit // or whatever :)
            return ($error);
        }

        // everything is OK
        return '';
    }

    /**
     * @param $base64Image
     * @return string
     */
    public function uploadImage($base64Image)
    {
        $mediaPath = $this->_fileSystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath('catalog/product/customproductbuilder');

        if (!file_exists($mediaPath)) {
            mkdir($mediaPath, 0777, true);
        }
        $fileName = $this->_request->getParam('configid') . '.' . $this->_request->getParam('type');
        file_put_contents("$mediaPath/$fileName", base64_decode($base64Image));

        return "customproductbuilder/$fileName";
    }
}
