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

        try {
            $this->flagManager->saveFlag(
                'buildateam_customproductbuilder_config',
                $this->getRequest()->getParam('config')
            );
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $resultSave = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            return $jsonResult->setData($resultSave);
        }

        $resultSave = ['error' => false, 'message' => 'You have successfully saved the config!'];
        return $jsonResult->setData($resultSave);
    }
}
