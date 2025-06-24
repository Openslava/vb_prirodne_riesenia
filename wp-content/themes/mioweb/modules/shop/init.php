<?php
/**
 * Plugin Name: MioShop
 * Plugin URI: http://mioweb.com/shop
 * Description: E-commerce extension with automatic selling support. Sell with ease.
 * Version: 1.0.0
 * Author: MioWeb team
 * Author URI: http://mioweb.com/shop
 *
 *
 * Text Domain: mwshop
 * Domain Path: /languages/
 *
 * **Requires at least: 4.1
 * **Tested up to: 4.3
 *
 * @package MioShop
 * @category Core
 * @author MioWeb
 * @since 1.0.0
 */

/**
 * global options prefix: "mwshop_"  --> MWS_OPTION
 * textdomain: "mwshop"
 * hooks prefix: "mws_"
 * html css prefix: "mws_" --> MWS_CSS
 * html id prefix: "mws_" --> MWS_ID
 * html name prefix: "mws_" --> MWS_NAME
 */

/* MW Shop entry point. This handles loading of data, registration of taxonomies, arranging plugin tables. */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* ==== Global shop DEFINES ==== */
/** Prefix of global options. */
define('MWS_OPTION', 'mwshop_');
/** Prefix of CSS classes in html elements. */
define('MWS_CSS', 'mws_');
/** Prefix of "name" attributes in html input editors. */
define('MWS_NAME', 'mws_');
/** Prefix of "id" attributes in html elements. */
define('MWS_ID', 'mws_');

/** Slug name for product. */
define('MWS_PRODUCT_SLUG', 'mwproduct');
/** Slug name for product variant. */
define('MWS_VARIANT_SLUG', 'mwvariant');
/** Slug name for order. */
define('MWS_ORDER_SLUG', 'mworder');
/** Slug name for order. */
define('MWS_ORDER_STATUS_SLUG', 'mworder_status');
/** Slug name for category of product. */
define('MWS_PRODUCT_CAT_SLUG', 'eshop_category');
/** Slug name for shipping methods. */
define('MWS_SHIPPING_SLUG', 'mwshipping');
/** Slug name for product parametr. */
define('MWS_PROPERTY_SLUG', 'mwsproperty');

/** Name for shop page. It lists products. */
define('MWS_PAGE_SHOP', 'shop');
/** Name for cart page. Used for manipulations with products within cart. */
define('MWS_PAGE_CART', 'shop');
/** Name for order page. Used to set billing and shipping details. Possible additional checkings */
define('MWS_PAGE_ORDER', 'shop');
/** Name for payment page. User fires up the payment here. It receives callbacks from payment gateways. */
define('MWS_PAGE_PAYMENT', 'shop');

/** Root path of the MioShop library. No ending slash. */
define('MWS_PATH_BASE', __DIR__);
/** URL of the MioShop library. No ending slash. */
define('MWS_URL_BASE', get_bloginfo('template_url').'/modules/shop');
/** Directory with rendering templates for single product, product archive etc. No ending slash. */
define('MWS_PATH_TEMPLATE', __DIR__.'/templates');
/** Directory with classes, routines. */
define('MWS_PATH_INCLUDE', __DIR__.'/includes');
/** Directory with classes, routines. */
define('MWS_PATH_LIBS', __DIR__.'/libs');

define('MWS_OPTION_SYNC_KEY', MWS_OPTION.'sync');
define('MWS_OPTION_VATS_KEY', MWS_OPTION.'vats');
define('MWS_OPTION_PERMALINKS', MWS_OPTION.'permalinks');
define('MWS_OPTION_SHOP', 'eshop_option');
define('MWS_OPTION_SHOP_SETTING', 'mw_eshop_setting'); //option page for global shop settings
define('MWS_OPTION_SHOP_PAYMENT_METHODS', 'mw_eshop_payment_methods'); //option page for payment methods settings

/** Field name and meta key name for stock values of the product. */
define('MWS_OPTION_STOCKCOUNT', MWS_OPTION.'stock_count');

/** Name of the meta key of product property settings. */
define('MWS_PROPERTY_META_KEY', 'mws_product_properties_set');
/** Name of the meta key of product's gallery. */
define('MWS_PRODUCT_META_KEY_GALLERY', 'product_gallery');
/** Name of the meta key of product's page codes. */
define('MWS_PRODUCT_META_KEY_PAGECODES', 'page_codes');
/** Name of the meta key of product. */
define('MWS_PRODUCT_META_KEY_STRUCTURE', 'product_structure');
/** Name of the meta key of product variant list - array defining variants. */
define('MWS_PRODUCT_META_KEY_VARIANTLIST', 'variant_list');

define('MWS_FIELDSET_ORDER_CONTACT', 'shop_set_order_contact');

/** Default number of products per page, if not defined by shop settings. */
define('MWS_DEFAULT_PER_PAGE', 10);

/** URL part for products, used in rewrite rules. */
define('MWS_PERMALINK_PRODUCT_DEFAULT',
  _x('produkty', 'Část URL permalinků pro produkty. Lokalizace musí být URL friendly.', 'mwshop'));
/** URL part for product categories when not nesting bellow products, used in rewrite rules. */
define('MWS_PERMALINK_PRODUCT_CAT_DEFAULT',
_x('kategorie-produktu', 'Část URL permalinků pro kategorie produktů. Lokalizace musí být URL friendly.', 'mwshop'));
/** URL part for product categories when nesting bellow products, used in rewrite rules. */
define('MWS_PERMALINK_PRODUCT_CAT_NESTED_DEFAULT',
  _x('kategorie', 'Část URL permalinků pro kategorie produktů, pokud jsou kategorie ' .
    'produktů zanořeny pod produkty. Lokalizace musí být URL friendly.', 'mwshop'));

/**
 * Name of the target browser window, that is popuped from the administrations functions. Using it as A.TARGET means
 * that all administrated outer pages are opened within the same browser window.
 */
const MW_HREF_TARGET_SHARED_ADMIN_EDIT = 'mw_shared_admin_edit';




// Load definition of the shop - the [MioShop] class.
require_once(__DIR__.'/shop_class.php');
// Load eshop elements definition
require_once(__DIR__.'/elements.php');
require_once(__DIR__.'/elements-print.php');
// Startup the main instance. Save it into global context.
$GLOBALS['mioshop'] = MWS();

// language
$cms->load_theme_lang('mwshop', get_template_directory() . '/modules/shop/languages');

/**
 * MioShop debugging routine. Pass a string or mixed item to ouput it into Wordpress's default error log
 * (typically wp-content/debug.log).
 * @param $x mixed Mixed item to be print out into log.
 * @param string $name Optional name of the $x. If present then it will be prepended in front of the output text as
 *                     "$name=<output>".
 */
