<?php
/**
 * Payment gateways support.
 * Global object of {@link MwsGateways} is instantiated in {@link MWC()} and contains description of all registered
 * gateways.
 *
 * User: kuba
 * Date: 23.02.16
 * Time: 16:17
 */

/**
 * List of available gateways and their settings.
 */
class MwsGateways {
	/** @var array List of gateway descriptors {@link MwsGatewayMeta}. */
	public $items = array();
	public $syncDisabled = false;

	function __construct() {
		apply_filters('mws_gateway_register', $this);
//		mwdbg(count($this->items), __CLASS__.' gatewaysRegistered');
		$this->syncDisabled = (defined('MWS_DISABLE_PAYGATE_SYNC') && MWS_DISABLE_PAYGATE_SYNC);
	}

	/**
	 * Adds new gateway into list of available gateways. Duplicate addition of gate with the same id is not available.
	 * @param $gw MwsGatewayMeta
	 * @return bool Returns true if new gateway was added.
	 */
	public function registerGw($gw) {
//		mwdbg(__METHOD__.'('.$gw->id.')');
		//Add only if not registered already.
		$item = $this->getById($gw->id);
		if (!empty($item))
			return false;
		$this->items[] = $gw;
		return true;
	}

	/**
	 * Returns gateway descriptor instance with passed ID or "null".
	 * @param $gwId string Id of searched gateway.
	 * @return MwsGatewayMeta|null
	 */
	public function getById($gwId) {
		if(!$gwId)
			return null;
		/** @var MwsGatewayMeta $item */
		foreach ($this->items as $item)
			if ($item->id === $gwId)
				return $item;
		return null;
	}

	/**
	 * Synchronize all enabled and connected gateways.
	 * @param string $gwId Optional id of gateway that should be synchronized.
	 * @return bool When all synchronization is successful then true is returned, otherwise false.
	 */
	public function synchronizeAll($gwId='') {
		mwshoplog(__METHOD__, MWLL_DEBUG);
		if($this->syncDisabled) {
			mwshoplog(__('Synchronizace platebních/fakturačních bran zakázáná, neprovádí se.', 'mwshop'), MWLL_WARNING, 'paygate');
			return false;
		}
		$res = true;
		$gw = $this->getById($gwId);
		if(is_null($gw)) {
			$gws = MWS()->gateways()->items;
			/** @var MwsGatewayMeta $gw */
			foreach ($gws as $gw) {
				$res = $res && $gw->synchronize();
			}
		} else {
			$res = $res && $gw->synchronize();
		}
		return $res;
	}

	/**
	 * Clear "isSynced" flag of all gates.
	 */
	public function clearSyncedAll() {
		mwshoplog(__METHOD__, MWLL_DEBUG, 'paygate');
		/** @var MwsGatewayMeta $gw */
		foreach($this->items as $gw)
			$gw->isSynced = false;
	}

	/**
	 * Tells whether synchronization of some gate is needed.
	 * @return bool
	 */
	public function syncNeeded() {
		$needed = false;
		/** @var MwsGatewayMeta $gw */
		foreach($this->items as $gw) {
			$needed = $needed || !($gw->isSynced);
		}
		return $needed;
	}

	/**
	 * Check whether synchronization of some object is needed depending on its previously saved synchronization status.
	 * Can be used to check against specific gateway or to a specific gate.
	 * @param MwsSync $syncData Synchronization object
	 * @param string $gwId
	 * @param string $gwSpecId
	 * @return bool
	 */
	public function isItemSyncNeeded($syncData, $gwId = '', $gwSpecId = '') {
		if(!$syncData)
			return true;
		$res = false;
		if(empty($gwId) && empty($gwSpecId)) {
			$gws = MWS()->gateways()->items;
			/** @var MwsGatewayMeta $gw */
			foreach ($gws as $gw) {
				$gwSpecId = $gw->sharedInstance()->doGetSyncSpecId();
				$gwId = $gw->id;
				$res |= $syncData->isSyncNeeded($gwId, $gwSpecId);
			}
		} else {
			$res |= $syncData->isSyncNeeded($gwId, $gwSpecId);
		}
		return $res;
	}

	/**
	 * Returns default gateway, that is currently active gateway selected in global shop options.
	 * @return MwsGatewayMeta
	 */
	public function getDefault() {
		$gwId = $this->getDefaultId();
		return $this->getById($gwId);
	}

	/**
	 * Returns default gateway id, that is currently active gateway selected in global shop options. If setting is missing
	 * then it uses default fallback gateway.
	 * @return string
	 */
	public function getDefaultId() {
		return MWS()->gatewaySelectedId;
	}

	/**
	 * Returns fallback gateway ID that is used when no gateway is configured.
	 * @return string
	 */
	public function getDefaultFallbackId() {
		return 'fapi';
	}

	/**
	 * Load information from the gateway for volatile caching for a specified order.
	 * @param $order MwsOrder Order for which the data should be loaded.
	 * @return MwsOrderGate Returns a data wrapper for corresponding gate or null.
	 */
	public function loadOrderFor($order) {
		if(!$order)
			return null;
		mwshoplog(__METHOD__.' order='.$order->orderNum, MWLL_DEBUG, 'paygate');
		$gw = $this->getById($order->gateId);
		if(is_null($gw)) {
			mwshoplog('Paygate ['.$order->gateId.'] of order '.$order->orderNum.' is not supported.', MWLL_WARNING, 'paygate');
			return null;
		}
		$gateOrder = $gw->sharedInstance()->loadOrderGate($order);
		return $gateOrder;
	}

