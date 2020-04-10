<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use Buildateam\CustomProductBuilder\Model\JsonFlagManager;
use Magento\Framework\Serialize\Serializer\Json;

class Get extends Action
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
     * @var
     */
    public $json;

    /**
     * Save constructor.
     * @param Context $context
     * @param JsonFlagManager $flagManager
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFlagManager $flagManager,
        Json $json,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->flagManager = $flagManager;
        $this->json = $json;
    }

    /**
     * @return Raw
     */
    public function execute(): Raw
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setHeader('Content-type', 'application/json');

        try {
            $data = $this->flagManager->getFlagData('buildateam_customproductbuilder_config');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            return $result->setContents($this->json->serialize($result));
        }

        return $result->setContents($data);
    }
}
