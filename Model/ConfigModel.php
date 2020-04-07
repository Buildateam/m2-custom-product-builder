<?php
declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Model;

use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;

class ConfigModel
{
    CONST FLAG_CODE = 'buildateam_customproductbuilder_config';

    /**
     * @var FlagResource
     */
    public $flagResource;

    /**
     * @var FlagFactory
     */
    public $flagFactory;

    /**
     * Save constructor.
     * @param FlagResource $flagResource
     * @param FlagFactory $flagFactory
     */
    public function __construct(
        FlagResource $flagResource,
        FlagFactory $flagFactory
    ) {
        $this->flagResource = $flagResource;
        $this->flagFactory = $flagFactory;
    }

    /**
     * @return Flag
     */
    public function getConfigModel(): Flag
    {
        $this->flagResource->load(
            $flag = $this->createConfigModel(),
            self::FLAG_CODE,
            'flag_code'
        );

        return $flag;
    }

    /**
     * @return Flag
     */
    public function createConfigModel(): Flag
    {
        return $this->flagFactory->create([
            'data' => [
                'flag_code' => self::FLAG_CODE
            ]
        ]);
    }
}
