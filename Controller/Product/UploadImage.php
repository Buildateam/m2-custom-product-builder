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

namespace Buildateam\CustomProductBuilder\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;

class UploadImage extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Data|\Buildateam\CustomProductBuilder\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ResultFactory $resultFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Buildateam\CustomProductBuilder\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ResultFactory $resultFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Buildateam\CustomProductBuilder\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $imagePath = $this->helper->uploadImage($this->request->getContent());
        $imageBaseUrl = $this->storeManager->getStore()->getBaseUrl('media') . $imagePath;
        $body = json_encode($imageBaseUrl);
        $result = $this->resultFactory->create('raw');
        $result->setHeader("Content-Type", 'application/json');
        $result->setContents($body);

        return $result;
    }
}
