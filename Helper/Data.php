<?php
/**
 * Copyright (c) 2017 Indigo Geeks, Inc. All rights reserved.
 *
 * General.
 * The custom product builder software and documentation accompanying this License
 * whether on disk, in read only memory, on any other media or in any other form (collectively
 * the “Software”) are licensed, not sold, to you by copyright holder, Indigo Geeks, Inc.
 * (“Buildateam”) for use only under the terms of this License, and Buildateam reserves all rights
 * not expressly granted to you. The rights granted herein are limited to Buildateam’s intellectual
 * property rights in the Buildateam Software and do not include any other patents or
 * intellectual property rights. You own the media on which the Buildateam Software is
 * recorded but Buildateam and/or Buildateam’s licensor(s) retain ownership of the Software
 * itself.
 *
 * Permitted License Uses and Restrictions.
 * This License allows you to install and use one (1) copy of the Software.
 * This License does not allow the Software to exist on more than one production domain.
 * Except as and only to the extent expressly permitted in this License or by applicable
 * law, you may not copy, decompile, reverse engineer, disassemble, attempt to derive
 * the source code of, modify, or create derivative works of the Software or any part
 * thereof. Any attempt to do so is a violation of the rights of Buildateam and its licensors of
 * the Software. If you breach this restriction, you may be subject to prosecution and
 * damages.
 *
 * Transfer.
 * You may not rent, lease, lend or sublicense the Software.
 *
 * Termination.
 * This License is effective until terminated. Your rights under this
 * License will terminate automatically without notice from Buildateam if you fail to comply
 * with any term(s) of this License. Upon the termination of this License, you shall cease
 * all use of the Buildateam Software and destroy all copies, full or partial, of the Buildateam
 * Software.
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
 * BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

namespace Buildateam\CustomProductBuilder\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Math\Random;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Data
 * @package Buildateam\CustomProductBuilder\Helper
 */
class Data extends AbstractHelper
{
    const JSON_ATTRIBUTE = 'json_configuration';
    const XPATH_BUILDER_MODE = 'cpb/development/mode';
    const XPATH_BUILDER_JS = 'cpb/development/cpb_js';
    const XPATH_BUILDER_THEME_JS = 'cpb/development/theme_js';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_fileSystem;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var Random
     */
    protected $_mathRandom;

    /**
     * Data constructor.
     * @param Context $context
     * @param Filesystem $fileSystem
     * @param StoreManagerInterface $storeManager
     * @param Random $random
     */
    public function __construct(
        Context $context,
        Filesystem $fileSystem,
        StoreManagerInterface $storeManager,
        Random $random
    ) {
        $this->_fileSystem = $fileSystem;
        $this->_storeManager = $storeManager;
        $this->_mathRandom = $random;
        parent::__construct($context);
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
     * @param bool $frontImage
     * @return string
     */
    public function uploadImage($base64Image, $frontImage = false)
    {
        $media = $this->_fileSystem
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaPath = $media->getAbsolutePath('catalog/product/customproductbuilder/' .
            ($frontImage ? 'variation' : 'configuration'));

        if (!file_exists($mediaPath)) {
            mkdir($mediaPath, 0777, true);
        }
        try {
            $variationId = $this->_request->getParam('configid') ?
                $this->_request->getParam('configid') : $this->_mathRandom->getRandomString(18);
        } catch (LocalizedException $e) {
            $this->_logger->critical($e->getMessage());
            return false;
        }

        $fileName = $variationId . '.' . $this->_request->getParam('type');
        $media->writeFile("$mediaPath/$fileName", base64_decode($base64Image));

        return ($frontImage ? '' : 'catalog/product/') . 'customproductbuilder/' .
            ($frontImage ? 'variation/' : 'configuration/') . $fileName;
    }

    /**
     * @param $path
     * @return string
     */
    public function getConfigValue($path)
    {
        $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
        return $value;
    }

    /**
     * @return string
     */
    public function getBuilderMode()
    {
        return $this->getConfigValue(self::XPATH_BUILDER_MODE);
    }
}
