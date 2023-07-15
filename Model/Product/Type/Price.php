<?php
namespace Buildateam\CustomProductBuilder\Model\Product\Type;

use Magento\Framework\Message\ManagerInterface;
use Buildateam\CustomProductBuilder\Helper\Data as Helper;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var array
     */
    private $jsonConfig = [];

    /**
     * @var bool
     */
    private $isJsonInfoByRequest = true;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     * @param ProductRepository $productRepository
     * @param ProductMetadataInterface $productMetadata
     * @param ManagerInterface $massageManager
     * @param SerializerInterface $serializer
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null,
        ProductRepository $productRepository,
        ProductMetadataInterface $productMetadata,
        ManagerInterface $massageManager,
        SerializerInterface $serializer
    ) {
        $this->productRepository = $productRepository;
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->isJsonInfoByRequest = false;
        }
        $this->messageManager = $massageManager;
        $this->serializer = $serializer;

        return parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $tierPriceExtensionFactory
        );
    }

    /**
     * Returns product final price depending on options chosen
     *
     * @param float $qty
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     * @throws \Magento\Checkout\Exception
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = parent::getFinalPrice($qty, $product);

        if (null === $product->getCustomOption('info_buyRequest')) {
            return max(0, $finalPrice);
        }

        /* Retrieve technical data of product that was added to cart */
        $buyRequest = $product->getCustomOption('info_buyRequest')->getData('value');
        $productInfo = $this->serializer->unserialize($buyRequest);

        if (!isset($productInfo['technicalData'])) {
            return max(0, $finalPrice);;
        }
        $technicalData = $productInfo['technicalData']['layers'];

        if (!isset($this->jsonConfig[$product->getId()])) {
            $productRepo = $this->productRepository->getById($product->getId());
            $this->jsonConfig[$product->getId()] = json_decode($productRepo->getData(Helper::JSON_ATTRIBUTE), true);
        }
        $jsonConfig = $this->jsonConfig[$product->getId()];
        if (null === $jsonConfig) {
            return max(0, $finalPrice);;
        }

        if (isset($productInfo['properties']['Item Customization - Colors'])) {
            $property = $productInfo['properties']['Item Customization - Colors'];
        } elseif (isset($productInfo['properties']['Colors'])) {
            $property = $productInfo['properties']['Colors'];
        } else {
            $property = '';
        }
        if ($property != '') {
            $parts = explode(' ', $property);
            $skusList = trim(end($parts), '[]');
            $skus = explode(',', $skusList);
            $sku = $skus[0];
        }

        if (isset($productInfo['properties']['Item Customization - Order'])) {
            $printMethod = $productInfo['properties']['Item Customization - Order'];
        } elseif (isset($productInfo['properties']['Order'])) {
            $printMethod = $productInfo['properties']['Order'];
        } else {
            $printMethod = 'blank';
        }

        $printMethod = trim(strtolower($printMethod));

        if ($printMethod == 'blank') {
            $type = 'Blank';
        } elseif ($printMethod == 'with logo') {
            $type = 'Decorated';
        } elseif ($printMethod == 'sample') {
            $type = 'Sample';
        }

        $availablePrices = [];
        if (isset($sku) && $sku != "") {
            if (isset($jsonConfig['data']) && isset($jsonConfig['data']['prices'])) {
                foreach ($jsonConfig['data']['prices'] as $price) {
                    if ($price['sku'] == $sku && $price['type'] == $type) {
                        $availablePrices[] = $price;
                    }
                }
                usort($availablePrices, function ($a, $b) {
                    return $a['minQty'] - $b['minQty'];
                });

                $maxQty = 0;
                foreach ($jsonConfig['data']['inventory'] as $inventory) {
                    if ($sku == $inventory['sku']) {
                        $maxQty = $inventory['qty'];
                        break;
                    }
                }

                if ($printMethod == 'sample' && $qty > 1) {
                    throw new \Magento\Checkout\Exception(__('Requested quantity is not available'));
                }

                if ($qty <= $maxQty) {
                    foreach ($availablePrices as $key => $value) {
                        if ($qty == $value['minQty']) {
                            $finalPrice = $value['price'];
                            break;
                        }
                        if ($qty >= $value['minQty']) {
                            continue;
                        }

                        if (isset($availablePrices[$key - 1])) {
                            $finalPrice = $availablePrices[$key - 1]['price'];
                            break;
                        } else {
                            throw new \Magento\Checkout\Exception(__('Requested quantity is not available'));
                        }
                    }
                    if (!isset($finalPrice) && $qty > end($availablePrices)['minQty']) {
                        $finalPrice = end($availablePrices)['price'];
                    }
                } else {
                    throw new \Magento\Checkout\Exception(__('Requested quantity is not available'));
                }
            }
        }

        $finalPrice = floatval($finalPrice);

        foreach ($jsonConfig['data']['panels'] as $panel) {
            foreach ($technicalData as $techData) {
                if ($panel['id'] == $techData['panel']) {
                    $finalPrice = $this->calculateFinalPrice($panel, $techData, $finalPrice);
                }
            }
        }
        if (isset($finalPrice)) {
            $product->setFinalPrice($finalPrice);
        }

        return max(0, $product->getData('final_price'));
    }

    /**
     * @param array $panel
     * @param array $techData
     * @param float $finalPrice
     * @return float
     */
    private function calculateFinalPrice(array $panel, array $techData, float $finalPrice)
    {
        foreach ($panel['categories'] as $category) {
            if ($category['id'] == $techData['category']) {
                foreach ($category['options'] as $option) {
                    if ($option['id'] == $techData['option']) {
                        $finalPrice += $option['price'];
                    }
                }
            }
        }
        return $finalPrice;
    }
}
