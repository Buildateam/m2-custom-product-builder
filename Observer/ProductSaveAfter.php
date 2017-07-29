<?php
namespace Buildateam\CustomProductBuilder\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Vendor\Product\Ui\DataProvider\Product\Form\Modifier\CustomFieldset;

class ProductSaveAfter implements ObserverInterface
{
    protected $_request;
    protected $_jsonData;
    const JSON_ATTRIBUTE = 'json_configuration';

    /**
     * @param EventObserver $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getEvent()->getProduct();

        if (!$product) {
            return;
        }

        $this->_jsonData = $product->getData(self::JSON_ATTRIBUTE);

        if(!$this->_jsonData && !empty($this->_jsonData)) {
            $product->setJsonConfiguration($this->_jsonData);
        }

        return;
    }

}