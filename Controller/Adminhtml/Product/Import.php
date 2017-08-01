<?php
/**
 * Saraiva Platform | Fcamara
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.fcamara.com.br for more information.
 *
 * @category  ${MAGENTO_MODULE_NAMESPACE}
 * @package   ${MAGENTO_MODULE_NAMESPACE}_${MAGENTO_MODULE}
 *
 * @copyright Copyright (c) 2017 Fcamara - Saraiva Platform (http://www.fcamara.com.br)
 *
 * @author    Tiago Daniel <tiago.daniel@fcamara.com.br>
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