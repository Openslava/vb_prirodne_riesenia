<?php

/**
 * Accessor to global VAT definitions. Works as proxy on global shop definitions.
 * User: kuba
 * Date: 07.03.16
 * Time: 13:55
 *
 * @property int count Number of defined items.
 */
class MwsVATs {
	protected $_items;
	/** @var MwsVatAccounting */
	private $_vatAccounting;

	/**
	 * Get current VAT definitions. Setting is cached from the time of first load from shop's global settings.
	 * @return array Keys are "vat IDs", values are integer numbers or null if not used.
	 */
	public function load() {
		if(isset($this->_items)) {
			return $this->_items;
		}
		$stgs = MWS()->setting;
		$items = isset($stgs['vat_values']) ? $stgs['vat_values'] : array();
		$items = array_map(function($item) {return (!empty($item) || is_numeric($item)) ? (int)$item : null;}, $items);
		$this->_items = $items;
		//Set default VAT value in case VAT is used and first level is empty.
		if($this->hasValues() && !isset($items[0])) {
			$this->_items[0] = 0;
		}
		$vatAccounting = isset($stgs['vat_accounting']) ? $stgs['vat_accounting'] : MwsVatAccounting::noVat;
		$vatAccounting = MwsVatAccounting::checkedValue($vatAccounting, MwsVatAccounting::noVat);
		$this->_vatAccounting = $vatAccounting;

		return $this->_items;
	}

	public function isUsingVatAccounting() {
		$this->load();
		return ($this->_vatAccounting == MwsVatAccounting::withVat);
	}

	/**
	 * Get value of a VAT level.
	 * @param int $vatId    ID of requested VAT level.
	 * @param bool $stored If set to <code>true</code>, stored value is returned (usefull only for setting controls).
	 *                     Otherwise effective value is returned.
	 * @param null $default Default value of VAT for the case VAT level is not used.
	 * @return int|null If VAT level is defined then its value is returned. If not then $default value is returned.
	 */
	public function getValueById($vatId, $stored, $default = null) {
		$items=$this->load();
		if($stored || $this->isUsingVatAccounting())
			return isset($items[$vatId]) && is_numeric($items[$vatId]) ? $items[$vatId] : $default;
		else
			return 0;
	}

	/**
	 * Get default VAT value. It is value of first VAT level. If first VAT level is not defined, it behaves like VAT is 0%.
	 * @param bool $stored If set to <code>true</code>, stored value is returned (usefull only for setting controls).
	 *                     Otherwise effective value is returned.
	 * @param int|mixed $default If default VAT is not defined, then this values is returned.
	 * @return int|null Value of VAT.
	 */
	public function getValueDefault($stored, $default = 0) {
		return $this->getValueById(0, $stored, $default);
	}

	/**
	 * Is some VAT value set? If not it means VAT accounting is not used or undefined.
	 * @return bool
	 */
	public function hasValues() {
		$items=$this->load();
		$nonempty = array_filter($items, function($item){return !is_null($item);});
		return !empty($nonempty);
	}

	/**
	 * Return all VATs in an array. Keys are vat IDs, values are percents of VAT.
	 * @return array
	 */
	public function toArray() {
		$items=$this->load();
		return $items;
	}

	function __get($name) {
		if ($name = 'count') {
			$items=$this->load();
			return count($items);
		}
		return null;
	}

//	public function setById() {
		//TODO save
//	}

}

class MwsVatAccounting extends MwsBasicEnum {
	/** No VAT usage, no VAT identification. */
	const noVat = 'noVat';
	/** No VAT usage, using VAT identification. */
	const noVatIdentified = 'noVatIdentified';
	/** Full VAT accounting. */
	const withVat = 'withVat';
}