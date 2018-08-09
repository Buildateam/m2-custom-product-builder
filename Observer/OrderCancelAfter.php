<?php
namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Exception\CouldNotRefundException;

class OrderCancelAfter implements ObserverInterface
{
    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * OrderCancelAfter constructor.
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->_serializer = $json;
    }

    /**
     * @param Observer $observer
     * @throws CouldNotRefundException
     */
    public function execute(Observer $observer)
    {
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
                        break;
                    }
                }
                $product->setJsonConfiguration($this->_serializer->serialize($jsonConfig));
                $product->save();
            }
        }
    }
}