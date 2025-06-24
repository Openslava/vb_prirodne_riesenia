<?php

/**
 * MioShop CART implementation.
 *
 * Cart is saved in the PHP session storage. Parallel updates of the cart are protected by PHP session handling mechanism
 * that protects session against multiple access (only one thread is allowed to access concurrently).
 *
 * User: kuba
 * Date: 26.02.16
 * Time: 12:12
 */

/**
 * MioShop's CART class.
 * @class MwsCart
 * @property MwsCartItems $items Items of the cart
 * @property array $contact Contact information of the order.
 * @property int $shipping Selected shipping method. Shipping method is ID of a post of MWS_SHIPPING_SLUG type.
 * @property MwsPayType $payment Type of selected payment method.
 * @property MwsPrice $shippingPrice Price of shipping.
 * @property MwsShipping $shippingInstance Instance of shipping, derived from $shipping property.
 * @property bool isRecounted Returns true if cart total prices are counted (recounting error message {@link recountError}
 * and the flag {@link $recountNeeded} is not set.
 * @property MwsPrice storedTotalPrice Stored total price as counted during the latest cart recounting.
 * when new order is created.
 * @property int $availabilityErrorsCount Total number of errors concerning availability or cart item. Maximum 1 error for
 * every {@link MwsCartItem}. Error details are stored within {@link MwsCartItem}s.
 * @property array $purposes Filled purposes for personal data protection.
 */
class MwsCart {
	/** @var string This is set to nonempty string when cart total price is not recounted due to an error during recounting. */
	public $recountError;
	/** @var string Exception text message. Not user friendly.*/
	public $recountAdminError;
	/** @var bool Is cart already loaded from session? */
	protected $loaded=false;
	/** @var MwsSessionHelper Session storage object. */
	public $session;
	/** @var MwsCartItems Items of the cart.  */
	protected $_items;
	/** @var bool When counted prices of the cart need a refresh then this is set to true. */
	public $recountNeeded = true;
  // Property storage
	/** @var MwsPrice */
	protected $_storedTotalPrice;
	/** @var MwsPrice Calculated price of shipping. */
	protected $_shippingPrice;
	/** @var array Invoice data from order step. */
	protected $_contact;
	/** @var array Shipping data from order step. */
	protected $_shipping;
	/** @var MwsPayType Payment data from order step. */
	protected $_payment;
	protected $_shippingPriceIncluded;
	/** @var array Check status and text of purposes. */
	protected $_purposes;
	/** @var array Status of fulfillment of each order step, indexed by MwsPayType. */
	protected $_stepsFulfilled=array();

	function __construct() {
//		mwdbg(__CLASS__.' created');
		//Update session data upon exit.
		add_action('shutdown', array($this, 'save' ), 10 );
	}

	/**
	 * Returns true if the cart is empty.
	 * @return bool
	 */
	public function isEmpty() {
		return ($this->items->count() === 0);
	}

	/**
	 * Load content of the cart from session into intern cache.
	 */
	public function load() {
		if($this->loaded)
			return;
//		mwdbg(__METHOD__);
		$this->session = MwsSessionHelper::getInstance();
		// Load items.
		$this->_items = new MwsCartItems($this);
		$this->_items->load($this->session);
		$this->recountNeeded = (bool) $this->session->recountNeeded;
		$this->recountError = $this->session->recountError;
		$this->_stepsFulfilled = $this->session->stepsFulfilled;
		$this->_contact = $this->session->contact;
		$this->_shipping = $this->session->shipping;
		$this->_payment = $this->session->payment;
		$this->_shippingPriceIncluded = (bool)$this->session->shippingPriceIncluded;
		$x = $this->session->shippingPrice;
		$this->_shippingPrice = (is_null($x) ? null : new MwsPrice($x));
		$x = $this->session->storedTotalPrice;
		$this->_storedTotalPrice = (is_null($x) ? null : new MwsPrice($x));
		$this->availabilityErrorsCount = isset($this->session->availabilityErrorsCount)
			? (int)$this->session->availabilityErrorsCount : 0;
		$this->_purposes = $this->session->purposes;

		$this->loaded = true;
	}

