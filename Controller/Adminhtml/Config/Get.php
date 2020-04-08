<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Flag;
use Buildateam\CustomProductBuilder\Model\ConfigModel;

class Get extends Action
{

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var ConfigModel
     */
    public $configModel;

    /**
     * Save constructor.
     * @param Context $context
     * @param ConfigModel $configModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ConfigModel $configModel,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->configModel = $configModel;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            /** @var Flag $flagModel */
            $flagModel = $this->configModel->getConfigModel();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            return $jsonResult->setData($result);
        }

        $result = ['config' => $flagModel->getData('flag_data')];
        return $jsonResult->setData($result);
    }
}
