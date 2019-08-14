<?php

class Dotdigitalgroup_Email_Block_Adminhtml_System_Config_Messagedefault extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    const DEFAULT_TEXT = 'Default SMS Text';

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract$element){


        $element->setData('placeholder', self::DEFAULT_TEXT);

        $element->setData('after_element_html', "

            <a href='#' onclick=\"injectText('sms_section_sms_message_one_message', '{{var order_number}}');return false;\">Insert Order Number</a>
            <a href='#' onclick=\"injectText('sms_section_sms_message_one_message', '{{var customer_name}}');return false;\">Insert Customer Name</a>

            <script type='text/javascript'>

                function addText(text){
                    $('sms_section_sms_message_one_message').value += text;
                }

                function injectText(element,value){
                 var element_dom=document.getElementById(element);
                 if(document.selection){
                  element_dom.focus();
                  sel=document.selection.createRange();
                  sel.text=value;
                  return;
                 }if(element_dom.selectionStart||element_dom.selectionStart=='0'){
                  var t_start=element_dom.selectionStart;
                  var t_end=element_dom.selectionEnd;
                  var val_start=element_dom.value.substring(0,t_start);
                  var val_end=element_dom.value.substring(t_end,element_dom.value.length);
                  element_dom.value=val_start+value+val_end;
                 }else{
                  element_dom.value+=value;
                 }
                }
            </script>
        ");
        return parent::_getElementHtml($element);
    }


}