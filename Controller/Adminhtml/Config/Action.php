<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Framework\FlagManager;

abstract class Action extends BackendAction
{

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var FlagManager
     */
    public $flagManager;

    /**
     * Save constructor.
     * @param Context $context
     * @param FlagManager $flagManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FlagManager $flagManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->flagManager = $flagManager;
    }
}
