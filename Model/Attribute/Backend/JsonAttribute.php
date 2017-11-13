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

namespace Buildateam\CustomProductBuilder\Model\Attribute\Backend;


class JsonAttribute extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @bool int $_isValidJson
     */
    protected $_isValidJson = false;

    /**
     * @var string $_jsonProductContent
     */
    protected $_jsonProductContent;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function afterLoad($object)
    {
        // your after load logic

        return parent::afterLoad($object);
    }

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $_objectManager
    )
    {
        $this->_objectManager = $_objectManager;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     *
     * @return $this
     */
    public function beforeSave($object)
    {
        $this->validateJson($object);

        return parent::beforeSave($object);
    }

    /**
     * Validate length
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateJson($object)
    {

        /** @var  $attributeCode */
        $attributeCode  = $this->getAttribute()->getAttributeCode();
        /** @var  $jsonData */
        $jsonData       = (string)$object->getData($attributeCode);
        if (!empty($jsonData)) {
            $this->_jsonProductContent = $jsonData;

            $validate = $this->_getHelper()->validate($this->_jsonProductContent);
            if (isset($this->_jsonProductContent) && !empty($this->_jsonProductContent) && $validate) {
                throw new \Magento\Framework\Exception\LocalizedException(__($validate));
            }
        }

        return true;
    }

    /**
     * return helper object
     * @return mixed
     */
    protected function _getHelper()
    {
        return $this->_objectManager->create('Buildateam\CustomProductBuilder\Helper\Data');
    }

}

