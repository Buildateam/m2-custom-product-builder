<?php
namespace Buildateam\CustomProductBuilder\Helper;

use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Framework\Serialize\SerializerInterface;

class Json
{
    /**
     * @var bool
     */
    private $_isJsonInfoByRequest = true;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        SerializerInterface $serializer
    ) {
        if (version_compare($productMetadata->getVersion(), '2.2.0', '<')) {
            $this->_isJsonInfoByRequest = false;
        }
        $this->serializer = $serializer;
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
            $result = $this->serializer->serialize($array);
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
            $result = $this->serializer->unserialize($json);
        }
        return $result;
    }
}
