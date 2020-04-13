<?php

declare(strict_types=1);

namespace Buildateam\CustomProductBuilder\Controller\Adminhtml\Config;

use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Flag;

class Set extends Action
{
    /**
     * @return Json
     */
    public function execute(): Json
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            /** @var Flag $flag */
            $flag = $this->flagFactory->create(['data' => ['flag_code' => Action::FLAG_CODE]]);
            $this->flagResource->load(
                $flag,
                Action::FLAG_CODE,
                'flag_code'
            );
            $flag->setData('flag_data', $this->getRequest()->getParam('config'));
            $this->flagResource->save($flag);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $resultSave = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            return $jsonResult->setData($resultSave);
        }

        $resultSave = ['error' => false, 'message' => 'You have successfully saved the config!'];
        return $jsonResult->setData($resultSave);
    }
}
