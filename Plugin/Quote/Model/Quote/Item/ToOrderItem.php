<?php
namespace Buildateam\CustomProductBuilder\Plugin\Quote\Model\Quote\Item;

class ToOrderItem
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(\Magento\Framework\Serialize\SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $data
     * @return \Magento\Sales\Api\Data\OrderItemInterface
     */
    public function afterConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Magento\Sales\Api\Data\OrderItemInterface $orderItem,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $data = []
    ) {
        if ($item->getProduct()->getTypeId() == \Buildateam\CustomProductBuilder\Model\Product\Type::TYPE_CODE) {
            $additionalOptions = $item->getOptionByCode('additional_options');
            if ($additionalOptions && $additionalOptions->getValue()) {
                $options = $orderItem->getProductOptions();
                $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                $orderItem->setProductOptions($options);
            }
        }

        return $orderItem;
    }
}