	/**
	 * Save content of the cart to session storage.
	 * Intern cache of the cart content is used.
	 * @return bool True when saved, false otherwise.
	 */
	public function save() {
//		mwdbg(__METHOD__);
		if($this->loaded) {
			// Save items
			$this->items->save($this->session);
			$this->session->recountNeeded = (bool)$this->recountNeeded;
			$this->session->recountError = $this->recountError;
			$this->session->stepsFulfilled = $this->_stepsFulfilled;
			$this->session->contact = $this->_contact;
			$this->session->shipping = $this->_shipping;
			$this->session->payment = $this->_payment;
			$this->session->shippingPriceIncluded = $this->_shippingPriceIncluded;
			$this->session->shippingPrice = isset($this->_shippingPrice) && !is_null($this->_shippingPrice)
				? $this->_shippingPrice->asArray()
				: null
			;
			$this->session->storedTotalPrice = isset($this->_storedTotalPrice) && !is_null($this->_storedTotalPrice)
				? $this->_storedTotalPrice->asArray()
				: null
			;
			$this->session->availabilityErrorsCount = isset($this->availabilityErrorsCount)
				? $this->availabilityErrorsCount : 0;
			$this->session->purposes = $this->_purposes;

			return true;
		}
		return false;
	}

	/**
	 * Calculates prices for the cart content. Default gateway is used for calculation if not specified differently.
	 * As a result prices of cart items a filled and total prices too.
	 * When recounting is successful, flag $recountNeeded is reset.
	 * @param bool $includeShippingPrice Should calculation include shipping price?
	 * @param bool $ignoreSimplified If set to true, then counting will not use optional simplified invoice calculation.
	 * @param string $gwId               ID of the gateway that should be used. If 'default' is used then global gateway defined
	 *                                   for counting is used.
	 * @param bool $force                Forces recounting even if flag $recountNeeded is not set.
	 * @return bool Returns true if recounting was performed.
	 * @throw Exception If error occurs during recounting.
	 */
  public function recount($includeShippingPrice, $ignoreSimplified, $gwId = 'default', $force = false) {
		$includeShippingPrice = (bool)$includeShippingPrice;
		$ignoreSimplified = (bool)$ignoreSimplified;
    $this->load();
    $perform = ($force || $this->recountNeeded || $includeShippingPrice!==$this->_shippingPriceIncluded);
    if (!$perform)
      return false;

		/** @var MwsGatewayMeta $gw */
		if($gwId='default')
			$gw = MWS()->gateways()->getDefault();
    else
      $gw = MWS()->gateways()->getById($gwId);
    if (is_null($gw)) {
      //TODO Some special handling? Or manual calculation at least?
      return false;
    }

		try {
			//Clear precounted prices
			/** @var MwsCartItem $cartItem */
			foreach ($this->items->data as $cartItem) {
				$cartItem->storedPrice = null;
				$cartItem->storedTotalPrice = null;
				$cartItem->availabilityStatus = MwsProductAvailabilityStatus::Unavailable_Disabled;
				$cartItem->availabilityError = '';
			}
			$this->storedTotalPrice = null;
			$this->shippingPrice = null;

			// Check an Uupdate availability of items
			$availabilityErrors = $this->checkAvailability();

			// Recount
			$wasCounted = $gw->sharedInstance()->recountCart($this, $includeShippingPrice, $ignoreSimplified);
			// Save the results.
			$this->_shippingPriceIncluded = $includeShippingPrice;
			$this->recountNeeded = !($wasCounted && ($availabilityErrors == 0));
			$this->recountError = ($availabilityErrors
				? sprintf(_nx('Omlouváme se, %d položku nelze zajistit v požadovaném množství.',
					'Omlouváme se, %d položek nelze zajistit v požadovaném množství.', $availabilityErrors,
					'Cart error message when some of cart items are not available in specified amount.', 'mwshop'),
					$availabilityErrors)
				: '');
			return $wasCounted;
		} catch (MwsUserException $e) {
			$this->recountNeeded = true;
			$this->recountError = $e->getMessage();
			$this->recountAdminError = '';
			return false;
		} catch (Exception $e) {
			$this->recountNeeded = true;
			$this->recountError = __('Omlouváme se, při výpočtu ceny došlo k chybě. Opakujte prosím pokus později.', 'mwshop');
			$this->recountAdminError = $e->getMessage();
			if(empty($this->recountAdminError))
				$this->recountAdminError = 'unexpected error';
			$this->recountAdminError .= ' ['.get_class($e).']';
			return false;
		}
  }

