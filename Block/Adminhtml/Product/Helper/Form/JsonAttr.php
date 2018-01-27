<?php
/**
 * Copyright (c) 2017 Indigo Geeks, Inc. All rights reserved.
 *
 * General.
 * The custom product builder software and documentation accompanying this License
 * whether on disk, in read only memory, on any other media or in any other form (collectively
 * the “Software”) are licensed, not sold, to you by copyright holder, Indigo Geeks, Inc.
 * (“Buildateam”) for use only under the terms of this License, and Buildateam reserves all rights
 * not expressly granted to you. The rights granted herein are limited to Buildateam’s intellectual
 * property rights in the Buildateam Software and do not include any other patents or
 * intellectual property rights. You own the media on which the Buildateam Software is
 * recorded but Buildateam and/or Buildateam’s licensor(s) retain ownership of the Software
 * itself.
 *
 * Permitted License Uses and Restrictions.
 * This License allows you to install and use one (1) copy of the Software.
 * This License does not allow the Software to exist on more than one production domain.
 * Except as and only to the extent expressly permitted in this License or by applicable
 * law, you may not copy, decompile, reverse engineer, disassemble, attempt to derive
 * the source code of, modify, or create derivative works of the Software or any part
 * thereof. Any attempt to do so is a violation of the rights of Buildateam and its licensors of
 * the Software. If you breach this restriction, you may be subject to prosecution and
 * damages.
 *
 * Transfer.
 * You may not rent, lease, lend or sublicense the Software.
 *
 * Termination.
 * This License is effective until terminated. Your rights under this
 * License will terminate automatically without notice from Buildateam if you fail to comply
 * with any term(s) of this License. Upon the termination of this License, you shall cease
 * all use of the Buildateam Software and destroy all copies, full or partial, of the Buildateam
 * Software.
 *
 * THIS SOFTWARE IS PROVIDED BY COPYRIGHT HOLDER "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. THE SOFTWARE IS NOT INTENDED FOR USE IN WHICH THE FAILURE OF
 * THE SOFTWARE COULD LEAD TO DEATH, PERSONAL INJURY, OR SEVERE PHYSICAL OR ENVIRONMENTAL DAMAGE.
 */

/**
 * @deprecated
 * TODO: Remove after review
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