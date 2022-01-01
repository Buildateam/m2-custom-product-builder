<?php
namespace Buildateam\CustomProductBuilder\CustomerData\Plugin;

use Magento\Checkout\CustomerData\AbstractItem as BaseAbstractItem;
use \Magento\Framework\App\ProductMetadataInterface;
use Magento\Quote\Model\Quote\Item;
use \Magento\Framework\Serialize\SerializerInterface;

class AbstractItem
{
    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Quote\Model\Quote $quote
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        \Magento\Quote\Model\Quote $quote,
        SerializerInterface $serializer
    ) {
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
        $this->serializer = $serializer;
    }

    /**
     * @param BaseAbstractItem $subject
     * @param callable $proceed
     * @param Item $item
     * @return mixed
     */
    public function aroundGetItemData(BaseAbstractItem $subject, callable $proceed, Item $item)
    {
        $buyRequest = $item->getProduct()->getCustomOption('info_buyRequest')->getValue();

        if ($this->_isJsonInfoByRequest) {
            $productInfo = json_decode($buyRequest, true);
        } else {
            $productInfo = $this->serializer->unserialize($buyRequest);
        }

        $result = $proceed($item);
        if (isset($productInfo['configid'])) {
            $result['configure_url'] = $result['configure_url'] . '#configid=' . $productInfo['configid'];
        }

        return $result;
    }
}
