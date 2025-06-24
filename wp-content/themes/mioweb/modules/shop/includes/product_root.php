<?php
/**
 * Simple single product
 * User: kuba
 * Date: 29.08.16
 * Time: 16:32
 */


/**
 * Instance of root product definition. It can be a SINGLE product or MASTER product of product variants.
 *
 * @property array $variants List of available published {@link MwsProductVariant} instances. Cached on first access.
 *                           Use {@link getVariants()} method to use a custom filter.
 * @property array|null $variantDefinition Defining array for variants.
 */
class MwsProductRoot extends MwsProduct {
	/** @var MwsPrice Effective price. Depends on sale-price settings if full or sale price is here. */
	private $_price;
	/** @var bool */
	private $_isDiscountedNow;
	/** @var MwsPrice Full price without discount. */
	private $_priceFull;
	/** @var MwsProductType */
	private $_type;
	/** @var bool */
	private $_stockEnabled;
	/** @var bool */
	private $_stockAllowBackorders;
	private $_sellRestriction;
	/** @var MwsSalePriceType */
	private $_salePriceType;
	/** @var bool Indicates that this defines a variant product. Value is counted upon instance creation and is preserved due it lifetime. */
	private $_isVariant = false;

	public function __construct($post) {
		parent::__construct($post);
	}

	function __get($name) {
		if ($name === 'priceFull') {
			if(is_null($this->_priceFull) && $this->_isVariant) {
				$this->loadVariantPrices(true);
			}
			return $this->_priceFull;
		} elseif ($name === 'price') {
			if(is_null($this->_price) && $this->_isVariant) {
				$this->loadVariantPrices(true);
			}
			return $this->_price;
		} elseif ($name === 'isDiscountedNow') {
			return $this->_isDiscountedNow;
		} elseif($name === 'type') {
			return $this->_type; //loaded in constructor
		} elseif($name === 'stockEnabled') {
			return $this->_stockEnabled;
		} elseif($name === 'stockAllowBackorders') {
			return $this->_stockAllowBackorders;
		} elseif($name === 'sellRestriction') {
			return $this->_sellRestriction;
		} elseif($name === 'salePriceType') {
			return $this->_salePriceType;
		} elseif($name === 'gallery') {
			return $this->loadGallery();
		} elseif($name === 'properties') {
			$res = array();
			if(!empty($this->meta['properties']) && is_array($this->meta['properties'])) {
				foreach ($this->meta['properties'] as $propId => $propValue) {
					$property = MwsProperty::getById($propId);
					// Use only properties with assigned values
					if($property && !empty($propValue)) {
						$newValue = $property->getValue($propValue);
						if($newValue) {
							$res[] = $newValue;
						} else {
							//Nonexisting value
							//TODO Do we want to have it in the collection, e.g. with some mark of invalidity?
						}
					}
				}
			}
			$this->__set($name, $res);
			return $res;
		} elseif($name === 'stockCount') {
//			if($this->structure === MwsProductStructureType::Variants)
//				return 0;
//			else
				return parent::__get('stockCount');
		} elseif($name === 'variants') {
			$res = $this->getVariants();
			$this->__set($name, $res);
			return $res;
		} elseif($name === 'variantDefinition') {
			$res = get_post_meta($this->id, MWS_PRODUCT_META_KEY_VARIANTLIST, true);
			return $res;
		}

		return parent::__get($name);
	}

