<?php

/**
 * Clas of the order.
 *
 * User: kuba
 * Date: 27.04.16
 * Time: 18:56
 */

/** Name of the meta key of product. */
define('MWS_ORDER_META_KEY', MWS_OPTION.'order');
define('MWS_ORDER_META_KEY_ORDERNUM', MWS_OPTION.'order_num');
define('MWS_ORDER_META_KEY_CUSTOMERNOTE', MWS_OPTION.'order_customernote');


/**
 * Encapsulates one order of MioShop - internally stored as WP custom post type {@link MWS_ORDER_SLUG}.
 * Contains ordering data: invoice address, optional shipping address, items with links to products (subset
 * of product data is stored within the item for case the product is not available anymore) and prices, total prices,
 * chosen shipping method, customer's note, result of payment gateway.
 * More to that contains status of the order, flag if fully paid.
 * Additional attachments can be present, like PDFs of invoice, proforma invoice, return payment, modification of
 * invoice - all stored as WP attachments.
 *
 * @class MwsOrder
 * @property-read int $id ID of the post.
 * @property-read string $title Title of the order.
 * @property MwsOrderStatus $status Status of the order.
 * @property string $orderNum ID of the order. This ID is sourced from the gateway at the moment when the order is
 *		successfully submitted to the gateway.
 * @property bool $isPaid True when order is fully paid.
 * @property int $paidOn Unix time epoch in GMT when order has been paid.
 * @property string $gateId ID of the gateway that has been used to fulfill the order.
 * @property string $customerNote Message from the customer to the shop sent within an order.
 * @property mixed $gateOrderData Data of the order from the gateway. This is used to find corresponding item within
 *		gate.
 * @property string $urlDirectPay URL for direct payments. Can be empty if direct payments are not accessible.
 * @property MwsOrderGate $gateLive Live connector to order at the gateway side. Use this to get realtime
 * 		information from the gateway. Data is automatically loaded.
 * @property MwsOrderItems $items
 * @property array $rawItems Value of $items property as stored in orders's meta.
 */
class MwsOrder {
//	/** @var bool Is <code>true</code> when fully paid. */
//	private $_isPaid = false;
//	/** @var MwsOrderStatus Status of the order according to processing. */
//	private $_status;

	/** @var WP_Post Post object. */
	public $post;
	/** @var array Loaded metadata. */
	public $meta = null;
	/** @var MwsOrderGate Link to order data stored at the gate side. */
	private $_gateLive;
	/** @var MwsOrderItems */
	private $_orderItems;

//	/** @var string ID of gate used for ordering. Empty if no gate has been used. */
//	private $_gateIdUsed;
//	/** @var mixed Data of gate as a result of successful ordering.  */
//	private $_gateOrderRes;
//	/** @var mixed Data of gate as a result of successful payment. */
//	private $_gatePaymentInfo;


//	/** @var MwsCartItems Items of the cart.  */
//	private $_items;
//	/** @var array Invoice address. */
//	private $_contact;
//	/** @var array Shipping address. */
//	private $_shipping;
//	/** @var MwsPayType Payment data from order step. */
//	private $_payment;
//	/** @var MwsPrice Calculated price of shipping. */
//	private $_shippingPrice;
//	/** @var MwsPrice Total price of the order. */
//	private $_totalPrice;
//	/** @var array Total price of different VAT levels. */
//	private $_totalVatPrices;


	/**
	 * @param null|WP_Post $post
	 * @param null|string $numOrder ID of new order, typically a number.
	 * @throws MwsException
	 */
	function __construct($post=null, $numOrder=null) {
//		mwshoplog('MwsOrder -- creating new instance', MWLL_DEBUG);
		if(!empty($post))
			// Existing order
			$this->post = get_post($post);
		else {
			// For creation of new order
			$this->post = null;
			if(empty($numOrder))
				throw new MwsException('Field [numOrder] need value.');
			$this->orderNum = (string)$numOrder;
			$this->status = MwsOrderStatus::Ordered;
		}

	}

