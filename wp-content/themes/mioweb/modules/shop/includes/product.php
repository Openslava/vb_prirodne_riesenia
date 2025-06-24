<?php

/**
 * MioShop product wrapper. It works as extension of {@link WP_Object}.
 * User: kuba
 * Date: 23.02.16
 * Time: 16:50
 */


/** Name of the meta key of product. */
define('MWS_PRODUCT_META_KEY', 'product');
/** Name of the meta key of product's number of ordered items. */
define('MWS_PRODUCT_META_KEY_ORDEREDCOUNT', 'ordered_count');


/**
 * Class MwsProduct wraps information about a single product. Price of the product can be accessed using
 * {@link MwsProduct::price} property.
 *
 * @property int $id ID of post.
 * @property string $name Name/title of post.
 *
 * @property array $gallery Product image gallery. It is lazy loaded upon first galery request.
 * @property array $properties Readonly array of {@link MwsPropertyValue} that contains fixed properties of product.
 * @property string $detailUrl URL to the detail of the product.
 *
 * @property string $conversionCode
 * @property bool $hideComments
 * @property bool $showSimilar
 * @property bool $showSocial
 *
 * @property MwsProductStructureType $structure Structure of the product - single,
 * @property MwsProductType $type Type of the product - physical, electronic...
 *
 * @property MwsSync_Product $sync Data for synchronization.
 *
 * @property int $orderedCount Count of ordered items across from the start of the product.
 * @property bool $hideInListings Should be product hidden from listings, like catalog?
 *
 * @property int|null $vatId Get VAT id associated with the product. When no vat id is assigned null is returned.
 *
 * @property MwsPrice $price Effective price. This is either full price or discounted price, if discount is active.
 * @property MwsPrice $priceFull Full price without discount.
 *
 * @property bool $isDiscountedNow Is <code>true</code> when discount price is active right now. Discount is active when
 *  sale price is set and sale price restriction is enabled for current moment.
 * @property int $discountPercentage Discount expressed as percentage.
 * @property bool $variantPricesAreEqual Set to true when effective prices of all variants are same.
 *
 * @property bool $stockEnabled Should the product keep track of a stock?
 * @property int $stockCount Count of items available on stock.
 * @property bool $stockAllowBackorders Can be product ordered when there are no goods in stock?
 *
 * @property string|MwsSellRestriction $sellRestriction Active selling restriction
 * @property int $sellEnabledFrom Datetime in Unix epoch timestamp from which the product is available to be sold (inclusive).
 * @property int $sellEnabledTill Datetime in Unix epoch timestamp until which the product is available to be sold (exclusive).
 *
 * @property string|MwsSalePriceType $salePriceType Active sale price type
 * @property int $salePriceEnabledFrom Datetime in Unix epoch timestamp from which the sale price is enabled (inclusive).
 * @property int $salePriceEnabledTill Datetime in Unix epoch timestamp until which the sale price is enabled (exclusive).
 *
 * @property MwsProductCodes $codes List of extended codes
 *
 */
abstract class MwsProduct {
	/** @var int Timestamp of today midnight in GMT as unix timestamp. */
	static $nowMidnightLocal;
	/** @var WP_Post Post object. */
	public $post;
	/** @var null|array Cached settings of product. */
	public $meta=null;
	/** @var MwsSync_Product Synchronization data for*/
	protected $_sync;

	/** @var int */
	protected $_stockCount;
//  /** @var null|array Cached product gallery. Lazy loaded. */
//	private $_gallery=null;
	/** @var null|array Cached settings of product's page codes. */
	public $metaPageCodes=null;
	/** @var MwsProductCodes List of codes. Lazy loaded.  */
	protected $_codes=null;

