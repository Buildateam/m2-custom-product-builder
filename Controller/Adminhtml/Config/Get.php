<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;

class Get extends Action
{

    /**
     * @return Json
     */
    public function execute(): Json
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $data = $this->flagManager->getFlagData(Action::FLAG_CODE);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $data = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        return $result->setData($data);
    }
}
