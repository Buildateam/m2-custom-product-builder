<?php

namespace Buildateam\CustomProductBuilder\Model\Plugin;

use \Magento\Quote\Model\Quote\Item;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class QuoteItem
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_shareLinksFactory;

    public function __construct(
        ShareableLinksFactory $factory
    )
    {
        $this->_shareLinksFactory = $factory;
    }

    /**
     * Removed check if $code is in $this->notRepresentOptions
     * so if $byRequest options are different, we will have a new quote item
     *
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param callable $proceed
     * @param $options1
     * @param $options2
     * @return bool|callable
     */
    public function aroundCompareOptions(\Magento\Quote\Model\Quote\Item $subject, callable $proceed, $options1, $options2)
    {
        foreach ($options1 as $option) {
            if ($option->getCode() == 'info_buyRequest') {
                $code = $option->getCode();
                $value = unserialize($option->getValue());
                if (!isset($value['technicalData'])) {
                    continue;
                }

                if (!isset($options2[$code]) || unserialize($options2[$code]->getValue())['technicalData'] != $value['technicalData']) {
                    return false;
                }
            }
        }
        return $proceed($options1, $options2);
    }

    /**
     * Change product image for configurable product
     *
     * @param Item $subject
     * @param $result
     * @return mixed
     */
    public function afterSetProduct(Item $subject, $result)
    {
        $productInfo = unserialize($subject->getProduct()->getCustomOption('info_buyRequest')->getValue());
        if (isset($productInfo['configid'])) {
            $configModel = $this->_shareLinksFactory->create()->loadByConfigId($productInfo['configid']);
            if ($configModel->getId()) {
                $result->getProduct()
                    ->setImage($configModel->getImage())
                    ->setSmallImage($configModel->getImage())
                    ->setThumbnail($configModel->getImage());
            }
        }
        return $result;
    }
}
