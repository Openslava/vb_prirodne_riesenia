<?php

/**
 * Storage of precounted prices of the items within the cart. Prices are indexed by "product ID" od text "shipping" for
 * the price of shipping.
 * Class MwsStoredPrices
 */
class MwsStoredPrices {
	private $_data=array();

	function __construct($savedArray) {
		$this->load($savedArray);
	}

	public function load($savedArray) {
		if(is_array($savedArray)) {
			foreach ($savedArray as $key => $data) {
				$this->set(
					$key,
					(isset($data['priceVatExcluded']) ? $data['priceVatExcluded'] : 0),
					(isset($data['priceVatIncluded']) ? $data['priceVatIncluded'] : 0),
					(isset($data['priceVatAmount']) ? $data['priceVatAmount'] : 0),
					(isset($data['vatPercentage']) ? $data['vatPercentage'] : 0)
				);
			}
		}
	}

	public function toArray() {
		$res = array();
		/** @var MwsStoredPrice $item */
		foreach ($this->_data as $key => $item) {
			$res[$key] = $item->toArray();
		}
	}

	public function set($index, $priceVatExcluded, $priceVatIncluded, $priceVatAmount, $vatPercentage) {
		if(!empty($index))
			$this->_data[$index] = new MwsStoredPrice($priceVatIncluded, $priceVatExcluded, $priceVatAmount, $vatPercentage);
	}

	public function get($index) {
		return isset($this->_data[$index]) ? $this->_data[$index] : null;
	}

}

/**
 * One precounted price.
 * Class MwsStoredPrice
 * @property float $priceVatExcluded
 * @property float $priceVatIncluded
 * @property float $priceVatAmount
 * @property float $vatPercentage
 */
class MwsStoredPrice {
	function __construct($priceVatExcluded, $priceVatIncluded, $priceVatAmount, $vatPercentage) {
		$this->_priceVatExcluded = $priceVatExcluded;
		$this->_priceVatIncluded = $priceVatIncluded;
		$this->_priceVatAmount = $priceVatAmount;
		$this->_vatPercentage = $vatPercentage;
	}

	function __get($name) {
		if ($name==='priceVatExcluded') {
			return isset($this->_priceVatExcluded) ? $this->_priceVatExcluded : 0;
		}
		elseif ($name==='priceVatIncluded') {
			return isset($this->_priceVatIncluded) ? $this->_priceVatIncluded : 0;
		}
		elseif ($name==='priceVatAmount') {
			return isset($this->_priceVatAmount) ? $this->_priceVatAmount : 0;
		}
		elseif ($name==='vatPercentage') {
			return isset($this->_vatPercentage) ? $this->_vatPercentage : 0;
		}
		return 0;
	}

	function __set($name, $value) {
		if ($name==='priceVatExcluded') {
			$this->_priceVatExcluded = (float)$value;
		}
		elseif ($name==='priceVatIncluded') {
			$this->_priceVatIncluded = (float)$value;
		}
		elseif ($name==='priceVatAmount') {
			$this->_priceVatAmount = (float)$value;
		}
		elseif ($name==='vatPercentage') {
			$this->_vatPercentage = (int)$value;
		}
	}

	public function toArray() {
		return array(
			'priceVatExcluded'=>$this->_priceVatExcluded,
			'priceVatIncluded'=>$this->_priceVatIncluded,
			'priceVatAmount'=>$this->_priceVatAmount,
			'vatPercentage'=>$this->_vatPercentage
		);
	}
}