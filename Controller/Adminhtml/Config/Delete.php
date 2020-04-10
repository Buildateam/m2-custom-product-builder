<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use Buildateam\CustomProductBuilder\Model\JsonFlagManager;

class Delete extends Action
{

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var JsonFlagManager
     */
    public $flagManager;

    /**
     * Save constructor.
     * @param Context $context
     * @param JsonFlagManager $flagManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFlagManager $flagManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->flagManager = $flagManager;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $this->flagManager->deleteFlag('buildateam_customproductbuilder_config');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            return $jsonResult->setData($result);
        }

        $resultDelete = ['error' => false, 'message' => 'You have successfully deleted the config!'];
        return $jsonResult->setData($resultDelete);
    }
}
