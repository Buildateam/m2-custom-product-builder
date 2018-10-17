<?php
namespace Buildateam\CustomProductBuilder\Block\Order\Email\Items\Order;

use Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder as MagentoDefaultOrder;

class DefaultOrder extends MagentoDefaultOrder
{
        /**
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }


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