<?php
/**
 * Copyright © 2017 Indigo Geeks, Inc. All rights reserved.
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions
 * are met:
 *
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * All advertising materials mentioning features or use of this software must display the following acknowledgement:
 * This product includes software developed by the the organization.
 * Neither the name of the copyright holder nor the names of its contributors may be used to endorse or promote products derived from this
 * software without specific prior written permission.
 *
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 * ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
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