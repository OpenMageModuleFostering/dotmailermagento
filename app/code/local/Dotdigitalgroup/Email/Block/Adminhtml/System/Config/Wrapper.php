<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Wrapper extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setData('onchange', "apiValidation(this.form, this);");

        $element->setData('after_element_html', "
            <script>
                document.observe('dom:loaded', function(){
                    apiValidation();

                 });
                function apiValidation(form, element) {
                    var api_user       = $('connector_api_settings_api_credentials_username');
                    var api_password   = $('connector_api_settings_api_credentials_password');

                    var reloadurl  = '{$this->getUrl('connector/debug/ajaxvalidation')}';

                    new Ajax.Request(reloadurl, {
                        method: 'post',
                        parameters: {'api_user' : api_user.value, 'api_password' : api_password.value},
                        onComplete: function(transport) {
                            Element.hide('loadingmask');
                            if(transport.responseText == '\"Valid\"'){
                                api_user.setStyle({
                                    fontWeight: 'bold',
                                    color:  'green' ,
                                    background: 'transparent url(\"" . $this->getSkinUrl('images/success_msg_icon.gif') . "\") no-repeat right center'
                                })
                            }else{
                                api_user.setStyle({
                                    fontWeight: 'bold',
                                    color:  'red',
                                    background: 'transparent url(\"" . $this->getSkinUrl('images/error_msg_icon.gif') . "\") no-repeat right center'
                                });

                            }
                        }
                    });

                    return false;
                }

            </script>
        ");

        return parent::_getElementHtml($element);

    }
}