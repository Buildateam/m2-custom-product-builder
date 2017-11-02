<?php

namespace Buildateam\CustomProductBuilder\Model\Plugin;

class QuoteItemCompareOptions
{
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
            $code = $option->getCode();
            $value = unserialize($option->getValue());
            if (($code == 'info_buyRequest') && !isset($value['technicalData'])) {
                continue;
            }

            if (!isset($options2[$code]) || $options2[$code]->getValue() != $option->getValue()) {
                return false;
            }
        }
        return $proceed($options1, $options2);
    }
}
