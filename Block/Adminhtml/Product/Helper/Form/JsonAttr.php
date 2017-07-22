<?php

namespace Buildateam\CustomProductBuilder\Block\Adminhtml\Product\Helper\Form;

class JsonAttr  extends \Magento\Framework\Data\Form\Element\File
{
    protected $_template = 'Buildateam_CustomProductBuilder/view/adminhtml/templates/product/edit/attribute/jsonattr.phtml';

    public function getAfterElementHtml()
    {
        $var = 0;
        $return
            = <<<HTML
        <button
            title=""
            type="button"
            class="action-secondary"
            onclick="jQuery('#new-video').modal('openModal'); jQuery('#new_video_form')[0].reset();"
            data-ui-id="widget-button-1">
            <span>Edit</span>
        </button>
        <button
            title=""
            type="button"
            class="action-secondary export-product-builder"            
            data-ui-id="widget-button-1">
            <span>Export</span>
        </button>
        <button
            title=""
            type="button"
            class="action-secondary"
            onclick="jQuery('#new-video').modal('openModal'); jQuery('#new_video_form')[0].reset();"
            data-ui-id="widget-button-1">            
            <span>Import</span>
        </button>        
<!--
<input id="json_configuration" name="product[json_configuration]" data-ui-id="product-tabs-attributes-tab-fieldset-element-file-product-json-configuration" value="" class="" type="file">-->

<script>
    require(['jquery'], function($){
    
        $( ".export-product-builder" ).click(function() {
            
            $.ajax({
               showLoader: true,
               url: '/admin/productbuilder/exportjson/build',
               data: 'alo',
               type: "POST",
               dataType: 'json'
                 }).done(function (data) {
                console.log(data);
            });
        });
        

});
</script>
HTML;


        return $return;
    }

}