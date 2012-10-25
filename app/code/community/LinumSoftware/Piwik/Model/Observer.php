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

class LinumSoftware_Piwik_Model_Observer extends Mage_Core_Helper_Abstract
{
  protected function track($isQuoteOrOrder, $q) {
    $_helper = Mage::helper('linumsoftware_piwik');
    // return if disabled or observer already executed on this request
    if (!$_helper->isEnabled() || Mage::registry('piwik_' . $isQuoteOrOrder . '_observer_executed')) {
      return ($this);
    }
    if($_helper->debugLog()) {
      $_helper->_log(">track[$isQuoteOrOrder] ---------------------------------------------------------");
    }
    
    $piwikTracker = new LinumSoftware_Piwik_Model_PiwikTracker();
    if($piwikTracker->getVisitorId()) {
      if(!is_null($q->getBaseGrandTotal())) {
	$this->_addEcommerceItems($piwikTracker, $q);
	if($isQuoteOrOrder == 'quote') {
	  $piwikTracker->doTrackEcommerceCartUpdate($q->getBaseGrandTotal());
	} else if($isQuoteOrOrder == 'order') {
	  $piwikTracker->doTrackEcommerceOrder($q->getId(),
					       $q->getBaseGrandTotal(),  // total with tax & shipping
					       $q->getBaseGrandTotal(),
					       0,
					       0,
					       0);
	}
      }
    } else {
      if($_helper->debugLog()) {
	$_helper->_log(" trackQuote: can't set visitor ID");
      }
    }
    
    if($_helper->debugLog()) {
      $_helper->_log("<track[$isQuoteOrOrder] ---------------------------------------------------------");
    }
    
    Mage::register('piwik_' . $isQuoteOrOrder . '_observer_executed', true);
  }
  
  public function trackQuote($observer)
  {
    $quote = $observer->getEvent()->getQuote();
    if(!is_null($quote)) {
      $this->track('quote', $quote);
    }
    return ($this);
  }
  
  public function trackOrder($observer)
  {
    $quote = $observer->getEvent()->getQuote();
    if(!is_null($quote)) {
      $this->track('quote', $quote);
    }
    
    return ($this);
  }
  
  /**
   * add all visible items from a quote as tracked ecommerce items
   *
   * @param Fooman_Jirafe_Model_JirafeTracker $piwikTracker
   * @param Mage_Sales_Model_Quote $quote
   */
  protected function _addEcommerceItems($piwikTracker, $quote)
  {
    $_helper = Mage::helper('linumsoftware_piwik');
    
    foreach ($quote->getAllVisibleItems() as $item) {
      if($item->getName()){
	//we only want to track the main configurable item
	//but not the subitem
	if($item->getParentItem()) {
	  if ($item->getParentItem()->getProductType() == 'configurable') {
	    continue;
	  }
	}
	
	$itemPrice = $item->getBasePrice();
	// This is inconsistent behaviour from Magento
	// base_price should be item price in base currency
	// TODO: add test so we don't get caught out when this is fixed in a future release
	if(!$itemPrice || $itemPrice < 0.00001) {
	  $itemPrice = $item->getPrice();
	}
	if($_helper->debugLog()) {
	  $_helper->_log("addEcommerceItem(" .
			 $item->getProduct()->getData('sku') . "," .
			 $item->getName() . "," .
			 $_helper->getProductCategories($item->getProduct()) . "," .
			 $itemPrice . "," .
			 $item->getQty() . ")"
			 );
	}
	$piwikTracker->addEcommerceItem(
					$item->getProduct()->getData('sku'),
					$item->getName(),
					$_helper->getProductCategories($item->getProduct()),
					$itemPrice,
					$item->getQty()
					);
      }
    }
  }
  
}
