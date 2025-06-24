<?php

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (class_exists('MioShop'))
	return;


/** Gets the main class of the shop. It is protected against multiple instances.
 * @return MioShop Singleton core shop class.
 */
function MWS() {
	return MioShop::instance();
}


/**
 * Core class of the MioWeb shop solution.
 *
 * @property string $gatewaySelectedId Currently selected gateway for payments and order calculations.
 * @class MioShop
 * @since 1.0.0
 */
final class MioShop {
	/** @var string Release version number. */
	const version = '1.0.11';
	/** Minimal count of items in stock meaning enough items in stock. */
	const StockLimit_Plenty = 5;
	/** Minimal count of items in stock meaning we are getting low on stock. */
	const StockLimit_Low = 3;
	/** @var MioShop Single instance holder. */
	protected static $_instance = null;
	/** @var string Release version number. */
	public $version = self::version;
	/** @var bool If shop is used withing Mioweb (true) or not (false). */
	public $inMioweb = false;
	/** @var MwsAsync Instance for asynchronious execution of events. Instantiated during class creation. */
	public $async;
	/** @var array Shop setting options. */
	public $setting;
	/** @var array Settings of permalink structure. */
	public $permalinks;
	/** @var array Shop visual setting options. */
	public $visual_setting;
	/** @var MwsCart Content of shopping cart. */
	public $cart;
	/** @var bool True if current user an edit pages. */
	public $edit_mode = false;
	/** @var array|mixed Info about active template - url, dir... */
	public $template;
	/** @var string Name of thumbnail - notcroped/croped */
	public $thumb_name = 'mio_columns_';
	/** @var array Two-dimensional array of currency conversion rater FROM unit TO unit. */
	protected $currencyConversionTable;
	/** @var MwsCurrent Property holder of current data for rendering phase. */
	private $_current;
	private $_async_SyncAll;
	/** @var MwsGateways List of available gateways for payments. */
	private $_gateways = null;
	/** @var MwsVATs List of defined VATs. */
	private $_vats = null;

	function __construct() {
		mwshoplog('   ---', MWLL_DEBUG);
//		mwshoplog('MioShop instance created', MWLL_DEBUG);

		$this->inMioweb = defined('CMS_VERSION');

		if (current_user_can('edit_pages')) $this->edit_mode = true;

		$this->setting = get_option(MWS_OPTION_SHOP_SETTING);
		$this->reloadPermalinks();

		$this->visual_setting = get_option('eshop_appearance');
		$this->thumb_name = isset($this->visual_setting['product_thumbnail'])
			? $this->visual_setting['product_thumbnail']
			: 'thumbnail';
		$this->template['url'] = MWS_URL_BASE . '/templates/';
		$this->template['dir'] = MWS_PATH_BASE . '/templates/';

		$this->registerDefines();
		$this->registerAutoloader();
		$this->registerHooks();

		//Asynchronous support
		require_once('libs/wp-background-processing-master/wp-background-processing.php'); //direct
		require_once('libs/wp-async-task/wp-async-task.php'); //later
		$this->async = new MwsAsyncNow();
		new MwsAsyncLater();

		// Fire start
		do_action('mws_loaded');

	}

	public function reloadPermalinks() {
		$this->permalinks = get_option(MWS_OPTION_PERMALINKS);
	}

	private function registerDefines() {
		if (!defined('MWS_INSTANCE_MODEL'))
			define('MWS_INSTANCE_MODEL', 'plugin');
	}

	/** Register autoloading of MWS classes. */
	private function registerAutoloader() {
		if (!function_exists('spl_autoload_register'))
			wp_die(
				__('MioShop requires at least PHP 5.3 with autoload support enabled.', 'mwshop'),
				__('MioShop error', 'mwshop'));
		spl_autoload_register(array($this, 'autoload'));
	}

	private function registerHooks() {
		// Fire installation if not already installed.
		// There is a difference in the way how thinks are handled in plugin and in theme.
		if (defined('MWS_INSTANCE_MODEL') && MWS_INSTANCE_MODEL === 'plugin') {
			register_activation_hook(__FILE__, array($this, 'checkInstallation'));
		} else {
			add_action('after_switch_theme', array($this, 'checkInstallation'));
			add_action('switch_theme', array('MwsInstall', 'uninstall'));
		}

		// Hook template files search.
		if ($this->inMioweb) {
			// Use custom template for specific template parts into MioWeb.
			add_filter('mw_locate_template', array($this, 'hookLocateShopTemplateForMioweb'), 10, 2);
			add_filter('mw_get_page_template', array($this, 'hookGetPageTemplate'), 10, 2);
		} else {
			// Use custom templates if Mioweb is not used.
			add_filter('single_template', array($this, 'hookLocateTemplate_single'), 100);
			add_filter('archive_template', array($this, 'hookLocateTemplate_archive'), 100);
		}

		// modify breadcrumbs on single page
		add_filter('mw_breadcrumb_items', array($this, 'hookBreadcrumbs'), 100);

		// Scripts, css
		add_action('wp_enqueue_scripts', array($this, 'enqueScripts'));
		add_action('admin_enqueue_scripts', array($this, 'enqueScripts' /*'load_admin_scripts'*/));

		// Ajax
		add_action('wp_ajax_mws_gate_sync', array('MwsAjax', 'gateSyncAll')); //enabled only in admin
		add_action('wp_ajax_nopriv_mws_gate_callback', array('MwsAjax', 'gateCallback'));
		add_action('wp_ajax_mws_gate_callback', array('MwsAjax', 'gateCallback')); //enabled in admin too
		add_action('wp_ajax_mws_gate_debug', array($this, 'ajaxGateDebug'));
		// cart manipulation
		add_action('wp_ajax_nopriv_mws_cart_add', array('MwsAjax', 'cartAddItem'));
		add_action('wp_ajax_mws_cart_add', array('MwsAjax', 'cartAddItem'));
		add_action('wp_ajax_nopriv_mws_cart_remove', array('MwsAjax', 'cartRemoveItem'));
		add_action('wp_ajax_mws_cart_remove', array('MwsAjax', 'cartRemoveItem'));
		// order manipulation
		add_action('wp_ajax_nopriv_mws_order_step', array('MwsAjax', 'orderStep'));
		add_action('wp_ajax_mws_order_step', array('MwsAjax', 'orderStep'));
		// quick buy
		add_action('wp_ajax_nopriv_mws_quick_buy', array('MwsAjax', 'quickBuy'));
		add_action('wp_ajax_mws_quick_buy', array('MwsAjax', 'quickBuy'));

		//
		add_action('wp_ajax_mws_eshop_activation', array('MwsAjax', 'shopActivation'));
		
		//
		add_action('init', array('MwsRss', 'addCustomRss'));


		// Administration
		if ($this->canEdit()) {
			require_once(__DIR__ . '/includes/order-admin.php');
			require_once(__DIR__ . '/includes/product-admin.php');
			add_action('wp_insert_post', array($this, 'hookSaveOrder'), 1000, 2);
			add_action('edit_form_after_title', array($this, 'admin_page_edit'));
			add_filter('post_updated', array($this, 'hookPageSlugChanged'), 1000, 3);
			add_action('current_screen', array($this, 'hookAdminScreen'));
		}

		//Detection of need of synchronization
		add_action('wp_insert_post', array($this, 'hookAfterSaveProduct'), 1000, 2);
		add_action('wp_insert_post', array($this, 'hookSaveProductShipping'), 1000, 2);
		add_action('pre_update_option_' . MWS_OPTION_SHOP_SETTING, array($this, 'hookShopOptionsChanged'), 10, 3);
//		add_action('pre_update_option_' . MWS_OPTION_SHOP_SETTING, array($this, 'hookOptionChanged_GatePayment'), 10, 3); //TODO Deprecated. Payment methods are read from gateway.
		add_action('pre_update_option_' . MWS_OPTION_SHOP_SETTING, array($this, 'hookOptionChanged_GateSettings'), 10, 3);

		// Shop initialization
		add_action('init', array($this, 'init'));
		add_action('wp', array($this, 'wpLoaded'), 1000);

		//visual setting
		add_action('ve_global_setting', array($this, 'useEshopVisual'));

		// modify per page for product list
		add_action('pre_get_posts', array($this, 'modifyProductQuery'));
		// generate nice permalinks for products
//		add_filter( 'post_link', array('MwsRewrite', 'hookPostLink' , 10, 2) );  SOLVED BY REGISTERING PERMALINK STRUCTURE

		// category fields
		add_action(MWS_PRODUCT_CAT_SLUG . '_edit_form_fields', array($this, 'presenters_eshop_category_custom_fields'), 10, 2);
		add_action(MWS_PRODUCT_CAT_SLUG . '_add_form_fields', array($this, 'presenters_eshop_category_custom_fields'), 10, 2);
		add_action('create_' . MWS_PRODUCT_CAT_SLUG, array($this, 'save_eshop_category_custom_fields'), 10, 2);
		add_action('edited_' . MWS_PRODUCT_CAT_SLUG, array($this, 'save_eshop_category_custom_fields'), 10, 2);
	}

	function canEdit() {
		return current_user_can('edit_posts');
	}

	/**
	 * @return MioShop Returns singleton instance of MioShop.
	 */
	public static function instance() {
		$var = static::$_instance;
		if (is_null($var))
			static::$_instance = new static();
		return static::$_instance;
	}

