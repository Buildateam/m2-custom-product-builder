<?php

namespace Buildateam\CustomProductBuilder\Model\Collector;

use Buildateam\CustomProductBuilder\Model\CacheManager;
use Buildateam\CustomProductBuilder\Model\Registry;
use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Collector\Config\PolicyReaderPool;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class CpbCollector implements PolicyCollectorInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var State
     */
    private $state;
    /**
     * @var PolicyReaderPool
     */
    private $readersPool;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $config;
    /**
     * @var CacheManager
     */
    private $cache;

    /**
     * CpbCollector constructor.
     * @param Registry $registry
     * @param State $state
     * @param PolicyReaderPool $readersPool
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param CacheManager $cache
     */
    public function __construct(
        Registry $registry,
        State $state,
        PolicyReaderPool $readersPool,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        CacheManager $cache
    ) {
        $this->registry = $registry;
        $this->state = $state;
        $this->readersPool = $readersPool;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->cache = $cache;
    }

    public function collect(array $defaultPolicies = []): array
    {
        $collected = [];

        $configArea = null;
        $area = $this->state->getAreaCode();
        if ($area === Area::AREA_ADMINHTML) {
            $configArea = 'admin';
        } elseif ($area === Area::AREA_FRONTEND) {
            $configArea = 'storefront';
        }

        if ($configArea) {
            $policiesConfig = $this->config->getValue(
                'csp/policies/' . $configArea,
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()
            );
            if (is_array($policiesConfig) && $policiesConfig) {
                $urls = $this->registry->registry() ? $this->registry->registry() : $this->cache->load();
                if ($urls) {
                    foreach ($urls as $url) {
                        $hosts[] = parse_url($url)['host'];
                    }
                    $policiesConfig['connections']['hosts'] = isset($policiesConfig['connections']['hosts']) ?
                        array_merge($policiesConfig['connections']['hosts'], $hosts) : $hosts;
                }
                foreach ($policiesConfig as $policyConfig) {
                    if ($defaultPolicies) {
                        $policyConfig['hosts'] = isset($policyConfig['hosts']) ?
                            array_merge(
                                $policyConfig['hosts'],
                                $defaultPolicies[$policyConfig["policy_id"]]->getHostSources()
                            ) :
                            $defaultPolicies[$policyConfig["policy_id"]]->getHostSources();
                    }
                    $collected[] = $this->readersPool->getReader($policyConfig['policy_id'])
                        ->read($policyConfig['policy_id'], $policyConfig);
                }
            }
        }

        return $collected;
    }
}
