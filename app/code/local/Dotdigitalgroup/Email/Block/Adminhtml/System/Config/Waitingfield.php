<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Waitingfield extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        // Get the default HTML for this option
        $html = parent::_getElementHtml($element);

        $html .= sprintf('<div id="loadingmask" style="position: fixed;">
            <div class="loader" id="loading-mask-loader">
            <img src="%sskin/adminhtml/default/default/images/ajax-loader-tr.gif" alt="%s"/>%s', preg_replace('/index.php\//', '', $this->getBaseUrl()), $this->__('Loading...'), $this->__('Loading...'))
            . '<div id="loading-mask"></div></div>';

        $jQuery = '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" ></script>';

        $jQuery .='
           <script type="text/javascript">
            jQuery.noConflict();
            jQuery(document).ready(function() {
                //hide the load image field
                jQuery("#row_connector_api_settings_api_credentials_validator .scope-label").hide();
                var loadingmask = jQuery("#loadingmask");
                loadingmask.hide();
                loadingmask.css({top : "50\%", left: "50\%"})

                jQuery("button").click(function(){

                    jQuery("body").css({"background-color": "black", "opacity": "0.4"});

                    loadingmask.show();
                    loadingmask.css({"display": "block", "opacity" : "1"});
                })
            });
            </script>';

       $html .= $jQuery;

       return $html;
    }

}