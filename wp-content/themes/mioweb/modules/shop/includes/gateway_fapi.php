<?php
/**
 * Implementation of FAPI gateway.
 * User: kuba
 * Date: 24.02.16
 * Time: 12:15
 */


define('AUTOMANAGED_TAG', 'automanaged');

class MwsGatewayImpl_Fapi extends MwsGatewayImpl {
	/** @var MwShop\FapiClient\FapiClient */
	private $api;
	private $storedItems = array();
	private $itemsToJoin = array();
	/** @var array Array of cached forms received from FAPI, indexed by form id. In memory cache. */
	private $cachedFormResp = array();

	function __construct($gwMeta) {
		parent::__construct($gwMeta);
		require_once(MWS_PATH_LIBS.'/fapi/autoload.php');
	}

	/**
	 * @return \MwShop\FapiClient\FapiClient
	 */
	public function getApi($auth=null) {
		if(!isset($this->api)) {
			if (is_null($auth))
				$auth = $this->loadAuth();
			$httpCli = new MwShop\HttpClient\CurlHttpClient();
//			$path = get_theme_root()
//				. '/mioweb-child/modules/licence/libs/nette.phar';
//			require_once($path);
//			\Tracy\Debugger::enable(\Tracy\Debugger::DEVELOPMENT);

			// Log FAPI API communication?
			if (defined('MW_LOG_LEVEL_FAPI') && MW_LOG_LEVEL_FAPI !== MWLL_DISABLED) {
				$httpCli = new MwShop\HttpClient\LoggingHttpClient($httpCli);
				MwLogger::instance()->setLevel(MWLS_FAPI, MW_LOG_LEVEL_FAPI);
			}

			$this->api = new MwShop\FapiClient\FapiClient($auth['login'], $auth['key'],
				(defined('MW_TEST_GATEWAY_NOT_ACCESSIBLE') && MW_TEST_GATEWAY_NOT_ACCESSIBLE ? 'https://nonexisten' : 'https://api.fapi.cz/'),
				$httpCli
			  , array('timeout'=>20, 'connect_timeout'=>10)
			);
		}
		return $this->api;
	}

