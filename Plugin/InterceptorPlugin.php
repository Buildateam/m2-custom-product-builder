<?php
/**
 * Saraiva Platform | Fcamara
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.fcamara.com.br for more information.
 *
 * @category  ${MAGENTO_MODULE_NAMESPACE}
 * @package   ${MAGENTO_MODULE_NAMESPACE}_${MAGENTO_MODULE}
 *
 * @copyright Copyright (c) 2017 Fcamara - Saraiva Platform (http://www.fcamara.com.br)
 *
 * @author    Tiago Daniel <tiago.daniel@fcamara.com.br>
 */

namespace Buildateam\CustomProductBuilder\Plugin;


class InterceptorPlugin
{

    public function beforeSave()
    {

        $data = $_REQUEST['product'];
        $productId = $this->getRequest()->getParam('id');
        $jsonData = !empty($_FILES['product']['tmp_name']['json_configuration'])
            ? file_get_contents($_FILES['product']['tmp_name']['json_configuration'])
            : $data['product']['json_configuration'];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

    }

}