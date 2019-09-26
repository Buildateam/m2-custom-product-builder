<?php

namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Buildateam\CustomProductBuilder\Helper\Json;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Magento\Store\Model\StoreManagerInterface;
use Buildateam\CustomProductBuilder\Helper\Data;

/**
 * Class CreditmemoRefund
 * @package Buildateam\CustomProductBuilder\Observer
 */
class CreditmemoRefund implements ObserverInterface
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
     * CreditmemoRefund constructor.
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->getConfigValue('cataloginventory/item_options/manage_stock')) {
            $storeId = $this->storeManager->getStore()->getId();
            $creditmemo = $observer->getData('creditmemo');
            $memoItems = $creditmemo->getItems();
            foreach ($memoItems as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->getProduct() !== null && $orderItem->getProduct()->getJsonConfiguration()) {
                    $product = $orderItem->getProduct();
                    if ($infoBuyRequest = $orderItem->getProductOptionByCode('info_buyRequest')) {
                        if (isset($infoBuyRequest['properties']['Item Customization - Colors'])) {
                            $property = $infoBuyRequest['properties']['Item Customization - Colors'];
                        } elseif (isset($infoBuyRequest['properties']['Colors'])) {
                            $property = $infoBuyRequest['properties']['Colors'];
                        } else {
                            $property = '';
                        }

                        if ($property != '') {
                            $parts = explode(' ', $property);
                            $sku = trim(end($parts), '[]');
                            $jsonConfig = $this->serializer->unserialize($product->getJsonConfiguration());
                            if (isset($jsonConfig['data']['inventory'])) {
                                foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                                    if ($value['sku'] == $sku) {
                                        $jsonConfig['data']['inventory'][$key]['qty'] += $item->getQty();
                                        $this->productAction->updateAttributes(
                                            [$product->getId()],
                                            ['json_configuration' => $this->serializer->serialize($jsonConfig)],
                                            $storeId
                                        );
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
}
