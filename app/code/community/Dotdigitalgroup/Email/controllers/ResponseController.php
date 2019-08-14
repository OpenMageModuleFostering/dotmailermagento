<?php

class Dotdigitalgroup_Email_ResponseController extends Mage_Core_Controller_Front_Action
{
    protected function checkContentNotEmpty($output, $flag = true)
    {
        try{
            if(strlen($output) < 3 && $flag == false)
                $this->sendResponse();
            elseif($flag && !strpos($output, '<table'))
                $this->sendResponse();
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception($e->getMessage());
        }
    }

    private function sendResponse()
    {
        try{
            $this->getResponse()
                ->setHttpResponseCode(204)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', 'text/html; charset=UTF-8', true);
            $this->getResponse()->sendHeaders();
        } catch (Exception $e) {
            Mage::logException($e);
            throw new Exception($e->getMessage());
        }
    }
}