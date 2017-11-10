<?php

namespace Buildateam\CustomProductBuilder\Model\Plugin;

use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Quote\Model\Quote\Item;
use \Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class QuoteItem
{
    /**
     * @var ShareableLinksFactory
     */
    protected $_shareLinksFactory;

    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    public function __construct(
        ShareableLinksFactory $factory,
        ProductMetadataInterface $productMetadata
    )
    {
        $this->_shareLinksFactory = $factory;
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
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
                if ($this->_isJsonInfoByRequest) {
                    $value = json_decode($option->getValue());
                } else {
                    $value = @unserialize($option->getValue());
                }

                if (!isset($value['technicalData'])) {
                    continue;
                }

                if ($this->_isJsonInfoByRequest) {
                    $value2 = json_decode($options2[$code]->getValue())['technicalData'];
                } else {
                    $value2 = @unserialize($options2[$code]->getValue())['technicalData'];
                }

                if (!isset($options2[$code]) || $value2['technicalData'] != $value['technicalData']) {
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
        $buyRequest = $subject->getProduct()->getCustomOption('info_buyRequest')->getValue();
        $productInfo = @unserialize($buyRequest);
        if ($buyRequest !== 'b:0;' && $productInfo === false) {
            $productInfo = $this->_serializer->unserialize($buyRequest);
        }

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
