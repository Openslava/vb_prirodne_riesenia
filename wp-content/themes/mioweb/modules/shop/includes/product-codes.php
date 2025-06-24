<?php
/**
 * Product codes.
 * User: kuba
 * Date: 01.03.17
 * Time: 9:54
 */

class MwsProductCodes {
	private $_items;

	public function __construct($items) {
		$this->_items = $items;
	}

	/**
	 * Get value of a code type.
	 * @param string|MwsProductCode $codeType
	 * @return string
	 */
	public function getCode($codeType) {
		return isset($this->_items[$codeType]) ? $this->_items[$codeType] : '';
	}

	/**
	 * Set value of a code type.
	 * @param string|MwsProductCode $codeType
	 * @param string $value New value of code
	 */
	public function setCode($codeType, $value) {
		if (!$this->_items) {
			$this->_items = array();
		}
		$this->_items[$codeType] = $value;
	}

	public function toArray() {
		return (!$this->_items) ? array() : $this->_items;
	}

}