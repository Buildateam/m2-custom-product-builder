<?php

namespace Buildateam\CustomProductBuilder\Block\Adminhtml\Product\Helper\Form;

class JsonAttr  extends \Magento\Framework\Data\Form\Element\Textarea
{

    public function getElementHtml()
    {

        $return
            = '
        <button
            id="open_model"
            title=""
            type="button"
            class="action-secondary"            
            data-ui-id="widget-button-1">
            <span>Edit</span>
        </button>
            <div id="myModel">                
                <textarea rows="50" cols="100">
                    '.$this->getData('value').'
                </textarea>
                <div>Json content.....</div>
            </div>
            <input style="display:none" id="json_configuration" class="" name="product[json_configuration]" data-ui-id="product-tabs-attributes-tab-fieldset-element-file-product-json-configuration" value="'.$this->getData('value').'" type="file">
            '.

            <<<HTML
            
            

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
            class="action-secondary import-json"            
            data-ui-id="widget-button-1">            
            <span>Import</span>
        </button>       

        <script>
        
        
        require(['jquery','Magento_Ui/js/modal/modal'], function($, modal){       
                
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        clickableOverlay: true,
                        title: ' Custom product builder',
                        buttons: [{
                            text: $.mage.__('Continue'),
                            class: '',
                            click: function () {
                                this.closeModal();
                            }
                        }]
                    };
                    
                    $(".import-json").on('click', function(e){
                        e.preventDefault();
                        $("#json_configuration:hidden").trigger('click');
                    });
                    
                    var popup = modal(options, $('#myModel'));
                    $("#open_model").on("click",function(){
                        $('#myModel').modal('openModal');
                    });                
                  
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