	/** Autoloading mechanism of files. */
	public static function autoload($className) {
		static $paths = array(
			'MwsInstall' => 'includes/install.php',
			'MwsTypesRegistration' => 'includes/types_registration.php',
			'MwsRewrite' => 'includes/rewrite.php',
			'MwsException' => 'includes/core.php',
			'MwsUserException' => 'includes/core.php',
			'MwsCurrent' => 'includes/core.php',
//			'MwsPropDef' => 'includes/property_definition.php',
//			'MwsPropGroupDef' => 'includes/property_definition.php',
//			'MwsPropFieldDef' => 'includes/property_definition.php',
//			'MwsPropPostDef' => 'includes/property_definition.php',
			'MwsMetaboxes' => 'includes/metaboxes.php',
			'MwsFieldEditor' => 'includes/field_editors.php',

			'MwsProduct' => 'includes/product.php',
			'MwsSync_Product' => 'includes/product.php',
			'MwsStockUpdate' => 'includes/product.php',
			'MwsProductAvailabilityStatus' => 'includes/product.php',

			'MwsProductRoot' => 'includes/product_root.php',
			'MwsProductVariant' => 'includes/product_variant.php',

			'MwsGateways' => 'includes/gateways.php',
			'MwsGatewayMeta' => 'includes/gateways.php',
			'MwsGatewayImpl' => 'includes/gateways.php',

			'MwsCart' => 'includes/cart.php',
			'MwsCartTemp' => 'includes/cart.php',
			'MwsCartItems' => 'includes/cart.php',
			'MwsCartItem' => 'includes/cart.php',

			'MwsVATs' => 'includes/VATs.php',
			'MwsVatAccounting' => 'includes/VATs.php',

			'MwsBasicEnum' => 'includes/enumerations.php',
			'MwsProductType' => 'includes/enumerations.php',
			'MwsSellRestriction' => 'includes/enumerations.php',
			'MwsSalePriceType' => 'includes/enumerations.php',
			'MwsOrderStep' => 'includes/enumerations.php',
			'MwsCurrency' => 'includes/enumerations.php',

			'MwsSessionHelper' => 'includes/session.php',
			'MwsAjax' => 'includes/ajax.php',

			'MwsAsyncNow' => 'includes/async.php',
			'MwsAsyncLater' => 'includes/async.php',
			'MwsSync' => 'includes/sync_base.php',

			'MwsShipping' => 'includes/shipping.php',
			'MwsShippingElectronic' => 'includes/shipping.php',
			'MwsPrice' => 'includes/price.php',
			'MwsPayType' => 'includes/payments.php',
//			'MwsPayment' => 'includes/payments.php',
//			'MwsPayments' => 'includes/payments.php',

			'MwsOrder' => 'includes/order.php',
			'MwsOrderStatus' => 'includes/enumerations.php',
			'MwsOrderGate' => 'includes/order.php',

			'MwsObjectCache' => 'includes/core.php',
			'MwsProperty' => 'includes/property.php',
			'MwsPropertyValue' => 'includes/property.php',

			'MwsProductCodes' => 'includes/product-codes.php',
			
			'MwsRss' => 'includes/rss.php',


//			'WP_Async_Task' => 'libs/wp-async-task/wp-async-task.php',
		);

		$className = ltrim($className, '\\'); // PHP namespace bug #49143

		if (isset($paths[$className])) {
//			mwshoplog("$className ...autoloaded", MWLL_DEBUG, 'autoload');
			require_once(__DIR__ . '/' . $paths[$className]);
		}
	}

	/**
	 * Detect changes in critical pages.
	 *
	 * @param int $postId         Post ID.
	 * @param WP_Post $postAfter  Post object following the update.
	 * @param WP_Post $postBefore Post object before the update.
	 */
	public static function hookPageSlugChanged($postId, $postAfter, $postBefore) {
//		mwshoplog(__METHOD__.' postId='.$postId, MWLL_DEBUG);
		$mws = MWS();
		//Is this shop page or cart/order page?
		$orderPageId = $mws->getOrderPage();
		$homePageId = $mws->getHomePage();

		// Gateways callback hook
		if ($postId == $orderPageId || $postId == $homePageId) {
			$before = $postBefore->post_name;
			$after = $postAfter->post_name;
			if (!empty($after) && $after != $before) {
				mwshoplog('Slug-name for critical page (cart, eshop...) changed.', MWLL_INFO);
				$mws->gateways()->clearSyncedAll();
				$mws->async_SyncAllNeeded();
			}
		}
	}

	/**
	 * Returns ID of the cart/order shop page. If page id is not known, 0 is returned.
	 * @return int
	 */
	function getOrderPage() {
		return (isset($this->setting['order_page'])) ? $this->setting['order_page'] : 0;
	}

	/**
	 * Returns ID of the entry shop page with list of products. If page id is not known, 0 is returned.
	 * @return int
	 */
	function getHomePage() {
		return (isset($this->setting['home_page'])) ? $this->setting['home_page'] : 0;
	}

	/**
	 * Get list of all registered gateways. Check gateway's "active" property to find out if it is enabled in shop's
	 * global settings.
	 * @return MwsGateways
	 */
	public function gateways() {
		if (is_null($this->_gateways)) {
			$gws = new MwsGateways();
			$this->_gateways = $gws;
		}
		return $this->_gateways;
	}

	/**
	 * Add synchronization request of gateways.
	 * Que resynchronization. Only gates requesting synchronization will be synchronized.
	 * Request is fired on WP_SHUTDOWN, that is after all option's modifications ARE SAVED! :o)
	 */
	public function async_SyncAllNeeded() {
		if (MWS()->gateways()->syncDisabled) {
			mwshoplog(__('Požadovaná synchronizace platebních bran nebude provedena, neboť je zakázána nastavením.', 'mwshop'),
				MWLL_WARNING, 'paygate');
			return;
		}
		if (is_null($this->_async_SyncAll)) {
			$this->_async_SyncAll = new MwsAsyncLater();
			$this->_async_SyncAll->data(array('operation' => 'syncAll', 'sleep' => 0));
			mwshoplog('Zařazen požadavek na synchronizaci platební brány.', MWLL_INFO, 'paygate');
		}
	}

	public static function hookLocateTemplate_single($defaultTpl) {
		$obj = get_queried_object();
		$templates = array();
		//Redefine only product and order.
		if ($obj && ($obj->post_type) == MWS_PRODUCT_SLUG || $obj == MWS_ORDER_SLUG) {
			$templates[] = "single-{$obj->post_type}.php";
			$templates[] = "single.php";
		}
		$tpl = (empty($templates) ? '' : static::locateShopTemplate($templates));
		return ($tpl ? $tpl : $defaultTpl);
	}

	public static function locateShopTemplate($template_names, $load = false, $require_once = true) {
		//Find eshop template.
		$located = '';
		foreach ((array)$template_names as $tplName) {
			if (!$tplName)
				continue;
			if (file_exists(MWS_PATH_TEMPLATE . '/' . $tplName)) {
				$located = MWS_PATH_TEMPLATE . '/' . $tplName;
				break;
			} elseif (file_exists(STYLESHEETPATH . '/' . $tplName)) {
				$located = STYLESHEETPATH . '/' . $tplName;
				break;
			} elseif (file_exists(TEMPLATEPATH . '/' . $tplName)) {
				$located = TEMPLATEPATH . '/' . $tplName;
				break;
			}
		}

		if ($load && '' != $located)
			load_template($located, $require_once);

		return $located;
	}

	public static function hookLocateTemplate_archive($defaultTpl) {
		$postTypes = array_filter((array)get_query_var('post_type'));
		$templates = array();
		if (count($postTypes) == 1 && $postTypes[0] = MWS_PRODUCT_SLUG) {
			$postTypes = reset($postTypes);
			$templates[] = "archive-{$postTypes}.php";
		}
//		$templates[] = 'archive.php';

		$tpl = (empty($templates) ? '' : static::locateShopTemplate($templates));
		return ($tpl ? $tpl : $defaultTpl);
	}

	/**
	 * Returns ID of the important shop page. If page id is not known, -1 is returned.
	 * @param $page string Name of requested page.
	 * @return int
	 */
	public static function getPageId($page) {
		$page = (string)$page;
		return absint(intval(get_option(MWS_OPTION . $page, -1)));
	}

	public static function renderTplParts($slug, $name = '', $toString = false) {
		$templates = array();
		$name = (string)$name;
		if ('' !== $name)
			$templates[] = "parts/{$slug}-{$name}.php";
		$templates[] = "parts/{$slug}.php";

		if ($toString) {
			ob_start();
			try {
				static::locateShopTemplate($templates, true, false);
				$str = ob_get_contents();
				ob_end_clean();
				return $str;
			} catch (Exception $e) {
				ob_end_clean();
				throw $e;
			}
		} else {
			static::locateShopTemplate($templates, true, false);
		}
	}

	function __get($name) {
		if ($name = 'gatewaySelectedId') {
			return isset($this->setting['pay_gate']['selected']) && (!empty($this->setting['pay_gate']['selected']))
				? $this->setting['pay_gate']['selected']
				: $this->gateways()->getDefaultFallbackId();
		}
		return null;
	}

	public function getVATs() {
		if (is_null($this->_vats)) {
			$gws = new MwsVATs();
			$this->_vats = $gws;
		}
		return $this->_vats;
	}

	function presenters_eshop_category_custom_fields($tax) {

		if (isset($tax->term_id)) {
			$t_id = $tax->term_id;
			$tax_meta = get_option("mws_eshop_category_fields_" . $t_id);
			$content = $tax_meta['category_image'];
		} else $content = '';
		?>

      <tr class="form-field">
          <th scope="row" valign="top">
              <label for="presenter_id"><?php echo __('Náhledový obrázek', 'mwshop'); ?></label>
          </th>
          <td>
						<?php cms_generate_field_image('mws_cat_meta[category_image]', 'mws_cat_meta_category_image', $content); ?>
          </td>
      </tr>

		<?php
	}

	function save_eshop_category_custom_fields($tax_id) {
		if (isset($_POST['mws_cat_meta'])) {
			$tax_meta = get_option("mws_eshop_category_fields_" . $tax_id);
			foreach ($_POST['mws_cat_meta'] as $key => $val) {
				$tax_meta[$key] = $_POST['mws_cat_meta'][$key];
			}
			//save the option array
			update_option("mws_eshop_category_fields_" . $tax_id, $tax_meta);
		}
	}