	/**
	 * Creates new instance of object. If instance of the same ID is already loaded then that instance is used from
	 * cache.
	 * @param $post WP_Post Instance of post with custom-post-type {@link MWS_ORDER_SLUG}.
	 * @return MwsOrder
	 * @throws MwsException If passed post is not of order post type.
	 */
	public static function createNew($post) {
		if(get_post_type($post) != MWS_ORDER_SLUG)
			throw new MwsException('Passed post is not of order type.');
		//Is created already?
		$obj = MwsObjectCache::get(get_class(), $post->ID);
		if(!$obj) {
//			mwdbg('MwsOrder -- not in cache');
			$obj = new MwsOrder($post);
			MwsObjectCache::add($obj, $obj->id);
//			mwdbg('MwsOrder -- added to cache');
		} else {
//			mwdbg('MwsOrder -- cache USED');
		}
		return $obj;
	}

	function __get($name) {
		if ($name==='id') {
			return isset($this->post) ? $this->post->ID : 0;
		} else {
			$this->load();
			if ($name === 'orderNum') {
				return (isset($this->meta['orderNum'])) ? $this->meta['orderNum'] : null;
			} elseif ($name === 'title') {
				$orderNum = $this->orderNum;
				return empty($orderNum) ? __('Objednávka (bez čísla)', 'mwshop') : __('Objednávka č. %s', 'mwshop');
			} elseif ($name === 'gateId') {
				return isset($this->meta['gateId']) ? $this->meta['gateId'] : '';
			} elseif ($name === 'isPaid') {
				return isset($this->meta['isPaid']) ? $this->meta['isPaid'] : '';
			} elseif ($name === 'paidOn') {
				return isset($this->meta['paidOn']) ? $this->meta['paidOn'] : '';
			} elseif ($name === 'status') {
				return isset($this->meta['status']) ? $this->meta['status'] : '';
			} elseif ($name === 'urlDirectPay') {
				return isset($this->meta['urlDirectPay']) ? $this->meta['urlDirectPay'] : '';
			} elseif ($name === 'customerNote') {
				return isset($this->meta['customerNote']) ? $this->meta['customerNote'] : '';
			} elseif ($name === 'gateOrderData') {
				return isset($this->meta['gateOrderData']) ? $this->meta['gateOrderData'] : '';
			} elseif ($name == 'gateLive') {
				if(!$this->_gateLive) {
					//Load info from gate.
					$live = MWS()->gateways()->loadOrderFor($this);
					$this->gateLive = $live;
				}
				return $this->_gateLive;
			} elseif ($name === 'items') {
				if(!$this->_orderItems) {
					// For this time use only stored items from the time of order. Do not reflect additional changes.
					$this->_orderItems = new MwsOrderItems($this, $this->rawItems);
				}
				return $this->_orderItems;
			} elseif ($name == 'rawItems') {
				return isset($this->meta['items']) ? $this->meta['items'] : array();
			}
		}
		return null;
	}


	function __set($name, $value) {
		$this->load();
		if($name==='orderNum') {
			$this->meta['orderNum'] = trim((string)$value);
		} elseif ($name==='gateId') {
			$this->meta['gateId'] = $value;
		} elseif ($name==='isPaid') {
			$this->meta['isPaid'] = $value;
		} elseif ($name==='paidOn') {
			$this->meta['paidOn'] = (int)$value;
		} elseif ($name==='status') {
			$this->meta['status'] = $value;
		} elseif ($name==='urlDirectPay') {
			$this->meta['urlDirectPay'] = $value;
		} elseif ($name==='customerNote') {
			$this->meta['customerNote'] = $value;
		} elseif ($name==='gateOrderData') {
			$this->meta['gateOrderData'] = $value;
		} elseif ($name == 'gateLive') {
			if(!$value instanceof MwsOrderGate)
				throw new MwsException('Instance of "MwsOrderGate" is expected.');
			$this->_gateLive = $value;
		} elseif ($name == 'rawItems') {
			if(!(is_array($value)))
				throw new MwsException('Value must be an array.');
			$this->meta['items'] = $value;
		}
	}

