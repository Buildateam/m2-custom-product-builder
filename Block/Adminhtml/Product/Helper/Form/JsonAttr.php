<?php

namespace Buildateam\CustomProductBuilder\Block\Adminhtml\Product\Helper\Form;

class JsonAttr  extends \Magento\Framework\Data\Form\Element\File
{
    protected $_template = 'Buildateam_CustomProductBuilder/view/adminhtml/templates/product/edit/attribute/jsonattr.phtml';

    public function getAfterElementHtml()
    {
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
            class="action-secondary"
            onclick="jQuery('#new-video').modal('openModal'); jQuery('#new_video_form')[0].reset();"
            data-ui-id="widget-button-1">
            <span>Export</span>
        </button>
        <button
            title=""
            type="button"
            class="action-secondary"
            onclick="jQuery('#new-video').modal('openModal'); jQuery('#new_video_form')[0].reset();"
            data-ui-id="widget-button-1">
            <span>Delete</span>
        </button>
HTML;

        return $return;
    }

}