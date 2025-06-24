<?php

/**
 * Class and routines for shipping methods.
 * User: kuba
 * Date: 04.04.16
 * Time: 12:49
 */


/** Name of the meta key of product. */
define('MWS_SHIPPING_META_KEY', 'shipping');
/** Pseudo identifier for electronic shipping method. */
define('MWS_SHIPPING_ID_ELECTRONIC', -1);

/**
 * Class MwsShipping is used to wrap shipping methods. To get a shipping method, use the {@link MwsShipping:createNew}
 * constructor, which uses caching. Or create new instance directly if caching is not necessary.
 * @property MwsPrice $price Fixed price of the shipping.
 * @property bool isPersonalPickup If delivery method is a personal pickup by the customer in a shop physical location.
 * @property bool isCodSupported If cash on delivery is supported.
 * @property MwsPrice $codPrice Optional additional price when cash on delivery payment is used.
 * @property MwsSync_Shipping sync Data for synchronization.
 * @property int id ID of post.
 * @property string name Name of post.
 *
 * @property MwsProductCodes $codes List of extended codes
 *
 */
class MwsShipping {
  /** @var WP_Post Post object. */
  public $post;
  /** @var null|array Settings of instance. */
  public $meta=null;
  /** @var MwsPrice Internal storage of the price. */
  protected $_price;
  protected $_codPrice;
  private $_sync;
	/** @var MwsProductCodes List of codes. Lazy loaded. */
	protected $_codes = null;

