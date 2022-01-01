<?php
namespace Buildateam\CustomProductBuilder\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Switcher extends AbstractSource
{
    const CPB_ENBALED = 1;
    const CPB_DISABLED = 0;

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        return [
            ['label' => __('Enabled'), 'value' => self::CPB_ENBALED],
            ['label' => __('Disabled'), 'value' => self::CPB_DISABLED]
        ];
    }
}