	/**
	 * Fills property {@link MwsOrder::gateLive} of passed orders. It tries to make it effectively in less API gate calls.
	 * @param array $orders Array of {@link MwsOrder} objects.
	 */
	public function preloadOrdersGateLive($orders) {
		// Create array for splitting orders by gate.
		$gOrders = array();
		/**@var MwsGatewayMeta $gate  */
		foreach ($this->items as $gate) {
			$gOrders[$gate->id] = array($gate, array());
		}

		// Split orders into array by its gateId.
		/** @var MwsOrder $order */
		foreach ($orders as $order) {
			$gateId = $order->gateId;
			if(isset($gOrders[$gateId]))
				$gOrders[$gateId][1][] = $order;
		}

		// Load gate data at once, separately for each gate
		foreach ($gOrders as $gateId => $gateItem) {
			if(empty($gateItem[1]))
				continue;
			/** @var MwsGatewayMeta $gw */
			$gw = $gateItem[0];
			if(!empty($gateItem[1]))
				$gw->sharedInstance()->preloadOrdersGateLive($gateItem[1]);
		}
	}

	/**
	 * Generate HTML select element with payment methods.
	 * @param $id
	 * @param string $name            Name of the HTML element.
	 * @param string $selected        Value of selected element.
	 * @param string $css             Optional CSS class
	 * @param string $notSelectedText When not empty then first item with passed text and zero value  is added to the beginning.
	 * @param array $excludedPayTypes	Set this to enumeration of {@link MwsPayType} to exclude them from the list.
	 * @return string HTML text
	 */
	public function htmlSelect($id, $name, $selected = '', $css = '', $notSelectedText = '', $excludedPayTypes = array()) {
		$gw = $this->getDefault();
		if(!$gw)
			if(Mws()->edit_mode)
				return '<div class="cms_error_box">' . __('Není vybrána aktivní platební brána.', 'mwshop') . '</div>';
			else
				return '';

		$res = '<select id="'.$id.'" name="'.$name.'"' .(empty($css) ? '' : ' class="'.$css.'"'). '>';
		if(!empty($notSelectedText)) {
			$res .= '<option value="0" '. ($selected == 0 ? ' selected="selected"' : '') . '>'
				. $notSelectedText
				. '</option>';
		}
		$payTypes = $gw->getEnabledPayTypes();
		foreach ($payTypes as $payType) {
			if(in_array($payType, $excludedPayTypes))
				continue;
			$res .= '<option value="'.$payType.'" '. ($selected == $payType ? ' selected="selected"' : ''). '>'
				. MwsPayType::getCaption($payType)
				. '</option>';

		}
		$res .= '</select>';
		return $res;
	}
}

/**
 * Describing information of tha gateway. Basically its id, caption, capabilities. Can create new instance of gateway
 * or use shared global instance.
 *
 * @property bool isSynced Reflects status of gateway synchronization. This state can be altered. Status is saved withing
 *                         own option, single for each gateway.
 */
class MwsGatewayMeta {
	/** @var string Id of the gateway. Format of identifier (no spaces...). Can be used as ID. */
	public $id = '';
	public $caption = '';
	/** @var string Class name of implementing class, ancestor of {@link MwsGatewayImpl}. */
	public $class = '';
	/** @var string Absolute path to the file with implementation of class {@link MwsGatewayMeta::class}. */
	private $filepath;
	/** @var null|array Setting of the gateway. Stored as option. Use {@link loadSettings()} and {@link saveSettings()}
	 * for manipulation. */
	public $gateStgs=null;
	/** @var null|array Remote settings of the gateway. Stored as transient option. Use {@link getRemoteSettings()}. */
	private $remoteGateStgs=null;
	/** @var null|array Remote paytypes of the gateway. Stored as transient option. Use {@link getEnabledPayTypes()}. */
	private $remotePayTypes=null;
	/** @var null|bool Remote setting of use simplified invoice of the gateway. Stored as transient option.
	 * Use {@link getUseSimplifiedInvoice()}. */
	private $useSimplifiedInvoice=null;
	/** @var MwsGatewayImpl Globally shared instance of the gateway, accessible by {@link instance()}, automatically
	 * created. */
	private $instance = null;

	/**
	 * Creates description of new gateway.
	 * @param $id string Id of the gateway. Must comply with format of PHP identifier.
	 * @param $caption string Localized title of the gateway. It is used in UI.
	 * @param $class string Name of the implementing class. Implementing class must be ancestor of {@MwsGatewayImpl}.
	 * @param $filepath string Absolute path to the file with implementing class. It is require_once() when the
	 *                  implementation is needed.
	 */
	function __construct($id, $caption, $class, $filepath) {
		$this->id = $id;
		$this->caption = ($caption ? $caption : $id);
		$this->class = $class;
		$this->filepath = $filepath;
	}

	/**
	 * Returns shared instance. It is save to reuse it withing same thread.
	 * @return MwsGatewayImpl
	 */
	public function sharedInstance() {
		if(is_null($this->instance))
			$this->instance = $this->newInstance();
		return $this->instance;
	}

	/**
	 * Returns new unique instance of the gateway.
	 * @return MwsGatewayImpl
	 */
	public function newInstance() {
		if (!empty($this->filepath)) {
			require_once($this->filepath);
		}
		$inst = new $this->class($this);
		return $inst;
	}

	/**
	 * Returns true if gateway is enabled for synchronization.
	 */
	public function isEnabled() {
//		$meta = get_option(MWS_OPTION_SHOP_SETTING);
//		$enabled = isset($meta['pay_gate']) && is_array($meta['pay_gate'])? in_array($this->id, $meta['pay_gate']) : false;
		$gateId = MWS()->gatewaySelectedId;
		$enabled = ($gateId == $this->id);
		return $enabled;
	}

