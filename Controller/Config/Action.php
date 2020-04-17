<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Config;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\FlagManager;

abstract class Action extends \Magento\Framework\App\Action\Action
{
    const FLAG_CODE = 'buildateam_customproductbuilder_config';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var FlagManager
     */
    protected $flagManager;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * Action constructor.
     * @param FlagManager $flagManager
     * @param LoggerInterface $logger
     * @param Json $jsonSerializer
     * @param Context $context
     */
    public function __construct(
        FlagManager $flagManager,
        LoggerInterface $logger,
        Json $jsonSerializer,
        Context $context
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->flagManager = $flagManager;
        $this->jsonSerializer = $jsonSerializer;
    }
}