	public function load() {
		if(!is_null($this->meta))
			return;

		if(is_null($this->post))
			$this->meta = array();
		else {
			$id = $this->id;
			$meta = get_post_meta($id, MWS_ORDER_META_KEY);
			if (isset($meta[0]))
				$this->meta = $meta[0];
		}
	}

	/**
	 * Save in-memory state of order. If order is not bound with a post (meaning {@link post} property is empty) then
	 * it creates new post of custom post type {@link MWS_ORDER_SLUG}.

	 * @return int|false Returns ID of the associated post (existing or newly created) on success. Returns <code>false</code>
	 *                   on error.
	 */
	public function save() {
		$this->updateMeta();
		if(empty($this->post)) {
			// Create new order.
			$orderNum = $this->orderNum;

			$args = array(
				'post_title' => sprintf(__('Objednávka č. %s', 'mwshop'), $orderNum),
				'post_status' => 'publish',
				'post_type' => MWS_ORDER_SLUG,
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_name' => sanitize_title(__('objednávka','mwshop').sprintf('_%s', $orderNum)),
				'meta_input' => array(
					MWS_ORDER_META_KEY => $this->meta,
					MWS_ORDER_META_KEY_ORDERNUM => $orderNum,
//					MWS_ORDER_META_KEY_CUSTOMERNOTE => $this->customerNote,
				),
			);
			$postId = wp_insert_post($args, false);
			if($postId) {
				$this->post = get_post($postId);
			} else {
				mwshoplog('New order could not be saved into database.', MWLL_ERROR, 'order');
				return false;
			}
		}

		if($this->id) {
			// Update existing order
			if(!empty($this->meta)) {
				update_post_meta($this->id, MWS_ORDER_META_KEY, $this->meta);
				update_post_meta($this->id, MWS_ORDER_META_KEY_ORDERNUM, $this->orderNum);
//				update_post_meta($this->id, MWS_ORDER_META_KEY_CUSTOMERNOTE, $this->customerNote);
			}
		}

		return $this->id;
	}

	/**
	 * Get order by its order number.
	 * @param $orderNum string Number of the order.
	 * @return MwsOrder|null
	 * @throws MwsException
	 */
	public static function getOrderByOrderNum($orderNum) {
		if(empty($orderNum))
			return null;

		$args = array(
			'meta_key' => MWS_ORDER_META_KEY_ORDERNUM,
			'meta_value' => (string)$orderNum,
			'post_type' => MWS_ORDER_SLUG,
			'post_status' => 'any',
			'posts_per_page' => -1
		);
		$posts = get_posts($args);

		if(count($posts) > 0) {
			try {
				return static::createNew($posts[0]);
			} catch (Exception $e) {
				return null;
			}
		} else
			return null;
	}

	/**
	 * Mark order as being paid. Log necessary information.
	 * @param $paid bool Is this payment (true) or refund (false)?
	 * @param $when int Unix timestamp in GMT.
	 */
	public function setPaid($paid, $when) {
		$paid = (bool)$paid;
		mwshoplog('Setting order '.$this->orderNum.' as '.($paid ? '' : 'un').'paid on GMT '.mwFormatAsDateTime($when), MWLL_INFO, 'order');
		$this->load();
		$this->isPaid = $paid;
		$this->paidOn = (int)$when;

		//TODO Add note into history of order.
	}

	public function setCancelled() {
		$when = new \DateTime('now', new \DateTimeZone('GMT'));
		mwshoplog('Setting order '.$this->orderNum.' as cancelled on GMT '.mwFormatAsDateTime($when->getTimestamp()), MWLL_INFO, 'order');
		$this->changeStatus(MwsOrderStatus::Cancelled);
		$this->save();
	}

