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

require_once(Mage::getBaseDir('lib') . DS . 'LinumSoftware' . DS . 'PiwikTracker.php');

class LinumSoftware_Piwik_Model_PiwikTracker extends PiwikTracker
{
  /**
   * the default piwikTracker automatically assigns a visitor id
   * we want to rely on the cookie id only for frontend visitors
   */
  public function __construct()
  {
    $_helper = Mage::helper('linumsoftware_piwik');
    parent::__construct($_helper->getConfig('siteid'), $_helper->getConfig('piwikurl'));
    
    if($_helper->debugLog()) {
      $_helper->_log("visitor Id(1): " . $this->getVisitorId());
    }
    /*
      if(Mage::getDesign()->getArea() == 'frontend') {
      $this->visitorId = false;
      }
    */
    if($_helper->debugLog()) {
      $_helper->_log("visitor Id(2): " . $this->getVisitorId());
    }
    
    // piwik authcode to allow setting of visitorId, IP and date & time
    $this->setTokenAuth($_helper->getConfig('authcode'));
    $this->setIp(Mage::helper('core/http')->getRemoteAddr());
    
    $_pai = $this->getAttributionInfo();
    if($_pai) {
      $this->setAttributionInfo($_pai);
      if($_helper->debugLog()) {
	$_helper->_log("set setAttributionInfo from frontend cookie.");
      }
    }
    // the visitor ID needs to be set to avoid a session split between a site
    // that uses the JS tracker for different urls at the same domain.
    $_tmpID = $this->getVisitorId();
    if(strlen($_tmpID) != 16) {
      if($_helper->debugLog()) {
	$_helper->_log("New user, no token or anything else found!");
      }
      //$_tmpID = '0123456789abcdef';
    } else {
      $this->setVisitorId($_tmpID);
    }
    if($_helper->debugLog()) {
      $_helper->_log("visitor Id(3): " . $this->getVisitorId());
    }
    
    
    // now we have a piwik tracker object filled with basic information
    // lets see if the current request is done from a logged in user
    $_session = Mage::getSingleton('customer/session');
    if($_session->isLoggedIn()) {
      $_customer = $_session->getCustomer();
      $_customerAddressId = $_customer->getDefaultBilling();
      if($_customerAddressId) {
	$_address = Mage::getModel('customer/address')->load($_customerAddressId);
	$_customer_company = $_address->getCompany();
      } else {
	$_customer_company = '<unbekannt>';
      }
      $_customer_name = $_customer->getName();
    } else {
      // sorry user is not logged in, no information available
      $_customer_company = '<unbekannt>';
      $_customer_name = "<nicht angemeldet>";
    }
    // log information about current user session
    $this->setCustomVariable(1, "Firma", $_customer_company);
    $this->setCustomVariable(2, "Name", $_customer_name);
    
    $_helper->_log("piwikTracker1: " . print_r($this, true));
  }
  
}
