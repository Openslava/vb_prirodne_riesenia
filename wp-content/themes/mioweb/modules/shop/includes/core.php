<?php
/**
 * Exception classes
 *
 * Date: 08.02.16
 * Time: 16:21
 *
 * @since 1.0.0
 */


/** Basic MioShop exception. */
class MwsException extends Exception {

}

/** Exception with a message presentable to user. It is localised. */
class MwsUserException extends MwsException {}

/**
 * Rendering helper class. Stores current instances that should be rendered using template files.
 * @property int productId
 * @property MwsProduct product
 * @property MwsCartItem cartItem
 * @property MwsOrderStep orderStep
 * @property bool $showAvailabilityInAdded
 */
class MwsCurrent {
	/** @var int */
	private $_productId;
	/** @var MwsProduct */
	private $_product;
	/** @var MwsCartItem */
	private $_cartItem;
	/** @var MwsOrderStep */
	private $_orderStep;

	function __get($name) {
		if ($name=='productId') {
			if (isset($this->_productId))
				return $this->_productId;
			else if (isset($this->_product) && $this->_product instanceof MwsProduct)
				return $this->_product->id;
			else
				return 0;
		} elseif ($name=='product') {
			return isset($this->_product) ? $this->_product : null;
		} elseif ($name=='cartItem') {
			return isset($this->_cartItem) ? $this->_cartItem : null;
		} elseif ($name=='orderStep') {
			return isset($this->_orderStep) ? $this->_orderStep : MwsOrderStep::Cart;
		}
		return null;
	}

	function __set($name, $value) {
		if ($name=='productId') {
			$this->_productId = (int)$value;
		} elseif ($name=='product') {
			if ($value instanceof WP_Post)
				$value = MwsProduct::createNew($value);
			elseif ($value instanceof MwsProduct)
				;
			else
				throw new MwsException('Invalid usage. For [product] use WP_Post or MwsProduct object.');
			$this->_product = $value;
			unset($this->_productId);
		} elseif ($name=='cartItem') {
			if ($value instanceof MwsCartItem)
				$this->_cartItem = $value;
			else
				throw new MwsException('Invalid usage. For [cartItem] use MwsCartItem object.');
		} elseif ($name=='orderStep') {
			$this->_orderStep = MwsOrderStep::checkedValue($value, MwsOrderStep::Cart);
		} elseif(!empty($name)) {
			$this->$name = $value;
		}
	}

}

/**
 * Global cache for object. Instances are saved keyed as (class name, id).
 */
class MwsObjectCache {
	private static $items;

	public static function init() {
		static::$items = array();
	}

	/**
	 * Add new instance into cache. If there is a cached instance with the same classname and same id then it will be
	 * overwritten by passed instance.
	 * @param $obj object Instance to be added into the cache.
	 * @param $id int|string ID bellow which the object will be accessible.
	 * @return bool|object On success added object is returned. On error <code>false</code> is returned.
	 */
	public static function add($obj, $id) {
		if(empty($obj))
			return false;
		$className = get_class($obj);
		if(!$className)
			return false;

		if(!isset(static::$items[$className])) {
			static::$items[$className] = array();
		}
//		if(!isset(static::$items[$className][$id]))
//			mwshoplog("Added [$className][$id] to cache.", MWLL_DEBUG, 'cache');
//		else
//			mwshoplog("Updated [$className][$id] in cache.", MWLL_DEBUG, 'cache');

		static::$items[$className][$id] = $obj;
		return $obj;
	}

	/**
	 * Get object from the cache.
	 * @param $className string Class name of the requested object.
	 * @param $id int|string ID bellow which the object was stored into cache.
	 * @return object|null Returns found object or <code>null</code>.
	 */
	public static function get($className, $id) {
		return isset(static::$items[$className][$id])
			? static::$items[$className][$id]
			: null;
	}

	/**
	 * Remove object from cache.
	 * @param $className string Class name of the requested object.
	 * @param $id int|string ID bellow which the object was stored into cache.
	 * @return bool Return true when object was present in cache.
	 */
	public static function remove($className, $id) {
		$res = isset(static::$items[$className][$id]);
		if($res) {
//			mwshoplog("Removed [$className][$id] from cache.", MWLL_DEBUG, 'cache');
			unset(static::$items[$className][$id]);
		}
		return $res;
	}

	/**
	 * Remove object from cache.
	 * @param object $obj
	 * @param $id int|string ID bellow which the object was stored into cache.
	 * @return bool Return true when object was present in cache.
	 */
	public static function removeObj($obj, $id) {
		$className = get_class($obj);
		return static::remove($className, $id);
	}
}

// Initialize static fields.
MwsObjectCache::init();
