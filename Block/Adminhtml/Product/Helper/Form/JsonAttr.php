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
        <a id="downloadAnchorElem" style="display:none"></a>
        <button
            title=""
            type="button"
            class="action-secondary"
            onclick="jQuery('#new-video').modal('openModal'); jQuery('#new_video_form')[0].reset();"
            data-ui-id="widget-button-1">            
            <span>Import</span>
        </button>       

        <script>
            require(['jquery'], function($){       
              
                var url = window.location.pathname;
                
                $( ".export-product-builder" ).click(function() {    
        
                    $.ajax({
                       showLoader: true,
                       url: '/admin/productbuilder/exportjson/build',
                       data: 'productid='+url,
                       type: "POST",
                       dataType: 'json'
                         }).done(function (data) {
                            var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data, 0,4));
                            var dlAnchorElem = document.getElementById('downloadAnchorElem');
                            dlAnchorElem.setAttribute("href", dataStr);
                            dlAnchorElem.setAttribute("download", "product-builder.json");
                            dlAnchorElem.click();       
                        
                    });
                });        
        
            });
    </script>
HTML;


        return $return;
    }

}