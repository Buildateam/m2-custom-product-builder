<?php
namespace Buildateam\CustomProductBuilder\Model\Product;

class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_CODE = 'custom';

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {

    }

    /**
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        $additionalOptions = [];
        $properties = $buyRequest->getProperties();
        if ($properties && is_array($properties)) {
            foreach($properties as $key => $value) {
                if ($key == 'productUrl' || $key == 'SKU' || empty($value)) {
                    continue;
                }
                $additionalOptions[] = ['label' => $key, 'value' => $value];
            }
        }
        $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        return $result;
    }
}
