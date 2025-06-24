<?php

/**
 * Definition of custom post types, taxonomies and properties.
 *
 * Date: 04.02.16
 * Time: 15:07
 *
 * @since 1.0.0
 */

/** Proceed with registration of post types, taxonomies etc. */
class MwsTypesRegistration {

	/** @var array List of group and field definitions for custom post types. Settings are grouped by custom post type. */
	protected static $groupDefs = null;

	public static function initClass() {
		static::$groupDefs = array(
			MWS_PRODUCT_SLUG => array(),
			MWS_ORDER_SLUG => array(),
		);
	}

	public static function registerHooks() {
		add_filter('init', array(__CLASS__, 'registerAll'));
	}

	public static function registerAll() {
		static::registerPostTypes();
		static::registerTaxonomies();
	}

	/** Register all post types. */
	public static function registerPostTypes() {
		if (post_type_exists(MWS_PRODUCT_SLUG)) {
//			mwdbg(__METHOD__ . ' ...SKIPPED');
			return;
		}
//		mwdbg(__METHOD__ . ' ...DONE');

		$isDebugging = (bool)(defined('MW_SHOW_DEBUGS') && MW_SHOW_DEBUGS);

		// -------------- PRODUCT CATEGORIES --------------

		$labels = array(
			'name'              => _x( 'Kategorie eshopu', 'taxonomy general name', 'mwshop' ),
			'singular_name'     => _x( 'Kategorie eshopu', 'taxonomy singular name', 'mwshop' ),
			'search_items'      => __( 'Hledat kategorie', 'mwshop' ),
			'all_items'         => __( 'Všechny kategorie', 'mwshop' ),
			'parent_item'       => __( 'Nadřazená kategorie', 'mwshop' ),
			'parent_item_colon' => __( 'Nadřazená kategorie:', 'mwshop' ),
			'edit_item'         => __( 'Upravit kategorii', 'mwshop' ),
			'update_item'       => __( 'Uložit kategorii', 'mwshop' ),
			'add_new_item'      => __( 'Přidat kategorii', 'mwshop' ),
			'new_item_name'     => __( 'Jméno nové kategorie', 'mwshop' ),
			'menu_name'         => __( 'Kategorie', 'mwshop' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug'=> MWS()->getPermalink_ProductCat(),
				'with_front' => true,
				'ep_mask' => EP_NONE,
				'pages' => false,
				'feeds' => false,
				'forcomments' => false,
				'walk_dirs' => false,
				'endpoints' => false,
			),
		);

		register_taxonomy( MWS_PRODUCT_CAT_SLUG, array( MWS_PRODUCT_SLUG ), $args );

		// -------------- PRODUCT --------------
		$labels = array(
			'name' => _x('Produkty', 'post type general name for product', 'mwshop'),
			'singular_name' => _x('Produkt', 'post type singular name for product', 'mwshop'),
			'menu_name' => _x('Produkty', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Produkt', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nový', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit nový produkt', 'mwshop'),
			'new_item' => __('Nový produkt', 'mwshop'),
			'edit_item' => __('Upravit produkt', 'mwshop'),
			'view_item' => __('Zobrazit produkt', 'mwshop'),
			'all_items' => __('Všechny produkty', 'mwshop'),
			'search_items' => __('Vyhledat produkty', 'mwshop'),
			'parent_item_colon' => __('Nadřazené zboží:', 'mwshop'),
			'not_found' => __('Žádné produkty nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné produkty nenalezeny v koši.', 'mwshop')
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Produkt MioWeb obchodu.', 'mwshop'),
      'taxonomies' => array(MWS_PRODUCT_CAT_SLUG),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'query_var' => true,
			'rewrite' => array(
					'slug'=> MWS()->getPermalink_Products(),
					'with_front' => true,
					'ep_mask' => EP_NONE,
					'pages' => false,
					'feeds' => false,
					'forcomments' => false,
					'walk_dirs' => false,
					'endpoints' => false,
				),
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 20,
			'supports' => array('title', 'editor',
				'thumbnail',  'comments', 'page-attributes')
		);

		register_post_type(MWS_PRODUCT_SLUG, $args);
    
		// -------------- PRODUCT VARIANT --------------
		$labels = array(
			'name' => _x('Varianta produktu', 'post type general name for variant of product', 'mwshop'),
			'singular_name' => _x('Varianta produktu', 'post type singular name for variant of product', 'mwshop'),
			'menu_name' => _x('Varianty produktů', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Varianta produktu', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit novou', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit novou variantu produktu', 'mwshop'),
			'new_item' => __('Nová varianta produktu', 'mwshop'),
			'edit_item' => __('Upravit variantu produktu', 'mwshop'),
			'view_item' => __('Zobrazit variantu produktu', 'mwshop'),
			'all_items' => __('Všechny varianty produktů', 'mwshop'),
			'search_items' => __('Vyhledat variantu produktu', 'mwshop'),
			'parent_item_colon' => __('Nadřazený produkt:', 'mwshop'),
			'not_found' => __('Žádné varianty produktů nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné varianty produktů nenalezeny v koši.', 'mwshop')
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Varianta produktu MioWeb obchodu.', 'mwshop'),
      'taxonomies' => array(),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => $isDebugging,
			'show_in_menu' => $isDebugging,
			'query_var' => false,
			'rewrite' => false
/*				array(
					'slug'=> MWS()->getPermalink_Products(),
					'with_front' => true,
					'ep_mask' => EP_NONE,
					'pages' => false,
					'feeds' => false,
					'forcomments' => false,
					'walk_dirs' => false,
					'endpoints' => false,
				)*/,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 21,
			'supports' => array('title',
				'thumbnail',  'comments', 'page-attributes')
		);

		register_post_type(MWS_VARIANT_SLUG, $args);

    // -------------- PRODUCT PROPERTIES --------------
		$labels = array(
			'name' => _x('Parametry produktu', 'post type general name for product', 'mwshop'),
			'singular_name' => _x('Parametr produktu', 'post type singular name for product', 'mwshop'),
			'menu_name' => _x('Parametry produktu', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Parametr produktu', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nový', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit nový parametr', 'mwshop'),
			'new_item' => __('Nový parametr produktu', 'mwshop'),
			'edit_item' => __('Upravit parametr', 'mwshop'),
			'view_item' => __('Zobrazit parametr', 'mwshop'),
			'all_items' => __('Parametry produktu', 'mwshop'),
			'search_items' => __('Vyhledat parametry', 'mwshop'),
			'not_found' => __('Žádné parametry nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné parametry nenalezeny v koši.', 'mwshop')
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Parametry pro produkty MioWeb eshopu.', 'mwshop'),
      'taxonomies' => array(),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
      'show_in_menu' => 'edit.php?post_type='.MWS_PRODUCT_SLUG,
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 40,
			'supports' => array('title')
		);

		register_post_type(MWS_PROPERTY_SLUG, $args);

		// -------------- ORDER --------------
		$labels = array(
			'name' => _x('Objednávky', 'post type general name for product', 'mwshop'),
			'singular_name' => _x('Objednávka', 'post type singular name for product', 'mwshop'),
			'menu_name' => _x('Objednávky', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Objednávka', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit objednávku', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit novou objednávku', 'mwshop'),
			'new_item' => __('Nová objednávka', 'mwshop'),
			'edit_item' => __('Upravit objednávku', 'mwshop'),
			'view_item' => __('Zobrazit objednávku', 'mwshop'),
			'all_items' => __('Objednávky', 'mwshop'),
			'search_items' => __('Vyhledat objednávku', 'mwshop'),
			'parent_item_colon' => __('Nadřazená objednávka:', 'mwshop'),
			'not_found' => __('Žádné objednávky nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné objednávky nenalezeny v koši.', 'mwshop')
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Objednávka MioWeb obchodu.', 'mwshop'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
      'show_in_menu' => 'eshop_option',
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 1,
			'supports' => array('title', /*'editor',*/
				/*'thumbnail', 'excerpt', 'comments'*/)
		);

		register_post_type(MWS_ORDER_SLUG, $args);
    
    // -------------- ORDER STATUS --------------
		$labels = array(
			'name' => _x('Stavy objednávky', 'post type general name for order state', 'mwshop'),
			'singular_name' => _x('Stav objednávky', 'post type singular name for product', 'mwshop'),
			'menu_name' => _x('Stavy objednávky', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Stav objednávky', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit stav', 'create new product', 'mwshop'),
			'add_new_item' => __('Vytvořit nový stav objednávky', 'mwshop'),
			'new_item' => __('Nový stav objednávky', 'mwshop'),
			'edit_item' => __('Upravit stav objednávky', 'mwshop'),
			'view_item' => __('Zobrazit stav objednávky', 'mwshop'),
			'all_items' => __('Stavy objednávky', 'mwshop'),
			'search_items' => __('Vyhledat stav objednávky', 'mwshop'),
			'not_found' => __('Žádné stavy objednávky nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné stavy objednávky nenalezeny v koši.', 'mwshop')
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Stavy objednávky MioWeb e-shopu', 'mwshop'),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
      'show_in_menu' => 'eshop_option',
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => 2,
			'supports' => array('title', /*'editor',*/
				/*'thumbnail', 'excerpt', 'comments'*/)
		);

		//register_post_type(MWS_ORDER_STATUS_SLUG, $args);

		// -------------- SHIPPING --------------
		$labels = array(
			'name' => _x('Doručování', 'post type general name for shipping', 'mwshop'),
			'singular_name' => _x('Doručování', 'post type singular name for shipping', 'mwshop'),
			'menu_name' => _x('Doručování', 'admin menu', 'mwshop'),
			'name_admin_bar' => _x('Doručování', 'add new on admin bar', 'mwshop'),
			'add_new' => _x('Vytvořit nové', 'create new shipping', 'mwshop'),
			'add_new_item' => __('Vytvořit nový způsob doručení', 'mwshop'),
			'new_item' => __('Nové doručování', 'mwshop'),
			'edit_item' => __('Upravit doručování', 'mwshop'),
			'view_item' => __('Zobrazit doručování', 'mwshop'),
			'all_items' => __('Způsoby doručení', 'mwshop'),
			'search_items' => __('Vyhledat doručování', 'mwshop'),
			'parent_item_colon' => __('Nadřazené doručování:', 'mwshop'),
			'not_found' => __('Žádné způsoby doručení nenalezeny.', 'mwshop'),
			'not_found_in_trash' => __('Žádné způsoby doručení nenalezeny v koši.', 'mwshop')
		);

		$args = array(
			'labels' => $labels,
			'description' => __('Způsob doručování MioWeb obchodu.', 'mwshop'),
			'taxonomies' => array(MWS_SHIPPING_SLUG),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => 'eshop_option',
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => 2,
			'supports' => array('title'),
		);

		register_post_type(MWS_SHIPPING_SLUG, $args);

		//Use permalink rules defined in our custom rewrite.
		// Previous "rewrite" definition for the custom types is necessary. That adds rewrite tags for the post types and some other stuff. ;)
//		MwsRewrite::removePermalinks();
//		MwsRewrite::registerPermalinks();

		// -------------- admin columns for listings --------------

		if (is_admin()) {
			if(MWS()->inMioweb) {
				//--------------- MIOWEB COLUMNS
				// Admin columns
				add_filter('manage_' . MWS_PRODUCT_SLUG . '_posts_columns', array(__CLASS__, 'mwAdminColumnHeaders'), 10);
				add_action('manage_' . MWS_PRODUCT_SLUG . '_posts_custom_column', array(__CLASS__, 'mwAdminColumnValues'), 10, 2);
				add_filter('manage_' . MWS_SHIPPING_SLUG . '_posts_columns', array(__CLASS__, 'mwAdminColumnHeaders'), 10);
				add_action('manage_' . MWS_SHIPPING_SLUG . '_posts_custom_column', array(__CLASS__, 'mwAdminColumnValues'), 10, 2);
				add_filter('manage_' . MWS_ORDER_SLUG . '_posts_columns', array(__CLASS__, 'mwAdminColumnHeaders'), 10);
				add_action('manage_' . MWS_ORDER_SLUG . '_posts_custom_column', array(__CLASS__, 'mwAdminColumnValues'), 10, 2);

			} else {
/*				//--------------- nonMIOWEB COLUMNS
				// Metaboxes, custom-fields
				add_action('add_meta_boxes', array('MwsMetaboxes', 'hookGenerator'));
				add_action('save_post', array('MwsMetaboxes', 'savePost'));
				// Admin columns
				add_filter('manage_' . MWS_PRODUCT_SLUG . '_posts_columns', array(__CLASS__, 'adminColumnHeaders'), 10);
				add_action('manage_' . MWS_PRODUCT_SLUG . '_posts_custom_column', array(__CLASS__, 'adminColumnValues'), 10, 2);*/
			}
		}

		// Customize post messages
		add_filter('bulk_post_updated_messages', array(__CLASS__, 'mwBulkPostUpdatedMessages'), 10, 2);
		add_filter('post_updated_messages', array(__CLASS__, 'mwPostUpdatedMessages'), 10, 1);
	}

	/** Register all taxonomies. Include custom taxonomies. */
	public static function registerTaxonomies() {
	}

	/**
	 * Add the bulk action updated messages for custom post types.
	 * By default, custom post types use the messages for the 'post' post type.
	 *
	 * @param array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
	 *                             keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
	 * @param array $bulk_counts   Array of item counts for each message, used to build internationalized strings.
	 * @return array
	 */
	public static function mwBulkPostUpdatedMessages($bulk_messages, $bulk_counts) {
		$bulk_messages[MWS_PRODUCT_SLUG] = array(
			'updated'   => _n( '%s produkt aktualizován.', '%s produkty aktualizovány.', $bulk_counts['updated'] , 'mwshop', 'mwshop'),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 produkt nebyl aktualizován, někdo jej edituje.' , 'mwshop') :
				_n( '%s produkt nebyl aktualizován, někdo jej edituje.', '%s produkty nebyly aktualizovány, někdo je edituje.', $bulk_counts['locked'] , 'mwshop', 'mwshop'),
			'deleted'   => _n( '%s produkt nevratně smazán.', '%s produkty nevratně smazány.', $bulk_counts['deleted'] , 'mwshop', 'mwshop'),
			'trashed'   => _n( '%s produkt přesunut do koše.', '%s produkty přesunuty do koše.', $bulk_counts['trashed'] , 'mwshop', 'mwshop'),
			'untrashed' => _n( '%s produkt obnoven z koše.', '%s produkty obnoveny z koše.', $bulk_counts['untrashed'] , 'mwshop', 'mwshop'),
		);
		$bulk_messages[MWS_SHIPPING_SLUG] = array(
			'updated'   => _n( '%s doručovací metoda aktualizována.', '%s doručovací metody aktualizovány.', $bulk_counts['updated'] , 'mwshop'),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 doručovací metoda nebyla aktualizována, někdo ji edituje.' , 'mwshop') :
				_n( '%s doručovací metoda nebyla aktualizována, někdo ji edituje.', '%s doručovací metody nebyly aktualizovány, někdo je edituje.', $bulk_counts['locked'] , 'mwshop'),
			'deleted'   => _n( '%s doručovací metoda nevratně smazána.', '%s doručovací metody nevratně smazány.', $bulk_counts['deleted'] , 'mwshop'),
			'trashed'   => _n( '%s doručovací metoda přesunuta do koše.', '%s doručovací metody přesunuty do koše.', $bulk_counts['trashed'] , 'mwshop'),
			'untrashed' => _n( '%s doručovací metoda obnovena z koše.', '%s doručovací metody obnoveny z koše.', $bulk_counts['untrashed'] , 'mwshop'),
		);
		$bulk_messages[MWS_PROPERTY_SLUG] = array(
			'updated'   => _n( '%s parametr produktu aktualizován.', '%s parametry produktu aktualizovány.', $bulk_counts['updated'] , 'mwshop'),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 parametr produktu nebyl aktualizován, někdo jej edituje.' , 'mwshop') :
				_n( '%s parametry produktu nebyl aktualizován, někdo jej edituje.', '%s parametry produktu nebyly aktualizovány, někdo je edituje.', $bulk_counts['locked'] , 'mwshop'),
			'deleted'   => _n( '%s parametr produktu nevratně smazán.', '%s parametry produktu nevratně smazány.', $bulk_counts['deleted'] , 'mwshop'),
			'trashed'   => _n( '%s parametr produktu přesunut do koše.', '%s parametry produktu přesunuty do koše.', $bulk_counts['trashed'] , 'mwshop'),
			'untrashed' => _n( '%s parametr produktu obnoven z koše.', '%s parametry produktu obnoveny z koše.', $bulk_counts['untrashed'] , 'mwshop'),
		);
		$bulk_messages[MWS_ORDER_SLUG] = array(
			'updated'   => _n( '%s objednávka aktualizována.', '%s objednávky aktualizovány.', $bulk_counts['updated'] , 'mwshop'),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 objednávka nebyla aktualizována, někdo ji edituje.' , 'mwshop') :
				_n( '%s objednávka nebyla aktualizována, někdo ji edituje.', '%s objednávky nebyly aktualizovány, někdo je edituje.', $bulk_counts['locked'] , 'mwshop'),
			'deleted'   => _n( '%s objednávka nevratně smazána.', '%s objednávky nevratně smazány.', $bulk_counts['deleted'] , 'mwshop'),
			'trashed'   => _n( '%s objednávka přesunuta do koše.', '%s objednávky přesunuty do koše.', $bulk_counts['trashed'] , 'mwshop'),
			'untrashed' => _n( '%s objednávka obnovena z koše.', '%s objednávky obnoveny z koše.', $bulk_counts['untrashed'] , 'mwshop'),
		);
		$bulk_messages[MWS_VARIANT_SLUG] = array(
			'updated'   => _n( '%s varianta produktu aktualizována.', '%s varianty produktu aktualizovány.', $bulk_counts['updated'] , 'mwshop'),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 varianta produktu nebyla aktualizována, někdo ji edituje.' , 'mwshop') :
				_n( '%s varianta produktu nebyla aktualizována, někdo ji edituje.', '%s varianty produktu nebyly aktualizovány, někdo je edituje.', $bulk_counts['locked'] , 'mwshop'),
			'deleted'   => _n( '%s varianta produktu nevratně smazána.', '%s varianty produktu nevratně smazány.', $bulk_counts['deleted'] , 'mwshop'),
			'trashed'   => _n( '%s varianta produktu přesunuta do koše.', '%s varianty produktu přesunuty do koše.', $bulk_counts['trashed'] , 'mwshop'),
			'untrashed' => _n( '%s varianta produktu obnovena z koše.', '%s varianty produktu obnoveny z koše.', $bulk_counts['untrashed'] , 'mwshop'),
		);
		$bulk_messages[MWS_ORDER_STATUS_SLUG] = array(
			'updated'   => _n( '%s stav objednávky aktualizován.', '%s stavy objednávky aktualizovány.', $bulk_counts['updated'] , 'mwshop'),
			'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 stav objednávky nebyl aktualizován, někdo jej edituje.' , 'mwshop') :
				_n( '%s stav objednávky nebyl aktualizován, někdo jej edituje.', '%s stavy objednávky nebyly aktualizovány, někdo je edituje.', $bulk_counts['locked'] , 'mwshop'),
			'deleted'   => _n( '%s stav objednávky nevratně smazán.', '%s stavy objednávky nevratně smazány.', $bulk_counts['deleted'] , 'mwshop'),
			'trashed'   => _n( '%s stav objednávky přesunut do koše.', '%s stavy objednávky přesunuty do koše.', $bulk_counts['trashed'] , 'mwshop'),
			'untrashed' => _n( '%s stav objednávky obnoven z koše.', '%s stavy objednávky obnoveny z koše.', $bulk_counts['untrashed'] , 'mwshop'),
		);

		return $bulk_messages;
	}

	/**
	 * Add post updated messages for custom post types.
	 * @param array $messages Post updated messages. For defaults @see $messages declarations above.
	 * @return array
	 */
	public static function mwPostUpdatedMessages($messages) {
		global $view_post_link_html, $preview_post_link_html, $scheduled_date, $scheduled_post_link_html;
		global $preview_url, $permalink;
		global $post_type;

		if(in_array($post_type, array(
			MWS_PRODUCT_SLUG,
			MWS_SHIPPING_SLUG,
			MWS_PROPERTY_SLUG,
			MWS_ORDER_SLUG,
			MWS_VARIANT_SLUG,
			MWS_ORDER_STATUS_SLUG,
		))) {
			// Preview post link.
			if($preview_post_link_html) {
				$preview_post_link_html = sprintf(' <a target="_blank" href="%1$s">%2$s</a>',
					esc_url($preview_url),
					__('Zobrazit náhled', 'mwshop')
				);
			}

			// Scheduled post preview link.
			if($preview_post_link_html) {
				$scheduled_post_link_html = sprintf(' <a target="_blank" href="%1$s">%2$s</a>',
					esc_url($permalink),
					__('Zobrazit náhled', 'mwshop')
				);
			}

			// View post link.
			if($preview_post_link_html) {
				$view_post_link_html = sprintf(' <a href="%1$s">%2$s</a>',
					esc_url($permalink),
					__('Zobrazit', 'mwshop')
				);
			}
		};

		$messages[MWS_PRODUCT_SLUG] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Produkt aktualizován.' , 'mwshop', 'mwshop') . $view_post_link_html,
			2 => __( 'Vlastní pole aktualizováno.' , 'mwshop', 'mwshop'),
			3 => __( 'Vlastní pole aktualizováno.' , 'mwshop', 'mwshop'),
			4 => __( 'Produkt aktualizován.' , 'mwshop', 'mwshop'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Produkt obnoven do revize z %s.' , 'mwshop', 'mwshop'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Produkt publikován.' , 'mwshop', 'mwshop') . $view_post_link_html,
			7 => __( 'Produkt uložen.' , 'mwshop', 'mwshop'),
			8 => __( 'Produkt odeslán.' , 'mwshop', 'mwshop') . $preview_post_link_html,
			9 => sprintf( __( 'Produkt naplánován na: %s.' , 'mwshop'), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => __( 'Koncept produktu aktualizován.' , 'mwshop') . $preview_post_link_html,
		);
		$messages[MWS_SHIPPING_SLUG] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Doručovací metoda aktualizována.' , 'mwshop') . $view_post_link_html,
			2 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			3 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			4 => __( 'Doručovací metoda aktualizována.' , 'mwshop'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Doručovací metoda obnovena do revize z %s.' , 'mwshop'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Doručovací metoda publikována.' , 'mwshop') . $view_post_link_html,
			7 => __( 'Doručovací metoda uložena.' , 'mwshop'),
			8 => __( 'Doručovací metoda odeslána.' , 'mwshop') . $preview_post_link_html,
			9 => sprintf( __( 'Doručovací metoda naplánována na: %s.' , 'mwshop'), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => __( 'Koncept doručovací metody aktualizován.' , 'mwshop') . $preview_post_link_html,
		);
		$messages[MWS_PROPERTY_SLUG] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Parametr produktu aktualizován.' , 'mwshop') . $view_post_link_html,
			2 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			3 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			4 => __( 'Parametr produktu aktualizován.' , 'mwshop'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Parametr produktu obnoven do revize z %s.' , 'mwshop'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Parametr produktu publikován.' , 'mwshop') . $view_post_link_html,
			7 => __( 'Parametr produktu uložen.' , 'mwshop'),
			8 => __( 'Parametr produktu odeslán.' , 'mwshop') . $preview_post_link_html,
			9 => sprintf( __( 'Parametr produktu naplánován na: %s.' , 'mwshop'), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => __( 'Koncept parametru produktu aktualizován.' , 'mwshop') . $preview_post_link_html,
		);
		$messages[MWS_ORDER_SLUG] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Objednávka aktualizována.' , 'mwshop') . $view_post_link_html,
			2 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			3 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			4 => __( 'Objednávka aktualizována.' , 'mwshop'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Objednávka obnovena do revize z %s.' , 'mwshop'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Objednávka publikována.' , 'mwshop') . $view_post_link_html,
			7 => __( 'Objednávka uložena.' , 'mwshop'),
			8 => __( 'Objednávka odeslána.' , 'mwshop') . $preview_post_link_html,
			9 => sprintf( __( 'Objednávka naplánována na: %s.' , 'mwshop'), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => __( 'Koncept objednávky aktualizován.' , 'mwshop') . $preview_post_link_html,
		);
		$messages[MWS_VARIANT_SLUG] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Varianta produktu aktualizována.' , 'mwshop') . $view_post_link_html,
			2 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			3 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			4 => __( 'Varianta produktu aktualizována.' , 'mwshop'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Varianta produktu obnovena do revize z %s.' , 'mwshop'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Varianta produktu publikována.' , 'mwshop') . $view_post_link_html,
			7 => __( 'Varianta produktu uložena.' , 'mwshop'),
			8 => __( 'Varianta produktu odeslána.' , 'mwshop') . $preview_post_link_html,
			9 => sprintf( __( 'Varianta produktu naplánována na: %s.' , 'mwshop'), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => __( 'Koncept varianty produktu aktualizován.' , 'mwshop') . $preview_post_link_html,
		);
		$messages[MWS_ORDER_STATUS_SLUG] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Stav objednávky aktualizován.' , 'mwshop') . $view_post_link_html,
			2 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			3 => __( 'Vlastní pole aktualizováno.' , 'mwshop'),
			4 => __( 'Stav objednávky aktualizován.' , 'mwshop'),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Stav objednávky obnoven do revize z %s.' , 'mwshop'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Stav objednávky publikován.' , 'mwshop') . $view_post_link_html,
			7 => __( 'Stav objednávky uložen.' , 'mwshop'),
			8 => __( 'Stav objednávky odeslán.' , 'mwshop') . $preview_post_link_html,
			9 => sprintf( __( 'Stav objednávky naplánován na: %s.' , 'mwshop'), '<strong>' . $scheduled_date . '</strong>' ) . $scheduled_post_link_html,
			10 => __( 'Koncept produktu aktualizován.' , 'mwshop') . $preview_post_link_html,
		);


		return $messages;
	}

	/** Add additional MioWeb columns for custom post type into header of custom post type's table view. */
	public static function mwAdminColumnHeaders($columns) {
		$postType = get_post_type();
		switch($postType) {
			case MWS_PRODUCT_SLUG:
				$columns[MWS_ID.'type'] = __('Typ produktu', 'mwshop');
				$columns[MWS_ID.'priceVatIncluded'] = __('Cena', 'mwshop');
				if(MWS()->getVATs()->isUsingVatAccounting())
					$columns[MWS_ID.'priceVatPercentage'] = __('Sazba DPH', 'mwshop');
				$columns[MWS_ID.'salePrice'] = __('Sleva', 'mwshop');
				$columns[MWS_ID.'stockItems'] = __('Sklad', 'mwshop');
				$columns[MWS_ID.'sellability'] = __('Omezení prodeje', 'mwshop');
				break;
			case MWS_SHIPPING_SLUG:
				$columns[MWS_ID.'priceVatIncluded'] = __('Cena', 'mwshop');
				$columns[MWS_ID.'priceVatPercentage'] = __('Sazba DPH', 'mwshop');
				$columns[MWS_ID.'cod'] = __('Možnost dobírky', 'mwshop');
				break;
			case MWS_ORDER_SLUG:
				$columns[MWS_ID.'status'] = __('Stav', 'mwshop');
				$columns[MWS_ID.'price'] = __('Cena', 'mwshop');
				$columns[MWS_ID.'isPaid'] = __('Zaplaceno', 'mwshop');
				$columns[MWS_ID.'customer'] = __('Zákazník', 'mwshop');
				break;
		}
		return $columns;
	}

	/** Print value for additional MioWeb column for custom post type into table view. */
	public static function mwAdminColumnValues($colId, $postId) {
		$noValue = '—';
		$colMws = str_replace(MWS_ID, '', $colId);
		if ($colMws === $colId)
			return; // not an MWS column
		$postType = get_post_type();
		$post = get_post($postId);
		if(!empty($post)) {
			switch ($postType) {
				case MWS_PRODUCT_SLUG:
					try {
						$product = MwsProduct::createNew($post);
						switch ($colMws) {
							case 'type':
								$structure = MwsProduct::getPostStructureTypeStatic($postId);
								switch ($structure) {
									case MwsProductStructureType::Single:
										$value = _x('běžný', 'product type - single', 'mwshop');
										break;
									case MwsProductStructureType::Variants:
										$value = _x('variantní', 'product type - variants', 'mwshop');
										break;
								}
								break;
							case 'priceVatIncluded':
								$value = $product->htmlPriceSaleFull();
								break;
							case 'priceVatPercentage':
								$value = ($product->price->vatPercentage * 100) . '%';
								break;
							case 'salePrice':
								switch($product->salePriceType) {
									case MwsSalePriceType::Continuous:
										$value = _x('trvalá', 'sale type - list of products - continuous', 'mwshop');
										break;
									case MwsSalePriceType::EnabledFrom;
										$value = sprintf(_x('od %s', 'sale type - list of products - enable from', 'mwshop'),
											mwFormatAsDateTime($product->salePriceEnabledFrom));
										break;
									case MwsSalePriceType::EnabledTill;
										$value = sprintf(__('do %s', 'sale type - list of products - enable till', 'mwshop'),
											mwFormatAsDateTime($product->salePriceEnabledTill));
										break;
									case MwsSalePriceType::EnabledInterval;
										$value = sprintf(__('od %s do %s', 'sale type - list of products - enable interval', 'mwshop'),
											mwFormatAsDateTime($product->salePriceEnabledFrom), mwFormatAsDateTime($product->salePriceEnabledTill));
										break;
									default:
										$value = $noValue;
								}
								$value = '<span class="'
									. ($product->salePriceType === MwsSalePriceType::None
										? ''
										: ($product->canDiscountNow() ? 'mws_saleprice_enabled' : 'mws_saleprice_disabled')
									)
									.'">' . $value . '</span>';
								break;
							case 'stockItems':
								$status = $product->getAvailabilityStatus(1, true);
								if($product->stockEnabled) {
									$stockCount = $product->stockCount;
									if($stockCount >= 0)
										$value = sprintf(_nx('%d kus', '%d kusů', $stockCount,
											'Column in admin list of products for stock', 'mwshop'), $stockCount);
									elseif($stockCount < 0) {
										if ($product->stockAllowBackorders)
											$value = sprintf(_nx('%d kus (v objednávkách)', '%d kusů (v objednávkách)', $stockCount,
												'Column in admin list of products for stock', 'mwshop'), $stockCount);
										else
											$value = sprintf(_nx('%d kus (v objednávkách)', '%d kusů (v objednávkách)', $stockCount,
												'Column in admin list of products for stock', 'mwshop'), $stockCount);
									} else
										$value = sprintf(_nx('%d kus', '%d kusů', $stockCount,
											'Column in admin list of products for stock', 'mwshop'), $stockCount);
								} else {
									$value = $noValue;
//									_x('(bez skladu)', 'Column in admin list of products for stock - no stock', 'mwshop');
								}
								$value = '<span class="' . $product->getAvailabilityCSS($status) .'">' . $value . '</span>';
								break;
							case 'sellability':
								switch($product->sellRestriction) {
									case MwsSellRestriction::FullDisable:
										$value = _x('neprodávat', 'selling restriction - list of products - full disable', 'mwshop');
										break;
									case MwsSellRestriction::EnabledFrom;
										$value = sprintf(_x('od %s', 'selling restriction - list of products - enable from', 'mwshop'),
											mwFormatAsDateTime($product->sellEnabledFrom));
										break;
									case MwsSellRestriction::EnabledTill;
										$value = sprintf(__('do %s', 'selling restriction - list of products - enable till', 'mwshop'),
											mwFormatAsDateTime($product->sellEnabledTill));
										break;
									case MwsSellRestriction::EnabledInterval;
										$value = sprintf(__('od %s do %s', 'selling restriction - list of products - enable interval', 'mwshop'),
											mwFormatAsDateTime($product->sellEnabledFrom), mwFormatAsDateTime($product->sellEnabledTill));
										break;
									default:
										$value = $noValue;
								}
								$value = '<span class="' . $product->getSellingCSS() .'">' . $value . '</span>';
								break;
						}
					} catch (Exception $e) {
						$value = __('chyba', 'mwshop');
					}
					break;
				case MWS_SHIPPING_SLUG:
					$shipping = MwsShipping::createNew($post);
					switch ($colMws) {
						case 'priceVatIncluded':
							$value = $shipping->price->htmlPriceVatIncluded();
							break;
						case 'priceVatPercentage':
							$value = ($shipping->price->vatPercentage * 100) . '%';
							break;
						case 'cod':
							if ($shipping->isCodSupported) {
								$value = __('ano', 'mwshop');
								if($shipping->codPrice->priceVatIncluded > 0)
									$value .= ' ('.__('příplatek','mwshop').' '.$shipping->codPrice->htmlPriceVatIncluded().')';
							} else
								$value = '-';
							break;
					}
					break;
				case MWS_ORDER_SLUG:
					try {
						$order = MwsOrder::createNew($post);
						$textNotAccessible = _x('(nedostupné)', 'List of orders - a column value is inaccessible cause order is not accessible.', 'mwshop');
						switch ($colMws) {
							case 'status':
								$value = MwsOrderStatus::getCaption($order->status);
								break;
							case 'price':
								$gateLive = $order->gateLive;
								$x = ($gateLive && !is_null($gateLive->price)) ? $gateLive->price->priceVatIncluded : null;
								if(!is_null($x)) {
									$priceVatIncluded = $x;
									$value = htmlPriceSimple($priceVatIncluded);
								} else {
									$value = $textNotAccessible;
								}
								break;
							case 'isPaid':
								$gateLive = $order->gateLive;
								$x = ($gateLive) ? $gateLive->isPaid : null;
								if(!is_null($x)) {
									$isPaid = $x;
									$value = ($isPaid ? __('ano', 'mwshop') : __('ne', 'mwshop'));
								} else {
									$value = $textNotAccessible;
								}
								break;
							case 'customer':
								$gateLive = $order->gateLive;
								$x = ($gateLive) ? $gateLive->formatInvoiceContact(true) : null;
								if(!is_null($x)) {
									$value = $gateLive->formatInvoiceContact(true);
								} else {
									$value = $textNotAccessible;
								}
								break;
						}
					} catch (Exception $e) {
						$value = __('data nelze získat', 'mwshop');
					}
					break;
			}
		}
		if(isset($value))
			echo $value;
	}

	/** Add additional columns for custom post type into header of custom post type's table view. */
	public static function adminColumnHeaders($columns) {
		$postType = get_post_type();
		$defs = static::getPropPostDefs($postType);
		if($defs) {
			$fields = $defs->getColumnFields();
			if($fields) {
				/** @var MwsPropFieldDef $field */
				foreach ($fields as $field) {
					$columns[MWS_ID.$field->name] = $field->caption;
				}
			}
		}

		return $columns;
	}

	/** Print value for additional column for custom post type into table view. */
	public static function adminColumnValues($colId, $postId) {
		$colMws = str_replace(MWS_ID, '', $colId);
		if ($colMws === $colId)
			return; // not an MWS column
		$postType = get_post_type();
		$defs = static::getPropPostDefs($postType);
		if($defs) {
			$field = $defs->getItemByIdSimple($colMws);
			if ($field) {
				$parentGroup = $field->getParentPropGroup();
				if ($parentGroup) {
					$meta = get_post_meta($postId, MWS_NAME . $parentGroup->name, true);
					$value = (isset($meta[$field->name])) ? $meta[$field->name] : null;
					MwsFieldEditor::renderEditor($field, $value);
				}
			}
		}
	}

	/** Adds a new group of specified name. */
	/**
	 * @param $postSlug string				Slug name of the post-type.
	 * @param $groupDef MwsPropGroupDef		Group definition to add.
	 */
	public static function addPropGroupDef($postSlug, $groupDef) {
		$def = static::$groupDefs[$postSlug];
		if(empty($def))
			static::$groupDefs[$postSlug] = $def = new MwsPropPostDef($postSlug);
		$def->addItem($groupDef);
	}

	/**
	 * Returns array of property group definitions for a custom post type.
	 * @param $postSlug string		Slug name of the custom post type.
	 * @return MwsPropPostDef|null       Returns definition of field of a post type or null.
	 */
	public static function getPropPostDefs($postSlug) {
		$def = static::$groupDefs[$postSlug];
		if(empty($def) || count($def)==0) {
			$def = static::initPropGroupDefs($postSlug);
		}
		return $def;
	}

	/**
	 * Initialize definition of properties for shop's custom post types. Initialization is done in case no definition
	 * is present yet. Initialized data are inserted into shop global list of properties ({@link MwsTypesRegistration::$groupDefs}).
	 * @param $postSlug string 		Slug name of the post type.
	 * @return MwsPropPostDef		Returns initialized definition
	 */
	private static function initPropGroupDefs($postSlug) {
		mwshoplog(__METHOD__."($postSlug)", MWLL_DEBUG);
		if($postSlug == MWS_PRODUCT_SLUG) {
			$def = static::$groupDefs[$postSlug];
			if(empty($def))
				static::$groupDefs[$postSlug] = $def = new MwsPropPostDef($postSlug);
			$def->addItem(
				new MwsPropGroupDef('core', __('Basic attributes', 'mwshop'), null, array(
					new MwsPropFieldDef('price', __('Price', 'mwshop'), 'price',
						array('showAdminTableColumn' => 1,)),
					new MwsPropFieldDef('availableFrom', __('Available from', 'mwshop'), 'date',
						array('placeholder'=>__('always', 'mwshop'))),
					new MwsPropFieldDef('availableTo', __('Available to', 'mwshop'), 'date',
						array('placeholder'=>__('always', 'mwshop'))),
					))
			);
			$def->addItem(
				new MwsPropGroupDef('stock', __('Stock / Inventory', 'mwshop'), null, array(
					new MwsPropFieldDef('useStock', __('Use stock management', 'mwshop'), 'check',
						array('placeholder'=>'0', 'showAdminTableColumn' => 0)),
					new MwsPropFieldDef('items', __('Items on stock', 'mwshop'), 'number',
						array('placeholder'=>'0', 'showAdminTableColumn' => 0)),
				))
			);
			$def->addItem(
				new MwsPropGroupDef('sync', __('Synchronization', 'mwshop'), null, array(
					new MwsPropFieldDef('syncStatus', __('Status of synchronization', 'mwshop'), 'syncStatus',
						array('showAdminTableColumn' => 1)),
					new MwsPropFieldDef('syncedLast', __('Last synchronized', 'mwshop'), 'syncedLast',
						array('showAdminTableColumn' => 1)),
					new MwsPropFieldDef('syncId', __('Sync Id', 'mwshop'), 'static',
						array('placeholder'=>'---', 'showAdminTableColumn' => 1,)),
				))
			);
			$def->addItem(
				new MwsPropGroupDef('custom', __('Custom properties', 'mwshop'), null, array(
					//TODO This definition should be loaded from global options of the mwshop, edited in global shop options.
					new MwsPropFieldDef('color', __('Sample color', 'mwshop'), 'color',
						array('placeholder'=>__('none', 'mwshop'))),
				))
			);

			return static::$groupDefs[$postSlug];
		} else if($postSlug == MWS_ORDER_SLUG) {
			$def = static::$groupDefs[$postSlug];
			if(empty($def))
				static::$groupDefs[$postSlug] = $def = new MwsPropPostDef($postSlug);
			$def->addItem(
				new MwsPropGroupDef('core', __('General Details', 'mwshop'), null, array(
					new MwsPropFieldDef('dateOrdered', __('Order date', 'mwshop'), 'date' ),
					new MwsPropFieldDef('orderStatus', __('Order status', 'mwshop'), 'orderStatus',
						array('showAdminTableColumn' => 1)),
					new MwsPropFieldDef('customerId', __('Customer', 'mwshop'), 'customer',
						array('placeholder' => __('Guest', 'mwshop'))),
					new MwsPropFieldDef('customerNote', __('Customer\'s note', 'mwshop'), 'textLong' ),
				))
			);
			$def->addItem(
				new MwsPropGroupDef('billing', __('Billing Details', 'mwshop'), null, array(
					new MwsPropFieldDef('billingAddr', __('Billing address', 'mwshop'), 'address'),
					new MwsPropFieldDef('company', __('Company details', 'mwshop'), 'company'),
					new MwsPropFieldDef('paymentType', __('Payment method', 'mwshop'), 'paymentType'),
				))
			);
			$def->addItem(
				new MwsPropGroupDef('shipping', __('Shipping Details', 'mwshop'), null, array(
					new MwsPropFieldDef('shippingAddr', __('Shipping address', 'mwshop'), 'address'),
					new MwsPropFieldDef('shippingType', __('Shipping method', 'mwshop'), 'shippingType'),
					new MwsPropFieldDef('shippingNote', __('Company details', 'mwshop'), 'company'),
				))
			);
			$def->addItem(
				new MwsPropGroupDef('items', __('Order Items', 'mwshop'), null, array(
					new MwsPropFieldDef('itemsList', __('List of items', 'mwshop'), 'static',
						array('placeholder'=>__('no items', 'mwshop'), )),
					new MwsPropFieldDef('itemsCount', __('Count of items', 'mwshop'), 'static',
						array('showAdminTableColumn' => 1, 'placeholder'=>0)),
					new MwsPropFieldDef('priceItems', __('Price for items', 'mwshop'), 'priceStatic',
						array('showAdminTableColumn' => 1)),
					new MwsPropFieldDef('priceShipping', __('Price for shipping', 'mwshop'), 'priceStatic',
						array('showAdminTableColumn' => 1)),
					new MwsPropFieldDef('priceVat', __('VAT', 'mwshop'), 'priceStatic',
						array('showAdminTableColumn' => 1)),
					new MwsPropFieldDef('priceTotal', __('Order total', 'mwshop'), 'priceStatic',
						array('showAdminTableColumn' => 1)),
				))
			);
			$def->addItem(
				new MwsPropGroupDef('custom', __('Custom properties', 'mwshop'), null, array(
					//TODO This definition should be loaded from global options of the mwshop, edited in global shop options.
					new MwsPropFieldDef('color', __('Sample color', 'mwshop'), 'color',
						array('placeholder'=>__('(no color)', 'mwshop'))),
				))
			);

			return static::$groupDefs[$postSlug];
		}


		return null;
	}
}

MwsTypesRegistration::initClass();
//Make sure that after INIT of WP needed types are really registered. Even if not called directly.
//MwsTypesRegistration::registerHooks();