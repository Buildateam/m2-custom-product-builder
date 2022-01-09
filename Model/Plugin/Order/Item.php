<?php
namespace Buildateam\CustomProductBuilder\Model\Plugin\Order;

use Buildateam\CustomProductBuilder\Model\ShareableLinksFactory;

class Item
{
    /**
     * @var ShareableLinksFactory
     */
    private $shareLinksFactory;

    /**
     * Item constructor.
     * @param ShareableLinksFactory $factory
     */
    public function __construct(ShareableLinksFactory $factory)
    {
        $this->shareLinksFactory = $factory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $subject
     * @param $result
     * @return mixed
     */
    public function afterGetProduct(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        $buyRequest = $subject->getProductOptionByCode('info_buyRequest');
        if (isset($buyRequest['configid'])) {
            $configModel = $this->shareLinksFactory->create()->loadByVariationId($buyRequest['configid']);
            if ($configModel->getId()) {
                $result->setImage($configModel->getImage())
                    ->setSmallImage($configModel->getImage())
                    ->setThumbnail($configModel->getImage());
            }
        }
        return $result;
    }
}