	/**
	 * Check availability of items in the cart. Method fills for every item its {@link MwsCartItem::availabilityStatus} and
	 * upon an error sets {@link MwsCartItem::availabilityError}. Method updates {@link availabilitErrorsCount} to the
	 * result value.
	 * @return int Number of items that has availability errors and can not be bought in requested order.
	 */
	public function checkAvailability() {
		// Check availability of items
		$availabilityErrors = 0;
		/** @var MwsCartItem $cartItem */
		foreach ($this->items->data as $cartItem) {
			if(!$cartItem->checkAvailability())
				$availabilityErrors++;
		}
		$this->availabilityErrorsCount = $availabilityErrors;
		return $availabilityErrors;
  }

	/**
	 * Add a new item into cart. If item is already present, then increment its count.
	 * @param $product int|MwsProduct ID of the product, alternatively a product instance.
	 * @param $count   int Count of product items to add into the cart.
	 * @return int Number of items added to the basket.
	 */
	public function addItem($product, $count) {
		return $this->items->add(
			new MwsCartItem(null, $product, array('count' => $count))
		);
	}

	function __get($name) {
		if($name==='items') {
			$this->load();
			return $this->_items;
    } elseif($name==='contact') {
			$this->load();
			return $this->_contact;
    } elseif($name==='shipping') {
			$this->load();
			return $this->_shipping;
    } elseif($name==='shippingPrice') {
			$this->load();
			return $this->_shippingPrice;
    } elseif($name==='shippingInstance') {
			if(!isset($this->_shippingInstance)) {
				return $this->_shippingInstance = MwsShipping::getById($this->shipping);
			}
			return $this->_shippingInstance;
    } elseif($name==='purposes') {
			$this->load();
			return $this->_purposes;
    } elseif($name==='payment') {
			$this->load();
			return $this->_payment;
		} elseif($name==='isRecounted') {
			$this->load();
			return !$this->recountNeeded && empty($this->recountError);
		} elseif($name==='storedTotalPrice') {
			$this->load();
			return $this->_storedTotalPrice;
		} elseif($name='availabilityErrorsCount') {
			$this->load();
			return $this->availabilityErrorsCount;
		}

    return null;
	}

	function __set($name, $value) {
		if($name==='contact') {
			$this->load();
			$this->_contact = $value;
		} elseif($name==='purposes') {
			$this->load();
			$this->_purposes = $value;
		} elseif($name==='shipping') {
			$this->load();
			$this->_shipping = $value;
		} elseif($name==='shippingPrice') {
			$this->load();
			if(is_null($value) || $value instanceof MwsPrice)
				$this->_shippingPrice = $value;
			else
				throw new MwsException('Property "shippingPrice" requires MwsPrice instance');
		} elseif($name==='payment') {
			$this->load();
			$this->_payment = $value;
		} elseif($name==='storedTotalPrice') {
			if(!is_null($value) && !$value instanceof MwsPrice)
				throw new MwsException('Property [storedTotalPrice] expects [MwsPrice] instance.');
			$this->_storedTotalPrice = $value;
		} elseif(!empty($name)) {
				$this->$name = $value;
		}
	}