	/**
	 * Alter query for product according to paging and product visibility settings.
	 * @param WP_Query $query
	 * @return WP_Query
	 */
	function modifyProductQuery($query) {
		global $wp_query;
		// do not modify local queries --> leads to infinite recursion
		if ($wp_query == $query) {
			// do not modify query for admin pages and detail pages
			if (
				!is_admin() && !isset($query->query_vars['own_per_page']) && !$query->is_single()
				&& (
					(isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == MWS_PRODUCT_SLUG)
					|| isset($query->query_vars[MWS_PRODUCT_CAT_SLUG])
				)
			) {
				// Paging
				$per_page = intval(MWS()->visual_setting['per_page']);
				if (!$per_page) $per_page = 16;
				$query->set('posts_per_page', $per_page);

				// Visibility

				$invisibleIds = MwsProduct::getInvisibleProducts(true);
				if (!empty($invisibleIds)) {
					$query->set('post__not_in', $invisibleIds);
				}

				// Ordering
				if (isset(MWS()->setting['product_order'])) {
					$query->set('orderby', MWS()->setting['product_order']);
					if (MWS()->setting['product_order'] == 'title' || MWS()->setting['product_order'] == 'menu_order') $query->set('order', 'ASC');
				}
			}
		}
		return $query;
	}

	/**
	 * Alter query for product according to paging and product visibility settings.
	 * @param WP_Query $query
	 * @return WP_Query
	 */
	function modifySearchQuery($query) {
		if (isset($_GET['search_product']) && ((isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == MWS_PRODUCT_SLUG) || isset($query->query_vars[MWS_PRODUCT_CAT_SLUG]))) {
			$query->set('post_type', array(MWS_PRODUCT_SLUG));
			$query->set('s', $_GET['search_product']);
		};
		return $query;
	}

	public function ajaxGateDebug() {
		mwshoplog(__METHOD__, MWLL_DEBUG);

		//Test RENDER PARTS to string
		MWS()->current()->productId = 16;
		$str = mwsRenderParts('cart', 'action-add', true);
		echo $str;

		$gws = $this->gateways()->items;
		/** @var MwsGatewayMeta $gw */
		foreach ($gws as $gw) {
			if ($gw->isEnabled()) {
				$inst = $gw->sharedInstance();
				$gwStgs = $inst->meta->loadSettings();
				echo '<h1>Form ID</h1>';
				if (isset($gwStgs['form']['id'])) {
					echo '<pre>';
					print_r($gwStgs['form']['id']);
					echo "</pre>";
				}
				echo '<h1>Form Items</h1>';
				if (isset($gwStgs['form']['items'])) {
					echo '<pre>';
					print_r($gwStgs['form']['items']);
					echo "</pre>";
				}
				echo '<h1>Form ALL</h1>';
				if (isset($gwStgs['form'])) {
					echo '<pre>';
					print_r($gwStgs['form']);
					echo "</pre>";
				}
			}
		}
		wp_die();
	}

	/**
	 * Currently rendered entity. Stores information during rendering phase, like current product etc.
	 * @return MwsCurrent
	 */
	public function current() {
		if (!isset($this->_current))
			$this->_current = new MwsCurrent();
		return $this->_current;
	}

	/** Add new item into the cart. Expects "count" and "product" to be set. */
	public function ajaxCartAdd() {
		$productId = $_REQUEST['product'];
		$count = $_REQUEST['count'];

		$added = $this->cart->addItem($productId, $count);
		//TODO Make output pretty.
		if ($added > 0)
			echo "Do kosiku bylo vlozeno $count kus(u) polozky $productId.";
		elseif ($added == 0)
			echo "Do kosiku nebylo nic pridano";
		else
			echo "Chyba pri vkladani do kosiku.";

		wp_die();
	}

	/**
	 * Initialization of MioShop. This is called in the Wordpress INIT hook, queued as the first item.
	 */
	public function init() {
//		mwshoplog(__METHOD__, MWLL_DEBUG);
//		mwshoplog(admin_url('admin-ajax.php'),'AJAX URL', MWLL_DEBUG);
		static::checkInstallation();
		MwsTypesRegistration::registerAll();

		// Classes/actions loaded for the frontend and for ajax requests.
		if ($this->isRequest('frontend')) {
			$this->cart = new MwsCart();
//			$this->customer = new MwsCustomer();
		}
	}

	/**
	 * Check installation status. Install or upgrade system if necessary.
	 */
	public function checkInstallation() {
//		mwshoplog(__METHOD__, MWLL_DEBUG);
		if (!defined('IFRAME_REQUEST') && get_option('mwshop_version') !== static::version) {
			MwsInstall::autoInstall();
		} else
			mwshoplog('MioShop at version ' . static::version . ' is used.', MWLL_DEBUG);

	}

	/**
	 * What type of request is being processed?
	 * string $type Possible values ajax|frontend|admin|cron
	 * @return bool
	 */
	private function isRequest($type) {
		switch ($type) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined('DOING_AJAX');
			case 'cron' :
				return defined('DOING_CRON');
			case 'frontend' :
				return ((!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON'));
			default:
				return false;
		}
	}

	public function wpLoaded() {
		if ($this->inMioweb && $this->isShop()) {
			global $vePage, $cms;
			$vePage->modul_type = 'eshop';
			//add eshop setting codes
			$codes = get_option('eshop_codes');

			$cms->add_script('header', $codes['head_scripts']);
			$cms->add_script('footer', $codes['footer_scripts']);
			$cms->add_script('css', $codes['css_scripts']);

			$step = (isset($_REQUEST['step'])) ? (int)$_REQUEST['step'] : '';
			$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);
			if ($step == MwsOrderStep::Cart) {
				if (isset(MWS()->setting['cart_content'])) {
					$fonts = get_post_meta(MWS()->setting['cart_content'], 've_google_fonts', true);
					$vePage->google_fonts = $vePage->merge_fonts($vePage->google_fonts, $fonts);
				}
			} else if ($step == MwsOrderStep::ThankYou) {
				if (isset(MWS()->setting['thanks_content'])) {
					$fonts = get_post_meta(MWS()->setting['thanks_content'], 've_google_fonts', true);
					$vePage->google_fonts = $vePage->merge_fonts($vePage->google_fonts, $fonts);
				}
			}

		}


		if ($this->isCreated()) {
			add_action('body_class', array($this, 'addBodyClass'));
			//cart to header
			if ($this->showCart())
				add_action('cms_after_menu', array($this, 'insertCartToHeader'));
		}

		// modify search query
		add_action('pre_get_posts', array($this, 'modifySearchQuery'));

		if (isset($_GET['create_mw_eshop']))
			$this->createEshop();
	}

	function isShop() {
		global $vePage;
		return ((is_post_type_archive(MWS_PRODUCT_SLUG) || is_singular(MWS_PRODUCT_SLUG) || is_tax(MWS_PRODUCT_CAT_SLUG)
				|| ($this->getHomePage() && $vePage->post_id == $this->getHomePage()) || ($this->getOrderPage() && $vePage->post_id == $this->getOrderPage()))
			&& !isset($_GET['window_editor'])) ? true : false;
	}

	function isCreated() {
		if (get_option('mw_eshop_created')) return true;
		else {
			if ($this->setting['home_page'] && $this->setting['order_page']) {
				update_option('mw_eshop_created', '1');
				return true;
			} else return false;
		}
	}

	function showCart() {
		global $vePage;
		$show = ($vePage->modul_type == 'eshop' || ($vePage->modul_type == 'member' && isset($this->visual_setting['show_cart_header']['show_member'])) || ($vePage->modul_type == 'blog' && isset($this->visual_setting['show_cart_header']['show_blog'])) || ($vePage->modul_type == 'web' && isset($this->visual_setting['show_cart_header']['show_web']))) ? true : false;
		if (isset($vePage->header_setting['hide_cart'])) $show = false;
		return $show;
	}

