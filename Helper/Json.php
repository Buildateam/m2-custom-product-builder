<?php
namespace Buildateam\CustomProductBuilder\Helper;

use \Magento\Framework\App\ProductMetadataInterface;

class Json
{
    /**
     * @var bool
     */
    protected $_isJsonInfoByRequest = true;

    /**
     * Json constructor.
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
    }

    /**
     * @param array $array
     * @return string
     */
    public function serialize(array $array): string
    {
        if ($this->_isJsonInfoByRequest) {
            $result = json_encode($array);
        } else {
            $result = @serialize($array);
        }
        return $result;
    }

    /**
     * @param string $json
     * @return array
     */
    public function unserialize(string $json): array
    {
        if ($this->_isJsonInfoByRequest) {
            $result = json_decode($json, true);
        } else {
            $result = @unserialize($json);
        }
        return $result;
    }

}