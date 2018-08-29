<?php
namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Exception\CouldNotRefundException;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Magento\Store\Model\StoreManagerInterface;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * @var Action
     */
    protected $_productAction;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * OrderCancelAfter constructor.
     * @param Json $json
     * @param Action $action
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(Json $json, Action $action, StoreManagerInterface $storeManager)
    {
        $this->_serializer = $json;
        $this->_productAction = $action;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @throws CouldNotRefundException
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $order = $observer->getData('order');
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            if ($product->getJsonConfiguration()) {
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
                } else {
                    throw new CouldNotRefundException('Could not retrieve product sku');
                }
                $qtyCanceled = $item->getQtyCanceled();
                $jsonConfig = $this->_serializer->unserialize($product->getJsonConfiguration());
                foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                    if ($sku == $value['sku']) {
                        $jsonConfig['data']['inventory'][$key]['qty'] += $qtyCanceled;
                        $this->_productAction->updateAttributes([$product->getId()], ['json_configuration' => $this->_serializer->serialize($jsonConfig)], $storeId);
                        break;
                    }
                }
            }
        }
    }
}