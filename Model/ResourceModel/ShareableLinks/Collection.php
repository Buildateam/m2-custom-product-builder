<?php

namespace Buildateam\CustomProductBuilder\Model\ResourceModel\ShareableLinks;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Buildateam\CustomProductBuilder\Model\ShareableLinks', 'Buildateam\CustomProductBuilder\Model\ResourceModel\ShareableLinks');
    }
}