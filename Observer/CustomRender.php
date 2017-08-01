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

namespace Buildateam\CustomProductBuilder\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Event\Observer as EventObserver;
use \Vendor\Product\Ui\DataProvider\Product\Form\Modifier\CustomFieldset;

class CustomRender  implements ObserverInterface
{

    /**
     * @param EventObserver $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $controller   = $observer->getAction();
//limit to the product view page
        if($controller->getFullActionName() != 'catalog_product_view')
        {
            return;
        }
        $layout       = $controller->getLayout();
        $root = $layout->getBlock('root');
        $product_info = $layout->getBlock('product.info');
        if(!$product_info)
        {
            Mage::log('Could not find product.info block');
            return;
        }
        $id = Mage::registry('current_product')->getId();
        $prod = Mage::getModel('catalog/product')->load($id);
        if ($prod->getAttributeSetId()==X) {
            $product_info->setTemplate('catalog/product/view7.phtml');
            $root->setTemplate('page/view7.phtml');
            $replacement =   $layout->createBlock('core/template')->setBlockAlias('replacements')->setTemplate('catalog/product/replacements.phtml')->setLayout($layout)->setNameInLayout('replacements');

            $options =     $layout->createBlock('replacements/replacement')->setBlockAlias('replacement_options')->setTemplate('catalog/product/replacement_options.phtml')->setLayout($layout)->setNameInLayout('replacement_options');
            $options->addOptionRenderer('select','replacements/options','catalog/product/view/options/type/replacement.phtml');
            $product_info->setChild('replacements',$replacement);
            $replacement->setChild('replacement_options',$options);
        }

    }

}