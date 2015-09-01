<?php
class CIN_ProductFilter_Model_Mysql4_Productfilter extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("productfilter/productfilter", "id");
    }
}