function mwdbg($x, $name='') {
	if(defined('WP_DEBUG') || WP_DEBUG==true) {
		if (!is_array($x) && !is_object($x))
			error_log(($name ? $name . '=' : '') . $x);
		else
			error_log(($name ? $name . '=' : '') . print_r($x, true));
	}
	mwshoplog('DEPRACATED mwdbg() call. '. ($name ? $name.'=' : '')
		. ((!is_array($x) && !is_object($x)) ? $x : print_r($x, true))
		, MWLL_WARNING, 'deprecated');
}

/** Log a shop message, optionally prefixed by shop part/module/... */
function mwshoplog($message, $level = MWLL_INFO, $ctg='') {
	mwlog(MWLS_SHOP, $message, $level, $ctg);
}

if (MWS()->inMioweb) {
  global $vePage;
  /** @var $cms Cms */
  global $cms;

  define('SHOP_VERSION',MioShop::version);
  $cms->add_version('shop',SHOP_VERSION);

//	define('SHOP_DIR',get_bloginfo('template_url').'/modules/shop/');
//	define('SHOP_DEFAULT_DIR',str_replace ( home_url() , '' , get_bloginfo('template_url') ).'/modules/shop/');

  require_once(__DIR__.'/functions.php');
//	require_once(__DIR__.'/elements-print.php');
//	require_once(__DIR__.'/elements.php');



  // Top panel menu
  //***********************************************************************************
  if(MWS()->edit_mode) {
    $page_id=MWS()->getHomePage();
    $url=($page_id)? get_permalink($page_id):'#';
    $vePage->add_top_panel_menu(11,array('id'=>'eshop','title'=>'Eshop', 'url'=>$url, 'submenu'=>MWS()->create_eshop_menu()));
  }

  $vePage->add_editable_type(MWS_PRODUCT_CAT_SLUG);
  $vePage->add_editable_type(MWS_PRODUCT_SLUG);

  // Settings metaboxes
  //***********************************************************************************
  $cms->define_set(array(
    'id' => 'shop_set_shipping',
    'title' => __('Nastavení','cms'),
    'context' => 'normal',
    'priority' => 'high',
    'include'=>array(MWS_SHIPPING_SLUG,),
  ));
  
  $cms->define_set(array(
    'id' => 'shop_set_order_state',
    'title' => __('Nastavení','cms'),
    'context' => 'normal',
    'priority' => 'high',
    'include'=>array(MWS_ORDER_STATUS_SLUG,),
  ));

  // Shipping setting
  //***********************************************************************************

  $cms->add_set(array(
    'id' => 'shipping',
    'include' => array(MWS_SHIPPING_SLUG),
    'title' => __('Nastavení doručování','mwshop'),
    'fields' => array(
			array(
				'id' => 'basic_setting',
				'type' => 'toggle_group',
				'open' => true,
				'title' => __('Základní nastavení', 'mwshop'),
				'setting' => array(
					array(
						'name' => __('Cena (včetně DPH)','mwshop'),
						'id' => 'price',
						'type' => 'number',
						'step' => MwsCurrency::getHtmlInputStepAttribute(),
						'unit' => MWS()->getCurrency(),
					),
					array(
						'name' => __('DPH','mwshop'),
						'id' => 'vat_id',
						'type' => 'vat_select',
					),
					array(
						'name' => __('Popis','mwshop'),
						'id' => 'post_excerpt',
						'type' => 'textarea',
						'save' => 'post',
					),
					/*            array(
					//                'name' => __('DPH','mwshop'),
													'label' => __('Cena obsahuje DPH','mwshop'),
													'id' => 'vat_included',
													'type' => 'hidden',
					//                'type' => 'checkbox',
													'show' => ''
											),*/
		//      array(
		//        'name' => __('Grafický symbol','mwshop'),
		//        'type' => 'image',
		//        'id' => 'img',
		//      ),
				),
			),
			array(
				'id' => 'basic_setting',
				'type' => 'toggle_group',
//				'open' => true,
				'title' => __('Další nastavení', 'mwshop'),
				'setting' => array(
					// Payment binding
					array(
						'label' => __('Osobní vyzvednutí','mwshop'),
						'id' => 'personal_pickup',
						'type' => 'checkbox',
						'show' => '',
						'desc' => __('Zákazník se dostaví a zboží si přebere sám. Lze využít pro vytvoření fyzického výdejního místa (kamenná prodejna).', 'mioshop'),
					),
					array(
						'label' => __('Umožňuje zaplatit při převzetí','mwshop'),
						'id' => 'cod_enabled',
						'type' => 'checkbox',
						'show' => 'cod_detail',
						'desc' => __('Platbu lze provést až při převzetí zboží. Při využití platby při převzetí bude cena dopravy navýšena o nastavitelnou výši příplatku.', 'mwshop'),
					),
		/*      array(
						'name' => __('Podporované způsoby plateb', 'mwshop'),
						'id' => 'cod_methods',
						//TODO Use real payment method generated out of some system collection
						'type' => 'multiple_checkbox',
						'options' => array(
							array('name'=>_('Kartou'), 'value'=>'card'),
							array('name'=>_('Hotově'), 'value'=>'cash'),
						),
						'class'=>'cms_show_group_shipping_'.'cod_detail',
					),*/
					array(
						'name' => __('Příplatek za dobírku (včetně DPH)','mwshop'),
						'id' => 'cod_price',
						'type' => 'number',
						'step' => MwsCurrency::getHtmlInputStepAttribute(),
						'unit'=>MWS()->getCurrency(),
						'class'=>'cms_show_group_shipping_'.'cod_detail',
						'placeholder' => '0',
					),
				),
			),
			array(
				'id' => 'codes_setting',
				'type' => 'toggle_group',
				'title' => __('Kódy produktu (evidenční, účetní, skladový)', 'mwshop'),
				'setting' => array(
					array(
						'id' => 'codes',
						'name' => '',
						'type' => 'product_codes',
					),
				),
			),
		)
  ),'shop_set_shipping');
  
  $cms->add_set(array(
    'id' => 'order_state_setting',
    'include' => array(MWS_ORDER_STATUS_SLUG),
    'title' => __('Nastavení stavu objednávky','mwshop'),
    'fields' => array(
        array(
          'name' => __('Barevné označení stavu','mwshop'),
          'id' => 'color',
          'type' => 'color',
        ),
        array(
          'name' => __('Po změně na tento stav uložit klienta do seznamu','mwshop'),
          'id' => 'save_to_list',
          'type' => 'list_select',
        ),
    )
  ),'shop_set_order_state');
  
  
$cms->add_set(array(
    'id' => MWS_PRODUCT_META_KEY_PAGECODES,
    'include' => array(MWS_PRODUCT_SLUG),
    'title' => __('Vlastní kódy','cms'),
    'fields' => array(  
        array(
        'name' => __('Konverzní kód produktu','mwshop'),
        'id' => 'product_conversion',
        'type' => 'textarea',
        'desc' => __('V případě že objednávka bude obsahovat tento produkt, tak se zadaný konverzní kód vypíše na děkovací stránce objednávky.','mwshop')
        ),
        array(
        'name' => __('Kódy v hlavičce','cms'),
        'id' => 'codes_header',
        'type' => 'textarea',
        'desc' => __('Zde můžete vložit kódy, které je potřeba umístit před tag <code>&lt;/head&gt;</code> a které chcete, aby byly platné pouze pro tuto stránku.','cms')
        ),
        array(
        'name' => __('Kódy v patičce','cms'),
        'id' => 'codes_footer',
        'type' => 'textarea',
        'desc' => __('Zde můžete vložit kódy, které je potřeba umístit před tag <code>&lt;/body&gt;</code> a které chcete, aby byly platné pouze pro tuto stránku.','cms')
        ),
        array(
        'name' => __('Vlastní CSS styly','cms'),
        'id' => 'codes_css',
        'type' => 'textarea',
        'desc' => __('Zde můžete vložit vlastní CSS styly, které budou platit pouze pro tuto stránku.','cms')
        ),
    )
),"page_set");

  // Order contact
  //***********************************************************************************

  $cms->add_set(array(
    'id' => 'order_contact',
    'include' => array(), //never include this set automatically
    'title' => __('Osobní údaje','mwshop'),
    'fields' => array(
      array(
        'name' => __('Email','mwshop'),
        'id' => 'email',
        'type' => 'text',
      ),
      array(
        'name' => __('Fakturační údaje','mwshop'),
        'id' => 'address',
        'type' => 'order_address',
        'class'=>'cms_show_group_order_contact_'.'primary_address',
      ),
      array(
        'label' => __('Zboží nakupuji na firmu','mwshop'),
        'id' => 'is_company',
        'type' => 'checkbox',
        'show' => 'company_detail'
      ),
      array( // optional
        'name' => __('Informace o firmě','mwshop'),
        'id' => 'company_info',
        'type' => 'company_info',
        'class'=>'cms_show_group_order_contact_'.'company_detail',
      ),
      array(
//        'name' => __('Zboží doručit na jinou adresu','mwshop'),
        'label' => __('Zboží doručit na jinou adresu','mwshop'),
        'id' => 'has_shipping_addr',
        'type' => 'checkbox',
        'show' => 'shipping_detail'
      ),
      array( // optional
        'name' => __('Doručovací adresa','mwshop'),
        'id' => 'shipping_address',
        'type' => 'order_address',
        'class'=>'cms_show_group_order_contact_'.'shipping_detail',
      ),
      array(
        'name' => __('Poznámka','mwshop'),
        'id' => 'note',
        'type' => 'textarea',
      ),
    )
  ), MWS_FIELDSET_ORDER_CONTACT);

// Product setting
//***********************************************************************************

  $cms->add_set(array(
    'id' => 'product',
    'include' => array('mwproduct'),
    'title' => __('Nastavení produktu','mwshop'),
    'fields' => array(
      array(
				'id' => 'product_basic_setting',
				'type' => 'toggle_group',
        'open' => 'true',
				'title' => __('Základní nastavení','mwshop'),
				'setting' => array(
            array(
              'name' => __('Krátký popis','mwshop'),
              'id' => 'post_excerpt',
              'type' => 'textarea',
              'save' => 'post',
            ),
            array(
              'id' => 'type',
              'type' => 'select',
              'name' => __('Typ produktu', 'mwshop'),
              'options' => array(
                array('value'=>MwsProductType::Physical,
                  'name' => __('Fyzický (vyžaduje klasické metody doručení - pošta, DPD, PPL...)','mwshop')),
                array('value'=>MwsProductType::Electronic,
                  'name' => __('Elektronický/virtuální (zboží nevyžaduje fyzické doručení)','mwshop')),
                /*array('value'=>MwsProductType::Other,
                  'name' => __('Ostatní (nevyžaduje klasické doručení)','mwshop')), */
              )
            ),
      			array(
              'id' => MWS_PRODUCT_META_KEY_STRUCTURE,
              'type' => 'select',
              'name' => __('Varianty produktu', 'mwshop'),
              'options' => array(
                array('value'=>MwsProductStructureType::Single,
                  'name' => __('Bez variant','mwshop')),
                array('value'=>MwsProductStructureType::Variants,
                  'name' => __('Produkt má více variant','mwshop')),
              ),
      				'content' => MwsProductStructureType::Single, //default value
							'html_after' => '
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var elem = $("#product_product_structure");
		if(elem.length) {
			elem.trigger("change");
		}	    
	});
</script>',
      				'show' => 'structure_type',
      				'save' => 'post_meta',
      			),
      
      			//********* SINGLE PRODUCT SETTINGS
      			// Price
      			array(
              'name' => (MWS()->getVATs()->isUsingVatAccounting()
                ? __('Cena (včetně DPH)','mwshop')
                : __('Cena','mwshop')
              ),
              'id' => 'price',
              'type' => 'size',
              'unit'=>MWS()->getCurrency(),
							'desc' => __('Pro zadání desetinné ceny použijte tečku, např "120.50".', 'mwshop'),
      				'show_group' => 'structure_type',
      				'show_val' => MwsProductStructureType::Single,
      			),
            array(
              'name' => __('DPH','mwshop'),
              'id' => 'vat_id',
              'type' => 'vat_select',
              'class' => (MWS()->getVATs()->isUsingVatAccounting() ? '' : 'cms_nodisp'),
      				'show_group' => 'structure_type',
      				'show_val' => (MWS()->getVATs()->isUsingVatAccounting()
      					? MwsProductStructureType::Single.','.MwsProductStructureType::Variants
      					: ''),
      			),
				)
      ),

			array(
				'id' => 'codes_setting',
				'type' => 'toggle_group',
				'show_group' => 'structure_type',
				'show_val' => MwsProductStructureType::Single,
				'title' => __('Kódy produktu (evidenční, účetní, skladový)', 'mwshop'),
				'setting' => array(
					array(
						'id' => 'codes',
						'name' => '',
						'type' => 'product_codes',
					),
				),
			),

			//*********** VARIANT PRODUCT SETTINGS
			array(
				'id'=>'variant_stock_enabled',
				'type'=>'toggle_group',
				'checkbox'=>true,
				'title'=>__('Sledovat sklad u variant','mwshop'),
				'show_group' => 'structure_type',
				'show_val' => MwsProductStructureType::Variants,
				'setting'=>array(
					array(
						'id'=>'variant_stock_allow_backorders',
						'label'=>__('Nabízet i po vyprodání skladu', 'mwshop'),
						'type'=>'checkbox',
					),
				),
				'show' => 'show_variant_stock',
				'class' => 'mws_variant_stock_toggle', // for javascript
				'html_after' => '
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var elem = $(".mws_variant_stock_toggle .mw_toggle_group_head");
		if(elem.length) {
			variantStockUpdateCSS(elem.first());
		}	    
	});
</script>',
			),
			array(
				'id' => 'variant_enabled',
				'type' => 'toggle_group',
//				'checkbox' => true,
				'open' => true,
				'title' => __('Varianty produktu','mwshop'),
				'class' => 'mws_variants',
				'setting' => array(
          array(
						'id' => MWS_PRODUCT_META_KEY_VARIANTLIST,
						'name' => '',
            'type' => 'variant_list',
						'save' => 'post_meta',
					),
				),
				'show_group' => 'structure_type',
				'show_val' => MwsProductStructureType::Variants,
			),           
                  
			// Sale price
			array(
				'id' => 'price_sale_enabled',
				'type' => 'toggle_group',
				'checkbox' => true,
				'title' => __('Sleva / akční cena'),
				'show_group' => 'structure_type',
				'show_val' => MwsProductStructureType::Single,
				'setting' => array(
					array(
						'name' => (MWS()->getVATs()->isUsingVatAccounting()
							? __('Cena po slevě (včetně DPH)','mwshop')
							: __('Cena po slevě','mwshop')
						),
						'id' => 'price_sale',
						'type' => 'size',
						'unit'=>MWS()->getCurrency(),
						'desc' => __('Zadáním hodnoty "Cena po slevě" zapnete u produktu zobrazování se slevou. ' .
							'Zákazníkům se bude produkt nabízet za cenu po slevě. Výše slevy bude zobrazena procentuelně v nabídce. ' .
							'Pro cenu ZDARMA zadejte nulu.', 'mwshop'),
					),
					array(
						'id' => 'price_sale_type',
						'type' => 'select',
//						'name' => __('Platnost slevy', 'mwshop'),
						'options' => array(
//							array('value' => MwsSellRestriction::None, 'name' => __('prodej povolen')),
							array('value' => MwsSalePriceType::Continuous, 'name' => __('trvalá (sleva je aktivní až do svého vypnutí)')),
							array('value' => MwsSalePriceType::EnabledFrom, 'name' => __('budoucí sleva (sleva je aktivní od určitého okamžiku)')),
							array('value' => MwsSalePriceType::EnabledTill, 'name' => __('končící sleva (sleva je aktivní do určitého okamžiku)')),
							array('value' => MwsSalePriceType::EnabledInterval, 'name' => __('v intervalu (sleva je aktivní ve vymezeném období)')),
						),
						'show' => 'show_price_sell_type'
					),
					array(
						'id' => 'price_sale_enabled_from',
						'type' => 'datetime',
						'name' => __('Slevu aktivovat v termínu', 'mwshop'),
						'show_group' => 'show_price_sell_type',
						'show_val' => MwsSalePriceType::EnabledFrom.','.MwsSalePriceType::EnabledInterval,
					),
					array(
						'id' => 'price_sale_enabled_till',
						'type' => 'datetime',
						'name' => 'Slevu deaktivovat v termínu',
						'show_group' => 'show_price_sell_type',
						'show_val' => MwsSalePriceType::EnabledTill.','.MwsSalePriceType::EnabledInterval,
					),
				)
			),
			// Stock management group
			array(
				'id'=>'stock_enabled',
				'type'=>'toggle_group',
				'checkbox'=>true,
				'title'=>__('Sledovat sklad','mwshop'),
				'show_group' => 'structure_type',
				'show_val' => MwsProductStructureType::Single,
				'setting'=>array(
					array(
						'id' => MWS_OPTION_STOCKCOUNT,
						'save' => 'post_meta',
						'savehook' => function($postId, $field, $fieldValue, &$fieldSaved) {
							$product = MwsProduct::getById($postId);
							if(!$product)
								return;
							// Update stock-enabled settings of product in case it is changing right now.
							$product->stockEnabled = isset($_REQUEST['product']['stock_enabled'])
								? (bool)$_REQUEST['product']['stock_enabled']
								: false;
							if($product->stockEnabled) {
								$fieldSaved = $product->updateStockCount((int)$fieldValue, MwsStockUpdate::Set);
							}
						},
						'name'=>__('Položek skladem', 'mwshop'),
						'type'=>'number',
						'step'=>1,
						'placeholder'=>0,
					),
					array(
						'id'=>'stock_allow_backorders',
						'label'=>__('Nabízet i po vyprodání skladu', 'mwshop'),
						'type'=>'checkbox',
					),
				),
			),
			// Selling restrictions
			array(
				'id' => 'selling_restrict',
				'type' => 'toggle_group',
				'checkbox' => true,
				'title' => __('Omezit prodej'),
//				'show_group' => 'structure_type',
//				'show_val' => MwsProductStructureType::Single,
				'setting' => array(
					array(
//						'name' => __('Omezení prodeje', 'mwhop'),
						'id' => 'selling_restrict_type',
						'type' => 'select',
						'options' => array(
//							array('value' => MwsSellRestriction::None, 'name' => __('prodej povolen')),
							array('value' => MwsSellRestriction::FullDisable, 'name' => __('Produkt nelze koupit')),
							array('value' => MwsSellRestriction::EnabledFrom, 'name' => __('Produkt lze koupit až od určeného data')),
							array('value' => MwsSellRestriction::EnabledTill, 'name' => __('Produkt lze koupit do určitého data')),
							array('value' => MwsSellRestriction::EnabledInterval, 'name' => __('Produkt lze koupit v období od do')),
						),
						'show' => 'show_selling_restrict_type'
					),
					array(
						'id' => 'selling_enabled_from',
						'type' => 'datetime',
						'name' => __('Prodej zahájit v termínu', 'mwshop'),
						'show_group' => 'show_selling_restrict_type',
						'show_val' => MwsSellRestriction::EnabledFrom.','.MwsSellRestriction::EnabledInterval,
					),
					array(
						'id' => 'selling_enabled_till',
						'type' => 'datetime',
						'name' => 'Prodej ukončit v termínu',
						'show_group' => 'show_selling_restrict_type',
						'show_val' => MwsSellRestriction::EnabledTill.','.MwsSellRestriction::EnabledInterval,
					),
				),
			),

			// ************* Product detail settings
      array(
				'id' => 'selling_restrict',
				'type' => 'toggle_group',
				'title' => __('Detail produktu'),
				'setting' => array(
               /*
              array(
                'name' => __('Popis produktu','mwshop'),
                'id' => '',
                'type' => 'visualedit',
                'button_text' => 'Upravit popis',
                'content_type' => MWS_PRODUCT_SLUG,
                'desc' => __('Popis produktu se zobrazuje v záložce "Popis" na stránce produktu a je vytvářet pomocí vizuálního editoru. Popis lze vytvářet a editovat i přímo na stránce produktu.','mwshop'),
              ),  */
              array(
                'name' => __('Dlouhý popis','mwshop'),
                'id' => '',
                'type' => 'info',
                'content' => __('Obsáhlejší popis produktu zobrazovaný v detailu produktu na záložce "Popis" upravíte přímo ' .
        					'na stránce produktu pomocí vizuálního editoru.','mwshop'),
        				'show_group' => 'structure_type',
        				'show_val' => MwsProductStructureType::Single,
        			),
              array(
                  'name' => __('Diskuze','mwshop'),
                  'label' => __('Skrýt diskuzi produktu','mwshop'),
                  'id' => 'hide_comments',
                  'type' => 'checkbox',
              ),
              array(
                  'name' => __('Vlastní detail','mwshop'),
                  'label' => __('Použít jako detail vlastní stránku','mwshop'),
                  'id' => 'custom_detail',
                  'type' => 'checkbox',
                  'show' => 'custom_detail'
              ),
              array(
                  'name' => __('Stránka','mwshop'),
                  'id'=>'detail_page',
                  'type' => 'selectpage',
                  'show_group' => 'custom_detail',
                  'desc' => __('Vybraná stránka se bude zobrazovat jako detail tohoto produktu.','mwshop'),
              ),   
          )
      ),
      			// ************* Product visibility
      array(
				'id' => 'product_visibility',
				'type' => 'toggle_group',
				'title' => __('Viditelnost produktu'),
				'setting' => array(
              array(
                  'name' => '',
                  'label' => __('Skrýt produkt z výpisu produktů','mwshop'),
                  'id' => 'hide_in_listings',
                  'type' => 'checkbox',
              ),
          )
      ),
      // Properties group
      array(
                'id'=>'properties_setting',
                'type'=>'toggle_group',
                'title'=>__('Parametry produktu','mwshop'),
                'setting'=>array( 
                    array(
                      'name' => '',
                      'id'=>'properties',
                      'type' => 'product_properties',
                    ),
                )
      ),
			// Similar products
      array(
        'id'=>'show_similar_products',
        'type'=>'toggle_group',
        'checkbox'=>0,
        'title'=>__('Podobné zboží','mwshop'),
        'setting'=>array( 
            array(
                'id'=>'show_type_similar_products',
                'title'=>'',
                'type' => 'radio',
                'options' => array(
                    'custom' => __('Vytvořit vlastní výběr','mwshop'), 
                    'category' => __('Vypisovat zboží ze stejné kategorie','mwshop'), 
                 ),   
                 'content' => 'custom', 
                 'show' => 'show_similar',
            ), 
            array(
                'id'=>'similar_products',
                'type'=>'multielement',
                'texts'=>array(
                    'add'=>__('Přidat podobný produkt','mwshop'),
                ),
                'setting'=>array(                             
                    array(
                        'id'=>'product_id',
                        'title'=>__('Produkt','mwshop'),
                        'type'=>'product_select',
                    ),
                ),
                'show_group' => 'show_similar',
                'show_val' => 'custom',
            ),
            
        )
      )
    )
  ),"page_set",1);
  
  $cms->add_set(array(
    'id' => MWS_PRODUCT_META_KEY_GALLERY,
    'include' => array(MWS_PRODUCT_SLUG),
    'title' => __('Galerie','mwshop'),
    'fields' => array(
        array(
            'id' => 'gallery_info',
            'content' => __('Jako hlavní obrázek produktu se použije náhledový obrázek, který můžete nastavit v pravém sloupci v nastavení tohoto produktu.','mwshop'),
            'type' => 'info'
        ),
        array(
            'id' => 'gallery',
            'title' => '',
            'type' => 'image_gallery',
            'editable' => false
        ),
    )
  ),"page_set",2);

