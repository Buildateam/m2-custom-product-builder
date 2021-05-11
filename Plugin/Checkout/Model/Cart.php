<?php
namespace Buildateam\CustomProductBuilder\Plugin\Checkout\Model;

class Cart
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(\Magento\Framework\Locale\ResolverInterface $localeResolver)
    {
        $this->localeResolver = $localeResolver;
    }

    public function beforeAddProduct(\Magento\Checkout\Model\Cart $subject, $productInfo, $requestInfo = null)
    {
        if (isset($requestInfo['technicalData']['breakdownData'])
            && count($requestInfo['technicalData']['breakdownData'])
        ) {
            $breakdownData = $requestInfo['technicalData']['breakdownData'];
            foreach ($breakdownData as $data) {
                if ($data['qty'] != 0) {
                    $requestInfo['properties']['Size'] = $data['label'];
                    $filter = new \Zend_Filter_LocalizedToNormalized(['locale' => $this->localeResolver->getLocale()]);
                    $requestInfo['qty'] = $filter->filter($data['qty']);
                    $requestInfo['technicalData']['breakdownData'] = $data;
                }
            }
        }

        return [$productInfo, $requestInfo];
    }
}
