<?php
declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;

class JsonFlagManager
{
    /**
     * The factory of flags.
     *
     * @var FlagFactory
     * @see Flag
     */
    private $flagFactory;

    /**
     * The flag resource.
     *
     * @var FlagResource
     */
    private $flagResource;

    /**
     *
     * @param FlagFactory $flagFactory The factory of flags
     * @param FlagResource $flagResource The flag resource
     */
    public function __construct(
        FlagFactory $flagFactory,
        FlagResource $flagResource
    ) {
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
    }

    /**
     * Retrieves raw data from the flag.
     *
     * @param string $code The code of flag
     * @return string|int|float|bool|array|null
     */
    public function getFlagData($code)
    {
        return $this->getFlagObject($code)->getData('flag_data');
    }

    /**
     * Saves the flag value by code.
     *
     * @param string $code The code of flag
     * @param string|int|float|bool|array|null $value The value of flag
     * @return bool
     * @throws AlreadyExistsException
     */
    public function saveFlag($code, $value)
    {
        $flag = $this->getFlagObject($code);
        $flag->setData('flag_data', $value);
        $this->flagResource->save($flag);

        return true;
    }

    /**
     * Deletes the flag by code.
     *
     * @param string $code The code of flag
     * @return bool
     * @throws \Exception
     */
    public function deleteFlag($code)
    {
        $flag = $this->getFlagObject($code);

        if ($flag->getId()) {
            $this->flagResource->delete($flag);
        }

        return true;
    }

    /**
     * Returns flag object
     *
     * @param string $code
     * @return Flag
     */
    private function getFlagObject($code)
    {
        /** @var Flag $flag */
        $flag = $this->flagFactory->create(['data' => ['flag_code' => $code]]);
        $this->flagResource->load(
            $flag,
            $code,
            'flag_code'
        );

        return $flag;
    }
}