	function createEshop() {
		if (!$this->isCreated()) {
			global $cms, $vePage;
			// create eshop page
			$post = array(
				'post_title' => __('Eshop', 'mwshop'),
				'post_name' => __('eshop', 'mwshop'),
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_type' => 'page',
				'post_author' => 1,
			);

			$temp_layer = array(
				'0' => array(
					'class' => '',
					'style' => array(
						'background_color' => array(
							'color1' => '#303030',
							'color2' => '',
							'transparency' => '100',
						),
						'background_setting' => 'image',
						'background_image' => array(
							'cover' => 'cover',
							'color_filter' => 'color_filter',
							'overlay_color' => '#121212',
							'overlay_transparency' => '71',
							'position' => 'center center',
							'repeat' => 'no-repeat',
							'image' => 'http://servis.mioweb.cz/graphic/bg/default_shop.jpg',
							'imageid' => '',
							'pattern' => '',
						),
						'background_delay' => '3000',
						'background_speed' => '1500',
						'background_video_mp4' => '',
						'background_video_webm' => '',
						'background_video_ogg' => '',
						'video_setting' => array(
							'is_saved' => '1',
						),
						'font' => array(
							'font-size' => '',
							'font-family' => '',
							'weight' => '',
							'color' => '#d6d6d6',
						),
						'link_color' => '',
						'type' => 'basic',
						'padding_top' => '200',
						'padding_bottom' => '200',
						'padding_left' => array(
							'size' => '',
							'unit' => 'px',
						),
						'padding_right' => array(
							'size' => '',
							'unit' => 'px',
						),
						'margin_t' => array(
							'size' => '',
						),
						'margin_b' => array(
							'size' => '',
						),
						'border-top' => array(
							'size' => '0',
							'style' => 'solid',
							'color' => '',
						),
						'border-bottom' => array(
							'size' => '0',
							'style' => 'solid',
							'color' => '',
						),
						'height_setting' => array(
							'arrow_color' => '#fff',
						),
						'min-height' => '',
						'css_class' => '',
						'delay' => '',
					),
					'content' => array(
						'0' => array(
							'type' => 'col-one',
							'class' => '',
							'content' => array(
								'0' => array(
									'type' => 'title',
									'content' => '<p style="text-align: center;">' . __('ÚVODNÍ STRÁNKA VAŠEHO NOVÉHO E-SHOPU', 'mwshop') . '</p>',
									'style' => array(
										'font' => array(
											'font-size' => '50',
											'font-family' => '',
											'weight' => '',
											'line-height' => '1.2',
											'color' => '#ffffff',
											'text-shadow' => 'none',
										),
										'style' => '1',
										'border' => array(
											'size' => '1',
											'color' => '#d5d5d5',
										),
										'background-color' => array(
											'color1' => '#efefef',
											'color2' => '',
											'transparency' => '100',
										),
										'align' => 'center',
									),
									'config' => array(
										'max_width' => '700',
										'margin_top' => '0',
										'margin_bottom' => '30',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									),
								),
								'2' => array(
									'type' => 'text',
									'content' => '<p style="text-align: center;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque euismod ex quis risus ornare dapibus. Cras id felis purus. Ut eros risus, pellentesque eget congue et, tempus non nisl.</p>',
									'style' => array(
										'font' => array(
											'font-size' => '17',
											'font-family' => '',
											'weight' => '',
											'line-height' => '',
											'color' => '',
										),
										'li' => '',
										'style' => '1',
										'p-background-color' => array(
											'color1' => '#e8e8e8',
											'color2' => '',
											'transparency' => '100',
										),
									),
									'config' => array(
										'max_width' => '600',
										'margin_top' => '0',
										'margin_bottom' => '20',
										'delay' => '',
										'animate' => '',
										'id' => '',
										'class' => '',
									),
								),
							),
						),
					),
				)
			);


			$home_id = $vePage->save_new_page($post, 'page/1/', $vePage->code($temp_layer));


			// create order page
			$post = array(
				'post_title' => __('Košík', 'mwshop'),
				'post_name' => __('kosik', 'mwshop'),
				'post_status' => 'publish',
				'comment_status' => 'open',
				'post_type' => 'page',
				'post_author' => 1,
			);

			$order_id = $vePage->save_new_page($post, 'page/1/', $vePage->code(array()));

			// save setting
			$setting = $cms->get_default_option($cms->page_set[MWS_OPTION_SHOP_SETTING]);
			$setting['home_page'] = $home_id;
			$setting['order_page'] = $order_id;

			update_option(MWS_OPTION_SHOP_SETTING, $setting);

			// save shipping

			$post = array(
				'post_title' => __('Osobní odběr', 'mwshop'),
				'post_name' => __('osobni-odber', 'mwshop'),
				'post_status' => 'publish',
				'post_excerpt' => __('Zboží si můžete vyzvednout na naší prodejně.', 'mwshop'),
				'post_type' => MWS_SHIPPING_SLUG,
				'post_author' => 1,
			);
			$shipping_id = wp_insert_post($post);
			update_post_meta($shipping_id, 'shipping', array(
				'price' => 0,
				'vat_id' => 0,
				'personal_pickup' => 1,
				'cod_enabled' => 1
			));

			$post = array(
				'post_title' => __('Poštou', 'mwshop'),
				'post_name' => __('postou', 'mwshop'),
				'post_status' => 'publish',
				'post_excerpt' => __('Zboží Vám bude doručeno jako balík Českou poštou.', 'mwshop'),
				'post_type' => MWS_SHIPPING_SLUG,
				'post_author' => 1,
			);
			$shipping_id = wp_insert_post($post);
			update_post_meta($shipping_id, 'shipping', array(
				'price' => 99,
				'vat_id' => 0,
			));

			// save visual setting
			$visual_setting = $cms->get_default_option($cms->page_set['eshop_appearance']);
			update_option('eshop_appearance', $visual_setting);

			// mark eshop created
			update_option('mw_eshop_created', '1');

			// activate rewrite rules for eshop products and categories
			flush_rewrite_rules();

			wp_redirect(get_permalink($home_id));
			die();
		}

	}

	/** Get currency conversion rate of 1 unit in "from" into "to" currency. Assures positive result.
	 * @param string|MwsCurrency $from
	 * @param string|MwsCurrency $to
	 * @return float Positive value, taken from eshop settings. If rates are not set, then default conversion rate is used.
	 */
	public function getCurrencyConversionRate($from, $to) {
		$arr = $this->getCurrencyConversionTable();
		if (!isset($arr[$from][$to])) {
			$rate = isset($arr[$from][$to]) ? (float)$arr[$from][$to] : 1;
			if ($rate <= 0) {
				$rate = 1.0; // false backup
			}
			// Save counted rate back into table for further use.
			$arr[$from][$to] = $rate;
			$this->currencyConversionTable = $arr;
		}
		return $arr[$from][$to];
	}

	public function getCurrencyConversionTable() {
		if (is_null($this->currencyConversionTable)) {
			$this->currencyConversionTable[MwsCurrency::czk][MwsCurrency::eur] =
              isset($this->setting['currency_conversion_CZK2EUR']) && !empty($this->setting['currency_conversion_CZK2EUR'])
				? $this->setting['currency_conversion_CZK2EUR']
				: MwsCurrency::getDefaultConversion(MwsCurrency::czk, MwsCurrency::eur);
			$this->currencyConversionTable[MwsCurrency::eur][MwsCurrency::czk] =
              isset($this->setting['currency_conversion_EUR2CZK']) && !empty($this->setting['currency_conversion_EUR2CZK'])
				? $this->setting['currency_conversion_EUR2CZK']
				: MwsCurrency::getDefaultConversion(MwsCurrency::eur, MwsCurrency::czk);

			// For inverse conversion of primary currency use exact inversion of forward conversion.
			$globalCurrency = MWS()->getCurrency('key');
			foreach (MwsCurrency::getAll() as $otherCurrency) {
				if (isset($this->currencyConversionTable[$globalCurrency][$otherCurrency])) {
					$forwardRate = $this->currencyConversionTable[$globalCurrency][$otherCurrency];
					if ($forwardRate > 0) {
						$backwardRate = 1 / $forwardRate;
						$this->currencyConversionTable[$otherCurrency][$globalCurrency] = $backwardRate;
					}
				}
			}
		}
		return $this->currencyConversionTable;
	}

	/** Get currency string.
	 * @param string $format Possibla values are: "html"=printable into html, "attr"=printable into html attribute,
	 *                       "raw"=no formatting=default formatting, "key"=value of {@link MwsCurrency}
	 * @return string
	 */
	public function getCurrency($format = 'raw') {
		$currency = (isset($this->setting['currency']))
			? MwsCurrency::checkedValue($this->setting['currency'], MwsCurrency::getDefault())
			: MwsCurrency::getDefault();
		$text = MwsCurrency::getSymbol($currency);
		switch ($format) {
			case 'html':
				return esc_html($text);
				break;
			case 'attr':
				return esc_attr($text);
				break;
			case 'key':
				return $currency;
				break;
			default:
				return $text;
		}
	}

	/**
	 * Returns list of availability statuses, that should be hidden.
	 * @param string $type One of [product|variant]
	 * @return array Array of <a href='psi_element://MwsProductAvailabilityStatus'>MwsProductAvailabilityStatus</a>.
	 */
	public function getHiddenAvailabilityStatusesFor($type = '') {
		if ($type == 'product') {
			$showThem = $this->stgShowUnavailableProduct();
		} elseif ($type = 'variant') {
			$showThem = $this->stgShowUnavailableVariant();
		} else {
			$showThem = false;
		}
		return $showThem
			? array()
			: array(
				MwsProductAvailabilityStatus::Unavailable_Disabled,
				MwsProductAvailabilityStatus::Unavailable_OutOfStock,
			);
	}

	/**
	 * Should be unavailable products displayed in catalog?
	 * @return bool
	 */
	public function stgShowUnavailableProduct() {
		return isset($this->setting['eshop_display_product']['unavailable_product']) && $this->setting['eshop_display_product']['unavailable_product'];
	}

	/**
	 * Should be unavailable product variant displayed in catalog?
	 * @return bool
	 */
	public function stgShowUnavailableVariant() {
		return isset($this->setting['eshop_display_product']['unavailable_variant']) && $this->setting['eshop_display_product']['unavailable_variant'];
	}

	public function hookAdminScreen() {
		if (!$screen = get_current_screen()) {
			return;
		}

		switch ($screen->id) {
			case 'dashboard' :
				break;
			case 'options-permalink' :
				MwsRewrite::extendAdminPage();
				break;
			case 'users' :
			case 'user' :
			case 'profile' :
			case 'user-edit' :
				break;
		}
	}

	public function hookSaveOrder($postId, $post) {
		if (get_post_type($post) !== MWS_ORDER_SLUG)
			return;
		mwshoplog(__METHOD__ . ' postId=' . $postId, MWLL_DEBUG);
		$order = MwsOrder::createNew(get_post($postId));
		$newStatus = isset($_REQUEST['order_status']) ? (int)$_REQUEST['order_status'] : '';
		$curStatus = $order->status;
		if (MwsOrderStatus::isValidValue($newStatus) && $newStatus <> $curStatus) {
			switch ($newStatus) {
				case MwsOrderStatus::Ordered:
					$order->changeStatus($newStatus);
					break;
				case MwsOrderStatus::Processing:
					$order->changeStatus($newStatus);
					break;
				case MwsOrderStatus::Delivered:
					$order->changeStatus($newStatus);
					break;
				case MwsOrderStatus::Closed:
					$order->changeStatus($newStatus);
					break;
				case MwsOrderStatus::Cancelled:
					$order->setCancelled();
					break;
			}
		}
	}

