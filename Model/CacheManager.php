<?php

namespace Buildateam\CustomProductBuilder\Model;

use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Interception cache manager.
 *
 */
class CacheManager
{
    /**
     * @var Block
     */
    private $cache;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var Identifier
     */
    private $identifier;

    /**
     * @param SerializerInterface $serializer
     * @param Identifier $identifier
     * @param Collection $cache
     */
    public function __construct(
        SerializerInterface $serializer,
        Identifier $identifier,
        Block $cache
    ) {
        $this->serializer = $serializer;
        $this->identifier = $identifier;
        $this->cache = $cache;
    }

    /**
     * Load the interception config from cache
     * @return array|null
     */
    public function load()
    {
        if (!$key = $this->identifier->getValue()) {
            return null;
        }
        $intercepted = $this->cache->load($key);
        return $intercepted ? $this->serializer->unserialize($intercepted) : null;
    }

    /**
     * Save config to filesystem
     * @param array $data
     */
    public function save(array $data)
    {
        if (!$key = $this->identifier->getValue()) {
            return;
        }
        $this->cache->save($this->serializer->serialize($data), $key);
    }
}
