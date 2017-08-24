<?php

namespace Buildateam\CustomProductBuilder\Plugin;


class AddToCartValidator
{
    /**
     * @param \Magento\ProductVideo\Block\Product\View\Gallery|Magento\Catalog\Block\Product\View\Gallery $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundValidate($subject, callable $proceed, $request)
    {
        if ($request->getHeader('X_CUSTOM_PRODUCT_BUILDER')) {
            return true;
        }
        return $proceed($request);
    }

}