	public function changeStatus($status) {
		$newStatus = MwsOrderStatus::checkedValue($status);
		if(is_null($newStatus)) {
			mwshoplog('Cannot change order status for '.$this->orderNum.' to ['.$status.']. Unsupported order status.',
				MWLL_ERROR, 'order');
			throw new MwsException('Invalid order status ['.$status.']');
		}

		$oldStatus = $this->status;
		if($oldStatus==$newStatus) {
			mwshoplog('Order status for '.$this->orderNum.' is already '.MwsOrderStatus::getCaption($newStatus).'. '
				. 'Nothing was changed.', MWLL_INFO, 'order');
			return;
		}
		$this->status = $newStatus;
		$this->save();

		mwshoplog('Order status for '.$this->orderNum.' changed from "'.MwsOrderStatus::getCaption($oldStatus)
			.'" to "'. MwsOrderStatus::getCaption($newStatus).'".', MWLL_INFO, 'order');

		//TODO Add note into history of order.
	}

	/** Save temporary states in memory into {@link $meta} field. */
	private function updateMeta() {
		$this->load();
		if(!is_null($this->_orderItems)) {
			$rawItems = $this->_orderItems->toArray();
			$this->rawItems = $rawItems;
		}
	}

}

/**
 * Group of backlinks from ordered items to products in shop.
 * @property-read array $items List of {@link MwsOrderItem} instances, that is list of ordered items.
 * @property-read MwsOrder $order Order where this list belongs to.
 */
class MwsOrderItems {
	/** @var array Array of created instances of MwsOrderProduct from loaded meta. */
	private $_data;
	/** @var array Loaded metadata. */
//	public $meta = null;
	/** @var MwsOrder Parent order where this instance belongs to. */
	private $_order;

	/**
	 * @param MwsOrder $parent Owning order.
	 * @param array $rawItemsMeta Serialized raw items as stored as meta within an order.
	 */
	function __construct($parent, $rawItemsMeta = null) {
		$this->_order = $parent;
		if(is_array($rawItemsMeta))
			$this->loadFromRawItems($rawItemsMeta);
	}

	private function loadFromRawItems($rawItems) {
		if(is_null($rawItems) || !is_array($rawItems))
			$this->_data = null;
		else {
			$this->_data = array();
			foreach ($rawItems as $item) {
				$newItem = new MwsOrderItem(
					isset($item['productId']) ? $item['productId'] : 0,
					isset($item['count']) ? $item['count'] : 0,
					isset($item['gatewayId']) ? $item['gatewayId'] : '',
					isset($item['syncId']) ? $item['syncId'] : 0,
					isset($item['conversionCode']) ? $item['conversionCode'] : null
				);
				$this->_data[] = $newItem;
			}
		}
	}

	function __get($name) {
		if($name==='order')
			return $this->_order;
		elseif($name=='items') {
			if(is_null($this->_data) && $this->_order) {
				$rawItems = $this->_order->rawItems;
				if(!$rawItems || !is_array($rawItems)) {
					mwshoplog("Invalid META value of order's items is stored within order with id [{$this->_order->id}].", MWLL_ERROR, 'order');
					$rawItems = array();
				}
				$this->loadFromRawItems($rawItems);
			}
			return $this->_data;
		} else
			return null;
	}

	/**
	 * Add new ordered item.
	 * @param MwsOrderItem $item
	 * @throws MwsException When Item is not of proper class.
	 */
	public function add($item) {
		if(!($item instanceof MwsOrderItem))
			throw new MwsException('Item must be of "MwsorderItem" class.');
		// Load array at first if not done already.
		if(is_null($this->_data))
			$this->__get('items');
		// Nothing to load or items are unloadable.
		if(is_null($this->_data))
			$this->_data = array();

		$this->_data[] = $item;
	}