	public function hookSaveProductShipping($postId, $post) {
		if (cms_is_saving() || cms_is_saved_disabled())
			return;
		// Do not fire for autosave.
		if (mwsIsPostAutosaveOrUnpublished($postId))
			return;
		if (get_post_type($post) !== MWS_SHIPPING_SLUG)
			return;
		mwshoplog(__METHOD__ . ' postId=' . $postId, MWLL_DEBUG);

        $shipping = MwsShipping::getById($postId, true);
		$gws = $this->gateways();

        $isSyncNeeded = $gws->isItemSyncNeeded($shipping->sync);
        if ($isSyncNeeded) {
            mwshoplog(sprintf(__('Provedené změny způsobu doručení "%s" [%d] vyžadují synchronizaci do platební brány.', 'mwshop'),
                $shipping->name, $shipping->id), MWLL_INFO, 'paygate');
            $gws->clearSyncedAll();
            $this->async_SyncAllNeeded();
        }
	}

	public function hookAfterSaveProduct($postId, $post) {
		if (cms_is_saving() || cms_is_saved_disabled())
			return;
		// Do not fire for autosave.
		if (mwsIsPostAutosaveOrUnpublished($postId))
			return;
		if (get_post_type($post) !== MWS_PRODUCT_SLUG)
			return;
		mwshoplog(__METHOD__ . ' postId=' . $postId, MWLL_DEBUG);

		$isSyncNeeded = false;
		$gws = $this->gateways();

		$product = MwsProduct::getById($postId, true);
		if ($product) {
			mwshoplog(sprintf(__('Uložen produkt "%s" [%d].', 'mwshop'), $product->name, $product->id), MWLL_INFO);
			// Update variants if needed
			if ($product->structure === MwsProductStructureType::Variants) {
				/** @var MwsProductRoot $variantProduct */
				$variantProduct = $product;

				// Save new, update existing, remove deleted variants.
				$this->updateVariantsOf($variantProduct);

				// Process the table of variants and run synchronization if necessary.
				foreach ($variantProduct->variants as $variant) {
					$isSyncNeeded |= $gws->isItemSyncNeeded($variant->sync);
				}
			}

			//original product
			$isSyncNeeded |= $gws->isItemSyncNeeded($product->sync);
			if ($isSyncNeeded) {
				mwshoplog(sprintf(__('Provedené změny produktu "%s" [%d] vyžadují synchronizaci do platební brány.', 'mwshop'),
					$product->name, $product->id), MWLL_INFO, 'paygate');
				$gws->clearSyncedAll();
				$this->async_SyncAllNeeded();
			}
		}
	}

	/**
	 * @param $product MwsProductRoot
	 */
	public function updateVariantsOf(&$product) {
		// Update variants if needed
		if ($product->structure === MwsProductStructureType::Variants) {
			$variantList = $product->variantDefinition;
			$preservedVariants = array();

			// Process the table of variants - create and update
			if (isset($variantList['variants']) && is_array($variantList['variants'])
				&& isset($variantList['parametres']) && is_array($variantList['parametres'])
			) {
				$stockEnabled = $product->stockEnabled;
				$parameters = $variantList['parametres'];
				foreach ($variantList['variants'] as $varArrKey => $varDef) {
					$variantId = isset($varDef['variant_id']) ? (int)$varDef['variant_id'] : 0;
					$properties = isset($varDef['property']) ? $varDef['property'] : array();
					$price = isset($varDef['price']) ? $varDef['price'] : 0;
					$priceSale = isset($varDef['price_sale']) ? $varDef['price_sale'] : null;
					$stockCount = $stockEnabled
						? isset($varDef['stock_count']) ? (int)$varDef['stock_count'] : 0
						: false;
					$thumbId = isset($varDef['image_id']) && !empty($varDef['image_id']) ? (int)$varDef['image_id'] : false;

					// Make sure that all requested parameters are set within $properties. This will force to their validation.
					foreach ($parameters as $parameter) {
						$parameter = (int)$parameter;
						if (!isset($properties[$parameter])) {
							$properties[$parameter] = '';
						}
					}

					$codes = isset($varDef['codes']) ? $varDef['codes'] : array();

					$variant = null;
					try {
						if ($variantId && $variant = MwsProductVariant::getById($variantId)) {
							// Existing variant -> update it
							$preservedVariants[] = $variantId;
							try {
								$variant->updateVariant($properties, $price, $priceSale, $stockCount, $codes);
								mwshoplog(sprintf(__('Varianta produktu "%s" [%d] aktualizována.', 'mwshop'), $variant->name, $variant->id), MWLL_INFO, 'variant');
								unset($variantList['variants'][$varArrKey]['error']);
							} catch (MwsException $e) {
								mwshoplog(sprintf(__('Variantu produktu "%s" se nepodařilo aktualizovat. %s', 'mwshop'), $product->name, $e->getMessage()),
									MWLL_ERROR, 'variant');
								$variantList['variants'][$varArrKey]['error'] = nl2br(__('Variantu se nepodařilo aktualizovat.', 'mwshop')
									. "\n" . $e->getMessage());
							}
						} else {
							// New variant
							try {
								$variant = MwsProductVariant::createVariant($product, $properties, $price, $priceSale, $stockCount, $codes);
								if ($variant) {
									mwshoplog(sprintf(__('Varianta produktu "%s" [%d] vytvořena.', 'mwshop'), $variant->name, $variant->id), MWLL_INFO, 'variant');
									// Propagate value of created variant back into variant definition list. It wil be saved withing the product fields.
									$variantList['variants'][$varArrKey]['variant_id'] = $variantId = $variant->id;
									unset($variantList['variants'][$varArrKey]['error']);
									$preservedVariants[] = $variantId;
								} else {
									mwshoplog(sprintf(__('Variantu produktu "%s" se nepodařilo vytvmořit.', 'mwshop'), $product->name), MWLL_ERROR, 'variant');
									$variantList['variants'][$varArrKey]['error'] = __('Variantu se nepodařilo vytvořit.', 'mwshop');
								}
							} catch (MwsException $e) {
								mwshoplog(sprintf(__('Variantu produktu "%s" se nepodařilo vytvořit. %s', 'mwshop'), $product->name, $e->getMessage()),
									MWLL_ERROR, 'variant');
								$variantList['variants'][$varArrKey]['error'] = nl2br(__('Variantu se nepodařilo vytvořit.', 'mwshop')
									. "\n" . $e->getMessage());
							}
						}
					} catch (Exception $e) {
						mwshoplog(sprintf(__('Došlo k chybě při zpracování definice varianty "%d" produktu [%d].', 'mwshop'), $varArrKey, $variantId)
							. "\n" . $e->getMessage()
							, MWLL_ERROR, 'variant');
						$variantList['variants'][$varArrKey]['error'] = sprintf(__('Definici varianty "%d" se nepodařilo zpracovat.', 'mwshop'), $varArrKey);
					}

					// Update thumbnail
					if ($variant) {
						$oldThumbId = get_post_thumbnail_id($variant->post);
						if ($oldThumbId != $thumbId) {
							if ($thumbId) {
								$res = set_post_thumbnail($variant->post, $thumbId);
								if (!$res) {
									mwshoplog(sprintf(__('Nepodařilo se aktualizovat náhled varianty "%s".', 'mwshop'), $variant->name, $variantId),
										MWLL_WARNING, 'variant');
									$variantList['variants'][$varArrKey]['error'] =
										(empty($variantList['variants'][$varArrKey]['error']) ? '' : "<br />\n")
										. __('Nepodařilo se aktualizovat náhled.', 'mwshop');
								}
							} else {
								delete_post_thumbnail($variant->post);
							}
						}
					}

				}
			}

			// Remove unused variants
			/** @var MwsProductRoot $rootProduct */
			$rootProduct = $product;
			$variants = $rootProduct->getVariants(array()); // get all variants including concepts etc.
			/** @var MwsProductVariant $variant */
			foreach ($variants as $variant) {
				$variantId = $variant->id;
				$preserve = in_array($variantId, $preservedVariants);
				if (!$preserve) {
				    //TODO Statistic of updated+added+deleted --> synchronization + errors to UI
					wp_delete_post($variantId);
					mwshoplog(sprintf(__('Varianta produktu "%s" [%d] odebrána.', 'mwshop'), $variant->name, $variantId), MWLL_INFO, 'variant');
				}
			}

			// Update field
			mwshoplog("Saving variant list definition for [$product->id]", MWLL_DEBUG, 'save');
			$product->variantDefinition = $variantList;

			$id = $product->id;
			$product = MwsProduct::getById($id, true);
		}
	}

