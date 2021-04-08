<?php
declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Plugin\Catalog\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Product
{
    /**
     * @var \Buildateam\CustomProductBuilder\Helper\Data
     */
    private $helper;

    /**
     * Product constructor.
     * @param \Buildateam\CustomProductBuilder\Helper\Data $helper
     */
    public function __construct(\Buildateam\CustomProductBuilder\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param $result
     * @param AbstractModel $object
     * @return mixed
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        $result,
        AbstractModel $object
    ) {
        $jsonConfiguration = $object->getData('json_configuration');
        if (!empty($jsonConfiguration)) {
            $jsonData = json_decode($jsonConfiguration);
            $jsonData->data->name = $object->getName();
            $jsonData->data->base->price = $object->getPrice();
            $jsonConfiguration = json_encode($jsonData);
            $this->helper->saveJsonConfiguration($object->getEntityId(), $jsonConfiguration);
        }
        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param $result
     * @param AbstractModel $object
     * @param $entityId
     * @param array $attributes
     * @return mixed
     */
    public function afterLoad(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        $result,
        AbstractModel $object,
        $entityId,
        $attributes = []
    ) {
        if ($object->getId()) {
            $jsonConfiguration = $this->helper->loadJsonConfiguration($object->getId());
            $object->setData('json_configuration', $jsonConfiguration);
        }
        return $result;
    }
}
