<?php
declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Config;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action
{
    /**
     * @return Json
     */
    public function execute(): Json
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $this->flagManager->deleteFlag(Action::FLAG_CODE);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result = ['error' => true, 'message' => $e->getMessage()];
            return $jsonResult->setData($result);
        }

        $resultDelete = ['error' => false, 'message' => 'You have successfully deleted the config!'];
        return $jsonResult->setData($resultDelete);
    }
}
