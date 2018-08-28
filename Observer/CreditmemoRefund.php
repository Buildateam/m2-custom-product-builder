<?php
namespace Buildateam\CustomProductBuilder\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Serialize\Serializer\Json;

class CreditmemoRefund implements ObserverInterface
{
    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * CreditmemoRefund constructor.
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->_serializer = $json;
    }

    public function execute(Observer $observer)
    {
        $creditmemo = $observer->getData('creditmemo');
        $memoItems = $creditmemo->getItems();
        foreach ($memoItems as $item) {
            $orderItem = $item->getOrderItem();
            $product = $orderItem->getProduct();
            if ($product->getJsonConfiguration()) {
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
                    }
                    $jsonConfig = $this->_serializer->unserialize($product->getJsonConfiguration());
                    if (isset($jsonConfig['data']['inventory'])) {
                        foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                            if ($value['sku'] == $sku) {
                                $jsonConfig['data']['inventory'][$key]['qty'] += $item->getQty();
                                $product->setJsonConfiguration($this->_serializer->serialize($jsonConfig));
                                $product->save();
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
}