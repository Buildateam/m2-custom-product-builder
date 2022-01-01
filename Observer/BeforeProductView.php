<?php
namespace Buildateam\CustomProductBuilder\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\View\Page\Config;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\View\Context;
use \Magento\Framework\Registry;

class BeforeProductView implements ObserverInterface
{
    /**
     * @var Config
     */
    private $pageConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * BeforeProductView constructor.
     * @param Config $pageConfig
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(Config $pageConfig, Context $context, Registry $coreRegistry)
    {
        $this->pageConfig = $pageConfig;
        $this->logger = $context->getLogger();
        $this->coreRegistry = $coreRegistry;
    }

    public function execute(Observer $observer)
    {
        try {
            if (null !== $product = $this->coreRegistry->registry('product')) {
                if ($product->getData('json_configuration')) {
                    $this->pageConfig->setElementAttribute(
                        'html',
                        'printable',
                        "true"
                    );
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
