<?php

namespace Buildateam\CustomProductBuilder\Model\ResourceModel;

class ShareableLinks extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('cpb_product_config', 'entity_id');
    }

    /**
     * Load country by ISO code
     *
     * @param ShareableLinks $links
     * @param string $code
     * @return ShareableLinks
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByConfigId(\Buildateam\CustomProductBuilder\Model\ShareableLinks $links, $code)
    {
        $field = 'config_id';

        return $this->load($links, $code, $field);
    }
}