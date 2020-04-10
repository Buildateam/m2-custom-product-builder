<?php
declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Model;

use Magento\Framework\Serialize\Serializer\Json;

class JsonSerializer extends Json
{
    /**
     * @param array|bool|float|int|string|null $data
     * @return array|bool|false|float|int|string|null
     */
    public function serialize($data)
    {
        if (!isJson($data)) {
            return parent::serialize($data);
        }

        return $data;
    }

    /**
     * @param string $string
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserialize($string)
    {
        if (isJson($string)) {
            return parent::unserialize($string);
        }

        return $string;
    }
}
