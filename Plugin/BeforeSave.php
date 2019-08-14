<?php

namespace Buildateam\CustomProductBuilder\Plugin;

use Magento\Catalog\Controller\Adminhtml\Product\Save as Subject;

class BeforeSave
{
    public function beforeExecute(Subject $subject)
    {
        $params = $subject->getRequest()->getPostValue();
        unset($params['product']['json_configuration']);
        $subject->getRequest()->setPostValue('product', $params['product']);
    }
}
