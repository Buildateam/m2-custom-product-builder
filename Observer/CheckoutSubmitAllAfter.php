<?php
namespace Buildateam\CustomProductBuilder\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Buildateam\CustomProductBuilder\Helper\Json;
use \Magento\Catalog\Model\ResourceModel\Product\Action;
use \Magento\Store\Model\StoreManagerInterface;

class CheckoutSubmitAllAfter implements ObserverInterface
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
     * CheckoutSubmitAllAfter constructor.
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
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $storeId = $this->_storeManager->getStore()->getId();
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
                   if (isset($jsonConfig['data']['inventory'])) {
                       foreach ($jsonConfig['data']['inventory'] as $key => $value) {
                           if ($value['sku'] == $sku) {
                               $jsonConfig['data']['inventory'][$key]['qty'] -= $infoBuyRequest['qty'];
                               $this->_productAction->updateAttributes([$product->getId()], ['json_configuration' => $this->_serializer->serialize($jsonConfig)], $storeId);
                               break;
                           }
                       }
                   }
               }
           }
        }
    }
}