	public function hookShopOptionsChanged($value, $oldValue, $option /*$oldValue, $value*/) {
		mwshoplog(__METHOD__, MWLL_DEBUG);
		$resync = false;
		$gws = $this->gateways();

		// Optimization to check VAT and force resync only when needed. Gate's special settings sets the "isSynced" flag on its own.
		$oldVats = isset($oldValue['vat_values']) && is_array($oldValue['vat_values']) ? $oldValue['vat_values'] : array();
		$newVats = isset($value['vat_values']) && is_array($value['vat_values']) ? $value['vat_values'] : array();
		if (!($oldVats == $newVats)) {
			mwshoplog('VAT levels have been changed.', MWLL_INFO, 'settings');
			$resync = true;
		}

		$oldVatAccounting = isset($oldValue['vat_accounting']) ? $oldValue['vat_accounting'] : MwsVatAccounting::noVat;
		$newVatAccounting = isset($value['vat_accounting']) ? $value['vat_accounting'] : MwsVatAccounting::noVat;
		if (!($oldVatAccounting == $newVatAccounting)) {
			mwshoplog('VAT accounting have been changed.', MWLL_INFO, 'settings');
			$resync = true;
		}

		// Order page changed? This is used in callbacks
		$oldCart = isset($oldValue['order_page']) ? $oldValue['order_page'] : '';
		$newCart = isset($value['order_page']) ? $value['order_page'] : '';
		if (!($oldCart == $newCart)) {
			mwshoplog('Order/cart page changed.', MWLL_INFO, 'settings');
			$resync = true;
		}

		// Currency changed
		$oldCurrency = isset($oldValue['currency']) ? $oldValue['currency'] : '';
		$newCurrency = isset($value['currency']) ? $value['currency'] : '';
		if (!($oldCurrency == $newCurrency)) {
			mwshoplog("Currency changed from [$oldCurrency] to [$newCurrency].", MWLL_INFO, 'settings');
			$resync = true;
		}

		// Currency conversion rate changed
		$oldCurrencyRate = isset($oldValue['currency_conversion_CZK2EUR']) ? $oldValue['currency_conversion_CZK2EUR'] : '';
		$newCurrencyRate = isset($value['currency_conversion_CZK2EUR']) ? $value['currency_conversion_CZK2EUR'] : '';
		if (!($oldCurrencyRate == $newCurrencyRate)) {
			mwshoplog("Currency rate of CZK2EUR changed from [$oldCurrencyRate] to [$newCurrencyRate].", MWLL_INFO, 'settings');
			$resync = true;
		}

		// Currency conversion rate changed
		$oldCurrencyRate = isset($oldValue['currency_conversion_EUR2CZK']) ? $oldValue['currency_conversion_EUR2CZK'] : '';
		$newCurrencyRate = isset($value['currency_conversion_EUR2CZK']) ? $value['currency_conversion_EUR2CZK'] : '';
		if (!($oldCurrencyRate == $newCurrencyRate)) {
			mwshoplog("Currency rate of EUR2CZK changed from [$oldCurrencyRate] to [$newCurrencyRate].", MWLL_INFO, 'settings');
			$resync = true;
		}


		if ($resync) {
			$gws->clearSyncedAll();
		}

		$this->async_SyncAllNeeded();

		/*		// Permalink structure changes
						$oldPermProduct = isset($oldValue['permalink_product']) && is_array($oldValue['permalink_product']) ? $oldValue['permalink_product'] : array();
						$newPermProduct = isset($value['permalink_product']) && is_array($value['permalink_product']) ? $value['permalink_product'] : array();
						$oldPermProductCat = isset($oldValue['permalink_product_category']) && is_array($oldValue['permalink_product_category']) ? $oldValue['permalink_product_category'] : array();
						$newPermProductCat = isset($value['permalink_product_category']) && is_array($value['permalink_product_category']) ? $value['permalink_product_category'] : array();
						if(!($oldPermProduct == $newPermProduct) || !($oldPermProductCat == $newPermProductCat) ) {
							mwdbg('Permalinks changed. Regenerating rewrite rules.');
							MwsRewrite::removePermalinks('');
							MwsRewrite::registerPermalinks(true, $newPermProduct, $newPermProductCat);
						}*/

		return $value;
	}

	/** Process modifications of gate's payment type settings. */
	public function hookOptionChanged_GatePayment($value, $oldValue, $option) {
		mwshoplog(__METHOD__, MWLL_DEBUG);
//		mwshoplog('option page = '.$_POST['option_page'], MWLL_DEBUG);
//		mwshoplog('option  = '.$option, MWLL_DEBUG);

		try {
			$gates = $this->gateways();
			/** @var MwsGatewayMeta $gw */
			foreach ($gates->items as $gw) {
				$resync = $gw->handleSettingsChanged_Payments();
				if ($resync) {
					$gw->isSynced = false;
					$this->async_SyncAllNeeded();
				}
			}
		} catch (Exception $e) {
			mwshoplog('Change of gate\'s payment settings could not be sent to gate. Request failed.' . ' ' . $e->getMessage() . __METHOD__, MWLL_ERROR, 'settings');
			mwnotice();
		}

		return $value;
	}

	/** Process modifications of gate general settings, typically gate's specialized form. */
	public function hookOptionChanged_GateSettings($value, $oldValue, $option) {
		mwshoplog(__METHOD__, MWLL_DEBUG);
//		mwshoplog('option page = '.$_POST['option_page'], MWLL_DEBUG);
//		mwdbg('option  = '.$option, MWLL_DEBUG);

		try {
			$gates = $this->gateways();
			/** @var MwsGatewayMeta $gw */
			foreach ($gates->items as $gw) {
				$resync = $gw->handleSettingsChanged_Form();
				if ($resync) {
					$gw->isSynced = false;
					$this->async_SyncAllNeeded();
				}
			}
		} catch (Exception $e) {
			mwshoplog('Change of gate\'s settings could not be sent to gate. Request failed.' . ' ' . $e->getMessage() . __METHOD__, MWLL_ERROR, 'settings');
			mwnotice();
		}

		return $value;
	}

	public function hookGetPageTemplate($templates, $id) {
		if ($this->getHomePage() == $id)
			$templates = array('eshop-home.php');
		else if ($this->getOrderPage() == $id)
			$templates = array('eshop-order.php');

		if (isset($_GET['window_editor']))
			$templates = array('window_editor.php');

		return $templates;
	}

	/**
	 * Custom hook for Mioweb. It adds possibility to override selected template by custom templated in module.
	 * @param $located      string Currently located template file that will be used, if the hook does not change it.
	 * @param $templateName string Name of the template file, e.g. "single.php".
	 * @return string Original or changed filename of the template file.
	 */
	public function hookLocateShopTemplateForMioweb($located, $templateName) {
		if ($templateName && file_exists(MWS_PATH_TEMPLATE . '/' . $templateName))
			$located = MWS_PATH_TEMPLATE . '/' . $templateName;
		return $located;
	}

	function enqueScripts() {
		$css = static::locateShopTemplate(array('shop.css'));
		if (file_exists($css))
			wp_register_style('mwsShop', $this->getTemplateFileUrl('shop.css'), array(), filemtime($css));

		wp_register_script('shop_front_script', $this->getTemplateFileUrl('shop.js'), array(), filemtime($this->getTemplateFileDir('shop.js')));

		if ($this->isShop() || $this->edit_mode) {

			wp_enqueue_script('shop_front_script');
			wp_enqueue_style('mwsShop');

			wp_enqueue_script('ve_lightbox_script');
			wp_enqueue_style('ve_lightbox_style');

		}
		if ($this->edit_mode) {
			wp_register_script('shop_admin_script', MWS_URL_BASE . '/js/admin.js', array(), filemtime(MWS_PATH_BASE . '/js/admin.js'));
			wp_enqueue_script('shop_admin_script');
		}

		//Administrating scripts
		if ($this->inMioweb) {
			if ($this->canEdit()) {
				//When on frontend, load admin scripts/CSS as well.
				$this->load_admin_scripts();
			}
		}

	}

	public function getTemplateFileUrl($file) {
		$file_url = $this->template['url'] . $file;
		return $file_url;
	}

	public function getTemplateFileDir($file) {
		$file_dir = $this->template['dir'] . $file;
		return $file_dir;
	}

	function load_admin_scripts() {
		$file = MWS_PATH_BASE . '/css/admin.css';
		wp_enqueue_style('shop_admin_css', /*static::getUrlFromFile($file)*/
			get_bloginfo('template_url') . '/modules/shop/css/admin.css', array(), filemtime($file));
		wp_register_script('shop_admin_script', MWS_URL_BASE . '/js/admin.js', array(), filemtime(MWS_PATH_BASE . '/js/admin.js'));
		wp_enqueue_script('shop_admin_script');
	}

	/**
	 * Transform absolute path of a file into URL. Potentially risky in very special cases of WP configuration.
	 * Functional in most cases.
	 * @param $file string File name with absolute path.
	 * @return string Filename modified into absolute URL.
	 */
	function getUrlFromFile($file) {
		if (empty($file) || !file_exists($file))
			return '';

		$rel = str_replace(ABSPATH, '', $file);

		$url = get_site_url(null, $rel);
		return $url;
	}

	/**
	 * Get URL for permalinks of product categories.
	 * @param array|null $stgPermProductCat Optional temporary settings as array that should be preferred over stored settings.
	 * @param array|null $stgPermProduct    Optional temporary settings as array that should be preferred over stored settings.
	 * @return string|void
	 */
	function getPermalink_ProductCat($stgPermProductCat = null, $stgPermProduct = null) {
		$permStg = is_null($stgPermProductCat)
			? (isset($this->permalinks['permalink_product_category']) ? $this->permalinks['permalink_product_category'] : array())
			: $stgPermProductCat;
		if (isset($permStg['use_nested']) && $permStg['use_nested']) {
			$valParent = $this->getPermalink_Products($stgPermProduct);
			$val = isset($permStg['value_nested']) && !empty($permStg['value_nested']) ? sanitize_title_with_dashes($permStg['value_nested'], MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT) : MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT;
			return $valParent . '/' . $val;
		} else {
			$val = isset($permStg['value']) && !empty($permStg['value']) ? sanitize_title_with_dashes($permStg['value'], MWS_PERMALINK_PRODUCT_CAT_DEFAULT) : MWS_PERMALINK_PRODUCT_CAT_DEFAULT;
			return $val;
		}
	}

	/**
	 * Get URL for permalinks of products.
	 * @param array|null $stgPermProduct Optional temporary settings as array that should be preferred over stored settings.
	 * @return string
	 */
	function getPermalink_Products($stgPermProduct = null) {
		$permStg = is_null($stgPermProduct)
			? isset($this->permalinks['permalink_product']) ? $this->permalinks['permalink_product'] : array()
			: $stgPermProduct;
		$val = isset($permStg['value']) && !empty($permStg['value']) ? sanitize_title_with_dashes($permStg['value'], MWS_PERMALINK_PRODUCT_DEFAULT) : MWS_PERMALINK_PRODUCT_DEFAULT;
		return $val;
	}

	/** Returns URL of home page of the shop - shop window */
	function getUrl_Home() {
		$pageId = $this->getHomePage();
		if ($pageId) {
			$url = get_permalink($pageId);
			return $url;
		} else
			return '';
	}