	function __get($name) {
		if ($name === 'id') {
			return (isset($this->post) && isset($this->post->ID))
				? $this->post->ID
				: 0;
		} elseif ($name === 'name'){
			return (isset($this->post) && isset($this->post->post_title))
				? $this->post->post_title
				: _('(nový produkt)', 'mwshop');
		} elseif ($name === 'sync') {
			if(is_null($this->_sync))
				$this->_sync = new MwsSync_Product($this->id, $this);
			return $this->_sync;
		} elseif ($name === 'codes') {
			if (is_null($this->_codes)) {
				$this->_codes = $this->loadCodes();
			}
			return $this->_codes;
		} elseif ($name === 'conversionCode') {
			$this->loadMeta_PageCodes();
			return isset($this->metaPageCodes['product_conversion']) ? $this->metaPageCodes['product_conversion'] : '';
		} elseif ($name === 'orderedCount') {
			$meta = get_post_meta($this->id, MWS_PRODUCT_META_KEY_ORDEREDCOUNT, true);
			$val = (int)$meta;
			return $val;
		} elseif($name === 'stockCount') {
			if(!isset($this->_stockCount)) {
				$this->_stockCount = $this->getStockCount();
			}
			return $this->_stockCount;
		}
		// DEFAULT VALUES if ancestor did not defined own value
		elseif($name === 'isDiscountedNow') {
			return false;
		} elseif ($name === 'discountPercentage') {
			$fullPrice = $this->priceFull;
			$price = $this->price;
			if($fullPrice && $price) {
				$amountFullPrice = $this->priceFull->priceVatIncluded;
				$amountSalePrice = $this->price->priceVatIncluded;
				$discount = $amountFullPrice - $amountSalePrice;
				if ($discount < 0 || $amountFullPrice <= 0)
					return 0;
				$percentage = round($discount / ($amountFullPrice / 100), 0);
				return $percentage;
			}
			return 0;
		} elseif($name === 'detailUrl') {
			return (isset($this->detailUrl) ? $this->detailUrl : '');
		} elseif($name === 'variantPricesAreEqual') {
			return false;
		} elseif($name === 'vatId') {
			$this->loadMeta();
			return isset($this->meta['vat_id']) ? (int)$this->meta['vat_id'] : null;
		} else {
			// Property is not defined. Probably derived class does not provide necessary definition.
			if(property_exists($this, $name)) {
				// Properties set directly as fields
				return $this->$name;
			}
			$msg = sprintf(__('Požadovaný atribut %s.%s není definován.', 'mwshop'), get_called_class(), $name);
			mwshoplog($msg, MWLL_ERROR);
			throw new MwsException($msg);
		}
	}

