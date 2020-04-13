<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
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
     * @var FlagResource
     */
    protected $flagResource;

    /**
     * @var FlagFactory
     */
    protected $flagFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param FlagManager $flagManager
     * @param FlagResource $flagResource
     * @param FlagFactory $flagFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        FlagManager $flagManager,
        FlagResource $flagResource,
        FlagFactory $flagFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->flagManager = $flagManager;
        $this->flagResource = $flagResource;
        $this->flagFactory = $flagFactory;
    }
}
