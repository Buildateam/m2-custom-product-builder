<?php

namespace Buildateam\CustomProductBuilder\Plugin;

use Buildateam\CustomProductBuilder\Model\CacheManager;
use Buildateam\CustomProductBuilder\Model\Registry;
use Magento\Catalog\Controller\Product\View;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class ProductPolicy
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * ProductEditPolicy constructor.
     * @param ProductRepository $productRepository
     * @param UrlInterface $url
     * @param Registry $registry
     * @param RequestInterface $request
     * @param CacheManager $cache
     */
    public function __construct(
        ProductRepository $productRepository,
        UrlInterface $url,
        Registry $registry,
        RequestInterface $request,
        CacheManager $cache
    ) {
        $this->productRepository = $productRepository;
        $this->url = $url;
        $this->registry = $registry;
        $this->request = $request;
        $this->cache = $cache;
    }

    public function beforeExecute()
    {
        $id = $this->request->getParam('id');
        if ($id) {
            $urls = $this->cache->load();
            if (!$urls) {
                $urls = [];
                $product = $this->productRepository->getById($id);
                $jsonConfiguration = $product->getJsonConfiguration() ?: '';
                foreach ($this->getUrls($jsonConfiguration) as $url) {
                    $urls[] = str_replace('\/', '/', $url);
                }
                $this->cache->save($urls);
            }
            $this->registry->register($urls);
        }
    }

    /**
     * Make preg match all with offsets and format array  to one dimensional
     *
     * @param string $haystack
     * @return array|null
     *
     * //phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
     */
    private function getUrls(string $haystack): ?array
    {
        $urlInfo = parse_url($this->url->getBaseUrl());
        $currentDomain = preg_quote($urlInfo['host']);
        $result = preg_match_all(
            '@http[s]?:[\\\/]+(?!' . $currentDomain . ')[\w\-\\\/.]+\.(jp[e]?g|png|eot|ttf|woff[2]?)@',
            $haystack,
            $matches
        );
        if ($result) {
            return $matches['0'];
        }

        return [];
    }
}
