<?php

/**
 * MioShop price item wrapper. It works as extension of {@link WP_Object}.
 * User: kuba
 * Date: 4.0$.16
 * Time: 16:50
 */

/**
 * Class MwsPrice is a helper class for price manipulation and output formatting.
 *
 * @property float $priceVatExcluded
 * @property float $priceVatIncluded
 * @property float $priceVatAmount
 * @property float $vatPercentage
 * @property MwsCurrency $currency
 *
 */
class MwsPrice {
	/** @var int|null Level of VAT. */
	public $vatId;
	/** @var bool Price contains VAT. Default true. */
	public $vatIncluded = true;
	/** @var float Stored price as a decimal number. */
	public $priceStored;

	// ==== calculated values of properties, cached ====
//	/** @var int Calculated VAT percentage. Value is derived from assigned VAT $vatId. */
//	protected $_vatPercentage;
//	/** @var float Calculated price without VAT.  */
//	protected $_priceVatExcluded;
//	/** @var float Calculated VAT price.  */
//	protected $_priceVatAmount;
//	/** @var float Calculated price including VAT.  */
//	protected $_priceVatIncluded;


	/**
	 * Creates new priced item.
	 * @param float|array $priceStored Raw price or price fields as array from {@link asArray()}.
	 * @param int $vatId         Identifies one of global VAT levels. Optional when first parameter is array.
	 * @param string|MwsCurrency $currency Key of the currency. If null is present then primary currency is considered.
	 */
	function __construct($priceStored, $vatId=null, $currency = null) {
		if(is_array($priceStored)) {
			if (isset($priceStored['vatId'])) {
				$this->vatId = isset($priceStored['vatId']) ? (int)$priceStored['vatId'] : 0;
				$this->priceStored = isset($priceStored['priceStored']) ? (float)$priceStored['priceStored'] : 0;
				$this->vatIncluded = isset($priceStored['vatIncluded']) ? (float)$priceStored['vatIncluded'] : 0;
			} elseif (isset($priceStored['priceStored'])) {
				//Direct settings
				$this->priceStored = (float)$priceStored['priceStored'];
				$this->_vatPercentage = isset($priceStored['vatPercentage']) ? (float)$priceStored['vatPercentage'] : 0;
				$this->vatIncluded = isset($priceStored['vatIncluded']) ? (bool)$priceStored['vatIncluded'] : true;
			} else {
				//Direct settings
				$this->_priceVatAmount = isset($priceStored['priceVatAmount']) ? (float)$priceStored['priceVatAmount'] : 0;
				$this->_priceVatIncluded = isset($priceStored['priceVatIncluded']) ? (float)$priceStored['priceVatIncluded'] : 0;
				$this->_priceVatExcluded = isset($priceStored['priceVatExcluded']) ? (float)$priceStored['priceVatExcluded'] : 0;
				$this->_vatPercentage = isset($priceStored['vatPercentage']) ? (float)$priceStored['vatPercentage'] : 0;
				$this->vatIncluded = isset($priceStored['vatIncluded']) ? (bool)$priceStored['vatIncluded'] : true;
			}
			if(isset($priceStored['currency']) && !empty($priceStored['currency']))
				$currency = $priceStored['currency'];
		} else {
			// Properties
			$this->priceStored = isset($priceStored) ? (float)$priceStored : 0;
			$this->vatId = isset($vatId) ? (int)$vatId : null;
			//		$this->vatIncluded = isset($this->meta['vat_included']) ? (bool)$this->meta['vat_included'] : true;
		}
		$this->_currency = (!empty($currency)) ? MwsCurrency::checkedValue($currency, null) : null;
	}

	public static function createByFields($priceVatIncluded, $priceVatExcluded, $priceVatAmount, $vatPercentage, $vatIncluded, $currency) {
		$instance = new MwsPrice(
			array(
				'priceVatIncluded' => $priceVatIncluded,
				'priceVatExcluded' => $priceVatExcluded,
				'priceVatAmount' => $priceVatAmount,
				'vatPercentage' => $vatPercentage,
				'currency' => $currency,
			)
		);
		return $instance;
	}

	public static function createByValues($priceStored, $vatPercentage, $vatIncluded, $currency) {
		$instance = new MwsPrice(
			array(
				'priceStored' => $priceStored,
				'vatIncluded' => (bool)$vatIncluded,
				'vatPercentage' => $vatPercentage,
				'currency' => $currency,
			)
		);
		return $instance;
	}

