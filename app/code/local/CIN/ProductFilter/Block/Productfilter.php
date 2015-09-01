<?php
class CIN_ProductFilter_Block_Productfilter
    extends Mage_Catalog_Block_Product_List
    implements Mage_Widget_Block_Interface
{
    protected function _toHtml()
    {
        $custom_template = $this->getData('custom_template');
        if($custom_template)
        {
            $this->setTemplate($custom_template);
        }
        else
        {
            $this->setTemplate('cin_productfilter/list.phtml');
        }
        return parent::_toHtml();
    }
    
    public function getProductCollection()
    {
        $type_filter = $this->getData('type_filter');
        $attribute_code = $this->getData('attribute_code');
        $sortby = $this->getData('sortby');
        $sort = explode(' ',$sortby);
        $count = $this->getData('count');
        $categories = $this->getData('categories');
        
        $storeId = (int) Mage::app()->getStore()->getId();
 
        // Date
        $date = new Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
        
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addPriceData()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setPageSize($count);
            
        if($type_filter == 1)//attribute code
        {
            $collection->addAttributeToFilter($attribute_code,1);
        }
        if($type_filter == 2)//best seller
        {
            $collection->getSelect()
                ->joinLeft(
                    array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                    "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                    array('SUM(aggregation.qty_ordered) AS sold_quantity')
                )
                ->group('e.entity_id');
        }
        if($type_filter == 3)//new product
        {
            $todayStartOfDayDate  = Mage::app()->getLocale()->date()
                ->setTime('00:00:00')
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

            $todayEndOfDayDate  = Mage::app()->getLocale()->date()
                ->setTime('23:59:59')
                ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            $collection->addAttributeToFilter('news_from_date', array('or'=> array(
                    0 => array('date' => true, 'to' => $todayEndOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter('news_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayStartOfDayDate),
                    1 => array('is' => new Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToFilter(
                    array(
                        array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
                        array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
                        )
                  );
        }
        if($type_filter == 4)//sale product
        {
            $collection->addFinalPrice()
               ->getSelect()
               ->where('price_index.final_price < price_index.price');
        }
        if($type_filter == 5)//normal product
        {
            //do nothing
        }
        if($type_filter == 6)//most viewed product
        {
            $collection = Mage::getResourceModel('reports/product_collection')
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addStoreFilter()
                ->addPriceData()
                ->addTaxPercents()
                ->addUrlRewrite()
                ->setPageSize($count);
        }
        
        //filter by categories
        if($categories)
        {
            $collection->joinField('category_id',
                    'catalog/category_product',
                    'category_id',
                    'product_id=entity_id',
                    null,
                    'left')
                ->addAttributeToFilter('category_id', array('in' => $categories));
            $collection->getSelect()->group('e.entity_id');
        }
        
        $collection->addAttributeToSort($sort[0], $sort[1]);
        
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        
        return $collection;
    }
	
}