	/**
   * Creates new instance of shipping method. If shipping of the same ID is already loaded then that instance is used from
   * cache.
   * @param $post WP_Post Instance of post with custom-post-type {@link MWS_SHIPPING_SLUG}.
	 * @param bool $forceUpdateCache When set to true then possibly precached instance will not be used but will be
	 *                               updated by the newly created instance.
	 * @return MwsShipping
   * @throws MwsException If passed post is not of shipping class.
   */
  public static function createNew($post, $forceUpdateCache = false) {
    if(get_post_type($post) != MWS_SHIPPING_SLUG) {
			throw new MwsException('Passed post is not of shipping type.');
		}

		if ($post->post_status === 'auto-draft') {
			// Newly created post
			mwshoplog('Newly created unsaved SHIPPING post: ' . json_encode($post, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG);
			return null;
		}

		//Is created already or must be updated in cache?
		$className = 'MwsShipping';
		if ($forceUpdateCache || !($obj = MwsObjectCache::get($className, $post->ID))) {
			$obj = new $className($post);
			MwsObjectCache::add($obj, $obj->id);
		} else {
		}
		return $obj;
  }

  /**
   * Get all defined shipping methods as an array of {@link MwsShipping} instances.
   * @return array List of {@link MwsShipping} instances.
   */
  public static function getAll() {
    $res = array();
    $args = array(
      'post_type' => MWS_SHIPPING_SLUG,
      'post_status' => 'publish',
			'posts_per_page' => -1,
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
      /** @var WP_Post $post */
      foreach ($query->posts as $post) {
        try {
          $res[] = MwsShipping::createNew($post);
        } catch (Exception $e) {
        }
      }
    }
    return $res;
  }

	/**
	 * Get shipping instance by shipping ID.
	 * @param int $shippingId
	 * @param bool $forceRecache
	 * @return MwsShipping|object Existing shipping or null
	 */
	public static function getById($shippingId, $forceRecache = false) {
		if ($shippingId === MwsShippingElectronic::id) {
			return MwsShippingElectronic::getInstance();
		} else if ($shippingId) {
			$post = get_post($shippingId);
			if ($post)
				try {
					return static::createNew($post, $forceRecache);
				} catch (MwsException $e) {
					mwshoplog(sprintf(__('Nepodařilo se vytvořit instanci způsobu doručení [%d] se zprávou: %s', 'mwshop'), $shippingId, $e->getMessage()),
						MWLL_ERROR);
				}
		}
		return null;
	}

	/**
   * Generate HTML select element with shipping methods.
   * @param int $id Id of shipping = post ID
   * @param string $name     Name of the HTML element.
   * @param string $selected Value of selected element.
   * @param string $css      Optional CSS class
   * @param string $notSelectedText When not empty then first item with passed text and zero value is added to the beginning.
   * @return string HTML text
   */
  public static function htmlSelect($id, $name, $selected = '', $css = '', $notSelectedText = '') {
    $shippings = static::getAll();
    if(empty($shippings))
      if (MWS()->edit_mode)
        echo '<div class="cms_error_box">' . __('Není definován žádný způsob doručení.', 'mwshop')
          . ' <a target="_blank" href="' . admin_url('post-new.php?post_type='.MWS_SHIPPING_SLUG) . '">'
          . __('Vytvořit způsob doručení', 'mwshop')
          . '</a></div>';
      else
        echo '<div class="cms_error_box">' . __('Není definován žádný způsob doručení, objednávku nelze dokončit.', 'mwshop') . '</div>';

    $res = '<select id="'.$id.'" name="'.$name.'"' .(empty($css) ? '' : ' class="'.$css.'"')
      . '>';
    if(!empty($notSelectedText)) {
      $res .= '<option value="0" '. ($selected == 0 ? ' selected="selected"' : '') . '>'
        . $notSelectedText
        . '</option>';
    }
    $unit = MWS()->getCurrency();
    /** @var MwsShipping $shipping */
    foreach ($shippings as $shipping) {
      $title = esc_html(get_the_title($shipping->post));
      $priceHtml =
        $shipping->price->htmlPriceVatIncluded($unit, 1, true, 'mws_shipping_price')
//        . ($shipping->codPrice->priceVatIncluded > 0
//          ? ' / +' . $shipping->codPrice->htmlPriceVatIncluded($unit, 1, false, 'mws_shipping_price_cod')
//          : ''
//        )
      ;
      $res .= '<option value="'.$shipping->id.'" '. ($selected == $shipping->id ? ' selected="selected"' : '')
        . ' class="'
          . ($shipping->isCodSupported ? 'mws_cod_enabled' : '')
          . ($shipping->isPersonalPickup ? ' mws_personal_pickup' : '')
        .'"'
        . '>'
        . $title . ($priceHtml ? ' ['.$priceHtml.']' : '')
        . '</option>';
    }
    $res .= '</select>';
    return $res;
  }

  function __construct($post) {
    $this->post = $post;
    $this->loadMeta();

    // Properties
    $this->_price = new MwsPrice(
      isset($this->meta['price']) ? (float)$this->meta['price'] : 0,
      isset($this->meta['vat_id']) ? (float)$this->meta['vat_id'] : null
    );
    if($this->isCodSupported) {
      $this->_codPrice = new MwsPrice(
        isset($this->meta['cod_price']) ? (float)$this->meta['cod_price'] : 0,
        isset($this->meta['vat_id']) ? (float)$this->meta['vat_id'] : null
      );
    } else {
      $this->_codPrice = new MwsPrice(0, null);
    }
  }

  /** Load metadata of the product. Uses cached data if present. */
  public function loadMeta() {
    if(is_null($this->meta)) {
      $meta = get_post_meta($this->post->ID, MWS_SHIPPING_META_KEY);
      if (isset($meta[0]))
        $this->meta = $meta[0];
    }
    return $this->meta;
  }

  function __get($name) {
    if ($name == 'price') {
      return $this->_price;
    } elseif ($name == 'codPrice') {
      return $this->_codPrice;
    } elseif ($name ==='isCodSupported') {
      return isset($this->meta['cod_enabled']) && $this->meta['cod_enabled'];
    } elseif ($name ==='isPersonalPickup') {
      return isset($this->meta['personal_pickup']) && $this->meta['personal_pickup'];
    } elseif ($name === 'sync') {
      if(is_null($this->_sync))
        $this->_sync = new MwsSync_Shipping($this->post->ID, $this);
      return $this->_sync;
    } elseif ($name == 'id') {
      return $this->post->ID;
    } elseif ($name == 'name') {
      return $this->post->post_title;
		} elseif ($name === 'codes') {
			if (is_null($this->_codes)) {
				$this->_codes = $this->loadCodes();
			}
			return $this->_codes;
		}
    return null;
  }

  /**
   * Count total price according to selected $payType.
   * @param $payType MwsPayType Selected type of payment.
   * @return MwsPrice
   */
  public function getTotalPrice($payType) {
    $payType = MwsPayType::checkedValue($payType, null);
    if(is_null($payType))
      return new MwsPrice(0);
    else
      return new MwsPrice(
        $this->price->priceStored + ($payType===MwsPayType::Cod ? $this->codPrice->priceStored : 0),
        $this->price->vatId
      );
  }

	/**
	 * Load extended codes.
	 * @return MwsProductCodes New instance of codes.
	 */
	protected function loadCodes() {
		$meta = $this->loadMeta();
		$codes = $meta && $meta['codes'] ? $meta['codes'] : array();
		return new MwsProductCodes($codes);
	}

}

final class MwsShippingElectronic extends MwsShipping {
	const id = MWS_SHIPPING_ID_ELECTRONIC;
	static private $_instance = null;

	public static function getById($shippingId, $forceRecache = false) {
		return parent::getById($shippingId, $forceRecache); // TODO: Change the autogenerated stub
	}


	public function __construct() {
		$post = new WP_Post(new stdClass());
		$post->ID = -1;
		$post->post_title = __('Elektronicky', 'mwshop');
		$post->post_type = MWS_SHIPPING_SLUG;
		$post->post_excerpt = __('Zboží vám bude zasláno elektronicky na váš email.', 'mwshop');

		$this->meta = array(
			'price' => 0,
			'cod_price' => 0,
			'cod_enabled' => false,
			'personal_pickup' => false,
		);
		parent::__construct($post);
	}

	public static function getInstance() {
		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}

}

class MwsSync_Shipping extends MwsSync {

  protected function doGetHashValuesArray() {
		/** @var MwsShipping $shipping */
		$shipping = $this->_parent;
		return array(
			$shipping->id, $shipping->name,
			$shipping->price->priceVatIncluded, $shipping->price->priceVatExcluded, $shipping->price->getVatPercentage(),
			$shipping->codPrice->priceVatIncluded, $shipping->codPrice->priceVatExcluded, $shipping->codPrice->getVatPercentage(),
			serialize(MWS()->getCurrencyConversionTable()),
			serialize($shipping->codes->toArray())
		);
	}
}