// Product properties setting
//***********************************************************************************
  
  $cms->define_set(array(
    'id' => 'shop_set_properties',
    'title' => __('Nastavení','cms'),
    'context' => 'normal',
    'priority' => 'high',
    'include'=>array(MWS_PROPERTY_SLUG),
  ));
  
  $cms->add_set(array(
    'id' => MWS_PROPERTY_META_KEY,
    'include' => array(MWS_PROPERTY_SLUG),
    'title' => __('Nastavení','mwshop'),
		'fields' => array(
			array(
				'name' => __('Typ parametru', 'mwshop'),
				'id' => 'type',
				'type' => 'select',
				'options' => array(
          array('value' => MwsPropertyType::Text, 'name' => __('Text (hodnota se zadává jako text)', 'mwshop')),
					array('value' => MwsPropertyType::Enumeration, 'name' => __('Výčet (hodnota se vybírá ze sady hodnot)', 'mwshop')),					
				),
				'content' => MwsPropertyType::Text, // default value
        'show'=>'parameter_type',
			),
			array(
				'name' => __('Seznam hodnot', 'mwshop'),
				'id' => 'values',
				'type' => 'multielement',
				'texts'=>array(
					'add'=>__('Přidat hodnotu','mwshop'),
				),
				'setting' => array(
					array(
						'id'=>'name',
						'title'=>__('Hodnota','mwshop'),
						'type'=>'text',
					),
//					array(
//						'id'=>'slug',
//						'title'=>__('ID','mwshop'),
//						'type'=>'text',
//					),
				),
        'show_group'=>'parameter_type',
        'show_val'=>MwsPropertyType::Enumeration,
			),
			array(
				'name' => __('Jednotka','mwshop'),
				'id'=>'unit',
				'type' => 'text',
				'desc' => __('Jednotka je volitelná. Ve výpisu produktu se zobrazuje za hodnotou vlastnosti.')
			),
			array(
				'name' => __('Popis','mwshop'),
				'id' => 'post_excerpt',
				'type' => 'textarea',
				'save' => 'post',
			),
		)
  ),"shop_set_properties");
  
}

