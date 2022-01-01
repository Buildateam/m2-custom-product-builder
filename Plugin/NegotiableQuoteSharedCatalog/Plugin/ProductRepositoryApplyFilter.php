<?php
namespace Buildateam\CustomProductBuilder\Plugin\NegotiableQuoteSharedCatalog\Plugin;

class ProductRepositoryApplyFilter
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(\Magento\Framework\App\Request\Http $request)
    {
        $this->request = $request;
    }

    /**
     * @param \Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api\ProductRepositoryApplyFilter $subjectApplyFilter
     * @param callable $proceed
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $subject
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function aroundAfterGetById(
        \Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api\ProductRepositoryApplyFilter $subjectApplyFilter,
        callable $proceed,
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {

        if ($this->request->getRouteName() == 'customproductbuilder') {
            return $product;
        }

        return $proceed($subject, $product);
    }

    public function aroundAfterGet(
        \Magento\NegotiableQuoteSharedCatalog\Plugin\Catalog\Api\ProductRepositoryApplyFilter $subjectApplyFilter,
        callable $proceed,
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        if ($this->request->getRouteName() == 'customproductbuilder') {
            return $product;
        }
        return $proceed($subject, $product);
    }
}
