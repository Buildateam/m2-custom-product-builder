<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Framework\FlagManager;

abstract class Action extends BackendAction
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
     * Save constructor.
     * @param Context $context
     * @param FlagManager $flagManager
     * @param LoggerInterface $logger
     * @param Json $jsonSerializer
     */
    public function __construct(
        Context $context,
        FlagManager $flagManager,
        LoggerInterface $logger,
        Json $jsonSerializer
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->flagManager = $flagManager;
        $this->jsonSerializer = $jsonSerializer;
    }
}
