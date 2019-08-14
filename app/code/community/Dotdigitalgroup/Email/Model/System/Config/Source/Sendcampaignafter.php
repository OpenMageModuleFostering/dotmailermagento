<?php

class Dotdigitalgroup_Email_Model_System_Config_Source_Sendcampaignafter
{

    protected $times = array(1,2,3,4,5,6,12,36,48,60,72,84,96,108,120);
    /**
     * send to campain options hours
     * @return array
     */
    public function toOptionArray()
    {
        $result = $row = array();
        $i = 0;
        foreach($this->times as $one){

            if($i == 0)
                $row = array(
                    'value' => $one,
                    'label' => Mage::helper('connector')->__($one . ' Hour')
                );
            else
                $row = array(
                    'value' => $one,
                    'label' => Mage::helper('connector')->__($one . ' Hours')
                );
            $result[] = $row;
        }

        return $result;
    }
}