	function __get($name) {
		if ($name == 'priceVatExcluded') {
			if (!isset($this->_priceVatExcluded)) {
				$this->_priceVatExcluded = $this->vatIncluded
					? $this->priceStored - $this->priceVatAmount
					: $this->priceStored;
			}
			return $this->_priceVatExcluded;
		} elseif ($name == 'priceVatIncluded') {
			if (!isset($this->_priceVatIncluded)) {
				$this->_priceVatIncluded = $this->vatIncluded
					? $this->priceStored
					: $this->priceStored + $this->priceVatAmount
				;
			}
			return $this->_priceVatIncluded;
		} elseif ($name == 'priceVatAmount') {
			if (!isset($this->_priceVatAmount)) {
				$percentage = $this->vatPercentage;
				if ($percentage==0)
					$this->_priceVatAmount = 0;
				else {
					$this->_priceVatAmount = floordec(
						($this->vatIncluded
						? $this->priceStored / (1 + $percentage) * $percentage
						: $this->priceStored * $percentage
					), 2);
				}
			}
			return $this->_priceVatAmount;
		} elseif ($name == 'vatPercentage') {
			if (!isset($this->_vatPercentage)) {
				$this->_vatPercentage = (float)($this->getVatPercentage()/100);
			}
			return $this->_vatPercentage;
		} elseif ($name == 'currency') {
			if (!isset($this->_currency) || is_null($this->_currency)) {
				// Use primary currency
				$this->_currency = MWS()->getCurrency('key');
			}
			return $this->_currency;
		}
		return null;
	}

	/**
	 * Format price including VAT.
	 * @param string $unit   Optional currency unit. Default currency is used if left with default null.
	 * @param int $amount Amount of pieces.
	 * @param bool $use0text When true and price is 0 then text "free" is output.
	 * @param string $divCSS Optional CSS text for wrapping DIV element.
	 * @return string
	 * @internal param float|null $priceOverride Optional value of the price, if the real price should not be used.
	 */
	public function htmlPriceVatIncluded($unit = null, $amount=1, $use0text = true, $divCSS=null, $beforeText='') {
		return htmlPriceSimpleIncluded($this->priceVatIncluded*$amount, $unit, $use0text, $divCSS, $beforeText);
	}

	/**
	 * Format price without VAT.
	 * @param null|string $unit Optional currency unit. Default currency is used if left with default null.
	 * @return string
	 */
	public function htmlPriceVatExcluded($unit=null, $amount=1) {
		return htmlPriceSimpleExcluded($this->priceVatExcluded*$amount, $unit, false);
	}

	/**
	 * Format all prices into one block, wrapped into div with CSS optionally.
	 * @param null|string $divCSS If not null then result will be wrapped within DIV element and value of this parameter
	 *                            will be used as value of element's CSS "class" attribute.
	 * @return string
	 */
	public function htmlPriceFull($divCSS=null, $amount=1) {
		$unit = MwsCurrency::getSymbol($this->currency);
		$res = '';
//		$res .= '<div class="mws_price_sale">'.$sale.'&nbsp;'.$unit.'&nbsp;'.$salePercentage.'%'</div>';
		$res .= $this->htmlPriceVatIncluded($unit, $amount, true);
		$res .= $this->htmlPriceVatExcluded($unit, $amount);
		if(!is_null($divCSS) && !empty($res))
			$res = '<div class="'.$divCSS.'">'.$res.'</div>';
		return $res;
	}

	/**
	 * Returns effective value of VAT assigned to the product. If VAT is not used or product has invalid settings, like
	 * currently disabled VAT, then default VAT value is returned.
	 * @return int
	 */
	public function getVatPercentage() {
		$vats = MWS()->getVATs();
		$vatVal = $vats->getValueById($this->vatId, false);
		if(is_null($vatVal))
			$vatVal = $vats->getValueDefault(false, 0);
		return $vatVal;
	}

	/**
	 * Get prices as array indexed by property names.
	 */
	public function asArray() {
		return array(
			'priceVatAmount' => $this->priceVatAmount,
			'priceVatIncluded' => $this->priceVatIncluded,
			'priceVatExcluded' => $this->priceVatExcluded,
			'vatPercentage' => $this->vatPercentage,
			'vatIncluded' => $this->vatIncluded,
			'currency' => $this->currency,
		);
	}

	/**
	 * Get prices as JSON encoded string.
	 * @param int $options JSON encoding options
	 * @return mixed|string
	 */
	public function asJson($options=0) {
		return json_encode($this->asArray(), JSON_PRESERVE_ZERO_FRACTION | $options);
	}

	/**
	 * @var array List of cached converted values.
	 */
	private $_currencies = array();

	/**
	 * @param $currency
	 * @return MwsPrice
	 */
	public function asCurrency($currency) {
		if(empty($currency)) {
			$currency = MWS()->getCurrency('key');
		}
		if(!isset($this->_currencies[$currency])) {
			$rate = MWS()->getCurrencyConversionRate($this->currency, $currency);
			$convPrice = MwsPrice::createByFields(
				$this->priceVatIncluded * $rate, $this->priceVatExcluded * $rate, ($this->priceVatIncluded - $this->priceVatExcluded) * $rate,
				$this->vatPercentage, $this->vatIncluded,
				$currency
			);
			$this->_currencies[$currency] = $convPrice;
		}
		return $this->_currencies[$currency];
	}

}