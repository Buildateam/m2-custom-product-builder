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

namespace Buildateam\CustomProductBuilder\Plugin;

use Exception;
use \Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\Math\Random;
use \Magento\Framework\Logger\Monolog;
use \Magento\Framework\Serialize\Serializer\Json;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;
use \Buildateam\CustomProductBuilder\Helper\Data;
use \Magento\Store\Model\StoreManagerInterface;

class AddToCartValidator
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var ShareableLinksFactory
     */
    private $shareLinksFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Monolog
     */
    private $logger;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Session $checkoutSession
     * @param ShareableLinksFactory $factory
     * @param Data $helper
     * @param Random $random
     * @param Monolog $logger
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $checkoutSession,
        ShareableLinksFactory $factory,
        Data $helper,
        Random $random,
        Monolog $logger,
        Json $json,
        StoreManagerInterface $storeManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->mathRandom = $random;
        $this->shareLinksFactory = $factory;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->json = $json;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Data\Form\FormKey\Validator $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundValidate(
        \Magento\Framework\Data\Form\FormKey\Validator $subject,
        callable $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($request->getHeader('X_CUSTOM_PRODUCT_BUILDER')) {
            $payload = $this->json->unserialize($request->getContent());

            foreach (['quantity', 'technicalData', 'properties', 'configid', 'type'] as $paramKey) {
                if (isset($payload[$paramKey])) {
                    if ($paramKey == 'properties') {
                        if (isset($payload[$paramKey]['_image'])) {
                            unset($payload[$paramKey]['_image']);
                        }
                        if (isset($payload[$paramKey]['configId'])) {
                            unset($payload[$paramKey]['configId']);
                        }
                    }
                    if ($paramKey == 'quantity') {
                        $request->setParam('qty', $payload[$paramKey]);
                    } else {
                        $request->setParam($paramKey, $payload[$paramKey]);
                    }
                }
            }

            if (isset($payload['buffer'])) {
                if (!isset($payload['configid'])) {
                    try {
                        $request->setParam('configid', $this->mathRandom->getRandomString(18));
                    } catch (LocalizedException $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }
                if (!isset($payload['type'])) {
                    $request->setParam('type', 'png');
                }

                $imagePath = $this->helper->uploadImage($payload['buffer'], true);
                $configModel = $this->shareLinksFactory->create();
                $configModel->setData([
                    'product_id' => $request->getParam('product'),
                    'technical_data' => $this->json->serialize($request->getParam('technicalData')),
                    'variation_id' => $request->getParam('configid'),
                    'image' => $imagePath
                ]);

                try {
                    $configModel->save();
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
            $refererUrl = $request->getServer('HTTP_REFERER');
            $search = $this->storeManager->getStore()->getBaseUrl() . 'checkout/cart/configure/id/';
            if (strpos($refererUrl, $search) !== false) {
                $parts = explode('/', str_replace($search, '', $refererUrl));
                $quoteItemId = $parts[0];
                $quote = $this->checkoutSession->getQuote();
                foreach ($quote->getItems() as $item) {
                    if ($item->getId() == $quoteItemId) {
                        $quote->deleteItem($item);
                        $request->setParam('return_url', $this->storeManager->getStore()->getUrl('checkout/cart'));
                    }
                }
            } else {
                $this->checkoutSession->setNoCartRedirect(true);
            }
            return true;
        }

        return $proceed($request);
    }
}
