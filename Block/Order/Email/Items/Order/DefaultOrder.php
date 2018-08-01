<?php
namespace Buildateam\CustomProductBuilder\Block\Order\Email\Items\Order;

class DefaultOrder extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{
    /**
     * @return array
     */
    public function getItemOptionsForEmail()
    {
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }
}