	/**
	 * Synchronize one gateway. Flag of gate's synchronization status is updated at the end.
	 *
	 * @param bool $force Synchronization is not performed when saved synchronization flag "isSynced" is set to false. Setting
	 *                    this argument to true forces synchronization to perform.
	 * @return bool Returns true when there was no error during synchronization. Disabled and/od unconnected gates requires
	 *              no synchronization and therefore they returns true too.
	 */
	public function synchronize($force=false) {
		$perform = $force || !$this->isSynced;
		if(!$perform) {
			// No synchronization necessary, quit.
			mwshoplog(__METHOD__.' ['.$this->id.'] ... skipped', MWLL_DEBUG, 'paygate');
			return true;
		}

		$res = true;
		if ($this->isEnabled()){
			mwshoplog("Synchronization of [$this->id] started.", MWLL_INFO, 'paygate');

			//Clear invalid cache items.
			$this->dropCache_EnabledPayTypes();

			//Perform sync.
			$inst = $this->sharedInstance();
			$res = $inst->isConnected();
			if($res) {
				$res = $inst->syncSettings();
				if(!$res)
					mwshoplog("Synchronization of settings of [$this->id] failed.", MWLL_ERROR, 'paygate');

				if($res)
					$res = $inst->syncItems();

				if(!$res)
					mwshoplog("Synchronization of items of [$this->id] failed.", MWLL_ERROR, 'paygate');
			} else {
				mwshoplog("Synchronization of gate [$this->id] failed. Paygate is not connected.", MWLL_ERROR, 'paygate');
			}
		} else {
			mwshoplog("Synchronization of settings of [$this->id] skipped. Gateway is disabled.", MWLL_DEBUG, 'paygate');
		}
		//Update saved flag.
		$this->isSynced = $res;
		mwshoplog("Synchronization of [$this->id] finished ".($res?'successfuly':'with errors').'.', MWLL_INFO, 'paygate');
		return $res;
	}

	function __get($name) {
		if($name==='isSynced') {
			$meta = get_option(MWS_OPTION.'gate_'.$this->id.'_synced', false);
			$meta = (bool)$meta;
			return $meta;
		}
		return null;
	}

	function __set($name, $value) {
		if($name==='isSynced') {
			$value = (bool)$value; //be sure to save bool value
			update_option(MWS_OPTION.'gate_'.$this->id.'_synced', $value);
			mwshoplog("Marking paygate [$this->id] as ".($value?'SYNCED':'UNSYNCED').'.', MWLL_INFO, 'paygate');
		}
	}


	/**
	 * Get settings form for a gateway. This should return HTML code with values of settings for the specific gateway.
	 * When no settings are for the gateway, empty string should be returned.
	 * @param $idPrefix string Prefix of ID for HTML elements.
	 * @param $namePrefix string Prefix of name attribute of HTML elements.
	 * @param $meta array Whole meta of all this gateway.
	 * @return string HTML code or empty string.
	 */
	public function getSettingsButton($idPrefix, $namePrefix, $meta) {
		// Drop all caches of remote settings relevant to gateway.
		$this->dropCache_EnabledPayTypes();
		$this->dropCache_UseSimplifiedInvoice();

		$code = $this->sharedInstance()->getSettingsForm($idPrefix, $namePrefix, $meta);
		return $code;
	}

	/**
	 * This method is called when page option of {@link MwsGatewayImpl::getSettingsForm} is to be updated.
	 * Values of form are stored within $_REQUST variable according to names used within HTML generated by
	 * {@link MwsGatewayImpl::getSettingsForm} method.
	 *
	 * Implementation should store necessary settings itself, possible reset flag to resynchronize gateway.

	 * @return bool If gate should be resynced, <code>true</code> should be returned.
	 */
	public function handleSettingsChanged_Form() {
		$resync = false;
		if(isset($_REQUEST['gate_settings'][$this->id])) {
			$resync |= $this->sharedInstance()->doSettingsChanged_Form();
		}
		return $resync;
	}

	/**
	 * This method is called when payment method's settings is to be updated.
	 * New settings are accessible within $_REQUEST as "<code>$_REQUEST['gate_settings'][$this->id]</code>" variable.
	 * @return bool If gate should be resynced, <code>true</code> should be returned.
	 */
	public function handleSettingsChanged_Payments() {
		$resync = false;
		if(isset($_REQUEST['gate_settings'][$this->id])) {
			$new = (isset($_REQUEST['gate_settings'][$this->id]['payments']))
				? $_REQUEST['gate_settings'][$this->id]['payments']
				: array();
			$this->loadSettings();
			//Shared general settings
			$changed = !(isset($this->gateStgs['payments'])) || !($this->gateStgs['payments'] == $new);
			if ($changed) {
				mwshoplog("Payment methods of paygate [$this->id] changed", MWLL_INFO, 'paygate');
				$this->gateStgs['payments'] = $new;
				$resync = true;
			}
			//Optional custom settings specific for gate
			$resync |= $this->sharedInstance()->doSettingsChanged_Payments();
			$this->saveSettings();
		}
		return $resync;
	}

	/**
	 * Load gate's settings from option. Fills internal {@link $gateStgs}.
	 * @return array Copy of current settings as array. If settings are empty, then empty array is returned.
	 */
	public function loadSettings() {
		if (is_null($this->gateStgs)) {
			$stgs = get_option(MWS_OPTION . '_gate_' . $this->id, array());
			if(!isset($stgs['payments'])) {
				mwshoplog("Payment methods are not set for paygate [$this->id]. Applying default payment methods for [$this->id].", MWLL_INFO, 'paygate');
				$stgs['payments'] = $this->sharedInstance()->doGetDefaultEnabledPayTypes();
			}
			$this->gateStgs = $stgs;
		}
		return $this->gateStgs;
	}

