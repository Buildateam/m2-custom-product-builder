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

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Product;


class Import extends \Magento\Backend\App\Action
{

    protected $_jsonProductContent;

    public function execute()
    {
        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $productId      = $this->getRequest()->getParam('id');
        $product        = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

        $jsonData = !empty($_FILES['product']['tmp_name']['json_configuration'])
            ? file_get_contents($_FILES['product']['tmp_name']['json_configuration'])
            : $product->getData('json_configuration');

        if (!empty($jsonData)) {
            $this->_jsonProductContent = $jsonData;
            $validate = $this->_objectManager->create('Buildateam\CustomProductBuilder\Helper\Data')->validate($this->_jsonProductContent);

            if (isset($this->_jsonProductContent) && !empty($this->_jsonProductContent) && $validate) {

                $result = [
                    'status'    => 'error',
                    'msg'       => $validate
                ];

                $this->sendJsonResponse($result, 200);
            }

            $product->setJsonConfiguration($this->_jsonProductContent);
            $product->save();

            $result = [
                'status'    => 'success',
                'msg'       => 'Custom Product Builder imported with success!'
            ];

            $this->sendJsonResponse($result, 200);
        }

    }

    protected function sendJsonResponse($data = [], $code = 200, Exception $exception = null)
    {
        $jsonData = Zend_Json_Encoder::encode($data);

        /** @var Mage_Core_Controller_Response_Http $response */
        $response = Mage::app()->getResponse();
        $response->setHeader('Content-type', 'application/json', true)
            ->setHttpResponseCode($code)
            ->setBody($jsonData);

        if ($exception) {
            $response->setException($exception);
        }

        $response->sendResponse();
        die();
    }

}