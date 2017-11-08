<?php

namespace Buildateam\CustomProductBuilder\Plugin;

use Exception;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\Math\Random;
use \Magento\Framework\Logger\Monolog;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;
use \Buildateam\CustomProductBuilder\Helper\Data;

class AddToCartValidator
{
    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var Random
     */
    protected $_mathRandom;

    /**
     * @var ShareableLinksFactory
     */
    protected $_shareLinksFactory;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Monolog
     */
    protected $_logger;

    /**
     * @var JsonHelper
     */
    protected $_jsonHelper;


    public function __construct(
        Session $checkoutSession,
        ShareableLinksFactory $factory,
        Data $helper,
        Random $random,
        Monolog $logger,
        JsonHelper $jsonHelper
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_mathRandom = $random;
        $this->_shareLinksFactory = $factory;
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param $request
     * @return string
     */
    public function aroundValidate($subject, callable $proceed, $request)
    {
        if ($request->getHeader('X_CUSTOM_PRODUCT_BUILDER')) {
            $payload = json_decode(file_get_contents('php://input'), 1);
            foreach (['qty', 'technicalData', 'properties', 'configid', 'type'] as $paramKey) {
                if (isset($payload[$paramKey])) {
                    $request->setParam($paramKey, $payload[$paramKey]);
                }
            }

            if (isset($payload['buffer'])) {
                if (!isset($payload['configid'])) {
                    $request->setParam('configid', $this->_mathRandom->getRandomString(20));
                }
                if (!isset($payload['type'])) {
                    $request->setParam('type', 'png');
                }

                $imagePath = $this->_helper->uploadImage($payload['buffer']);
                $configModel = $this->_shareLinksFactory->create();
                $configModel->setData(array(
                    'product_id' => $request->getParam('product'),
                    'technical_data' => $this->_jsonHelper->jsonEncode($request->getParam('technicalData')),
                    'config_id' => $request->getParam('configid'),
                    'image' => $imagePath
                ));

                try {
                    $configModel->save();
                } catch (Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }

            $this->_checkoutSession->setNoCartRedirect(true);
            return true;
        }

        return $proceed($request);
    }

}