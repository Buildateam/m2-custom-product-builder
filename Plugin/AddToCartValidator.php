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