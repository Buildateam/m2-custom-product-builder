<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

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
