<?php

namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Buildateam\CustomProductBuilder\Helper\Json;
use Buildateam\CustomProductBuilder\Helper\Data;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Magento\Store\Model\StoreManagerInterface;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var Action
     */
    private $productAction;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * OrderCancelAfter constructor.
     * @param Json $json
     * @param Action $action
     * @param StoreManagerInterface $storeManager
     * @param Data $helper
     */
    public function __construct(
        Json $json,
        Action $action,
        StoreManagerInterface $storeManager,
        Data $helper
    ) {
        $this->serializer = $json;
        $this->productAction = $action;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->getConfigValue('cataloginventory/item_options/manage_stock')) {
            $storeId = $this->storeManager->getStore()->getId();
            $order = $observer->getData('order');
            foreach ($order->getItems() as $item) {
                if ($item->getProduct() !== null && $item->getProduct()->getJsonConfiguration()) {
                    $product = $item->getProduct();
                    $productInfo = $item->getProductOptionByCode('info_buyRequest');
                    if (isset($productInfo['properties']['Item Customization - Colors'])) {
                        $property = $productInfo['properties']['Item Customization - Colors'];
                    } elseif (isset($productInfo['properties']['Colors'])) {
                        $property = $productInfo['properties']['Colors'];
                    } else {
                        $property = '';
                    }
                    if ($property != '') {
                        $parts = explode(' ', $property);
                        $sku = trim(end($parts), '[]');
                        $qtyCanceled = $item->getQtyCanceled();
                        $jsonConfig = $this->serializer->unserialize($product->getJsonConfiguration());
                        if (isset($jsonConfig['data']) && isset($jsonConfig['data']['inventory'])) {
                            foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                                if ($sku == $value['sku']) {
                                    $jsonConfig['data']['inventory'][$key]['qty'] += $qtyCanceled;
                                    $this->productAction->updateAttributes([$product->getId()], ['json_configuration' => $this->serializer->serialize($jsonConfig)], $storeId);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