// Eshop setting
//***********************************************************************************

$cms->add_page(array(
    'page_title' => __( 'Eshop', 'mwshop' ),
    'menu_title' => __( 'Eshop', 'mwshop' ),
    'capability' => 'edit_theme_options',
    'menu_slug' => MWS_OPTION_SHOP,
    'icon_url' => '',
    'position' => 21
));

$cms->add_subpage(array(
    'parent_slug' => MWS_OPTION_SHOP,
    'page_title' => __( 'Nastavení eshopu', 'mwshop' ),
    'menu_title' => __( 'Nastavení eshopu', 'mwshop' ),
    'capability' => 'edit_theme_options',
    'menu_slug' => MWS_OPTION_SHOP,
));
$cms->add_subpage(array(
    'parent_slug' => MWS_OPTION_SHOP,
    'page_title' => __( 'Vzhled eshopu', 'mwshop' ),
    'menu_title' => __( 'Vzhled eshopu', 'mwshop' ),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'appearance_eshop_option',
));
$cms->add_page_group(array(
  'id' => MWS_OPTION_SHOP_SETTING,
  'page' => MWS_OPTION_SHOP,
  'name' => __( 'Základní nastavení', 'mwshop'),
));
$cms->add_page_group(array(
    'id' => 'eshop_codes',
    'page' => MWS_OPTION_SHOP,
    'name' => __( 'Vlastní kódy', 'mwshop'),
)); 
$cms->add_page_group(array(
    'id' => 'eshop_appearance',
    'page' => 'appearance_eshop_option',
    'name' => __( 'Vzhled eshopu', 'mwshop'),
));
$cms->add_page_group(array(
    'id' => 'eshop_header',
    'page' => 'appearance_eshop_option',
    'name' => __( 'Hlavička eshopu', 'mwshop'),
));
$cms->add_page_group(array(
    'id' => 'eshop_footer',
    'page' => 'appearance_eshop_option',
    'name' => __( 'Patička eshopu', 'mwshop'),
)); 
$cms->add_page_group(array(
    'id' => 'eshop_popups',
    'page' => MWS_OPTION_SHOP,
    'name' => __('Pop-upy eshopu','mwshop'),
));
$cms->add_page_group(array(
    'id' => 'eshop_comparers',
    'page' => MWS_OPTION_SHOP,
    'name' => __('Srovnávače cen','mwshop'),
));

