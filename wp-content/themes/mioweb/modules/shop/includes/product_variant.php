<?php
/**
 * Product variations
 * User: kuba
 * Date: 29.08.16
 * Time: 16:32
 */


/**
 * Class MwsProductVariant
 *
 * @property int $productId Id or parent product.
 * @property MwsProduct $product Instance of a parenting product.
 * @property array $variantVals List of variant values {@link MwsPropertyValue} defining variation.
 */
class MwsProductVariant extends MwsProduct {
	private $_variantVals;
	/** @var MwsPrice Effective price. Depends on sale-price settings if full or sale price is here. */
	private $_price;
	/** @var bool */
	private $_isDiscountedNow;
	/** @var MwsPrice Full price without discount. */
	private $_priceFull;
	/** @var MwsSalePriceType */
	private $_salePriceType;

	public function __construct($post) {
		parent::__construct($post);

		$this->structure = MwsProductStructureType::OneVariant;
	}

	function __get($name) {
		if ($name === 'priceFull') {
			return $this->_priceFull;
		} elseif ($name === 'price') {
			return $this->_price;
		} elseif($name === 'variantVals') {
			if(is_null($this->_variantVals)) {
				$this->loadMeta();
				$this->_variantVals = isset($this->meta['variant_values'])
					? MwsPropertyValue::unserializeArray($this->meta['variant_values'])
					: array();
			}
			return $this->_variantVals;
		} elseif($name === 'product') {
			$product = null;
			if($this->post) {
				$parentId = $this->post->post_parent;
				if($parentId) {
					$product = MwsProductRoot::getById($parentId);
				}
			}
			$this->product = $product;
			return $product;
		} elseif($name === 'isDiscountedNow') {
			return $this->_isDiscountedNow;
		} elseif($name === 'stockEnabled') {
			return $this->product->stockEnabled;
		} elseif($name === 'stockAllowBackorders') {
			return $this->product->stockAllowBackorders;
		} elseif(in_array($name, array(
			'type',
			'detailUrl',
			'hideInListings',
//			'hideComments',

			'vatId',

			'sellRestriction',
			'sellEnabledFrom',
			'sellEnabledTill',

			'salePriceType',
			'salePriceEnabledFrom',
			'salePriceEnabledTill',

			'gallery',
			'properties',
		))) {
			// Inherited from parent product
			$product = $this->product;
			if($product) {
				return $this->product->__get($name);
			} else {
				throw new MwsException('Parent product of a variant is not defined. [variantId='.$this->id.']');
			}
		} else {
			// Inherited from ancestor implementation -- shared or already defined properties
			return parent::__get($name);
		}
	}

