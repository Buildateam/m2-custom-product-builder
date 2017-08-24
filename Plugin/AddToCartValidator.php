<?php

namespace Buildateam\CustomProductBuilder\Plugin;


class AddToCartValidator
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\ProductVideo\Block\Product\View\Gallery|Magento\Catalog\Block\Product\View\Gallery $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundValidate($subject, callable $proceed, $request)
    {
        if ($request->getHeader('X_CUSTOM_PRODUCT_BUILDER')) {
            
            $payload = json_decode(file_get_contents('php://input'),1);
            $request->setParam('qty', $payload['qty']);

            $this->_checkoutSession->setNoCartRedirect(true);
            return true;
        }
        return $proceed($request);
    }

}