$cms->add_page_setting(MWS_OPTION_SHOP_PAYMENT_METHODS,array(
//  array(
//    'name' => __('Platební metody', 'mwshop'),
//    'type' => 'title',
//  ),
  array(
    'content' => __('Zaškrtnutím povolte ty platební metody, které může zákazník použít k úhradě objednávky. ' .
      'Pro správnou funkčnost musí být zde povolené platební metody povoleny i v používaném platebním/fakturačním systému.'
      ,'mwshop'),
    'type' => 'info',
    'id' => 'info'
  ),   /*
  array(
    'id'=>'',
    'name' => __('Nastavení pro platební bránu','mwshop'),
    'type' => 'paygate_selected',
//    'label' => __('Používat FAPI','mwshop'),
  ),  */
  array(
    'name' => __('Platební metody','mwshop'),
    'id' => 'payment_methods',
    'type' => 'payment_methods',
  ),
));

$cms->add_page_setting(MWS_OPTION_SHOP_SETTING,array(
    array(
        'id'=>'basic_setting',
        'type'=>'toggle_group',
        'title'=>__('Základní nastavení eshopu','mwshop'),
        'open'=>true,
        'setting'=>array(
            array(
                'id'=>'home_page',
                'name' => __('Úvodní stránka obchodu','mwshop'),
                'type' => 'selectpage',
                'content' => '',
                'desc' => __('Stránka slouží k zobrazení katalogu produktů. Umožňuje uživateli zobrazit vaši nabídku produktů ' .
                    'v přehledném seznamu.', 'mwshop'),
            ),
            array(
                'id'=>'order_page',
                'name' => __('Stránka košíku','mwshop'),
                'type' => 'selectpage',
                'content' => '',
                'desc' => __('Stránka zobrazuje obsahu nákupního košíku. Umožňuje uživateli odebrat položky z košíku ' .
                    'a změnit množství produktů v košíku.', 'mwshop')
            ),
            array(
              'id'=>'terms',
              'name' => __('Stránka s obchodními podmínkami','mwshop'),
              'type' => 'selectpage',
              'desc' => __('Vyberte stránku, která obsahuje vaše obchodní podmínky. ' .
                'Podmínky jsou zobrazeny zákazníkovi před vlastním objednáním. Objednáním vyjadřuje souhlas s obchodními podmínkami. '
                , 'mwshop'),
              'target' => false,
            ),
//            array(
//              'id'=>'personalDataProtection',
//              'name' => __('Stránka se zásadami zpracování osobních údajů','mwshop'),
//              'type' => 'selectpage',
//              'desc' => __('Vyberte stránku, která obsahuje vaše zásady zpracování osobních údajů. ' .
//                'Zásady jsou zobrazeny zákazníkovi před vlastním objednáním. Objednáním dává klient primární souhlas s těmito zásadami ' .
//                           '(tzv. "informační povinnost"). ' .
//                           '<br />' .
//                           'Nastavením fakturačního nástroje je možné vyžádat si další volitelné souhlasy, ' .
//                           'které jsou klientovi nabídnuty k poskytnutí v posledním kroku vytvoření závazné objednávky. ' .
//                           '<br />' .
//                           'Poskytnutý souhlas je evidován ve fakturačním nástroji jako nedílná součást objednávky.'
//                , 'mwshop'),
//              'target' => false,
//            ),
            array(
							'id'=>'currency',
							'name' => __('Měna','mwshop'),
							'type' => 'currency',
							'content' => __('Kč','mwshop'),
							'show'=>'currency',
						),
            array(
							'id'=>'currency_conversion_CZK2EUR',
							'name' => __('Převodní kurz (CZK na EUR)','mwshop'),
							'type' => 'currency_conversion',
//							'desc' => __('Pro zadání desetinné ceny použijte tečku, např "120.50".', 'mwshop'),
							'from' => MwsCurrency::czk,
							'to' => MwsCurrency::eur,
							'step' => 0.005,
							'min' => 0.005,
							'show_group'=>'currency',
							'show_val' => MwsCurrency::czk,
						),
						array(
							'id'=>'currency_conversion_EUR2CZK',
							'name' => __('Převodní kurz (EUR na CZK)','mwshop'),
							'type' => 'currency_conversion',
//							'desc' => __('Pro zadání desetinné ceny použijte tečku, např "120.50".', 'mwshop'),
							'from' => MwsCurrency::eur,
							'to' => MwsCurrency::czk,
							'step' => 0.1,
							'min' => 0.1,
							'show_group'=>'currency',
							'show_val' => MwsCurrency::eur,
						),
            array(
                'id' => 'eshop_hide',
                'title' => __('Nastavení zobrazení', 'cms_ve'),
                'type' => 'multiple_checkbox',
                'options' => array(
                    array('name' => __('Skrýt diskuze u produktů', 'mwshop'), 'value' => 'comments'),
                    array('name' => __('Skrýt podobné produkty', 'mwshop'), 'value' => 'similar_products'),
                    array('name' => __('Skrýt kategorie eshopu', 'mwshop'), 'value' => 'categories'),
                    array('name' => __('Skrýt vyhledávání v eshopu', 'mwshop'), 'value' => 'search'),
                    array('name' => __('Skrýt sociální tlačítka v detailu produktu', 'mwshop'), 'value' => 'social'),
                    array('name' => __('Skrýt dostupnost u produktů', 'mwshop'), 'value' => 'availability'),
                ),
            ),
						array(
							'id' => 'eshop_display_product',
							'title' => __('Viditelnost nedostupných produktů', 'cms_ve'),
							'type' => 'multiple_checkbox',
							'options' => array(
								array('name' => __('Zobrazovat nedostupné produkty v seznamu produktů', 'mwshop'), 'value' => 'unavailable_product'),
								array('name' => __('Zobrazovat nedostupné varianty ve výběru varianty', 'mwshop'), 'value' => 'unavailable_variant'),
							),
							'desc' => __('Týká se těch produktů a jejich variant, které mají "omezení prodeje" nastaveno na ' .
								'"Produkt nelze koupit" anebo byly vyčerpány jejich skladové zásoby bez možnosti nákupu i po vyčerpání skladu.'),
						),
            array(
                'id'=>'product_order',
                'title'=>__('Řadit zboží podle','mwshop'),
                'type'=>'select',
                'content'=> 'date',
                'options' => array(
                    array('name' => __('Data vytvoření', 'mwshop'), 'value' => 'date'),
                    array('name' => __('Názvu', 'mwshop'), 'value' => 'title'),
                    array('name' => __('Vlastního řazení', 'mwshop'), 'value' => 'menu_order'),
                ),
                'desc'=> __('Pořadí pro vlastní řazení se určuje podle hodnoty "Pořadí" v nastavení každého produktu.', 'mwshop')
            ),
        )
    ),

  array(
    'id'=>'vat_setting',
    'type'=>'toggle_group',
    'title'=>__('Nastavení DPH','mwshop'),
    'setting'=>array(
      array(
        'id' => 'vat_accounting',
        'title'=>__('Účtování DPH','mwshop'),
        'type'=>'select',
        'content'=> 'noVat',
        'options' => array(
          array('name' => __('Neplátce DPH', 'mwshop'), 'value' => MwsVatAccounting::noVat),
          array('name' => __('Identifikovaná osoba', 'mwshop'), 'value' => MwsVatAccounting::noVatIdentified),
          array('name' => __('Plátce DPH', 'mwshop'), 'value' => MwsVatAccounting::withVat),
        ),
//            'desc'=> __('', 'mwshop'),
        'show'=>'vat_setting',
      ),
      array(
        'content' => __('Zadejte až 5 sazeb DPH. ' .
          'Nechcete-li některou sazbu DPH používat, vymažte její hodnotu. ' .
          'Změna hodnoty DPH u jednotlivé sazby se projeví u všech produktů, které tuto sazbu používají. ' .
          '"Sazba 1" slouží jako výchozí sazba a bude použita u produktů, které nemají žádnou sazbu přiřazenou. ', 'mwshop'),
        'id' => 'vat_info',
        'name' => '',
        'type' => 'info',
        'show_group'=>'vat_setting',
        'show_val' => 'withVat',
      ),
      array(
        'id'=>'vat_values',
        'name' => '',
        'type' => 'vatvalues',
        'content' => array(21,15,10,0),
        'show_group'=>'vat_setting',
        'show_val' => 'withVat',
      ),
    ),
  ),

  array(
    'id'=>'other_setting',
    'type'=>'toggle_group',
    'title'=>__('Ostatní nastavení','mwshop'),
    'setting'=>array(
        array(
          'id'=>'cart_content',
          'title'=>__('Obsah pod výpisem zboží v košíku', 'cms_ve'),
          'type'=>'weditor',
          'setting'=>array(
              'post_type'=>'weditor',
              'texts'=>array(
                  'empty'=>__( ' - Bez obsahu - ', 'cms_ve' ),
                  'edit'=>__( 'Upravit vybraný obsah', 'cms_ve' ),
                  'duplicate'=>__( 'Duplikovat vybraný obsah', 'cms_ve' ),
                  'create'=>__( 'Vytvořit nový obsah', 'cms_ve' ),
                  'delete'=>__( 'Smazat vybraný obsah', 'cms_ve' ),
              ),
          )
        ),
        array(
          'id'=>'thanks_content',
          'title'=>__('Obsah na děkovací stránce objednávky', 'cms_ve'),
          'type'=>'weditor',
          'setting'=>array(
              'post_type'=>'weditor',
              'texts'=>array(
                  'empty'=>__( ' - Bez obsahu - ', 'cms_ve' ),
                  'edit'=>__( 'Upravit vybraný obsah', 'cms_ve' ),
                  'duplicate'=>__( 'Duplikovat vybraný obsah', 'cms_ve' ),
                  'create'=>__( 'Vytvořit nový obsah', 'cms_ve' ),
                  'delete'=>__( 'Smazat vybraný obsah', 'cms_ve' ),
              ),
          )
        ),
    ),
  ),

	array(
    'id'=>'paygate_setting',
    'type'=>'toggle_group',
//    'open' => true,
    'title'=>__('Platby a fakturace','mwshop'),
    'setting'=>array(
      array(
        'id'=>'pay_gate',
        'name' => __('Faktury a platby zpracovávat pomocí','mwshop'),
        'type' => 'paygate',
      ),
      /*
  array(
    'id'=>'',
    'name' => __('Nastavení pro platební bránu','mwshop'),
    'type' => 'paygate_selected',
//    'label' => __('Používat FAPI','mwshop'),
  ),  */
//      array(
//        'name' => __('Platební metody','mwshop'),
//        'id' => 'payment_methods',
//        'type' => 'payment_methods',
//        'desc' => __('Zaškrtnutím povolte ty platební metody, které může zákazník použít k úhradě objednávky. ' .
//          'Pro správnou funkčnost mustí být zde povolené platební metody povoleny i v používaném platebním/fakturačním systému.'
//          ,'mwshop'),
//      ),

    )
  ),
));
$cms->add_page_setting('eshop_comparers',array(   
		array(
				'name' => __('Odkazy na XML feedy pro srovnávače cen','cms_blog'),
				'id' => 'eshop_feeds',
				'type' => 'eshop_feeds', 
		),        
));  
$cms->add_page_setting('eshop_popups',array(
                array(
                    'name' => __('Klasický pop-up','cms_blog'),
                    'type' => 'title', 
                ),       
                array(
                    'name' => __('Klasický pop-up','cms_blog'),
                    'id' => 'clasic_popup',
                    'type' => 'popupselect', 
                    'tooltip'=>__('Tento pop-up se zobrazí po načtení stránky nebo při splnění zadané podmínky v pokročilém nastavení.','cms_blog'), 
                ),        
                array(
                    'id'=>'popup_type',
                    'name'=>__('Zobrazit pop-up','cms_blog'),
                    'type' => 'radio',
                    'show'=>'popup_type',
                    'options' => array(
                         'onload' => __('Po načtení stránky','cms_blog'),
                         'advance' => __('Pokročilé nastavení','cms_blog'),
                    ),
                    'content' => 'onload',                                   
                ), 
                array(
                    'name' => __('Zobrazit po x sekundách','cms_blog'),
                    'id' => 'time',
                    'type' => 'text',
                    'desc'=>__('Pop-up se zobrazí po x sekundách od načtení stránky.','cms_blog'), 
                    'show_group' => 'popup_type',
                    'show_val' => 'advance',  
                ),  
                array(
                    'name' => __('Zobrazit po odskrolování','cms_blog'),
                    'id' => 'scroll',
                    'type' => 'size', 
                    'content'=>array(
                      'size'=>'',
                      'unit'=>'px'
                    ),
                    'desc'=>__('Pop-up se zobrazí po odskrolování zadané části stránky (v % nebo v px).','cms_blog'),
                    'show_group' => 'popup_type',
                    'show_val' => 'advance',  
                ),  
                array(
                    'name' => __('Zobrazit po naskrolování na prvek s CSS selektorem','cms_blog'),
                    'id' => 'selector',
                    'type' => 'text',
                    'placeholder' =>__('.class nebo #id','cms_blog'), 
                    'desc'=>__('Pop-up se zobrazí po naskrolování na prvek stránky se zadaným CSS selektorem.','cms_blog'), 
                    'show_group' => 'popup_type',
                    'show_val' => 'advance', 
                ),  
                array(
                    'name' => __('Exit pop-up','cms_blog'),
                    'type' => 'title', 
                ),       
                array(
                    'name' => __('Exit pop-up','cms_blog'),
                    'id' => 'exit_popup',
                    'type' => 'popupselect', 
                    'tooltip'=>__('Tento pop-up se zobrazí v momentě, kdy uživatel vyjede myší do horní části prohlížeče.','cms_blog'), 
                ),  
));  