	function __set($name, $value) {
		if ($name === 'stockEnabled') {
			$this->loadMeta();
			$value = (bool)$value;
			$this->meta['stock_enabled'] = (string)(int)$value;
			$this->_stockEnabled = $value;
		} elseif ($name === 'variantDefinition') {
			update_post_meta($this->id, MWS_PRODUCT_META_KEY_VARIANTLIST, $value);
		} else {
			parent::__set($name, $value);
		}
	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 * @param $post WP_Post Instance of post with custom-post-type {@link MWS_VARIANT_SLUG}.
	 * @param bool $forceUpdateCache When set to true then possibly precached instance will not be used but will be
	 *                               updated by the newly created instance.
	 * @return MwsProductRoot
	 * @throws MwsException If passed post is not of product post type.
	 */
	public static function createNew($post, $forceUpdateCache = false) {
		$res = parent::createNew($post, $forceUpdateCache);
		return $res;
	}

	/**
	 * Get product variant instance by variant ID.
	 * @param int $variantId
	 * @return MwsProductRoot Existing variant of product or null
	 */
	public static function getById($variantId, $forceRecache = false) {
		$res = parent::getById($variantId, $forceRecache);
		return $res;
	}

	protected function loadProperties_Derived() {
		$this->_isVariant = ($this->structure === MwsProductStructureType::Variants);

		// Product type according to delivery
		$this->_type = isset($this->meta['type'])
			? MwsProductType::checkedValue($this->meta['type'], MwsProductType::Electronic)
			: MwsProductType::Electronic;

		// detailUrl
		if (isset($this->meta['custom_detail']) && $this->meta['detail_page'])
			$this->detailUrl = get_permalink($this->meta['detail_page']);
		else
			$this->detailUrl = $this->id ? get_permalink($this->id) : '';

		// hiding of UI elements
		if (isset(MWS()->setting['eshop_hide']['comments'])) {
			$this->hideComments = true;
		} else {
			$this->hideComments = (isset($this->meta['hide_comments'])) ? true : false;
		}
		if (isset(MWS()->setting['eshop_hide']['social'])) {
			$this->showSocial = false;
		} else {
			$this->showSocial = true;
		}
		if (isset(MWS()->setting['eshop_hide']['similar_products'])) {
			$this->showSimilar = false;
		} else {
			$this->showSimilar = (isset($this->meta['show_similar_products'])) ? true : false;
		}

		// hiding product from product listings
		$this->hideInListings = isset($this->meta['hide_in_listings']) && $this->meta['hide_in_listings'];

		// Sell restriction
		$nowMidnightLocal = MwsProduct::$nowMidnightLocal;
		$this->_sellRestriction = MwsSellRestriction::None; //default
		if (isset($this->meta['selling_restrict']) && (bool)$this->meta['selling_restrict']) {
			$this->_sellRestriction = MwsSellRestriction::checkedValue($this->meta['selling_restrict_type'], MwsSellRestriction::None);
			switch ($this->_sellRestriction) {
				case MwsSellRestriction::FullDisable:
					break;
				case MwsSellRestriction::EnabledFrom:
					$this->sellEnabledFrom = mwExtractDateTimeFromField(
						isset($this->meta['selling_enabled_from']) ? $this->meta['selling_enabled_from'] : array(),
						$nowMidnightLocal
					);
					break;
				case MwsSellRestriction::EnabledTill:
					$this->sellEnabledTill = mwExtractDateTimeFromField(
						isset($this->meta['selling_enabled_till']) ? $this->meta['selling_enabled_till'] : array(),
						$nowMidnightLocal
					);
					break;
				case MwsSellRestriction::EnabledInterval:
					$this->sellEnabledFrom = mwExtractDateTimeFromField(
						isset($this->meta['selling_enabled_from']) ? $this->meta['selling_enabled_from'] : array(),
						$nowMidnightLocal
					);
					$this->sellEnabledTill = mwExtractDateTimeFromField(
						isset($this->meta['selling_enabled_till']) ? $this->meta['selling_enabled_till'] : array(),
						$nowMidnightLocal
					);
					break;
				default:
					$this->_sellRestriction = MwsSellRestriction::None;
					break;
			}
		}

		switch ($this->structure) {
			case MwsProductStructureType::Single:
				$this->loadProperties_Single();
				break;
			case MwsProductStructureType::Variants:
				$this->loadProperties_Variant();
				break;
		}
	}

	private function loadProperties_Variant() {
		// Stock enabled
		$this->_stockEnabled = isset($this->meta['variant_stock_enabled']) ? (bool)$this->meta['variant_stock_enabled'] : false;
		$this->_stockAllowBackorders = $this->_stockEnabled && isset($this->meta['variant_stock_allow_backorders'])
			? (bool)$this->meta['variant_stock_allow_backorders'] : false;

	}

	private function loadProperties_Single() {
		$nowMidnightLocal = MwsProduct::$nowMidnightLocal;

		// Stock enabled
		$this->_stockEnabled = isset($this->meta['stock_enabled']) ? (bool)$this->meta['stock_enabled'] : false;
		$this->_stockAllowBackorders = $this->_stockEnabled && isset($this->meta['stock_allow_backorders'])
			? (bool)$this->meta['stock_allow_backorders'] : false;

		// Price full
		$this->_priceFull = new MwsPrice(
			isset($this->meta['price']['size']) ? (float)$this->meta['price']['size'] : 0,
			isset($this->meta['vat_id']) ? (float)$this->meta['vat_id'] : null
		);

		// Sale price type
		$this->_salePriceType = MwsSalePriceType::None; //default
		if (isset($this->meta['price_sale_enabled']) && (bool)$this->meta['price_sale_enabled']) {
			$this->_salePriceType = MwsSalePriceType::checkedValue($this->meta['price_sale_type'], MwsSalePriceType::None);
			switch ($this->_salePriceType) {
				case MwsSalePriceType::Continuous:
					break;
				case MwsSalePriceType::EnabledFrom:
					$this->salePriceEnabledFrom = mwExtractDateTimeFromField(
						isset($this->meta['price_sale_enabled_from']) ? $this->meta['price_sale_enabled_from'] : array(),
						$nowMidnightLocal
					);
					break;
				case MwsSalePriceType::EnabledTill:
					$this->salePriceEnabledTill = mwExtractDateTimeFromField(
						isset($this->meta['price_sale_enabled_till']) ? $this->meta['price_sale_enabled_till'] : array(),
						$nowMidnightLocal
					);
					break;
				case MwsSalePriceType::EnabledInterval:
					$this->salePriceEnabledFrom = mwExtractDateTimeFromField(
						isset($this->meta['price_sale_enabled_from']) ? $this->meta['price_sale_enabled_from'] : array(),
						$nowMidnightLocal
					);
					$this->salePriceEnabledTill = mwExtractDateTimeFromField(
						isset($this->meta['price_sale_enabled_till']) ? $this->meta['price_sale_enabled_till'] : array(),
						$nowMidnightLocal
					);
					break;
				default:
					$this->_salePriceType = MwsSalePriceType::None;
					break;
			}
		}

		// Price -- considering discounting
		$this->_isDiscountedNow = isset($this->meta['price_sale']['size'])
			&& (!empty($this->meta['price_sale']['size']) || $this->meta['price_sale']['size']==='0')
			&& $this->canDiscountNow();
		if($this->_isDiscountedNow)
		{
			//Sale price activated
			$this->_price = new MwsPrice(
				isset($this->meta['price_sale']['size']) ? (float)$this->meta['price_sale']['size'] : 0,
				isset($this->meta['vat_id']) ? (float)$this->meta['vat_id'] : null
			);
		} else {
			//Ordinary price activated
			$this->_price = $this->_priceFull;
		}
	}

	/**
	 * Create a new variant of a product.
	 * @param MwsProduct $product   Superior product where the variant belongs to
	 * @param array $properties     Array key-ed by {@link MwsProperty::id} value-ed by {@link MwsPropertyValue::id}.
	 * @param float $price
	 * @param $priceSale
	 * @param int|false $stockCount New count of stock items. If false is passed then stock is not updated.
	 * @param $codes
	 * @return MwsProductVariant
	 */
/*	public static function createVariant($product, $properties, $price, $priceSale, $stockCount, $codes) {
		$new = new MwsProductVariant(null);
		$new->product = $product;
		if($new->updateVariant($properties, $price, $priceSale, $stockCount, $codes)) {
			return $new;
		} else {
			return null;
		}
	}*/

	/**
	 * @param $properties
	 * @param $price
	 * @param int|false $stockCount New count of stock items. If false is passed then stock is not updated.
	 * @return int Id of a variant. For update the value of {@link id} persists. For new save this is new ID.
	 *             For error 0 is returned and message is logged.
	 */
/*	public function updateVariant($properties, $price, $stockCount) {
		// Set properties
		$errorIds = array();
		$parsedProps = array();
		if(is_array($properties)) {
			foreach ($properties as $propId => $propVal) {
				$instProperty = MwsProperty::getById($propId);
				if($instProperty) {
					$instPropValue = $instProperty->getValue($propVal);
					if($instPropValue) {
						$parsedProps[] = $instPropValue;
					} else {
						$errorIds[$propId] = sprintf(__('Parametr produktu "%s" nemá zavedenou hodnotu [%s].', 'mwshop'),
							(string)$propId, (string)$propVal);
					}
				} else {
					$errorIds[$propId] = sprintf(__('Parametr produktu [%s] neexistuje.', 'mwshop'), (string)$propId);
				}
			}
		}
		if(!empty($errorIds)) {
			// Critical errors in properties. Interrupt processing.
			$errorMsg = ''; //sprintf(__('Variantu produktu "%s" se nepodařilo vytvořit. Sada parametrů obsahuje chyby.', 'mwshop'), $this->product->name);
			foreach ($errorIds as $errorId => $errorText) {
				$errorMsg .= "\n" . $errorText;
			}
			mwshoplog($errorMsg,
				MWLL_WARNING, 'variant');
			return 0;
		}
		$this->__set('variantVals', $parsedProps);

		// Set price
		$vatId = $this->product->priceFull->vatId;
		$this->_priceFull = new MwsPrice($price, $vatId);
		$this->_price = $this->priceFull;

		// Save
		try {
			$newId = $this->save();

			if($newId) {
				//Update stock count
				if ($stockCount !== false && $this->product->stockEnabled && $this->stockCount != $stockCount)
					$this->updateStockCount($stockCount, MwsStockUpdate::Set);
			}
		} catch (Exception $e) {
			$newId = 0;
		}

		return $newId;
	}*/

/*	private function getPostArray($includeMeta = true) {
		$postArr = array();
		if($this->id)
			$postArr['ID'] = $this->id;
		$postArr['post_title'] = $this->product->name;
		$postArr['post_status'] = 'publish';
		$postArr['post_parent'] = $this->product->id;
		$postArr['post_type'] = MWS_VARIANT_SLUG;
		$postArr['comment_status'] = !(bool)$this->product->hideComments;

		$meta = $this->loadMeta();
		$meta['structure'] = $this->structure;
		$meta['variant_values'] = MwsPropertyValue::serializeArray($this->variantVals);
		$meta['price']['size'] = $this->priceFull->priceStored;
		$meta['vat_id'] = $this->priceFull->vatId;

		$postArr['meta_input'] = array(
			MWS_PRODUCT_META_KEY => $meta,
		);
		return $postArr;
	}*/

	/**
	 * Save instance of a product variant. Saving uses properties relevant to variants.
	 * @return int Current or new post id = variant id. Failure raises exception.
	 * @throws MwsException
	 */
/*	public function save() {
		$this->loadMeta();
		$postArr = $this->getPostArray();
		cms_save_disable();
		$newId = wp_insert_post($postArr);
		cms_save_enable();
		if($newId && $newId != $this->id) {
			// Saved successfully
			$this->post = static::getById($newId);
		}
		return $newId;
	}*/

	/**
	 * Get all defined variant children of the product.
	 * @param array $queryArgs Optional parameters for {@link WP_Query}.
	 * @return array Array of {@link MwsProductVariant}
	 */
	public function getVariants($queryArgs = array('post_status' => 'publish')) {
		$args = array_merge(
			array(
				'post_type' => MWS_VARIANT_SLUG,
				'post_parent' => $this->id,
			), //a must
			$queryArgs,	//user customization
			array('posts_per_page' => -1) //default values
		);
		$qry = new WP_Query($args);
		$res = array();
		if($qry->have_posts()) {
			foreach ($qry->posts as $post) {
				$variant = MwsProductVariant::createNew($post);
				if($variant) {
					$res[] = $variant;
				}
			}
		}
		return $res;
	}

	/**
	 * Update variant prices. As a result a price/saleprice for VARIANT definition is set to the cheapest price from
	 * available variants.
	 * @param bool $excludeHiddenVariants If hidden variants should be not taken into account.
	 */
	private function loadVariantPrices($excludeHiddenVariants) {
		// Protect against double loading when no price has been found.
		if(isset($this->_variantPricesLoaded))
			return;
		$this->_variantPricesLoaded = true;

		$variants = $this->variants;
		$priceMin = false;
		$found = null;
		$priceEq = false;
		$pricesAreEqual = true;
		/** @var MwsProductVariant $variant */
		foreach ($variants as $variant) {
			if($excludeHiddenVariants) {
				$isVisible = $variant->isVisible();
				if(!$isVisible) {
					continue;
				}
			}
			$curPrice = $variant->price->priceVatIncluded;
			if($priceMin === false || $curPrice < $priceMin) {
				$priceMin = $curPrice;
				$found = $variant;
			}
			if($priceEq === false) {
				$priceEq = $curPrice;
			} elseif ($curPrice != $priceEq) {
				$pricesAreEqual = false;
			}
		}
		if($found) {
			$this->_price = $found->price;
			$this->_priceFull = $found->priceFull;
		}
		$this->__set('variantPricesAreEqual', $pricesAreEqual);
	}

	protected function getStockCount() {
		if($this->_isVariant) {
			$cnt = 0;
			$variants = $this->variants;
			/** @var MwsProductVariant $variant */
			foreach ($variants as $variant) {
				$curCnt = $variant->stockCount;
				if($curCnt > 0) {
					// Negative values mean backorders for a variant. Do not count them.
					$cnt += $curCnt;
				}
			}
		} else {
			$cnt = parent::getStockCount();
		}
		return $cnt;
	}

	public function getBuyButtonText($availabilityStatus = 0) {
		if($availabilityStatus===0)
			$availabilityStatus = $this->getAvailabilityStatus(1);
//		if($this->_isVariant && $this->canBuy($availabilityStatus)) {
//			$res = _x('Zvolit variantu', 'Buy buttontext - choose variant of product to be bought', 'mwshop');
//		} else {
			$res = parent::getBuyButtonText($availabilityStatus);
//		}
		return $res;
	}


}