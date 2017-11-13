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