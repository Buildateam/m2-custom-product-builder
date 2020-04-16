<?php
declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Plugin\Catalog\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Product
{

    public function aroundSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Closure $proceed,
        AbstractModel $object
    ) {
        $jsonConfiguration = $object->getData('json_configuration');
        $result = $proceed($object);
        if (!empty($jsonConfiguration)) {
            $productId = $object->getEntityId();
            $subject->getConnection()->insertOnDuplicate(
                'cpb_product_configuration',
                [
                    'product_id' => $productId,
                    'configuration' => $jsonConfiguration
                ]
            );
        }
        return $result;
    }

    public function aroundLoad(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Closure $proceed,
        AbstractModel $object,
        $entityId,
        $attributes = []
    ) {
        $result = $proceed($object, $entityId, $attributes);
        if ($object->getId()) {
            $select = clone $subject->getConnection()->select();
            $select->from(
                'cpb_product_configuration',
                'configuration'
            )->where("product_id = {$object->getId()}");
            $jsonConfiguration = $subject->getConnection()->fetchOne($select);
            $object->setData('json_configuration', $jsonConfiguration);
        }
        return $result;
    }
}
