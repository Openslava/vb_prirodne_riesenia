<?php
/**
 * MioShop enumerations.
 * User: kuba
 * Date: 08.03.16
 * Time: 11:17
 */

/**
 * Basic helper routines for enumerations. Has routines to check validity of enumeration name an enumeration value.
 * Class BasicEnum
 *
 * @link http://stackoverflow.com/users/32536/brian-cline
 * @link http://stackoverflow.com/questions/254514/php-and-enumerations.
 */
abstract class MwsBasicEnum {
	private static $constCacheArray = NULL;
	private static $captions = NULL;

	private static function getConstants() {
		if (self::$constCacheArray == NULL) {
			self::$constCacheArray = array();
		}
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$constCacheArray)) {
			$reflect = new ReflectionClass($calledClass);
			self::$constCacheArray[$calledClass] = $reflect->getConstants();
		}
		return self::$constCacheArray[$calledClass];
	}

	public static function isValidName($name, $strict = false) {
		$constants = self::getConstants();

		if ($strict) {
			return array_key_exists($name, $constants);
		}

		$keys = array_map('strtolower', array_keys($constants));
		return in_array(strtolower($name), $keys);
	}

	public static function isValidValue($value, $strict = true) {
		$values = array_values(self::getConstants());
		return in_array($value, $values, $strict);
	}

	/**
	 * Checks if value is a valid value of enumeration. If not, default value is returned.
	 * @param $value mixed Value of enumeration to check.
	 * @param null $default Value used when check fails.
	 * @return mixed|null
	 */
	public static function checkedValue($value, $default=null) {
		if(self::isValidValue($value))
			return $value;
		else
			return $default;
	}

	/**
	 * Returns array of enumeration keys (=constant names).
	 * @return array
	 */
	public static function getAllKeyNames() {
		return array_keys(self::getConstants());
	}

	/**
	 * Returns array of enumeration values (=constant values) in their order of definition.
	 * @return array
	 */
	public static function getAll() {
		return array_values(self::getConstants());
	}

	/**
	 * Descendants should return array of strings indexed by enumeration values.
	 * These strings can be gained using {@link getCaption()} method.
	 */
	protected static function doInitCaptions() {
		return null;
  }

	private static function initCaptions() {
		if (self::$captions == NULL) {
			self::$captions = array();
		}
		$calledClass = get_called_class();
		if (!array_key_exists($calledClass, self::$captions)) {
			$strings = static::doInitCaptions();
			self::$captions[$calledClass] = $strings;
		}
	}

	public static function getCaption($value) {
		$calledClass = get_called_class();
		self::initCaptions();
		return isset(self::$captions[$calledClass][$value]) ? self::$captions[$calledClass][$value] : (string)$value;
	}

}

/** Types of product as related to its delivery characteristic. */
abstract class MwsProductType extends MwsBasicEnum {
	const Electronic = 'electronic';
	const Physical = 'physical';
	const Other = 'other';
}

/** Types of product a for its structure */
abstract class MwsProductStructureType extends MwsBasicEnum {
	/** Basic product with simplest settings. */
	const Single = 'single';
	/** Product supporting multiple variants. Each variant is defined by a set of parameters. */
	const Variants = 'variants';

	/** Instance of one variant. Used internally. */
	const OneVariant = 'onevariant';
}

/** Enumeration of steps of an order. */
abstract class MwsOrderStep extends MwsBasicEnum {
	const Cart = 1;
	const Contact = 2;
	const Shipping = 3;
	const Summarize = 4;
	const ThankYou = 5;

	protected static function doInitCaptions() {
//		mwshoplog(__METHOD__, MWLL_DEBUG);
		return array(
			self::Cart => __('Nákupní košík','mwshop'),
			self::Contact =>__('Osobní údaje','mwshop'),
			self::Shipping =>__('Doprava a platba','mwshop'),
			self::Summarize =>__('Shrnutí objednávky','mwshop'),
			self::ThankYou =>__('Závěr','mwshop'),
		);
	}

	public static function getIcons() {
		return array(
			self::Cart => 'step_cart',
			self::Contact => 'step_contact',
			self::Shipping => 'step_shipping',
			self::Summarize => 'step_summarize',
			self::ThankYou => 'step_ok',
		);
	}
}

/** Enumeration of status of an order. */
abstract class MwsOrderStatus extends MwsBasicEnum {
	const Ordered = 1;
	const Processing = 2;
	const Delivered = 3;
	const Closed = 10;
	const Cancelled = 20;

	protected static function doInitCaptions() {
//		mwdbg(__METHOD__);
		return array(
			self::Ordered => __('nová','mwshop'),
			self::Processing =>__('zpracovává se','mwshop'),
			self::Delivered =>__('doručeno','mwshop'),
			self::Closed =>__('uzavřeno','mwshop'),
			self::Cancelled =>__('stornováno','mwshop'),
		);
	}

//	public static function getIcons() {
//		return array(
//			self::Ordered => __('Nová objednávka','mwshop'),
//			self::Processing =>__('Zpracovává se','mwshop'),
//			self::Delivered =>__('Doručeno','mwshop'),
//			self::Closed =>__('Uzavřeno','mwshop'),
//			self::Canceled =>__('Stornováno','mwshop'),
//		);
//	}
}


