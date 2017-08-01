<?php

namespace Buildateam\CustomProductBuilder\Plugin;


class ProductPageHideBlock
{
    /**
     * @param \Magento\ProductVideo\Block\Product\View\Gallery|Magento\Catalog\Block\Product\View\Gallery $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundToHtml($subject, callable $proceed)
    {
        $attribute = $subject->getProduct()->getData('json_config');
        $result = "";
        /* if (empty($attribute)) {
              $result= $proceed();
        }
          */
        return $result;
    }

}