<?php


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

