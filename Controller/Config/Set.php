<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Config;

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

        if (null !== $config = $this->getRequest()->getParam('config')) {
            if (!is_array($config)) {
                try {
                    $config = $this->jsonSerializer->unserialize($config);
                } catch (\Exception $e) {
                    $result = ['error' => true, 'message' => $e->getMessage()];
                    return $jsonResult->setData($result);
                }
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
