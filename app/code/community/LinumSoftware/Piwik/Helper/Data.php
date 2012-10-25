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

class LinumSoftware_Piwik_Helper_Data extends Mage_Core_Helper_Abstract {

  public function _log($msg)
  {
    Mage::Log($msg, 0, "piwik.log");
  }
  
  public function getProductCategories($product)
  {
    $id = current($product->getCategoryIds());
    $category = Mage::getModel('catalog/category')->load($id);
    $aCategories = array();
    foreach ($category->getPathIds() as $k => $id) {
      // Skip null and root
      if ($k > 1) {
	$category = Mage::getModel('catalog/category')->load($id);
	$aCategories[] = $this->toUTF8($category->getName());
      }
    }
    return (join('/', $aCategories));
  }
  
  public function getCategoryPath($category)
  {
    $aCategories = array();
    
    while($category->getLevel() > 1) {
      if($this->debugLog()) {
	$this->_log("getCategoryPath part:$category->getName()");
      }
      $aCategories[] = $this->toUTF8($category->getName());
      $category = $category->getParentCategory();
    }
    $_path = join('/', array_reverse($aCategories));
    if($this->debugLog()) {
      $this->_log("getCategoryPath fullpath: $_path");
    }
    
    return ($_path);
  }
  
  
  /**
   * returns a valid UTF8 string, either by converting from the store default
   * charset or cleaning up non compliant characters
   *
   * @param string $string
   * @param int $storeId
   * @return string
   */
  public function toUTF8($string, $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
  {
    if (strlen($string) > 0) {
      $utf8 = @iconv('UTF-8', 'UTF-8', $string);
      if ($string != $utf8) {
	// Not UTF-8
	$storeCharset = Mage::getStoreConfig("api/config/charset", $storeId);
	if (empty($storeCharset) || preg_match('/^utf-?8$/i', $storeCharset)) {
	  // No charset, or charset wrongly reported as utf-8
	  $storeCharset = 'ISO-8859-1';
	}
	return @iconv($storeCharset, 'UTF-8', $string);
      }
    }
    return $string;
  }
  
  public function getConfig($path)
  {
    // This method doesn't depend on stores being loaded.
    return (string) Mage::getConfig()->getNode('default/piwik/piwik/'.$path);
  }
  
  public function debugLog() {
    return ($this->getConfig('debuglog'));
  }
  
  public function isEnabled() {
    return ($this->getConfig('enabled'));
  }
  
}

?>