$cms->add_page_setting('eshop_codes',array(
    array(
        'name' => __('Skripty v hlavičce','cms_blog'),
        'id' => 'head_scripts',
        'type' => 'textarea'
        ),
    array(
        'name' => __('Skripty v patičce','cms_blog'),
        'id' => 'footer_scripts',
        'type' => 'textarea'
        ),
    array(
        'name' => __('Konverzní kód eshopu','mwshop'),
        'id' => 'eshop_conversion',
        'type' => 'textarea',
        'description'=>__('Konverzní kód se vypíše na děkovací stránce eshopu po dokončení objednávky.','cms_blog'), 
        ),
    array(
        'name' => __('Vlastní css styly (platné pro eshop)','cms_blog'),
        'id' => 'css_scripts',
        'type' => 'textarea'
        )
));
$cms->add_page_setting('eshop_appearance',array( 
    array(
        'id'=>'basic_setting',
        'type'=>'toggle_group',
        'title'=>__('Základní nastavení vzhledu','mwshop'),
        'open'=>true,
        'setting'=>array( 
            array(
                'name' => __('Barva','cms_blog'),
                'id' => 'eshop_color',
                'type' => 'color',
                'content'=>'#158ebf',
                'desc' => __('Tato barva se použije pro obarvení základních prvků jako jsou tlačítka.','mwshop'),
            ),
            array(
                'id'=>'product_thumbnail',
                'title'=>__('Zobrazení produktových obrázků','mwshop'),
                'type' => 'radio',
                'options' => array(
                    'mio_columns_' => __('Použít obrázky v poměru 4:3','mwshop'),
                    'mio_columns_c' => __('Zachovávat původní poměry stran obrázků','mwshop'),
                ),
                'content'=>'mio_columns_',
            ),
            array(
                'name' => __('Pozadí eshopu','mwshopg'),
                'type' => 'title',
            ),
            array(
                'name' => __('Barva pozadí','cms_blog'),
                'id' => 'background_color',
                'type' => 'color',
                'content'=>''
            ),
            array(
                'name' => __('Obrázek na pozadí','cms_blog'),
                'id' => 'background_image',
                'type' => 'bgimage',
                'content'=>array(
                      'pattern'=>0,
                      'fixed'=>'fixed'
                    )
            ),  
        )
    ),
    array(
        'id'=>'productlist_setting',
        'type'=>'toggle_group',
        'title'=>__('Zobrazení výpisu produktů','mwshop'),
        'setting'=>array(         
            array(
                'id'=>'product_style',
                'title'=>__('Vzhled výpisu produktů','mwshop'),
                'type'=>'imageselect',
                'content'=>'1',
                'options' => array(
                    '1' => MWS_URL_BASE.'/img/image_select/product1.png',
                    '3' => MWS_URL_BASE.'/img/image_select/product3.png',
                    '2' => MWS_URL_BASE.'/img/image_select/product2.png',
                    '4' => MWS_URL_BASE.'/img/image_select/product4.png',
                ),
                'show' => 'p_style',
            ),
            array(
                'id'=>'cols',
                'title'=>__('Počet sloupců','mwshop'),
                'type'=>'select',
                'content'=>3,
                'options' => array(
                    array('name' => '3', 'value' => 3),
                    array('name' => '4', 'value' => 4),
                    array('name' => '5', 'value' => 5),
                 ),
                 'show_group' => 'p_style',
                 'show_val' => '1,3,4', 
            ), 
            array(
                'name' => __('Počet produktů na stránku','mwshop'),
                'id' => 'per_page',
                'type' => 'text',
                'content'=>'15'
            ), 
            array(
                'name' => __('Maximální počet slov popisku ve výpisu','mwshop'),
                'id' => 'excerpt_length',
                'type' => 'text',
                'content'=>'10'
            ), 
        )
    ),
    array(
        'id'=>'other_setting',
        'type'=>'toggle_group',
        'title'=>__('Ostatní zobrazení','mwshop'),
        'setting'=>array( 
    
            array(
                'id'=>'show_cart_header',
                'title'=>__('Zobrazení košíku v hlavičce','mwshop'),
                'type' => 'multiple_checkbox',
                'options' => array(
                    array('name' => __('Zobrazit košík v hlavičce webu','mwshop'), 'value' => 'show_web'),  
                    array('name' => __('Zobrazit košík v hlavičce blogu','mwshop'), 'value' => 'show_blog'),
                    array('name' => __('Zobrazit košík v hlavičce členských sekcí','mwshop'), 'value' => 'show_member'),                                     
                ),
                'content'=>array('show_web', 'show_blog')
           ),
        )
    )
)); 
$cms->add_page_setting('eshop_footer',array(
    array(
            'name' => __('Použít','cms_blog'),
            'id' => 'show',
            'type' => 'radio',
            'show'=>'footerset',
            'options' => array(
                'global'=>__('Patičku webu','cms_blog'),
                'eshop'=>__('Vlastní patičku','cms_blog'),
            ),
            'content' => 'global',
    ),
    array(
            'id'=>'footer_group',
            'type'=>'group',
            'setting'=>$cms->container['footer_setting'],
            'show_group' => 'footerset',
            'show_val' => 'eshop',
        )

)); 
$cms->add_page_setting('eshop_header',array(
        array(
            'name' => __('Použít','cms_blog'),
            'id' => 'show',
            'type' => 'radio',
            'show'=>'headerset',
            'options' => array(
                'global'=>__('Hlavičku webu','cms_blog'),
                'eshop'=>__('Vlastní hlavičku','cms_blog'),
            ),
            'content' => 'global',            
        ),
        array(
            'id'=>'header_group',
            'type'=>'group',
            'setting'=>$cms->container['header_setting'],
            'show_group' => 'headerset',
            'show_val' => 'eshop', 
        ),
)); 
