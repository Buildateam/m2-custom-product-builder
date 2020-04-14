<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Set extends Action
{
    /**
     * @return Json
     */
    public function execute(): Json
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($this->getRequest()->getParam('config')) {
            try {
                $config = $this->jsonSerializer->unserialize($this->getRequest()->getParam('config'));
            } catch (\InvalidArgumentException $e) {
                $result = ['error' => true, 'message' => $e->getMessage()];
                return $jsonResult->setData($result);
            }

            try {
                $this->flagManager->saveFlag(Action::FLAG_CODE, $config);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
                $resultSave = ['error' => true, 'message' => $e->getMessage()];
                return $jsonResult->setData($resultSave);
            }
        }

        $resultSave = ['error' => false, 'message' => 'You have successfully saved the config!'];
        return $jsonResult->setData($resultSave);
    }
}
