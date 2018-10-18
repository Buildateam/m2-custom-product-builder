<?php

namespace Buildateam\CustomProductBuilder\Block\Order\Email\Items\Order;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder as MagentoDefaultOrder;

class DefaultOrder extends MagentoDefaultOrder
{
    /**
     * @var Registry
     */
    private $_registry;

    /**
     * DefaultOrder constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    )
    {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getItemOptions()
    {
        $result = [];
        if ($options = $this->getItem()->getProductOptions()) {
            $product = $this->getItem()->getProduct();

            if (!$product->getJsonConfiguration() || $this->_registry->registry('is_admin_notify')) {
                if (isset($options['options'])) {
                    $result = array_merge($result, $options['options']);
                }
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
}