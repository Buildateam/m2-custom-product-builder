<?php
namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Buildateam\CustomProductBuilder\Helper\Json;
use Buildateam\CustomProductBuilder\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Store\Model\StoreManagerInterface;

class CheckoutSubmitAllAfter implements ObserverInterface
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
     */
    public function execute(Observer $observer)
    {
        if ($this->helper->getConfigValue('cataloginventory/item_options/manage_stock')) {
            return;
        }

        $storeId = $this->storeManager->getStore()->getId();
        $items = $observer->getOrder()->getItems();

        foreach ($items as $item) {
            if ($item->getProduct() !== null && $item->getProduct()->getJsonConfiguration()) {
                $product = $item->getProduct();
                $jsonConfig = $this->serializer->unserialize($product->getJsonConfiguration());
                $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');
                if (isset($infoBuyRequest['properties']['Item Customization - Colors'])) {
                    $property = $infoBuyRequest['properties']['Item Customization - Colors'];
                } elseif (isset($infoBuyRequest['properties']['Colors'])) {
                    $property = $infoBuyRequest['properties']['Colors'];
                } else {
                    $property = '';
                }

                if ($property == '') {
                    continue;
                }

                $parts = explode(' ', $property);
                $sku = trim(end($parts), '[]');
                if (!isset($jsonConfig['data']['inventory'])) {
                    continue;
                }

                foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                    if ($value['sku'] == $sku) {
                        $jsonConfig['data']['inventory'][$key]['qty'] -= $infoBuyRequest['qty'];
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