	/**
	 * Return true if the step was fulfilled successfully.
	 * @param $step string|MwsOrderStep Step to be checked.
	 * @return bool
	 */
	public function isFulfilledStep($step) {
		$this->load();
		return (isset($this->_stepsFulfilled) && isset($this->_stepsFulfilled[$step]) && $this->_stepsFulfilled[$step]);
	}

	/**
	 * Return true if all previous steps are fulfilled. Current step is not taken into account.
	 * @param $step string|MwsOrderStep Step to be checked.
	 * @return bool
	 */
	public function areFulfilledPriorSteps($step) {
		$all = MwsOrderStep::getAll();
		$res = true;
		foreach ($all as $value) {
			if($value == $step || !$res)
				break;
			$res = $res && $this->isFulfilledStep($value);
		}
		return $res;
	}

	/**
	 * Update fulfilled step status.
	 * @param $step string|MwsOrderStep Which step status should be changed.
	 * @param $fulfilled bool
	 */
	public function setFulfilledStep($step, $fulfilled) {
		$this->load();
		if(!is_array($this->_stepsFulfilled))
			$this->_stepsFulfilled = array();
		$this->_stepsFulfilled[$step] = (bool)$fulfilled;
	}

	/**
	 * Returns associative array indexed by steps with bool values of each step fulfillment.
	 * @return array
	 */
	public function getStepsFulfillment() {
		$res = array();
		$all = MwsOrderStep::getAll();
		foreach ($all as $value) {
			$res[$value] = $this->isFulfilledStep($value);
		}
		return $res;
	}

	public function formatAddress($address, $toHtml = false) {
		if($toHtml)
			$quote = function($str) {
				$str=trim($str);
				if(empty($str))
					return '';
				else
					return '<div>'.esc_html($str).'</div>';
			};
		else
			$quote = function($str) {
				$str=trim($str);
				if(empty($str))
					return '';
				else
					return $str."\n";
			};

		if(is_array($address) && !empty($address)) {
			return trim(''
				. $quote(trim(
					(isset($address['firstname']) && !empty($address['firstname']) ? $address['firstname'] : '')
					. ' ' .
					(isset($address['surname']) && !empty($address['surname']) ? $address['surname'] : '')
				))
				. $quote((isset($address['street']) && !empty($address['street']) ? $address['street'] : ''))
				. $quote(trim(
					(isset($address['zip']) && !empty($address['zip']) ? $address['zip'] : '')
					. ' ' .
					(isset($address['city']) && !empty($address['city']) ? $address['city'] : '')
				))
				. $quote((isset($address['country']) && !empty($address['country']) ? MWS()->getCountryByCode($address['country']) : ''))
				. (isset($address['phone']) && !empty($address['phone']) ? $quote(__('Tel:','mwshop').' '.$address['phone']) : '')
			);
		} else
			return '';

	}

	public function formatCompanyInfo($toHtml = false) {
		if ($toHtml)
			$quote = function ($str) {
				$str = trim($str);
				if (empty($str))
					return '';
				else
					return '<div>' . esc_html($str) . '</div>';
			};
		else
			$quote = function ($str) {
				$str = trim($str);
				if (empty($str))
					return '';
				else
					return $str . "\n";
			};

		$companyInfo = isset($this->contact['company_info']) ? $this->contact['company_info'] : array();
		if (is_array($companyInfo) && !empty($companyInfo)) {
			return trim(''
				. (isset($companyInfo['company_name']) && !empty($companyInfo['company_name']) ? $quote($companyInfo['company_name']) : '')
				. (isset($companyInfo['company_id']) && !empty($companyInfo['company_id']) ? $quote(__('IČ', 'mwshop').' '.$companyInfo['company_id']) : '')
				. (isset($companyInfo['company_vat_id']) && !empty($companyInfo['company_vat_id']) ? $quote(__('DIČ', 'mwshop').' '.$companyInfo['company_vat_id']) : '')
			);
		} else
			return '';
	}

