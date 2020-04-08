<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Buildateam\CustomProductBuilder\Model\ConfigModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Psr\Log\LoggerInterface;

class Set extends Action
{
    /**
     * @var ConfigModel
     */
    public $configModel;

    /**
     * @var LoggerInterface
     */
    public $logger;

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
        $this->configModel = $configModel;
        $this->logger = $logger;
    }

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $config = $this->getRequest()->getParam('config');
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $flagModel = $this->configModel->getConfigModel()->setData('flag_data', $config);
            $this->configModel->flagResource->save($flagModel);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $resultSave = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            return $jsonResult->setData($resultSave);
        }

        $resultSave = ['error' => false, 'message' => 'You have successfully saved the config!'];
        return $jsonResult->setData($resultSave);
    }
}