	public function loadRemoteSettings() {
		try {
			$api = $this->getApi();
			$stgs = $api->getSettings();
			return $stgs;
		}	catch (Exception $e) {
			throw new MwsException(__('Globální nastavní FAPI se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	/**
	 * Load form definition from Fapi. Use in memory cache.
	 * @param $formId
	 * @param bool $reload
	 * @throws MwsException
	 */
	private function getFormFromFapi($formId, $reload = false) {
		if ($reload || !isset($this->cachedFormResp[$formId])) {
			if (!$formId) {
				throw new MwsException(__('Eshop nemá zvolen FAPI formulář.'));
			}
			$api = $this->getApi();
			$this->cachedFormResp[$formId] = $api->getForm($formId);
		}
		return $this->cachedFormResp[$formId];
	}

	public function loadRemotePayTypes() {
		try {
			$gateStg = $this->meta->loadSettings();
			$formId = isset($gateStg['form']['id']) ? $gateStg['form']['id'] : 0;
			$form = $this->getFormFromFapi($formId);

			$allowGoPay = $form['allow_gopay'];
			$allowed = array();
			$map = array(
//				MwsPayType::Cash => 'allow_cash',
				MwsPayType::Cod => 'allow_collect_on_delivery',
				MwsPayType::Wire => 'allow_wire',
//				MwsPayType::Voucher' => 'allow_voucher',
				// GoPay dependant
				MwsPayType::PayPal => 'allow_gopay_paypal',
				MwsPayType::Sms => 'allow_gopay_sms',
				MwsPayType::WireOnline => 'allow_gopay_wire',
				MwsPayType::CreditCard => 'allow_gopay_card',
			);
			foreach ($map as $type => $name) {
				if (isset($form[$name]) && $form[$name]) {
					if (in_array($type, array(MwsPayType::PayPal, MwsPayType::Sms, MwsPayType::WireOnline, MwsPayType::CreditCard))) {
						if ($allowGoPay) {
							$allowed[] = $type;
						}
					} else {
						$allowed[] = $type;
					}
				}
			}

			return $allowed;
		} catch (MwsException $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst. ' . $e->getMessage(), 'mwshop'));
		} catch (Exception $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function loadRemoteUseSimplifiedInvoice() {
		try {
			$gateStg = $this->meta->loadSettings();
			$formId = isset($gateStg['form']['id']) ? $gateStg['form']['id'] : 0;
			$form = $this->getFormFromFapi($formId);

			return $form['allow_simplified'];
		} catch (MwsException $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst. ' . $e->getMessage(), 'mwshop'));
		} catch (Exception $e) {
			throw new MwsException(__('Nastavení FAPI formuláře se nepodařilo načíst.', 'mwshop'), 0, $e);
		}
	}

	public function doGetEnabledCodes($reload = false) {
		$stgs = $this->meta->getRemoteSettings($reload);
		$codes = array();
		$codes[] = MwsProductCode::Filing;
		if (isset($stgs['accounting_codes']) && $stgs['accounting_codes']) {
			$codes[] = MwsProductCode::Financial;
		}
		if (isset($stgs['pohoda_accounting']) && $stgs['pohoda_accounting']) {
			$codes[] = MwsProductCode::Assignment;
		}
		if (isset($stgs['pohoda_centre']) && $stgs['pohoda_centre']) {
			$codes[] = MwsProductCode::Center;
		}
		if (isset($stgs['pohoda_store']) && $stgs['pohoda_store']) {
			$codes[] = MwsProductCode::Stock;
		}
		if (isset($stgs['pohoda_stock_item']) && $stgs['pohoda_stock_item']) {
			$codes[] = MwsProductCode::StockItem;
		}
		return $codes;
	}

	function isUsingCZK() {
		static $cache;
		if(!$cache) {
			$currency = MWS()->getCurrency('key');
			$cache = (MwsCurrency::czk == $currency);
		}
		return $cache;
	}

	/**
	 * Get price converted into specified currency. Reflect {@link MwsPrice::vatIncluded} setting.
	 * @param MwsPrice $price
	 * @return float
	 */
	function getPriceForSyncAsCurrency($price, $currency) {
		$priceVal = ($price->vatIncluded ? $price->priceVatIncluded : $price->priceVatExcluded);
		if($price->currency != $currency) {
			$priceVal = $priceVal * MWS()->getCurrencyConversionRate($price->currency, $currency);
		}
		return $priceVal;
	}

	/**
	 * Get price converted into CZK. Reflect {@link MwsPrice::vatIncluded} setting.
	 * @param MwsPrice $price
	 * @return float
	 */
	function getPriceForSyncCZK($price) {
		return $this->getPriceForSyncAsCurrency($price, MwsCurrency::czk);
	}

	/**
	 * Get price converted into EUR. Reflect {@link MwsPrice::vatIncluded} setting.
	 * @param MwsPrice $price
	 * @return float
	 */
	function getPriceForSyncEUR($price) {
		return $this->getPriceForSyncAsCurrency($price, MwsCurrency::eur);
	}

	/**
	 * Look for valid country code within passed array, field "country". Raise exception on error.
	 * @param $address Array with address information. Field "country" is checked.
	 * @return string ISO 3166-1 country code (2 capital letters)
	 * @throws MwsException On validation errors
	 */
	function getCountryCode($address) {
		if(isset($address['country'])) {
			if(strlen($address['country']) == 2 && array_key_exists($address['country'], MWS()->getSupportedCountries())) {
				return $address['country'];
			} else {
				throw new MwsException(sprintf('Invalid country code "%s".', $address['country']));
			}
		} else {
			return $this->isUsingCZK()
				? 'CZ'
				: 'SK';
		}
	}

	protected function doBeforeSyncItems(&$products, &$variants, &$shippings) {
		//Get list of present itemtpls within FAPI.
		$api=$this->getApi();
		$this->storedItems = $api->getItemTemplates(array(
			'mioweb_eshop' => true,
//			'mioweb_eshop_url' => 'http://mujweb.cz/slozka/',
		));
		//Only shop items are relevant.
		$this->storedItems = array_filter($this->storedItems, function($item){ return ($item['mioweb_eshop']); });
		$this->itemsToJoin = array();
	}

	protected function doAfterSyncItems(&$products, &$variants, &$shippings) {
		//Merge assigned templates with newly added templates
		$stgs=$this->meta->loadSettings();
		$oldItems=isset($stgs['form']['items'])?$stgs['form']['items']:array();
		$oldIds = array_map(function($item){return $item['item_template'];},$oldItems);
		$newIds = $this->itemsToJoin;

		//Check if new IDs are already assigned with the form. Continue only in case some items should be added.
		$shouldBeAdded = array_diff($newIds, $oldIds);
		if(empty($shouldBeAdded))
			return;

		//Create new array for API call. Preserve original linking IDs of FAPI form.
		$newItems = array_map(function($item) use ($oldItems) {
			$oldItem = array_filter($oldItems,
				function($orig) use ($item) {
					return ($orig['item_template']==$item);
				});
			if(empty($oldItem))
				return array('item_template'=>$item);
			else
				return reset($oldItem);
		}, array_unique(array_merge(/*$oldIds,*/ $newIds))
		);
		//Update form
		$formId = $stgs['form']['id'];
		try {
			mwshoplog(__METHOD__. ' updating FAPI form ' . $formId, MWLL_DEBUG, 'paygate');
			$form = $this->getApi()->updateForm($formId, array('items' => $newItems));
			//Remember new status of FAPI form.
			$stgs['form']=$form;
			$this->meta->saveSettings($stgs);
			mwshoplog(sprintf(__('FAPI formulář [%d] aktualizování', 'mwshop'), $formId), MWLL_INFO, 'paygate');
		} catch (Exception $e) {
			//TODO Report synchronization error
			mwshoplog(sprintf(__('Chyba při aktualizaci FAPI formuláře [%d]:', 'mwshop'), $formId) . ' ' . $e->getMessage() . ' ' .__METHOD__, MWLL_ERROR, 'paygate');
			mwnotice();
		};
	}

	protected function doSyncProduct($product, &$syncData) {
		if(empty($product))
			return false;

		//Is sync necessary?
		$hash = $product->sync->calcSyncHash();
		$doSync = ($hash!==$syncData['hash'] || $syncData['status']!='synced');
		$id = isset($syncData['id']) ? $syncData['id'] : 0;

		//Perform sync
		if(!$doSync) {
			$this->itemsToJoin[] = $id; //mark item template as synced; usesful in case when synchronization of products
			// terminates prematurely
			return false;
		}
		$found = array_filter($this->storedItems, function($item) use (&$id) {
			return ($item['id'] == $id);
		});
		$api=$this->getApi();
		//TODO Value of VAT, VAT inclusion, electronically supplied
		$data = array(
			'name' => $product->name,
//			'description' => 'Nepovinný popis položky',
			'price_czk' => $this->getPriceForSyncCZK($product->price),
			'price_eur' => $this->getPriceForSyncEUR($product->price),
			'vat' => $product->price->getVatPercentage(),
			'including_vat' => $product->price->vatIncluded,
			'count' => 1,
			'electronically_supplied_service' => ($product->type==MwsProductType::Electronic),
			'mioweb_eshop' => true,
			'mioweb_eshop_url' => get_home_url(),

			'code' => $product->codes->getCode(MwsProductCode::Filing),
			'accounting_code' => $product->codes->getCode(MwsProductCode::Financial),
			'pohoda_accounting' => $product->codes->getCode(MwsProductCode::Assignment),
			'pohoda_centre' => $product->codes->getCode(MwsProductCode::Center),
			'pohoda_store' => $product->codes->getCode(MwsProductCode::Stock),
			'pohoda_stock_item' => $product->codes->getCode(MwsProductCode::StockItem),
		);
		$item = null;
		try {
			if (empty($found)) {
				mwshoplog(__METHOD__ .' creating FAPI template for product ' . $product->name . ' ['.$product->id.']', MWLL_DEBUG, 'paygate');
				$item = $api->createItemTemplate($data);
				$id = $item['id'];
				mwshoplog(sprintf(__('Vytvořena nová FAPI šablona produktu "%s" [%d, fapiItemTplId=%d]', 'mwshop'), $product->name, $product->id, $id),
					MWLL_INFO, 'paygate');
			} else {
				mwshoplog(__METHOD__ .' updating FAPI template of product ' . $product->name . ' ['.$product->id.', fapiItemTplId='.$id.']',
					MWLL_DEBUG, 'paygate');
				$item = $api->updateItemTemplate($id, $data);
				mwshoplog(sprintf(__('Aktualizována FAPI šablona produktu "%s" [%d, fapiItemTplId=%d]', 'mwshop'), $product->name, $product->id, $id),
					MWLL_INFO, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog(sprintf(__('Chyba při aktualizaci FAPI šablony produktu [%d]:', 'mwshop'), $product->id) . ' ' . $e->getMessage() . ' ' .__METHOD__, MWLL_ERROR, 'paygate');
		}
		if(empty($item))
			return false;

		//Update sync status
		$syncData['id'] = $id;
		$syncData['status'] = 'synced';
		$syncData['when'] = time();
		$syncData['hash'] = $hash;

		$this->itemsToJoin[] = $id;

		return true;
	}

	protected function doSyncShipping($shipping, &$syncData) {
		if(empty($shipping))
			return false;

		//Is sync necessary?
		$hash = $shipping->sync->calcSyncHash();
		$doSync = ($hash!==$syncData['hash'] || $syncData['status']!='synced');
		$id = isset($syncData['id']) ? $syncData['id'] : 0;

		//Perform sync
		if(!$doSync) {
			$this->itemsToJoin[] = $id; //mark item template as synced; usesful in case when synchronization of shippings
			// terminates prematurely
			return false;
		}
		$found = array_filter($this->storedItems, function($item) use (&$id) {
			return ($item['id'] == $id);
		});
		$api=$this->getApi();
		//TODO Value of VAT, VAT inclusion, electronically supplied
		$data = array(
			'name' => $shipping->name,
//			'description' => 'Nepovinný popis položky',
			'price_czk' => $this->getPriceForSyncCZK($shipping->price),
			'price_eur' => $this->getPriceForSyncEUR($shipping->price),
			'vat' => $shipping->price->getVatPercentage(),
			'including_vat' => $shipping->price->vatIncluded,
			'count' => 1,
			'electronically_supplied_service' => false,
			'mioweb_eshop' => true,
			'mioweb_eshop_url' => get_home_url(),

			'code' => $shipping->codes->getCode(MwsProductCode::Filing),
			'accounting_code' => $shipping->codes->getCode(MwsProductCode::Financial),
			'pohoda_accounting' => $shipping->codes->getCode(MwsProductCode::Assignment),
			'pohoda_centre' => $shipping->codes->getCode(MwsProductCode::Center),
			'pohoda_store' => $shipping->codes->getCode(MwsProductCode::Stock),
			'pohoda_stock_item' => $shipping->codes->getCode(MwsProductCode::StockItem),
		);
		$item = null;
		try {
			if (empty($found)) {
				mwshoplog(__METHOD__ .' creating FAPI template for shipping ' . $shipping->name . ' ['.$shipping->id.']', MWLL_DEBUG, 'paygate');
				$item = $api->createItemTemplate($data);
				$id = $item['id'];
				mwshoplog(sprintf(__('Vytvořena nová FAPI šablona doručení "%s" [%d, fapiItemTplId=%d]', 'mwshop'), $shipping->name, $shipping->id, $id),
					MWLL_INFO, 'paygate');
			} else {
				mwshoplog(__METHOD__ .' updating FAPI template of shipping ' . $shipping->name . ' ['.$shipping->id.', fapiItemTplId='.$id.']',
					MWLL_DEBUG, 'paygate');
//				mwshoplog(__METHOD__ . '  FAPI request: update id=' . $id . ' ' . json_encode($data, JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES),
//					MWLL_DEBUG, 'fapi');
				$item = $api->updateItemTemplate($id, $data);
				mwshoplog(sprintf(__('Aktualizována FAPI šablona doručení "%s" [%d, fapiItemTplId=%d]', 'mwshop'), $shipping->name, $shipping->id, $id),
					MWLL_INFO, 'paygate');
			}
		} catch (Exception $e) {
			mwshoplog(sprintf(__('Chyba při aktualizaci FAPI šablony doručení [%d]:', 'mwshop'), $shipping->id) . ' ' . $e->getMessage() . ' ' .__METHOD__, MWLL_ERROR, 'paygate');
		}
		if(empty($item))
			return false;

		//Update sync status
		$syncData['id'] = $id;
		$syncData['status'] = 'synced';
		$syncData['when'] = time();
		$syncData['hash'] = $hash;

		$this->itemsToJoin[] = $id;

		return true;
	}

	protected function doSyncSettings() {
		//Create or update existing FAPI form.
		$this->meta->loadSettings();
		$formId = isset($this->meta->gateStgs['form']['id']) ? $this->meta->gateStgs['form']['id'] : 0;
		$api = $this->getApi();

		// Check whether FAPI form really exists in FAPI.
		$form = null;
		if($formId) {
			try {
				$form = $api->getForm($formId);
				if(!(isset($form['id']) && $form['id']==$formId && $form['deleted']==false)) {
					$formId = 0;
					$form = null;
				}
				// Update of current form value withing gate settings by real setting at the gate.
				$this->meta->gateStgs['form'] = $form;
				//TODO Is this for our web? How to cope with DNS aliases, web redirections etc.? Is it necessary?
				//TODO If we allow switching to form of another shop, then URL of form SHOULD BE UPDATED appropriately.
			} catch (Exception $e) {
				$formId = 0;
			}
		}

		$callbackUrl =
//			'http://requestb.in/oyj2thoy'
		  MWS()->getUrl_Ajax()
		  ;
		$trigger_ordered = array(
			'event' => 'paid',
			'action' => 'url-notification',
			'notification_url' => add_query_arg(
				array(
					AUTOMANAGED_TAG=>1,
					'action'=>'mws_gate_callback',
					'gw'=>$this->id,
					'operation'=>'paid',
				), $callbackUrl
			)
		);
		$trigger_cancelled = array(
			'event' => 'cancelled',
			'action' => 'url-notification',
			'notification_url' => add_query_arg(
				array(
					AUTOMANAGED_TAG=>1,
					'action'=>'mws_gate_callback',
					'gw'=>$this->id,
					'operation'=>'cancelled',
				), $callbackUrl
			)
		);
		$orderUrl = MWS()->getUrl_Cart(MwsOrderStep::ThankYou);
		$thanksUrl = add_query_arg(array('success'=>1,'gw'=>$this->id), $orderUrl);
		$errorUrl = add_query_arg(array('success'=>0,'gw'=>$this->id), $orderUrl);
		$purposePrimary = array(
			'checkbox_label' => _('Odesláním objednávky nám dáváte souhlas se zpracováním osobních údajů za účelem ' .
			                  'zpracování Vaší objednávky.', 'mwshop'),
			'link_label' => 'Zásady zpracování osobních údajů',
			'link_href' => MWS()->getUrl_PersonalDataProtection(),
			'is_primary' => true,
		);


		// FAPI form does not exist or is not accessible. Create one.
		if(!$form) {
			try {
				mwshoplog(__METHOD__.' creating FAPI form', MWLL_DEBUG, 'paygate');
				$form = $api->createForm(array(
					'name' => sprintf(_x('MioWeb - %s', 'name of FAPI form', 'mwshop'), get_bloginfo('name'))
						. ' [' . date('Y/m/d G:i') . ']',
					'thanks_url' => $thanksUrl,
					'error_url' => $errorUrl,
					'mioweb_eshop' => true,
					'mioweb_eshop_url' => get_home_url(),
					'allow_region_cz' => true,
					'allow_region_sk' => true,
					'allow_region_eu' => true,
					'allow_region_world' => true,
//					'currency'=> ($this->isUsingCZK() ? 'always-czk' : 'always-eur'),
					'triggers' => array(
						$trigger_ordered, $trigger_cancelled,
					),
					'allow_cash' => true,
					'allow_collect_on_delivery' => true,
					'allow_wire' => true,
//					'allow_voucher' => true,
//					'allow_gopay' => true,
//					'gopay_connection' => 1234,
//					'allow_gopay_paypal' => true,
//					'allow_gopay_sms' => true,
//					'allow_gopay_wire' => true,
//					'allow_gopay_card' => true,
					'purposes' => array($purposePrimary),
				));
				$this->meta->gateStgs['form'] = $form;
				$formId = $form['id'];
				mwshoplog(sprintf(__('Vytvořen nový FAPI formulář [%d]', 'mwshop'), $formId), MWLL_INFO, 'paygate');
			} catch (Exception $e) {
				$formId = 0;
				mwshoplog(sprintf(__('Chyba při vytváření FAPI formuláře:', 'mwshop')) . ' ' . $e->getMessage() . ' ' .__METHOD__, MWLL_ERROR, 'paygate');
			}
		} else {
			// Updating form
			mwshoplog(__METHOD__." updating FAPI form [$formId]", MWLL_DEBUG, 'paygate');
			// $form is updated during check of its presence.

			//Update hooks, callbacks, triggers, purposes.
			try {
				// Get current settings
				$oldTriggers = isset($form['triggers']) ? $form['triggers'] : array();
				// Update automatically managed triggers, preserve other triggers.
				$newTriggers = array($trigger_ordered, $trigger_cancelled);
				foreach ($oldTriggers as $trg) {
					//skip automanaged triggers
					if($trg['action']==='url-notification' && (strpos($trg['notification_url'], AUTOMANAGED_TAG) !== false))
						continue;

					$newTriggers[] = $trg;
				}

				$purposes = isset($form['purposes']) && is_array($form['purposes']) ? $form['purposes'] : array();
				//Add primary purpose, if it does not exists. Leave existing primary purpose if it is present.
				$primaryPurposeIdx = -1;
				foreach ( $purposes as $idx => $purpose ) {
					if (isset($purpose['is_primary']) && $purpose['is_primary'] ) {
						$primaryPurposeIdx = $idx;
						break;
					}
				}
				if ( $primaryPurposeIdx === -1 ) {
					$purposes[] = $purposePrimary;
					mwshoplog( sprintf( __( 'Přidávám primární účel do formuláře FAPI [%d]', 'mwshop' ), $formId ), MWLL_INFO, 'paygate' );
				} else {
					// Propagate URL of purposes
//					if (isset( $purposes[ $primaryPurposeIdx ]['link_label'] ) && !empty( $purposes[ $primaryPurposeIdx ]['link_label'])) {
//						$purposes[$primaryPurposeIdx]['link_href'] = MWS()->getUrl_PersonalDataProtection();
//						mwshoplog( sprintf( __( 'Aktualizuju URL se zásadami do formuláře FAPI [%d]', 'mwshop' ), $formId ), MWLL_INFO, 'paygate' );
//					}
				}

				// Store new settings
				$form = $api->updateForm($formId, array(
//					'name' => sprintf(_x('MioWeb - %s', 'name of FAPI form', 'mwshop'), get_bloginfo('name')),
					'thanks_url' => $thanksUrl,
					'error_url' => $errorUrl,
					'mioweb_eshop' => true,
					'mioweb_eshop_url' => get_home_url(),
//					'allow_region_cz' => true,
//					'allow_region_sk' => false,
//					'allow_region_eu' => false,
//					'allow_region_world' => false,
//					'currency'=>($this->isUsingCZK() ? 'always-czk' : 'always-eur'),
					'triggers' => $newTriggers,

//					'allow_cash' => true,
//					'allow_collect_on_delivery' => true,
//					'allow_wire' => true,
//					'allow_voucher' => true,
//					'allow_gopay' => true,
//					'gopay_connection' => 1234,
//					'allow_gopay_paypal' => true,
//					'allow_gopay_sms' => true,
//					'allow_gopay_wire' => true,
//					'allow_gopay_card' => true,

					'purposes' => $purposes,

				));
				$this->meta->gateStgs['form'] = $form;
				mwshoplog(sprintf(__('FAPI formulář [%d] aktualizován', 'mwshop'), $formId), MWLL_INFO, 'paygate');
			} catch (Exception $e) {
				$formId = 0;
				mwshoplog(sprintf(__('Chyba při aktualizaci FAPI formuláře [%d]:', 'mwshop'), $formId) . ' ' . $e->getMessage() . ' ' .__METHOD__, MWLL_ERROR, 'paygate');
			}
		}

		//Update mwshop settings.
//		if ($origFormId != $formId)
			$this->meta->saveSettings();

		return (!empty($formId));
	}

	protected function loadAuth() {
		//Load default GW authorization according to gate type.
		$meta = get_option('ve_connect_fapi');
		$login = isset($meta)&&isset($meta['connection'])&&isset($meta['connection']['login']) ? $meta['connection']['login']:'';
		$key = isset($meta)&&isset($meta['password']) ? $meta['password']:'';
		return array('login'=>$login, 'key'=>$key);
	}

	protected function doIsConnected($auth) {
		$res = false;
		$api = $this->getApi($auth);
		try {
			$user = $api->getCurrentUser();
			$res = isset($user['id']) && is_int($user['id']);
		} catch (Exception $e) {
		}
		return $res;

	}

	/**
	 * @param $idPrefix
	 * @param $namePrefix
	 * @param $meta
	 *
	 * @return string
	 */
	public function getSettingsForm($idPrefix, $namePrefix, $meta) {
		$htmlSpanCreateNew = '<span class="cms_info_box_gray">'.__('(nový formulář eshopu bude vytvořen po uložení nastavení)','mwshop').'</span>';
		$gateStg = $this->meta->loadSettings();
		$formId = isset($gateStg['form']['id']) ? $gateStg['form']['id'] : null;
		$code = '';

		try {
			$api = $this->getApi();

			// FAPI global settings
			$code .= '<div class="mws_fapi_global_settings">' .
			         '<h4>'. __('Nastavení FAPI účtu', 'mwshop') . '</h4>' .
			         '<div class="cms_description">' .
			         sprintf(
				         __( 'Nastavení této sekce se přejímají z propojeného FAPI účtu. ' .
				             'Nastavení %s. ' .
				             '<br />' .
				             'Konfigurace z FAPI je do eshopu načítána vždy při otevření "Nastavení eshopu", ve kterém se právě nacházíte. ' .
				             'Nezapomeňte proto pro změně nastavení ve FAPI otevřít "Nastavení eshopu" a zkontrolovat, že eshop ' .
				             'se o změnách ve FAPI dozvěděl.', 'mwshop' ),
				         '<a href="https://web.fapi.cz/settings/?projectId=all" class="" id="' . $idPrefix . '_settings" target="'
				         . MW_HREF_TARGET_SHARED_ADMIN_EDIT . '">'
				         . __( 'upravíte přímo ve FAPI', 'mwshop' ) . '</a>'
			         ) .
			         '</div>'
			;
			try {
				$rmtSettings = $this->meta->getRemoteSettings(true);

				$options = array(
					'accounting_codes' => 'Účetní kód',
					'pohoda_accounting' => 'Kód předkontace (Pohoda)',
					'pohoda_centre' => 'Kód střediska (Pohoda)',
					'pohoda_store' => 'Kód skladu (Pohoda)',
					'pohoda_stock_item' => 'Kód skladové položky (Pohoda)',
				);

				$code .= '<span class="label">' . __('Používat kódy položek', 'mwshop') . '</span>';
				foreach ($options as $key=>$label) {
					$code .= '<div><input type="checkbox" disabled="disabled"'. (isset($rmtSettings[$key]) && $rmtSettings[$key] ? ' checked="checked"' : '') .' />'
						. esc_html($label) . '</div>';
				}
			} catch (MwsException $e) {
				$code .= '<span class="mws_admin_error">' . _x('Nelze načíst nastavení z FAPI.', 'FAPI gate - unreachable FAPI settings', 'mwshop') . '</span>';
			}

			$code .= '</div>';

			// FAPI form selector
			$forms = $api->getForms(array(
				'mioweb_eshop' => true,
			));
			$code .= '<span class="label">'.__('FAPI formulář', 'mwshop').'</span>';
			$selected = false;
			$code .= '<select class="" name="' . $namePrefix .'[form]" id="' . $idPrefix . '_form">';
			$options='';
			$stgsForms = array();
			foreach ($forms as $form) {
				// Check cached form against FAPI counterpart
				if ( ( $formId == $form['id'] ) && ($form !== $gateStg['form'])) {
					// There is some difference.
					mwshoplog(
						sprintf(__('FAPI formulář [%s] se liší od verze uložené v eshopu. Aktualizuji cache eshopu daty z FAPI.', 'mwshop'),
							$formId)
					);
					$gateStg['form'] = $form;
					$this->meta->saveSettings($gateStg);
				}

				// Prepare data for selector
				$selected = $selected || ($formId == $form['id']);
				$options .= '<option'
					. ' value="' . $form['id'] . '" '
					. ($formId == $form['id'] ? ' selected="selected"' : '')
					. '>'
					. ( $formId == $form['id'] ? '* ' : '') . $form['name'].' ('.preg_replace('#^https?://#', '', $form['mioweb_eshop_url']).')'
					. '</option>';

				$stgsForms[$form['id']] = array(
//						'allow_voucher' => $form['allow_voucher'],
					'allowGoPay' => $form['allow_gopay'],
					'payTypes' => array(
//							MwsPayType::Cash => $form['allow_cash'],
						MwsPayType::Cod => $form['allow_collect_on_delivery'],
						MwsPayType::Wire => $form['allow_wire'],
						// GoPay dependant
						MwsPayType::PayPal => $form['allow_gopay'] && $form['allow_gopay_paypal'],
						MwsPayType::Sms => $form['allow_gopay'] && $form['allow_gopay_sms'],
						MwsPayType::WireOnline => $form['allow_gopay'] && $form['allow_gopay_wire'],
						MwsPayType::CreditCard => $form['allow_gopay'] && $form['allow_gopay_card'],
					),
					'simplifiedInvoice' => $form['allow_simplified'],
					'purposes' => $form['purposes'],
				);
			}
			if(!$selected) {
				$code .= '<option value="" selected="selected" disabled="disabled">' .
					__('(neexistuje žádný FAPI formulář, je potřeba vytvořit nový)', 'mwshop') . '</option>';
			}
			$code .= '<option value="-1">' . __('(vytvořit nový eshop formulář)', 'mwshop') . '</option>';
			$code .= $options;
			$code .= '</select>';
			$gatewayId = MWS()->gateways()->getDefault()->id;
			$code .= '<div class="mws_fapi_form_buttons">';
				$code .= ' <a href="https://web.fapi.cz/forms/?projectId=all" class="mws_fapi_form_setup cms_button_secondary"
								title="' . __('Otevře editaci zvoleného FAPI formuláře', 'mwshop') . '" 
								target="'
				         . MW_HREF_TARGET_SHARED_ADMIN_EDIT . '">'
				         . __( 'Konfigurovat', 'mwshop' )
				         . '</a>';
				$code .= ' <a href="#" class="mws_fapi_form_sync cms_button_secondary"
								title="' . __('Spustí synchronizaci s FAPI, která předá důležitá data mezi FAPI a eshopem.', 'mwshop') . '" 
								onclick=\'mwsSynchronizeGateway(jQuery(this), ' . json_encode($gatewayId) . ', mwsGatewaySyncNonce); return false;\' 
								target="'
				         . MW_HREF_TARGET_SHARED_ADMIN_EDIT . '">'
				         . __( 'Synchronizovat', 'mwshop' )
				         . '</a>';
				$code .= ' <a href="#" class="mws_fapi_form_create cms_button_secondary"
								title="' . __('Vytvoří nový formulář ve FAPI.', 'mwshop') . '"
								onclick=\'mwsSynchronizeGateway(jQuery(this), ' . json_encode( $gatewayId ) . ', mwsGatewaySyncNonce); return false;\' 
								target="'
				         . MW_HREF_TARGET_SHARED_ADMIN_EDIT . '">'
				         . __( 'Vytvořit', 'mwshop' )
				         . '</a>';
			$code .= '</div>';
			$code .= '<div class="cms_description">' .
			         __( 'Z výběrového seznamu můžete ' .
			             'zvolit již existující formulář (seznam jeho položek bude během synchronizace ' .
			             'nahrazen produkty z eshopu) ' .
			             'nebo zvolit, ať se vytvoří nový FAPI formulář (děje se při stisku tlačíka ULOŽIT). '
				         , 'mwshop'
			         ) .
			         '</div>'
				;

			// FAPI form settings
			$code .= '<div class="mws_fapi_form_settings">' .
			         '<h4>' . __( 'Nastavení FAPI formuláře', 'mwshop' ) . '</h4>' .
			         '<div class="cms_description">' .
			         __( 'Nastavení této sekce se přejímají z propojeného FAPI formuláře. ' .
			             '<br />' .
			             'Provedete-li změny zde zobrazených nastavení ve FAPI formuláři, spusťe synchronizaci s FAPI, ' .
			             'aby se eshop o změnách ve FAPI dozvěděl.'
				         , 'mwshop'
			         ) .
			         '</div>'
			;
			$formEditHyperlink = 've <a href="https://web.fapi.cz/forms/?projectId=all" class="mws_fapi_form_setup" target="'
			                     . MW_HREF_TARGET_SHARED_ADMIN_EDIT . '">'
			                     . __( 'FAPI', 'mwshop' )
			                     . '</a>';

			// Form payments
			$code .= '<span class="label">' . __('Povolené platební metody FAPI formuláře', 'mwshop') . '</span>';
			$supported = $this->meta->getSupportedPayTypes();
			$stgsForm = isset($stgsForms[$formId]) ? $stgsForms[$formId] : array();
			$stgsFormPayTypes = isset($stgsForm['payTypes']) ? $stgsForm['payTypes'] : array();
			if (count($supported) > 0) {
				$baseId = "gate_settings_payments_{$this->id}";
				$baseName = "gate_settings[{$this->id}][payments]";
				$code .= '<div class="gate_settings_payments ' . $baseId . '" id="' . $idPrefix . '_fapiformpaytypes">';
				foreach ($supported as $type) {
					$code .= '<div><input type="checkbox" '
						. ' name="' . $baseName . '[]" '
						. ' disabled="disabled"'
						. ' value="' . htmlspecialchars($type) . '" '
						. (isset($stgsFormPayTypes[$type]) && $stgsFormPayTypes[$type] ? ' checked="checked" ' : '')
						. '>'
						. MwsPayType::getCaption($type)
						. '</input></div>';
				}
				$code .= '</div>';
			}
			$code .= '<div class="cms_description">'
				. 'Změnu proveďte ' . $formEditHyperlink . ' na záložce <i>3 - Platby a fakturace</i>.<br />'
				. '</div>';

			// Form simplified invoice
			$code .= '<span class="label">' . __('Zjednodušený daňový doklad', 'mwshop') . '</span>';
			$simplified = isset($stgsForm['simplifiedInvoice']) ? $stgsForm['simplifiedInvoice'] : false;
			$code .= '<div><input type="checkbox" '
				. ' name="' . "gate_settings[{$this->id}]" . '[simplifiedInvoice]" '
				. ' id="' . $idPrefix . '_fapiformsimplifiedinvoice"'
				. ' disabled="disabled"'
				. ' value="' . htmlspecialchars($simplified) . '" '
				. ($simplified ? ' checked="checked" ' : '')
				. '>'
				. __('Povolit zjednodušený doklad pro objednávky do 10000Kč zákazníkům z CZ', 'mwshop')
				. '</input></div>';
			$code .= '<div class="cms_description">'
				. 'Změnu proveďte ' . $formEditHyperlink . ' na záložce <i>6 - Vzhled</i>.<br />'
				. '</div>';

			// Form purposes
			$code .= '<span class="label">' . __('Informační povinnost (GDPR)', 'mwshop') . '</span>';
			$purposes = isset($stgsForm['purposes']) ? $stgsForm['purposes'] : array();
			$primaryPurpose = null;
			foreach ( $purposes as $purpose ) {
				if ( isset($purpose['is_primary']) ) {
					$primaryPurpose = $purpose;
					break;
				}
			}
			$textPrimaryPurposeEmptyOrMissing = __( 'Není nastaven text informačního souhlasu ve FAPI. ' .
			                                        'Pro splnění informační povinnosti je potřeba jej nastavit.', 'mwshop' );
			if ( !$primaryPurpose || !isset($primaryPurpose['checkbox_label']) || empty($primaryPurpose['checkbox_label']) ) {
				$code .= '<div ' .
				         ' id="' . $idPrefix . '_fapiformPurposePrimary"' .
				         'class="cms_error_box">' .
				         $textPrimaryPurposeEmptyOrMissing .
				         '</div>';
			} else {
				$code .= '<div ' .
				         ' id="' . $idPrefix . '_fapiformPurposePrimary"' .
				         'class="cms_info_box_gray">' .
				         ( isset( $primaryPurpose['checkbox_label'] ) ? htmlspecialchars( $primaryPurpose['checkbox_label'] ) : '' ) .
				         '</div>';
			}
			$code .= '<div class="cms_description">'
			         . 'Změnu proveďte ' . $formEditHyperlink . ' na záložce <i>6 - Vzhled</i>, sekce <i>Obchodní podmínky a GDPR</i>.<br />'
			         . '</div>';

			$code .= '</div>';

//				$nonce = wp_create_nonce( MWS_GATEWAY_SYNC_NONCE);
//				$check = wp_verify_nonce( $nonce, MWS_GATEWAY_SYNC_NONCE );

			// Script to respond to FAPI form selection changes.
			$code .= '
<script>
console.log("FAPI form");
var jsPayments = JSON.parse(\'' . json_encode($stgsForms) . '\');
var mwsGatewaySyncNonce = "'.wp_create_nonce(MWS_GATEWAY_SYNC_NONCE).'";
var textError_AjaxError="' . __( 'Komunikace se serverem se nezdařila. Prosím opakujte požadavek později.', 'mwshop' ) . '";


function elFapiFormButton(element, enable, formId) {
	if(formId <= 0) {
		// no form selected
		element.attr("data-formid", "");
		element.removeAttr("href");
	} else {
		element.attr("data-formid", formId);
		element.attr("href", "https://web.fapi.cz/forms/update/" + formId + "?projectId=all")
	}
	if(enable) {
		element.removeClass("disabled");
		element.show();			
	} else {
		element.addClass("disabled");
		element.hide();				
	}
}						

jQuery(document).ready(function($){
		$("#'.$idPrefix.'_form").live("change", function() {
		var textPrimaryPurposeEmptyOrMissing = ' . json_encode($textPrimaryPurposeEmptyOrMissing) . ';
		var elSelect = $(this);
		var elFapiFormSetup = $(".mws_fapi_form_setup");
		var elFapiFormCreate = $(".mws_fapi_form_create");
		var elFapiFormSync = $(".mws_fapi_form_sync");
		var val = elSelect.val();
		var elPayTypes = $("#' . $idPrefix . '_fapiformpaytypes");
		var elSimplifiedInvoice = $("#' . $idPrefix . '_fapiformsimplifiedinvoice");
		var elPurposesPrimary = $("#' . $idPrefix . '_fapiformPurposePrimary");
		var elFormSetupHyperlink = $("a.fapiformsetup");
		console.log("FAPI form =",val);
		// Clear checked payment methods always
		elPayTypes.find("input").prop("checked", false);
		if(!val || val == -1 || val == 0) {
			// no form selected
			elFapiFormButton(elFapiFormSetup, false, 0);
			elFapiFormButton(elFapiFormSync, false, 0);
			elFapiFormButton(elFapiFormCreate, true, 0);
			
			elFormSetupHyperlink.removeAttr("href");
			
			elPayTypes.find("input").prop("checked", false);
			elSimplifiedInvoice.prop("checked", false);
			elPurposesPrimary.text(textPrimaryPurposeEmptyOrMissing);
		} else {
			elFapiFormButton(elFapiFormSetup, true, val);
			elFapiFormButton(elFapiFormSync, true, val);
			elFapiFormButton(elFapiFormCreate, false, val);
			
			elFormSetupHyperlink.attr("href", "https://web.fapi.cz/forms/update/"+val+"?projectId=all")
			//PayTypes
			var payTypes = jsPayments[val]["payTypes"];
			for(var propName in payTypes) {
				if (payTypes.hasOwnProperty(propName)) {
					elPayTypes.find("input[value=\'"+propName+"\']").prop("checked", payTypes[propName]);
				}
			}
			var simplifiedInvoice = jsPayments[val]["simplifiedInvoice"];
			elSimplifiedInvoice.prop("checked", simplifiedInvoice);
			//Purposes
			var purposes = jsPayments[val]["purposes"];
			var primaryPurpose = null;
			for(var purposeId in purposes) {
			    var purpose = purposes[purposeId];
				if (purpose.hasOwnProperty("is_primary") && purpose["is_primary"]) {
					primaryPurpose = purpose;
					break;
				}
			}
			if (primaryPurpose && primaryPurpose.hasOwnProperty("checkbox_label") && primaryPurpose["checkbox_label"]) { 
				elPurposesPrimary.text(primaryPurpose["checkbox_label"]).removeClass("cms_error_box").addClass("cms_info_box_gray");
			} else {
				elPurposesPrimary.text(textPrimaryPurposeEmptyOrMissing).removeClass("cms_info_box_gray").addClass("cms_error_box");				    
			}
		}
	}).change();
});
</script>';
			$code .= '</div>'; //FAPI form section
		} catch (Exception $e) {
			$code .= '<input type="hidden" name="'.$namePrefix.'[form] " value="'.$formId.'">';
			$code .= '<span class="mws_admin_error">'._x('neznámo (chyba komunikace s FAPI)', 'FAPI gate - unrecognized FAPI form, communication error', 'mwshop').'</span>';
		}

		$code = '<div class="mws_fapi_settings">' . $code . '</div>';

		return $code;
	}

	/**
	 * @param MwsCart $cart
	 * @return string
	 */
	private function formatInvoiceFooter($cart) {
		$contact = $cart->contact;
		$footer = '';

		$phone = $this->getPhone($cart);

		if (isset($contact['has_shipping_addr']) && $contact['has_shipping_addr'] && isset($contact['shipping_address']) && !empty($contact['shipping_address']['city'])) {
			$contact['shipping_address']['phone'] = '';
			$footer .= (!empty($footer) ? '; ' : '')
				. __('Dodací adresa', 'mwshop') . ": "
				. str_replace("\n", ", ", $cart->formatAddress($contact['shipping_address'], false));
		}
		if (!empty($phone)) {
			$footer .= (!empty($footer) ? "; " : '')
				. __('Tel:', 'mwshop') . ' ' . $phone;
		}

		return $footer;
	}

	private function getPhone($cart) {
		$contact = $cart->contact;

		$phone = '';
		if ($phone === '' && isset($contact['has_shipping_addr']) && $contact['has_shipping_addr'] && !empty($contact['shipping_address']['phone'])) {
			$phone = trim($contact['shipping_address']['phone']);
		}
		if ($phone === '' && isset($contact['want_invoice']) && $contact['want_invoice'] && !empty($contact['address']['phone'])) {
			$phone = trim($contact['address']['phone']);
		}
		return $phone;
	}


	public function recountCart($cart, $includeShippingPrice, $ignoreSimplifiedInvoice) {
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$res = false;

		//TODO Possible failure can happen, when linked product or shipping does not exist (were deleted or so).
		$gwId = $this->id;
    $items = array();
    /** @var array $map Map item templated id to product id. */
    $map = array();
		$gwSpecId = $this->doGetSyncSpecId();
    /** @var MwsCartItem $cartItem */
    foreach ($cart->items->data as $cartItem) {
      $product = $cartItem->product;
      $syncData = $product->sync->getSyncData($gwId, $gwSpecId);

      //TODO Check sync needed? Resync.

			$price = MwsPrice::createByFields(
				$product->price->priceVatIncluded, $product->price->priceVatExcluded, $product->price->priceVatAmount, $product->price->vatPercentage, $product->price->vatIncluded, null
			);

			$items[] = array(
        'item_template' => $syncData['id'],
        'count' => $cartItem->count,
        'price_czk' => $this->getPriceForSyncCZK($price),
        'price_eur' => $this->getPriceForSyncEUR($price),
        'vat' => $price->vatPercentage*100,
        'including_vat' => $price->vatIncluded,
        'electronically_supplied_service' => ($product->type==MwsProductType::Electronic),
      );
      $map[] = array('id'=>$product->id, 'type'=>'product');
    }

		//Shipping
		$priceForShipping = null;
		if($includeShippingPrice) {
			// Stored price
			$shipping = $cart->shippingInstance;
			$syncData = $shipping->sync->getSyncData($gwId, $gwSpecId);
			$priceForShipping = $shipping->getTotalPrice($cart->payment);
			$items[] = array(
				'item_template' => $syncData['id'],
				'count' => 1,
				'price_czk' => $this->getPriceForSyncCZK($priceForShipping),
				'price_eur' => $this->getPriceForSyncEUR($priceForShipping),
				'vat' => $priceForShipping->vatPercentage*100,
				'including_vat' => $priceForShipping->vatIncluded,
				'electronically_supplied_service' => false,
			);
			$map[] = array('id'=>$shipping->id, 'type'=>'shipping');
		}

    $gateStgs = $this->meta->loadSettings();
    $formId = isset($gateStgs['form']['id']) ? $gateStgs['form']['id'] : 0;
    if(empty($formId)) {
			mwshoplog(sprintf(__('Platný FAPI formulář není nastaven. Košík nemůže být přepočítán.', 'mwshop')), MWLL_ERROR, 'paygate');
      return $res;
    }

    $api = $this->getApi();
    try {
			$contact = $cart->contact;

			$currency = $cart->getCurrency();
			$data = array(
        'only_calculate' => true,
				'simplified' => ($ignoreSimplifiedInvoice ? false : $cart->useSimplifiedInvoice()),

				'currency' => strtoupper($currency),

				'form' => $formId,

        'first_name' => 'Josef',
        'last_name' => 'Novák',
        'email' => 'josef.novak@example.com',
        'phone' => '+420 123 456 789',
        'company' => 'Firma s.r.o.',
        'ic' => '12345678',
        'dic' => 'CZ12345678',
        'address' => array(
          'street' => 'Ulice a č.p.',
          'city' => 'Město',
          'zip' => '123 45',
          'country' => $this->getCountryCode($contact['address']), // kód státu dle normy ISO 3166-1
        ),
        'shipping_address' => array(
          'name' => 'Karel',
          'surname' => 'Novák',
          'street' => 'Ulice a č.p.',
          'city' => 'Město',
          'zip' => '123 45',
          'country' => $this->getCountryCode($contact['address']), // kód státu dle normy ISO 3166-1
        ),

        'items' => $items,
				'footer_text' => $this->formatInvoiceFooter($cart),
      );

			$order = $api->createOrder($data);
			$res = isset($order['total_price']);

			// Get back currency according to FAPI
			$currency = MwsCurrency::checkedValue(strtolower($order['currency']), MWS()->getCurrency('key'));

      //Update prices of cart item
			$idx = -1;
      foreach ($order['items'] as $item) {
      	$idx++;
				if(isset($map[$idx])) {
					$mapItem = $map[$idx];
					switch($mapItem['type']) {
						case 'product':
							$prodId = $mapItem['id'];
							$cartItem = $cart->items->getById($prodId);
							$product = $cartItem->product;
							if (!is_null($cartItem)) {
								// Unit price in final currency
								$cartItem->storedPrice = MwsPrice::createByValues(
									$item['unit_price'],
									$item['vat']/100,
									$item['including_vat'],
									$currency
								);
								// Total price in final currency
								$cartItem->storedTotalPrice = MwsPrice::createByFields(
									$item['total_price_including_vat'], $item['total_price'], $item['total_vat_amount'],
									$item['vat']/100, $item['including_vat'],
									$currency
								);
							}
							break;
						case 'shipping':
							$shipId = $mapItem['id'];
							$shipping = $cart->shippingInstance;
							if (! ($shipping && $shipping->id == $shipId)) {
								throw new MwsException('Shipping ID error after invoicing.');
							}
							if (!is_null($shipping)) {
								// Total price in final currency
								$cart->shippingPrice = MwsPrice::createByFields(
									$item['total_price_including_vat'], $item['total_price'], $item['total_vat_amount'],
									$item['vat']/100, $item['including_vat'],
									$currency
								);
							}
							break;
					}
				}
      }
      if (is_null($cart->shippingPrice)) {
      	$cart->shippingPrice = new MwsPrice(0, null, $currency);
			}
      $cart->storedTotalPrice = MwsPrice::createByFields(
				$order['total_price_including_vat'], $order['total_price'], $order['total_vat_amount'], 0.0, true,
				$currency
			);

		} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
			if ($e->getMessage() === "[400] Simplified invoice can only be issued when the total price does not exceed 10000 CZK.") {
				throw new MwsUserException(__('Zjednodušený doklad lze využít pouze pro objednávky do 10000Kč včetně DPH. Je potřeba zadat fakturační údaje.', 'mwshop'));
			} else {
				throw $e;
			}
		} catch (Exception $e) {
			mwshoplog(sprintf(__('Chyba při přepočítávání košíku pomocí FAPI formuláře [%d]:', 'mwshop'), $formId) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
			throw $e;
		}
		return $res;
	}

	protected function doMakeOrder($cart) {
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$res = array(
			'success' => false,
		);

		//TODO Possible failure can happen, when linked product or shipping does not exist (were deleted or so).
		$gwId = $this->id;
		$items = array();
		/** @var array $map Map item templated id to product id. */
		$map = array();
		$gwSpecId = $this->doGetSyncSpecId();
		/** @var MwsCartItem $cartItem */
		foreach ($cart->items->data as $cartItem) {
			$product = $cartItem->product;
			$syncData = $product->sync->getSyncData($gwId, $gwSpecId);
			//TODO Check sync needed? Resync.

			$price = $cartItem->storedPrice;
			if(is_null($price))
				throw new MwsException(__('Předvypočítaná cena objednávky je neočekávané prázdná.', 'mwshop'));

			$items[] = array(
				'item_template' => $syncData['id'],
				'count' => $cartItem->count,
				'price_czk' => $this->getPriceForSyncCZK($price),
				'price_eur' => $this->getPriceForSyncEUR($price),
				'vat' => $price->vatPercentage*100,
				'including_vat' => $price->vatIncluded,
				'electronically_supplied_service' => ($product->type==MwsProductType::Electronic),
			);
			$map[$syncData['id']] = $product->id;
		}
		$price = $cart->shippingPrice;
		if($cart->shipping !== MwsShippingElectronic::id && !is_null($price) && $price->vatIncluded > 0) {
			// Stored price
			$shipping = $cart->shippingInstance;
			$syncData = $shipping->sync->getSyncData($gwId, $gwSpecId);
			$items[] = array(
				'item_template' => $syncData['id'],
				'count' => 1,
				'price_czk' => $this->getPriceForSyncCZK($price),
				'price_eur' => $this->getPriceForSyncEUR($price),
				'vat' => $price->vatPercentage*100,
				'including_vat' => $price->vatIncluded,
				'electronically_supplied_service' => false,
			);
		}

		$gateStgs = $this->meta->loadSettings();
		$formId = isset($gateStgs['form']['id']) ? $gateStgs['form']['id'] : 0;
		if(empty($formId)) {
			mwshoplog(sprintf(__('Platný FAPI formulář není vybrán. Objednávku nelze vytvořit.', 'mwshop')), MWLL_ERROR, 'paygate');
			$res['message'] = __('Komunikace s fakturačním SW není správně nastavená (neurčený formulář).', 'mwshop');
			return $res;
		}

		$api = $this->getApi();
		try {
			$contact = $cart->contact;

			if(isset($contact['has_shipping_addr']) && filter_var($contact['has_shipping_addr'], FILTER_VALIDATE_BOOLEAN))
				$shippingAddr = array(
					'name' => (isset($contact['shipping_address']['firstname']) ? $contact['shipping_address']['firstname'] : ''),
					'surname' => (isset($contact['shipping_address']['surname']) ? $contact['shipping_address']['surname'] : ''),
					'street' => (isset($contact['shipping_address']['street']) ? $contact['shipping_address']['street'] : ''),
					'city' => (isset($contact['shipping_address']['city']) ? $contact['shipping_address']['city'] : ''),
					'zip' => (isset($contact['shipping_address']['zip']) ? $contact['shipping_address']['zip'] : ''),
					'country' => $this->getCountryCode($contact['shipping_address']), // state code according to ISO 3166-1
				);
			else
				$shippingAddr = array();

			//Get user's order note
			$note = isset($cart->contact['note']) ? $cart->contact['note'] : '';

			$currency = $cart->getCurrency();

			//Format purposes into FAPI format
			$purposes = $cart->purposes;
			$fapiPurposes = array();
			foreach ( $purposes as $id => $purpose ) {
				$fapiPurpose['text'] = isset($purpose['text']) ? $purpose['text'] : '';
				$fapiPurpose['form_purpose_id'] = $id;
				$fapiPurpose['checked'] = isset($purpose['checked']) ? (bool)$purpose['checked'] : false;
				$fapiPurposes[] = $fapiPurpose;
			}

			$data = array(
				'form' => $formId,
				'simplified' => $cart->useSimplifiedInvoice(),
				'currency' => strtoupper($currency),

				'form_url' => (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
				'form_title' => get_bloginfo('name'),

				'first_name' => (isset($contact['address']['firstname']) ? $contact['address']['firstname'] : ''),
				'last_name' => (isset($contact['address']['surname']) ? $contact['address']['surname'] : ''),
				'email' => (isset($contact['email']) ? $contact['email'] : ''),
				'phone' => $this->getPhone($cart),
				'company' => (isset($contact['company_info']['company_name']) ? $contact['company_info']['company_name'] : ''),
				'ic' => (isset($contact['company_info']['company_id']) ? $contact['company_info']['company_id'] : ''),
				'dic' => (isset($contact['company_info']['company_vat_id']) ? $contact['company_info']['company_vat_id'] : ''),
				'ic_dph' => (isset($contact['company_info']['company_sk_vat_id']) ? $contact['company_info']['company_sk_vat_id'] : ''),
				'address' => array(
					'street' => (isset($contact['address']['street']) ? $contact['address']['street'] : ''),
					'city' => (isset($contact['address']['city']) ? $contact['address']['city'] : ''),
					'zip' => (isset($contact['address']['zip']) ? $contact['address']['zip'] : ''),
					'country' => $this->getCountryCode($contact['address']), // state code according to ISO 3166-1
				),
				'shipping_address' => $shippingAddr,
				'items' => $items,
				'notes' => $note,
				'footer_text' => $this->formatInvoiceFooter($cart),
				'purposes' => $fapiPurposes,
			);

			//Payment type
			$payment = $cart->payment;
			$canPayOnline = false;
			$data['bank'] = ''; //default
			switch($payment) {
				case MwsPayType::Wire: $data['payment_type'] = 'wire'; $data['bank'] = 'wire'; break;
				case MwsPayType::CreditCard: $data['payment_type'] = 'credit card'; $canPayOnline = true; break;
				case MwsPayType::WireOnline: $data['payment_type'] = 'wire'; $data['bank'] = ''; $canPayOnline = true; break;
				case MwsPayType::Sms: $data['payment_type'] = 'sms'; $canPayOnline = true; break;
				case MwsPayType::PayPal: $data['payment_type'] = 'paypal'; $canPayOnline = true; break;
//	  		case MwsPayType::Cash: $data['payment_type'] = 'x'; break;
				case MwsPayType::Cod: $data['payment_type'] = 'collect on delivery'; break;
				default:
					throw new MwsException(sprintf(__('Nepodporovaná platební metoda [%s].', 'mwshop'), $payment));
			}
//			'payment_type' => $payment,
				// cash | collect on delivery | credit card | wire | sms

//				'bank' => 'wire',
				// Pokud je payment_type = 'wire', pak je možné specifikovat banku:
				// cz_fio | cz_kb | cz_mbank | cz_rb | sk_slsp | sk_unicredit | sk_csob |
				// sk_tatrabanka | sk_sberbank | sk_otp | sk_pabanksk | sk_vub | wire
				// Parametr bank = 'wire' znamená ruční platbu bankovním převodem. Ostatní
				// možnosti přesměrují zákazníka na platební bránu GoPay.

			mwshoplog('[FAPI] payment_type='.$data['payment_type'].' (bank='.$data['bank'].')', MWLL_DEBUG, 'paygate');

//			mwdbg('[FAPI] create order data='.print_r($data,true)); //DEVEL
			try {
//        DEVEL TESTING DATA
//				throw new Exception( 'INTERRUPTED' ); //DEVEL
//				$data['simplified'] = true;
//				$data['items'][0]['price_czk'] = 20000;
				$order = $api->createOrder($data);
			} catch (\MwShop\FapiClient\Rest\InvalidStatusCodeException $e) {
				if ($e->getMessage() === "[400] Simplified invoice can only be issued when the total price does not exceed 10000 CZK.") {
					throw new MwsUserException(__('Zjednodušený doklad lze využít pouze pro objednávky do 10000Kč včetně DPH. Je potřeba zadat fakturační údaje.', 'mwshop'));
				} else {
					throw $e;
				}
			}
//			mwdbg('[FAPI] create order response='.print_r($order,true)); //DEVEL
//			throw new Exception('INTERRUPTED'); //DEVEL
			$ok = isset($order['invoice']);

			if($ok) {
				// Is URL redirection for payment present?
				if(isset($order['next_url']))
					$res['nextUrl'] = $order['next_url'];

				$idInvoice = $order['invoice'];
				$numOrder = 0;
				$invoice = null;
				try {
					$invoice = $api->getInvoice($idInvoice);
					$numOrder = $invoice['number'];
				} catch (Exception $e) {
				}

				$orderObj = new MwsOrder(null, $numOrder);
				$orderObj->gateId = $gwId;
				// Prepare specific data of gate concerning new order.
				$orderObj->gateOrderData = array(
					'idInvoice' => $idInvoice,
					'idForm' => $order['form'],
					'idOrder' => $order['id'],
					'dataOrder' => $order,
					'dataInvoice' => $invoice,
				);
				$orderObj->customerNote = $note;
				$orderedItems = $orderObj->items;
				if(!is_null($orderedItems)) {
					foreach ($cart->items->data as $cartItem) {
						$product = $cartItem->product;
						$syncData = $product->sync->getSyncData($gwId, $gwSpecId);
						$orderItem = new MwsOrderItem(
							$cartItem->productId,
							$cartItem->count,
							$gwId,
							$syncData['id'],
							$product->conversionCode
						);
						$orderedItems->add($orderItem);
					}
				}

				// URL for direct payments
				$formPath = isset($gateStgs['form']['path']) ? $gateStgs['form']['path'] : '';
				$payUrl = empty($formPath) || empty($numOrder) || !$canPayOnline
					? ''
					: 'https://form.fapi.cz/gateway/?' . http_build_query(array('id' => $formPath, 'vs' => $numOrder));
				mwshoplog('[FAPI] urlDirectPay='.$payUrl, MWLL_DEBUG, 'paygate');
				$orderObj->urlDirectPay = $payUrl;

				$orderObj->save();

				$res['orderId'] = $orderObj->id;
				$res['orderNum'] = $numOrder;
			}
			$res['success'] = $ok;

		} catch (Exception $e) {
			mwshoplog(sprintf(__('Chyba při vytváření objednávky pomocí FAPI formuláře [%d]:', 'mwshop'), $formId) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
			$res['message'] = __('Objednávku se nepodařilo vytvořit.', 'mwshop')
				. " <br />\n"
				. $e->getMessage();
		}
		return $res;
	}

	public function doGetSyncSpecId() {
		$gateStgs = $this->meta->loadSettings();
		$formId = isset($gateStgs['form']['id']) ? $gateStgs['form']['id'] : 'unknown';
		return $formId;
	}

	public function doGetSupportedPayTypes() {
		return array(
//			MwsPayType::Cash,
			MwsPayType::Cod,
			MwsPayType::CreditCard,
			MwsPayType::PayPal,
			MwsPayType::Sms,
			MwsPayType::Wire,
			MwsPayType::WireOnline,
		);
	}

	public function doGetPurposes() {
		$stgs = $this->meta->loadSettings();
		$purposes = isset($stgs['form']['purposes']) ? $stgs['form']['purposes'] : array();
		return $purposes;
	}

	public function doGetDefaultEnabledPayTypes() {
		return array(
//			MwsPayType::Cash,
			MwsPayType::Cod,
			MwsPayType::Wire,
		);
	}

	public function doSettingsChanged_Form() {
		//Handle changes of selected FAPI form
		$resync = parent::doSettingsChanged_Form();
		$newFormId = (isset($_REQUEST['gate_settings'][$this->id]['form']))
			? (int)$_REQUEST['gate_settings'][$this->id]['form']
			: null
		;
		if($newFormId < 0)
			$newFormId = null;
		$stgs = $this->meta->loadSettings();
		$oldFormId = isset($stgs['form']['id']) ? $stgs['form']['id'] : null;
		mwshoplog(__METHOD__.": newFormId=[$newFormId] oldFormId=[$oldFormId]", MWLL_DEBUG);
		if(empty($newFormId)) {
			mwshoplog(__('Nový FAPI formulář bude vytvořen při synchronizaci FAPI.', 'mwshop'), MWLL_INFO, 'paygate');
			$stgs['form'] = array(); //clear cached form
			$this->meta->saveSettings($stgs);
			//TODO Force resync of all products
			$resync = true;
		} elseif($oldFormId != $newFormId) {
			mwshoplog(sprintf(__('Vybrán jiný FAPI formulář, nový FAPI formulář má ID [%d]', 'mwshop'), (int)$newFormId), MWLL_INFO, 'paygate');
			$stgs['form'] = array();
			$stgs['form']['id'] = $newFormId;
			$this->meta->saveSettings($stgs);
			//TODO Force resync of all products
			$resync = true;
		}

		return $resync;
	}

	public function getOrderFromThankYou() {
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$res = null;

		$orderNum=isset($_REQUEST['vs']) ? $_REQUEST['vs'] : '';
//		$email=isset($_REQUEST['email']) ? $_REQUEST['email'] : '';

		$res = MwsOrder::getOrderByOrderNum($orderNum);

		return $res;
	}

	public function orderPaied() {
		$res = null;

		$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
		if($id) {
			$api = $this->getApi();
			try {
				$invoice = $api->getInvoice($id);
				if ($invoice['paid']) {
					//Paid
					$numOrder = $invoice['number'];
					mwshoplog("Invoice {$numOrder} of id=[$id] was paid in FAPI.", MWLL_DEBUG, 'paygate');
					$order = MwsOrder::getOrderByOrderNum($numOrder);
					if(is_null($order)) {
						mwshoplog(sprintf(__('Objednávka pro FAPI fakturu {%s} [%d] nebyla nalezena ve FAPI', 'mwshop'), $numOrder, $id), MWLL_WARNING, 'paygate');
						return $res;
					}
					$paidOn = $invoice['paid_on']; //2016-05-05 12:11:11
					$paidOn = new \DateTime($paidOn, new \DateTimeZone('Europe/Prague'));
					$paidOn->setTimezone(new \DateTimeZone('GMT')); //times are saved in UTC
					//Update order
					$order->setPaid(true, $paidOn->getTimestamp());
					$order->save();
					$res = $order;
					mwshoplog(sprintf(__('FAPI objednávka {%s} odbavena jako UHRAZENÁ.', 'mwshop'), $numOrder), MWLL_INFO, 'paygate');
				} else {
					//Not paid
					mwshoplog(sprintf(__('FAPI faktura {%s} [%d] není ve FAPI označena jako UHRAZENÁ. Podvádíš?', 'mwshop'), $invoice['number'], $id)
						, MWLL_WARNING, 'paygate');
				}
			} catch (Exception $e) {
				mwshoplog(sprintf(__('Chyba při zpracování FAPI faktury [%d]:', 'mwshop'), $id) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
				return $res;
			}
		} else {
			mwshoplog(sprintf(__('Chybí důležitý argument "id" ve FAPI callbacku.', 'mwshop')) . ' ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_WARNING, 'paygate');
		}

		return $res;
	}

	public function orderCancelled() {
		$res = null;

		$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
		if($id) {
			$api = $this->getApi();
			try {
				$invoice = $api->getInvoice($id);
				if ($invoice['cancelled']) {
					//Paid
					$numOrder = $invoice['number'];
					mwshoplog("FAPI invoice {$numOrder} of id=[$id] was cancelled at gateway.", MWLL_DEBUG, 'paygate');
					$order = MwsOrder::getOrderByOrderNum($numOrder);
					if(is_null($order)) {
						mwshoplog(sprintf(__('Objednávka pro FAPI fakturu {%s} [%d] nebyla nalezena ve FAPI', 'mwshop'), $numOrder, $id), MWLL_WARNING, 'paygate');
						return $res;
					}
					//Update order
					$order->setCancelled();
					$order->save();
					$res = $order;
					mwshoplog(sprintf(__('FAPI objednávka {%s} odbavena jako STORNOVANÁ.', 'mwshop'), $numOrder), MWLL_INFO, 'paygate');
				} else {
					//Not cancelled
					mwshoplog(sprintf(__('FAPI faktura {%s} [%d] není ve FAPI označena jako STORNOVANÁ. Podvádíš?', 'mwshop'), $invoice['number'], $id)
						, MWLL_WARNING, 'paygate');
				}
			} catch (Exception $e) {
				mwshoplog(sprintf(__('Chyba při zpracování FAPI faktury [%d]:', 'mwshop'), $id) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
				return $res;
			}
		} else {
			mwshoplog(sprintf(__('Chybí důležitý argument "id" ve FAPI callbacku.', 'mwshop')) . ' ' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_WARNING, 'paygate');
		}

		return $res;
	}

	public function loadOrderGate($order, $preloadedData = null) {
		$numOrder = $order->orderNum;

		//Load data from gate.
		if(!$preloadedData) {
			$api = $this->getApi();
			try {
				$saveOrderData = $order->gateOrderData;
				if(! isset($saveOrderData['idInvoice']))
					throw new MwsException('Id of invoice is missing from the order.');
				$idInvoice = $saveOrderData['idInvoice'];
				$preloadedData = $api->getInvoice($idInvoice);
			} catch (Exception $e) {
				mwshoplog(sprintf(__('FAPI fakturu se nepodařilo načíst z FAPI [%s]:', 'mwshop'), $numOrder) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
				return null;
			}
		}

		$res = new MwsOrderGate_Fapi($order);
		$res->setData($preloadedData);
		$order->gateLive = $res;
		return $res;
	}

	public function preloadOrdersGateLive($orders) {
		$invoiceIds = array();
		$orderMap = array();
		/** @var MwsOrder $order */
		foreach ($orders as $order) {
			$saveOrderData = $order->gateOrderData;
			if(! isset($saveOrderData['idInvoice']))
				continue; //ignore unloadable orders
			$invoiceIds[] = $saveOrderData['idInvoice'];
			$orderMap[$saveOrderData['idInvoice']] = $order;
		}

		$api = $this->getApi();
		try {
			$preloadedData = $api->getInvoices(array('id'=>$invoiceIds));
			foreach ($preloadedData as $data) {
				$invoiceId = $data['id'];
				if(isset($orderMap[$invoiceId])) {
					$order = $orderMap[$invoiceId];
					mwshoplog("Preloading FAPI order {$order->orderNum}, invoiceId={$invoiceId}.", MWLL_DEBUG, 'paygate');
					$this->loadOrderGate($order, $data);
				}
			}

		} catch (Exception $e) {
			mwshoplog(sprintf(__('FAPI faktury se nepodařilo přednačíst:', 'mwshop')) . ' ' . $e->getMessage(), MWLL_ERROR, 'paygate');
		}
	}
}


class MwsOrderGate_Fapi extends MwsOrderGate {
	private $data;
	const UrlFapiUI = 'https://web.fapi.cz';

	public function setData($data) {
		$this->data = $data;
	}

	public function formatInvoiceContact($short = false) {
		$quote = function ($str) {
			$str = trim($str);
			if (empty($str))
				return '';
			else
				return '<div>' . esc_html($str) . '</div>';
		};

		$customer = isset($this->data['customer']) ? $this->data['customer'] : array();
		if (is_array($customer) && !empty($customer)) {
			if($short) {
				return (isset($customer['name']) && !empty($customer['name']) ? $quote($customer['name']) : '');
			} else {
				$ids = trim(''
					. (isset($customer['ic']) && !empty($customer['ic']) ? '<div><b>' . __('IČ', 'mwshop') . ':</b> '
						. esc_html($customer['ic']) . '</div>' : '')
					. (isset($customer['dic']) && !empty($customer['dic']) ? '<div><b>' . __('DIČ', 'mwshop') . ':</b> '
						. esc_html($customer['dic']) . '</div>' : '')
				);
				$orderData = $this->parent->gateOrderData;
				if (isset($orderData['dataOrder']['email']))
					$ids = '<div><b>' . __('Email', 'mwshop') . ':</b> '
						. '<a href="mailto:' . $orderData['dataOrder']['email'] . '">'
						. esc_html($orderData['dataOrder']['email'])
						. '</a>'
						. '</div>'
						. $ids;
				$addr = trim(''
					. (isset($customer['name']) && !empty($customer['name']) ? $quote($customer['name']) : '')
					. (isset($customer['address']['street']) && !empty($customer['address']['street']) ? $quote($customer['address']['street']) : '')
					. (isset($customer['address']['city']) && !empty($customer['address']['city']) ? $quote($customer['address']['city']) : '')
					. (isset($customer['address']['zip']) && !empty($customer['address']['zip']) ? $quote($customer['address']['zip']) : '')
					. (isset($customer['address']['country']) && !empty($customer['address']['country']) ? $quote($customer['address']['country']) : '')
				);
				if (!empty($ids) && !empty($addr))
					$ids .= '<br />';
				return $ids . $addr;
			}
		} else
			return '';
	}

	public function formatShippingContact() {
		$quote = function ($str) {
			$str = trim($str);
			if (empty($str))
				return '';
			else
				return '<div>' . esc_html($str) . '</div>';
		};

		$shippingAddr = isset($this->data['customer']['shipping_address']) ? $this->data['customer']['shipping_address'] : array();
		if (is_array($shippingAddr) && !empty($shippingAddr)) {
			return trim(''
				. (isset($shippingAddr['name']) && !empty($shippingAddr['name']) || isset($shippingAddr['surname']) && !empty($shippingAddr['surname'])
					? $quote($shippingAddr['name'].' '.$shippingAddr['surname']) : '')
				. (isset($shippingAddr['street']) && !empty($shippingAddr['street']) ? $quote($shippingAddr['street']) : '')
				. (isset($shippingAddr['city']) && !empty($shippingAddr['city']) ? $quote($shippingAddr['city']) : '')
				. (isset($shippingAddr['zip']) && !empty($shippingAddr['zip']) ? $quote($shippingAddr['zip']) : '')
				. (isset($shippingAddr['country']) && !empty($shippingAddr['country']) ? $quote($shippingAddr['country']) : '')
			);
		} else
			return '';
	}

	public function formatContactEditting() {
		$edit = '';
		if (isset($this->data['client'])) {
			$edit .= htmlAdminEditButton_SharedWindow(__('Zobrazit klienta', 'mwshop'),
				$this::UrlFapiUI . '/client/detail/' . $this->data['client']);
			$edit .= htmlAdminEditButton_SharedWindow(__('Upravit klienta', 'mwshop'),
				$this::UrlFapiUI . '/client/update/' . $this->data['client']);
			$edit = '<div>' . $edit . '</div>';
		}
		return $edit;
	}

	public function getItems() {
		$res = array();
		$items = isset($this->data['items']) ? $this->data['items'] : array();
		foreach ($items as $item) {
			$line = array();
			$line['title'] = $item['name'];
			$line['count'] = $item['count'];
			$price = (float)$item['price'];
			$vatIncluded = (bool)$item['including_vat'];
			$vatPercent = (float)$item['vat'];
			$line['priceIncludingVat'] = $vatIncluded
				? $price
				: $price / 100 * (100+$vatPercent);
			$line['vatPercentage'] = $vatPercent;
			$code = $item['code'];
			if($code) {
				//TODO Use some sort of global hashmap of synced IDS of items to link to product ID.
				$line['productId'] = 0;
			} else
				$line['productId'] = 0;

			$res[] = $line;
		}


		return $res;
	}

	/**
	 * Get API for FAPI.
	 * @return \MwShop\FapiClient\FapiClient
	 */
	private function getApi() {
		if (!isset($this->api)) {
			$api = null;
			$gw = $this->getGateway();
			if ($gw) {
				try {
					$api = $gw->sharedInstance()->getApi(); //direct call into the MwsGatewayImpl_Fapi class
				} catch (Exception $e) {
				}
			}
			$this->api = $api;
		}
		return $this->api;
	}

	public function getDocuments() {
		$res = array();
		$invoiceId = isset($this->data['id']) ? $this->data['id'] : 0;
		if(!$invoiceId)
			return $res;

		//Primary invoice
		$invoice = $this->data;

		$invoices = array();
		$processedParentIds = array();
		$invoices[] = $this->data;

		$api = $this->getApi();
		if($api) {
			$this->getChildInvoices($api, $invoices, $processedParentIds);
		}

		foreach ($invoices as $invoice) {
			$invoiceId = $invoice['id'];
			if(isset($invoice['type']))
				switch ($invoice['type']) {
					case 'proforma':
						$title = sprintf(__('%s - zálohová faktura', 'mwshop'), $invoice['number']);
						break;
					case 'payment_confirmation':
						$title = sprintf(__('%s - přijetí platby', 'mwshop'), $invoice['number']);
						break;
					case 'invoice':
						$title = sprintf(__('%s - faktura', 'mwshop'), $invoice['number']);
						break;
					case 'simplified_invoice':
						$title = sprintf(__('%s - zjednodušená daň. doklad', 'mwshop'), $invoice['number']);
						break;
					case 'credit_note':
						$title = sprintf(__('%s - opravný daň. doklad', 'mwshop'), $invoice['number']);
						break;
				}

			$res[] = array(
				'title' => (isset($title)
						? $title
						: $title = sprintf(__('Doklad č. %s', 'mwshop'), $invoice['number'])
					),
				'dateCreated' => $invoice['created_on'],
				'urlShow' => $this::UrlFapiUI . '/invoice/detail/'.$invoiceId,
				'urlDownload' => $this::UrlFapiUI . '/invoice/pdf/'.$invoiceId,
				'urlEdit' => $this::UrlFapiUI . '/invoice/update/'.$invoiceId,
				'total' => $invoice['total'],
				'isPaid' => (bool)$invoice['paid'],
			);
		}

		return $res;
	}

	/**
	 * Get all dependent invoices or documents within FAPI for documents already in array.
	 * @param \MwShop\FapiClient\FapiClient $api
	 * @param array $invoices List of invoices.
	 * @param array $processedIds List of IDs that were already processed as parents.
	 */
	private function getChildInvoices($api, &$invoices, &$processedIds) {
		$toProcess = array();
		foreach ($invoices as $invoice) {
			if(isset($invoice['id']) && !in_array($invoice['id'], $processedIds)) {
				$toProcess[] = $invoice['id'];
			}
		}
		while(!empty($toProcess)) {
			$parentId = array_shift($toProcess);
			if($parentId) {
				$processedIds[] = $parentId;
				$newInvoices = $api->getInvoices(array('parent' => $parentId));
				foreach ($newInvoices as $newInvoice) {
					$invoices[] = $newInvoice;
					if(isset($newInvoice['id']) && !empty($newInvoice['id'])) {
						$newInvoiceId = $newInvoice['id'];
						if(!in_array($newInvoiceId, $processedIds) && !in_array($newInvoiceId, $toProcess)) {
							$toProcess[] = $newInvoiceId;
						}
					}
				}
			}
		}
	}

	protected function doGetPrice() {
		return isset($this->data['total'])
			? new MwsPrice(array(
				'priceVatAmount' => $this->data['total_vat'],
				'priceVatIncluded' => $this->data['total'],
				'priceVatExcluded' => $this->data['total'] - $this->data['total_vat'],
				'vatPercentage' => 0))
			: parent::doGetPrice();
	}

	protected function doGetIsPaid() {
		return isset($this->data['paid'])
			? (bool)$this->data['paid']
			: parent::doGetIsPaid();
	}

	protected function doGetPaidOn() {
		if(isset($this->data['paid_on'])) {
			$datetime = new DateTime($this->data['paid_on'], new DateTimeZone('Europe/Prague'));
			// get the unix timestamp (adjusted for the site's timezone already)
			$timestamp = $datetime->format('U');
			return $timestamp;
		} else
			return parent::doGetPaidOn();
	}

}