	function __set($name, $value) {
		if($name === 'variantVals') {
			if(!is_array($value)) {
				throw new MwsException('Value for property "variantVals" must be an array.');
			}
			$this->_variantVals = $value;
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
	 * @return MwsProductVariant
	 * @throws MwsException If passed post is not of product post type.
	 */
	public static function createNew($post, $forceUpdateCache = false) {
		$res = parent::createNew($post, $forceUpdateCache);
		return $res;
	}

	/**
	 * Get product variant instance by variant ID.
	 * @param int $variantId
	 * @param bool $forceRecache
	 * @return MwsProductVariant Existing variant of product or null
	 */
	public static function getById($variantId, $forceRecache = false) {
		$res = parent::getById($variantId, $forceRecache);
		return $res;
	}

	protected function loadProperties_Derived() {
		// Price full
		if(isset($this->meta['price']['size'])) {
			// Backward compatibility, when price in variant was saved within subaray.
			$priceFull = (float)$this->meta['price']['size'];
		} else {
			$priceFull = isset($this->meta['price']) ? (float)$this->meta['price'] : 0;
		}
		$vatId = null;
		if (!is_null($this->product)) {
			$vatId = $this->product->vatId;
		}
		$this->_priceFull = new MwsPrice(
			$priceFull,
			$vatId
		);

		// Price -- considering discounting
		$this->_isDiscountedNow = isset($this->meta['price_sale'])
			&& (!empty($this->meta['price_sale']) || is_numeric($this->meta['price_sale']))
//			&& $this->canDiscountNow()
		;
		if($this->_isDiscountedNow)
		{
			//Sale price activated
			$this->_price = new MwsPrice(
				isset($this->meta['price_sale']) ? (float)$this->meta['price_sale'] : 0,
				$vatId
			);
			// Sale price type
			$this->_salePriceType = MwsSalePriceType::Continuous;
		} else {
			//Ordinary price activated
			$this->_price = $this->_priceFull;
			// Sale price type
			$this->_salePriceType = MwsSalePriceType::None;
		}

	}

	/**
	 * Create a new variant of a product.
	 * @param MwsProduct $product   Superior product where the variant belongs to
	 * @param array $properties     Array key-ed by {@link MwsProperty::id} value-ed by {@link MwsPropertyValue::id}.
	 * @param float $price
	 * @param $priceSale
	 * @param int|false $stockCount New count of stock items. If false is passed then stock is not updated.
	 * @param array $codes List of codes and values.
	 * @return MwsProductVariant
	 */
	public static function createVariant($product, $properties, $price, $priceSale, $stockCount, $codes) {
		$new = new MwsProductVariant(null);
		$new->product = $product;
		if($new->updateVariant($properties, $price, $priceSale, $stockCount, $codes)) {
			return $new;
		} else {
			return null;
		}
	}

	/**
	 * @param $properties
	 * @param float $price
	 * @param float $priceSale
	 * @param int|false $stockCount New count of stock items. If false is passed then stock is not updated.
	 * @param array $codes List of codes
	 * @return int Id of a variant. For update the value of <a href='psi_element://id'>id</a> persists. For new save this is new ID.
	 *                              persists. For new save this is new ID.
	 * @throws MwsException Raised when validation of parameters fails. Error message is localized and can be used in UI.
	 */
	public function updateVariant($properties, $price, $priceSale, $stockCount, $codes) {
		// Set properties
		$errorIds = array();
		$parsedProps = array();
		if(is_array($properties)) {
			foreach ($properties as $propId => $propVal) {
				$instProperty = MwsProperty::getById($propId);
				if($instProperty) {
					if(empty($propVal) && $propVal !== '0') {
						$errorIds[$propId] = sprintf(__('Hodnota parametru "%s" je prázdná.', 'mwshop'), $instProperty->name);
					} else {
						$instPropValue = $instProperty->getValue($propVal, true);
						if ($instPropValue) {
							$parsedProps[] = $instPropValue;
						} else {
							$errorIds[$propId] = sprintf(__('Hodnota "%s" parametru "%s" není platná.', 'mwshop'),
								(string)$propVal, $instProperty->name);
						}
					}
				} else {
					$errorIds[$propId] = sprintf(__('Parametr [%s] neexistuje.', 'mwshop'), (string)$propId);
				}
			}
		}
		if(!empty($errorIds)) {
			// Critical errors in properties. Interrupt processing.
			$errorMsg = implode("\n", $errorIds);
			throw new MwsException($errorMsg);
		}

		$this->__set('variantVals', $parsedProps);

		// Set price
		$parentPrice = $this->product->priceFull;
		$vatId = is_null($parentPrice) ? null : $parentPrice->vatId;
		$this->_priceFull = new MwsPrice($price, $vatId);
		$this->_isDiscountedNow = (!empty($priceSale) || is_numeric($priceSale));
		if($this->_isDiscountedNow) {
			$this->_price = new MwsPrice((float)$priceSale, $vatId);
		} else {
			$this->_price = $this->_priceFull;
		}
		// Codes
		$this->_codes = new MwsProductCodes($codes);

		// Save
		try {
			$newId = $this->save();
			// Saving successful?
			if($newId) {
				//Update stock count
				if ($stockCount !== false && $this->stockEnabled && $this->stockCount != $stockCount) {
					$this->updateStockCount($stockCount, MwsStockUpdate::Set);
				}
			}
		} catch (Exception $e) {
			$newId = 0;
		}

		return $newId;
	}

	private function getPostArray($includeMeta = true) {
		$postArr = array();
		if($this->id)
			$postArr['ID'] = $this->id;
		$varDesc = $this->composeVariantDesc();
		$postArr['post_title'] = $this->product->name . ($varDesc ? ' - ' . $varDesc : '');
		$postArr['post_status'] = 'publish';
		$postArr['post_parent'] = $this->product->id;
		$postArr['post_type'] = MWS_VARIANT_SLUG;
		$postArr['comment_status'] = !(bool)$this->product->hideComments;

		$meta = $this->loadMeta();
		$meta['structure'] = $this->structure;
		$meta['variant_values'] = MwsPropertyValue::serializeArray($this->variantVals);
		$priceFull = $this->priceFull;
		if ($priceFull) {
			$vatId = $priceFull->vatId;
			$meta['vat_id'] = $vatId;
			$meta['price'] = $priceFull->priceStored;
			if($this->_isDiscountedNow) {
				$meta['price_sale'] = $this->price->priceStored;
			} else {
				unset($meta['price_sale']);
			}
		}
		$meta['codes'] = $this->codes->toArray();

		$postArr['meta_input'] = array(
			MWS_PRODUCT_META_KEY => $meta,
		);
		return $postArr;
	}

	/**
	 * Save instance of a product variant. Saving uses properties relevant to variants.
	 * @return int Current or new post id = variant id. Failure raises exception.
	 * @throws MwsException
	 */
	public function save() {
		$this->loadMeta();
		$postArr = $this->getPostArray();
		cms_save_disable();
		$newId = wp_insert_post($postArr);
		cms_save_enable();
		if($newId) {
			// Saved successfully. Refresh post content.
			$this->post = get_post($newId);
		}
		return $newId;
	}

	/**
	 * Compose text description of variant. That is list of comma-separated values of each variant parameter.
	 * Name of variant parameter and od variant parameter unit can be included optionally.
	 * @param bool $includeParameterName Include name of parameter before its value.
	 * @param bool $includeUnit          Include unit of parameter after its value.
	 * @param string $glue               Textual glue to join each parameter string into final string.
	 * @param bool $includeEmptyValues Include parameters with empty values.
	 * @return string
	 */
	public function composeVariantDesc($includeParameterName = false, $includeUnit = true, $glue = ', ', $includeEmptyValues = false) {
		$arr = array();
		/** @var MwsPropertyValue $variantVal */
		foreach ($this->variantVals as $variantVal) {     
			$item = $variantVal->name;
			if(empty($item) && $item != '0') {
				if(!$includeEmptyValues) {
					continue;
				}
				$item = '-';
			}
			if($includeUnit) {
				$unit = ($variantVal->propertyDef->unit);
				if (!empty($unit)) {
					$item .= ' ' . $unit;
				}
			}
			if($includeParameterName) {
				$item = $variantVal->propertyDef->name . ' ' . $item;
			}
			$arr[] = $item;
		}
		$res = implode($glue, $arr);
		return $res;
	}

	public function getThumbnail($size = 'post-thumbnail', $attr = '') {
		$res = parent::getThumbnail($size, $attr);
		if(empty($res)) {
			$res = $this->product->getThumbnail($size, $attr);
		}
		return $res;
	}
	public function getThumbnailUrl($size = 'post-thumbnail') {
		$res = parent::getThumbnailUrl($size);
		if(empty($res)) {
			$res = $this->product->getThumbnailUrl($size);
		}
		return $res;
	}

	/**
	 * Gets if the variant should be visible in catalog or not. Variant is visible, when parenting product is visible,
	 * variant's availability is OK or visibility setting for unavailable variants is set to "show unavailable variants".
	 * @param int $availabilityStatus
	 * @return bool
	 */
	public function isVisible($availabilityStatus = 0) {
		if(!is_null($this->product)) {
			$res = $this->product->isVisible();
		} else {
			$res = true;
		}
		if($res) {
			static $hiddenVariantAvailabilityStatuses;
			if(is_null($hiddenVariantAvailabilityStatuses)) {
				$hiddenVariantAvailabilityStatuses = MWS()->getHiddenAvailabilityStatusesFor('variant');
			}
			if($availabilityStatus === 0) {
				$availabilityStatus = $this->getAvailabilityStatus();
			}
			$res = !in_array($availabilityStatus, $hiddenVariantAvailabilityStatuses);
		}
		return $res;
	}


}