	public function toArray() {
		$res = array();
		/** @var MwsOrderItem $item */
		foreach ($this->_data as $item) {
			$res[] = array(
				'productId' => $item->productId,
				'count' => $item->count,
				'gatewayId' => $item->gatewayId,
				'gateSyncId' => $item->gateSyncId,
				'conversionCode' => $item->conversionCode,
			);
		}
		return $res;
	}

}

/** One item of an order. Can provide direct access to product through properties. */
class MwsOrderItem {
	public $productId;
	public $count;
	public $gatewayId;
	public $gateSyncId;
	public $conversionCode;

	function __construct($productId, $count, $gatewayId, $gateSyncId, $conversionCode) {
		$this->productId = $productId;
		$this->count = $count;
		$this->gatewayId = $gatewayId;
		$this->gateSyncId = $gateSyncId;
		$this->conversionCode = $conversionCode;
	}
}

/**
 * Live connector to the order at the gateway. Works as caching wrapper object for order, where its data is loaded
 * directly from the gateway. This object is accessible as {@link MwsOrder::gateLive} property.
 *
 * @property MwsPrice $price Total price of the order.
 * @property bool $isPaid Is order fully paid?
 * @property int $paidOn When the order has been paid, Unix timestamp in UTC.
 */
class MwsOrderGate {
	/** @var MwsOrder */
	protected $parent;
//	/** @var MwsPrice */
//	protected $_price;
//	/** @var bool */
//	protected $_isPaid;

	/**
	 * @param $parentOrder MwsOrder
	 * @throws MwsException On validation errors.
	 */
	function __construct($parentOrder) {
		if(is_null($parentOrder))
			throw new MwsException('Parent order can not be null.');
		$this->parent = $parentOrder;
	}

	function __get($name) {
		if($name==='price') {
			if(!isset($this->_price))
				$this->_price = $this->doGetPrice();
			return $this->_price;
		} else if($name=='isPaid') {
			if(!isset($this->_isPaid))
				$this->_isPaid = $this->doGetIsPaid();
			return $this->_isPaid;
		} else if($name=='paidOn') {
			if(!isset($this->_paidOn))
				$this->_paidOn = $this->doGetPaidOn();
			return $this->_paidOn;
		} else
			return false;
	}

	/**
	 * Get associated gateway instance.
	 * @return MwsGatewayMeta|null
	 */
	protected function getGateway() {
		if(!isset($this->gw)) {
			$this->gw = MWS()->gateways()->getById($this->parent->gateId);
		}
		return $this->gw;
	}

	/**
	 * Get information about the ordering person.
	 * @param bool $short Set to <code>true</code> to output short version of the contact, e.g. like a title.
	 * @return string If none is present then empty string is returned.
	 */
	public function formatInvoiceContact($short = false) {return '';}

	/**
	 * Get shipping contact.
	 * @return string If none is present then empty string is returned.
	 */
	public function formatShippingContact() {return '';}

	/**
	 * Get contact edit buttons for WP administration.
	 * @return string If none is present then empty string is returned.
	 */
	public function formatContactEditting() {return '';}

	/**
	 * Get items of the order.
	 * @return array Items of the order. These fields are included: title, count, priceIncludingVat, vatPercentage, productId
	 */
	public function getItems() {return array();}

	/**
	 * Get documents of the order.
	 * @return array Documents of the order. These fields are included: title, urlShow, urlDownload, urlEdit
	 */
	public function getDocuments() {return array();}

	/**
	 * Ancestor loads real price.
	 * @return MwsPrice
	 */
	protected function doGetPrice() {
		return null;
	}

	/**
	 * Ancestor load real status of payments.
	 * @return bool|null Null on error
	 */
	protected function doGetIsPaid() {
		return null;
	}

	/**
	 * Ancestor load real time of payment as Unix timestamp in UTC.
	 * @return int|null Null on error
	 */
	protected function doGetPaidOn() {
		return null;
	}
}