/** Supported currencies . */
abstract class MwsCurrency extends MwsBasicEnum {
	const czk='czk';
	const eur='eur';

	private static $symbols;

	protected static function doInitCaptions() {
		return array(
			self::czk => __('CZK - Kč','mwshop'),
			self::eur =>__('EUR - €','mwshop'),
		);
	}

	public static function getDefault() {
		return self::czk;
	}

	/**
	 * Get currency according to country. If not defined, then use default currency for eshop.
	 * @param string $country
	 * @return string|MwsCurrency
	 */
	public static function getByCountry($country) {
		if ($country === 'CZ') {
			return self::czk;
		} elseif ($country === 'SK') {
			return self::eur;
		} else {
			return MWS()->getCurrency('key');
		}
	}

	/**
	 * Get default currency conversion rate.
	 * @param string|MwsCurrency $from
	 * @param string|MwsCurrency $to
	 * @return float
	 */
	public static function getDefaultConversion($from, $to) {
		$arr = array();
		$arr[static::czk][static::eur] = 0.0369659674;
		$arr[static::eur][static::czk] = 27.0519094;
//		$arr[static::eur][static::usd] = 1.0725;
		return isset($arr[$from][$to])
			? $arr[$from][$to]
			: 1;
	}

	/** Get short unit of the currency, like € symbor for EUR
	 * @param string $currency Requested currency as value of {@link MwsCurrency}.
	 * @return string Currency symbol or passed value if symbol is not defined.
	 */
	public static function getSymbol($currency) {
		if(empty(static::$symbols)) {
			static::$symbols = array(
				self::czk => __('Kč','mwshop'),
				self::eur =>__('€','mwshop'),
			);
		}
		if(static::isValidValue($currency))
			return isset(static::$symbols[$currency]) ? static::$symbols[$currency] : $currency;
		 else
			return $currency;
	}

	/**
	 * Get an attribute value "step" for HTML5 elements according to passed currency.
	 * @param string|MwsCurrency|null $currency When null then default currency is used.
	 * @return string Resulting string is according to HTML specifications. Can be an integer or decimal number or "any" string.
	 */
	public static function getHtmlInputStepAttribute($currency = null) {
		static $steps;
		if (!$steps) {
			// "any" behaves the best - updates integ part of number and does not complain about decimal part
			$steps = array(
				static::czk => 'any',//'0.1',
				static::eur => 'any',//'0.01',
			);
		}
		if (!$currency) {
			$currency = MWS()->getCurrency('key');
		}
		return isset($steps[$currency]) ? $steps[$currency] : 'any';
	}
}

/** Type of restriction of selling of a product - disabled or kind of enabling */
abstract class MwsSellRestriction extends MwsBasicEnum {
	/** No restriction, selling is enabled. */
	const None = 'none';
	/** Full restriction, selling is disabled. */
	const FullDisable = 'full';
	/** Timed restrictions based on calendar. Selling is enabled within specified time period. */
	const EnabledInterval = 'interval';
	/** Timed restrictions based on calendar. Selling is enabled from time without end date. */
	const EnabledFrom = 'from';
	/** Timed restrictions based on calendar. Selling is enabled from now until specified time period. */
	const EnabledTill = 'to';
}

/** Type of sale price for product - disabled or which kind of enabling */
abstract class MwsSalePriceType extends MwsBasicEnum {
	/** No sale price active. */
	const None = 'none';
	/** Sale price is active permanently. */
	const Continuous = 'continuous';
	/** Sale price is enabled within an interval */
	const EnabledInterval = 'interval';
	/** Sale price is enabled from time without end date. */
	const EnabledFrom = 'from';
	/** Sale price is enabled from now until specified time period. */
	const EnabledTill = 'to';
}

/** Type of product property. */
abstract class MwsPropertyType extends MwsBasicEnum {
	const Enumeration = 'enum';
	const Text = 'text';
}

/** Type of product code. */
abstract class MwsProductCode extends MwsBasicEnum {
	/** Evidencni */
	const Filing = 'filing';
	/** Ucetni */
	const Financial = 'financial';
	/** Predkontace */
	const Assignment = 'assignment';
	/** Stredisko */
	const Center = 'center';
	/** Sklad */
	const Stock = 'stock';
	/** Skladova polozka */
	const StockItem = 'stockItem';

	protected static function doInitCaptions() {
		return array(
			self::Filing => __('Evidenční kód', 'mwshop'),
			self::Financial => __('Účetní kód', 'mwshop'),
			self::Assignment => __('Kód předkontace', 'mwshop'),
			self::Center => __('Kód střediska', 'mwshop'),
			self::Stock => __('Kód skladu', 'mwshop'),
			self::StockItem => __('Kód skladové položky', 'mwshop'),
		);
	}

}
