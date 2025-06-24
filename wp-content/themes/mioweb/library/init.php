<?php
// ********* LOAD LANGUAGE

load_theme_textdomain( 'cms', get_template_directory() . '/library/languages' );
$locale = get_locale();

$locale_file = get_template_directory() . "/library/languages/$locale.php";
if ( is_readable( $locale_file ) ) require_once( $locale_file );


define('CMS_VERSION','0.9');
define('UPDATE_SERVER','http://servis.mioweb.cz/update');
define('LICENSE_SERVER','https://mioweb-admin.smartcluster.net/public/');

require_once(TEMPLATEPATH . '/library/update.php');
require_once(TEMPLATEPATH . '/library/main/logger.php');
require_once(TEMPLATEPATH . '/library/main/functions.php');
require_once(TEMPLATEPATH . '/library/main/main_classes.php'); 

//define('SE_API',TEMPLATEPATH.'/library/api/se/base.php');  
define('FAPI_API',TEMPLATEPATH.'/library/api/fapi/FAPIClient.php');

//require_once(__DIR__.'/api/se/se.php');     
require_once(__DIR__.'/api/fapi/fapi.php'); 
require_once(__DIR__.'/api/affilbox/functions.php'); 
require_once(__DIR__.'/api/api_class.php'); 

$cms = New Cms();

require_once(TEMPLATEPATH . '/library/main/skins.php');
require_once(TEMPLATEPATH . '/library/main/field_types.php');
require_once(TEMPLATEPATH . '/library/init_set.php');
if(is_admin()) {
  require_once(TEMPLATEPATH . '/library/main/admin.php');   
}
else {
  
}
require_once(TEMPLATEPATH . '/skin/functions.php');

// ********* LOAD STYLES AND SCRIPTS

add_action( 'wp_enqueue_scripts', 'cms_register_scripts' );
add_action( 'admin_enqueue_scripts', 'cms_register_scripts' );
add_action('admin_enqueue_scripts', 'load_cms_admin_scripts', 10);
add_action('admin_print_styles', 'load_cms_admin_styles');
add_action( 'wp_enqueue_scripts', 'cms_localize_scripts', 20 );
add_action( 'admin_enqueue_scripts', 'cms_localize_scripts', 20 );

function cms_register_scripts() {
  global $cms;
  
  wp_register_style('font_icon_style',get_template_directory_uri().'/modules/visualeditor/css/fontello.css' ); 
  wp_register_style('cms_datepicker_style',get_template_directory_uri() . '/library/includes/datepicker/datepicker.css' ); 
  
  wp_register_script('cms_admin_script', get_template_directory_uri().'/library/admin/js/admin.js', array('jquery','media-upload','thickbox', 'jquery-ui-sortable'),$cms->script_version);
  wp_register_script('cms_datepicker_cs', get_template_directory_uri().'/library/includes/datepicker/jquery.ui.datepicker-cs.js',array('jquery-ui-datepicker'),$cms->script_version);
  wp_register_script( 'mw_api_script',get_bloginfo('template_url').'/library/api/api.js',array('jquery'),$cms->script_version);
  wp_register_script( 've_weditor_admin_script',get_bloginfo('template_url').'/modules/visualeditor/lib/weditor/weditor_admin.js',array('jquery'),$cms->script_version);
}

function load_cms_admin_scripts() {
    global $cms;
    
    if (isset($_GET['page']) || isset($_GET['post']) || isset($_GET['taxonomy'])) {
        wp_enqueue_script('media-upload'); 
        wp_enqueue_script('thickbox');
        wp_enqueue_media();
    }    
        
    $current_screen = get_current_screen();


    wp_enqueue_script('cms_minicolor_script',get_template_directory_uri().'/library/includes/minicolors/jquery.minicolors.js' ,array('jquery'),$cms->script_version,true);
    wp_localize_script( 'cms_minicolor_script', 've_used_colors', isset($_SESSION['ve_used_colors'])? $_SESSION['ve_used_colors']:array());         
    wp_enqueue_script('cms_lightbox_script', get_template_directory_uri().'/library/includes/cms_lightbox/lightbox.js', array('jquery'),$cms->script_version);        
    wp_enqueue_script('jquery-ui-datepicker');    
    wp_enqueue_script('cms_datepicker_cs');
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_script('cms_admin_script');
    wp_localize_script( 'cms_admin_script', 'wpadmin', "");
    wp_localize_script( 'cms_admin_script', 'siteurl', home_url());
    wp_enqueue_script( 'mw_api_script' );
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-sortable');
    
    wp_enqueue_script( 've_weditor_admin_script' );

    if($current_screen->id=='widgets'){
        wp_enqueue_script('cms_widgets_script', get_template_directory_uri().'/library/admin/js/widgets.js',array('jquery'),$cms->script_version);
        wp_enqueue_media();
    }
    
}

function load_cms_admin_styles() {
    global $cms;

    wp_enqueue_style( 'cms_admin_styles', get_template_directory_uri().'/library/admin/admin.css',array(),$cms->script_version );
    wp_enqueue_style('cms_editor_css',get_template_directory_uri().'/modules/visualeditor/css/editor.css',array(),$cms->script_version ); 
    wp_enqueue_style('cms_datepicker_style', get_template_directory_uri().'/library/includes/datepicker/datepicker.css'); 
    wp_enqueue_style('cms_minicolor_css',get_template_directory_uri().'/library/includes/minicolors/jquery.minicolors.css' ); 
    wp_enqueue_style('cms_lightbox_css',get_template_directory_uri().'/library/includes/cms_lightbox/lightbox.css',array(),$cms->script_version );     
    
    if (isset($_GET['page']) || isset($_GET['post'])) {
        wp_enqueue_style('thickbox');
    }  
     
}

function cms_localize_scripts() {
    require_once(__DIR__.'/admin/js_texts.php');
    wp_localize_script( 'cms_admin_script', 'MioAdminjs', $js_texts['admin'] ); 
    wp_localize_script( 'cms_lightbox_script', 'lightbox_texts', $js_texts['lightbox'] );
    wp_localize_script( 'cms_datepicker_cs', 'datepicker_texts', $js_texts['datepicker'] );
    wp_localize_script( 'mw_api_script', 'ajaxurl', admin_url( 'admin-ajax.php' ));
}

// Prolongate wp_remote_get timeout
if(defined('MW_HTTP_REMOTE_GET_TIMEOUT') && is_int(MW_HTTP_REMOTE_GET_TIMEOUT) && MW_HTTP_REMOTE_GET_TIMEOUT > 1) {
	add_filter('http_request_timeout', 'mw_prolongate_wp_remote_get_timeout');
	/**
	 * @param int $timeoutSec
	 * @return int
	 */
	function mw_prolongate_wp_remote_get_timeout($timeoutSec) {
		return MW_HTTP_REMOTE_GET_TIMEOUT;
	}
}