	/**
	 * Clear content of the cart, including clearing session data.
	 */
	public function clearAll() {
		// Properties
		$this->loaded = false;
		$this->recountNeeded = true;
		$this->_items->clear();
		$this->_stepsFulfilled = null;
		$this->_contact = null;
		$this->_shipping = null;
		$this->_payment = null;
		$this->_shippingPriceIncluded = null;
		$this->_shippingPrice = null;
		$this->_purposes = null;

		// Session
		unset($this->session->items);
		unset($this->session->recountNeeded);
		unset($this->session->stepsFulfilled);
		unset($this->session->contact);
		unset($this->session->shipping);
		unset($this->session->payment);
		unset($this->session->shippingPriceIncluded);
		unset($this->session->shippingPrice);
		unset($this->session->purposes);
	}

	/**
	 * Update ordered count of products within cart.
	 */
	public function incOrderedCount() {
		//Make statistics of ordered products.
		/** @var MwsCartItem $cartItem */
		foreach ($this->items->data as $cartItem) {
			$product = $cartItem->product;
			if($product)
				$product->incOrderedCount($cartItem->count);
		}
	}

	/**
	 * Returns if cart should be invoiced in simplified mode.
	 */
	public function useSimplifiedInvoice() {
		$canSimplified = MWS()->gateways()->getDefault()->getUseSimplifiedInvoice();
		$contact = $this->contact;
		return ($canSimplified && !(isset($contact['want_invoice']) && $contact['want_invoice']));
	}

