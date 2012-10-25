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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * @package     LinumSoftware_Piwik
 * @copyright   Copyright (c) 2012 Linum Software GmbH (http://www.linum.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0) 
 */

class LinumSoftware_Piwik_Block_Tracker extends Mage_Core_Block_Text {
  
  protected function _toHtml() {
    
    $_helper = Mage::helper('linumsoftware_piwik');
    
    if(!$_helper->isEnabled()) {
      return ('');
    }
    
    $_trackThisRequest = true;
    
    // retrieve information about what kind of page we work with
    $_request    = Mage::app()->getRequest();
    $_controller = $_request->getControllerName();
    $_action     = $_request->getActionName();
    $_router     = $_request->getRouteName();
    $_module     = $_request->getModuleName();
    
    if($_helper->debugLog()) {
      $_helper->_log("controller: $_controller");
      $_helper->_log("action    : $_action");
      $_helper->_log("router    : $_router");
      $_helper->_log("module    : $_module");
    }
    
    // create new piwik tracker object
    $piwikTracker = new LinumSoftware_Piwik_Model_PiwikTracker();
    
    // user browse the catalog view
    if($_controller == 'category' && $_action == 'view') {
      $_layer         = Mage::getSingleton('catalog/layer');
      $_category      = $_layer->getCurrentCategory();
      $_category_path = $_helper->getCategoryPath($_category);
      $piwikTracker->setEcommerceView(
				      false,
				      false,
				      $_category_path,
				      false
				      );
    }
    
    // user browse the product view
    if($_controller == 'product' && $_action == 'view') {
      $_product  = Mage::registry('product');
      $itemPrice = $_product->getBasePrice();
      // This is inconsistent behaviour from Magento
      // base_price should be item price in base currency
      // TODO: add test so we don't get caught out when this is fixed in a future release
      if(!$itemPrice || $itemPrice < 0.00001) {
	$itemPrice = $_product->getPrice();
      }
      $piwikTracker->setEcommerceView(
				      $_product->getData('sku'),
				      $_product->getName(),
				      $_helper->getProductCategories($_product),
				      $itemPrice
				      );
    }
    
    if($_module == 'superstage') {
      $_trackThisRequest = false;
    }
    
    if($_trackThisRequest) {
      if($_helper->debugLog()) {
	$_helper->_log("visitor Id(4): " . $piwikTracker->getVisitorId());
	$_helper->_log("remote IP    : " . Mage::helper('core/http')->getRemoteAddr());
	
	$_helper->_log("piwikTracker2: " . print_r($piwikTracker, true));
      }
      $piwikTracker->doTrackPageView($this->getLayout()->getBlock('head')->getTitle());
    }
    // return no text, we just need to track the user
    return ('');
  }
}
