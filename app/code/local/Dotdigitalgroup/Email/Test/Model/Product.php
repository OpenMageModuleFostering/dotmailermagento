<?php
class Dotdigitalgroup_Email_Test_Model_Product extends EcomDev_PHPUnit_Test_Case
{

    public function testIfTheAnswerToTheUniverseIs42()
    {

        $productModel = Mage::getModel('catalog/product')->load(39);


        $price = $productModel->getPrice();

        $this->assertEquals('20', $price);



    }
}