	/**
	 * Return URL of AJAX calls.
	 * @return string
	 */
	function getUrl_Ajax($queryParams = array()) {
		$url = admin_url('admin-ajax.php');
		if (!empty($queryParams) && is_array($queryParams))
			$url = add_query_arg($queryParams, $url);
		return $url;
	}

	/** Returns URL of terms and conditions page of the shop
	 * @return string
	 */
	function getUrl_TermsAndCondtions() {
		$pageId = $this->getTermsPageId();
		if ($pageId) {
			$url = get_permalink($pageId);
			return $url;
		} else
			return '';
	}

	function getTermsPageId() {
		return (isset($this->setting['terms'])) ? $this->setting['terms'] : 0;
	}

	/** Returns URL of personal data protection page
	 * @return string
	 */
	function getUrl_PersonalDataProtection() {
		$pageId = $this->getPersonalDataProtectionPageId();
		if ($pageId) {
			$url = get_permalink($pageId);
			return $url;
		} else
			return get_home_url();
	}

	function getPersonalDataProtectionPageId() {
		return (isset($this->setting['personalDataProtection'])) ? $this->setting['personalDataProtection'] : 0;
	}

	/**
	 * Generates URL to add a product/products into the cart.
	 * @param int|array $productId
	 * @return false|string
	 */
	function getUrl_CartAdd($productId = null, $count = 1) {
		$url = $this->getUrl_Cart();
		if (!empty($url)) {
			$arr = array('operation' => 'add');
			if (!empty($productId)) {
				$arr['product'] = $productId;
				if ($count > 1)
					$arr['count'] = $count;
			}
			$url .= '?' . http_build_query($arr);
		}
		return $url;
	}

	/** Returns URL of cart/order page of the shop */
	function getUrl_Cart($step = null) {
		$pageId = $this->getOrderPage();
		if ($pageId) {
			$url = get_permalink($pageId);
			if (!empty($step))
				$url = add_query_arg(array('step' => $step), $url);
			return $url;
		} else
			return '';
	}

	/**
	 * Generates URL to remove a product/products from the cart.
	 * @param int|array $productId
	 * @return false|string
	 */
	function getUrl_CartRemove($productId = null, $count = 1) {
		$url = $this->getUrl_Cart();
		if (!empty($url)) {
			$arr = array('operation' => 'remove');
			if (!empty($productId)) {
				$arr['product'] = $productId;
			}
			$url .= '?' . http_build_query($arr);
		}
		return $url;
	}

	function getEditCategoryLink($id = null) {
		$content = '';
		if (is_null($id)) {
			global $post;
			$postId = $post->ID;
		}

		global $blog_module;
		$content = $blog_module->edit_post_bar($id);

		return $content;
	}

	function create_eshop_menu() {
		if ($this->isCreated()) {
			return '<ul> 
            <li><a target="_blank" href="' . admin_url('post-new.php?post_type=' . MWS_PRODUCT_SLUG) . '">' . __('Nový produkt', 'mwshop') . '</a></li>
            <li><a target="_blank" href="' . admin_url('edit.php?post_type=' . MWS_PRODUCT_SLUG) . '">' . __('Seznam produktů', 'mwshop') . '</a></li>
            <li><a target="_blank" href="' . admin_url('edit.php?post_type=' . MWS_ORDER_SLUG) . '">' . __('Objednávky', 'mwshop') . '</a></li>
            <li><a target="_blank" href="' . admin_url('edit-tags.php?taxonomy=' . MWS_PRODUCT_CAT_SLUG) . '">' . __('Kategorie', 'mwshop') . '</a></li>
            <li><a target="_blank" href="' . admin_url('edit.php?post_type=' . MWS_SHIPPING_SLUG) . '">' . __('Způsoby doručení', 'mwshop') . '</a></li>
            <li><a class="open-setting" data-setting="' . MWS_OPTION_SHOP . '" title="' . __('Nastavení eshopu', 'mwshop') . '" href="' . admin_url('admin.php?page=eshop_option') . '">' . __('Nastavení eshopu', 'mwshop') . '</a></li>
        </ul>';
		} else {
			$login = get_option('ve_connect_fapi');
			if ($login && is_fapi_connected($login['login'], $login['password'])) {
				return '<ul> 
                <li><a title="' . __('Vytvořit eshop', 'mwshop') . '" href="' . get_home_url() . '/?create_mw_eshop=1">' . __('Vytvořit eshop', 'mwshop') . '</a></li>
            </ul>';
			} else {
				return '<ul> 
                <li><a class="mws_eshop_activation" title="' . __('Vytvořit eshop', 'mwshop') . '" href="#">' . __('Vytvořit eshop', 'mwshop') . '</a></li>
            </ul>';
			}
		}
	}

