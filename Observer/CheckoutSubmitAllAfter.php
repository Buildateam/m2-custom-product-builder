<?php
namespace Buildateam\CustomProductBuilder\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Serialize\Serializer\Json;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * CheckoutSubmitAllAfter constructor.
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->_serializer = $json;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $items = $observer->getOrder()->getItems();
        foreach ($items as $item) {
           $product = $item->getProduct();
           if ($product->getJsonConfiguration()) {
               $jsonConfig = $this->_serializer->unserialize($product->getJsonConfiguration());
               $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');
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
                   foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                       if ($value['sku'] == $sku) {
                           $jsonConfig['data']['inventory'][$key]['qty'] -= $infoBuyRequest['qty'];
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