	/**
	 * Saves internal {@link $gateStgs}. If argument @newStgs is passed then internal {@link $gateStgs} is updated first.
	 * @param null|array $newStgs Optional new value of settings. If now value is passed then cached settings is used.
	 *                            If value is given, it will be used ase new settings and value will overwrite internal
	 *                            {@link $gateStgs} too.
	 */
	public function saveSettings($newStgs = null) {
		if(is_array($newStgs))
			$this->gateStgs = $newStgs;
		if (empty($this->gateStgs))
			return;
		update_option(MWS_OPTION.'_gate_'.$this->id, $this->gateStgs, false);
	}

	/**
	 * Get remote settings of the gateway.
	 * @param bool $reload Transient cache usage can be skipped by setting to true.
	 * @return array
	 * @throws MwsException On error
	 */
	public function getRemoteSettings($reload = false) {
		/** @var bool $isReloaded Protection against multiple reloads in one PHP run. */
		static $isReloaded = false;
		$saveNeeded = false;
		if ($reload && !$isReloaded) {
			mwshoplog("Remote settings for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
			$this->remoteGateStgs = $this->sharedInstance()->loadRemoteSettings();
			$saveNeeded = true;
			$isReloaded = true;
		} else {
			if (is_null($this->remoteGateStgs)) {
				$transient = get_transient(MWS_OPTION . '_gateremote_' . $this->id);
				if (!$transient || empty($transient)) {
					mwshoplog("Remote settings for paygate [$this->id] need to be loaded.", MWLL_DEBUG, 'paygate');
					$this->remoteGateStgs = $this->sharedInstance()->loadRemoteSettings();
					$saveNeeded = true;
					$isReloaded = true;
				} else {
					mwshoplog("Remote settings for paygate [$this->id] loaded from cache.", MWLL_DEBUG, 'paygate');
				$this->remoteGateStgs = $transient;
				}
			}
		}
		if ($saveNeeded) {
			set_transient(MWS_OPTION . '_gateremote_' . $this->id, $this->remoteGateStgs, 60*60*24); //one day persistence
			mwshoplog("Remote settings for paygate [$this->id] saved.", MWLL_DEBUG, 'paygate');
		}
		return $this->remoteGateStgs;
	}

	/**
	 * Get array of purposes.
	 * Each object within array contains: id, checkbox_label (text description), link_href (url),
	 * link_label (text for url), is_primary (bool, if this is primary and therefore necessary).
	 * @return array Array of object with field: id (purpose id), checkbox_label (text description), link_href (url),
	 * link_label (text for url), is_primary (bool, if this is primary and therefore necessary).
	 */
	public function getPurposes() {
		return $this->sharedInstance()->doGetPurposes();
	}

	/**
	 * Get array of supported payment methods by the gateway. Values are sorted according to definition order within
	 * {@link MwsPayType}.
	 * @return array Array of values of enumeration {@link MwsPayType}.
	 */
	public function getSupportedPayTypes() {
		$supported = $this->sharedInstance()->doGetSupportedPayTypes();
		$all = MwsPayType::getAll();
		$validated = array_intersect($all,$supported);
		return $validated;
	}

	/**
	 * Get array of payment methods enabled as default by the gateway. Values are sorted according to definition order within
	 * {@link MwsPayType}.
	 * @return array Array of values of enumeration {@link MwsPayType}.
	 */
	public function getDefaultEnabledPayTypes() {
		$supported = $this->sharedInstance()->doGetSupportedPayTypes();
		$enabled = $this->sharedInstance()->doGetDefaultEnabledPayTypes();
		$all = MwsPayType::getAll();
		$validated = array_intersect($all,$supported,$enabled);
		return $validated;
	}

	/**
	 * Returns enabled payment methods for the gateway. This is stored within gateway settings.
	 * @param bool $reload When true than result is downloaded from remote gateway.
	 * @return array
	 */
	public function getEnabledPayTypes($reload = false) {
		/** @var bool $isReloaded Protection against multiple reloads in one PHP run. */
		static $isReloaded = false;
		$saveNeeded = false;
		try {
			if (!$isReloaded && ($reload || MWS()->canEdit())) {
				mwshoplog("Enabled paytypes for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
				$this->remotePayTypes = $this->sharedInstance()->loadRemotePayTypes();
				$saveNeeded = true;
				$isReloaded = true;
			} else {
				if (is_null($this->remotePayTypes)) {
					$transient = get_transient(MWS_OPTION . '_gateremote_paytypes_' . $this->id);
					if (!$transient || empty($transient)) {
						mwshoplog("Enabled paytypes for paygate [$this->id] need to be loaded.", MWLL_DEBUG, 'paygate');
						$this->remotePayTypes = $this->sharedInstance()->loadRemotePayTypes();
						$saveNeeded = true;
						$isReloaded = true;
					} else {
						mwshoplog("Enabled paytypes for paygate [$this->id] loaded from cache.", MWLL_DEBUG, 'paygate');
						$this->remotePayTypes = $transient;
					}
				}
			}
			if ($saveNeeded) {
				set_transient(MWS_OPTION . '_gateremote_paytypes_' . $this->id, $this->remotePayTypes, 60 * 60 * 24); //one day persistence
				mwshoplog("Enabled paytypes for paygate [$this->id] saved.", MWLL_DEBUG, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog("Enabled paytypes for paygate [$this->id] could not be loaded.", MWLL_ERROR, 'paygate');
			$this->remotePayTypes = array();
		}
		return $this->remotePayTypes;
	}

	public function dropCache_EnabledPayTypes() {
		mwshoplog("Enabled paytypes for paygate [$this->id] uncached.", MWLL_DEBUG, 'paygate');
		delete_transient(MWS_OPTION . '_gateremote_paytypes_' . $this->id);
	}

	/**
	 * Returns true if simplified invoice is allowed.
	 * @param bool $reload When true than result is downloaded from remote gateway. This is done always when in admin mode.
	 * @return bool
	 */
	public function getUseSimplifiedInvoice($reload = false) {
		/** @var bool $isReloaded Protection against multiple reloads in one PHP run. */
		static $isReloaded = false;
		$saveNeeded = false;
		try {
			if (!$isReloaded && ($reload || MWS()->canEdit())) {
				mwshoplog("Simplified invoice for paygate [$this->id] forced reload.", MWLL_DEBUG, 'paygate');
				$this->useSimplifiedInvoice = $this->sharedInstance()->loadRemoteUseSimplifiedInvoice();
				$saveNeeded = true;
				$isReloaded = true;
			} else {
				if (is_null($this->useSimplifiedInvoice)) {
					$transient = get_transient(MWS_OPTION . '_gateremote_simplifiedinvoice_' . $this->id);
					if (!$transient || empty($transient)) {
						mwshoplog("Simplified invoice for paygate [$this->id] need to be loaded.", MWLL_DEBUG, 'paygate');
						$this->useSimplifiedInvoice = $this->sharedInstance()->loadRemoteUseSimplifiedInvoice();
						$saveNeeded = true;
						$isReloaded = true;
					} else {
						mwshoplog("Simplified invoice for paygate [$this->id] loaded from cache.", MWLL_DEBUG, 'paygate');
						$this->useSimplifiedInvoice = $transient;
					}
				}
			}
			if ($saveNeeded) {
				set_transient(MWS_OPTION . '_gateremote_simplifiedinvoice_' . $this->id, $this->useSimplifiedInvoice, 60 * 60 * 24); //one day persistence
				mwshoplog("Simplified invoice for paygate [$this->id] saved.", MWLL_DEBUG, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog("Simplified invoice for paygate [$this->id] could not be loaded.", MWLL_ERROR, 'paygate');
			$this->useSimplifiedInvoice = array();
		}
		return $this->useSimplifiedInvoice;
	}

	public function dropCache_UseSimplifiedInvoice() {
		mwshoplog("Simplified invoice for paygate [$this->id] uncached.", MWLL_DEBUG, 'paygate');
		delete_transient(MWS_OPTION . '_gateremote_simplifiedinvoice_' . $this->id);
	}


	/**
	 * @param array $params
	 * @return string
	 */
	function getUrl_CallbackPaied($params=array()) {
		$params['gw']=$this->id;
		$params['action']='mws_gate_callback';
		$params['operation']='paied';
		return MWS()->getUrl_Ajax($params);
	}

	function getUrl_CallbackCancelled($params=array()) {
		$params['gw']=$this->id;
		$params['action']='mws_gate_callback';
		$params['operation']='paied';
		return MWS()->getUrl_Ajax($params);
	}

	/**
	 * Get enabled codes.
	 * @return array
	 */
	public function getEnabledCodes($reload = false) {
		try {
			$codes = $this->sharedInstance()->doGetEnabledCodes($reload);
			return $codes;
		} catch (Exception $e) {
			return array();
		}
	}

}


/**
 * Basic class of payment gateway. It defines interface of derived instances of gateways.
 * @class MwsGatewayImpl
 *
 * @property string $id Unique identifier of the gate. It is implemented as access helper to {@link MwsGatewayMeta::id}.
 */
class MwsGatewayImpl {
	/** @var MwsGatewayMeta Contains description of the gateway instance, like id, caption etc. */
	public $meta;

	/**
	 * @param $gwMeta MwsGatewayMeta Instance/link of a descriptive meta object of the API.
	 */
	function __construct($gwMeta) {
		$this->meta = $gwMeta;
	}

	function __get($name) {
		if ($name === 'id') {
			return $this->meta->id;
		}
		return null;
	}

	/**
	 * Get ID of the gateway.
	 * @return string
	 * @deprecated
	 */
	public final function id() {
		trigger_error('Function "id()" is deprecated. Use "id" property instead.', E_USER_NOTICE);
		return $this->id;
	}

	/**
	 * Checks if gateway connection is valid.
	 * @param $auth array Authorization tokens. Typically "login" with "password" or "apikey".
	 * @return bool
	 */
	public final function isConnected($auth = null) {
		if (empty($auth))
			$auth = $this->loadAuth();
		$res = $this->doIsConnected($auth);
		return $res;
	}

	/**
	 * Load default authorization credentials for gateway. Descendants override this method to supply correct data from
	 * system options.
	 * @return array Authorization tokens.
	 */
	protected function loadAuth() {
		return array();
	}

	/**
	 * Gateway dependent check if gateway connection is valid. Override this to implement custom checks.
	 * @param $auth array Authorization tokens. Typically "login" with "password" or "apikey".
	 * @return bool
	 */
	protected function doIsConnected($auth) {
		return false;
	}

	/**
	 * Prepares remote gateway to work with MioShop. This can perform different tasks for different gates. For example
	 * the FAPI gate assures that MioShop form is present and that its ID is properly known by MioShop.
	 * @return bool Returns true when no error occurred.
	 */
	public final function syncSettings() {
		mwshoplog(__METHOD__ . ' [' . $this->id . ']', MWLL_DEBUG);
		try {
			$res = $this->doSyncSettings();
		} catch (Exception $e) {
			mwshoplog("Unexpected error when synchronizing paygate [$this->id]. " . $e->getMessage() . ' [' . get_class($e) . ']',
				MWLL_ERROR, 'paygate');
			$res = false;
		}
		return $res;
	}

	/**
	 * Synchronizes all products and shippings with the gateway. Creates necessary items within gateway that are necessary to use it
	 * for payments.
	 *
	 * IDs of non existing products are ignored.
	 *
	 * @param $productIds  array Array of product IDs that should be synced with the gateway including product variants.
	 * @param $shippingIds array Array of shipping IDs that should be synced with the gateway.
	 * @return bool|array In case of error returns false. Otherwise returns new status of synchronization of passed product
	 *                     IDs as array index by product IDs.
	 */
	public final function syncItems($productIds = array(), $shippingIds = array()) {
		mwshoplog(__METHOD__ . ' [' . $this->id . ']', MWLL_DEBUG);
		$res = array();


		// Get products
		$args = array('post_type' => MWS_PRODUCT_SLUG, 'posts_per_page' => -1, 'post_status' => 'publish');
		if (!empty($productIds)) {
			$args['post__in'] = $productIds;
		}
		$products = array();
		$loop = new WP_Query($args);
		if (count($loop->posts) > 0) {
			mwshoplog('[' . $this->id . '] count of all products = ' . count($loop->posts), MWLL_DEBUG, 'paygate');
			foreach ($loop->posts as $post) {
				try {
					$product = MwsProduct::createNew($post);
					if($product) {
						$products[] = $product;
					}
				} catch (Exception $e) {
					mwshoplog(sprintf(__('Příspěvek [%d] není platným produktem a nemůže být proto synchronizován.', 'mwshop'), $post->ID), MWLL_ERROR, 'paygate');
				}
			}
		} else {
			mwshoplog('[' . $this->id . '] count of all products = 0', MWLL_DEBUG, 'paygate');
		}

		// Get product variants
		$args = array('post_type' => MWS_VARIANT_SLUG, 'posts_per_page' => -1, 'post_status' => 'publish');
		if (!empty($productIds)) {
			$args['post__in'] = $productIds;
		}
		$variants = array();
		$loop = new WP_Query($args);
		if (count($loop->posts) > 0) {
			mwshoplog('[' . $this->id . '] count of all product variants = ' . count($loop->posts), MWLL_DEBUG, 'paygate');
			foreach ($loop->posts as $post) {
				$variant = MwsProduct::createNew($post);
				if($variant) {
					$variants[] = $variant;
				}
			}
		} else {
			mwshoplog('[' . $this->id . '] count of all product variants = 0', MWLL_DEBUG, 'paygate');
		}

		// Get shippings
		$args = array('post_type' => MWS_SHIPPING_SLUG, 'posts_per_page' => -1, 'post_status' => 'publish');
		if (!empty($shippingIds)) {
			$args['post__in'] = $shippingIds;
		}
		$shippings = array();
		$loop = new WP_Query($args);
		if (count($loop->posts) > 0) {
			mwshoplog('[' . $this->id . '] count of all shippings = ' . count($loop->posts), MWLL_DEBUG, 'paygate');
			foreach ($loop->posts as $post) {
				try {
					$shippings[] = new MwsShipping($post);
				} catch (Exception $e) {
					mwshoplog(sprintf(__('Příspěvek [%d] není platnou doručovací metodou a nemůže být proto synchronizován.', 'mwshop'), $post->ID), MWLL_ERROR, 'paygate');
				}
			}
		} else {
			mwshoplog('[' . $this->id . '] count of all shippings = 0', MWLL_DEBUG, 'paygate');
		}

		$cntProdModif = $cntProdVariantModif = $cntShipModif = 0;
		if (count($products) || count($variants) || count($shippings)) {
			$gwId = $this->id;
			$this->doBeforeSyncItems($products, $variants, $shippings);
			$gwSpecId = $this->doGetSyncSpecId();
			// Synchronize each product item.
			/** @var MwsProduct $product */
			foreach ($products as $product) {
				if($product->sync->shouldSync()) {
					try {
						$syncData = $product->sync->getSyncData($gwId, $gwSpecId);
						$syncModified = $this->doSyncProduct($product, $syncData);
						if ($syncModified) $cntProdModif++;
						$res[] = ($syncData['status'] == 'synced');
						$product->sync->setSyncData($syncData, $gwId, $gwSpecId);
					} catch (Exception $e) {
						// Silently ignore exception to continue with next product.
						mwshoplog(printf(__('Synchronizace produktu [%d] "%s" selhala.', 'mwshop'), $product->id, $product->name), MWLL_ERROR, 'paygate');
					}
				}
			}
			mwshoplog('[' . $this->id . '] ... updated products = ' . $cntProdModif, MWLL_DEBUG, 'paygate');

			// Synchronize each product variant.
			/** @var MwsProduct $variant */
			foreach ($variants as $variant) {
				if($variant->sync->shouldSync()) {
					try {
						$syncData = $variant->sync->getSyncData($gwId, $gwSpecId);
						$syncModified = $this->doSyncProduct($variant, $syncData);
						if ($syncModified) $cntProdVariantModif++;
						$res[] = ($syncData['status'] == 'synced');
						$variant->sync->setSyncData($syncData, $gwId, $gwSpecId);
					} catch (Exception $e) {
						// Silently ignore exception to continue with next product.
						mwshoplog(printf(__('Synchronizace varianty produktu [%d] "%s" selhala.', 'mwshop'), $variant->id, $variant->name), MWLL_ERROR, 'paygate');
					}
				}
			}
			mwshoplog('[' . $this->id . '] ... updated product variants = ' . $cntProdVariantModif, MWLL_DEBUG, 'paygate');

			// Synchronize each shipping item.
			/** @var MwsShipping $shipping */
			foreach ($shippings as $shipping) {
				try {
					$syncData = $shipping->sync->getSyncData($gwId, $gwSpecId);
					//array('status'=>'unsynced', 'syncId'=>-1, 'syncedAt'=>null);
					$syncModified = $this->doSyncShipping($shipping, $syncData);
					if ($syncModified) $cntShipModif++;
					$res[] = ($syncData['status'] == 'synced');
					$shipping->sync->setSyncData($syncData, $gwId, $gwSpecId);
				} catch (Exception $e) {
					// Silently ignore exception to continue with next shipping.
					mwshoplog(printf(__('Synchronizace doručovací metody [%d] "%s" selhala.', 'mwshop'), $shipping->id, $shipping->name), MWLL_ERROR, 'paygate');
				}
			}
			mwshoplog('[' . $this->id . '] ... updated shippings = ' . $cntShipModif, MWLL_DEBUG, 'paygate');
			$this->doAfterSyncItems($products, $variants, $shippings);
		}

		//Consolidates result into one value when method was called with empty arguments.
		if (empty($productIds) and empty ($shippingIds)) {
			$r2 = true;
			foreach ($res as $oneRes) $r2 = $r2 && $oneRes;
			$res = $r2;
		}

		return $res;
	}

	/** Called just before synchronization of products and shippings is started. */
	protected function doBeforeSyncItems(&$products, &$variants, &$shippings) {
	}

	/** Called just after synchronization of products an shippings is finished. */
	protected function doAfterSyncItems(&$products, &$variants, &$shippings) {
	}

	/**
	 * Real implementation of synchronization of one product into remote gateway. Ancestors should override this method.
	 * @param $product        MwsProduct Item of product to synchronize.
	 * @param $syncData       array Array with synchronization data (according to {@link MwsSync::getSyncData()}.
	 *                        It should be updated updating according to result of synchronization.
	 * @return bool Return true if synchronization $syncData changed.
	 */
	protected function doSyncProduct($product, &$syncData) {
		return false;
	}

	/**
	 * Real implementation of synchronization of one shipping into remote gateway. Ancestors should override this method.
	 * @param $shipping       MwsShipping Item of shipping to synchronize.
	 * @param $syncData       array Array with synchronization data (according to {@link MwsSync::getSyncData()}.
	 *                        It should be updated updating according to result of synchronization.
	 * @return bool Return true if synchronization $syncData changed.
	 */
	protected function doSyncShipping($shipping, &$syncData) {
		return false;
	}

	/**
	 * Real implementation of synchronization of MioShop settings into remote gateway. Ancestors should override this method.
	 * Possible task are saving some general data into remote gateway, saving some special setting into MioShop.
	 * @return bool Returns true if all passed successfully, false on some error.
	 */
	protected function doSyncSettings() {
		return false;
	}

	/** Generates HTML form elements for gateway's proprietary settings. */
	public function getSettingsForm($idPrefix, $namePrefix, $meta) {
		$code = '';
		return $code;
	}

	/**
	 * Get remote settings of the gateway.
	 * @param bool $reload Transient cache usage can be skipped by setting to true.
	 * @return array
	 * @throws MwsException On error
	 */
	public function loadRemoteSettings() {
		return array();
	}


	/**
	 * Calculates prices within the cart. Stores calculated prices back into the cart.
	 * @param $cart                      MwsCart Cart that should be recalculated.
	 * @param bool $includeShippingPrice Should calculation include shipping price?
	 * @param bool $ignoreSimplifiedInvoice If set to true, simplified invoice counting is ignored.
	 * @return bool Returns true when prices were calculated. Otherwise false.
	 */
	public function recountCart($cart, $includeShippingPrice, $ignoreSimplifiedInvoice) {
		return false;
	}

	/**
	 * Order content of the cart in a specific way for a gateway.
	 * @param $cart MwsCart
	 * @return array Returns array with several items:
	 *               "success" as bool - was operation successful?
	 *               "nextUrl" as string - URL where to redirect (in case payment gateway is involved)
	 *               "orderId" as int - on success has post ID of new MwsOrder object
	 *               "orderNum" as string - on success has number of the invoice of corresponding paygate, if supported
	 */
	protected function doMakeOrder($cart) {
		$res = array(
			'success' => false,
			'nextUrl' => '',
		);
		return $res;
	}

	/**
	 * Order content of the cart.
	 * @param $cart MwsCart
	 * @return array Returns array with several items:
	 *               "success" as bool - was operation successful?
	 *               "nextUrl" as string - URL where to redirect (in case payment gateway is involved)
	 *               "message" as string, optional - error text of the failure, localized, to be shown in UI
	 *               "orderId" as int - on success has post ID of new MwsOrder object
	 *               "orderNum" as string - on success has number of the invoice of corresponding paygate, if supported
	 */
	public function makeOrder($cart) {
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$res = array(
			'success' => false,
			'nextUrl' => '',
		);
		// Check availability, compose errors for availability, decrement counts for each product
		$stockDecremented = array();
		$errorCount = 0;
		mwshoplog(__('Kontrola a snížení stavu skladových zásob před vytvořením objednávky.', 'mwshop'), MWLL_INFO, 'order');
		/** @var MwsCartItem $cartItem */
		foreach ($cart->items->data as $cartItem) {
			if ($cartItem->checkAvailability(true)) {
				// Remember items whose stock has been successfully decremented
				if($cartItem->product->stockEnabled) // Product existence is assured within checkAvailability
					$stockDecremented[$cartItem->productId] = $cartItem->count;
			} else {
				$errorCount++;
			}
		}

		if ($errorCount === 0) {
			$res = $this->doMakeOrder($cart);
			if ($res['success']) {
				unset($stockDecremented);
			}
		} else {
			$cart->availabilityErrorsCount = $errorCount;
			$res['message'] = __('Omlouváme se, objednávku se nepodařilo vytvořit. Některé položky nejsou dostupné.', 'mwshop');
		}

		if(isset($stockDecremented)) {
			// Refund decremented stock items
			mwshoplog(__('Objednávku se nepodařilo vytvořit. Vrácím zásoby rezervované objednávkou na sklad.', 'mwshop'), MWLL_INFO, 'order');
			foreach ($cart->items->data as $cartItem) {
				$productId = $cartItem->productId;
				if (isset($stockDecremented[$productId])) {
					$cartItem->product->updateStockCount($stockDecremented[$productId], MwsStockUpdate::Inc);
				}
			}
		}

		return $res;
	}

	/**
	 * Get array of purposes defined within gateway.
	 * @return array Array of object with field: id (purpose id), checkbox_label (text description), link_href (url),
	 * link_label (text for url), is_primary (bool, if this is primary and therefore necessary).
 */
	public function doGetPurposes() {
		return array();
	}

	/**
	 * Get array of payment methods supported by the gateway.
	 * @return array Array of items, which are values of {@link MwsPayType} enumeration.
	 */
	public function doGetSupportedPayTypes() {
		return array();
	}

	/**
	 * Get array of payment methods enabled by default in the gateway.
	 * @return Array of items, which are values of {@link MwsPayType} enumeration.
	 */
	public function doGetDefaultEnabledPayTypes() {
		return array();
	}

	/**
	 * This method should handle changes of general gate settings specific for the gateway. Values of HTML inputs generated in
	 * {@link getSettingsForm()} are accessible in <code>$_REQUEST</code> array, typically in
	 * "<code>$_REQUEST['gate_settings'][$this->id]</code>" variable.
	 * @return bool If gate should be resynced then method should return <code>true</code>.
	 */
	public function doSettingsChanged_Form() {
		return false;
	}

	/**
	 * This method should handle changes of payment method specific for the gateway. Values are typically accessible in
	 * <code>$_REQUEST</code> array as "<code>$_REQUEST['gate_settings'][$this->id]['payments']</code>" variable.
	 * @return bool If gate should be resynced then method should return <code>true</code>.
	 */
	public function doSettingsChanged_Payments() {
		return false;
	}

	/**
	 * Method to handle callback from the gateway. Ancestor should mark corresponding order as paid.
	 * Incoming data are present in $_REQUEST variable.
	 * @return MwsOrder Method returns order object that has been paid. If payment can not be proved
	 *                  then null is returned.
	 * @throws MwsException On failure method should throw an exception with message describing case of the error.
	 */
	public function orderPaied() {
		throw new MwsException('Not implemented.');
	}

	/**
	 * Method to handle callback from the gateway. Ancestor should mark corresponding order as cancelled.
	 * Incoming data are present in $_REQUEST variable.
	 * @return MwsOrder Method returns order object that has been cancelled. If operation can not be finished
	 *                  then null is returned.
	 * @throws MwsException On failure method should throw an exception with message describing case of the error.
	 */
	public function orderCancelled() {
		throw new MwsException('Implementation pending.');
	}

	/**
	 * Get corresponding order object from $_REQUEST array. Translate it into {@link MwsOrder}.
	 * @return MwsOrder|null
	 */
	public function getOrderFromThankYou() {
		return null;
	}

	/**
	 * Create caching object for order for the gateway.
	 * @param $order              MwsOrder Order for which the caching object should be created.
	 * @param null $preloadedData Optionally preloaded data. Can be used when creating multiple caching object at once.
	 * @return MwsOrderGate Caching object for specific gateway.
	 */
	public function loadOrderGate($order, $preloadedData = null) {
	}

	/**
	 * Load gate live data for several orders at once. Default implementation calls {@link loadOrderGate()} separately for
	 * each order.
	 * @param array $orders List of {@link MwsOrder} instances.
	 */
	public function preloadOrdersGateLive($orders) {
		/** @var MwsOrder $order */
		foreach ($orders as $order) {
			$this->loadOrderGate($order);
		}
	}

	/**
	 * Get specific synchronization ID for the gateway. This is generally used to differentiate synchronization
	 * data among multiple account into same gateway. There for when new set of synchronization data should be
	 * created (like multiple forms or accounts etc.), new unique specific synchronization ID should be returned.
	 * This ID should be same for the same destination account within the gateway.
	 * @return string
	 */
	public function doGetSyncSpecId() {
		return '';
	}

	/**
	 * Get remote paytypes of the gateway.
	 * @return array List of {@link MwsPayType} methods that are enabled.
	 * @throws MwsException On error
	 */
	public function loadRemotePayTypes() {
		return array();
	}

	/**
	 * Get remote allow simplified invoice.
	 * @return bool True when allowd
	 * @throws MwsException On error
	 */
	public function loadRemoteUseSimplifiedInvoice() {
		return false;
	}

	/**
	 * Get array of enabled codes.
	 * @param bool $reload Set this to true when cache should not be used.
	 * @return array List of {@link MwsProductCode}
	 * @throws MwsException On error.
	 */
	public function doGetEnabledCodes($reload = false) {
		return array();
	}

}

/*
Register supported gateways. This is done only in case this file was really loaded. Otherwise whole gateway part
is not loaded.
*/
add_filter('mws_gateway_register', 'mwsGetGateways');
function mwsGetGateways($gws) {
	/** @var $gws MwsGateways */
	$gws->registerGw(
		new MwsGatewayMeta('fapi', __('FAPI', 'mwshop'),
			'MwsGatewayImpl_Fapi', __DIR__.'/gateway_fapi.php')
	);
}