	function useEshopVisual() {
		global $vePage, $cms;
		if ($this->isShop()) {

			$vePage->page_setting['background_color'] = $this->visual_setting['background_color'];
			$vePage->page_setting['background_image'] = $this->visual_setting['background_image'];

			$setting = get_option('eshop_header');
			if ($setting['show'] == 'eshop') {
				$vePage->header_setting = $setting;
				$vePage->h_menu = (isset($vePage->header_setting['menu'])) ? $vePage->header_setting['menu'] : '';
			}
			$setting = get_option('eshop_footer');
			if ($setting['show'] == 'eshop') {
				$vePage->footer_setting = $setting;
				$vePage->f_menu = (isset($vePage->footer_setting['menu'])) ? $vePage->footer_setting['menu'] : '';
			}

			$vePage->popups->popups_setting = get_option('eshop_popups');
		}
		$vePage->add_styles(array(
			".mw_to_cart svg path, 
        .mw_to_cart svg circle" => array(
				'fill' => $vePage->header_setting['menu_font']['color'],
			),
			".mw_to_cart:hover svg path, 
        .mw_to_cart:hover svg circle" => array(
				'fill' => $vePage->header_setting['menu_active_color'],
			),
			"a.mws_product_title:hover, 
        .mws_top_panel .mw_vertical_menu li a:hover, 
        .mws_top_panel .mw_vertical_menu li a.mws_category_item_current,
        .mws_shop_order_content h2 span.point" => array(
				'color' => $this->visual_setting['eshop_color'],
			),
			".eshop_color_background, 
        .add_tocart_button, 
        .remove_fromcart_button, 
        .mws_cart_navigation:after" => array(
				'background-color' => $this->visual_setting['eshop_color'],
			),
			"a.eshop_color_background:hover, 
        .add_tocart_button:hover" => array(
				'background-color' => $vePage->shiftColor($this->visual_setting['eshop_color'], 0.9),
			),
			".mws_dropdown:hover .mws_dropdown_button, .mws_dropdown.mws_dropdown_opened .mws_dropdown_button" => array(
				'background-color' => $vePage->shiftColor($this->visual_setting['eshop_color'], 0.9),
			),
			".eshop_color_svg_hover:hover svg path" => array(
				'fill' => $this->visual_setting['eshop_color'],
			),
			".mw_tabs_element_style_3 .mw_tabs a.active, 
        .mws_shop_order_content h2 span.point" => array(
				'border-color' => $this->visual_setting['eshop_color'],
			),
			".mws_cart_step_item_a span.arrow" => array(
				'border-left' => '8px solid ' . $this->visual_setting['eshop_color'],
			),

		));
	}

	function insertCartToHeader() {

		$cart = $this->cart;
		$cartItemsCount = $cart->items->count();

		?>
      <div id="mw_header_cart">
          <a class="mw_to_cart"
             href="<?php echo $this->getUrl_Cart(); ?>"><?php echo file_get_contents($this->getTemplateFileDir("img/cart.svg"), true); ?>
              <span class="mws_cart_items_count"><?php echo $cartItemsCount; ?></span></a>
          <div class="mws_header_cart_hover <?php if ($cart->items->isEmpty()) echo 'mws_header_cart_hover_empty'; ?>">
						<?php
						echo '<div class="mws_header_empty">' . __('Košík je prázdný', 'mwshop') . '</div>';
						echo '<table>';
						if (!$cart->items->isEmpty()) {
							foreach ($cart->items->data as $cartItem) {
								MWS()->current()->cartItem = $cartItem;
								mwsRenderParts('cart', 'hover-items');
							}
						}
						echo '</table>';
						echo '<div class="mws_header_cart_footer">';
						echo '<a class="ve_content_button ve_content_button_1 eshop_color_background" href="' . $this->getUrl_Cart() . '">' . __('Do košíku', 'mwshop') . '</a>';
						echo '</div>';
						?>
          </div>
      </div>
		<?php
	}

	/**
	 * Render HTML for purposes.
	 *
	 * @param $purposes array List of purposes as described by {@link MwsGatewayMeta::getPurposes()}.
	 *
	 * @return string
	 */
	function renderPurposes($purposes) {
		$primaryPurpose = null;
		foreach ( $purposes as $purpose ) {
			if ( isset( $purpose['is_primary'] ) && $purpose['is_primary'] ) {
				$primaryPurpose = $purpose;
				break;
			}
		}

		$code = '';
		// Do we have something to display for primary purpose?
		if ( isset( $primaryPurpose['checkbox_label'] ) || $primaryPurpose['link_href'] ) {
			$purposeText =
				( isset( $primaryPurpose['checkbox_label'] ) ? $primaryPurpose['checkbox_label'] . ' ' : '' ) .
				( isset( $primaryPurpose['link_href'] )
					? '<a target="_blank" href="' . $primaryPurpose['link_href'] . '">' .
					  ( isset( $primaryPurpose['link_label'] ) ? $primaryPurpose['link_label'] : $primaryPurpose['link_href'] ) .
					  '</a>' . ' '
					: '' );
			$purposeId = "mws_purpose_{$purpose['id']}";
			$code        .= '<div class="mws_purpose mws_purpose_primary">' .
			                '<label for="' . $purposeId . '">' . $purposeText . '</label>' .
                            '<input type="hidden" name="purposes[' . $primaryPurpose['id'] . '][text]" value="' . htmlentities( $purposeText ) . '" />' .
                            '<input type="hidden" name="purposes[' . $primaryPurpose['id'] . '][isPrimary]" value="1" />' .
                            '<input type="hidden" name="purposes[' . $primaryPurpose['id'] . '][checked]" value="true" />' .
			                '</div>';
		} else {
			$code .= '<div class="cms_info_box">' . __( 'Nastavení eshopu postrádá zásady zpracování osobních údajů.', 'mwshop' ) . '</div>';
		}

		foreach ( $purposes as $purpose ) {
			if ( isset( $purpose['is_primary'] ) && $purpose['is_primary'] ) {
				continue;
			}

			$purposeText = ( isset( $purpose['checkbox_label'] ) ? $purpose['checkbox_label'] . ' ' : '' ) .
			               ( isset( $purpose['link_href'] )
				               ? '<a target="_blank" href="' . $purpose['link_href'] . '">' .
				                 ( isset( $purpose['link_label'] ) ? $purpose['link_label'] : $purpose['link_href'] ) .
				                 '</a>' . ' '
				               : ''
			               );
			$purposeId = "mws_purpose_{$purpose['id']}";
			$code .=
				'<div class="mws_purpose">' .
				'<input type="hidden" name="purposes[' . $purpose['id'] . '][text]" value="' . htmlentities( $purposeText ) . '"/>' .
                '<input type="hidden" name="purposes[' . $purpose['id'] . '][isPrimary]" value="false" />' .
			    '<input type="checkbox" name="purposes[' . $purpose['id'] . '][checked]" id="' . $purposeId . '">' .
				'<label for="' . $purposeId . '">'. $purposeText . '</label>' .
				'</input>' .
				'</div>';
		}

		return $code;
    }

	function hookBreadcrumbs($crumbs) {
		if ($this->isShop()) {
			$new_crumbs = array();
			foreach ($crumbs as $crumb) {
				if ($crumb['type'] == 'home') {
					$new_crumbs[] = $crumb;
					if (get_permalink($this->getHomePage()) != get_home_url()) {
						$eshop_home = get_post($this->getHomePage());
						$new_crumbs[] = array(
							'href' => get_permalink($eshop_home->ID),
							'title' => $eshop_home->post_title,
							'type' => 'eshop_home'
						);
					}
				} else if ($crumb['type'] != 'post_type_' . MWS_PRODUCT_SLUG) {
					$new_crumbs[] = $crumb;
				}
			}
		} else $new_crumbs = $crumbs;

		return $new_crumbs;
	}


	// body class

	function writeProducts($posts, $cols, $style, $row_class = 'mw_list_row', $el_style = array()) {
		$content = '';

		$thumb = $cols;
		if ($style == '2') {
			$cols = 1;
			$thumb = 2;
		}
		$rows = array_chunk($posts, $cols);

		foreach ($rows as $row) {
			$content .= '<div class="' . $row_class . '">';
			/** @var MwsProduct $product */
			foreach ($row as $post) {
				$product = MwsProduct::createNew($post);
				// For templates rendering
				MWS()->current()->product = $product;

				$excerpt_length = MWS()->visual_setting['excerpt_length'];
				if (isset($el_style['excerpt_length']) && $el_style['excerpt_length']) $excerpt_length = $el_style['excerpt_length'];
				$availability = $product->getAvailabilityStatus(1);

				$content .= '<div class="mws_product mws_product_id-' . $product->id . ' col col-' . $cols
					. ' ' . $product->getAvailabilityCSS($availability) . '">
              
                  <a href="' . $product->detailUrl . '" class="mws_product_thumb">
                  ' . $product->getThumbnail(MWS()->thumb_name . $thumb) . '
                  <div class="mws_product_sale">' . $product->htmlPriceSaleFull(null, 1, array('vatExcluded', 'vatIncluded', 'salePrice')) . '</div>
                  </a>
                  <div class="mws_product_body">
                      <a href="' . $product->detailUrl . '" class="mws_product_title title_element_container">' . $product->name . '</a>
                      ' . (($product->post->post_excerpt) ? '<div class="mws_product_excerpt">' . wp_trim_words($product->post->post_excerpt, $excerpt_length) . '</div>' : '') .
											'<div class="mws_product_footer">
                          <div class="mws_product_price">' . $product->htmlPriceSaleFull(null, 1, array('vatExcluded')) . '</div>
                          <div class="mws_product_button">' . mwsRenderParts('cart', 'action-add', true) . '</div>
                          <div class="cms_clear"></div>
                      </div>
                      ' . MWS()->getEditProductLink($product->id) . '
                  </div>
                  
              </div>';

				/*$product->htmlAvailabilityMessage($availability)*/
			}
			$content .= '<div class="cms_clear"></div></div>';
		}

		return $content;
	}

	function getEditProductLink($postId = null) {
		$content = '';
		if (is_null($postId)) {
			global $post;
			$postId = $post->ID;
		}
		if (MWS()->inMioweb) {
			//Prefer MioWeb UI if present
			global $blog_module;
			$content = $blog_module->edit_post_bar($postId);
		} else if (MWS()->canEdit()) {
			$content = '<div class="post_edit_bar"><a target="_blank" class="post_edit" title="' .
				__('Editovat produkt', 'mwshop') . '" href="' . get_edit_post_link($postId) . '">' .
				__('Editovat produkt', 'mwshop') . '</a></div>';
		}
		return $content;
	}


	/* Admin edit page
		************************************************************************** */

	function addBodyClass($classes) {
		if ($this->isShop())
			$classes[] = 'eshop_page';
		if ($this->showCart())
			$classes[] = 'eshop_cart_header';

		if ($this->isShop() && (($this->visual_setting['background_color'] && $this->visual_setting['background_color'] !== '#ffffff') || (isset($this->visual_setting['background_image']) && isset($this->visual_setting['background_image']['image']) && $this->visual_setting['background_image']['image'])) || (isset($this->visual_setting['background_image']) && isset($this->visual_setting['background_image']['pattern']) && $this->visual_setting['background_image']['pattern']))
			$classes[] = 'eshop_page_wbg';
		return $classes;
	}

	/* Create eshop
		************************************************************************** */

	function getShopCategories($class = '', $all = 1, $categories = null) {
		if (!$categories) $categories = get_categories(array('taxonomy' => MWS_PRODUCT_CAT_SLUG, 'hide_empty' => 0, 'parent' => 0));

		$cur_cat = get_queried_object();

		$cur = (isset($cur_cat->term_id)) ? false : true;

		$content = '<ul class="mws_category_menu_list">';
		if ($all) $content .= '<li><a class="mws_category_item ' . $class . ' ' . ($cur ? 'mws_category_item_current' : '') . '" title="' . __('Vše', 'mwshop') . '" href="' . get_permalink($this->getHomePage()) . '">' . __('Vše', 'mwshop') . '</a></li>';
		foreach ($categories as $cat) {
			$cur = (isset($cur_cat->term_id) && $cur_cat->term_id == $cat->term_id) ? true : false;
			$content .= '<li><a class="mws_category_item ' . $class . ' ' . ($cur ? 'mws_category_item_current' : '') . '" title="' . $cat->name . '" href="' . get_term_link(intval($cat->term_id), MWS_PRODUCT_CAT_SLUG) . '">' . $cat->name . '</a></li>';
		}
		$content .= '</ul>';

		$content .= '<div class="mws_category_menu_select_container">';
		$content .= '<div class="mws_top_panel_label">' . __('Kategorie', 'mwshop') . '</div>';
		$content .= '<select class="mws_category_menu_select" onchange="document.location.href=this.value">';
		$content .= '<option value="' . get_permalink($this->getHomePage()) . '">' . __('Vše', 'mwshop') . '</option>';
		foreach ($categories as $cat) {
			$cur = (isset($cur_cat->term_id) && $cur_cat->term_id == $cat->term_id) ? true : false;
			$content .= '<option ' . ($cur ? 'selected="selected"' : '') . '" title="' . $cat->name . '" value="' . get_term_link(intval($cat->term_id), MWS_PRODUCT_CAT_SLUG) . '">' . $cat->name . '</option>';
		}
		$content .= '</select></div>';


		return $content;
	}

	function admin_page_edit() {
		global $post;
		if (get_post_type($post) == MWS_PRODUCT_SLUG) {
			?>
        <style>
            #postdivrich {
                display: none;
            }
        </style>
			<?php
		}
	}

	public function getGetPropertyDefs() {
		global $wp_query;
		$paged = isset($wp_query->query['paged']) ? $wp_query->query['paged'] : 1;

		$args = array('post_type' => MWS_PRODUCT_SLUG, 'paged' => $paged);
		query_posts($args);
	}

	/**
	 * Translate country code into country name.
	 * @param $code
	 * @return string
	 */
	public function getCountryByCode($code) {
		$arr = $this->getSupportedCountries();
		return isset($arr[$code])
			? $arr[$code]
			: $code;
	}

	/**
	 * Get list of supported countries.
	 * @return array Array is indexed by 2-letter country codes, the value is country name.
	 */
	public function getSupportedCountries() {
		$arr = array(
			'CZ' => 'Česká republika',
			'SK' => 'Slovenská republika',
			'AT' => 'Austria',
			'BE' => 'Belgium',
			'BG' => 'Bulgaria',
			'HR' => 'Croatia',
			'CY' => 'Cyprus',
			'DK' => 'Denmark',
			'EE' => 'Estonia',
			'FI' => 'Finland',
			'FR' => 'France',
			'DE' => 'Germany',
			'GR' => 'Greece',
			'HU' => 'Hungary',
			'IE' => 'Ireland',
			'IT' => 'Italy',
			'LV' => 'Latvia',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MT' => 'Malta',
			'NL' => 'Netherlands',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'RO' => 'Romania',
			'SI' => 'Slovenia',
			'ES' => 'Spain',
			'SE' => 'Sweden',
			'GB' => 'United Kingdom',
		);
		return $arr;
	}
}
