<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
class CIN_ProductFilter_Model_Category
{
    protected $_cats;
    /**
     * Options getter
     *
     * @return array
     */
    protected function getTreeCategories($parentId, $level, $isChild){
        $allCats = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('*')
                    //->addAttributeToFilter('is_active','1')
                    //->addAttributeToFilter('include_in_menu','1')
                    ->addAttributeToFilter('parent_id',array('eq' => $parentId));

        //$children = Mage::getModel('catalog/category')->getCategories(7);
        foreach ($allCats as $category) 
        {
            if($isChild)
            {
                $this->_cats[] = array('value' => $category->getId(), 'label' => $level.$category->getName());
            }
            $subcats = $category->getChildren();
            if($subcats != ''){
                $this->getTreeCategories($category->getId(), $level.'-----', true);
            }
        }
    }


    public function toOptionArray()
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $this->_cats[] = array('value' => 0, 'label' => '');
        $this->getTreeCategories($rootCatId,'', false);
        return $this->_cats;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('No'),
            1 => Mage::helper('adminhtml')->__('Yes'),
        );
    }

}