	/**
	 * If content of the cart requires shipping. That is in case when there is at least one product with physical delivery.
	 * @return bool
	 */
	public function isShippingRequired() {
		/** @var MwsCartItem $item */
		foreach ($this->items->data as $item) {
			if ($item->product->type === MwsProductType::Physical) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get currency for the cart. Currency is derived by selected country.
	 * @return MwsCurrency|string Key of the currency
	 */
	public function getCurrency() {
		$contact = $this->contact;
		$country = isset($contact['address']['country']) ? $contact['address']['country'] : '';
		return MwsCurrency::getByCountry($country);
	}

}

/**
 * List of cart items.
 * Class MwsCartItems
 */
class MwsCartItems {
	/** @var MwsCart */
	private $_cart;
	/** @var array Items in cart as array. Items are of MwsCartItem class. */
	public $data = array();

	function __construct($cart) {
		$this->_cart = $cart;
	}

	public function count() {
		return count($this->data);
	}

	/**
	 * Search for item by its product id.
	 * @param $productId int
	 * @return MwsCartItem|null
	 */
	public function getById($productId) {
		/** @var MwsCartItem $item */
		foreach ($this->data as $item) {
			if ($item->productId == $productId)
				return $item;
		}
		return null;
	}

	/**
	 * Add new item into the cart. If same product is already present then only increment number of items in the basket.
	 * @param $item MwsCartItem
	 * @return int Returns number of added items. If no addition occurred, 0 is returned.
	 */
	public function add($item) {
		$found = $this->getById($item->productId);
		if(is_null($found)) {
			$item->parent = $this;
			$this->data[] = $item;
		} else {
			$found->count += $item->count;
		}
		$this->_cart->recountNeeded = true;
		$added = $item->count;
		return $added;
	}

	/**
	 * Remove item from the cart.
	 * @param $productId int
	 * @return int Number of removed cart items = lines in cart (not count of removed product).
	 */
	public function remove($productId) {
		$res = 0;
		/** @var MwsCartItem $item */
		foreach ($this->data as $key => $item) {
			if ($item->productId == $productId) {
				$res++;
				unset($this->data[$key]);
			}
		}
		if($res>0)
			$this->_cart->recountNeeded = true;
		return $res;
	}

	/**
	 * Is cart empty?
	 * @return bool
	 */
	public function isEmpty() {
		return (empty($this->data));
	}

	/**
	 * Load items from the session.
	 * @param $session MwsSessionHelper
	 */
	public function load($session) {
		$items = $session->items;
		if(isset($items) && is_array($items)) {
			foreach ($items as $productId => $data) {
				$product = MwsProduct::getById($productId);
				if(is_null($product)) {
					mwshoplog("Product with id=[$productId] does not exists or is not a product.", MWLL_WARNING, 'cart');
				} else {
					$newItem = new MwsCartItem($this, $productId, $data);
					$this->data[] = $newItem;
				}
			}
		}
	}

	/**
	 * Save items into the session.
	 * @param $session MwsSessionHelper
	 */
	public function save($session) {
		//Save only important data. Dynamic data, like bound objects, should not be stored.
		$reduced = array();
		/** @var MwsCartItem $item */
		foreach ($this->data as $item) {
			$reduced[$item->productId] = $item->toArray();
		}
		$session->items = $reduced;
	}

	public function clear() {
		$this->data = array();
	}

	public function setRecountNeeded() {
		if(!is_null($this->_cart))
			$this->_cart->recountNeeded = true;
	}
}

/**
 * One item of the cart.
 *
 * @property-read MwsProduct $product Direct access to the product. Lazy loaded.
 * @property int $count Count of items
 * @property MwsPrice $storedPrice Stored price from last successful cart recounting.
 * @property MwsPrice $storedTotalPrice Stored total price from last successful cart recounting.
 * @property int|MwsProductAvailabilityStatus $availabilityStatus Last counted status of availability of requested count
 * @property string $availabilityError Last counted error message depending on availability of requested count
 *
 */
class MwsCartItem {
	/** @var MwsCartItems */
	public $parent;
	public $productId;
	private $_count;
	/** @var MwsProduct Direct access to the product. Lazy loaded.*/
	private $_product;
  // Property storage
	private $_storedPrice;
	private $_storedTotalPrice;

	/**
	 * @param MwsCartItems $parent Owning parent item.
	 * @param $product int|MwsProduct
	 * @param array $data
	 */
	function __construct($parent, $product, $data=array()) {
		if(is_numeric($product))
			$this->productId = $product;
		elseif($product instanceof MwsProduct) {
			$this->productId = $product->id;
			$this->product = $product;
		}

		$this->_count = isset($data['count']) ? $data['count'] : 1;
		if(isset($data['storedPrice']))
			$this->_storedPrice = new MwsPrice($data['storedPrice']);
		if(isset($data['storedTotalPrice']))
			$this->_storedTotalPrice = new MwsPrice($data['storedTotalPrice']);
		$this->availabilityStatus = isset($data['availabilityStatus'])
			? (int)$data['availabilityStatus']
			: MwsProductAvailabilityStatus::Unavailable_Disabled;
		$this->availabilityError = isset($data['availabilityError'])
			? $data['availabilityError'] : '';
	}

	function __get($name) {
		if ($name==='product') {
			if (!isset($this->product)) {
				$post = get_post($this->productId);
				if(is_null($post)) {
					$this->_product = null;
				} else {
					$this->_product = MwsProduct::createNew($post);
				}
			}
			return $this->_product;
		} elseif($name==='count') {
			return (isset($this->_count) ? $this->_count : 0);
		} elseif($name==='storedPrice') {
			return $this->_storedPrice;
		} elseif($name==='storedTotalPrice') {
			return $this->_storedTotalPrice;
    }

		return null;
	}

	function __set($name, $value) {
		if($name=='product') {
			if($value instanceof MwsProduct) {
				$this->product = $value;
				$this->productId = $this->product->id;
			}
		} elseif ($name==='count') {
			$value = (int)$value;
			if($value !== $this->_count) {
				$this->_count = $value;
				$this->setRecountNeeded();
			}
		} elseif($name==='storedPrice') {
			if(!is_null($value) && !$value instanceof MwsPrice)
				throw new MwsException('Property [storedPrice] expects [MwsPrice] instance.');
			$this->_storedPrice = $value;
		} elseif($name==='storedTotalPrice') {
			if(!is_null($value) && !$value instanceof MwsPrice)
				throw new MwsException('Property [storedTotalPrice] expects [MwsPrice] instance.');
			$this->_storedTotalPrice = $value;
		} elseif(!empty($name)) {
//				if(is_string($value)||is_int($value)) mwshoplog($name.'='.$value, MWLL_DEBUG);
				$this->$name = $value;
		}
	}

	private function setRecountNeeded() {
		if(!is_null($this->parent))
			$this->parent->setRecountNeeded();
	}

	public function toArray() {
		$res = array(
			'count' => $this->count,
		);
		if($this->_storedPrice)
			$res['storedPrice'] = $this->_storedPrice->asArray();
		if($this->_storedTotalPrice)
			$res['storedTotalPrice'] = $this->_storedTotalPrice->asArray();
		if(isset($this->availabilityStatus))
			$res['availabilityStatus'] = $this->availabilityStatus;
		if(isset($this->availabilityError))
			$res['availabilityError'] = $this->availabilityError;

		return $res;
	}

	/**
	 * Check availability of amount of requested items. Set {@link availabilityStatus} and {@link availabilityError}.
	 * @param bool $decreaseStock If true then try to decrease stock. If stock is enabled and gets bellow 0 then forms error message
	 *                            for insufficient stock status.
	 * @return bool Returns false if error is present, true on success (item is/was available).
	 */
	public function checkAvailability($decreaseStock = false) {
		$product = $this->product;
		if(!$product) {
			$status = MwsProductAvailabilityStatus::Unavailable_Disabled;
			$error = __('Produkt není v prodeji.', 'mwshop');
		} else {
			$count = $this->count;
			$status = $product->getAvailabilityStatus($count);
			// For stock-enabled product pre-decrease items on stock
			if($decreaseStock && $product->stockEnabled && $product->canBuy($status)) {
				if($product->updateStockCount($count, MwsStockUpdate::Dec)) {
					$stockCount = $product->stockCount;
					if($stockCount >= 0 || $product->stockAllowBackorders) {
						// There were enough items on stock
						$status = $product->getAvailabilityStatus(0);
						$error = $product->getAvailabilityError(0, $status);
					} else {
						// Not enough items on stock, can still be OK for backorders
						$status = $product->getAvailabilityStatus($stockCount + $count);
						$error = $product->getAvailabilityError($stockCount + $count, $status);
						// Return into stock
						if(!$product->updateStockCount($count, MwsStockUpdate::Inc)) {
							//TODO Silence on stock update error??
						}
					}
				} else {
					// Error updating status
					// Logging was performed by "updatedStockCount()" routine.
					$status = MwsProductAvailabilityStatus::Unavailable_Disabled;
					$error = sprintf(__('Interní chyba (aktualizace skladových zásob produktu \'%s\')', 'mwshop'), $product->name);
				}
			} else {
				$error = $product->getAvailabilityError($count, $status);
			}
		}
		$this->availabilityStatus = $status;
		$this->availabilityError = $error;
		return $product->canBuy($status);
	}
}

/**
 * Temporary cart with disabled loading and saving.
 * Class MwsCartTemporary
 */
class MwsCartTemporary extends MwsCart {
	public function save() {
		// Do not save anything
	}

	public function load() {
		if($this->loaded)
			return;

		parent::load();
		$this->session = null;
		$this->_items->clear();
		$this->recountNeeded = false;
		$this->recountError = '';
		$this->_stepsFulfilled = array();
		$this->_contact = null;
		$this->_shipping = null;
		$this->_payment = null;
		$this->_shippingPriceIncluded = false;
		$this->_shippingPrice = null;
		$this->_storedTotalPrice = null;
		$this->_purposes = null;
	}


}