	function __set($name, $value) {
		if($name==='orderedCount') {
			$value = (int)$value;
			update_post_meta($this->id, MWS_PRODUCT_META_KEY_ORDEREDCOUNT, $value);
		} elseif(!empty($name)) {
			$this->$name = $value;
		}
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 * @param $post WP_Post Instance of post with custom-post-type {@link MWS_PRODUCT_SLUG}.
	 * @param bool $forceUpdateCache When set to true then possibly precached instance will not be used but will be
	 *                               updated by the newly created instance.
	 * @return MwsProduct|object
	 * @throws MwsException If passed post is not of product post type.
	 */
	public static function createNew($post, $forceUpdateCache = false) {
		$postType = get_post_type($post);
		if(!in_array($postType, array(MWS_PRODUCT_SLUG, MWS_VARIANT_SLUG)))
			throw new MwsException('Passed post type is not of product type.');
		$structure = static::getPostStructureTypeStatic($post->ID);
		if($structure === false) {
			if($postType === MWS_VARIANT_SLUG)
				$structure = MwsProductStructureType::OneVariant;
		}
		switch($structure) {
			case MwsProductStructureType::Single:
			case MwsProductStructureType::Variants:
				$className = 'MwsProductRoot';
				break;
			case MwsProductStructureType::OneVariant:
				$className = 'MwsProductVariant';
				break;
			default:
				$className = '';
		}
		if(!$className) {
			if($post->post_status === 'auto-draft') {
				// Newly created post
				mwshoplog('Newly created unsaved PRODUCT post: ' . json_encode($post, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG);
				return null;
			}
			mwshoplog('Invalid PRODUCT post: ' . json_encode($post, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
			throw new MwsException('Passed post  ' . (isset($post->ID) ? "[{$post->ID}] " : '') . 'is not recognized to be a post of a product.');
		}
		$calledClass = get_called_class();
		// Strict check for called classes if class is not the base class.
		if($calledClass !== 'MwsProduct' && $className !== $calledClass)
			throw new MwsException('Post is not for requested class ' . get_called_class(). '.');
		//Is created already or must be updated in cache?
		if($forceUpdateCache || !($obj = MwsObjectCache::get($className, $post->ID))) {
			$obj = new $className($post);
			MwsObjectCache::add($obj, $obj->id);
		} else {
		}
		return $obj;
	}

	/**
	 * Get product instance by product ID.
	 * @param int $productId
	 * @param bool $forceRecache
	 * @return MwsProduct|object Existing product or null
	 */
	public static function getById($productId, $forceRecache = false) {
		if($productId) {
			$post = get_post($productId);
			if ($post)
				try {
					return static::createNew($post, $forceRecache);
				} catch (MwsException $e) {
					mwshoplog(sprintf(__('Nepodařilo se vytvořit instanci produktu [%d] se zprávou: %s', 'mwshop'), $productId, $e->getMessage()),
						MWLL_ERROR);
				}
		}
		return null;
	}

	/**
	 * Creates new product object as a wrapper of WP_Post object.
	 * @param $post WP_Post
	 * @throws MwsException
	 */
	function __construct($post) {
		$this->post = $post;

		$this->loadMeta();
		$this->loadProperties_Shared();
		$this->loadProperties_Derived();
	}

	/**
	 * Load property values shared by all product types.
	 */
	private function loadProperties_Shared() {
		// structure type
		$structure = static::getPostStructureTypeStatic($this->id);
		$this->__set('structure', $structure
			? $structure
			: MwsProductStructureType::Single
		);

	}

	protected function loadProperties_Derived() {
	}

	/** Load metadata of the product. Uses cached data if present. Does nothing if data has been already loaded. */
	public function loadMeta() {
		if(is_null($this->meta)) {
			$meta = get_post_meta($this->id, MWS_PRODUCT_META_KEY);
			if (isset($meta[0]))
				$this->meta = $meta[0];
			else
				$this->meta = array(); //support for nonexisting metadefinitions, can be case of nonexisting product too
		}
		return $this->meta;
	}

	/** Save meta information. Uses internal cached meta values. Updated values must be present in {@link meta} field. */
	protected function saveMeta() {
		if(is_null($this->meta))
			return;
		update_post_meta($this->id, MWS_PRODUCT_META_KEY, $this->meta);
	}

	/** Find structure-type of a product from a generic post.
	 * @param int $postId ID of a post whose {@link MwsProductStructureType} should be gained.
	 * @return string|false	Value from enumeration {@link MwsProductStructureType} or false.
	 */
	public static function getPostStructureTypeStatic($postId) {
		$meta = get_post_meta($postId, MWS_PRODUCT_META_KEY_STRUCTURE);
		$meta = isset($meta[0]) ? $meta[0] : false;
		if($meta === false) {
			$postType = get_post_type($postId);
			if($postType === MWS_VARIANT_SLUG) {
				$meta = MwsProductStructureType::OneVariant;
			}
		}
		$res = MwsProductStructureType::checkedValue($meta, false);
		return $res;
	}

	/** Find structure-type of a product from a generic post.
	 * @return string|false	Value from enumeration {@link MwsProductStructureType} or false.
	 */
	public function getPostStructureType() {
		$res = static::getPostStructureTypeStatic($this->id);
		return $res;
	}

	/**
	 * Save product structure type directly.
	 * @param bool $structure
	 */
	public function savePostStructureType($structure = false) {
		if(!$structure)
			$structure = $this->structure;
		$res = update_post_meta($this->id, MWS_PRODUCT_META_KEY_STRUCTURE, $structure);
	}


	/** Load metadata of the product gallery. Uses cached data if present. */
	public function loadGallery() {
		if(!isset($this->gallery)) {
			$meta = get_post_meta($this->id, MWS_PRODUCT_META_KEY_GALLERY);
			$this->__set('gallery', isset($meta[0])
				? $meta[0] : null);
		}
		return $this->gallery;
	}

	/** Load metadata of the product's page codes. Uses cached data if present. */
	public function loadMeta_PageCodes() {
		if(is_null($this->metaPageCodes)) {
			$meta = get_post_meta($this->id, MWS_PRODUCT_META_KEY_PAGECODES);
			if (isset($meta[0]))
				$this->metaPageCodes = $meta[0];
		}
		return $this->metaPageCodes;
	}

	/**
	 * Increment number of ordered items. Value is stored within own metavalue.
	 * @param int $addenum Number of ordered items.
	 */
	public function incOrderedCount($addenum=1) {
		$val = $this->orderedCount;
		$val += (int)$addenum;
		$this->orderedCount = $val;
	}

	/**
	 * Format all prices into one block, wrapped into div with CSS optionally. If sale price is active, than also
	 * full price is included.
	 * @param array $hideFields   List of fields to be hidden. Possible values are <code>'salePrice', 'salePercentage', 'vatIncluded', 'vatExcluded'</code>.
	 * @param null|string $divCSS If not null then result will be wrapped within DIV element and value of this parameter
	 *                            will be used as value of element's CSS "class" attribute.
	 * @return string
	 */
	public function htmlPriceSaleFull($divCSS = null, $amount=1, $hideFields=array()) {
		$shouldShow = function ($field) use ($hideFields) {
			return !(is_array($hideFields) && in_array($field, $hideFields));
		};
    $beforeText='';
		$res = '';
		$price = $this->price;
		if ($this->structure === MwsProductStructureType::Variants) {
			if($shouldShow('salePrice') || $shouldShow('vatIncluded') || $shouldShow('vatExcluded')) {
				if ($price) {
					if(!$this->variantPricesAreEqual)
						$beforeText = _x('od', 'Prepended text when price is counted as lowest price from product variants.', 'mwshop') . ' ';
				} else {
					$res .= _x('(neurčeno)', 'Text used when a price for variant product is not present.', 'mwshop');
				}
			}
		}
		if($price) {
			$unit = MWS()->getCurrency();
			if (($shouldShow('salePrice') || $shouldShow('salePercentage'))
				&& $this->isDiscountedNow && $this->priceFull->priceVatIncluded > 0
			) {
				$res .= '<div class="mws_price_sale">';
				if ($shouldShow('salePrice'))
					$res .= htmlPriceSimple($amount * $this->priceFull->priceVatIncluded, $unit, false,
						'mws_price_sale_vatincluded');
				if ($shouldShow('salePercentage') && $this->discountPercentage > 0)
					$res .= ' <span class="mws_price_sale_percentage">-' . $this->discountPercentage . '%</span>';
				$res .= '</div>';
			}
			if ($shouldShow('vatIncluded'))
				$res .= $this->price->htmlPriceVatIncluded($unit, $amount, true, null, $beforeText);
			if ($shouldShow('vatExcluded'))
				$res .= $this->price->htmlPriceVatExcluded($unit, $amount);
		}
		if(!is_null($divCSS) && !empty($res))
			$res = '<div class="'.$divCSS.'">'.$res.'</div>';
		return $res;
	}

	/**
	 * Update stock count using one of three methods. Update is done directly in the database.
	 * @param int $count                    New count or difference of count.
	 * @param string|MwsStockUpdate $method Method, how the stock count should be updated.
	 * @return bool Returns true when stock was updated as requested. False indicates error.
	 * When stock is not enabled od trying to update product type that does not support stock directly, then false is returned.
	 */
	public function updateStockCount($count, $method) {
		if(!is_int($count)) return false;
		if(!$this->stockEnabled) return true;
		if($this->structure === MwsProductStructureType::Variants) return true;

		global $wpdb;
		// Ensure key exists
		add_post_meta($this->id, MWS_OPTION_STOCKCOUNT, 0, true);
		// Update stock in DB directly
		$res = false;
		switch ($method) {
			case MwsStockUpdate::Inc :
				$res = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + %d WHERE post_id = %d AND meta_key='".MWS_OPTION_STOCKCOUNT."'", $count, $this->id) );
				$msg = _nx('Stav skladu produktu %s zvýšen o %d kus.', 'Stav skladu produktu %s zvýšen o %d kusů.', $count,
					'Shop log message when stock count has been incremented.', 'mwshop');
				break;
			case MwsStockUpdate::Dec :
				$res = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = meta_value - %d WHERE post_id = %d AND meta_key='".MWS_OPTION_STOCKCOUNT."'", $count, $this->id ) );
				$msg = _nx('Stav skladu produktu %s snížen o %d kus.', 'Stav skladu produktu %s snížen o %d kusů.', $count,
					'Shop log message when stock count has been decremented.', 'mwshop');
				break;
			default :
				$res = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = %d WHERE post_id = %d AND meta_key='".MWS_OPTION_STOCKCOUNT."'", $count, $this->id ) );
				$msg = _nx('Stav skladu produktu %s nastaven na %d kus.', 'Stav skladu produktu %s nastaven na %d kusů.', $count,
					'Shop log message when stock count has been set.', 'mwshop');
				break;
		}
		if(is_int($res)) {
			mwshoplog(
				sprintf($msg, "'{$this->name}' [{$this->id}]", $count),
				MWLL_INFO, 'stock'
			);
			$res = true;
		} else {
			mwshoplog(
				sprintf(__('Chyba při aktualizaci stavu skladu produktu %s.', 'mwshop'), "'{$this->name}' [{$this->id}]"),
				MWLL_ERROR, 'stock'
			);
			$res = false;
		}


		// Clear caches
		wp_cache_delete($this->id, 'post_meta');
		unset($this->_stockCount);

		// Stock status and notifications
//		$this->check_stock_status();

		// Trigger action
//		do_action( 'mws_product_set_stock', $this );

		return $res;
	}

	/**
	 * Is is possible to use discounted price now? This checks only sale price type and dates.
	 * @return bool Returns true when discount can be used.
	 */
	function canDiscountNow() {
		// Is sale price enabled?
		$salePriceType = $this->salePriceType;

		static $curTime;
		if (!isset($curTime) || is_null($curTime)) {
			$dt = new DateTime('now', new DateTimeZone('UTC'));
			$curTime = $dt->format('U');
		}

		if ($salePriceType === MwsSalePriceType::Continuous) {
			return true;
		} elseif ($salePriceType === MwsSalePriceType::EnabledFrom) {
			if ($curTime <= $this->salePriceEnabledFrom)
				return false;
		} elseif ($salePriceType === MwsSalePriceType::EnabledTill) {
			if ($this->salePriceEnabledTill < $curTime)
				return false;
		} elseif ($salePriceType === MwsSalePriceType::EnabledInterval) {
			if ($curTime <= $this->salePriceEnabledFrom)
				return false;
			elseif ($this->salePriceEnabledTill < $curTime)
				return false;
		} else {
			return false;
		}
		return true;
	}


	/**
	 * Is selling disabled in current time?
	 * @return string|MwsProductAvailabilityStatus|false Returns one of UNAVAILABLE status if selling restriction is active.
	 *                                                   If no restriction is applied then false is returned.
	 */
	function getSellingStatus() {
		static $curTime;
		if (!isset($curTime) || is_null($curTime)) {
			$dt = new DateTime('now', new DateTimeZone('UTC'));
			$curTime = $dt->format('U');
		}

		// Are selling restrictions enabled?
		$sellRestriction = $this->sellRestriction;
		if ($sellRestriction === MwsSellRestriction::FullDisable) {
			return MwsProductAvailabilityStatus::Unavailable_Disabled;
		} elseif ($sellRestriction === MwsSellRestriction::EnabledFrom) {
			if ($curTime <= $this->sellEnabledFrom)
				return MwsProductAvailabilityStatus::Unavailable_NotStartedYet;
		} elseif ($sellRestriction === MwsSellRestriction::EnabledTill) {
			if ($this->sellEnabledTill < $curTime)
				return MwsProductAvailabilityStatus::Unavailable_AlreadyFinished;
		} elseif ($sellRestriction === MwsSellRestriction::EnabledInterval) {
			if ($curTime <= $this->sellEnabledFrom)
				return MwsProductAvailabilityStatus::Unavailable_NotStartedYet;
			elseif ($this->sellEnabledTill < $curTime)
				return MwsProductAvailabilityStatus::Unavailable_AlreadyFinished;
		}
		return false;
	}

	/**
	 * Are any selling restrictions active?
	 * @param int $sellingStatus Optional precounted selling status as subset of values of {@link MwsProductAvailabilityStatus}.
	 * @return bool Returns false if no restrictions are active
	 */
	function canSell($sellingStatus = 0) {
		if($sellingStatus===0)
			$sellingStatus = $this->getSellingStatus();
		return ($sellingStatus === false);
	}

	/**
	 * Get CSS for selling status - enabled or disabled.
	 * @param int $sellingStatus Optional precounted selling status as subset of values of {@link MwsProductAvailabilityStatus}.
	 * @return string
	 */
	function getSellingCSS($sellingStatus = 0) {
		return ($this->canSell($sellingStatus))
			? 'mws_selling_enabled'
			: 'mws_selling_disabled';
	}

	/**
	 * Get the status of availability to sell the product. Status is further differentiated by the reason for that status.
	 * Positive value means AVAILABLE, negative values UNAVAILABLE.
	 * @param int $count Number of items that should be present, default 1.
	 * @param bool $skipSellRestriction If set to true than availability is evaluated without taking selling restriction
	 *                                  into account.
	 * @return int Value of enumeration <a href='psi_element://MwsProductAvailabilityStatus'>MwsProductAvailabilityStatus</a>.
	 */
	public function getAvailabilityStatus($count = 1, $skipSellRestriction = false) {
		//TODO Add other cases, like variants etc.

		if(!$skipSellRestriction) {
			$res = $this->getSellingStatus();
			if(!$this->canSell($res))
				return $res;
		}

		$res = MwsProductAvailabilityStatus::Unavailable_Disabled;

		if($this->stockEnabled) {
			// Stock enabled
			if($this->stockCount >= $count)
				$res = MwsProductAvailabilityStatus::Available_InStock;
			elseif ($this->stockAllowBackorders)
				$res = MwsProductAvailabilityStatus::Available_StockBackorder;
			else
				$res = MwsProductAvailabilityStatus::Unavailable_OutOfStock;
		} else {
			// Stock disabled
			$res = MwsProductAvailabilityStatus::Available_StockDisabled;
		}
		return $res;
	}

	/**
	 * Translate availability status into CSS for passed status or 1 items if default is used.
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real status value for1 item.
	 * @return string
	 */
	public function getAvailabilityCSS($availabilityStatus = 0) {
		if($availabilityStatus===0)
			$availabilityStatus = $this->getAvailabilityStatus(1);
		return MwsProductAvailabilityStatus::getCSS($availabilityStatus);
	}

	/**
	 * Translate availability status into CSS for defined count of items.
	 * @param int $count Number of items that should be present, default 1.
	 * @return string
	 */
	public function getAvailabilityCSS_Count($count = 1) {
		$status = $this->getAvailabilityStatus($count);
		return $this->getAvailabilityCSS($status);
	}

	/**
	 * Get text for BUY BUTTON depending on product availability status
	 * @param int|MwsProductAvailabilityStatus $availabilityStatus
	 * @return string
	 */
	public function getBuyButtonText($availabilityStatus = 0) {
		if($availabilityStatus===0)
			$availabilityStatus = $this->getAvailabilityStatus(1);
		if($this->canBuy($availabilityStatus)) {
			$res = _x('Koupit', 'Buy buttontext - product can be bought', 'mwshop');
		} else {
			$res = _x('Nedostupné', 'Buy buttontext - Product availability status is not recognized.', 'mwshop');
			switch ($availabilityStatus) {
				case MwsProductAvailabilityStatus::Unavailable_StockDisabled:
					$res = _x('Vyprodáno', 'Product is unavailable, stock is disabled.', 'mwshop');
					break;
				case MwsProductAvailabilityStatus::Unavailable_OutOfStock:
					$res = _x('Vyprodáno', 'Product is unavailable, stock is enabled.', 'mwshop');
					break;
				case MwsProductAvailabilityStatus::Unavailable_Disabled:
					$res = _x('Není v prodeji', 'Product is unavailable, manually disabled selling.', 'mwshop');
					break;
				case MwsProductAvailabilityStatus::Unavailable_NotStartedYet:
					$res = _x('Připravujeme', 'Product is unavailable, future sell.', 'mwshop');
					break;
				case MwsProductAvailabilityStatus::Unavailable_AlreadyFinished:
					$res = _x('Ukončeno', 'Product is unavailable, past sell.', 'mwshop');
					break;

				/*
				//	const Available_Variants = 4;
				//	const Unavailable_Variants = -4;
				*/
			}
		}
		return $res;
	}

	/**
	 * Translate availability status into user friendly localized string for passed status or 1 item if default is used.
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real status value for 1 item.
	 * @return string
	 */
	public function getAvailabilityMessage($availabilityStatus = 0) {
		if($availabilityStatus===0)
			$availabilityStatus = $this->getAvailabilityStatus(1);
		$res = _x('Dostupnost neznámá', 'Product availability status is not recognized.', 'mwshop');
		switch($availabilityStatus) {
			case MwsProductAvailabilityStatus::Available_StockDisabled:
				switch($this->type) {
					case MwsProductType::Electronic:
						$res = _x('Dostupné', 'Product is available, stock is disabled.', 'mwshop');
						break;
					default:
						$res = _x('Dostupné', 'Product is available, stock is disabled.', 'mwshop');
						break;
				}
				break;
			case MwsProductAvailabilityStatus::Available_InStock:
				$count = $this->stockCount;
				if($count > MioShop::StockLimit_Plenty)
					$res = sprintf(_x('Skladem >%s kusů', 'Product is available in stock, plenty of items available.', 'mwshop'),
						MioShop::StockLimit_Plenty);
				else
					$res = sprintf(_nx('Posledních 1 kus', 'Posledních %s kusů', $count, 'Product is available in stock, last items available.', 'mwshop'),
						$count);
				break;
			case MwsProductAvailabilityStatus::Available_StockBackorder:
				$res = _x('Na objednávku', 'Product is available using backorder.', 'mwshop');
				break;
			case MwsProductAvailabilityStatus::Unavailable_StockDisabled:
				$res = _x('Nedostupné', 'Product is unavailable, stock is disabled.', 'mwshop');
				break;
			case MwsProductAvailabilityStatus::Unavailable_OutOfStock:
				$res = _x('Vyprodáno', 'Product is unavailable, stock is enabled.', 'mwshop');
				break;
			case MwsProductAvailabilityStatus::Unavailable_Disabled:
				$res = _x('Nedostupné', 'Product is unavailable, manually disabled selling.', 'mwshop');
				break;
			case MwsProductAvailabilityStatus::Unavailable_NotStartedYet:
				$res = _x('Prodej nezahájen', 'Product is unavailable, future sell.', 'mwshop');
				break;
			case MwsProductAvailabilityStatus::Unavailable_AlreadyFinished:
				$res = _x('Prodej ukončen', 'Product is unavailable, past sell.', 'mwshop');
				break;

			/*
			//	const Available_Variants = 4;
			//	const Unavailable_Variants = -4;
			*/
		}
		return $res;
	}

	/**
	 * Translate availability status into user friendly localized string for defined count of items.
	 * @param int $count Number of items that should be present, default 1.
	 * @return string
	 */
	public function getAvailabilityMessage_Count($count = 1) {
		$status = $this->getAvailabilityStatus($count);
		return $this->getAvailabilityMessage($status);
	}

	/**
	 * Format HTML DIV element with availability status
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real value for 1 item.
	 * @return string
	 */
	public function htmlAvailabilityMessage($availabilityStatus = 0) {
    if(!isset(MWS()->setting['eshop_hide']['availability'])) {
    		if($availabilityStatus===0)
    			$availabilityStatus = $this->getAvailabilityStatus(1);
    		return '<div class="mws_product_availability">'
    			. esc_html($this->getAvailabilityMessage($availabilityStatus))
    			. '</div>';
    }
    else return '';
	}

	/**
	 * Get error message for availability like "Product sold out.", "Only 2 items in stock." when a count of items is requested.
	 * If availability of count is OK then empty string is returned.
	 * @param int $count Count of items whose error status message should be evaluated.
	 * @param int $status Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                    for specified count. Default value will count real value for $count of items.
	 * @return string
	 */
	public function getAvailabilityError($count, $status=0) {
		if($status===0)
			$status = $this->getAvailabilityStatus($count);
		if(!$this->canBuy($status)) {
			// Product can not be bought in specified amount.
			if($this->stockEnabled) {
				$stockCount = $this->stockCount;
				if ($stockCount < 1)
					$error = __('Produkt byl zcela vyprodán.', 'mwshop');
				else
					$error = sprintf(_nx('V nabídce je poslední %d kus.',
						'V nabídce je posledních %d kusů.', $stockCount,
						'Cart print count error message when product is out of stock.', 'mwshop'), $stockCount);
			} else {
				$error = __('Produkt není v prodeji.', 'mwshop');
			}
		} else
			$error = '';
		return $error;
	}

	/**
	 * Can be the product bought in specified amount?
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real status value for 1 item.
	 * @return bool
	 */
	public function canBuy($availabilityStatus = 0) {
		if($availabilityStatus===0)
			$availabilityStatus = $this->getAvailabilityStatus(1);
		return ($availabilityStatus > 0);
	}

	/**
	 * Can be the product bought in specified amount?
	 * @param int $count Number of items to buy, default 1
	 * @return bool
	 */
	public function canBuy_Count($count = 1) {
		$status = $this->getAvailabilityStatus($count);
		return $this->canBuy($status);
	}

	/**
	 * Get current stock count without caching. There is speed property {@link stockCount} which cache stock status upon
	 * first read.
	 * @return int
	 */
	protected function getStockCount() {
		return (int)get_post_meta($this->id, MWS_OPTION_STOCKCOUNT, true);
	}

	/**
	 * @param string|array $size Optional. Image size to use. Accepts any valid image size, or
	 *                           an array of width and height values in pixels (in that order).
	 *                           Default 'post-thumbnail'.
	 * @param string|array $attr Optional. Query string or array of attributes. Default empty.
	 * @return string The post thumbnail image tag.
	 */
	public function getThumbnail($size = 'post-thumbnail', $attr = '') {
		return get_the_post_thumbnail(
			$this->post ? $this->post : $this->id,
			$size, $attr);
	}
	public function getThumbnailUrl($size = 'large') {
		return wp_get_attachment_image_src( get_post_thumbnail_id( $this->post ? $this->post : $this->id ), $size );
	}
	/**
	 * Returns true if product is visible in catalog. This depends on global settings and availability of product.
	 * @param int $availabilityStatus Optional precounted availability status as constant {@link MwsProductAvailabilityStatus}.
	 *                                Default value will count real status value for 1 item.
	 * @return bool
	 */
	public function isVisible($availabilityStatus = 0) {
		if($this->hideInListings) {
			return false;
		} else {
			static $hiddenAvailabilityStatuses;
			if(is_null($hiddenAvailabilityStatuses)) {
				$hiddenAvailabilityStatuses = MWS()->getHiddenAvailabilityStatusesFor('product');
			}
			if($availabilityStatus === 0) {
				$availabilityStatus = $this->getAvailabilityStatus();
			}
			$res = !in_array($availabilityStatus, $hiddenAvailabilityStatuses);
			return $res;
		}
	}

	/**
	 * Get all defined published root products, that is single and variant product but without variations.
	 * @param array $queryArgs Optional parameters for {@link WP_Query}.
	 * @return array Array of {@link MwsProduct}
	 */
	public static function getAll($queryArgs = array('post_status' => 'publish')) {
		$args = array_merge(
			array(
				'post_type' => MWS_PRODUCT_SLUG,
			), //a must
			$queryArgs,	//user customization
			array('posts_per_page' => -1) //default values
		);
		$qry = new WP_Query($args);
		$res = array();
		if($qry->have_posts()) {
			foreach ($qry->posts as $post) {
				$product = MwsProduct::createNew($post);
				if($product) {
					$res[] = $product;
				}
			}
		}
		return $res;
	}

	/**
	 * Get list of root products that are not visible according to global settings and per product settings.
	 * @param bool $onlyIds If only relevant IDs of posts=products should be returned.
	 * @return array Array of {@link MwsProduct} or {@link int}.
	 */
	public static function getInvisibleProducts($onlyIds = false) {
		$all = MwsProduct::getAll();
		$invisible = array();
		/** @var MwsProduct $product */
		foreach ($all as $product) {
			if(!$product->isVisible()) {
				$invisible[] = $onlyIds ? $product->id : $product;
			}
		}
		return $invisible;
	}

	/**
	 * Load extended codes. Ancestors overrides.
	 * @return MwsProductCodes New instance of product codes.
	 */
	protected function loadCodes() {
		$meta = $this->loadMeta();
		$codes = $meta && $meta['codes'] ? $meta['codes'] : array();
		return new MwsProductCodes($codes);
	}

}

class MwsSync_Product extends MwsSync {

	public function shouldSync() {
		/** @var MwsProduct $product */
		$product = $this->_parent;

		if($product && ($product->structure != MwsProductStructureType::Variants)) {
			return parent::shouldSync();
		} else {
			return false;
		}
	}

	protected function doGetHashValuesArray() {
		/** @var MwsProduct $product */
		$product = $this->_parent;
		$res = array(
			$product->id, $product->name,
			$product->price->priceVatIncluded, $product->price->priceVatExcluded, $product->price->getVatPercentage(),
			$product->type,
			serialize(MWS()->getCurrencyConversionTable()),
			serialize($product->codes->toArray())
		);
		return $res;
	}
}

/** Update methods of a stock. */
class MwsStockUpdate extends MwsBasicEnum {
	/** Increment the stock count */
	const Inc = 'inc';
	/** Decrement stock count */
	const Dec = 'dec';
	/** Set stock count */
	const Set = 'set';
}

class MwsProductAvailabilityStatus extends MwsBasicEnum {
	const Available_StockDisabled = 1;
	const Available_InStock = 2;
	const Available_StockBackorder = 3;
//	const Available_Variants = ;

	const Unavailable_StockDisabled = -1;
	const Unavailable_OutOfStock = -2;
	const Unavailable_Disabled = -3;
	const Unavailable_NotStartedYet = -10;
	const Unavailable_AlreadyFinished = -11;
//	const Unavailable_Variants = ;


	private static function getCSSMatrix() {
		static $arr;
		if(empty($arr)) {
			$arr = array(
				self::Available_StockDisabled => 'mws_available mws_available_stockdisabled',
				self::Available_InStock => 'mws_available mws_available_instock',
				self::Available_StockBackorder => 'mws_available mws_available_stockbackorder',
//			self::Available_Variants => 'mws_available mws_available_variants',

				self::Unavailable_StockDisabled => 'mws_unavailable mws_unavailable_stockdisabled',
				self::Unavailable_OutOfStock => 'mws_unavailable mws_unavailable_outofstock',
				self::Unavailable_Disabled => 'mws_unavailable mws_unavailable_disabled',
				self::Unavailable_NotStartedYet => 'mws_unavailable mws_unavailable_futuresell',
				self::Unavailable_AlreadyFinished => 'mws_unavailable mws_unavailable_pastsell',

//			self::Unavailable_Variants => 'mws_unavailable mws_unavailable_variants',
			);
		}
		return $arr;
	}

	/**
	 * Get array of all CSS classes without starting dot.
	 * @return array
	 */
	public static function getAllCSSArray() {
		static $cssArr;
		if(!$cssArr) {
			$matrix = static::getCSSMatrix();
			$cssArr = array_unique(explode(' ', implode(' ', $matrix)));
		}
		return $cssArr;
	}
	/**
	 * Convert status into CSS classes.
	 * @param int $status Value of enumeration
	 * @return string
	 */
	public static function getCSS($status) {
		$arr = static::getCSSMatrix();
		return self::isValidValue($status, true) && isset($arr[$status]) ? $arr[$status] : '';
	}
}

$dt = new DateTime('today midnight', new DateTimeZone(wp_get_timezone_string()));
MwsProduct::$nowMidnightLocal = $dt->format('U');
