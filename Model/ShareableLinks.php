<?php

namespace Buildateam\CustomProductBuilder\Model;

class ShareableLinks extends \Magento\Framework\Model\AbstractModel implements ShareableLinksInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'buildateam_customproductbuilder_shareable_links';

    protected function _construct()
    {
        $this->_init('Buildateam\CustomProductBuilder\Model\ResourceModel\ShareableLinks');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Load country by config id
     *
     *
     *
     *
     *
     *
     * @param string $configId
     * @return $this
     */
    public function loadByConfigId($configId)
    {
        $this->_getResource()->loadByConfigId($this, $configId);
        return $this;
    }
}