<?php   
class visualEditorPage {
var $post_id;   
var $save_id;                                                                       
var $post_status;
var $template;                      
var $layer;
var $page_setting;                      
var $template_setting;
var $template_config;
var $header_setting;
var $h_menu;
var $footer_setting;
var $f_menu;
var $edit_mode;
var $editable_type=array('page');
var $elements=array();
var $element_scripts=array();
var $element_groups=array();
var $shortcodes=array();
var $shortcode_groups=array();
var $top_panel_menu=array();
var $styles=array();
var $mobile_styles=array();
var $home_url;
var $google_fonts=array();
var $page_type;
var $modul_type;
var $list_icons;
var $list_headers;         
var $set_list=array();
var $edited_page=0;
var $is_mobile;
var $is_iphone;
var $tutorials;
var $popups;
var $window_editor;
var $window_editor_setting=array();
var $script_version;
var $webs=array();
var $js_texts;
var $row_setting; 
var $rows;
var $template_visual_setting=NULL;
var $google_map_api;

function __construct()
    {

        $this->script_version = filemtime(get_template_directory() . '/style.css');

        $this->check_version();
        if (current_user_can('edit_pages')) $this->edit_mode = true;
        else $this->edit_mode = false;


        // window editor init (visual editor in iframe window - popups)
        if (isset($_GET['window_editor'])) {
            $this->window_editor = true;
            $this->window_editor_setting['type'] = $_GET['window_editor'];
            if (isset($_GET['id'])) {
                $this->window_editor_setting['id'] = $_GET['id'];
                $this->window_editor_setting['new'] = false;
            } else {
                $this->window_editor_setting['id'] = 0;
                $this->window_editor_setting['new'] = true;
            }
        }

        require_once(__DIR__ . '/js/js_texts.php');
        $this->js_texts = $js_texts;

        $this->home_url = home_url();
        if (!$this->modul_type) $this->modul_type = 'web';
        add_filter('show_admin_bar', '__return_false');
        add_action('wp', array($this, 'init'), 1);

        add_action('wp_login', array($this, 'after_login'));

        if (!session_id()) {
            session_start();
        }

        if ($this->edit_mode) {
            // no cache for explorers

            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: -1');

            // default fonts
            $this->google_fonts['Open Sans']['700'] = 700;
            $this->google_fonts['Open Sans']['400'] = 400;

            if (!isset($_SESSION['ve_used_colors'])) {
                $_SESSION['ve_used_colors'] = array();
                if (get_option('ve_used_colors')) $_SESSION['ve_used_colors'] = get_option('ve_used_colors');
            }
            if (!isset($_SESSION['ve_used_fonts'])) {
                $_SESSION['ve_used_fonts'] = array();
                if (get_option('ve_used_fonts')) $_SESSION['ve_used_fonts'] = get_option('ve_used_fonts');
            }
            if (!isset($_SESSION['ve_used_buttons'])) {
                $_SESSION['ve_used_buttons'] = array();
                if (get_option('ve_used_buttons')) $_SESSION['ve_used_buttons'] = get_option('ve_used_buttons');
            }

            if (isset($_POST["ve_connect_fapi"])) {
                add_action('init', 'after_save_connection');
                add_action('ve_after_save_options', 'after_save_connection');
            }

            add_action('init', array($this, 'actions'), 1);

            add_action('wp_ajax_ve_change_template', array($this, 'change_template'));
            add_action('wp_ajax_ve_create_page', array($this, 'create_page'));

            add_action('wp_ajax_open_element_setting', array($this, 'open_element_setting'));
            add_action('wp_ajax_open_element_config', array($this, 'open_element_config'));
            add_action('wp_ajax_open_new_element_setting', array($this, 'open_new_element_setting'));
            add_action('wp_ajax_open_new_shortcode_setting', array($this, 'open_new_shortcode_setting'));
            add_action('wp_ajax_open_element_select', array($this, 'open_element_select'));
            add_action('wp_ajax_open_shortcode_select', array($this, 'open_shortcode_select'));
            add_action('wp_ajax_save_element_setting', array($this, 'save_element_setting'));
            add_action('wp_ajax_save_element_config', array($this, 'save_element_config'));
            add_action('wp_ajax_open_row_setting', array($this, 'open_row_setting'));
            add_action('wp_ajax_open_row_select', array($this, 'open_row_select'));
            add_action('wp_ajax_add_new_row', array($this, 'add_new_row'));
            add_action('wp_ajax_paste_row', array($this, 'paste_row'));
            add_action('wp_ajax_copy_row', array($this, 'copy_row'));
            add_action('wp_ajax_save_row_setting', array($this, 'save_row_setting'));
            add_action('wp_ajax_save_page', array($this, 'save_page'));
            add_action('wp_ajax_delete_page', array($this, 'delete_page_ajax'));

            add_action('wp_ajax_ve_sort_rows', array($this, 'sort_rows'));
            add_action('wp_ajax_ve_sort_elements', array($this, 'sort_elements'));

            add_action('wp_ajax_ve_check_url', array($this, 've_check_url'));

            add_action('wp_ajax_open_page_setting', array($this, 'open_page_setting'));
            add_action('wp_ajax_open_page_single_setting', array($this, 'open_page_single_setting'));
            add_action('wp_ajax_open_global_setting', array($this, 'open_global_setting'));
            add_action('wp_ajax_open_global_single_setting', array($this, 'open_global_single_setting'));
            add_action('wp_ajax_open_global_single_setting_tab', array($this, 'open_global_single_setting_tab'));

            add_action('wp_restore_post_revision', array($this, 'layer_revision'));

            // front only editor
            add_action('wp_enqueue_scripts', array($this, 'load_front_editor_scripts'));
            //$this->modul_dir=get_bloginfo('template_url');

            //admin
            add_action('admin_init', array($this, 'actions'), 1);
            add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts'));
            add_action('edit_form_after_title', array($this, 'admin_page_edit'));

            add_filter('body_class', array($this, 've_body_class'));
            add_action("cms_activation", array($this, 've_activation'));

            // delete post
            add_action('before_delete_post', array($this, 'delete_page_hook'));

            // menu edit
            add_filter('wp_nav_menu', array($this, 'menu_filter'), 10, 2);
            add_action('wp_ajax_open_menu_setting', array($this, 'open_menu_setting'));
            add_action('wp_ajax_open_single_menu_setting', array($this, 'open_single_menu_setting'));
            add_action('wp_ajax_save_menu_setting', array($this, 'save_menu_setting'));
            add_action('wp_ajax_ve_generate_edit_menu_item', array($this, 've_generate_edit_menu_item'));
            add_action('wp_ajax_ve_change_menu_setting', array($this, 'change_menu_setting'));
            add_action('wp_ajax_ve_create_new_menu', array($this, 'create_new_menu'));
            add_action('wp_ajax_delete_menu', array($this, 'delete_menu_ajax'));

            // set default text editor tab
            add_filter('wp_default_editor', create_function('', 'return "tinymce";'));

            // add image sizes to media library
            add_filter('image_size_names_choose', array($this, 'display_custom_image_sizes'));

            $this->tutorials = New introTutorials();
        }

        //popups library
        $this->popups = New cmsPopups();
        $this->weditor = New cmsWEditor();

        $this->is_mobile = wp_is_mobile();
        $this->is_iphone = (stripos($_SERVER['HTTP_USER_AGENT'],"iPad") || stripos($_SERVER['HTTP_USER_AGENT'],"iPhone"));

        // front web
        add_action('wp_enqueue_scripts', array($this, 'load_front_scripts'));
        add_action('body_class', array($this, 'add_bodyclass'));
        add_action('wp_footer', array($this, 'add_page_footer'));
        add_action('wp_head', array($this, 'add_page_header_scripts'));
        add_filter('the_content', array($this, 'create_content'));
        
        // generated styles
        /*
        add_action('wp_ajax_mw_create_dynamic_css', array($this,'mw_create_dynamic_css'));
        add_action('wp_ajax_nopriv_mw_create_dynamic_css', array($this,'mw_create_dynamic_css'));
        */

        // redirect after login
        add_filter('login_redirect', array($this, 'login_redirect'), 10, 3);

        // send contact form
        add_action('wp_ajax_nopriv_ve_send_contact_form', array($this, 'send_contact_form'));
        add_action('wp_ajax_ve_send_contact_form', array($this, 'send_contact_form'));

        // save form data
        add_action('wp_ajax_nopriv_ve_save_form_data', array($this, 'save_form_data'));
        add_action('wp_ajax_ve_save_form_data', array($this, 'save_form_data'));

        add_action('init', array($this, 'web_actions'), 2);
        /*
    add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
		add_filter( 'mce_buttons_2', array( $this, 'mce_buttons_2' ) );
		add_filter( 'content_save_pre', array( $this, 'content_save_pre' ), 15 );
    */
    }
    
function mw_create_dynamic_css() {
  header("Content-type: text/css");
  echo get_post_meta($_GET['post_id'],'mw_generated_css', true);
  exit;
}

    /*
public static function mce_external_plugins( $plugin_array ) {

		$plugin_array['table'] = VS_DIR . 'lib/tinymce_tables/tables.min.js';

		return $plugin_array;
}
public static function mce_buttons_2( $buttons ) {

			// in case someone is manipulating other buttons, drop table controls at the end of the row
			if ( ! $pos = array_search( 'undo', $buttons ) ) {
				array_push( $buttons, 'table' );
				return $buttons;
			}

			$buttons = array_merge( array_slice( $buttons, 0, $pos ), array( 'table' ), array_slice( $buttons, $pos ) );

		return $buttons;
}
public static function content_save_pre( $content ) {
		if ( false !== strpos( $content, '<table' ) ) {
			// paragraphed content inside of a td requires first paragraph to have extra line breaks (or else autop breaks)
			$content  = preg_replace( "/<td([^>]*)>(.+\r?\n\r?\n)/m", "<td$1>\n\n$2", $content );

			// make sure there's space around the table
			if ( substr( $content, -8 ) == '</table>' ) {
				$content .= "\n<br />";
			}
		}

		return $content;
}
*/         

    function save_form_data()
    {
        $data = array();
        $forbid = array('action', 'referrer', 'webFormRenderer-webForm-submit');
        foreach ($_POST as $key => $val) {
            if (!in_array($key, $forbid))
                $data[$key] = trim($val);
        }
        setcookie('ve_form_data', serialize($data), time() + (60 * 60 * 24 * 365), "/");
        die();
    }

    function login_redirect($redirect_to)
    {
        if (!isset($_POST['cms_abort_redirect'])) $redirect_to = home_url();
        return $redirect_to;
    }

    function ve_body_class($classes)
    {
        if (isset($_COOKIE['ve_hidden_features']) && $_COOKIE['ve_hidden_features']) $classes[] = 've_hidden_features';
        if (isset($_COOKIE['ve_hidden_panel']) && !$_COOKIE['ve_hidden_panel']) $classes[] = 've_editor_panel_visible';
        return $classes;
    }

    function add_bodyclass($classes)
    {
        $classes[] = 've-header-' . $this->header_setting['appearance'];
        if (isset($this->template_config['body_class'])) $classes[] = $this->template_config['body_class'];
        if (isset($this->page_setting['li'])) $classes[] = 've_list_style' . $this->page_setting['li'];
        if (wp_is_mobile()) $classes[] = "ve_on_mobile";
        if (!(isset($this->page_setting['background_setting']) && $this->page_setting['background_setting']!='image') && isset($this->page_setting['background_image']['image']) && $this->page_setting['background_image']['image'] && isset($this->page_setting['background_image']['cover']) && isset($this->page_setting['background_image']['color_filter'])) 
            $classes[] = "ve_colored_background";
        return $classes;
    }


    function load_front_editor_scripts()
    {

        wp_register_script('ve_fapi_script', get_bloginfo('template_url') . '/library/api/fapi/fapi.js', array('jquery'));
        wp_register_script('ve_se_email_corrector', 'https://app.smartemailing.cz/public/email-correction-suggester-loader');
        
        wp_enqueue_script('jquery');

        wp_enqueue_script('media-upload');
        wp_enqueue_media();

        wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-dialog');

        wp_enqueue_script('wpdialogs');

        wp_enqueue_script('editor');
        wp_enqueue_script('quicktags');

        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('cms_datepicker_style');
        wp_enqueue_script('cms_datepicker_cs');

        wp_register_script('jquery-ui-nestedsortable', get_bloginfo('template_url') . '/modules/visualeditor/js/jquery.mjs.nestedSortable.js', array('jquery-ui-sortable'));

        wp_enqueue_script('ve-fronteditor-script', get_bloginfo('template_url') . '/modules/visualeditor/js/front_editor.js', array('jquery', 'jquery-ui-nestedsortable'), $this->script_version, true);
        wp_enqueue_script('ve-editor-script', get_bloginfo('template_url') . '/modules/visualeditor/js/editor.js');
        wp_enqueue_script('ve_minicolor_script', get_template_directory_uri() . '/library/includes/minicolors/jquery.minicolors.js');
        wp_localize_script('ve_minicolor_script', 've_used_colors', $_SESSION['ve_used_colors']);
        wp_enqueue_style('ve_minicolor_css', get_template_directory_uri() . '/library/includes/minicolors/jquery.minicolors.css');
        wp_localize_script('ve-fronteditor-script', 'ajaxurl', admin_url('admin-ajax.php'));

        wp_enqueue_style('cms_admin_styles', get_template_directory_uri() . '/library/admin/admin.css', array(), $this->script_version);
        wp_enqueue_script('cms_admin_script');
        wp_localize_script('cms_admin_script', 'wpadmin', admin_url());
        wp_localize_script('cms_admin_script', 'siteurl', home_url());

        wp_enqueue_script('cms_lightbox_script', get_bloginfo('template_url') . '/library/includes/cms_lightbox/lightbox.js');
        wp_enqueue_style('cms_lightbox_style', get_template_directory_uri() . '/library/includes/cms_lightbox/lightbox.css');

        wp_enqueue_style('ve_front_style', get_template_directory_uri() . '/modules/visualeditor/css/front.css', array(), $this->script_version);
        wp_enqueue_style('ve_editor_style', get_template_directory_uri() . '/modules/visualeditor/css/editor.css', array(), $this->script_version);

        wp_enqueue_style('font_icon_style');

        wp_enqueue_script('ve_fapi_script');

        wp_enqueue_style('ve_install_style', get_bloginfo('template_url') . '/modules/visualeditor/lib/install/style.css', array(), $this->script_version);
        wp_enqueue_script('ve_install_scripts', get_bloginfo('template_url') . '/modules/visualeditor/lib/install/install.js', array(), $this->script_version);

        wp_enqueue_script('mw_api_script');

        //localize scripts
        wp_localize_script('ve-editor-script', 'ed_texts', $this->js_texts['editor']);
        wp_localize_script('ve-fronteditor-script', 'texts', $this->js_texts['front_editor']);
        wp_localize_script('ve_weditor_admin_script', 'weditor_texts', $this->js_texts['weditor']);      

    }

    function load_front_scripts()
    {

        //register scripts
        wp_register_script('ve_lightbox_script', get_bloginfo('template_url') . '/modules/visualeditor/includes/lightbox/lightbox.js', array('jquery'), $this->script_version, true);
        wp_register_script('ve_waypoints_script', get_bloginfo('template_url') . '/modules/visualeditor/includes/animate/waypoints.min.js', array('jquery'), true);
        //wp_register_script('picturefill', get_bloginfo('template_url') . '/modules/visualeditor/js/picturefill.min.js', array(), 1, true);
        wp_register_script('velocity', get_bloginfo('template_url') . '/modules/visualeditor/js/velocity.min.js', array(), 1, true);
        //wp_register_script('ve_admin_image_gallery', get_bloginfo('template_url') . '/modules/visualeditor/js/admin_image_gallery.js', array('picturefill'), 1, true);
        wp_register_script('front_menu', get_bloginfo('template_url') . '/modules/visualeditor/js/front_menu.js', array('jquery', 'velocity'), 1, true);
        wp_register_script('ve_countdown_script', get_bloginfo('template_url') . '/modules/visualeditor/includes/countdown/jquery.countdown.js', array('jquery'), 5, true);
        wp_register_script('ve_miocarousel_script', get_bloginfo('template_url') . '/modules/visualeditor/includes/miocarousel/miocarousel.js', array('jquery'), $this->script_version, true);
        wp_register_script('ve_masonry_script', get_bloginfo('template_url') . '/modules/visualeditor/includes/mansory/mansory.min.js', array('jquery'), $this->script_version, true);
        wp_register_script('ve-front-script', get_bloginfo('template_url') . '/modules/visualeditor/js/front.js',array('jquery'), $this->script_version, true);
        
        wp_register_script('ve_youtube_api', 'https://www.youtube.com/iframe_api',false, false, 3);
        
        wp_register_script('ve_social_sprinters', 'https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.14/iframeResizer.min.js');  
        
        //google_maps_api
        $this->google_map_api=get_option('ve_google_api');
        if($this->google_map_api && isset($this->google_map_api['api_key']) && $this->google_map_api['api_key']) $gmap_api_key=$this->google_map_api['api_key'];
        else $gmap_api_key='AIzaSyDSyH51Ik2gY3QGHo4Isn45ogmUvfqKC6I';
        if($this->edit_mode) $gmap_api_key.='&libraries=places';
        wp_register_script('ve_google_maps', 'https://maps.googleapis.com/maps/api/js?key='.$gmap_api_key.'&callback=initialize_google_maps',false, false, 3);
        
        //localize scripts
        wp_localize_script('ve_countdown_script', 'velang', $this->js_texts['countdown']);

        //register styles
        wp_register_style('ve_lightbox_style', get_bloginfo('template_url') . '/modules/visualeditor/includes/lightbox/lightbox.css');
        wp_register_style('ve_animate_style', get_bloginfo('template_url') . '/modules/visualeditor/includes/animate/animate.css');
        wp_register_style('ve_countdown_style', get_bloginfo('template_url') . '/modules/visualeditor/includes/countdown/jquery.countdown.css');
        wp_register_style('ve_miocarousel_style', get_bloginfo('template_url') . '/modules/visualeditor/includes/miocarousel/miocarousel.css');

        //enqueue scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('front_menu');
        wp_enqueue_script('ve-front-script');
        wp_localize_script('ve-front-script', 'ajaxurl', admin_url('admin-ajax.php'));
        wp_localize_script('ve-front-script', 'front_texts', $this->js_texts['front']);

        //enqueue styles

        wp_enqueue_style('ve-content-style', get_bloginfo('template_url') . '/modules/visualeditor/css/content.css', array(), $this->script_version);
        
        // generated styles   
        // wp_enqueue_style('ve-generated-style', admin_url('admin-ajax.php').'?action=mw_create_dynamic_css&post_id='.$this->post_id, array(), $this->script_version);

        // get template css
        if (isset($this->template_config['styles']) && $this->template_config['styles'])
            wp_enqueue_style('ve-template-style', $this->get_template_file('style.css', true), array(), $this->script_version);

        if ($this->edit_mode) {
            wp_enqueue_script('ve_lightbox_script');
            wp_enqueue_style('ve_lightbox_style');
            wp_enqueue_script('ve_waypoints_script');
            wp_enqueue_style('ve_animate_style');
            //wp_enqueue_script('ve_admin_image_gallery');
            wp_enqueue_script('ve_countdown_script');
            wp_enqueue_style('ve_countdown_style');
            wp_enqueue_style('ve_miocarousel_style');
            wp_enqueue_script('ve_miocarousel_script');
            wp_enqueue_script('ve_google_maps');
            wp_enqueue_script('ve_social_sprinters');
        }
    }

    function load_admin_scripts()
    {
        $current_screen = get_current_screen();
        if ($current_screen->post_type === 'page' || isset($_GET['page']) || $current_screen->post_type === 'post') {
            wp_enqueue_script('visuale_editor_admin_script', get_template_directory_uri() . '/modules/visualeditor/js/admin_editor.js', array('jquery'), $this->script_version);
            wp_enqueue_script('visuale_editor_script', get_template_directory_uri() . '/modules/visualeditor/js/editor.js', array('jquery'), $this->script_version);
            wp_enqueue_style('visuale_editor_css', get_template_directory_uri() . '/modules/visualeditor/css/editor.css', array(), $this->script_version);
        }
        wp_enqueue_style('visuale_editor_admin_css', get_template_directory_uri() . '/modules/visualeditor/css/admin.css', array(), $this->script_version);
    }

    function init()
    {
        global $post;
        
        $save_id=0;

        // editor for post types like popups, footers... opened in iframe window
        if (is_404()) {
            $web_options = get_option('web_option_basic');
            if (isset($web_options['404page']) && $web_options['404page']) {
                $post_id = $web_options['404page'];
                $post = get_post($post_id);
                $this->page_type = $post->post_type;
            } else {
                $post_id = 0;
                $this->page_type = '404';
            }
        } else if ($this->window_editor) {
            $post_id = $this->window_editor_setting['id'];
            $this->page_type = $this->window_editor_setting['type'];
        } else if (is_home()) {
            $post_id = get_option('page_for_posts');
            $this->template_config['hide_rows'] = true;
            $this->page_type = 'blog';
            $post_id = 0;
        } else if(is_tax()) {
            $post_id = 0;
            $save_id = get_queried_object_id();
            $this->page_type=substr(get_query_var('taxonomy'),0,10);        
        } else if ($this->is_blog()) {
            $post_id = 0;
            $this->page_type = 'blog_type';
            if (is_author()) $this->page_type = 'author';
            else if (is_category()) $this->page_type = 'category';
            else if (is_tag()) $this->page_type = 'tag';
        } else if (isset($post->ID)) {
            $post_id = $post->ID;

            // page statistics
            if (!$this->edit_mode) {
                $original_id = $post_id;
                $page_statistics = get_post_meta($original_id, 'page_statistics', true);
                if (isset($page_statistics['pages']) && is_array($page_statistics['pages'])) {
                    if (isset($_COOKIE['ve_ab_page_' . $original_id]) && (in_array($_COOKIE['ve_ab_page_' . $original_id], $page_statistics['pages']) || $_COOKIE['ve_ab_page_' . $original_id] == $original_id) && get_page($_COOKIE['ve_ab_page_' . $original_id])) {
                        $post_id = $_COOKIE['ve_ab_page_' . $original_id];
                    } else {
                        $pag_count = count($page_statistics['pages']);
                        $show_page_id = rand(0, $pag_count);

                        if ($show_page_id != $pag_count) {
                            // delete deleted pages
                            if (!get_page($page_statistics['pages'][$show_page_id])) {
                                $new_page_statistics = array();
                                foreach ($page_statistics['pages'] as $spage) {
                                    if (get_page($spage)) $new_page_statistics[] = $spage;
                                }
                                $page_statistics['pages'] = $new_page_statistics;
                                update_post_meta($original_id, 'page_statistics', $page_statistics);
                            } // set post id to a/b variant
                            else $post_id = $page_statistics['pages'][$show_page_id];
                        }
                        if (isset($_COOKIE['ve_ab_page_' . $original_id])) unset($_COOKIE['ve_ab_page_' . $original_id]);
                    }
                };

                if (isset($_SESSION['ve_page_statistics'])) {
                    if ($_SESSION['ve_page_statistics']['target'] == $original_id) {
                        $count = get_post_meta($_SESSION['ve_page_statistics']['page'], 'page_conversion_rate', true);
                        if (isset($count[$_SESSION['ve_page_statistics']['source']]['con_target'])) $count[$_SESSION['ve_page_statistics']['source']]['con_target']++;
                        else $count[$_SESSION['ve_page_statistics']['source']]['con_target'] = 1;
                        update_post_meta($_SESSION['ve_page_statistics']['page'], 'page_conversion_rate', $count);
                    }
                    unset($_SESSION['ve_page_statistics']);
                }
                if (isset($page_statistics['target']) && $page_statistics['target']) {
                    if (!isset($_COOKIE['ve_ab_page_' . $original_id])) {
                        $visit = get_post_meta($original_id, 'page_conversion_rate', true);
                        if (isset($visit[$post_id]) && isset($visit[$post_id]['con_source'])) $visit[$post_id]['con_source']++;
                        else $visit[$post_id]['con_source'] = 1;
                        update_post_meta($original_id, 'page_conversion_rate', $visit);
                        $_SESSION['ve_page_statistics'] = array('page' => $original_id, 'source' => $post_id, 'target' => $page_statistics['target']);
                        setcookie('ve_ab_page_' . $original_id, $post_id, time() + (60 * 60 * 24 * 2), "/");
                    }
                }
            }
            if (is_single()) $this->page_type = $post->post_type;
            else $this->page_type = 'page';
        } else {
            $post_id = 0;
            $this->page_type = 'none';
        }
        
        $this->page_type = apply_filters( 've_page_type', $this->page_type, $post_id );
        $this->set_page($post_id, $save_id);
    }

    function set_page($post_id, $save_id=0) {
        $this->post_id=$post_id; 
        if(isset($save_id) && $save_id) $this->save_id=$save_id; 
        $this->post_status=get_post_status( $this->post_id ); 
        $this->layer=$this->get_layer(($save_id)? $save_id:$this->post_id, $this->page_type);  
        $this->create_setting();  
    }

    function get_layer($post_id, $page_type = 'page')
    {
        global $wpdb;
        if (isset($_SESSION['ve_layer_autosave'][$post_id])) {
            // save setting with not saved layer
            $layer = $_SESSION['ve_layer_autosave'][$post_id];
            return $this->decode($layer);
        } else {

            $result = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $page_type . "' AND vpl_post_id=" . $post_id);

            // ****************** temporary
            if (!$wpdb->num_rows && $page_type == 'blog' && $post_id == 0) {
                $wpdb->update($wpdb->prefix . "ve_posts_layer", array('vpl_post_id' => 0), array('vpl_type' => 'blog'));
                $result = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "ve_posts_layer WHERE vpl_type='" . $page_type . "' AND vpl_post_id=" . $post_id);
            }
            // ****************** end temporary

            if ($wpdb->num_rows) {
                return $this->decode($result->vpl_layer);
            } else return "";

        }
    }

    function create_content( $content='' ) {
        if($this->is_editable() && (! post_password_required() || $this->edit_mode) && !is_feed()) {
            $content=$this->write_content($this->layer, $this->edit_mode);
        }   
        return apply_filters('ve_content',$content);
    }
    function write_content($layer,$edit_mode, $pre='') {
        $content="";
        if($edit_mode) $content.='<div id="sortable-content">';
        if($layer) { 
            foreach($layer as $row_key=>$row) {
                if(!$this->is_mobile || !isset($row['style']['mobile_visibility'])) $content.=$this->generate_row($row, $row_key, $edit_mode, $pre);
            }
            $row_key++;
        }
        else $row_key=0;    
        if($edit_mode) $content.='<div class="add_row_last admin_feature">'.$this->generate_row_edit_bar(1).'</div>';
        if($edit_mode) $content.='</div>';
        return '<div id="content">'.$content.'</div>';
    }

    function generate_row($row, $row_key = '', $edit_mode = true, $pre = '', $added = false)
    {
        $row_key = ($row_key === '')? md5(microtime()) : $row_key;
        $row_id = $pre . 'row_' . $row_key;

        if (isset($row['style']['type']) && $row['style']['type'] == 'fixed') $rowclass = 'row_fixed';
        else if (isset($row['style']['type']) && $row['style']['type'] == 'full') {
          $rowclass = 'row_full';
          if($row['style']['padding_left']['size']=='0' && $row['style']['padding_right']['size']=='0')
              $rowclass .= ' row_full_0';
        }
        else $rowclass = 'row_basic';

        $rowclass .= ($pre) ? ' row_' . $pre : ' row_content';
        
        if(isset($row['type'])) {
          $rowclass .= ' row_' . $row['type'];
          
          if($row['type']=='slide') {
              $row['style']['height_setting']['full_height']=1;
              $row['style']['height_setting']['centered_content']=1;
          }
        }
        
        if (isset($row['style']['css_class']) && $row['style']['css_class']) $rowclass .= ' ' . $row['style']['css_class'];

        if (isset($row['style']['height_setting']['full_height']) && isset($row['style']['height_setting']['noheader'])) $rowclass .= ' row_window_height_noheader';
        else if (isset($row['style']['height_setting']['full_height'])) $rowclass .= ' row_window_height';
        $rowclass .= (isset($row['style']['height_setting']['centered_content'])) ? ' row_centered_content' : '';

        // display delay
        $datadelay = '';
        if (isset($row['style']['delay']) && $row['style']['delay'] && !$edit_mode) {
            //$content .= '<div class="row_container_delay" data-delay="'.$row['style']['delay'].'">';
            $rowclass .= ' row_container_delay';
            $datadelay = 'data-delay="'.$row['style']['delay'].'"';
        }

        

        $styles = array();
        $styles[] = array(
            'styles' => $row['style'],
            'element' => '#' . $row_id,
        );
        if (isset($row['style']['link_color']) && $row['style']['link_color']) {
            $styles[] = array(
                'styles' => array('color' => $row['style']['link_color']),
                'element' => '#' . $row_id . ' a',
            );
        }
        if (isset($row['style']['font']['color']) && $row['style']['font']['color']) {
            $styles[] = array(
                'styles' => array('color' => $row['style']['font']['color']),
                'element' => '#' . $row_id . ' h2,#' . $row_id . ' h1,#' . $row_id . ' h3,#' . $row_id . ' h4,#' . $row_id . ' h5,#' . $row_id . ' h6,#' . $row_id . ' .title_element_container,#' . $row_id . ' .form_container_title',
            );
        }
        if (isset($row['style']['padding_left']) && $row['style']['padding_left']['size'] != '') {
            $styles[] = array(
                'styles' => array('padding-left' => $row['style']['padding_left']['size'] . $row['style']['padding_left']['unit']),
                'element' => '#' . $row_id . ' .row_fix_width',
            );
        }
        if (isset($row['style']['padding_right']) && $row['style']['padding_right']['size'] != '') {
            $styles[] = array(
                'styles' => array('padding-right' => $row['style']['padding_right']['size'] . $row['style']['padding_right']['unit']),
                'element' => '#' . $row_id . ' .row_fix_width',
            );
        }
        if (isset($row['style']['margin_t']) && $row['style']['margin_t']['size'] != '') {
            $styles[] = array(
                'styles' => array('margin_top' => $row['style']['margin_t']['size']),
                'element' => '#' . $row_id,
            );
        }
        if (isset($row['style']['margin_b']) && $row['style']['margin_b']['size'] != '') {
            $styles[] = array(
                'styles' => array('margin_bottom' => $row['style']['margin_b']['size']),
                'element' => '#' . $row_id,
            );
        }
        if (isset($row['style']['height_setting']) && isset($row['style']['height_setting']['arrow'])) {
            $styles[] = array(
                'styles' => array('fill' => $row['style']['height_setting']['arrow_color']),
                'element' => '#' . $row_id. ' .mw_scroll_tonext_icon svg path',
            );
        }
        
        // fixed background for iphones
        if ($this->is_iphone) {  
            $styles[] = array(
                'styles' => array('background-attachment' => 'scroll'),
                'element' => '#' . $row_id,
            ); 
        }
        
        // color cover for image  
        if (!(isset($row['style']['background_setting']) && $row['style']['background_setting']!='image') && isset($row['style']['background_image']['image']) && $row['style']['background_image']['image'] && isset($row['style']['background_image']['cover']) && isset($row['style']['background_image']['color_filter'])) {
            $styles[] = array(
                'styles' => array('background-color' => $row['style']['background_image']['overlay_color'],'opacity'=>$row['style']['background_image']['overlay_transparency']),
                'element' => '#' . $row_id.':before',
            );
            $rowclass.=' ve_colored_background';   
        }
        
        // styles for mobile devices
        if (isset($row['style']['background_image']) && isset($row['style']['background_image']['mobile_hide'])) {
            $this->add_style(
                '#' . $row_id,
                array('background-image' => 'none'),
                '640'
            );
        } 
        
        $content='';   
        
         
        if(isset($row['style']['row_anchor']) && $row['style']['row_anchor']) $content.='<a id="'.$row['style']['row_anchor'].'"></a>';
        
        $content .= '<div ' . (($row_id) ? 'id="' . $row_id . '"' : '') . ' class="row ' . $rowclass . '" ' . $datadelay . '>';
        
        if ($edit_mode) $content .= '<div class="row_background_container">';

        // slider on row background
        if (isset($row['style']['background_setting']) && $row['style']['background_setting'] == 'slider') {
            $content .= $this->generate_slider_background($row['style']['background_slides'], $row['style']['background_delay'], $row['style']['background_speed'], $row['style']['background_color']['color1'], 'miocarousel_slider_' . $row_id);
        } else if(isset($row['style']['background_setting']) && $row['style']['background_setting'] == 'video') {
            if ((isset($row['style']['background_video_webm']) && ($row['style']['background_video_webm']) || (isset($row['style']['background_video_mp4']) && $row['style']['background_video_mp4']) || (isset($row['style']['background_video_ogg']) && $row['style']['background_video_ogg']))) {

                $mute = (isset($row['style']['video_setting']['sound'])) ? 'false' : 'true';

                if (!$this->is_mobile || isset($row['style']['video_setting']['show_mobile'])) {
                    $content .= '<div class="mw_row_video_background"><video autoplay="true" loop="true" muted="' . $mute . '">';
                    if ($row['style']['background_video_webm']) $content .= '<source src="' . $row['style']['background_video_webm'] . '" type="video/webm">';
                    if ($row['style']['background_video_mp4']) $content .= '<source src="' . $row['style']['background_video_mp4'] . '" type="video/mp4">';
                    if ($row['style']['background_video_ogg']) $content .= '<source src="' . $row['style']['background_video_ogg'] . '" type="video/ogg">';
                    $content .= '</video></div>';
                    $content .= '<!--[if lt IE 9]><![endif]-->';
                }
            }
        }

        if ($edit_mode) $content .= '</div>';

        if ($edit_mode) {
            $content .= '<textarea autocomplete="off" class="row_content_textarea" name="row[]">' . $this->code($row) . '</textarea>';
            $content .= $this->generate_row_edit_bar();
        }

        if (isset($row['style']['background_setting']) && $row['style']['background_setting'] == 'slider') {
            $styles[0]['styles']['background_color'] = array();
            $styles[0]['styles']['background_image'] = array();
        }
        $content .= $this->print_styles_array($styles);

        if(isset($row['type']) && $row['type']=='slider') $content .= $this->generate_slider_row($row, $row_key, $edit_mode, $pre, $added);
        else $content .= $this->generate_basic_row($row, $row_id, $rowclass, $row_key, $edit_mode, $pre, $added);

        $content .= '</div>';
        return $content;
    }
    
    function generate_basic_row($row, $row_id, $rowclass, $row_key, $edit_mode, $pre, $added) {
      $content='';

      $content .= '<div class="row_fix_width">';
      
      $col_num=0;
      foreach ($row['content'] as $col_key => $col) {
           
          $class = 'col ' . $col['type'];
          $class .= ($row_key) ? ' col_' . $row_key . '_' . $col_key : '';
          if ($col_num == 0) $class .= ' col-first';
          if ($edit_mode) $class .= ' sortable-col';
          if (($col_key == count($row['content']) - 1) || isset($col['break'])) $class .= ' col-last';
          $content .= '<div class="' . $class . '">';

          foreach ($col['content'] as $content_key => $code) {
              if (!$this->is_mobile || !isset($code['config']['mobile_visibility'])) $content .= $this->generate_element($code, $row_key . '_' . $col_key . '_' . $content_key, '', $edit_mode, $pre, $added, false, $row['style']);
          }
          if ($edit_mode) {
            $r_type = (isset($row['type']) && $row['type']=='slide')? $row['type'] : 'all';
            $content .= $this->generate_new_element_but(0, $r_type);
          }
          
          $content .= '</div>'; 
          
          $col_num++;
          
          if(isset($col['break'])) {
              $col_num=0;
              $content .= '<div class="ve_row_break"></div>'; 
          }
          

      }
      $content .= '<div class="cms_clear"></div></div>';
      if ($rowclass == 'row_basic') $content .= '</div>';

      

      if (isset($row['style']['height_setting']['arrow'])) $content .= $this->generate_next_to_scroll_link();

      
      return $content;
    }
    
    function generate_slider_row($row, $row_key, $edit_mode, $pre, $added) {
      $content='';
      $css_id = '#row_'.$row_key;
      
      $styles=array();
      
      wp_enqueue_script( 've_miocarousel_script' );
      wp_enqueue_style( 've_miocarousel_style' );
      
      $carousel_set='data-centered="row_fix_width"';
      if(isset($row['style']['off_autoplay'])) $carousel_set.=' data-autoplay="0"';
      if($row['style']['a_delay']) $carousel_set.=' data-duration="'.$row['style']['a_delay'].'"';
      if($row['style']['speed']) $carousel_set.=' data-speed="'.$row['style']['speed'].'"';
      if(!$row['style']['slider_height']) $carousel_set.=' data-height="full"';
      if($row['style']['animation'] && $row['style']['animation']!='fade') $carousel_set.=' data-animation="'.$row['style']['animation'].'"';
      
      $content .= '<div class="miocarousel miocarousel_style_3 miocarousel_'.$row['style']['color_scheme'].'" '.$carousel_set.'>';  
      $content .= '<div class="miocarousel-inner">';
      
      $row_num=1;

      if(isset($row['style']['slides'])) {
      foreach($row['style']['slides'] as $slide) {
          
          $row_class='slide';
          if($row_num==1) $row_class.=' active';

          if(isset($slide['slider_content']) && $slide['slider_content'] && get_post($slide['slider_content'])) {
              
              $layer=$this->get_layer($slide['slider_content'], 'mw_slider');  
              
              $style=$layer[0]['style'];
              
              $style['background_image']['cover']=1;

              $styles[]=array(
                  'styles'=>array(
                      'background_image'=>$style['background_image'], 
                      'background_color'=>$style['background_color'], 
                      'font'=>$style['font'], 
                      'min-height'=>$row['style']['slider_height']
                  ),
                  'element'=>$css_id.' #mw_slider_slide_'.$row_num,
              );
              $styles[]=array(
                  'styles'=>array(
                      'color'=>$style['link_color'], 
                  ),
                  'element'=>$css_id.' #mw_slider_slide_'.$row_num.' .entry_content a',
              );
              
              // color cover for image  
              if (isset($style['background_image']['image']) && $style['background_image']['image'] && isset($style['background_image']['cover']) && isset($style['background_image']['color_filter'])) {
                  $styles[] = array(
                      'styles' => array('background-color' => $style['background_image']['overlay_color'],'opacity'=>$style['background_image']['overlay_transparency']),
                      'element' => $css_id.' #mw_slider_slide_'.$row_num.':before',
                  );
                  $row_class.=' ve_colored_background';   
              }
              
              // styles for mobile devices
              if (isset($style['background_image']) && isset($style['background_image']['mobile_hide'])) {
                  $this->add_style(
                      $css_id.' #mw_slider_slide_'.$row_num,
                      array('background-image' => 'none'),
                      '640'
                  );
              } 
              
              $content .= '<div id="mw_slider_slide_'.$row_num.'" class="'.$row_class.'">';
              $content .= '<div class="row_fix_width">';
              
              // cols
              $col_num=count($layer[0]['content']);
              
              foreach ($layer[0]['content'] as $col_key => $col) {
                   
                  $class = 'col ' . $col['type'];
                  $class .= ($row_key) ? ' col_' . $row_key . '_' . $col_key : '';
                  if ($col_num == 0) $class .= ' col-first';
                  if (($col_key == count($col['content']) - 1) || isset($col['break'])) $class .= ' col-last';
                  $content .= '<div class="' . $class . '">';
                  
                  // elements
                  $i=0;
                  foreach ($col['content'] as $content_key => $code) {
                      $new_css_id=str_replace('#element_','',$row_key).'_'.$i;
                      if (!$this->is_mobile || !isset($code['config']['mobile_visibility'])) $content .= $this->generate_element($code, str_replace('#','',$new_css_id), '', false, 'var'.$slide['slider_content'].'_');
                      $i++;
                  }

                  $content .= '</div>'; 
                  
                  $col_num++;
                  
                  if(isset($col['break'])) {
                      $col_num=0;
                      $content .= '<div class="ve_row_break"></div>'; 
                  }
                  

              }
              
              $content .= '<div class="cms_clear"></div></div>';
              $content .= '</div>';
              
              $row_num++;
          }

          
      }
      }
      
      if($row_num==1) $content .= '<div class="row_fix_width" style="text-align:center;color:#888;">'.__('Tento slider nemá žádný obsah. Pravděpodobně byl smazán.','cms_ve').'</div>';
      
      $content .= '</div>';  //slider end
      $content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
      $content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
      if($added) {
          $content .= "";
      }
      
      $content.=$this->print_styles_array($styles);
      
      $content .= '</div><div class="cms_clear"></div>';

      return $content;
    }
    
    function generate_next_to_scroll_link() {
        return '<a href="#" class="mw_scroll_tonext_icon mw_scroll_tonext">'.file_get_contents(get_template_directory() ."/modules/visualeditor/images/more.svg", true).'</a>';
    }

    function generate_element($code, $key = '', $post_id = '', $edit_mode = true, $pre = '', $added = false, $single = false, $row_set=array())
    {
        global $post;
        $content = '';

        $post_id = ($this->post_id) ? $this->post_id : $post_id;
        $element_id = ($key) ? $pre . 'element_' . $key : $pre . 'element_' . md5(microtime());
        if (isset($code['config']['id']) && $code['config']['id']) $element_id = $code['config']['id'];
        $elconfig = (isset($code['config'])) ? $this->print_styles($code['config'], '#' . $element_id) : '';

        $el_class = 'element_container ' . $code['type'] . '_element_container ' . ((isset($code['config']['class'])) ? $code['config']['class'] : '');
        if (isset($code['config']['max_width']) && $code['config']['max_width']) $el_class .= ' element_container_max_width';

        if (isset($this->elements[$code['type']]['subelements'])) {
            $el_class .= 'subelement_container';
            $type = 'subelement';
        } else $type = 'element';

        // outside element
        if ($single) {
            $el_class .= ' element_single';
            $textarea_name = $key;
        } else $textarea_name = 'element[]';

        // animate
        if (isset($code['config']['animate']) && $code['config']['animate']) {
            $el_class .= ' ve_animation';
            $animate = 'data-animation="' . $code['config']['animate'] . '"';
            wp_enqueue_script('ve_waypoints_script');
            wp_enqueue_style('ve_animate_style');
            if ($added) {
                $content .= "";
            }
        } else $animate = '';

        $content .= '<div ' . $animate . ' ' . (($element_id) ? 'id="' . $element_id . '"' : '') . ' ' . $elconfig . ' class="' . $el_class . '">';


        // back compatibility (temporary)
        if ($code['type'] == 'classic_bullets') {
            if (isset($code['style']['font'])) $code['style']['text_font'] = $code['style']['font'];
            $code['type'] = 'bullets'; // remove classic bullets element
        }
        // back compatibility end

        // editbar
        if ($edit_mode) {
            $content .= '<div class="content_element_editbar" style="' . ((isset($code['config'])) ? $this->generate_style_atribut(array('padding_top' => $code['config']['margin_top'] + 6, 'top' => -($code['config']['margin_top'] + 6), 'padding_bottom' => $code['config']['margin_bottom'])) : '') . '"><div class="ce_editbar">';
            if (!$single) $content .= '<a class="ece_move" href="#" data-type="' . $type . '" title="Přesunout"></a>';
            if ((isset($this->elements[$code['type']]['setting']) && count($this->elements[$code['type']]['setting'])) || (isset($this->elements[$code['type']]['tab_setting']) && count($this->elements[$code['type']]['tab_setting']))) $content .= '<a class="ece_edit" data-type="' . $type . '" href="#" title="' . __('Editovat', 'cms_ve') . ' - ' . $this->elements[$code['type']]['name'] . '"></a>';
            if (!$single) $content .= '<a class="ece_config" data-type="' . $type . '" href="#" title="' . __('Nastavení elementu', 'cms_ve') . '"></a>';
            if (!$single) $content .= '<a class="ece_copy" href="#" title="' . __('Kopírovat', 'cms_ve') . '"></a>';
            if (!$single) $content .= '<a class="ece_delete" href="#" title="' . __('Smazat', 'cms_ve') . '"></a>';
            $content .= '</div></div><div class="element_content">';
        }
        if (isset($code['config']['delay']) && $code['config']['delay'] && !$edit_mode) $content .= '<div class="element_container_delay" data-delay="' . $code['config']['delay'] . '">';
        if (function_exists("ve_element_" . $code['type'])) $content .= call_user_func_array("ve_element_" . $code['type'], array($code, '#' . $element_id, $post_id, $edit_mode, $added, $row_set));
        else if ($this->edit_mode) $content .= '<div class="cms_error_box admin_feature">' . __('Tento element nelze zobrazit, pravděpodobně není v této verzi MioWebu podporován. Smažte jej nebo zvyšte verzi MioWebu.', 'cms_ve') . '</div>';
        if (isset($code['config']['delay']) && $code['config']['delay'] && !$edit_mode) $content .= '</div>';
        if ($edit_mode) $content .= '</div>';
        if ($edit_mode) $content .= '<textarea autocomplete="off" class="element_content_textarea" name="' . $textarea_name . '">' . $this->code($code) . '</textarea>';
        $content .= '</div>';
        return $content;
    }

    function print_single_element($key, $post_id, $setting = array())
    {
        $content = get_post_meta($post_id, 'single_elements', true);
        $code = (isset($content[$key])) ? $this->decode($content[$key]) : $setting;
        return $this->generate_element($code, $key, $post_id, $this->edit_mode, '', false, true);
    }

    function generate_row_edit_bar($last = 0)
    {
        if (!(isset($this->template_config['hide_rows']) && $last)) {
            $class = "row_edit_container admin_feature ";
            if (isset($this->template_config['hide_rows'])) $class .= "row_edit_container_editonly";
            if (isset($this->template_config['delete_rows'])) $class .= "row_edit_container_noedit";
            $content = '<div class="' . $class . '">';
            if (!$last) $content .= '<div class="right_row_editbar">'.apply_filters( 'mw_developer_edit_row', '' ).'</div>';
            $content .= '<div class="row_add_container">';
            $content .= '<a class="row_add" data-last="' . $last . '" href="#" title="' . __('Přidat řádek', 'cms_ve') . '">' . __('Přidat řádek', 'cms_ve') . '</a>';
            $content .= '<a class="row_paste ' . (isset($_SESSION['ve_copy_row']) ? '' : 'cms_nodisp') . '"  data-last="' . $last . '" href="#" title="' . __('Vložit řádek ze schránky', 'cms_ve') . '"></a>';
            $content .= '</div>';
            if (!$last) {
                $content .= '<div class="row_edit_bar">';
                $content .= '<a class="row_move" href="#" title="' . __('Přesunout', 'cms_ve') . '"></a>';
                $content .= '<a class="row_edit" href="#" title="' . __('Editovat řádek', 'cms_ve') . '"></a>';
                $content .= '<a class="row_copy" href="#" title="' . __('Vytvořit kopii řádku', 'cms_ve') . '"></a>';
                $content .= '<a class="row_copy_memory" href="#" title="' . __('Kopírovat řádek do schránky', 'cms_ve') . '"></a>';
                $content .= '<a class="row_delete" href="#" title="' . __('Smazat řádek', 'cms_ve') . '"></a>';
                $content .= '</div>';
            }
            $content .= '</div>';
            return $content;
        } else return '';

    }

    function generate_new_element_but($subelement = 0, $group = 'all')
    {
        $content = '<div class="element_container sortable-disabled"></div><a class="add_element admin_feature" data-group="' . $group . '" data-subelement="' . $subelement . '" title="' . __('Přidat element', 'cms_ve') . '" href="#"><span></span>' . __('Přidat element', 'cms_ve') . '</a>';
        return $content;
    }
    
    function edit_button($post_id, $link='') {
        if($this->edit_mode){
            if(!$link) $link=get_edit_post_link($post_id);
            $content='<div class="post_edit_bar"><a target="_blank" class="post_edit" title="'.__('Editovat','cms_ve').'" href="'.$link.'"></a></div>';
            return $content;
        } else return ''; 
    }

    /* Element actions ********
*******************************************************************************  */

    function open_element_setting()
    {
        global $wpdb;

        $element = $this->decode($_POST['code']);

        if (isset($this->elements[$element['type']]['tab_setting'])) {
          
            // back compatibility
            if($element['type']=='bullets') {
                
                if(!isset($element['style']['style'])) {
                    if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='1') || ($element['style']['type']=='image' && $element['style']['style_image']=='1')) {
                        $element['style']['style']='2';
                        $element['style']['size']='40';
                        $element['style']['space']='30';
                        $element['style']['title_font']['font-size']='35';
                    }
                    else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='2') || ($element['style']['type']=='image' && $element['style']['style_image']=='2')) {
                        $element['style']['style']='2';
                        $element['style']['size']='20';
                        $element['style']['space']='15';
                    }
                    else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='3') || ($element['style']['type']=='image' && $element['style']['style_image']=='3')) {
                        $element['style']['style']='1';
                        $element['style']['size']='40';
                        $element['style']['space']='30';
                        $element['style']['title_font']['font-size']='35';
                    }
                    else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='4') || ($element['style']['type']=='image' && $element['style']['style_image']=='4')) {
                        $element['style']['style']='1';
                        $element['style']['size']='20';
                        $element['style']['space']='15';
                    }
                    
                    if($element['style']['custom_image']['image']) $element['style']['type']='own_image';
                    
                    if($element['style']['icon']=='1') {
                        $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/right2.svg", true);
                        $element['style']['bullet_icon']['icon']='right2';
                    }
                    else if($element['style']['icon']=='2') {
                        $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/check1.svg", true);
                        $element['style']['bullet_icon']['icon']='check1';
                    }
                    else if($element['style']['icon']=='3') {
                        $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/right1.svg", true);
                        $element['style']['bullet_icon']['icon']='right1';
                    }
                    else if($element['style']['icon']=='4') {
                        $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/right3.svg", true);
                        $element['style']['bullet_icon']['icon']='right3';
                    }
                }
                
            }
            // back compatibility end
          
          
            echo '<ul class="cms_tabs">';
            $i = 1;
            foreach ($this->elements[$element['type']]['tab_setting'] as $set_tab) {
                $class = '';
                if (isset($set_tab['class'])) $class = $set_tab['class'];
                echo '<li class="cms_tab element_set_groups_tab ' . $class . '"><a href="#select_element_setting_' . $set_tab['id'] . '" data-group="element_set_groups" ' . (($i == 1) ? 'class="active"' : '') . '>' . $set_tab['name'] . '</a>';
                $i++;
            }
            echo '</ul><div class="clear"></div>';
            $i = 1;
            foreach ($this->elements[$element['type']]['tab_setting'] as $set_tab) {
                echo '<div id="select_element_setting_' . $set_tab['id'] . '" class="cms_setting_block_content cms_tab_container element_set_groups_container ' . (($i == 1) ? 'cms_tab_container_active' : '') . '">';
                write_meta($set_tab['setting'], $element, 've_style', 've_style', '', 've');
                echo '</div>';
                $i++;
            }
        } else {
            echo '<div class="cms_setting_block_content">';
            write_meta($this->elements[$element['type']]['setting'], $element, 've_style', 've_style', '', 've');
            echo '</div>';
        }
        ?>
        <input type="hidden" name="element_type" value="<?php echo $element['type'] ?>"/>
        <input type="hidden" id="element_setting_post_id" name="post_id" value="<?php echo $_POST['post_id'] ?>"/>
        <input type="hidden" name="layer" value="<?php echo $_POST['code'] ?>"/>
        <input type="hidden" name="type" value="<?php echo $_POST['type'] ?>"/>
        <?php
        die();
    }

    function open_new_element_setting()
    {
        $this->open_new_setting($this->elements);
        die();
    }

    function open_new_shortcode_setting()
    {
        $this->open_new_setting($this->shortcodes);
        die();
    }

    function open_new_setting($items)
    {
        global $wpdb;
        $style = array();
        if (isset($items[$_POST['type']]['tab_setting'])) {
            foreach ($items[$_POST['type']]['tab_setting'] as $tab_set) {
                foreach ($tab_set['setting'] as $el_style) {
                    $style[$el_style['id']] = (isset($el_style['content'])) ? $el_style['content'] : '';
                }
            }
        } else {
            foreach ($items[$_POST['type']]['setting'] as $el_style) {
                $style[$el_style['id']] = (isset($el_style['content'])) ? $el_style['content'] : '';
            }
        }
        $element = array(
            'type' => $_POST['type'],
            'style' => $style
        );

        if (isset($items[$element['type']]['tab_setting'])) {
            echo '<ul class="cms_tabs">';
            $i = 1;
            foreach ($items[$element['type']]['tab_setting'] as $set_tab) {
                echo '<li class="cms_tab element_set_groups_tab"><a href="#select_element_setting_' . $set_tab['id'] . '" data-group="element_set_groups" ' . (($i == 1) ? 'class="active"' : '') . '>' . $set_tab['name'] . '</a>';
                $i++;
            }
            echo '</ul><div class="clear"></div>';
            $i = 1;
            foreach ($items[$element['type']]['tab_setting'] as $set_tab) {
                echo '<div id="select_element_setting_' . $set_tab['id'] . '" class="cms_setting_block_content cms_tab_container element_set_groups_container ' . (($i == 1) ? 'cms_tab_container_active' : '') . '">';
                write_meta($set_tab['setting'], $element, 've_style', 've_style', '', 've');
                echo '</div>';
                $i++;
            }
        } else {
            echo '<div class="cms_setting_block_content">';
            write_meta($items[$element['type']]['setting'], $element, 've_style', 've_style', '', 've');
            echo '</div>';
        }
        ?>

        <input type="hidden" name="element_type" value="<?php echo $element['type'] ?>"/>
        <input type="hidden" name="post_id" value="<?php echo $_POST['post_id'] ?>"/>
        <?php
    }

    function open_element_select()
    {
        $this->item_selector($this->elements, $this->element_groups);
    }

    function open_shortcode_select()
    {
        $shortcodes = array();
        if (isset($_POST['mw_editor']) && $_POST['mw_editor']) {
            foreach ($this->shortcodes as $key => $shortcode) {
                if (!isset($shortcode['visibility']) || $shortcode['visibility'] != 'blog')
                    $shortcodes[$key] = $shortcode;

            }
        } else $shortcodes = $this->shortcodes;
        $this->item_selector($shortcodes, $this->shortcode_groups, 'shortcode');
    }

// selector for elements and shortcodes
    function item_selector($items, $groups, $type = 'element')
    {
        if(isset($_POST['group']) && $_POST['group']=='slide') {
            $allowed['groups'] = array('basic');
            $allowed['elements'] = array('text','title','image','button');
            
            foreach ($groups as $key => $group) {
                if(!in_array($key ,$allowed['groups'])) unset($groups[$key]);
                else if (count($group['elements'])) {
                    foreach ($group['elements'] as $el_key=>$el_val) {
                        if(!in_array($el_val ,$allowed['elements'])) unset($groups[$key]['elements'][$el_key]);
                    }
                }
            }
            
        }
      
        if (count($groups) > 1) {
            $i = 0;
            echo '<ul class="cms_tabs">';
            foreach ($groups as $key => $group) {
                if (!isset($_POST['subelement']) || !$_POST['subelement'] || (isset($group['subelement']) && $group['subelement'])) {
                    echo '<li class="cms_tab element_groups_tab"><a href="#select_element_container_' . $key . '" data-group="element_groups" class="' . (($i == 0) ? 'active' : '') . '">' . $group['name'] . '</a></li>';
                    $i++;
                }
            }
            echo '</ul><div class="cms_clear"></div>';
        }
        $i = 0;
        foreach ($groups as $key => $group) {
            echo '<div id="select_element_container_' . $key . '" class="select_element_container cms_tab_container element_groups_container ' . (($i == 0) ? 'cms_tab_container_active' : '') . '">';
            if (count($group['elements'])) {
                foreach ($group['elements'] as $el_key) {
                    if (isset($items[$el_key])) {
                        ?>
                        <a class="open_new_<?php echo $type; ?>_setting add_type add_type_<?php echo $el_key; ?>"
                           data-desc="<?php echo (isset($items[$el_key]['description'])) ? $items[$el_key]['description'] : ''; ?>"
                           data-type="<?php echo $el_key; ?>"
                           href="#"><span></span><?php echo $items[$el_key]['name']; ?></a>
                        <?php
                    }
                }
            }
            echo '<div class="cms_clear"></div></div>';

            $i++;
        }
        echo '<div id="select_element_info"><div class="cms_clear"></div></div>';
        die();
    }


// ****** SAVE USED COLORS
    function save_colors($array)
    {
        foreach ($array as $key => $val) {
            if (is_array($val) && $key != 'button') $this->save_colors($val);
            // color
            else if (strpos($key, 'color') !== false) {
                if (!isset($_SESSION['ve_used_colors']) || !$_SESSION['ve_used_colors']) $_SESSION['ve_used_colors'] = array();
                if ($val) {
                    $_SESSION['ve_used_colors'] = array_diff($_SESSION['ve_used_colors'], array($val));
                    array_unshift($_SESSION['ve_used_colors'], $val);
                    $_SESSION['ve_used_colors'] = array_slice($_SESSION['ve_used_colors'], 0, 28);
                }
            } // fonts
            else if ($key === 'font-family') {
                if (!isset($_SESSION['ve_used_fonts']) || !is_array($_SESSION['ve_used_fonts'])) $_SESSION['ve_used_fonts'] = array();
                if ($val) {
                    $_SESSION['ve_used_fonts'] = array_diff($_SESSION['ve_used_fonts'], array($val));
                    array_unshift($_SESSION['ve_used_fonts'], $val);
                    $_SESSION['ve_used_fonts'] = array_slice($_SESSION['ve_used_fonts'], 0, 6);
                }
            } // buttons
            else if ($key === 'button') {
                if (!isset($_SESSION['ve_used_buttons']) || !$_SESSION['ve_used_buttons']) $_SESSION['ve_used_buttons'] = array();
                if ($val) {
                    unset($val['icon']);

                    foreach ($_SESSION['ve_used_buttons'] as $key => $but) {
                        if ($val == $but) unset($_SESSION['ve_used_buttons'][$key]);
                    }

                    array_unshift($_SESSION['ve_used_buttons'], $val);
                    $_SESSION['ve_used_buttons'] = array_slice($_SESSION['ve_used_buttons'], 0, 12);
                }
            }
        }
        if (isset($_SESSION['ve_used_colors'])) update_option('ve_used_colors', $_SESSION['ve_used_colors']);
        if (isset($_SESSION['ve_used_fonts'])) update_option('ve_used_fonts', $_SESSION['ve_used_fonts']);
        if (isset($_SESSION['ve_used_buttons'])) update_option('ve_used_buttons', $_SESSION['ve_used_buttons']);
    }


    function save_element_setting()
    {
        global $cms;

        if (isset($_POST['layer']) && $_POST['layer']) $element = $this->decode($_POST['layer']);
        else $element = array();

        $element['type'] = $_POST['element_type'];
        $element['content'] = (isset($_POST['ve']['content'])) ? $_POST['ve']['content'] : '';
        $element['style'] = (isset($_POST['ve_style'])) ? $_POST['ve_style'] : array();

        $return['newkey'] = (isset($_POST['el_id'])) ? str_replace("element_", "", $_POST['el_id']) : md5(microtime());

        $single = (isset($_POST['single']) && $_POST['single']) ? true : false;

        $return['content'] = $this->generate_element($element, $return['newkey'], $_POST['post_id'], true, '', true, $single);

        $el_fonts = $this->get_element_fonts($element, array());

        if (count($el_fonts) > 0) {
            $fonts = array();
            foreach ($el_fonts as $key => $val) {
                $fonts[] = str_replace(" ", "+", $key) . ':' . implode(",", array_keys($val));
            }

            $return['font'] = implode("|", $fonts);
        } else $return['font'] = "";

        if (isset($_POST['type'])) $return['type'] = $_POST['type'];

        $this->save_colors($element['style']);

        wp_send_json($return);
        //print_r( $return);
        die();
    }

    function save_element_config()
    {
        global $cms;
        $layer = $this->decode($_POST['layer']);

        $layer['config'] = $_POST['ve_config'];
        $return['newkey'] = md5(microtime());
        $return['content'] = $this->generate_element($layer, $return['newkey'], $_POST['post_id'], true, '', true);

        if (isset($_POST['type'])) $return['type'] = $_POST['type'];

        wp_send_json($return);
        die();
    }

    function open_element_config()
    {
        $element_config = array(
            array(
                'id' => 'max_width',
                'title' => __('Maximální šířka elementu (v px)', 'cms_ve'),
                'type' => 'text',
                'content' => '',
            ),
            array(
                'id' => 'margin_top',
                'title' => __('Horní odsazení (v px)', 'cms_ve'),
                'type' => 'text',
                'content' => '0',
            ),
            array(
                'id' => 'margin_bottom',
                'title' => __('Spodní odsazení (v px)', 'cms_ve'),
                'type' => 'text',
                'content' => '20',
            ),
            array(
                'id' => 'delay',
                'title' => __('Zobrazit se zpožděním (x sekund od načtení stránky)', 'cms_ve'),
                'type' => 'text',
                'content' => '',
            ),
            array(
                'id' => 'animate',
                'title' => __('Animace po naskrolování nad element', 'cms_ve'),
                'type' => 'select',
                'content' => '',
                'options' => array(
                    array('name' => __('Bez animace', 'cms_ve'), 'value' => ''),

                    array('name' => __('Odskočení svrchu', 'cms_ve'), 'value' => 'bounceInDown'),
                    array('name' => __('Odskočení zleva', 'cms_ve'), 'value' => 'bounceInLeft'),
                    array('name' => __('Odskočení zprava', 'cms_ve'), 'value' => 'bounceInRight'),
                    array('name' => __('Odskočení zezdola', 'cms_ve'), 'value' => 'bounceInUp'),

                    array('name' => __('Objevení', 'cms_ve'), 'value' => 'fadeIn'),
                    array('name' => __('Objevení svrchu', 'cms_ve'), 'value' => 'fadeInDown'),
                    array('name' => __('Objevení zleva', 'cms_ve'), 'value' => 'fadeInLeft'),
                    array('name' => __('Objevení zprava', 'cms_ve'), 'value' => 'fadeInRight'),
                    array('name' => __('Objevení zezdola', 'cms_ve'), 'value' => 'fadeInUp'),

                    array('name' => __('Otočení X', 'cms_ve'), 'value' => 'flipInX'),
                    array('name' => __('Otočení Y', 'cms_ve'), 'value' => 'flipInY'),

                    array('name' => __('Zoom', 'cms_ve'), 'value' => 'zoomIn'),
                    array('name' => __('Zoom svrchu', 'cms_ve'), 'value' => 'zoomInDown'),
                    array('name' => __('Zoom zleva', 'cms_ve'), 'value' => 'zoomInLeft'),
                    array('name' => __('Zoom zprava', 'cms_ve'), 'value' => 'zoomInRight'),
                    array('name' => __('Zoom zezdola', 'cms_ve'), 'value' => 'zoomInUp'),

                    array('name' => __('Odskočení', 'cms_ve'), 'value' => 'bounce'),
                    array('name' => __('Přiskočení', 'cms_ve'), 'value' => 'bounceIn'),
                    array('name' => __('Pulzování', 'cms_ve'), 'value' => 'pulse'),
                    array('name' => __('Roztažení a smrsknutí', 'cms_ve'), 'value' => 'rubberBand'),
                    array('name' => __('Zatřesení', 'cms_ve'), 'value' => 'shake'),
                    array('name' => __('Zahoupání', 'cms_ve'), 'value' => 'swing'),
                    array('name' => __('Tadá', 'cms_ve'), 'value' => 'tada'),
                    array('name' => __('Rozviklání', 'cms_ve'), 'value' => 'wobble'),
                    array('name' => __('Přijetí', 'cms_ve'), 'value' => 'lightSpeedIn'),

                ),
            ),
            array(
                'id' => 'id',
                'title' => __('ID elementu', 'cms_ve'),
                'type' => 'text',
                'desc' => __('ID nesmí začínat číslovkou', 'cms_ve'),
            ),
            array(
                'id' => 'class',
                'title' => __('Vlastní css třída elementu', 'cms_ve'),
                'type' => 'text'
            ),
            array(
                'id' => 'mobile_visibility',
                'title' => __('Zobrazení na mobilních zařízeních', 'cms_ve'),
                'type' => 'checkbox',
                'label' => __('Skrýt na mobilních zařízeních', 'cms_ve'),
            ),
        );
        $element = $this->decode($_POST['code']);

        echo '<div class="cms_setting_block_content">';
        $i = 0;
        foreach ($element_config as $set) {
            ?>
            <div class="set_form_row">
                <div class="label"><?php echo $set['title']; ?></div>
                <?php call_user_func_array("field_type_" . $set['type'], array($set, isset($element['config'][$set['id']]) ? $element['config'][$set['id']] : null, 've_config', 've_config')); ?>
                <?php if (isset($set['desc'])) echo '<span class="cms_description">' . $set['desc'] . '</span>'; ?>
            </div>
            <?php
            $i++;
        }
        echo '</div>';
        ?>
        <input type="hidden" name="element_type" value="<?php echo $element['type'] ?>"/>
        <input type="hidden" name="layer" value="<?php echo $_POST['code'] ?>"/>
        <input type="hidden" name="post_id" value="<?php echo $_POST['post_id'] ?>"/>
        <input type="hidden" name="type" value="<?php echo $_POST['type'] ?>"/>
        <?php
        die();
    }

    /* Row actions ********
*******************************************************************************  */

    function open_row_setting()
    {
        $row_setting = $this->row_setting;

        $decoded_layer = $this->decode($_POST['code']);
        $row = $decoded_layer['style'];
        
        $tabs=array();
        
        if(isset($decoded_layer['type']) && $decoded_layer['type']=='slider') {
          $tabs=array(
            'slider'=> __('Slider', 'cms_ve'),
            'slider_set'=> __('Nastavení slideru', 'cms_ve'),
            'show'=> __('Viditelnost', 'cms_ve'),
          );
        } else if(isset($decoded_layer['type']) && $decoded_layer['type']=='slide') {
            $tabs=array(
              'slide_set'=> __('Nastavení slidu', 'cms_ve'),
            );
        } else {
          $tabs=array(
            'basic'=> __('Základní nastavení', 'cms_ve'),
            'advance'=> __('Pokročilé nastavení', 'cms_ve'),
            'show'=> __('Viditelnost', 'cms_ve'),
          );
        }

        echo '<ul class="cms_tabs">';
        // row set tabs
        $i=0;
        foreach($tabs as $tab_key=>$tab_val) {
            echo '<li class="cms_tab row_set_groups_tab"><a href="#select_element_container_'.$tab_key.'" data-group="row_set_groups" '.($i==0?'class="active"':'').'>'.$tab_val.'</a></li>';
            $i++;
        }
        echo '</ul><div class="clear"></div>';
        
        // row set setting
        $i = 0;
        foreach ($tabs as $tab_key=>$tab_val) {
            echo '<div id="select_element_container_' . $tab_key . '" class="cms_setting_block_content cms_tab_container row_set_groups_container ' . (($i == 0) ? 'cms_tab_container_active' : '') . '">';

            write_meta($row_setting[$tab_key], $row, 've_style', 've_style');

            echo '<div class="cms_clear"></div></div>';
            $i++;
        }
        ?>
        <input type="hidden" name="layer" value="<?php echo $_POST['code'] ?>"/>
        <input type="hidden" name="row_id" value="<?php echo $_POST['row_id'] ?>"/>
        <?php
        die();
    }

    function save_row_setting()
    {
        global $cms;

        $layer = $this->decode($_POST['layer']);
        $layer['style'] = $_POST['ve_style'];
        
        $return['reload_row']=false;
        
        if(isset($layer['type']) && $layer['type']=='slider') 
          $return['reload_row']=true;
          
        if(isset($layer['type']) && $layer['type']=='slide') {
            $layer['style']['height_setting']['full_height']=1;
            $layer['style']['height_setting']['centered_content']=1;
        }
        
        $return['row_class'] = isset($layer['style']['type']) ? 'row_' . $layer['style']['type'] : 'row_basic';
        
        $return['min_height'] = isset($layer['style']['min-height']) ? $layer['style']['min-height'] : 'auto';
        
        $return['scroll_arrow']='';
        if (isset($layer['style']['height_setting']['arrow'])) $return['scroll_arrow'] = $this->generate_next_to_scroll_link();
        
        $return['code'] = $this->code($layer);
        //$return['style']=$this->print_styles(array('color'=>$layer['style']['link_color']),'#'.$_POST['row_id'].' a','online');
        $styles = array(
            array(
                'styles' => $layer['style'],
                'element' => '#' . $_POST['row_id'],
            ),
            array(
                'styles' => array('color' => $layer['style']['link_color']),
                'element' => '#' . $_POST['row_id'] . ' a',
            ),
            array(
                'styles' => array('color' => $layer['style']['font']['color']),
                'element' => '#' . $_POST['row_id'] . ' h2,#' . $_POST['row_id'] . ' h1,#' . $_POST['row_id'] . ' h3,#' . $_POST['row_id'] . ' h4,#' . $_POST['row_id'] . ' h5,#' . $_POST['row_id'] . ' h6,#' . $_POST['row_id'] . ' .title_element_container,#' . $_POST['row_id'] . ' .form_container_title',
            )
        );
        if ($layer['style']['padding_left']['size'] != '') {
            $styles[] = array(
                'styles' => array('padding-left' => $layer['style']['padding_left']['size'] . $layer['style']['padding_left']['unit']),
                'element' => '#' . $_POST['row_id'] . ' .row_fix_width',
            );
        }
        if ($layer['style']['padding_right']['size'] != '') {
            $styles[] = array(
                'styles' => array('padding-right' => $layer['style']['padding_right']['size'] . $layer['style']['padding_right']['unit']),
                'element' => '#' . $_POST['row_id'] . ' .row_fix_width',
            );
        }
        if (isset($layer['style']['margin_t']) && $layer['style']['margin_t']['size'] != '') {
            $styles[] = array(
                'styles' => array('margin_top' => $layer['style']['margin_t']['size']),
                'element' => '#' . $_POST['row_id'],
            );
        }
        if (isset($layer['style']['margin_b']) && $layer['style']['margin_b']['size'] != '') {
            $styles[] = array(
                'styles' => array('margin_bottom' => $layer['style']['margin_b']['size']),
                'element' => '#' . $_POST['row_id'],
            );
        }
        if (isset($layer['style']['height_setting']) && isset($layer['style']['height_setting']['arrow'])) {
            $styles[] = array(
                'styles' => array('fill' => $layer['style']['height_setting']['arrow_color']),
                'element' => '#' . $_POST['row_id']. ' .mw_scroll_tonext_icon svg path',
            );
        }

        $return['background'] = '';
        $return['background_type'] = '';
        // background slider
        if (isset($layer['style']['background_setting']) && $layer['style']['background_setting'] == 'slider') {
            $return['background'] = $this->generate_slider_background($layer['style']['background_slides'], $layer['style']['background_delay'], $layer['style']['background_speed'], $layer['style']['background_color']['color1'], 'miocarousel_slider_' . $_POST['row_id']);
            $return['background'] .= '';
            $styles[0]['styles']['background_color'] = array();
            $styles[0]['styles']['background_image'] = array();
        }
        
        if (!(isset($layer['style']['background_setting']) && $layer['style']['background_setting']!='image') && isset($layer['style']['background_image']['image']) && $layer['style']['background_image']['image'] && isset($layer['style']['background_image']['cover']) && isset($layer['style']['background_image']['color_filter'])) {
            $styles[] = array(
                'styles' => array('background-color' => $layer['style']['background_image']['overlay_color'],'opacity'=>$layer['style']['background_image']['overlay_transparency']),
                'element' => '#' . $_POST['row_id'].':before',
            );
            $return['row_class'].=' ve_colored_background';
        }

        $return['style'] = $this->print_styles_array($styles);
        
        if (isset($layer['style']['height_setting']['full_height']) && isset($layer['style']['height_setting']['noheader'])) $return['row_class'] .= ' row_window_height_noheader';
        else if (isset($layer['style']['height_setting']['full_height'])) $return['row_class'] .= ' row_window_height';
        if (isset($layer['style']['height_setting']['centered_content'])) $return['row_class'] .= ' row_centered_content';

        // row fonts
        $return_fonts=array();
        $fonts=array();
        $font = $layer['style']['font']['font-family'];
        if ($font && isset($cms->google_fonts[$font])) {
            $fonts[$font] = $cms->google_fonts[$font]['weights'];
        } 
        if(isset($layer['type']) && $layer['type']=='slider' && isset($layer['style']['slides'])) {
            foreach($layer['style']['slides'] as $key=>$val) {
                $fonts = $this->merge_fonts($fonts, $this->get_weditor_fonts($val['slider_content']));
            }
        }
        foreach ($fonts as $key => $val) {
            // add bold to text
            if ((isset($val['400']) || isset($val['300'])) && !isset($val['700']) && isset($cms->google_fonts[$key]['weights']['700'])) $val['700'] = '700';
            // print google font link
            if($key) $return_fonts[]=str_replace(" ", "+", $key) . ':' . implode(',', $val);
        }
        $return['font']=implode('|', $return_fonts);

        $this->save_colors($layer['style']);
        
        if($return['reload_row']) {
          $return['row_content']=$this->generate_row($layer, str_replace('row_','',$_POST['row_id']), true, '', true);
        }
        
        wp_send_json($return);

        die();
    }

    function open_row_select()
    {
         ?>
         <div class="ve_div_table">
                <div class="ve_left_setting_menu ve_select_row_tags_menu">
                    <ul>
                        <li><a class="mw_select_tag active" data-container="ve_select_row_container" data-tag="all" href="#"><?php echo __('Všechny','cms_ve'); ?></a></li>
                        <?php
                        foreach($this->rows as $row) {
                            if(!is_array($val)) echo '<li><a class="mw_select_tag" data-container="ve_select_row_container" data-tag="'.$row['id'].'" href="#">'.$row['tab'].'</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="ve_right_content ve_select_row_container">
                    <?php
                    foreach ($this->rows as $row) {

                        $type=(isset($row['type']))?$row['type']:'';
                        
                        foreach ($row['layouts'] as $key => $lay) {
                            $class='mw_tag_item_'.$row['id'];
                            if($type=='template') {
                            
                                $class.=' mw_tag_item_all ve_row_template_item';
                                $lang=get_locale();
                                if($lang!='en_US') $lang="";
                                else $lang.='/';
                                $thumb = '<img src="'.VS_DIR.'templates/rows/'.$lang.$lay['content'].'.jpg" alt="">';
                                
                            } else if($type=='custom') {
                            
                                $class.=' ve_nodisp ve_row_template_item';
                                
                                $thumbnail = get_the_post_thumbnail( $lay['content'],'mio_columns_c3' );
            
                                $thumb='<div class="ve_row_template_item_thumb">';
                                if($thumbnail) $thumb.=$thumbnail;
                                else $thumb.='<div class="ve_empty_image_container"><span></span></div>';
                                
                                $thumb.='</div>';
                                
                                $thumb .= '<h2>'.$lay['title']."</h2>";
                                $thumb .= '<div class="cms_clear"></div>';
                                                    
                            } else {
                                $thumb = '<span></span>' . $lay['title']; 
                                $class.=' ve_nodisp add_type add_type_' . $row['id'] . $key;
                            }
                            if(isset($lay['type']) && $lay['type']='title') {
                                echo '<div class="cms_clear"></div>'
                                  .'<h3 class="mw_tag_item ve_nodisp mw_tag_item_'.$row['id']. '">'.$lay['title'].'</h3>';
                            }
                            else echo '<a class="add_new_row mw_tag_item ' .$class. '" title="'.$lay['title'].'" href="#" data-content="' . $lay['content'] . '" data-type="' . $type. '">'.$thumb.'</a>';
                        }
                        echo '<div class="cms_clear"></div>';
            
                    }
                    ?>
                </div>
            </div>
            <div class="cms_clear"></div>
    
    
        <?php
        die();
    }

    function add_new_row()
    {
        if($_POST['rowtype']=='template') {
        
            require_once(__DIR__.'/templates/rows/'.$_POST['content'].'.php');
            $newrow=$content;
            
        } else if($_POST['rowtype']=='custom') {
        
            if($row_temp=get_post( $_POST['content'] )){
                $newrow=$this->decode($row_temp->post_content);
            }
            else $newrow=array();
            
        } else {
        
            $cols = explode("-", $_POST['content']);
            foreach ($cols as $col) {
                $newcols[] = array(
                    'type' => 'col-' . $col,
                    'class' => '',
                    'content' => array()
                );
            }
            $newrow = array(
                'class' => '',
                'style' => array(
                    'background_color' => array('color1' => '#fff', 'color2' => '', 'transparency' => '100'),
                    'link_color' => '',
                    'font' => array(
                        'font-size' => '',
                        'font-family' => '',
                        'weight' => '',
                        'color' => '',
                    ),
                ),
                'content' => $newcols
            );
            
        }   
        
        $return['id']=md5(microtime()); 
        $return['row_type']=isset($newrow['type'])? $newrow['type'] : 'row';
        
        if(isset($newrow['type']) && $newrow['type']=='slider') {
        
            $existing_slides = get_posts( array('post_type' => 'mw_slider','posts_per_page'=> -1) );

            foreach($newrow['style']['slides'] as $key => $slide) {
              
                $s_title = $this->new_nodup_name($slide['slider_content']['title'],$existing_slides);
              
                $new_post = array(
                      'post_title' => $s_title,
                      'post_status' => 'publish',
                      'post_type'=>'mw_slider',
                      'post_author' => 1, 
                );
                $newslider_id=$this->save_new_window_post($new_post, $slide['slider_content']['theme'],$this->code($slide['slider_content']['content']),'mw_slider'); 
                $newrow['style']['slides'][$key]['slider_content']=$newslider_id;
            }
        }
        
        $return['row']=$this->generate_row($newrow, $return['id'], true, '', true);
        
        wp_send_json($return);
        
        die();
    }
    
    // function for finding not duplicate names 
    function new_nodup_name($name, $existing_pages, $after='', $i=1) {
        foreach($existing_pages as $p) {
            if($p->post_title==$name.$after) {
                $i++;
                $after=' ('.$i.')';
                return $name=$this->new_nodup_name($name,$existing_pages, $after, $i);
            }
        }
        return $name.$after;
    }
    
    function create_row_set($template_set) { /*
        $this->template_visual_setting=array(
            'dark_bg'=>'#158ebf',
            'text_color'=>'#cad6db'
        );    */
        return wp_parse_args( $this->template_visual_setting, $template_set  );
    }

    function copy_row()
    {
        $content = array();
        if (isset($_POST['row'])) {

            $row_decoded = $this->decode($_POST['row']);

            $content = $row_decoded;
            if ($row_decoded['content']) {
                foreach ($row_decoded['content'] as $ckey => $col) {
                    $content['content'][$ckey]['content'] = array();
                    if (isset($_POST['element'][$ckey])) {
                        $i = 0;
                        foreach ($_POST['element'][$ckey] as $element) {

                            if ($element) {
                                $content['content'][$ckey]['content'][$i] = $this->decode($element);
                                // if subelement
                                if ($content['content'][$ckey]['content'][$i]['type'] == 'twocols' || $content['content'][$ckey]['content'][$i]['type'] == 'box') {
                                    $content['content'][$ckey]['content'][$i]['content'] = array();
                                    //first col
                                    if (isset($_POST['subelement'][$ckey][$i][0]) && is_array($_POST['subelement'][$ckey][$i][0])) {
                                        foreach ($_POST['subelement'][$ckey][$i][0] as $subelement) {
                                            if ($subelement) $content['content'][$ckey]['content'][$i]['content'][0][] = $this->decode($subelement);
                                        }
                                    }
                                    //second col
                                    if (isset($_POST['subelement'][$ckey][$i][1]) && is_array($_POST['subelement'][$ckey][$i][1])) {
                                        foreach ($_POST['subelement'][$ckey][$i][1] as $subelement) {
                                            if ($subelement) $content['content'][$ckey]['content'][$i]['content'][1][] = $this->decode($subelement);
                                        }
                                    }
                                }
                            }
                            $i++;
                        }

                    }
                }
            }

        }
        $_SESSION['ve_copy_row'] = $this->code($content);

        echo '<div style="text-align: center; padding: 30px;">' . __('Řádek byl zkopírován do paměti, nyní jej můžete vložit na jakoukoli stránku.', 'cms_ve') . '</div>';
        die();
    }

    function paste_row()
    {
        $row = $this->decode($_SESSION['ve_copy_row']);
        $return['content'] = $this->generate_row($row, '', true, '', true);
        $row_fonts = $this->get_row_fonts($row, array());
        if (count($row_fonts) > 0) {
            $fonts = array();
            foreach ($row_fonts as $key => $val) {
                $fonts[] = str_replace(" ", "+", $key) . ':' . implode(",", array_keys($val));
            }

            $return['font'] = implode("|", $fonts);
        } else $return['font'] = "";

        wp_send_json($return);
        die();
    }


    /* Menu edit
************************************************************************* */

//add editor on menu
    function menu_filter($nav_menu, $args = array())
    {          
            
        if(isset($args->menu) && !isset($args->menu->term_id)) {
            $nav_menu = '<div class="menu_editbar_container">
              <div class="content_element_editbar">
                  <a class="ve_edit_menu" data-modul="' . $this->modul_type . '" data-menuid="' . $args->menu . '" href="#" title="' . __('Editovat menu', 'cms_ve') . '"></a>
              </div>
              ' . $nav_menu . '
              <div class="cms_clear"></div>
            </div>';
        }
        return $nav_menu;
    }

//open menu setting
    function open_menu_setting()
    {

        if ($_POST['location'] == 'site_header_nav') $location = 'header';
        else $location = 'footer';

        $page_set = get_post_meta($_POST['post_id'], 've_' . $location, true);
        if (!isset($page_set['show']) || $page_set['show'] != 'page')
            echo '<div class="cms_info_box">' . __('Tato stránka používá globální', 'cms_ve') . ' ' . (($location == 'header') ? __('hlavičku', 'cms_ve') : __('patičku', 'cms_ve')) . '. ' . __('Změna menu se proto projeví na všech stránkách s globální hlavičkou.', 'cms_ve') . '</div>';

        echo '<div class="ve_menu_select_container">' . __('Menu', 'cms_ve') . ': ';
        $menus = get_terms('nav_menu', array('hide_empty' => false));

        echo '<select name="menu_id" class="cms_text_input" id="ve_menu_selector">';
        echo '<option value="" ' . (($_POST['menu_id'] == "") ? ' selected="selected"' : '') . '>' . __('Bez menu', 'cms_ve') . '</option>';
        if (count($menus)) {
            foreach ($menus as $menu) {
                echo '<option value="' . $menu->term_id . '" ' . (($_POST['menu_id'] == $menu->term_id) ? ' selected="selected"' : '') . '>' . $menu->name . '</option>';
            }
        }
        echo '</select>';

        echo '<button id="ve_add_new_menu" class="cms_button_secondary">' . __('Vytvořit nové menu', 'cms_ve') . '</button></div>';

        echo '<div id="add_new_menu_container" class="add_new_menu_container">
      <input id="add_new_menu_name" type="text" class="cms_text_input" name="" placeholder="' . __('Jméno nového menu', 'cms_ve') . '" />
      <button id="ve_save_new_menu" class="cms_button_secondary">' . __('Vytvořit menu', 'cms_ve') . '</button>
      <button id="ve_storno_new_menu" class="cms_button_secondary">' . __('Storno', 'cms_ve') . '</button>
  </div>';

        echo '<div id="ve_menu_selected_menu_container">';
        if ($_POST['menu_id']) echo $this->ve_generate_edit_menu($_POST['menu_id']);
        echo '</div>
  <input type="hidden" name="post_id" value="' . $_POST['post_id'] . '" />
  <input type="hidden" name="location" value="' . $location . '" />
  <input type="hidden" name="modul" value="' . $_POST['modul'] . '" />';
        die();
    }

//open single menu setting
    function open_single_menu_setting()
    {
        if (!$_POST['menu_id']) {
            echo '<div id="add_new_single_menu_container" class="add_new_menu_container">
          <input id="add_new_menu_name" type="text" class="cms_text_input" name="add_new_menu" placeholder="' . __('Jméno nového menu', 'cms_ve') . '" />
          <button id="ve_save_new_menu" class="cms_button_secondary">' . __('Vytvořit menu', 'cms_ve') . '</button>
      </div>
      <input type="hidden" id="single_menu_action" name="single_menu_action" value="create" />';
        } else echo '<input type="hidden" id="single_menu_action" name="single_menu_action" value="edit" />';
        echo '<div id="ve_menu_selected_menu_container">';
        if ($_POST['menu_id']) echo $this->ve_generate_edit_menu($_POST['menu_id']);
        echo '</div>';
        die();
    }

// change menu
    function change_menu_setting()
    {
        echo $this->ve_generate_edit_menu($_POST['menu_id']);
        die();
    }

// generate list of menu items
    function ve_generate_edit_menu($menu_id)
    {
        $menu = new \Mio\VisualEditor\Models\NavMenu($menu_id);
        $menu_items = $menu->getNestedMenuItems();
        $pages = get_pages(array('post_status' => 'publish,draft'));

        $menu_list = '<input type="hidden" value="' . $menu_id . '" name="menu_id">';

        $menu_list .= '<div class="ve_menu_note">' . __('Zanoření menu můžete ovládat posuntím položek vlevo nebo vpravo. Lze vytvářet menu pouze do třetí úrovně zanoření.', 'cms_ve') . '</div>';

        $menu_list .= '<ol class="ve_nestedsortable">';

        foreach ($menu_items as $item) {

            $menu_list .= '<li class="ve_nestedsortable__item ve_pack_setting_container">';
            $menu_list .= $this->print_edit_menu_item($item, $pages);
            $menu_list .= $this->ve_generate_edit_menu_child_pages($item->children, $pages);
            $menu_list .= '</li>';

        };

        $menu_list .= '</ol>';
        $menu_list .= '<button id="ve_add_menu_item" class="cms_button_secondary" data-id="0">' . __('Přidat položku menu', 'cms_ve') . '</button>';

        return $menu_list;
    }

    /**
     * Recursive function for infinite deep levels of menu children
     *
     * @param $menu_items array Array of menu items with nested children
     * @param $pages array cache for existing WP pages
     *
     * @return string
     */
    function ve_generate_edit_menu_child_pages($menu_items, $pages)
    {
        $return = '';

        if (!empty($menu_items)) {
            $return .= '<ol>';
            foreach ($menu_items as $item) {
                $return .= '<li class="ve_nestedsortable__item ve_pack_setting_container">';
                $return .= $this->print_edit_menu_item($item, $pages);
                $return .= $this->ve_generate_edit_menu_child_pages($item->children, $pages);
                $return .= '</li>';
            }
            $return .= '</ol>';
        }

        return $return;
    }

// create new menu item
    function ve_generate_edit_menu_item()
    {
        if (!current_user_can('edit_posts')) wp_die();

        $pages = get_pages(array('post_status' => 'publish,draft'));
        $menu_item = new stdClass();
        $menu_item->ID = 'new_' . $_POST['id'];
        $menu_item->title = '';
        $menu_item->url = '';
        $menu_item->object_id = '0';
        $menu_item->db_id = '0';
        $menu_item->menu_item_parent = '0';

        echo $this->print_edit_menu_item($menu_item, $pages, true);
        die();
    }

// generate menu item
    function print_edit_menu_item($menu_item, $pages, $new = false)
    {
        $url = (get_permalink($menu_item->object_id) != $menu_item->url) ? $menu_item->url : 'http://';
        $return = '<div class="ve_nestedsortable__item__wrap">
                <div class="ve_pack_setting_container_head">
                  <a href="#" class="ve_sortable_handler"></a>
                  ' . (($menu_item->title) ? $menu_item->title : __('Nová položka', 'cms_ve')) . '
                  <a href="#" class="ve_pack_setting_edit" title="' . __('Editovat položku menu', 'cms_ve') . '"></a>
                  <a href="#" class="ve_pack_setting_delete" title="' . __('Smazat položku menu', 'cms_ve') . '"></a>
              </div>
              <div class="ve_pack_setting_container_body ' . (($new) ? 've_pack_setting_container_body_open' : '') . '">
              <div class="ve_pack_setting_set">';
        if ($new) {
            $return .= '<div class="ve_half_set">
                      <div class="label">' . __('Stránka', 'cms_ve') . ' (<label for="edit-menu-item-custom-' . $menu_item->ID . '"><input class="edit-menu-item-custom" id="edit-menu-item-custom-' . $menu_item->ID . '" data-id="' . $menu_item->ID . '" type="checkbox" name="custom_url[' . $menu_item->ID . ']" value="1">' . __('Zadat vlastní URL', 'cms_ve') . ')</div>
                      ' . $this->select_page($pages, $menu_item->object_id, 'menu_item[' . $menu_item->ID . '][menu-item-object-id]', 'edit-menu-item-page-' . $menu_item->ID, 'cms_text_input', '', true) . '
                      <input id="edit-menu-item-url-' . $menu_item->ID . '" class="cms_text_input cms_nodisp" type="text" value="' . $url . '" name="menu_item[' . $menu_item->ID . '][menu-item-url]">
                  </div>
                  <div class="ve_half_set ve_half_set_r">
                      <label for="edit-menu-item-title-' . $menu_item->ID . '">
                      <div class="label">' . __('Text odkazu', 'cms_ve') . '</div>
                      <input id="edit-menu-item-title-' . $menu_item->ID . '" class="cms_text_input" type="text" value="' . $menu_item->title . '" name="menu_item[' . $menu_item->ID . '][menu-item-title]">
                      </label>
                  </div>
                  <div class="cms_clear"></div>
                  <input type="hidden" value="1" name="menu_item[' . $menu_item->ID . '][new]">';
        } else {
            $return .= '<label for="edit-menu-item-title-' . $menu_item->ID . '">
            <div class="label">' . __('Text odkazu', 'cms_ve') . '</div>
            <input id="edit-menu-item-title-' . $menu_item->ID . '" class="cms_text_input" type="text" value="' . $menu_item->title . '" name="menu_item[' . $menu_item->ID . '][menu-item-title]">
            </label>
            <input type="hidden" value="' . $menu_item->object_id . '" name="menu_item[' . $menu_item->ID . '][menu-item-object-id]">
            <input type="hidden" value="' . implode(" ", $menu_item->classes) . '" name="menu_item[' . $menu_item->ID . '][menu-item-classes]">
            <input type="hidden" value="' . $menu_item->attr_title . '" name="menu_item[' . $menu_item->ID . '][menu-item-attr-title]">

            <input class="menu-item-data-object" type="hidden" value="' . $menu_item->object . '" name="menu_item[' . $menu_item->ID . '][menu-item-object]">
            <input class="menu-item-data-type" type="hidden" value="' . $menu_item->type . '" name="menu_item[' . $menu_item->ID . '][menu-item-type]">';
            // link
            if ($menu_item->type == 'custom') {
                $return .= '</div><div class="ve_pack_setting_set"><label for="edit-menu-item-url-' . $menu_item->ID . '">
                <div class="label">' . __('Odkaz', 'cms_ve') . '</div>
                <input id="edit-menu-item-url-' . $menu_item->ID . '" class="cms_text_input" type="text" value="' . $url . '" name="menu_item[' . $menu_item->ID . '][menu-item-url]">
                </label>';
            } //page
            else {

                $return .= '<div class="ve_menu_item_info">' . __('Odkaz', 'cms_ve') . ': <a target="_blank" href="' . get_permalink($menu_item->object_id) . '">' . get_the_title($menu_item->object_id) . '</a></div>
                <input type="hidden" value="' . $url . '" name="menu_item[' . $menu_item->ID . '][menu-item-url]">';
            }
        }
        $return .= '</div><div class="ve_pack_setting_set">
                  <label for="edit-menu-item-target-' . $menu_item->ID . '">
                  <input id="edit-menu-item-target-' . $menu_item->ID . '" type="checkbox" name="menu_item[' . $menu_item->ID . '][menu-item-target]" ' . ((isset($menu_item->target) && $menu_item->target == "_blank") ? 'checked="checked"' : '') . ' value="_blank">
                  ' . __('Otevřít odkaz v novém okně/záložce', 'cms_ve') . '
                  </label>
              </div>

              <input class="menu-item-data-db-id" type="hidden" value="' . $menu_item->ID . '" name="menu_item[' . $menu_item->ID . '][menu-item-db-id]">
              <input class="menu-item-data-parent-id" type="hidden" value="' . $menu_item->menu_item_parent . '" name="menu_item[' . $menu_item->ID . '][menu-item-parent-id]">
            </div>
          </div>';

        return $return;
    }

// create new menu
    function create_new_menu()
    {
        $menu_exists = wp_get_nav_menu_object($_POST['name']);

        if (!$menu_exists) {
            $menu_id = wp_create_nav_menu($_POST['name']);
            echo $this->ve_generate_edit_menu($menu_id);
        } else echo 'false';
        die();
    }

// save menu
    function save_menu_setting()
    {
        //print_r($_POST);
        $edit = false;
        if ($_POST['menu_id']) {
            $menu_items = wp_get_nav_menu_items($_POST['menu_id']);
            foreach ((array)$menu_items as $menu_item) {
                if (!isset($_POST['menu_item'][$menu_item->ID])) wp_delete_post($menu_item->ID, true);
            }
            $i = 1;
            if (isset($_POST['menu_item']) && is_array($_POST['menu_item'])) {

                $new_items_binding = array();

                foreach ($_POST['menu_item'] as $key => $item) {
                    $item['menu-item-position'] = $i;

                    //If this is child of new item, we have to get new item ID from data, we have created
                    if (substr($item['menu-item-parent-id'], 0, 4) === 'new_') {
                        $item['menu-item-parent-id'] = $new_items_binding[$item['menu-item-parent-id']];
                    }

                    if (isset($item['new'])) {
                        //new menu item
                        if (isset($_POST['custom_url'][$key])) {
                            $item['menu-item-type'] = 'custom';
                            $item['menu-item-object'] = 'custom';
                            $item['menu-item-object-id'] = '0';
                            if ($item['menu-item-url'] == '') $item['menu-item-url'] = '#';
                            if ($item['menu-item-title'] == '') $item['menu-item-title'] = __('Nová položka', 'cms_ve');
                        } else {
                            $item['menu-item-type'] = 'post_type';
                            $item['menu-item-object'] = 'page';
                        }
                        $item['menu-item-status'] = 'publish';

                        $new_item_id = wp_update_nav_menu_item($_POST['menu_id'], 0, $item);

                        //Store new item WP ID for possible children
                        $new_items_binding[$item['menu-item-db-id']] = $new_item_id;

                    } else {
                        //existing menu item
                        wp_update_nav_menu_item($_POST['menu_id'], $item['menu-item-db-id'], $item);
                    }

                    $i++;

                }
                $edit = true;
            }
        } else $edit = true;

        $menu = ($_POST['menu_id']) ? $_POST['menu_id'] : "";

        if ($edit && isset($_POST['modul'])) {
            //save menu to right place - global x local, web x blog x member..., header x footer
            $page_set = get_post_meta($_POST['post_id'], 've_' . $_POST['location'], true);

            if (isset($page_set['show']) && $page_set['show'] == 'page') {
                $page_set['menu'] = $menu;
                update_post_meta($_POST['post_id'], 've_' . $_POST['location'], $page_set);
            } else {
                if ($_POST['modul'] == 'web') $mod = 've';
                else $mod = $_POST['modul'];
                
                $global_set = get_option($mod . '_' . $_POST['location']);
                
                if($global_set['show'] == 'global') {
                    $mod = 've';
                    $global_set = get_option($mod . '_' . $_POST['location']);
                }
                if($mod=='member') {
                    $page_member=get_post_meta($_POST['post_id'], 'page_member', true);                 
                    $global_set['members'][$page_member['member_section']['section']]['menu']=$menu; 
                } else $global_set['menu'] = $menu;
                update_option($mod . '_' . $_POST['location'], $global_set);
            }
        }
        //print new menu
        if (isset($_POST['location'])) {  
            $this->modul_type=$_POST['modul'];
            if (!$edit) $menu = '';
            if ($_POST['location'] == "header") $this->header_menu($menu);
            else $this->footer_menu($menu);
        } else wp_send_json(array('title' => $_POST['add_new_menu'], 'id' => $menu));

        die();
    } 

    function header_menu($menu)
    {
        if (isset($menu) && wp_get_nav_menu_items($menu)) { ?>
            <a href="#" id="mobile_nav">
                <span class="mobile_nav_menu"><?php echo file_get_contents(get_template_directory() ."/modules/visualeditor/images/mobile_menu.svg", true); ?></span> 
                <span class="mobile_nav_close"><?php echo file_get_contents(get_template_directory() ."/modules/visualeditor/images/mobile_menu_close.svg", true); ?></span>    
            </a>
            <nav>
                <div id="site_header_nav"><?php wp_nav_menu(array('menu' => $menu, 'after'=>'<span></span>')); ?>
                    <div class="cms_clear"></div>
                </div>
            </nav>
            <?php
        } else if ($this->edit_mode) {
            ?>
            <div class="admin_feature add_menu_container"><a class="ve_add_menu" data-location="site_header_nav"
                                                             data-modul="<?php echo $this->modul_type; ?>"
                                                             href="#"><?php echo __('Přidat menu', 'cms_ve'); ?></a>
            </div>
            <?php
        }
    }

    function footer_menu($menu)
    {
        if (isset($menu) && $menu!='' && wp_get_nav_menu_object($menu) && wp_get_nav_menu_object($menu)->count) { ?>
            <nav>
                <div id="site_footer_nav">
                    <?php
                    wp_nav_menu(array('menu' => $menu, 'depth' => 1));
                    ?>
                    <div class="cms_clear"></div>
                </div>
            </nav>
            <?php
        } else if ($this->edit_mode) {
            ?>
            <div class="admin_feature add_menu_container"><a class="ve_add_menu" data-location="footer"
                                                             data-modul="<?php echo $this->modul_type; ?>"
                                                             href="#"><?php echo __('Přidat menu', 'cms_ve'); ?></a>
            </div>
            <?php
        }
    }


    /* Save page ********
*******************************************************************************  */
    function create_post_layer()
    {
        $content = array();
        if (isset($_POST['row'])) {
            foreach ($_POST['row'] as $rkey => $row) {

                $row_decoded = $this->decode($row);

                $content[$rkey] = $row_decoded;
                if ($row_decoded['content']) {
                    foreach ($row_decoded['content'] as $ckey => $col) {
                        $content[$rkey]['content'][$ckey]['content'] = array();
                        if (isset($_POST['element'][$rkey][$ckey])) {
                            $i = 0;
                            foreach ($_POST['element'][$rkey][$ckey] as $element) {

                                if ($element) {
                                    $content[$rkey]['content'][$ckey]['content'][$i] = $this->decode($element);
                                    // if subelement
                                    if ($content[$rkey]['content'][$ckey]['content'][$i]['type'] == 'twocols' || $content[$rkey]['content'][$ckey]['content'][$i]['type'] == 'box') {
                                        $content[$rkey]['content'][$ckey]['content'][$i]['content'] = array();
                                        //first col
                                        if (isset($_POST['subelement'][$rkey][$ckey][$i][0]) && is_array($_POST['subelement'][$rkey][$ckey][$i][0])) {
                                            foreach ($_POST['subelement'][$rkey][$ckey][$i][0] as $subelement) {
                                                if ($subelement) $content[$rkey]['content'][$ckey]['content'][$i]['content'][0][] = $this->decode($subelement);
                                            }
                                        }
                                        //second col
                                        if (isset($_POST['subelement'][$rkey][$ckey][$i][1]) && is_array($_POST['subelement'][$rkey][$ckey][$i][1])) {
                                            foreach ($_POST['subelement'][$rkey][$ckey][$i][1] as $subelement) {
                                                if ($subelement) $content[$rkey]['content'][$ckey]['content'][$i]['content'][1][] = $this->decode($subelement);
                                            }
                                        }
                                    }
                                }
                                $i++;
                            }

                        }
                    }
                }
            }
        }
        return $content;
    }

    function save_page() {
        global $wpdb, $cms;
    
        // create layer
        $layer=$this->create_post_layer(); 
        // save single_elements
        if(isset($_POST['single_elements']) && is_array($_POST['single_elements'])) {
            update_post_meta($_POST['post_id'],'single_elements', $_POST['single_elements']);
        }
        
        $fonts=$this->get_layer_fonts($layer,array());
    
        update_post_meta($_POST['post_id'],'ve_google_fonts', $fonts);
        
        $layer=$this->code($layer); 
        
        if($_POST['page_type']=='page') {
            wp_update_post( array(
                'ID' => $_POST['post_id'],
                'post_status' => $_POST['status'],  
                'post_content' => $layer,  
            ));
        }
        
        $result=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ve_posts_layer WHERE vpl_type='".$_POST['page_type']."' AND vpl_post_id=".$_POST['post_id']);
        if($wpdb->num_rows) $wpdb->update( $wpdb->prefix . "ve_posts_layer", array( 'vpl_layer' => $layer ), array( 'vpl_post_id' => $_POST['post_id'],'vpl_type' => $_POST['page_type'] ));
        else $wpdb->insert( $wpdb->prefix . "ve_posts_layer",array( 'vpl_post_id' =>$_POST['post_id'], 'vpl_type' => $_POST['page_type'], 'vpl_layer' => $layer)); 
        
        die();
    }

    /* load page / web setting ********
*******************************************************************************  */

    function create_setting()
    {
        if ($this->post_id) {
            $this->template = get_post_meta($this->post_id, 've_page_template', true);

            if (!$this->template) {
                if ($this->page_type == 'cms_popup') $this->template = array('type' => 'cms_popup', 'directory' => 'popups/1/');
                else $this->template = array('type' => 'page', 'directory' => 'page/1/');
                add_post_meta($this->post_id, 've_page_template', $this->template);
            }

            $this->template_config = get_post_meta($this->post_id, 've_page_config', true);
            $this->template_setting = get_post_meta($this->post_id, 've_page_setting', true);
        } else {
            $this->template_config = array();
            $this->template_setting = array();
        }
        
        // generated styles
        /*
        if($this->post_id) {
            $page=get_post($this->post_id);
            
            $generated_css=get_post_meta($this->post_id,'mw_generated_css', true);
            $generated_css_time=get_post_meta($this->post_id,'mw_generated_css_time', true);
            $this->last_modified=strtotime($page->post_modified);

            if($generated_css && $this->last_modified==$generated_css_time) 
                $this->generate_css=true;

        }
        */
        
        // Visual setting
        $this->page_setting = get_option('ve_appearance');
        // Header setting
        $this->header_setting = get_option('ve_header');
        $this->h_menu = (isset($this->header_setting['menu'])) ? $this->header_setting['menu'] : '';
        // Footer setting
        $this->footer_setting = get_option('ve_footer');
        $this->f_menu = (isset($this->footer_setting['menu'])) ? $this->footer_setting['menu'] : '';
        // Popups setting
        $this->popups->popups_setting = get_option('ve_popups');

        do_action('ve_global_setting');

        //Page setting

        $p_appearance = get_post_meta($this->post_id, 've_appearance', true);

        if ((isset($p_appearance['background_image']) && isset($p_appearance['background_image']['image']) && $p_appearance['background_image']['image']) || (isset($p_appearance['background_color']) && $p_appearance['background_color'])) {
            if (isset($p_appearance['background_image']['image']) && $p_appearance['background_image']['image']) $this->page_setting['background_image'] = $p_appearance['background_image'];
            else $this->page_setting['background_image'] = array('image' => '');
        }
        if (isset($p_appearance['background_image']) && isset($p_appearance['background_image']['pattern']) && $p_appearance['background_image']['pattern']) {
            $this->page_setting['background_image'] = $p_appearance['background_image'];
        }
        $this->page_setting = $this->merge_setting(get_post_meta($this->post_id, 've_appearance', true), $this->page_setting);

        //if(isset($p_appearance['show']) && $p_appearance['show']=="page") $this->page_setting=$p_appearance;
        //$this->page_setting['show']=(isset($p_appearance['show']))? $p_appearance['show'] : 'global';

        //Page header setting
        $p_header = get_post_meta($this->post_id, 've_header', true);
        if (isset($p_header['show']) && $p_header['show'] == "page") {
            $this->header_setting = $p_header;
            $this->h_menu = (isset($this->header_setting['menu'])) ? $this->header_setting['menu'] : '';
        }
        $this->header_setting['show'] = (isset($p_header['show'])) ? $p_header['show'] : 'global';

        // Page footer setting
        $p_footer = get_post_meta($this->post_id, 've_footer', true);
        if (isset($p_footer['show']) && $p_footer['show'] == "page") {
            $this->footer_setting = $p_footer;
            $this->f_menu = (isset($this->footer_setting['menu'])) ? $this->footer_setting['menu'] : '';
        }
        $this->footer_setting['show'] = (isset($p_footer['show'])) ? $p_footer['show'] : 'global';

        // Page popups setting
        $p_popups = get_post_meta($this->post_id, 've_popup', true);
        if (isset($p_popups['show']) && $p_popups['show'] == 'page')
            $this->popups->popups_setting = $p_popups;


        // hide header and footer in window editor
        if ($this->window_editor) {
            $this->header_setting['show'] = "noheader";
            $this->footer_setting['show'] = "nofooter";
        }

    }

    /* Styles ********
*******************************************************************************  */

    function print_styles($styles, $element, $mode = 'inline')
    {
        if ($this->edit_mode) {
            if ($mode == 'inline') return ($styles) ? 'style="' . $this->generate_style_atribut($styles) . '"' : '';  //inline styles
            else return ($styles) ? '<style>' . $element . "{" . $this->generate_style_atribut($styles) . "}" . '</style>' : '';  //<style>styles</style>
        } else {
            if (isset($this->styles[$element])) $this->styles[$element] .= $this->generate_style_atribut($styles);
            else $this->styles[$element] = $this->generate_style_atribut($styles);
            return '';
        }
    }

    function print_styles_array($styles_array)
    {
        $content = '';
        if ($this->edit_mode) {
            $content = '<style>';
            foreach ($styles_array as $styles) {
                $content .= ($styles['styles']) ? $styles['element'] . "{" . $this->generate_style_atribut($styles['styles']) . "}" : '';
            }
            $content .= '</style>';
        } else {
            foreach ($styles_array as $styles) {
                if (isset($this->styles[$styles['element']])) $this->styles[$styles['element']] .= $this->generate_style_atribut($styles['styles']);
                else $this->styles[$styles['element']] = $this->generate_style_atribut($styles['styles']);
            }
        }
        return $content;
    }

    function add_style($element, $styles, $mobile = false)
    {
        if ($mobile) {
            if (isset($this->mobile_styles[$mobile]) && isset($this->mobile_styles[$mobile][$element])) $this->mobile_styles[$mobile][$element] .= $this->generate_style("inline", $styles);
            else $this->mobile_styles[$mobile][$element] = $this->generate_style("inline", $styles);
        } else {
            if (isset($this->styles[$element])) $this->styles[$element] .= $this->generate_style("inline", $styles);
            else $this->styles[$element] = $this->generate_style("inline", $styles);
        }
    }

    function add_styles($styles, $mobile = false)
    {
        foreach ($styles as $element => $style) {
            $this->add_style($element, $style, $mobile);
        }
    }

    function generate_style_atribut($row_style, $element = 'row')
    {
        $style = "";
        $style .= $this->generate_style("inline", array(
            'bg' => $row_style,
            'background-color' => (isset($row_style['background-color'])) ? $row_style['background-color'] : '',
            'background-image' => (isset($row_style['background-image']) && $row_style['background-image']) ? 'url(' . $row_style['background-image'] . ')' : '',
            'background-attachment' => (isset($row_style['background-attachment']) && $row_style['background-attachment']) ? $row_style['background-attachment'] : '',
            'border-color' => (isset($row_style['border-color'])) ? $row_style['border-color'] : '',
            'border-top-color' => (isset($row_style['border-top-color'])) ? $row_style['border-top-color'] : '',
            'border-bottom-color' => (isset($row_style['border-bottom-color'])) ? $row_style['border-bottom-color'] : '',
            'padding-bottom' => (isset($row_style['padding_bottom'])) ? $row_style['padding_bottom'] . "px" : '',
            'margin-bottom' => (isset($row_style['margin_bottom'])) ? $row_style['margin_bottom'] . "px" : '',
            'margin-top' => (isset($row_style['margin_top'])) ? $row_style['margin_top'] . "px" : '',
            'margin-left' => (isset($row_style['margin_left'])) ? $row_style['margin_left'] . "px" : '',
            'margin-right' => (isset($row_style['margin_right'])) ? $row_style['margin_right'] . "px" : '',
            'font' => (isset($row_style['font'])) ? $row_style['font'] : '',
            'text-align' => (isset($row_style['align'])) ? $row_style['align'] : '',
            'shadow' => (isset($row_style['shadow'])) ? $row_style['shadow'] : '',
            'box-shadow' => (isset($row_style['box-shadow'])) ? $row_style['box-shadow'] : array(),
            'shadow_color' => (isset($row_style['shadow_color'])) ? $row_style['shadow_color'] : '',
            'text-shadow' => (isset($row_style['font']) && isset($row_style['font']['text-shadow'])) ? $row_style['font']['text-shadow'] : '',
            'color' => (isset($row_style['color'])) ? $row_style['color'] : '',
            'fill' => (isset($row_style['fill'])) ? $row_style['fill'] : '',
            'padding-top' => (isset($row_style['padding_top'])) ? $row_style['padding_top'] . "px" : '',
            'top' => (isset($row_style['top'])) ? $row_style['top'] . "px" : '',
            'right' => (isset($row_style['right'])) ? $row_style['right'] . "px" : '',
            'bottom' => (isset($row_style['bottom'])) ? $row_style['bottom'] . "px" : '',
            'position' => (isset($row_style['position'])) ? $row_style['position'] : '',
            'width' => (isset($row_style['width'])) ? $row_style['width'] : '',
            'min-width' => (isset($row_style['min-width'])) ? $row_style['min-width'] : '',
            'height' => (isset($row_style['height'])) ? $row_style['height'] . "px" : '',
            'min-height' => (isset($row_style['min-height'])) ? $row_style['min-height'] . "px" : '',
            'max-width' => (isset($row_style['max-width'])) ? $row_style['max-width'] . "px" : '',
            'font-weight' => (isset($row_style['font-weight'])) ? $row_style['font-weight'] . "" : '',
            'corner' => (isset($row_style['corner']) && $row_style['corner'] > 0) ? $row_style['corner'] . "px" : '',
            'padding' => (isset($row_style['padding'])) ? $row_style['padding'] : '',
            'paddingem' => (isset($row_style['paddingem'])) ? $row_style['paddingem'] : '',
            'padding-left' => (isset($row_style['padding-left'])) ? $row_style['padding-left'] : ((isset($row_style['padding_left']) && !is_array($row_style['padding_left'])) ? $row_style['padding_left'] . "px" : ''),
            'padding-right' => (isset($row_style['padding-right'])) ? $row_style['padding-right'] : ((isset($row_style['padding_right']) && !is_array($row_style['padding_right'])) ? $row_style['padding_right'] . "px" : ''),
            'padding-bottom' => (isset($row_style['padding-bottom'])) ? $row_style['padding-bottom'] : ((isset($row_style['padding_bottom']) && !is_array($row_style['padding_bottom'])) ? $row_style['padding_bottom'] . "px" : ''),
            'padding-top' => (isset($row_style['padding-top'])) ? $row_style['padding-top'] : ((isset($row_style['padding_top']) && !is_array($row_style['padding_top'])) ? $row_style['padding_top'] . "px" : ''),
            'opacity' => (isset($row_style['opacity'])) ? $row_style['opacity'] : '', 
        ));

        if (isset($row_style['border-bottom']) && isset($row_style['border-bottom']['size'])) {
            $style .= $this->generate_style("inline", array(
                'border-bottom' => $row_style['border-bottom'],
            ));
        }
        if (isset($row_style['border-top']) && isset($row_style['border-top']['size'])) {
            $style .= $this->generate_style("inline", array(
                'border-top' => $row_style['border-top'],
            ));
        }
        if (isset($row_style['border']) && isset($row_style['border']['size'])) {
            $style .= $this->generate_style("inline", array(
                'border' => $row_style['border'],
            ));
        }

        return $style;
    }

    function hex2rgba($hex,$transparency) {
       $hex = str_replace("#", "", $hex);
    
       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
       } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
       }
    
       return 'rgba('.$r.', '.$g.', '.$b.', '.($transparency/100).')';
    }
    function shiftColor($color, $coef=0.8) {
        
        if(!preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $parts))
            return '';
        
        $out = ""; 
        for($i = 1; $i <= 3; $i++) {
            $parts[$i] = hexdec($parts[$i]);
            $parts[$i] = round($parts[$i] * $coef); 
            if($parts[$i]>255) $parts[$i]=255;
            $out .= str_pad(dechex($parts[$i]), 2, '0', STR_PAD_LEFT);       
        }
        return '#'.$out;
    }

    function generate_style($selector, $styles)
    {
        $css = "";
        if ($selector != "inline") $css .= $selector . '{';
        foreach ($styles as $key => $style) {
            if ($style && $style != "px" && $style != " !important") {
                if ($key == 'bg') {
                    if (isset($style['background_image']['image']) && $style['background_image']['image']) {
                        $background_image = (substr($style['background_image']['image'], 0, 4) == 'http') ? $style['background_image']['image'] : home_url() . $style['background_image']['image'];
                        if (isset($style['background_color']['color1']) && $style['background_color']['color1']) {
                            $css .= "background: " . $style['background_color']['color1'] . " url(" . $background_image . "); background-position: " . $style['background_image']['position'] . "; background-repeat: " . $style['background_image']['repeat'] . ";";
                        } else if (isset($style['background_color']['color2']) && $style['background_color']['color2']) {
                            $css .= "background: " . $style['background_color']['color2'] . " url(" . $background_image . "); background-position: " . $style['background_image']['position'] . "; background-repeat: " . $style['background_image']['repeat'] . ";";
                        } else {
                            $css .= "background: url(" . $background_image . "); background-position: " . $style['background_image']['position'] . "; background-repeat: " . $style['background_image']['repeat'] . ";";
                        }

                        if (isset($style['background_image']['cover']) && $style['background_image']['cover']) {
                            $css .= "-webkit-background-size: cover;
                          -moz-background-size: cover;
                          -o-background-size: cover;
                          background-size: cover;
                          -ms-filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $background_image . "',sizingMethod='scale');
                          filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" . $background_image . "', sizingMethod='scale');
                          height: auto;";
                        }
                        if (isset($style['background_image']['fixed']) && $style['background_image']['fixed']) {

                            $css .= 'background-attachment: fixed;';

                        }
                        if (isset($style['background_image']['fixed']) && $style['background_image']['fixed'] && isset($style['background_image']['cover']) && $style['background_image']['cover']) {
                            //$css.='background-size: 100%;';
                        }

                    } else if (isset($style['background_image']['pattern']) && $style['background_image']['pattern']) {
                        $css .= "background-image: url(" . $this->list_patterns[$style['background_image']['pattern']] . $style['background_image']['pattern'] . "_p.png);";
                    } else if (isset($style['background_color']['color1']) && $style['background_color']['color1'] && isset($style['background_color']['color2']) && $style['background_color']['color2']) {
                        if (isset($style['background_color']['transparency']) && $style['background_color']['transparency'] < 100) {
                            $color1 = $this->hex2rgba($style['background_color']['color1'], $style['background_color']['transparency']);
                            $color2 = $this->hex2rgba($style['background_color']['color2'], $style['background_color']['transparency']);
                            $ie_color1 = str_replace("#", "#" . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color1']);
                            $ie_color2 = str_replace("#", "#" . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color2']);
                        } else {
                            $ie_color1 = $color1 = $style['background_color']['color1'];
                            $ie_color2 = $color2 = $style['background_color']['color2'];
                        }

                        $css .= "background: linear-gradient(to bottom, " . $color1 . " 0%, " . $color2 . " 100%) no-repeat border-box;";
                        $css .= "background: -moz-linear-gradient(top,  " . $color1 . ",  " . $color2 . ") no-repeat border-box;";
                        $css .= "background: -webkit-gradient(linear, left top, left bottom, from(" . $color1 . "), to(" . $color2 . ")) no-repeat border-box;";
                        $css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='" . $ie_color1 . "', endColorstr='" . $ie_color2 . "');";
                    } else if (isset($style['background_color']['color1']) && $style['background_color']['color1']) {
                        if (isset($style['background_color']['transparency']) && $style['background_color']['transparency'] < 100) {
                            $css .= "background: " . $this->hex2rgba($style['background_color']['color1'], $style['background_color']['transparency']) . ";";
                            $css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=" . str_replace("#", "#" . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color1']) . ", endColorstr=" . str_replace("#", "#" . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color1']) . ");";
                            $css .= "zoom:1;";

                        } else
                            $css .= "background: " . $style['background_color']['color1'] . ";";
                    } else if (isset($style['background_color']['color2']) && $style['background_color']['color2']) {
                        if (isset($style['background_color']['transparency']) && $style['background_color']['transparency'] < 100) {
                            $css .= "background: " . $this->hex2rgba($style['background_color']['color2'], $style['background_color']['transparency']) . ";";
                            $css .= "filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=" . str_replace("#", "#" . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color2']) . ", endColorstr=" . str_replace("#", "#" . dechex($style['background_color']['transparency'] * 2.5), $style['background_color']['color2']) . ");";
                            $css .= "zoom:1;";
                        } else $css .= "background: " . $style['background_color']['color2'] . ";";
                    }


                } else if ($key == 'font') {
                    if (isset($style['font-size']) && $style['font-size'] != "") $css .= "font-size: " . $style['font-size'] . "px;";
                    if (isset($style['font-family']) && $style['font-family']) $css .= "font-family: '" . $style['font-family'] . "';";
                    if (isset($style['color']) && $style['color']) $css .= "color: " . $style['color'] . ";";
                    if (isset($style['weight']) && $style['weight']) $css .= "font-weight: " . $style['weight'] . ";";
                    if (isset($style['align']) && $style['align']) $css .= "text-align: " . $style['align'] . ";";
                    if (isset($style['line-height']) && $style['line-height']) $css .= "line-height: " . $style['line-height'] . "em;";
                    if (isset($style['letter-spacing']) && $style['letter-spacing']) $css .= "letter-spacing: " . $style['letter-spacing'] . "px;";
                } else if ($key == 'text-shadow') {
                    if ($style == "dark") $css .= 'text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5); ';
                    else if ($style == "light") $css .= 'text-shadow: 1px 1px 1px rgba(255, 255, 255, 0.5); ';
                } else if ($key == 'box-shadow') {
                    if (isset($style['size']) && $style['size'])
                        $css .= '-webkit-box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . ');
                    -moz-box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . ');
                    box-shadow: ' . $style['horizontal'] . 'px ' . $style['vertical'] . 'px ' . $style['size'] . 'px 0 rgba(0, 0, 0, ' . ($style['transparency'] / 100) . '); ';
                } else if ($key == 'corner') {
                    if ($style) $css .= "-moz-border-radius: " . $style . ";
                -webkit-border-radius: " . $style . ";
                -khtml-border-radius: " . $style . ";
                border-radius: " . $style . ";";
                } else if ($key == 'padding') {
                    if ($style) $css .= "padding: " . $style['top'] . "px " . $style['right'] . "px " . $style['bottom'] . "px " . $style['left'] . "px;";
                } else if ($key == 'paddingem') {
                    if ($style) $css .= "padding: " . $style['top'] . "em " . $style['right'] . "em " . $style['bottom'] . "em " . $style['left'] . "em;";
                } else if ($key == 'border-top' || $key == 'border-bottom' || $key == 'border') {
                    $css .= $key . ": " . $style['size'] . "px " . (isset($style['style']) ? $style['style'] : 'solid') . " " . $style['color'] . ";";
                } else if ($key == 'opacity') {
                    if (!empty($style)) $css .= "zoom: 1;
                  filter: alpha(opacity=" . $style . ");
                  opacity: " . ($style / 100) . ";";
                } else $css .= $key . ': ' . $style . ';';
            }
        }
        if ($selector != "inline") $css .= '} ';
        return $css;
    }

    function generate_page_styles()
    {
        $content = "";
        foreach ($this->styles as $key => $val) {
            $content .= $key . "{" . $val . "}";
        }
        foreach ($this->mobile_styles as $resolution => $styles) {
            $content .= ' @media screen and (max-width: ' . $resolution . 'px) {';
            foreach ($styles as $key => $val) {
                $content .= $key . "{" . $val . "}";
            }
            $content .= '}';
        }
        return $content;
    }

    /* Footer ********
*******************************************************************************  */
    function add_page_footer()
    {
        if ($this->edit_mode) {
            echo '<div class="cms_nodisp">';
            wp_editor('', 've_hidden_tinymce_editor', array('quicktags' => 'true', 'textarea_rows' => '5', 'dfw' => false, 'wpautop' => false));
            echo '</div>';

            if (!$this->window_editor) {
                $this->editor_panel();
                $this->editor_top_panel();
                $this->page_selector();
            } else {
                $this->window_editor_panel();
            }
        } else {
            foreach ($this->element_scripts as $script) echo $script;
        }

        // cookie bar
        $this->cookie_info_bar();

        //slider background

        if (isset($this->page_setting['background_setting']) && $this->page_setting['background_setting'] == 'slider') {
            echo $this->generate_slider_background($this->page_setting['background_slides'], $this->page_setting['background_delay'], $this->page_setting['background_speed'], $this->page_setting['background_color']);
        }

        // video background
        
        if ((isset($this->page_setting['background_video_webm']) && ($this->page_setting['background_video_webm']) || (isset($this->page_setting['background_video_mp4']) && $this->page_setting['background_video_mp4']) || (isset($this->page_setting['background_video_ogg']) && $this->page_setting['background_video_ogg']))) {

            $mute = (isset($this->page_setting['video_setting']['sound'])) ? 'false' : 'true';

            if (!$this->is_mobile || isset($this->page_setting['video_setting']['show_mobile'])) {
                echo '<video autoplay="true" loop="true" muted="' . $mute . '" id="ve_video_background">';
                if ($this->page_setting['background_video_webm']) echo '<source src="' . $this->page_setting['background_video_webm'] . '" type="video/webm">';
                if ($this->page_setting['background_video_mp4']) echo '<source src="' . $this->page_setting['background_video_mp4'] . '" type="video/mp4">';
                if ($this->page_setting['background_video_ogg']) echo '<source src="' . $this->page_setting['background_video_ogg'] . '" type="video/ogg">';
                echo '</video>';
                echo '<!--[if lt IE 9]><![endif]-->';
            }
        }

        // print page styles
        echo '<style>';
        echo $this->generate_page_styles();
        echo '</style>';
        
        // generated styles
        /*
        if($this->edit_mode || !$this->generate_css) {
            // print page styles
            echo '<style>';
            echo $this->generate_page_styles();
            echo '</style>';
            
            if(!$this->generate_css && $this->post_id) {
                update_post_meta($this->post_id,'mw_generated_css',$this->generate_page_styles());
                update_post_meta($this->post_id,'mw_generated_css_time',$this->last_modified);   
            }
        } 
        */

        if ($this->is_iphone && !isset($this->page_setting['background_image']['mobile_hide']) && isset($this->page_setting['background_image']['cover']) && isset($this->page_setting['background_image']['image']) && $this->page_setting['background_image']['image']) {
            $background_image = $this->get_image_url($this->page_setting['background_image']['image']);
            
            echo '<div id="ve_background_image"><img src="' . $background_image . '"></div>';

        }   
          
    }


    function cookie_info_bar()
    {
        $cookie_info = get_option('web_option_others');
        if (isset($cookie_info['use_cookie']) && !isset($_COOKIE['mw_eu_cookie'])) {
            $ci_url = $this->create_link($cookie_info['cookie_url_info'], false);
            ?>
            <div class="mw_cookie_info_bar">
                <p>
                    <?php echo $cookie_info['cookie_text']; ?>
                    <?php if ($ci_url) { ?> - <a class="cookie_info_more" target="_blank" href="<?php echo $ci_url; ?>">
                            <?php echo __('Více informací','cms_ve'); ?></a><?php } ?>
                </p>
                <a class="ve_content_button cookie_info_button"
                   href="#"><?php echo $cookie_info['cookie_button_text']; ?></a>

                <div class="cms_clear"></div>
            </div>
            <script type="text/javascript">
                jQuery(".cookie_info_button").click(function () {
                    var exdate = new Date()
                    exdate.setDate(exdate.getDate() + 36500);
                    document.cookie = 'mw_eu_cookie=1; path=/; expires=' + exdate.toGMTString();
                    jQuery(".mw_cookie_info_bar").remove();
                    return false;
                });
            </script>
            <?php
        }
    }

    /* Header ********
*******************************************************************************  */
    function get_layer_fonts($layer, $fonts)
    {
        $popups = array();

        if ($layer && is_array($layer)) {
            foreach ($layer as $row) {
                $fonts = $this->get_row_fonts($row, $fonts);
            }
        }

        return $fonts;

    }

    function get_row_fonts($row, $fonts)
    {
        if (isset($row['style']['font'])) $fonts = $this->get_item_fonts($row['style']['font'], $fonts);
        if(isset($row['content'])) {
            foreach ($row['content'] as $col) {

                foreach ($col['content'] as $element) {

                    $fonts = $this->get_element_fonts($element, $fonts);
                }
            }
        }
        if(isset($row['type']) && $row['type']=='slider' && isset($row['style']['slides'])) {
            foreach($row['style']['slides'] as $key=>$val) {
                $fonts = $this->merge_fonts($fonts, $this->get_weditor_fonts($val['slider_content']));
            }
        }
        return $fonts;
    }

    function get_element_fonts($element, $fonts)
    {
        // get popup fonts
        if ($element['type'] == 'button' && isset($element['style']['show']) && $element['style']['show'] == 'popup' && $element['style']['popup']) {
            $fonts = $this->merge_fonts($fonts, $this->get_weditor_fonts($element['style']['popup']));
        }
        if ($element['type'] == 'variable_content' && $element['content']) {
            $fonts = $this->merge_fonts($fonts, $this->get_weditor_fonts($element['content']));
        }
        if ($element['type'] == 'text' && $element['content']) {
            preg_match_all("/\[(popup|content) id=(\d+)\]/", $element['content'], $text_popups, PREG_PATTERN_ORDER);
            foreach ($text_popups[2] as $tpop) {
                $fonts = $this->merge_fonts($fonts, get_post_meta($tpop, 've_google_fonts', true));
            }
        }

        $fonts = $this->get_setting_fonts($element['style'], $fonts);

        if ($element['type'] == "twocols" || $element['type'] == "box") {
            if (isset($element['content'][0]) && is_array($element['content'][0])) {
                foreach ($element['content'][0] as $subelement) {
                    $fonts = $this->get_setting_fonts($subelement['style'], $fonts);
                }
            }
            if (isset($element['content'][1]) && is_array($element['content'][1])) {
                foreach ($element['content'][1] as $subelement) {
                    $fonts = $this->get_setting_fonts($subelement['style'], $fonts);
                }
            }
        }
        return $fonts;
    }

    function get_weditor_fonts($id)
    {
        return get_post_meta($id, 've_google_fonts', true);
    }

    function merge_fonts($font1, $font2)
    {
        if (!empty($font2) && is_array($font2)) {
            foreach ($font2 as $key => $val) {
                if (isset($font1[$key])) $font1[$key] += $val;
                else $font1[$key] = $val;
            }
        }
        return $font1;
    }

    function get_setting_fonts($set, $fonts)
    {
        if (is_array($set)) {
            foreach ($set as $key => $val) {
                if (strpos($key, 'font') !== false) {
                    $fonts = $this->get_item_fonts($set[$key], $fonts);
                } else if (is_array($val)) {
                    foreach ($val as $subkey => $subval) {
                        if (strpos($subkey, 'font') !== false) {
                            $fonts = $this->get_item_fonts($set[$key][$subkey], $fonts);
                        }
                    }
                }
            }
        }
        return $fonts;
    }

    function get_item_fonts($element, $fonts)
    {
        global $cms;
        $weight = isset($element['weight']) ? $element['weight'] : '';
        if (isset($element['font-family']) && isset($cms->google_fonts[$element['font-family']])) $fonts[$element['font-family']][$weight] = $weight;
        return $fonts;
    }


    function add_page_header_scripts()
    {
        global $cms;
        
        // page fonts
        $page_fonts = get_post_meta($this->post_id, 've_google_fonts', true);
        if (!$page_fonts) {
            $page_fonts = $this->get_layer_fonts($this->layer, array());
            update_post_meta($this->post_id, 've_google_fonts', $page_fonts);
        }
        $this->google_fonts = $this->merge_fonts($this->google_fonts, $page_fonts);
        //classic popup fonts
        if (isset($this->popups->popups_setting['clasic_popup']) && $this->popups->popups_setting['clasic_popup']) {
            $popup_fonts = get_post_meta($this->popups->popups_setting['clasic_popup'], 've_google_fonts', true);
            $this->google_fonts = $this->merge_fonts($this->google_fonts, $popup_fonts);
        }
        //exit popup fonts
        if (isset($this->popups->popups_setting['exit_popup']) && $this->popups->popups_setting['exit_popup']) {
            $popup_fonts = get_post_meta($this->popups->popups_setting['exit_popup'], 've_google_fonts', true);
            $this->google_fonts = $this->merge_fonts($this->google_fonts, $popup_fonts);
        }

        // popups in blog posts
        if (is_single()) {
            global $post, $blog_module;
            preg_match_all("/\[(popup|content) id=(\d+)\]/", $post->post_content, $text_popups, PREG_PATTERN_ORDER);
            foreach ($text_popups[2] as $tpop) {
                $this->google_fonts = $this->merge_fonts($this->google_fonts, get_post_meta($tpop, 've_google_fonts', true));
            }
            // weditor after post
            if(isset($blog_module->setting['content_after_post']))
                $this->google_fonts = $this->merge_fonts($this->google_fonts, get_post_meta($blog_module->setting['content_after_post'], 've_google_fonts', true));
        }

        //custom header
        if (isset($this->header_setting['before_header']) && $this->header_setting['before_header']) {
            $header_fonts = get_post_meta($this->header_setting['before_header'], 've_google_fonts', true);
            $this->google_fonts = $this->merge_fonts($this->google_fonts, $header_fonts);
        }

        //custom footer
        if (isset($this->footer_setting['custom_footer']) && $this->footer_setting['custom_footer']) {
            $footer_fonts = get_post_meta($this->footer_setting['custom_footer'], 've_google_fonts', true);
            $this->google_fonts = $this->merge_fonts($this->google_fonts, $footer_fonts);
        }

        // header logo font
        if (isset($this->header_setting['logo_font']) && isset($cms->google_fonts[$this->header_setting['logo_font']['font-family']])) $this->google_fonts[$this->header_setting['logo_font']['font-family']][$this->header_setting['logo_font']['weight']] = $this->header_setting['logo_font']['weight'];
        // header menu font
        if (isset($cms->google_fonts[$this->header_setting['menu_font']['font-family']])) $this->google_fonts[$this->header_setting['menu_font']['font-family']][$this->header_setting['menu_font']['weight']] = $this->header_setting['menu_font']['weight'];
        // page font
        if (isset($cms->google_fonts[$this->page_setting['font']['font-family']])) $this->google_fonts[$this->page_setting['font']['font-family']][$this->page_setting['font']['weight']] = $this->page_setting['font']['weight'];
        // footer font
        if (isset($cms->google_fonts[$this->footer_setting['font']['font-family']])) $this->google_fonts[$this->footer_setting['font']['font-family']][$this->footer_setting['font']['weight']] = $this->footer_setting['font']['weight'];
        // text format font
        if (isset($cms->google_fonts[$this->page_setting['title_font']['font-family']])) $this->google_fonts[$this->page_setting['title_font']['font-family']][$this->page_setting['title_font']['weight']] = $this->page_setting['title_font']['weight'];

        foreach ($this->google_fonts as $key => $val) {
            // add bold to text
            if ((isset($val['400']) || isset($val['300'])) && !isset($val['700']) && isset($cms->google_fonts[$key]['weights']['700'])) $val['700'] = '700';
            // print google font link
            if($key) echo '<link href="https://fonts.googleapis.com/css?family=' . str_replace(" ", "+", $key) . ':' . implode(',', $val) . '&subset=latin,latin-ext" rel="stylesheet" type="text/css">';
        }

        echo '<!--[if IE 8]>
    	
    <![endif]-->';

        //basic styles

        $header_active_bg = (isset($this->header_setting['menu_active_bg']) ? $this->header_setting['menu_active_bg'] : '');
        $header_menu_bg = '';
        if ($this->header_setting['appearance'] == 'type4' || $this->header_setting['appearance'] == 'type5' || $this->header_setting['appearance'] == 'type8' || $this->header_setting['appearance'] == 'type9' || $this->header_setting['appearance'] == 'type10') {
            $header_active_bg = (isset($this->header_setting['menu_active_color']) ? array('color1' => $this->header_setting['menu_active_color'], 'color2' => '') : '');
        }
        if ($this->header_setting['appearance'] == 'type5' || $this->header_setting['appearance'] == 'type8' || $this->header_setting['appearance'] == 'type9' || $this->header_setting['appearance'] == 'type10') {
            $header_menu_bg = (isset($this->header_setting['menu_bg'])) ? $this->header_setting['menu_bg'] : '';
        }

        if (!isset($this->page_setting['background_setting'])) $this->page_setting['background_setting'] = 'image';

        // header styles
        $this->add_styles(array(
            "#header" => array(
                'bg' => $this->header_setting,
            ),
            "#site_title" => array(
                'font' => (isset($this->header_setting['logo_font']) ? $this->header_setting['logo_font'] : ''),
            ),
            "#site_title img" => array(
                'width' => (isset($this->header_setting['logo_size']) ? $this->header_setting['logo_size'].'px' : ''),
            ),
            "#header nav li a, #header nav li:after" => array(
                'font' => (isset($this->header_setting['menu_font']) ? $this->header_setting['menu_font'] : ''),
            ),
            "#mobile_nav svg path" => array(
                'fill' => (isset($this->header_setting['menu_font']['color']) ? $this->header_setting['menu_font']['color'] : '')
            ),
            "#header nav li:hover a, #header nav li.current-menu-item a, #header nav li.current_page_parent a, #header nav li.current-page-ancestor a " => array(
                'bg' => array('background_color' => $header_active_bg),
                'color' => (isset($this->header_setting['menu_active_color']) && $this->header_setting['appearance'] != 'type4' && $this->header_setting['appearance'] != 'type5' && $this->header_setting['appearance'] != 'type8' && $this->header_setting['appearance'] != 'type9' && $this->header_setting['appearance'] != 'type1c' ? $this->header_setting['menu_active_color'] : '')
            ),
            ".ve-header-type1c #header:not(.is-mobile_menu) nav li:hover span, 
            .ve-header-type1c #header:not(.is-mobile_menu) nav li.current-menu-item span, 
            .ve-header-type1c #header:not(.is-mobile_menu) nav li.current_page_parent span, 
            .ve-header-type1c #header:not(.is-mobile_menu) nav li.current-page-ancestor span " => array(
                'background-color' => (isset($this->header_setting['menu_active_color'])? $this->header_setting['menu_active_color'] : "")
            ),
            "#header nav #site_header_nav" => array(
                'bg' => array('background_color' => $header_menu_bg),
            ),  
            '#header .sub-menu' => array(
                'bg' => array('background_color' => array('color1' => isset($this->header_setting['menu_active_color']) ? $this->header_setting['menu_active_color'] : ''))
            ),
            '#header nav li.menu-item .sub-menu a,
            .header_s3 #site_header_nav li:hover a,
            .header_s3 #site_header_nav li.current-menu-item a,
            .header_s3 #site_header_nav li.current-page-ancestor a,
            .header_s3 #site_header_nav li.current_page_parent a,
            .ve-header-type8 #header nav li:hover a,
            .ve-header-type8 #header nav li.current-menu-item a,
            .ve-header-type8 #header nav li.current-page-ancestor a,
            .ve-header-type8 #header nav li.current_page_parent a,
            .ve-header-type5 #header nav li:hover a,
            .ve-header-type5 #header nav li.current-menu-item a,
            .ve-header-type5 #header nav li.current-page-ancestor a,
            .ve-header-type5 #header nav li.current_page_parent a,
            .ve-header-type4 #header nav li:hover a,
            .ve-header-type4 #header nav li.current-menu-item a,
            .ve-header-type4 #header nav li.current-page-ancestor a,
            .ve-header-type4 #header nav li.current_page_parent a' => array(
                'color' => (isset($this->header_setting['menu_submenu_text_color'])) ? $this->header_setting['menu_submenu_text_color'] : 'inherit'
            ),
            "#header nav li.menu-item .sub-menu li.menu-item-has-children > a::after" => array(
                'border-color' => (isset($this->header_setting['menu_submenu_text_color'])) ? $this->header_setting['menu_submenu_text_color'] : 'inherit'
            ),
            // page styles

            "body" => array(
                'background-color' => isset($this->page_setting['background_color']) ? $this->page_setting['background_color'] : '',
                'font' => isset($this->page_setting['font']) ? $this->page_setting['font'] : '',
            ),
            "input, textarea" => array(
                'font' => isset($this->page_setting['font']) ? $this->page_setting['font'] : '',
            ),
            "a" => array(
                'color' => $this->page_setting['link_color'],
            ),
            ".in_element_content" => array(
                'line-height' => (isset($this->page_setting['font']['line-height']) && $this->page_setting['font']['line-height']) ? $this->page_setting['font']['line-height'] : '',
            ),
            // text styles
            "h1,h2,h3,h4,h5,h6,.title_element_container,.form_container_title" => array(
                'font' => $this->page_setting['title_font'],
            ),
            ".entry_content h1" => array(
                'font' => $this->page_setting['h1_font'],
            ),
            ".entry_content h2" => array(
                'font' => $this->page_setting['h2_font'],
            ),
            ".entry_content h3" => array(
                'font' => $this->page_setting['h3_font'],
            ),
            ".entry_content h4" => array(
                'font' => $this->page_setting['h4_font'],
            ),
            ".entry_content h5" => array(
                'font' => $this->page_setting['h5_font'],
            ),
            ".entry_content h6" => array(
                'font' => $this->page_setting['h6_font'],
            ),
            // footer styles
            "#footer" => array(
                'font' => $this->footer_setting['font'],
                'bg' => $this->footer_setting,
            ),
            "#footer a" => array(
                'color' => $this->footer_setting['font']['color'],
            )
        ));
        if(in_array($this->header_setting['appearance'],array('type5','type8','type9','type10'))) {
            $this->add_style("#mobile_nav", array(
                'bg' => array('background_color' => $header_menu_bg),
            ));
        }

        if (isset($this->page_setting['page_width']['size']) && $this->page_setting['page_width']['size']) {
            $this->add_style(".row_fix_width, .row_basic_fix_width, .fixed_width_page .row, .row_fixed, .fixed_template #content, .fixed_width_page #wrapper, .fixed_narrow_width_page #wrapper", array(
                'max-width' => $this->page_setting['page_width']['size'] . $this->page_setting['page_width']['unit'],
            ));
        }
        if (isset($this->header_setting['header_width']['size']) && $this->header_setting['header_width']['size']) {
            $this->add_style("#header_in, .header_in_s2 #site_header_nav", array(
                'max-width' => $this->header_setting['header_width']['size'] . $this->header_setting['header_width']['unit'],
            ));
        }
        if (isset($this->header_setting['header_padding']) && $this->header_setting['header_padding']!=='') {
            $this->add_style("#header_in", array(
                'padding-top' => $this->header_setting['header_padding'] . 'px',
                'padding-bottom' => $this->header_setting['header_padding'] . 'px',
            ));
        }
        if (isset($this->header_setting['fixed_header'])) {
            $this->add_styles(array(
                ".ve_fixed_header_scrolled #header" => array(
                    'bg' => isset($this->header_setting['background_color_fix']) ? array('background_color' => $this->header_setting['background_color_fix']) : "",
                    'box-shadow' => (isset($this->header_setting['header_shadow_fix'])) ? array('horizontal' => 0, 'vertical' => 3, 'size' => 3, 'transparency' => 8) : array(),
                ),
                ".ve_fixed_header_scrolled #header_in" => array(
                    'padding-top' => isset($this->header_setting['header_padding_fix']) ? $this->header_setting['header_padding_fix']['size'] . 'px' : "",
                    'padding-bottom' => isset($this->header_setting['header_padding_fix']) ? $this->header_setting['header_padding_fix']['size'] . 'px' : "",
                ),
            ));
        }
        if (isset($this->footer_setting['footer_width']['size']) && $this->footer_setting['footer_width']['size']) {
            $this->add_style("#footer-in, footer .row_fix_width", array(
                'max-width' => $this->footer_setting['footer_width']['size'] . $this->footer_setting['footer_width']['unit'],
            ));
        }
        
        // if is cover image
        if ($this->is_iphone && isset($this->page_setting['background_image']['cover']) && $this->page_setting['background_image']['image']) {

        } else  {
                /*if(isset($this->page_setting['background_image']['cover']))
                    $this->page_setting['background_image']['fixed']=1; */
                $this->add_style("body", array(
                    'bg' => array('background_image' => $this->page_setting['background_image']),
                    'background-color' => isset($this->page_setting['background_color']) ? $this->page_setting['background_color'] : '',
                ));                  
        }  
        
        // color cover for image
                
        if (!(isset($this->page_setting['background_setting']) && $this->page_setting['background_setting']!='image') && isset($this->page_setting['background_image']['image']) && $this->page_setting['background_image']['image'] && isset($this->page_setting['background_image']['cover']) && isset($this->page_setting['background_image']['color_filter'])) {
            $this->add_style("body.ve_colored_background:before, body #ve_background_image:before", array(
                'background-color' => $this->page_setting['background_image']['overlay_color'],
                'opacity' => $this->page_setting['background_image']['overlay_transparency'],
            ));   
        } 

        // styles for mobile devices
        if (isset($this->page_setting['background_image']['mobile_hide']) && $this->page_setting['background_image']['image']) {
            $this->add_style(
                'body',
                array('background-image' => 'none'),
                '640'
            );
        }
        if (isset($this->header_setting['background_image']['mobile_hide']) && $this->header_setting['background_image']['image']) {
            $this->add_style(
                '#header',
                array('background-image' => 'none'),
                '640'
            );
        }


        // print basic styles
        echo '<style>';
        echo $this->generate_page_styles();
        echo '</style>';

        $this->styles = array();
    }

    /* Editor ********
*******************************************************************************  */
    function editor_panel()
    {
        global $cms;
        global $member_module;
        
        $hid_panel = (isset($_COOKIE['ve_hidden_panel'])) ? $_COOKIE['ve_hidden_panel'] : 0;
        $hid_features = (isset($_COOKIE['ve_hidden_features'])) ? $_COOKIE['ve_hidden_features'] : 0;

        ?>
        <div id="ve_editor_panel" class="ve_editor_panel <?php if ($hid_panel) echo "ve_editor_panel_hidden"; ?>">
            <a id="ve_change_page" class="ve_open_page_selector"
               href="#"><?php if (is_404()) echo __('Stránka neexistuje', 'cms_ve'); else if (is_home()) echo __('Úvodní stránka', 'cms_ve'); else the_title(); ?>
                <span></span></a>

            <ul class="ve_editor_menu ve_menu">
                <?php

                // Global setting

                /* Blog global setting
       *********************************************************************** */
                if ($this->modul_type == 'blog') {
                    $allowed_sets = allowed_sets($cms->p_set['page_set'], 'post', '');

                    // Nastavení blogu
                    ?>
                    <li class="ve_left_menu_has_submenu">
                        <a class="open-setting ve_page_setting_ico" data-type="group" data-setting="blog_option"
                           title="<?php echo __('Nastavení blogu', 'cms_ve') ?>"
                           href="#"><?php echo __('Nastavení blogu', 'cms_ve') ?></a>
                        <ul>
                            <?php
                            foreach ($cms->page_set_groups['blog_option'] as $value) {
                                echo '<li><a class="open-setting" data-type="single_tab" data-setting="blog_option:' . $value['id'] . '" title="' . $value['name'] . '" href="#">' . $value['name'] . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <?php // Vzhled blogu
                    ?>
                    <li class="ve_left_menu_has_submenu">
                        <a class="open-setting ve_page_appearance_ico" data-type="group"
                           data-setting="appearanceblog_option" title="<?php echo __('Vzhled blogu', 'cms_ve') ?>"
                           href="#"><?php echo __('Vzhled blogu', 'cms_ve') ?></a>
                        <ul>
                            <?php
                            foreach ($cms->page_set_groups['appearanceblog_option'] as $value) {
                                echo '<li><a class="open-setting" data-type="single_tab" data-setting="appearanceblog_option:' . $value['id'] . '" title="' . $value['name'] . '" href="#">' . $value['name'] . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                    <?php

                    /* Member global setting
    *********************************************************************** */

                } else if ($this->modul_type == 'member') {
                    $allowed_sets = allowed_sets($cms->p_set['page_set'], 'page', $this->post_id);

                    ?>
                    <li class="ve_left_menu_has_submenu">
                        <a class="open-setting ve_page_appearance_ico" data-type="group"
                           data-setting="appearancemember_option:<?php echo $member_module->member_section_id; ?>" 
                           title="<?php echo __('Vzhled členské sekce','cms_ve') ?> <?php echo $member_module->member_section['name']; ?>"
                           href="#"><?php echo __('Vzhled členské sekce', 'cms_ve') ?></a>
                        <ul>
                            <?php
                            foreach ($cms->page_set_groups['appearancemember_option'] as $value) {
                                echo '<li><a class="open-setting" data-type="single_tab" data-setting="appearancemember_option:'.$value['id'].':'.$member_module->member_section_id.'" title="'.$value['name'].'" href="#">'.$value['name'].'</a></li>';
                            }
                            ?>
                        </ul>
                    </li>

                    <?php 
                    
                    /* Eshop global setting
                    *********************************************************************** */
                  } else if($this->modul_type=='eshop'){
                        if(is_page()) $allowed_sets = allowed_sets($cms->p_set['page_set'], 'page', $this->post_id);
                        else $allowed_sets=allowed_sets($cms->p_set['page_set'], 'mwproduct', '');
                
                        // Nastavení eshopu ?>
                        <li class="ve_left_menu_has_submenu">
                            <a class="open-setting ve_page_setting_ico" data-type="group" data-setting="eshop_option" title="<?php echo __('Nastavení eshopu','cms_ve') ?>" href="#"><?php echo __('Nastavení eshopu','cms_ve') ?></a>
                            <ul> 
                                <?php                    
                                foreach($cms->page_set_groups[MWS_OPTION_SHOP] as $value) {
                                    echo '<li><a class="open-setting" data-type="single_tab" data-setting="eshop_option:'.$value['id'].'" title="'.$value['name'].'" href="#">'.$value['name'].'</a></li>'; 
                                } 
                                ?>
                            </ul>
                        </li>
                        <?php // Vzhled eshopu ?>
                        <li class="ve_left_menu_has_submenu">
                            <a class="open-setting ve_page_appearance_ico" data-type="group" data-setting="appearance_eshop_option" title="<?php echo __('Vzhled eshopu','cms_ve') ?>" href="#"><?php echo __('Vzhled eshopu','cms_ve') ?></a>
                            <ul> 
                                <?php                    
                                foreach($cms->page_set_groups['appearance_eshop_option'] as $value) {
                                    echo '<li><a class="open-setting" data-type="single_tab" data-setting="appearance_eshop_option:'.$value['id'].'" title="'.$value['name'].'" href="#">'.$value['name'].'</a></li>'; 
                                } 
                                ?>
                            </ul>
                        </li>
                    <?php 

                /* Web, Campaign global setting
                *********************************************************************** */

                } else if (is_page()) {

                    $allowed_sets = allowed_sets($cms->p_set['page_set'], 'page', $this->post_id);
                    ?>
                    <li class="ve_left_menu_has_submenu ve_editor_first_menu">
                        <a class="open-setting ve_page_appearance_ico" data-type="group" data-setting="ve_option"
                           title="<?php echo __('Vzhled webu', 'cms_ve') ?>"
                           href="#"><?php echo __('Vzhled webu', 'cms_ve') ?></a>
                        <ul>
                            <?php
                            foreach ($cms->page_set_groups['ve_option'] as $value) {
                                echo '<li><a class="open-setting" data-type="single_tab" data-setting="ve_option:' . $value['id'] . '" title="' . $value['name'] . '" href="#">' . $value['name'] . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>

                    <?php
                    
                  
                  }

                ?>
            </ul>
            <ul class="ve_editor_menu ve_menu ve_editor_second_menu">
                <?php
                
                if((is_page() || (is_single() && $this->modul_type!='eshop'))) {
                    //Nastavení stránky ?>
                    <li class="ve_left_menu_has_submenu">
                        <a class="ve_open_page_setting ve_page_setting_ico" data-setid="page_set"
                           data-id="<?php echo $this->post_id; ?>" href="#"
                           title="<?php echo (is_single()) ? __('Nastavení článku', 'cms_ve') : __('Nastavení stránky', 'cms_ve') ?>"><?php echo (is_single()) ? __('Nastavení článku', 'cms_ve') : __('Nastavení stránky', 'cms_ve') ?></a>
                        <ul>
                            <li><a class="ve_open_basic_page_setting" href="#" data-id="<?php echo $this->post_id; ?>"
                                   title="<?php echo __('Základní nastavení', 'cms_ve') ?>"><?php echo __('Základní nastavení', 'cms_ve') ?></a>
                            </li>
                            <?php          
                            if($allowed_sets) {           
                                foreach ($allowed_sets as $set) {
                                    echo '<li><a class="ve_open_page_single_setting" title="' . $set['title'] . '" href="#" data-id="' . $this->post_id . '" data-setid="page_set" data-tabid="' . $set['id'] . '">' . $set['title'] . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </li>
                    <?php
                    if(is_page() ) { 
                        // Vzhled stránky 
                        ?>
                        <li class="ve_left_menu_has_submenu">
                            <a class="ve_open_page_setting ve_page_appearance_ico" data-setid="ve_page_appearance"
                               data-id="<?php echo $this->post_id; ?>" href="#"
                               title="<?php echo __('Vzhled stránky', 'cms_ve') ?>"><?php echo __('Vzhled stránky', 'cms_ve') ?></a>
                            <ul>
                                <?php
                                foreach (allowed_sets($cms->p_set['ve_page_appearance'], 'page', $this->post_id) as $set) {
                                    echo '<li><a class="ve_open_page_single_setting" title="' . $set['title'] . '" href="#" data-id="' . $this->post_id . '" data-setid="ve_page_appearance" data-tabid="' . $set['id'] . '">' . $set['title'] . '</a></li>';
                                }
                                
                                if($this->modul_type!='eshop') {
                                ?>
                                <li><a class="ve_change_template_but" data-id="<?php echo $this->post_id; ?>"
                                       data-type="page" href="#"
                                       title="<?php echo __('Šablona stránky', 'cms_ve') ?>"><?php echo __('Šablona stránky', 'cms_ve') ?></a>
                                </li>
                                <?php }  ?>
                            </ul>
                        </li>
                        <?php 
                            
                        if ($this->modul_type=='eshop' && MWS()->getOrderPage()==$this->post_id) {
                        } else {
                        // Akce stránky ?>
                        <li class="ve_left_menu_has_submenu">
                            <a class="ve_prevent_default ve_page_action_ico"
                               href="#"><?php echo __('Akce stránky', 'cms_ve'); ?></a>
                            <ul>
                                <li><a class="create-page-copy" data-type="<?php echo $this->modul_type; ?>"
                                       title="<?php echo __('Duplikovat stránku', 'cms_ve') ?>" href="#"
                                       data-id="<?php echo $this->post_id; ?>"><?php echo __('Duplikovat stránku', 'cms_ve') ?></a>
                                </li>
                                <li><a class="" target="_blank" title="<?php echo __('Exportovat stránku', 'cms_ve') ?>"
                                       href="<?php echo get_permalink($this->post_id) . '?export_mioweb_template=' . $this->post_id; ?>"><?php echo __('Exportovat stránku', 'cms_ve') ?></a>
                                </li>
                                <li><a id="ve_set_homepage"
                                       href="?ve_set_home=<?php echo $this->post_id; ?>"><?php echo __('Nastavit jako domovskou', 'cms_ve'); ?></a>
                                </li>
                                <?php if($this->modul_type!='eshop') { ?>
                                <li><a id="ve_delete_page"
                                       href="?ve_delete_page=<?php echo $this->post_id; ?>"><?php echo __('Smazat stránku', 'cms_ve'); ?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php
                        }
                    } else if (is_single()) {
                        ?>
                        <li><a class="ve_page_setting_ico" target="_blank"
                               href="<?php echo admin_url('post.php?post=' . $this->post_id . '&action=edit'); ?>"
                               title="<?php echo __('Upravit příspěvek', 'cms_ve') ?>"><?php echo __('Upravit příspěvek', 'cms_ve') ?></a>
                        </li>
                        <?php
                    }
                } else if ($this->modul_type == 'blog') { ?>
                    <?php
                    //get_option('page_for_posts');

                    if (get_option('show_on_front') == 'page' && is_home()) { ?>
                        <li class="ve_left_menu_has_submenu">
                            <a class="ve_open_page_setting ve_page_setting_ico" data-setid="page_set"
                               data-id="<?php echo get_option('page_for_posts'); ?>"
                               href="#"><?php echo __('Nastavení stránky', 'cms_ve') ?></a>
                            <ul>
                                <li><a class="ve_open_basic_page_setting" href="#"
                                       data-id="<?php echo get_option('page_for_posts'); ?>"
                                       title="<?php echo __('Základní nastavení', 'cms_ve') ?>"><?php echo __('Základní nastavení', 'cms_ve') ?></a>
                                </li>
                            </ul>
                        </li>
                    <?php }
                } else if($this->modul_type=='eshop'){ 
                    
                    if(is_single()) {
                        ?>
                        <li class="ve_left_menu_has_submenu">
                            <a class="ve_open_page_setting ve_page_setting_ico" data-setid="page_set" data-id="<?php echo $this->post_id; ?>" href="#" title="<?php echo __('Nastavení produktu','cms_ve') ?>"><?php echo __('Nastavení produktu','cms_ve') ?></a>
                            <ul> 
                                <li><a class="ve_open_basic_page_setting" href="#" data-id="<?php echo $this->post_id; ?>" title="<?php echo __('Základní nastavení','cms_ve') ?>"><?php echo __('Základní nastavení','cms_ve') ?></a></li>
                                <?php                 
                                    foreach($allowed_sets as $set) {
                                        echo '<li><a class="ve_open_page_single_setting" title="'.$set['title'].'" href="#" data-id="'.$this->post_id.'" data-setid="page_set" data-tabid="'.$set['id'].'">'.$set['title'].'</a></li>';
                                    }
                                ?>
                             </ul>
                        </li>
                        <li class="ve_left_menu_has_submenu">
                            <a class="ve_prevent_default ve_page_action_ico" href="#"><?php echo __('Akce produktu','cms_ve'); ?></a>
                            <ul> 
                                <!-- <li><a class="create-page-copy" data-type="<?php echo $this->modul_type; ?>" title="<?php echo __('Duplikovat produkt','cms_ve') ?>" href="#" data-id="<?php echo $this->post_id; ?>"><?php echo __('Duplikovat produkt','cms_ve') ?></a></li> -->
                                <li><a id="ve_delete_page" href="?ve_delete_page=<?php echo $this->post_id; ?>"><?php echo __('Smazat produkt','cms_ve'); ?></a></li>
                            </ul>
                        </li>
                        <li><a class="ve_page_setting_ico" target="_blank"
                            href="<?php echo admin_url('post.php?post=' . $this->post_id . '&action=edit'); ?>"
                            title="<?php echo __('Spustí editaci produktu', 'cms_ve') ?>"><?php echo __('Upravit produkt', 'cms_ve') ?></a>
                        </li>
                        <?php
                    }           
                } ?>

            </ul>

            <form id="ve_save_post_form" action="" method="post">
                <input id="ev_post_id" type="hidden" name="post_id" value="<?php echo ($this->save_id)? $this->save_id : $this->post_id; ?>"/>
                <input id="ve_page_type" type="hidden" name="ve_page_type" value="<?php echo $this->page_type; ?>"/>
                <?php if (is_page() || is_404() || is_home() || $this->is_editable()) { ?>
                    <?php if (is_page()) { ?>
                        <button class="ve_editor_subut ev_save_page" type="submit" data-status="draft"
                                data-ostatus="<?php echo $this->post_status; ?>"><?php echo __('Uložit jako koncept', 'cms_ve'); ?></button><?php } ?>
                    <button type="submit" class="ev_save_page cms_button" id="ev_save_page" data-status="publish"
                            data-ostatus="<?php echo $this->post_status; ?>"><?php if ($this->post_status == "draft") echo __('Publikovat', 'cms_ve'); else echo __("Uložit změny", 'cms_ve'); ?> </button>
                <?php } ?>
            </form>

            <a class="sh-editor-features <?php echo ($hid_features) ? "show-editor-features" : "hide-editor-features"; ?>"
               href="#"><?php echo ($hid_features) ? __("Zobrazit ovládání", 'cms_ve') : __("Skrýt ovládání", 'cms_ve'); ?></a>
            <a class="show-hide-panel <?php echo ($hid_panel) ? "shp-show-panel" : "shp-hide-panel"; ?>" href="#"></a>
            <input type="hidden" id="edited_page" autocomplete="off"
                   value="<?php echo (isset($_SESSION['ve_layer_autosave'][$this->post_id])) ? 1 : $this->edited_page;
                   unset($_SESSION['ve_layer_autosave'][$this->post_id]); ?>"/>
        </div>
        <?php
        if (!$hid_panel) echo '<style>html{padding-left: 185px;}</style>';
    }

    function page_selector() {
        global $cms;
        ?>
        <div id="ve_page_selector">
            <a class="ve_close_page_selector" href="#">
            <?php 
                if(is_404()) echo __('Stránka neexistuje.','cms_ve'); 
                else the_title(); 
            ?><span></span></a>
            
            <?php 
            $page_list=array();
            $tabs=array();
            $exclude=array();
            $tabs['web']=__('Web','cms_ve');
            $i=1;
    
            if($this->modul_type=='member') $current_tab='member';
            else if($this->modul_type=='campaign') $current_tab='campaign';
            else if($this->modul_type=='eshop') $current_tab='shop';
            else if($this->modul_type=='blog') $current_tab='blog';
            else $current_tab='web';
            
            // Blog
            
            // blog categories
                
            $page_list[$i]=array(
                    'name'=>__('Kategorie blogu','cms_ve'), 
                    'type'=>'blog',
                    'taxonomy'=>'category', 
                    'copy'=>false, 
                    'pages'=>get_categories( array( 'hide_empty'=>0, 'parent'=>0 ) )
            );
            $i++;
    
            $page_list[$i] = array(
                'name'=>__('Články','cms_ve'), 
                'pages'=>get_posts(array('post_status'=>'publish,private,draft,future,pending')),
                'type'=>'blog',
                'copy'=>false, 
                'del'=>false, 
            );
            $i++;
            $tabs['blog']=__('Blog','cms_ve');    
    
            
            // Eshop
            if(in_array('shop',$cms->license['modules'])) {  
            
                // eshop pages
                $eshop_pages=array();
                $eshop_set=get_option(MWS_OPTION_SHOP_SETTING);
    
                if(isset($eshop_set['home_page']) && $eshop_set['home_page']) {
                    //eshop home
                    $eshop_pages[]=get_page($eshop_set['home_page']);
                    $exclude[]=$eshop_set['home_page'];
                }
                if(isset($eshop_set['order_page']) && $eshop_set['order_page']) {
                    //eshop order
                    $eshop_pages[]=get_page($eshop_set['order_page']);
                    $exclude[]=$eshop_set['order_page'];
                }
                
                if(!empty($eshop_pages)) {
                    $page_list[$i]=array(
                        'name'=>'Eshop', 
                        'type'=>'shop', 
                        'del'=>false, 
                        'copy'=>false, 
                        'pages'=>$eshop_pages
                    );
                    $i++;
                
                } 
                
                // eshop categories
                
                $page_list[$i]=array(
                    'name'=>__('Kategorie eshopu','cms_ve'), 
                    'type'=>'shop',
                    'taxonomy'=>'eshop_category', 
                    'copy'=>false, 
                    'pages'=>get_categories( array('taxonomy' => 'eshop_category', 'hide_empty'=>0, 'parent'=>0 ) )
                );
                $i++;
                
                //eshop products
                $page_list[$i]=array(
                    'name'=>__('Produkty eshopu','cms_ve'),
                    'type'=>'shop', 
                    'copy'=>false, 
                    'pages'=>get_posts(array('post_type'=>'mwproduct','posts_per_page'=>-1))
                );
                $i++;
    
                $tabs['shop']=__('Eshop','cms_ve');        
            }
         
            // Campaigns
            if (in_array('mioweb', $cms->license['modules'])) {
                $campaigns = get_option('campaign_basic');
                $campaign_pages = get_pages(array('meta_key' => 'mioweb_campaign', 'post_status' => 'publish,private,draft,future,pending', 'parent' => '0'));
                if (isset($campaigns['campaigns']) && !empty($campaigns['campaigns'])) {
                    foreach ($campaigns['campaigns'] as $campaign_id => $campaign) {
                        $add_pages = array();
                        foreach ($campaign_pages as $page) {
                            $meta = get_post_meta($page->ID, 'mioweb_campaign', true);
                            if (isset($meta['campaign']) && $meta['campaign'] == $campaign_id) {
                                $add_pages[] = $page;
                                $exclude[] = $page->ID;
                            }
                        }

                        $page_list[$i] = array('name' => $campaign['name'], 'type' => 'campaign', 'pages' => $add_pages);
                        $i++;
                    }
                }

                $tabs['campaign'] = __('Kampaně', 'cms_ve');
            }

            // Member
            if (in_array('member', $cms->license['modules'])) {
                $members = get_option('member_basic');
                $member_pages = get_pages(array('meta_key' => 'page_member', 'post_status' => 'publish,private,draft,future,pending', 'parent' => '0'));
                if (isset($members['members']) && !empty($members['members'])) {
                    foreach ($members['members'] as $member_id => $member) {
                        $add_pages = array();
                        if (isset($member['login']) && $member['login']) {
                            $page = get_page($member['login']);
                            $meta = get_post_meta($page->ID, 'page_member', true);
                            if (!isset($meta['member_page']) || $meta['member_section']['section'] != $member_id) {  // add login only if not member page
                                $add_pages[] = $page;
                                $exclude[] = $page->ID;
                            }
                        }
                        foreach ($member_pages as $page) {
                            $meta = get_post_meta($page->ID, 'page_member', true);
                            if (isset($meta['member_page']) && $meta['member_section']['section'] == $member_id) {
                                $add_pages[] = $page;
                                $exclude[] = $page->ID;
                            }
                        }
                        $page_list[$i] = array('name' => $member['name'], 'type' => 'member', 'pages' => $add_pages);
                        $i++;
                    } 
                }
                $tabs['member'] = __('Členské sekce', 'cms_member');

            }   

            // Pages
            $page_list[0] = array(
                'name'=>__('Web','cms_ve'), 
                'pages'=>get_pages(array('post_status'=>'publish,private,draft,future,pending','parent'=>'0','exclude'=>implode(',',$exclude))),
                'type'=>'web'
            );
            
            $tabs['all']=__('Vše','cms_ve'); 
        
            // Write tabs
            echo '<ul class="ve_page_selector_tabs">';
            foreach ($tabs as $tab_id => $tab_name) {
                echo '<li><a ' . ($tab_id == $current_tab ? 'class="active"' : '') . ' data-target="' . $tab_id . '" href="#">' . $tab_name . '</a></li>';
            }
            echo '<li class="ve_page_search_container"><input id="ve_page_search" autocomplete="off" type="text" name="page_search" /></li>';
            echo '</ul>';


            ksort($page_list);
            // Write page select
            echo '<div class="cms_clear"></div>';
            echo '<div id="ve_page_list">';
            $level=0;
            echo '<a class="ve_page_list_home" href="'.get_home_url().'">'.__('Úvodní stránka','cms_ve').'</a>';
            foreach ( $page_list as $list_id=>$list ) {
                $copy=(isset($list['copy']) && !$list['copy'])? false : true;
                $del=(isset($list['del']) && !$list['del'])? false : true;
                $type=(isset($list['taxonomy']) && $list['taxonomy'])? $list['taxonomy'] : 'page';
                echo '<div class="ve_page_selector_list ve_psl_all ve_psl_'.$list['type'].' '.($current_tab!=$list['type']?'ve_nodisp':'').'">';
                if(isset($list['name'])) echo '<a data-target="'.$list_id.'" class="ve_page_list_name ve_pln_close" href="#">'.$list['name'].'</a>';
                echo '<ul class="ve_page_list_'.$list_id.'">';
                foreach ( $list['pages'] as $page ) {          
                    if($type=='page') echo $this->print_ps_page($page, $level, $copy, $del);  
                    else echo $this->print_ps_category($page, $level, $type);              	
                }
                echo '</ul></div>';
            }
            echo '<div id="ve_pagelist_empty_search">'.__('Nebyla nalezena žádná stránka','cms_ve').'</div>';
            echo '</div>';
            ?>
        </div>
        <?php
    }
    function get_ps_subpages($id, $level, $copy, $del) {
        $pages = get_pages(array('post_status'=>'publish,private,draft,future,pending','parent'=>$id));
        $content='';
        if($pages) {
            $content.='<ul class="ve_ps_subpages">';
            foreach($pages as $page) {
                $content.=$this->print_ps_page($page, $level+1, $copy, $del);
            }
            $content .= "</ul>";
        }
        return $content;
    }
    
function print_ps_page($page, $level, $copy=true, $del=true) {
    $cur=($this->post_id==$page->ID)?true:false;
    $option = '<li><div class="ve_page_item_container"><a class="ve_page_item '.($cur?'ve_page_item_current':'').'" title="' . get_page_link( $page->ID ) . '" href="' . get_page_link( $page->ID ) . '"  '.(($page->ID==$this->post_id)? 'class="selected"' : '').'><span class="ve_page_item_title">';
              	$option .= ' '.(($page->post_title)? $page->post_title : __("(bez názvu)",'cms_ve'));  
                $option .= post_password_required($page->ID)? ' ('.__('chráněná heslem','cms_ve').')' : ""; 
                $option .= ($page->post_status=="draft")? ' ('.__('draft').')' : "";        
                $option .= '</span>';
                $option .= ($page->post_status=="future")? $this->print_page_icon('time',__('Naplánované','cms_ve')) : "";    
                $option .= $this->get_page_icons($page->ID);   
              	$option .= '</a>';
                if($del) {
                  if($cur) $option .= '<a class="ve_ps_delete ve_delete_page" href="?ve_delete_page='.$page->ID.'" title="'.__("Smazat stránku",'cms_ve').'"></a>'; 
                  else $option .= '<a class="ve_ps_delete ve_delete_page_ajax" data-id="'.$page->ID.'" href="#" title="'.__("Smazat stránku",'cms_ve').'"></a>'; 
                }
                if($copy) $option .= '<a class="ve_ps_copy create-page-copy" data-id="'.$page->ID.'" href="#" title="'.__("Duplikovat stránku",'cms_ve').'"></a></div>';
                $option .= $this->get_ps_subpages($page->ID, $level+1, $copy, $del);
                $option .= '</li>';
              	return $option;
}
function print_ps_category($page, $level, $taxonomy) {
    $category = get_queried_object();       
    $cur=($category && $category->term_id==$page->term_id)?true:false;
    $option = '<li><div class="ve_page_item_container"><a class="ve_page_item '.($cur?'ve_page_item_current':'').'" title="' . get_term_link( intval($page->term_id),$taxonomy ) . '" href="' . get_term_link( intval($page->term_id),$taxonomy ) . '"  '.(($cur)? 'class="selected"' : '').'><span class="ve_page_item_title">';
              	$option .= ' '.(($page->name)? $page->name : __("(bez názvu)",'cms_ve'));            
              	$option .= '</span></a></div>';
                $option .= $this->get_ps_subcategories($page->term_id, $level+1, $taxonomy);
                $option .= '</li>';
              	return $option;
}
function get_ps_subcategories($id, $level, $taxonomy) {
    $pages = get_categories( array('taxonomy' => $taxonomy, 'hide_empty'=>0, 'child_of'=>$id ) );
    $content='';
    if($pages) {
        $content.='<ul class="ve_ps_subpages">';
        foreach($pages as $page) {
            $content.=$this->print_ps_category($page, $level+1, $taxonomy);
        }
        $content.="</ul>";
    }
    return $content;
}

function get_page_icons($id) {
  $icons='';
  
  // member
  if($member = get_post_meta($id,'page_member',true)) {
    if(isset($member['member_page'])) {
        $icons.=$this->print_page_icon('member',__('Členská stránka','cms_ve'));
        if(isset($member['evergreen_datetime']) && isset($member['evergreen_datetime']['date']) && $member['evergreen_datetime']['date']) 
            $icons.=$this->print_page_icon('time',__('Evergreen','cms_ve').': '.$member['evergreen_datetime']['date']);
        else if(isset($member['evergreen']) && $member['evergreen']) 
            $icons.=$this->print_page_icon('time',__('Evergreen','cms_ve').': '.$member['evergreen'].' '.__('dní','cms_ve'));
    }
  }
  // redirect
  if($redirect = get_post_meta($id,'page_redirect',true)) {
    if(isset($redirect['redirect_url']) && !isset($redirect['redirect_url']['use_url']) && isset($redirect['redirect_url']['page']) && $redirect['redirect_url']['page']) {
        $icons.=$this->print_page_icon('redirect',__('Přesměrování','cms_ve').': '.get_permalink($redirect['redirect_url']['page']));    
    }
    else if(isset($redirect['redirect_url']) && isset($redirect['redirect_url']['use_url']) && $redirect['redirect_url']['link']) {
        $icons.=$this->print_page_icon('redirect',__('Přesměrování','cms_ve').': '.$redirect['redirect_url']['link']);        
    }
  }
  return $icons;
}
function print_page_icon($icon,$text="") {
  $icons='<div class="ve_page_list_item_icon">';
  $icons.=file_get_contents(get_template_directory() ."/modules/visualeditor/images/pages_icons/".$icon.".svg", true);
  if($text) $icons.='<span>'.$text.'</span>';
  $icons.='</div>';
  return $icons;
}

    function editor_top_panel()
    {
        global $cms, $current_user, $post;
        ?>
        <div id="ve_editor_top_panel">
            <ul class="ve_menu">
                <li class="ve_etp_title"></li>
                <?php
                foreach ($this->top_panel_menu as $menu) {
                    $url = (isset($menu['url'])) ? $menu['url'] : '#';
                    $class = ($url == '#') ? 've_prevent_default ' : '';
                    $class = ($this->modul_type == $menu['id']) ? 'current_top_menu ' : '';
                    echo '<li class="ve_top_menu_' . $menu['id'] . '"><a class="' . $class . '" href="' . $url . '">' . $menu['title'] . '</a>' . $menu['submenu'] . '</li>';
                }
                ?>
                <li class="ve_top_menu_setting">
                    <a class="open-setting" data-setting="web_option" title="<?php echo __('Nastavení', 'cms_ve'); ?>"
                       href="#"><?php echo __('Nastavení', 'cms_ve'); ?></a>
                    <ul>
                        <?php
                        foreach ($cms->subpages as $page) {
                            if ($page['parent_slug'] == $cms->subpages['web_option']['parent_slug'] && $page['menu_slug'] != 've_option' && $page['menu_slug'] != 've_popups')
                                echo '<li><a class="open-setting" data-type="group" data-setting="' . $page['menu_slug'] . '" title="' . $page['menu_title'] . '" href="#">' . $page['menu_title'] . '</a></li>';
                        }
                        ?>
                        <li><a class="open-install-web" title="<?php echo __('Instalace / Šablony webů', 'cms_ve'); ?>"
                               href="#"><?php echo __('Instalace / Šablony webů', 'cms_ve'); ?></a></li>
                        <li><a class="open-import-web" title="<?php echo __('Import / Export webů','cms_ve'); ?>" href="#"><?php echo __('Import / Export webů','cms_ve'); ?></a></li>
                        <li>
                            <a href="<?php echo admin_url('options-general.php'); ?>"><?php echo __('Nastavení wordpressu', 'cms_ve'); ?></a>
                        </li>
                    </ul>


                </li>
                <li class="ve_etp_help">
                    <a target="_blank" class="ve_etp_help_link" title="<?php echo __('Nápověda', 'cms_ve'); ?>"
                       href="http://napoveda.mioweb.cz/">?</a>
                    <ul>
                        <li><a class="start_intro_tutorial" data-tut="start"
                               href="#"><?php echo __('Seznámení', 'cms_ve'); ?></a></li>
                        <li><a target="_blank"
                               href="http://napoveda.mioweb.cz/"><?php echo __('Nápověda', 'cms_ve'); ?></a>
                        </li>
                        <li><a target="_blank"
                               href="https://www.mioweb.cz/member/dotazy/"><?php echo __('Podpora', 'cms_ve'); ?></a>
                        </li>
                    </ul>
                </li>
                <li class="ve_etp_wp">
                    <a class="ve_etp_wp_link" title="<?php echo __('Wordpressová administrace', 'cms_ve'); ?>"
                       href="<?php echo admin_url(); ?>"></a>
                    <?php
                    if (is_page() || (is_home() && !is_front_page())) {
                        edit_post_link(__('Upravit stránku', 'cms_ve'), '<ul><li>', '</li></ul>', $this->post_id);
                    } else if (is_tag()) {
                        edit_tag_link('Upravit štítek', '<ul><li>', '</li></ul>');
                    } else if (is_category()) {
                        edit_term_link(__('Upravit kategorii', 'cms_ve'), '<ul><li>', '</li></ul>');
                    } else if (is_single()) {
                        edit_post_link(__('Upravit příspěvek', 'cms_ve'), '<ul><li>', '</li></ul>', $this->post_id);
                    } else if (is_author()) {
                        echo '<ul><li><a href="' . home_url() . '/wp-admin/user-edit.php?user_id=' . $post->post_author . '">' . __('Upravit autora', 'cmes_ve') . '</a></li></ul>';
                    }
                    ?>
                </li>
                </li>
                <?php
                // for smartselling icon
                do_action('ve_etp_right_icons');
                ?>
                <li class="ve_etp_user">
                    <a href="<?php echo admin_url('profile.php'); ?>">
                        <?php echo __('Uživatel', 'cms_ve'); ?>: <?php echo $current_user->user_nicename; ?>
                        <?php echo get_avatar($current_user->ID, 15); ?>
                    </a>
                    <ul>
                        <li><a target="_blank"
                               href="https://mioweb.cz/member"><?php echo __('Můj MioWeb', 'cms_ve'); ?></a></li>
                        <li>
                            <a href="<?php echo admin_url('profile.php'); ?>"><?php echo __('Můj profil ve wordpressu', 'cms_ve'); ?></a>
                        </li>
                        <li><a href="<?php echo wp_logout_url(); ?>"><?php echo __('Odhlásit', 'cms_ve'); ?></a></li>
                    </ul>
                </li>
                <li class="new_version_info">
                    <?php
                    $theme = basename(dirname(dirname(dirname(__FILE__))));
                    $wp_info = get_option('_site_transient_update_themes');
                    $mw_info = get_option('mioweb_update_info');
                    if (isset($wp_info->response[$theme]) || isset($mw_info['extend'])) {
                        echo '<a class="mioweb_new_version_info" target="_blank" href="#" title="' . __('Informace o nové verzi MioWebu', 'cms_ve') . '">' . __('Nová verze MioWebu', 'cms_ve') . '</a>';
                        $this->new_version_popup($mw_info);
                    }
                    ?>
                </li>
            </ul>

        </div>
        <style>body {
                margin-top: 40px !important;
            }

            #ve_editor_panel {
                top: 40px !important;
            }</style>
        <?php


    }

    function new_version_popup($info)
    {
        ?>
        <div class="cms_nodisp mioweb_new_version_info_popup">
            <div class="mioweb_new_version_info_popup_content">
                <div class="mioweb_update_info">
                    <h2><?php echo __('K dispozici je nová verze MioWebu.', 'cms_ve'); ?></h2>
                    <?php if (isset($info['extend'])) { ?>
                        <p><?php echo __('Aktualizaci nelze provést. Vypršelo obodobí, po které byly aktualizace a podpora k dispozici.<br />Aby bylo možné aktualizace stáhnout, je potřeba toto období prodloužit.', 'cms_ve'); ?></p>
                        <a class="cms_button" target="_blank"
                           href="https://mioweb.cz/member/objednavka-prodlouzeni-podpory/"><?php echo __('Prodloužit podporu a aktualizace o rok', 'cms_ve'); ?></a>
                    <?php } else { ?>
                        <p><?php echo __('Po kliknutí na tlačítko níže se dostanete na stránku s aktualizacemi. Ve spodní části stránky je seznam šablon. Zaškrtněte MioWeb20 a klikněte na tlačítko „Aktualizovat šablonu“.', 'cms_ve'); ?></p>
                        <a class="cms_button" target="_blank"
                           href="<?php echo admin_url('update-core.php#update-themes-table'); ?>"><?php echo __('Aktualizovat MioWeb', 'cms_ve'); ?></a>
                    <?php } ?>
                </div>
                <div class="mioweb_update_news">
                    <?php
                    if (count($info['news']) > 1) echo '<h2 class="maintitle">' . __('Seznam novinek', 'cms_ve') . '</h2>';
                    foreach ($info['news'] as $ver => $new) {
                        ?>
                        <div class="mioweb_update_version">
                            <h2>MioWeb <?php echo $ver; ?></h2>
                            <small><?php echo __('Vydáno', 'cms_ve'); ?><?php echo date("d. m. Y", strtotime($new['date'])); ?></small>
                            <div class="mioweb_update_version_text"><?php echo $new['text']; ?></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

            </div>
        </div>
        <?php
    }

    function create_web_menu()
    {
        return '<ul>
    <li><a class="create-new-page" data-type="web" title="' . __('Vytvořit novou stránku', 'cms_ve') . '" href="#">' . __('Nová stránka', 'cms_ve') . '</a></li>
    <li><a class="open-setting" data-type="group" data-setting="ve_popups" title="' . __('Pop-upy webu', 'cms_ve') . '" href="#">' . __('Pop-upy webu', 'cms_ve') . '</a></li>
    <li><a class="open-setting" data-type="group" data-setting="ve_option" title="' . __('Vzhled webu', 'cms_ve') . '" href="#">' . __('Vzhled webu', 'cms_ve') . '</a></li>
  </ul>';
    }

    function window_editor_panel()
    {
        global $cms;
        $hid_panel = (isset($_COOKIE['ve_hidden_panel'])) ? $_COOKIE['ve_hidden_panel'] : 0;
        $hid_features = (isset($_COOKIE['ve_hidden_features'])) ? $_COOKIE['ve_hidden_features'] : 0;

        ?>
        <div id="ve_editor_window_panel">

            <ul>
                <?php if ($this->window_editor_setting['type'] == 'cms_popup') { ?>
                    <li>
                        <a class="ve_open_page_setting ve_window_setting_ico"
                           title="<?php echo __('Nastavení', 'cms_ve') ?>" data-setid="popup_set"
                           data-id="<?php echo $this->window_editor_setting['id']; ?>"
                           href="#"><?php echo __('Nastavení', 'cms_ve') ?></a>
                    </li>
                <?php } ?>
                <li><a class="ve_change_template_but" title="<?php echo __('Změnit šablonu', 'cms_ve') ?>"
                       data-id="<?php echo $this->window_editor_setting['id']; ?>"
                       data-type="<?php echo $this->window_editor_setting['type']; ?>"
                       href="#"><?php echo __('Šablona', 'cms_ve') ?></a></li>
            </ul>

            <form id="ve_save_post_form" action="" method="post">
                <a class="sh-editor-features <?php echo ($hid_features) ? "show-editor-features" : "hide-editor-features"; ?>"
                   href="#"><?php echo ($hid_features) ? __("Zobrazit", 'cms_ve') : __("Skrýt", 'cms_ve'); ?> <?php echo __("ovládání", 'cms_ve'); ?></a>
                <input id="ev_post_id" type="hidden" name="post_id" value="<?php echo $this->post_id; ?>"/>
                <input id="ve_page_type" type="hidden" name="ve_page_type" value="<?php echo $this->page_type; ?>"/>
                <button class="cms_button cms_close_lightbox_window" href="#" data-target="cms_lightbox_window_editor"
                        title=""></button>
                <button type="submit" class="ev_save_page cms_button" data-status="publish" data-ostatus="publish"
                        id="ev_save_page"><?php echo __("Uložit", 'cms_ve'); ?> </button>
            </form>
            <input type="hidden" id="edited_page" autocomplete="off"
                   value="<?php echo (isset($_SESSION['ve_layer_autosave'][$this->post_id])) ? 1 : $this->edited_page;
                   unset($_SESSION['ve_layer_autosave'][$this->post_id]); ?>"/>

        </div>
        <style>body {
                margin-top: 50px !important;
            }</style>
        <?php
    }


    /* Global setting
***************************************************************************** */

    function open_global_setting()
    {
        global $cms, $wpdb;

        if ($_POST['edited']) $wpdb->update($wpdb->prefix . "ve_posts_layer", array('vpl_layer' => $this->code($this->create_post_layer())), array('vpl_post_id' => $_POST['post_id']));

        //print_r($cms->page_set_groups);
        $slug = $_POST['setting'];  
        $subpages = array();
        if (count($cms->subpages)) {
            foreach ($cms->subpages as $page) {
                if ($page['parent_slug'] == $cms->subpages[$slug]['parent_slug'])
                    $subpages[] = $page;
                if ($slug == $page['menu_slug'])
                    $currentp = $page['parent_slug'];
            }
        }

        echo '<div class="subpage_nav"><ul class="cms_tabs">';
        $i = 1;
        foreach ($subpages as $page) {
            $class = ($i == 1) ? "active" : "";
            echo '<li class="ve_global_setting_tab"><a href="#ve_global_setting_' . $page['menu_slug'] . '" data-group="ve_global_setting" class=' . $class . '>' . $page['menu_title'] . '</a></li>';
            $i++;
        }
        echo '</ul></div>';
        $i = 1;
        foreach ($subpages as $page) {
            echo '<div id="ve_global_setting_' . $page['menu_slug'] . '" class="ve_global_setting_container cms_tab_content-' . $i . '">';
            echo '<ul class="cms_tabs ve_global_setting_subtabs ' . $page['menu_slug'] . '_tab">';
            $j = 1;
            foreach ($cms->page_set_groups[$page['menu_slug']] as $value) {
                $class = ($j == 1) ? "active" : "";
                echo '<li><a href="#' . $value['id'] . '" data-group="' . $page['menu_slug'] . '" class=' . $class . '>' . $value['name'] . '</a></li>';
                $j++;
            }
            echo '</ul>';
            $j = 1;
            foreach ($cms->page_set_groups[$page['menu_slug']] as $value) {
                echo '<div id="' . $value['id'] . '" class="' . $page['menu_slug'] . '_container ve_global_setting_subcontainer cms_setting_block_content cms_tab_content cms_tab_content-' . $j . '  ' . $value['id'] . '_container"">';
                $meta = get_option($value['id']);
                write_meta($cms->page_set[$value['id']], $meta, $value['id'], $value['id']);
                echo '</div>';


                $j++;
            }
            echo '</div>';
            $i++;
        }


        wp_nonce_field('ve_save_global_setting_nonce', 've_save_global_setting_nonce');
        ?>
        <input type="hidden" name="ve_save_global_setting" value="<?php echo $slug; ?>"/>
        <?php
        die();
    }

// generate single setting
    function open_global_single_setting()
    {
        global $cms, $wpdb;

        if ($_POST['edited']) $wpdb->update($wpdb->prefix . "ve_posts_layer", array('vpl_layer' => $this->code($this->create_post_layer())), array('vpl_post_id' => $_POST['post_id']));

        $setting=explode(":",$_POST['setting']);
    
        $slug=$setting[0];
        if(isset($setting[1])) $a_id=$setting[1];

        echo '<ul class="cms_tabs ve_global_setting_subtabs ' . $slug . '_tab">';
        $j = 1;
        foreach ($cms->page_set_groups[$slug] as $value) {
            $class = ($j == 1) ? "active" : "";
            echo '<li><a href="#' . $value['id'] . '" data-group="' . $slug . '" class=' . $class . '>' . $value['name'] . '</a></li>';
            $j++;
        }
        echo '</ul>';
        $j = 1;
        foreach ($cms->page_set_groups[$slug] as $value) {
            echo '<div id="' . $value['id'] . '" class="' . $slug . '_container ve_global_setting_subcontainer cms_setting_block_content cms_tab_content cms_tab_content-' . $j . '  ' . $value['id'] . '_container">';
            
            $tagname=$value['id'];
            $tagid=$value['id'];
            
            $meta = get_option($value['id']);
            
            // for array member setting
            if(isset($a_id) && $a_id!='') {
                $meta=$meta['members'][$a_id];
                $tagid.='_members_'.$a_id;
                $tagname.='[members]['.$a_id.']';
                echo '<input type="hidden" name="save_single_members" value="1" />';
            }
            
            write_meta($cms->page_set[$value['id']],$meta,$tagname,$tagid); 

            echo '</div>';

            $j++;
        }

        wp_nonce_field('ve_save_global_setting_nonce', 've_save_global_setting_nonce');
        ?>
        <input type="hidden" name="ve_save_global_setting_group" value="<?php echo $slug; ?>"/>
        <?php
        die();
    }

// generate single setting single tab
    function open_global_single_setting_tab()
    {
        global $cms, $wpdb;

        if ($_POST['edited']) $wpdb->update($wpdb->prefix . "ve_posts_layer", array('vpl_layer' => $this->code($this->create_post_layer())), array('vpl_post_id' => $_POST['post_id']));

        $slug = explode(":", $_POST['setting']);
        foreach ($cms->page_set_groups[$slug[0]] as $value) {
            if ($value['id'] == $slug[1]) {
               
                echo '<div id="'.$value['id'].'" class="'.$slug[0].'_container cms_setting_block_content">'; 
             
                $tagname=$value['id'];
                $tagid=$value['id'];

                $meta = get_option($value['id']);
                
                // for array member setting
                if(isset($slug[2]) && $slug[2]!=='') {
                    $meta=$meta['members'][$slug[2]];
                    $tagid.='_members_'.$slug[2];
                    $tagname.='[members]['.$slug[2].']';
                    echo '<input type="hidden" name="save_single_members" value="1" />';
                }
                
                write_meta($cms->page_set[$value['id']],$meta,$tagname,$tagid); 

                echo '</div>';
            }

        }

        wp_nonce_field('ve_save_global_setting_nonce', 've_save_global_setting_nonce');
        ?>
        <input type="hidden" name="ve_save_global_setting_single" value="<?php echo $slug[1]; ?>"/>
        <?php
        die();
    }

    /* Page
***************************************************************************** */
    /* page setting */
    function open_page_setting()
    {
        global $cms;
        $post = get_post($_POST['post_id']);
        $set_id = $_POST['set_id'];

        if ($_POST['edited']) $_SESSION['ve_layer_autosave'][$_POST['post_id']] = $this->code($this->create_post_layer());

        if ($set_id == 'page_set') {
            ?>
            <div class="cms_setting_block_content">
            <div class="float-setting" style="width:74%">
                <div class="set_form_row">
                    <label class="label"><?php echo __('Název stránky', 'cms_ve'); ?></label>
                    <input class="cms_text_input cms_text_input_s required" type="text" id="ve_post_title"
                           name="ve_post_title" value="<?php echo htmlspecialchars($post->post_title) ?>"/>
                </div>
                <div class="set_form_row">
                    <label class="label"><?php echo __('URL název stránky', 'cms_ve'); ?></label>
                    <input class="cms_text_input cms_text_input_s required cms_check_url" type="text" id="ve_post_url"
                           name="ve_post_url" value="<?php echo $post->post_name ?>"/>
                </div>
                <div class="set_form_row">
                    <label class="label"><?php echo __('Popisek stránky', 'cms_ve'); ?></label>
                    <textarea class="cms_text_textarea" type="text" id="ve_post_excerpt" name="ve_post_excerpt"><?php echo $post->post_excerpt ?></textarea>
                </div>
            </div>
            <div class="cms_setting_blog_thumbnail">
                <div class="set_form_row">
                    <label class="label"><?php echo __('Náhledový obrázek', 'cms_ve'); ?></label>
                    <?php 
                    $post_thumbnail_id = get_post_thumbnail_id($_POST['post_id']);
                    $post_thumbnail_url = wp_get_attachment_image_src( $post_thumbnail_id, 'medium' );
                    $post_thumbanil=array(
                        'image'=>$post_thumbnail_url[0],
                        'imageid'=>$post_thumbnail_id,
                    );
                    echo cms_generate_field_image('ve_post_thumbnail', 've_post_thumbnail', $post_thumbanil); 
                    ?>
                </div>
            </div>
            <div class="cms_clear"></div>
            
            <?php
            if ($post->post_type == "page") { ?>
                <div class="set_form_row">
                    <div class="float-setting">
                        <label class="label"><?php echo __('Nadřazená stránka', 'cms_ve'); ?></label>
                        <?php
                        $pages = get_pages(array('post_status' => 'publish', 'exclude' => $_POST['post_id'],));
                        $cms->select_page($pages, $post->post_parent, 've_post_parent_id', 've_post_parent_id'); ?>
                    </div>
                    <div class="float-setting">
                        <label class="label"><?php echo __('Pořadí stránky', 'cms_ve'); ?></label>
                        <input class="cms_text_input cms_text_input_s" type="text" id="ve_post_menu_order"
                               name="ve_post_menu_order" value="<?php echo $post->menu_order ?>"/>
                    </div>
                    <div class="cms_clear"></div>
                </div>
            <?php }
            if (!isset($_POST['single'])) { ?>
                <div class="set_form_row">
                    <label class="label"><?php echo __('Nastavení stránky', 'cms_ve'); ?></label>
                    <?php
                    show_page_set($cms->p_set[$set_id], $set_id, $post);
                    ?>
                </div>
                <?php
            }
        } else show_page_set($cms->p_set[$set_id], $set_id, $post);


        ?>
        </div>
        <input type="hidden" name="ve_save_page_setting" value="1"/>
        <input type="hidden" name="set_id" value="<?php echo $set_id; ?>"/>
        <input type="hidden" name="post_id" value="<?php echo $_POST['post_id']; ?>"/>
        <?php
        die();
    }

    /* single page setting */
    function open_page_single_setting()
    {
        global $cms;
        $post = get_post($_POST['post_id']);
        $set_id = $_POST['set_id'];
        $tab_id = $_POST['tab_id'];

        if ($_POST['edited']) $_SESSION['ve_layer_autosave'][$_POST['post_id']] = $this->code($this->create_post_layer());
        ?>
        <div class="cms_setting_block_content">
            <?php
            show_page_set($cms->p_set[$set_id], $set_id, $post, $tab_id);
            ?>
            <input type="hidden" name="ve_save_page_single_setting" value="1"/>
            <input type="hidden" name="tab_id" value="<?php echo $tab_id; ?>"/>
            <input type="hidden" name="post_id" value="<?php echo $_POST['post_id']; ?>"/>
        </div>
        <?php
        die();
    }

    /* create page */
    function create_page()
    {
        $pages = get_pages(array('post_status' => array('publish', 'draft')));
        if (isset($_POST['copy'])) {
            $post = get_post($_POST['post_id']);
        }
        ?>
        <div class="cms_setting_block_content">
                 
            <div class="cms_setting_block_content">
                <div class="float-setting" style="width:74%">
                    <div class="set_form_row">
                    <label class="label"><?php echo __('Název nové stránky', 'cms_ve'); ?></label>
                    <input class="cms_text_input required" type="text" id="ve_post_title"
                           name="ve_post_title" value="<?php if (isset($_POST['copy'])) {
                        echo $post->post_title . '_kopie';
                    } ?>"/>
                </div>
                <div class="set_form_row">
                    <label class="label"><?php echo __('URL název stránky', 'cms_ve'); ?></label>
                    <input class="cms_text_input required cms_check_url" type="text" id="ve_post_url"
                           name="ve_post_url" value="<?php if (isset($_POST['copy'])) {
                        echo $post->post_name . '_kopie';
                    } ?>"/>
                </div>
                <div class="set_form_row">
                    <label class="label"><?php echo __('Popisek stránky', 'cms_ve'); ?></label>
                    <textarea class="cms_text_textarea" type="text" id="ve_post_excerpt" name="ve_post_excerpt"></textarea>
                </div>
            </div>
            <div class="cms_setting_blog_thumbnail">
                <div class="set_form_row">
                    <label class="label"><?php echo __('Náhledový obrázek', 'cms_ve'); ?></label>
                    <?php 
                    echo cms_generate_field_image('ve_post_thumbnail', 've_post_thumbnail', ''); 
                    ?>
                </div>
            </div>
            <div class="cms_clear"></div>            
            <?php

            // create member page
            if (isset($_POST['page_type']) && $_POST['page_type'] == 'member') {
                ?>
                <div class="set_form_row">
                    <label class="label"><?php echo __('Zařadit do členské sekce', 'cms_ve'); ?></label>
                    <?php
                    $members = get_option('member_basic');
                    $member_section = '';
                    $member_levels = array();
                    // if is copy
                    if (isset($_POST['copy'])) {
                        $page_member = get_post_meta($_POST['post_id'], 'page_member', true);
                        $member_section = $page_member['member_section']['section'];
                        $member_levels = isset($page_member['member_section'][$member_section]) ? $page_member['member_section'][$member_section]['levels'] : array();
                    }
                    if (isset($members['members'])) {
                        ?>
                        <select id="ve_post_member" class="member_select_member_level" name="ve_post_member">
                            <?php
                            foreach ($members['members'] as $id => $member) {
                                echo '<option value="' . $id . '" ' . ($id == $member_section ? 'selected="selected"' : '') . '>' . $member['name'] . '</option>';
                            }
                            ?>
                        </select>
                        <?php
                        $i = 1;
                        foreach ($members['members'] as $id => $member) {
                            if (isset($member['levels'])) { ?>

                                <div id="member_levels_container_<?php echo $id; ?>"
                                     class="member_levels_container <?php if (($member_section && $member_section == $id) || (!$member_section && 1 == $i)) echo 'member_levels_container_v'; ?>">
                                    <label class="label"><?php echo __('Zpřístupnit stránku pro členské úrovně','cms_ve'); ?>:</label>
                                    <?php
                                    foreach ($member['levels'] as $lid => $level) { ?>
                                        <div class="member_level_item">
                                            <input id="<?php echo 'member_input_' . $id . '_levels_' . $lid; ?>"
                                                   type="checkbox"
                                                   name="<?php echo 've_post_member_levels[' . $id . '][levels][' . $lid . ']'; ?>" <?php if (isset($member_levels[$lid])) echo 'checked="checked"'; ?> />
                                            <label for="<?php echo 'member_input_' . $id . '_levels_' . $lid; ?>">
                                                <?php echo $level['name']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                    <span
                                        class="cms_description"><?php echo __("Pokud zaškrtnete některou z členských úrovní, bude stránka přístupná pouze pro uživatele s danou členskou úrovní. Pokud nezaškrtnete nic, bude stránka přístupná pro všechny členy vybrané členské sekce.", 'cms_ve'); ?></span>
                                </div>
                                <?php
                            }
                            $i++;
                        }
                    }
                    ?>
                </div>
            <?php } // create campaign page
            else if (isset($_POST['page_type']) && $_POST['page_type'] == 'campaign' && !isset($_POST['copy'])) {
                ?>
                <div class="set_form_row">
                    <label class="label"><?php echo __('Zařadit do kampaně', 'cms_ve'); ?></label>
                    <?php
                    $campaigns = get_option('campaign_basic');
                    if (isset($campaigns['campaigns'])) {
                        ?>
                        <select id="ve_post_campaign" name="ve_post_campaign">
                            <?php
                            foreach ($campaigns['campaigns'] as $id => $campaign) {
                                echo '<option value="' . $id . '">' . $campaign['name'] . '</option>';
                            }
                            ?>
                        </select>
                        <?php
                    }
                    ?>
                </div>
            <?php } ?>


            <div class="set_form_row">
                <div class="float-setting">
                    <label class="label"><?php echo __('Nadřazená stránka', 'cms_ve'); ?></label>
                    <?php
                    global $cms;
                    $cms->select_page($pages, ((isset($_POST['copy'])) ? $post->post_parent : ''), 've_post_parent_id', 've_post_parent_id'); ?>
                </div>
                <div class="float-setting">
                    <label class="label"><?php echo __('Pořadí stránky', 'cms_ve'); ?></label>
                    <input class="cms_text_input cms_text_input_s" type="text" id="ve_post_menu_order"
                           name="ve_post_menu_order" value="0"/>
                </div>
                <div class="cms_clear"></div>
            </div>

            <?php if (!isset($_POST['copy'])) { ?>
                <div class="set_form_row">
                    <div class="label"><?php echo __('Šablona stránky', 'cms_ve'); ?></div>
                    <div class="ve_create_page_selector">
                        <?php $this->get_template_selector(); ?>
                    </div>
                    <input type="hidden" name="ve_create_page" value="1">
                </div>
            <?php } else { ?>
                <input type="hidden" name="ve_create_page_copy" value="<?php echo $_POST['post_id']; ?>">
                <?php
            }
            ?>
        </div>
        <?php
        die();
    }

    /* check url */
    function ve_check_url()
    {
        global $wpdb;
        $path = $_POST['url'];
        if ($_POST['parent']) $path = get_post($_POST['parent'])->post_name . '/' . $path;
        if (get_page_by_path($path)) echo '1';
        else echo '0';
        //echo $post_name_check = $wpdb->query( "SELECT post_name FROM $wpdb->posts WHERE post_name = '".$_POST['url']."' LIMIT 1" );
        die();
    }

    /* merge setting */
    function merge_setting($set1, $set2, $if = true)
    {
        if ($if && $set1) {
            foreach ($set1 as $key => $value) {
                if (is_array($value)) {

                    if ($key != 'background_image' || (isset($value['image']) && $value['image'])) {
                        foreach ($value as $val_key => $val) {
                            if ($val != "") $set2[$key][$val_key] = $val;
                        }
                    }

                } else if ($value != "") $set2[$key] = $value;
            }
        }
        return $set2;
    }

    /* templates */
    function get_template_file($file, $url = false, $directory = false)
    {
        global $cms;
        $temp = explode("/", $this->template['directory']);
        if (!$directory && isset($cms->p_templates[$temp[0]])) $directory = $cms->p_templates[$temp[0]]['path'] . $temp[1] . '/';
        else {
            $temp[0]='page';
            $temp[1]='1';
            $directory = $cms->p_templates[$temp[0]]['path'] . $temp[1] . '/';
        }
        if ($url) {
            return $this->get_template_url($temp[0]) . $directory . $file;
        } else {
            return $this->get_template_dir($temp[0]) . $directory . $file;
        }
    }

    function get_template_dir($temp)
    {
        global $cms;
        if (isset($cms->p_templates[$temp]['directory'])) return $cms->p_templates[$temp]['directory'];
        else return get_template_directory();
    }

    function get_template_url($temp)
    {
        global $cms;
        if (isset($cms->p_templates[$temp]['url'])) return $cms->p_templates[$temp]['url'];
        else return get_bloginfo('template_url');
    }

    function change_template()
    {

        $template = get_post_meta($_POST['post_id'], 've_page_template', true);

        ?>
        <div class="cms_setting_block_content">
            <input type="hidden" name="post_id" value="<?php echo $_POST['post_id']; ?>">
            <input type="hidden" name="post_type" value="<?php echo $_POST['post_type']; ?>">
            <input type="hidden" name="ve_change_template_action" value="1">
            <?php if ($_POST['post_type'] == 'page') { ?>


                <div class="set_form_row">
                    <label class="label"><?php echo __('Zachovat obsah', 'cms_ve'); ?></label>
                    <input id="keep_header" type="checkbox" name="keep_header" value="1"> <label
                        for="keep_header"><?php echo __('Zachovat stávající hlavičku', 'cms_ve'); ?></label>
                </div>
                <div>
                    <input id="keep_footer" type="checkbox" name="keep_footer" value="1"> <label
                        for="keep_footer"><?php echo __('Zachovat stávající patičku', 'cms_ve'); ?></label>
                </div>
                <div>
                    <input id="keep_appearance" type="checkbox" name="keep_appearance" value="1"> <label
                        for="keep_appearance"><?php echo __('Zachovat základní nastavení vzhledu (pozadí, font stránky...)', 'cms_ve'); ?></label>
                </div>
                <div>
                    <input id="keep_content" type="checkbox" name="keep_content" value="1"> <label
                        for="keep_content"><?php echo __('Zachovat stávající obsah', 'cms_ve'); ?></label>
                </div>

            <?php } ?>
            <div class="set_form_row">
                <label class="label"><?php echo __('Změnit šablonu', 'cms_ve'); ?></label>
                <?php
                $this->get_template_selector($_POST['post_id'], $_POST['post_type']);
                ?>
            </div>
        </div>
        <?php
        die();
    }

    function get_template_selector($post_id = 0, $post_type = 'page', $default_directory = 'page/1/')
    {
        global $cms;

        if ($post_id) $template = get_post_meta($post_id, 've_page_template', true);
        if (!isset($template) || !$template) $template = array('type' => $post_type, 'directory' => $default_directory);
        $temp = explode("/", $template['directory']);

        $allow_templates = array();
        foreach ($cms->p_templates as $key => $tmpl) {
            if (!isset($tmpl['cat']) && ((isset($tmpl['type']) && $tmpl['type'] == $post_type) || ($post_type == 'page' && !isset($tmpl['type'])))) $allow_templates[$key] = $tmpl;
        }
        if (count($allow_templates)) {
            if (isset($allow_templates[$temp[0]])) $current = $temp[0];
            else $current = $cms->p_templates[$temp[0]]['cat'];
            if (count($allow_templates) > 1) { ?>
                <ul id="tempate-cat-list">
                    <?php foreach ($allow_templates as $key => $tmpl) { ?>
                        <li class="template-cat-<?php echo $key; ?>">
                            <a data-id="<?php echo $key; ?>" href="#"
                               class="<?php if ($current == $key) echo 've_template_cat_active'; ?>">
                                <div class="icon"></div>
                                <?php echo $tmpl['name']; ?>
                                <span></span>
                            </a>
                        </li>
                        <?php
                    }
                    if ($_POST['action'] != 've_change_template') {
                        ?>
                        <li class="template-cat-import">
                            <a data-id="import" href="#">
                                <div class="icon"></div><?php echo __('Importovat', 'cms_ve'); ?><span></span>
                            </a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="cms_clear"></div>
            <?php } ?>
            <input type="hidden" name="ve_page_template[type]" value="<?php echo $post_type; ?>">
            <div class="cms_lightbox_content">
                <?php
                foreach ($allow_templates as $key => $tmpl) {
                    $dir = $this->get_template_dir($key) . $tmpl['path'];
                    $url = $this->get_template_url($key) . $tmpl['path'];
                    ?>
                    <div id="ve_template_selbox_<?php echo $key; ?>"
                         class="ve_template_selbox <?php if ($current == $key) echo 've_template_selbox_active'; ?>">
                        <?php
                        if (isset($tmpl['list']) && count($tmpl['list'])) {
                            foreach ($tmpl['list'] as $tmpl_category) {

                                if ($tmpl_category['name']) echo '<h2 class="ve_template_category_title">' . $tmpl_category['name'] . '</h2>';

                                foreach ($tmpl_category['list'] as $tmpl_template) {
                                    if (is_array($tmpl_template)) {
                                        $temp_dir = $this->get_template_dir($tmpl_template['cat']) . $cms->p_templates[$tmpl_template['cat']]['path'] . $tmpl_template['folder'] . '/';
                                        $temp_url = $this->get_template_url($tmpl_template['cat']) . $cms->p_templates[$tmpl_template['cat']]['path'] . $tmpl_template['folder'] . '/';

                                        $directory = $tmpl_template['cat'] . '/' . $tmpl_template['folder'] . '/';
                                    } else {
                                        $temp_dir = $dir . $tmpl_template . '/';
                                        $temp_url = $url . $tmpl_template . '/';

                                        $directory = $key . '/' . $tmpl_template . '/';
                                        
                                    }

                                    $template_data = implode('', file($temp_dir . 'template.php'));
                                    preg_match("| Template Title:(.*)|i", $template_data, $name);
                                    preg_match("| Template Description:(.*)|i", $template_data, $description);
                                    
                                    $language=get_locale();
                                    $thumb_name='thumb.jpg';
                                    if($language=='en_US') {
                                        if(file_exists($temp_dir.'thumb_en.jpg')) $thumb_name='thumb_en.jpg';
                                    }
                                    $lang_domain='cms_ve';
                                    if($key=='campaign') $lang_domain='cms_mioweb';
                                    if($key=='member') $lang_domain='cms_member';
                                    
                                    
                                    ?>
                                    <div id="ve_template_box_<?php echo $key . $tmpl_template; ?>"
                                         data-id="<?php echo $key . $tmpl_template; ?>"
                                         class="ve_template_box <?php if ($directory == $template['directory']) echo 've_template_box_select'; ?>">
                                        <img src="<?php echo $temp_url.$thumb_name; ?>"
                                             alt="<?php echo __(trim($description[1]), 'cms_ve'); ?>"/>
                                        <input id="sel_template_rad_<?php echo $key . $tmpl_template; ?>" type="radio"
                                               name="ve_page_template[directory]"
                                               value="<?php echo $directory; ?>" <?php if ($directory == $template['directory']) echo 'checked="checked"'; ?> >
                                        <h2><?php echo __(trim($name[1]), $lang_domain); ?></h2>
                                        <?php if(trim($description[1])) echo '<p>'.__(trim($description[1]), $lang_domain).'</p>'; ?>
                                        <span></span>
                                    </div>
                                    <?php

                                }
                                echo '<div class="cms_clear"></div>';
                            }
                        } else {
                            echo '<div class="ve_template_empty_dir">' . __('Tato kategorie neobsahuje žádné šablony.', 'cms_ve') . '</div>';
                        }
                        ?>
                        <div class="cms_clear"></div>
                    </div>
                    <?php
                }


                ?>
                <div id="ve_template_selbox_import" class="ve_template_selbox">
                    <h2 class="ve_template_category_title"><?php echo __('Naimportovat šablonu ze souboru:', 'cms_ve'); ?></h2>
                    <div class="ve_template_import_container">
                        <div
                            class="cms_info_box"><?php echo __('Nahrajte zip soubor obsahující exportovanou stránku z MioWebu.', 'cms_ve'); ?></div>
                        <input type="file" name="import_template_upload" multiple="false"/>
                    </div>
                </div>
            </div>


            <?php
        }
    }

    /* After login
*************************** */

    function after_login()
    {
        setcookie('ve_hidden_features', '0', time() + (60 * 60 * 24 * 365), "/");
        setcookie('ve_hidden_panel', '0', time() + (60 * 60 * 24 * 365), "/");
    }

    /* Mazání postu
************** */

    function delete_page_hook($pid)
    {
        global $wpdb;
        if ($wpdb->get_var($wpdb->prepare('SELECT vpl_post_id FROM ' . $wpdb->prefix . 've_posts_layer WHERE vpl_post_id = %d', $pid))) {
            return $wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 've_posts_layer WHERE vpl_post_id = %d', $pid));
        }
        return true;
    }

    /* Class functions
***************************************************************************** */

function generate_element_item($args) {

    $defaults=array(
        'style'=>'1',
        'cols'=>3,
        'cols_style'=>'',
        'hover_style'=>'',
        'link'=>'',
        'target'=>false,
        'imageid'=>'',
        'image'=>'',
        'thumb'=>'mio_columns_3',
        'title'=>'',
        'subtitle'=>'',
        'description'=>'',
        'price'=>'',
        'edit_button'=>'',
        'align'=>'',
        'hide_image'=>false,
        'image_hover'=>false,
        'image_hover_link'=>false,
        'image_hover_content'=>'',
        'tags'=>'',
        'img_col_size'=>'',
        'show_description'=>true,
    );
                    
    $args = wp_parse_args( $args, $defaults );
    
    $show_content=true;
    $show_price=true;
    
    $target='';
    if($args['target']) $target='target="_blank"';
    
    if($args['style']=='1') {
        $show_content=false;
    } else if($args['style']=='2' || $args['style']=='5') {
        $args['show_description']=false;
        $show_price=false;
    }
    
    $tags='';
    if($args['tags']) {
        $tags.='mw_tag_item';
        foreach($args['tags'] as $tag) 
            $tags.=' mw_tag_item_'.$tag;
    }
    
    $html_start_tag='div';
    $html_end_tag='div';
    if($args['link']) {
        $html_start_tag='a href="'.$args['link'].'" '.$target.' ';
        $html_end_tag='a';
    }
    
    $content='';
    $image_hover='';
    if($args['image_hover']) $image_hover='<'.(($args['image_hover_link'])?$html_start_tag:'div').' class="mw_element_item_image_hover"><div class="mw_element_item_image_hover_content">'.$args['image_hover_content'].'</div></'.(($args['image_hover_link'])?$html_end_tag:'div').'>';

    $image='<div class="ve_empty_image_container"></div>';
    if($args['imageid']) 
        $image=wp_get_attachment_image( $args['imageid'], $args['thumb']); 
    else if($args['image']) $image='<img src="' . $this->get_image_url($args['image']) . '" alt="' . $args['title'] . '" />';  
    
    $class = '';
    $class .= ($args['hover_style'])? 'image_hover_'.$args['hover_style'] : ''; // hover
    $class .= ($args['img_col_size'] && $args['style']=='6')? ' mw_element_item_image_1-'.$args['img_col_size'] : '';
                    
    $content.='<div class="mw_element_item '.$class.' '.$args['cols_style'].'col '.$args['cols_style'].'col-'.$args['cols'].' '.$tags.'">';
    if(!$args['hide_image']) $content.='<div class="mw_element_item_image_container"><'.$html_start_tag.' class="responsive_image mw_element_item_image_link">'. $image .'</'.$html_end_tag.'>'.$image_hover.'</div>';
    if($show_content) {
        $content.='<div class="mw_element_item_content '.(($args['align']=='center')? 've_center':'').'">';
        $content.='<div class="mw_element_item_title">';
        if($args['link']) $content.='<a href="'.$args['link'].'" '.$target.'>';
        $content.='<h3>' . $args['title'] . '</h3>';
        if($args['link']) $content.='</a>';
        if($args['subtitle']) $content.='<span class="mw_element_item_subtitle">' . $args['subtitle'] . '</span>';
        $content.='</div>';
        if($args['show_description'] && $args['description']) $content.='<div class="mw_element_item_description">' . $args['description'] . '</div>';
        if($show_price && $args['price']) $content.='<div class="mw_element_item_price title_element_container">' . $args['price'] . '</div>';
        $content.='</div>';
    }
    if($args['edit_button'])  $content.=$args['edit_button'];
    $content.='<div class="cms_clear"></div></div>';
    
    return $content;
    }

    function generate_slider_background($slides, $duration, $speed, $background_color = '#fff', $id = 'miocarousel_page_background')
    {
        wp_enqueue_style('ve_miocarousel_style');
        wp_enqueue_script('ve_miocarousel_script');
        $content = '';
        $styles = array();
        $styles[] = array(
            'styles' => array('background-color' => $background_color),
            'element' => '#' . $id,
        );
        if (is_array($slides)) {
            $content = '<div id="' . $id . '" class="miocarousel miocarousel_background" data-speed="' . $speed . '" data-duration="' . $duration . '" data-indicators="0"><div class="miocarousel-inner">';
            $i = 0;
            foreach ($slides as $slide) {
                $content .= '<div class="slide slide_' . $i . ' ' . (($i == 0) ? 'active' : '') . '"></div>';
                $styles[] = array(
                    'styles' => array('background_image' => array('image' => $slide['image']['image'], 'cover' => 1, 'repeat' => 'no-repeat', 'position' => '0 0')),
                    'element' => '#' . $id . ' .slide_' . $i,
                );
                $i++;
            }
            $content .= '</div></div>';
            $content .= $this->print_styles_array($styles);
        }
        return $content;
    }

    function print_form($element, $form, $css_id, $butstyle = '')
    {
        $datepicker=false;

        if (!isset($element['style']['button']['height'])) $element['style']['button']['height'] = '';

        $formstyle = $this->print_styles(array(
            'font' => $element['style']['form-font'],
            'background-color' => $element['style']['background'],
            'height' => $element['style']['button']['height'],
        ), $css_id . " .ve_form_row .ve_form_text, " . $css_id . "_form .ve_form_row .ve_form_text");

        $textstyle = $this->print_styles(array(
            'font' => $element['style']['form-font'],
        ), $css_id . " .ve_form_label");

        $button_text = (isset($element['style']['button_text'])) ? $element['style']['button_text'] : '';

        $fields = $form['fields'];

        if ($this->edit_mode) $content = '<form action="' . $form['url'] . '" method="post" class="ve_check_form ve_content_form ve_form_input_style_' . $element['style']['form-look'] . ' ve_form_style_' . $element['style']['form-style'] . '">';
        else $content = '<form action="" data-action="' . $form['url'] . '" method="post" class="ve_check_form ve_content_form ve_content_form_antispam ve_form_input_style_' . $element['style']['form-look'] . ' ve_form_style_' . $element['style']['form-style'] . '">';

        if (isset($element['style']['form-style']) && $element['style']['form-style'] == '2') {
            $content .= '<table><tr>';
            $tag = "td";
        } else $tag = "div";
        foreach ($fields as $key => $input) {

            $class = "";

            if ($input['required']) {
                $class .= " ve_form_required";
                $input['label'] .= "*";
            }
            if ($key == 'df_emailaddress'
                || isset($input['email'])
                || (isset($input['customfield_type']) && $input['customfield_type'] == 'email')
            ) {
                $class .= " ve_form_email";
                if (is_user_logged_in() && !$this->edit_mode) {
                    $current_user = wp_get_current_user();
                    $input['content'] = $current_user->user_email;
                }
                if (isset($_GET['email']))
                    $input['content'] = $_GET['email'];
            }
            $errorm = (isset($input['errormessage']) && $input['errormessage']) ? 'data-errorm="' . $input['errormessage'] . '"' : '';

            if (isset($input['customfield_type']) && $input['customfield_type'] == 'hidden')
                $hidden_field = true;
            else
                $hidden_field = false;

            //content from url
            if (isset($_GET[$input['fieldname']])) $input['content'] = $_GET[$input['fieldname']];

            if (!$hidden_field) {
                $form_row_class='ve_form_row_' . $input['fieldname'];
                if(isset($input['customfield_type']) && $input['customfield_type']) $form_row_class.=' ve_form_row_type_' . $input['customfield_type'];
                $content .= '<' . $tag . ' class="ve_form_row ' . $form_row_class . '">';
            }

            if (isset($element['style']['form-labels']) && $element['style']['form-labels'] == "2" && $input['label'] && (!isset($input['customfield_type']) || $input['customfield_type']!='bool')) {
                $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
                $input['label'] = '';
            }

            if (!isset($input['customfield_type'])) {
                if ($input['defaultfield'] == 'notes')
                    $content .= '<textarea class="ve_form_text' . $class . '" ' . $formstyle . ' name="' . $input['fieldname'] . '" ' . $errorm . ' placeholder="' . $input['label'] . '"></textarea>';
                else if ($input['defaultfield'] == 'birthday') {
                    $datepicker=true;
                    $content .= '<input class="ve_form_text ' . $class . ' cms_datepicker" ' . $formstyle . ' type="text" name="' . $input['fieldname'] . '" ' . $errorm . ' value="" placeholder="' . $input['label'] . '" />';
                } else
                    $content .= '<input class="ve_form_text ' . $class . '" ' . $formstyle . ' type="text" name="' . $input['fieldname'] . '" ' . $errorm . ' value="' . ((isset($input['content'])) ? $input['content'] : '') . '" placeholder="' . $input['label'] . '" />';
            } else {
                switch ($input['customfield_type']) {
                    case 'select':
                        $content .= '<select class="ve_form_text ' . $class . '" ' . $formstyle . ' data-errorm="Vyberte prosím jednu z možností." name="' . $input['fieldname'] . '" value="" placeholder="' . $input['label'] . '">';
                        if ($input['label']) $content .= '<option value="">' . $input['label'] . '</option>';
                        if (isset($input['options']['item'][0])) $foreach = $input['options']['item']; else $foreach = $input['options'];
                        foreach ($foreach as $option) {
                            $content .= '<option value="' . $option['id'] . '">' . $option['name'] . '</option>';
                        }
                        $content .= '</select>';
                        break;
                    case 'radio':
                        if ($input['label']) $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
    
                        $i=1;
                        foreach ($input['options']['item'] as $option) {
                            $content .= '<div class="ve_form_option_row"><input id="ve_form_radio_' . $key . '_' . $option['order'] . '" type="radio" name="' . $input['fieldname'] . '" value="' . $option['id'] . '" '.($i===1?'checked="checked"':'').'  /><label for="ve_form_radio_' . $key . '_' . $option['order'] . '">' . $option['name'] . '</label></div>';
                            $i++;
                        }
                        break;
                    case 'checkbox':
                        if ($input['label']) $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
                        if (isset($input['options']['item'][0])) $foreach = $input['options']['item']; else $foreach = $input['options'];
                        if($input['required']) $content .= '<div class="ve_form_checkbox_container ' . $class . '" ' . $errorm . '>';
                        foreach ($foreach as $option) {
                            $content .= '<div class="ve_form_option_row"><input id="ve_form_check_' . $key . '_' . $option['order'] . '" type="checkbox" name="' . $input['fieldname'] . '[]" value="' . $option['id'] . '" /><label for="ve_form_check_' . $key . '_' . $option['order'] . '">' . $option['name'] . '</label></div>';
                        }
                        if($input['required']) $content .= '</div>';
                        break;
                    case 'textarea':
                        $content .= '<textarea class="ve_form_text' . $class . '" ' . $formstyle . ' ' . $errorm . ' name="' . $input['fieldname'] . '" placeholder="' . $input['label'] . '">' . ((isset($input['content'])) ? $input['content'] : '') . '</textarea>';
                        break;
                    case 'hidden':
                        $content .= '<input type="hidden" name="' . $input['fieldname'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" />';
                        break;
                    case 'antispam':
                        $content .= '<div class="ve_nodisp"><input type="text" name="' . $input['fieldname'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" /></div>';
                        break;
                    case 'date':    
                        $datepicker=true;
                        $content .= '<input class="ve_form_text ' . $class . ' cms_datepicker" ' . $formstyle . ' type="text" name="' . $input['fieldname'] . '" ' . $errorm . ' value="" placeholder="' . $input['label'] . '" />';
                        break;
                    case 'bool':
                        $content .= '<div class="ve_form_option_row"><input type="checkbox" class="ve_form_checkbox ' . $class . '" id="ve_form_bool_' . $key . '" name="' . $input['fieldname'] . '" ' . $errorm . ' value="1" /><label for="ve_form_bool_' . $key . '">' . $input['label'] . '</label></div>';
                        break;
                    case 'agree':
                        $content .= '<div class="ve_form_option_row_agree"><input type="checkbox" class="ve_form_checkbox ' . $class . '" id="ve_form_bool_' . $key . '" name="' . $input['fieldname'] . '" ' . $errorm . ' value="('.__('Souhlasím','cms_ve').')" /><label for="ve_form_bool_' . $key . '">' . $input['label'] . '</label></div>';
                        break;
                    case 'password':
                        $content .= '<input class="ve_form_text' . $class . '" ' . $formstyle . ' ' . $errorm . ' type="password" name="' . $input['fieldname'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" placeholder="' . $input['label'] . '" />';
                        break;
                    default:
                        $content .= '<input class="ve_form_text' . $class . '" ' . $formstyle . ' ' . $errorm . ' type="text" name="' . $input['fieldname'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" placeholder="' . $input['label'] . '" />';
                        break;
                }
            }
            if (!$hidden_field) $content .= '</' . $tag . '>';
        }
        if (!$button_text) $button_text = $form['submit'];
        $but_class = '';
        if (isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect'])
            $but_class = ' ve_cb_hover_' . $element['style']['button']['hover_effect'];
        $content .= '<' . $tag . ' class="ve_form_button_row">
            <button ' . $butstyle . ' class="ve_content_button ve_content_button_' . $element['style']['button']['style'] . $but_class . '" type="submit" >' . $button_text . '</button>
        </' . $tag . '>';
        if ($element['style']['form-style'] == '2') $content .= '</tr></table>';
        $content .= '</form>';
        
        if($datepicker) {
          wp_enqueue_script('jquery-ui-datepicker');
          wp_enqueue_style('cms_datepicker_style');
          wp_enqueue_script('cms_datepicker_cs');
        }

        return $content;
    }
  function print_seform($element, $form, $css_id, $butstyle = '') {
    $datepicker=false;

    if (!isset($element['style']['button']['height'])) $element['style']['button']['height'] = '';

    $formstyle = $this->print_styles(array(
        'font' => $element['style']['form-font'],
        'background-color' => $element['style']['background'],
        'height' => $element['style']['button']['height'],
    ), $css_id . " .ve_form_row .ve_form_text, " . $css_id . "_form .ve_form_row .ve_form_text");

    $textstyle = $this->print_styles(array(
        'font' => $element['style']['form-font'],
    ), $css_id . " .ve_form_label");

    $button_text = (isset($element['style']['button_text'])) ? $element['style']['button_text'] : '';

    if ($this->edit_mode) $content = '<form action="' . $form['url'] . '" method="post" class="ve_check_form ve_content_form ve_form_input_style_' . $element['style']['form-look'] . ' ve_form_style_' . $element['style']['form-style'] . '" '.($form['submit_in_new_window']? 'target="_blank"' : '').'>';
    else $content = '<form action="" data-action="' . $form['url'] . '" method="post" class="ve_check_form ve_content_form ve_content_form_antispam ve_form_input_style_' . $element['style']['form-look'] . ' ve_form_style_' . $element['style']['form-style'] . '" '.($form['submit_in_new_window']? 'target="_blank"' : '').'>';

    if (isset($element['style']['form-style']) && $element['style']['form-style'] == '2') {
        $content .= '<table><tr>';
        $tag = "td";
    } else $tag = "div";
    
    //print_r($form['fields']);
    foreach ($form['fields'] as $key => $input) {

        $class = "";

        if (isset($input['is_required']) && $input['is_required']) {
            $class .= " ve_form_required";
            $input['label'] .= "*";
        }

        if ($input['html_input_name'] == 'df_emailaddress') {
            $class .= " ve_form_email";
            if (is_user_logged_in() && !$this->edit_mode) {
                $current_user = wp_get_current_user();
                $input['content'] = $current_user->user_email;
            }
            if (isset($_GET['email']))
                $input['content'] = $_GET['email'];
        }
        
        $errorm = (isset($input['error_message']) && $input['error_message']) ? 'data-errorm="' . $input['error_message'] . '"' : '';


        //content from url
        if (isset($_GET[$input['html_input_name']])) $input['content'] = $_GET[$input['html_input_name']];
        
        $hidden_field=false;
        if($input['html_input_type']=='hidden') $hidden_field=true;

        if(!$hidden_field) $content .= '<' . $tag . ' class="ve_form_row ve_form_row_' . $input['html_input_name'] . '">';

        if (isset($element['style']['form-labels']) && $element['style']['form-labels'] == "2" && $input['label'] && ($input['html_input_type']!='checkbox' || isset($input['options']))) {
            $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
            $input['label'] = '';
        }

        if($input['html_input_type']=='select') {
                    if ($input['label'] && (!isset($input['is_required']) || !$input['is_required'])) {
                        $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
                        $input['label'] = '';
                    }
                    $content .= '<select class="ve_form_text ' . $class . '" ' . $formstyle . ' ' . $errorm . ' name="' . $input['html_input_name'] . '" value="" placeholder="' . $input['label'] . '">';
                    if ($input['label']) $content .= '<option value="">' . $input['label'] . '</option>';
                    if (isset($input['options']['item'][0])) $foreach = $input['options']['item']; else $foreach = $input['options'];
                    foreach ($foreach as $oid=>$option) {
                        $content .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
                    }
                    $content .= '</select>';
        } else if($input['html_input_name']=='df_gender') {
                    $content .= '<select class="ve_form_text ' . $class . '" ' . $formstyle . ' ' . $errorm . '" name="' . $input['html_input_name'] . '" value="" placeholder="' . $input['label'] . '">';
                    $options=array(
                        array(
                          'value'=>'',
                          'label'=>__('Pohlaví','cms_ve'),
                        ),
                        array(
                          'value'=>'M',
                          'label'=>__('Muž','cms_ve'),
                        ),
                        array(
                          'value'=>'F',
                          'label'=>__('Žena','cms_ve'),
                        ),
                    );
                    foreach ($options as $oid=>$option) {
                        $content .= '<option value="' . $option['value'] . '">' . $option['label'] . '</option>';
                    }
                    $content .= '</select>';
        } else if($input['html_input_type']=='radio') {
                    if ($input['label']) $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
                    $i=1;
                    foreach ($input['options'] as $oid=>$option) {
                        $content .= '<div class="ve_form_option_row"><input id="ve_form_radio_' . $key . '_' . $oid . '" type="radio" name="' . $input['html_input_name'] . '" value="' . $option['value'] . '" '.($i===1?'checked="checked"':'').' /><label for="ve_form_radio_' . $key . '_' . $oid . '">' . $option['label'] . '</label></div>';
                        $i++;
                    }
        } else if($input['html_input_type']=='checkbox') {

                    if(isset($input['is_required']) && $input['is_required']) $content .= '<div class="ve_form_checkbox_container ' . $class . '" ' . $errorm . '>';
                    
                    if(isset($input['options'])) {  
                        if ($input['label']) $content .= '<div class="ve_form_label" ' . $textstyle . '>' . $input['label'] . '</div>';
                        foreach ($input['options'] as $oid=>$option) {
                            $content .= '<div class="ve_form_option_row"><input id="ve_form_check_' . $key . '_' . $oid . '" type="checkbox" name="' . $input['html_input_name'] . '[]" value="' . $option['value'] . '" /><label for="ve_form_check_' . $key . '_' . $oid . '">' . $option['label'] . '</label></div>';
                        }
                    } else {
                        $content .= '<input id="ve_form_check_' . $key . '" type="checkbox" name="' . $input['html_input_name'] . '" value="1" /><label for="ve_form_check_' . $key . '">' . $input['label'] . '</label>';
                    }

                    if(isset($input['is_required']) && $input['is_required']) $content .= '</div>';
                    
        } else if($input['html_input_type']=='textarea' || $input['html_input_name']=='df_notes') {
          
                    $content .= '<textarea class="ve_form_text' . $class . '" ' . $formstyle . ' ' . $errorm . ' name="' . $input['html_input_name'] . '" placeholder="' . $input['label'] . '">' . ((isset($input['content'])) ? $input['content'] : '') . '</textarea>';
        
        } else if($input['html_input_type']=='date' || $input['html_input_name']=='df_birthday' || $input['html_input_name']=='df_nameday') {
           
                    $datepicker=true;
                    $content .= '<input class="ve_form_text ' . $class . ' cms_datepicker" ' . $formstyle . ' type="text" name="' . $input['html_input_name'] . '" ' . $errorm . ' value="" placeholder="' . $input['label'] . '" />';
        
        } else if($input['html_input_type']=='hidden') {
          
            $content .= '<input type="hidden" name="' . $input['html_input_name'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" />';    
                   
        } else if($input['html_input_type']=='number') {

            $content .= '<input class="ve_form_text ve_form_number ' . $class . '" ' . $formstyle . ' ' . $errorm . ' type="text" name="' . $input['html_input_name'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" placeholder="' . $input['label'] . '" />';

        } else {

            $content .= '<input class="ve_form_text' . $class . '" ' . $formstyle . ' ' . $errorm . ' type="text" name="' . $input['html_input_name'] . '" value="' . ((isset($input['content'])) ? $input['content'] : '') . '" placeholder="' . $input['label'] . '" />';

        }
        
    
        if(!$hidden_field) $content .= '</' . $tag . '>';
    }
    
    $purposes='';
    if(isset($form['purposes']) && !empty($form['purposes'])) {
        //primary
        foreach ($form['purposes'] as $key => $purpose) {
            if($purpose['checkbox_label'] && $purpose['is_primary']) {
                $purposes .= '<div class="ve_form_purpose_row"><span>';
                $purposes .= $purpose['checkbox_label'];
                if($purpose['link_href']) $purposes .= ' <a href="'.$purpose['link_href'].'" target="_blank">'.$purpose['link_label'].'</a>';
                $purposes .= '</span></div>';
            }
        }
        foreach ($form['purposes'] as $key => $purpose) {
            if($purpose['checkbox_label'] && !$purpose['is_primary']) {
                $purposes .= '<div class="ve_form_purpose_row"><label>';
                $purposes .= '<input type="checkbox" name="' . $purpose['html_input_name'] . '" value="1" />';
                $purposes .= $purpose['checkbox_label'];
                if($purpose['link_href']) $purposes .= ' <a href="'.$purpose['link_href'].'" target="_blank">'.$purpose['link_label'].'</a>';
                $purposes .= '</label></div>';
            }
        }
        
        if($purposes && $element['style']['form-style'] == '1') $content.='<div class="ve_form_purposes_container">'.$purposes.'</div>';
    }
    
    
    
    if (!$button_text) $button_text = $form['submit'];
    $but_class = '';
    if (isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect'])
        $but_class = ' ve_cb_hover_' . $element['style']['button']['hover_effect'];
    $content .= '<' . $tag . ' class="ve_form_button_row">
        <button ' . $butstyle . ' class="ve_content_button ve_content_button_' . $element['style']['button']['style'] . $but_class . '" type="submit" >' . $button_text . '</button>
    </' . $tag . '>';
    if ($element['style']['form-style'] == '2') $content .= '</tr></table>';
    
    // purposes for table
    if($purposes && $element['style']['form-style'] == '2') $content.='<div class="ve_form_purposes_container">'.$purposes.'</div>';
    
    // antispam
    $content.='<div class="field-shift" aria-label="Please leave the following three fields empty" style="left: -9999px; position: absolute;">
                <!-- people should not fill these in and expect good things -->
                <label for="b_name">Name: </label>
                <input tabindex="-1" value="" placeholder="Freddie" id="b_name" type="text" name="b_name" autocomplete="'.wp_generate_password(12, false).'">
                <label for="b_email">Email: </label>
                <input type="email" tabindex="-1" value="" placeholder="youremail@gmail.com" id="b_email" name="b_email" autocomplete="'.wp_generate_password(12, false).'">
                <label for="b_comment">Comment: </label>
                <textarea tabindex="-1" placeholder="Please comment" id="b_comment" name="b_comment" autocomplete="'.wp_generate_password(12, false).'"></textarea>
            </div>';
    
    $content .= '</form>';
    
    if($datepicker) {
      wp_enqueue_script('jquery-ui-datepicker');
      wp_enqueue_style('cms_datepicker_style');
      wp_enqueue_script('cms_datepicker_cs');
    }

    return $content;
            
}

function add_set_field($type, $array) {
    if(!isset($this->set_list[$type])) $this->set_list[$type]=array();
    $this->set_list[$type]=array_merge($this->set_list[$type],$array);
}


    function code($code)
    {
        return base64_encode(serialize($code));
    }

    function decode($code)
    {
        return unserialize(base64_decode($code));
    }

    function add_element_groups($groups)
    {
        $this->element_groups = array_merge($this->element_groups, $groups);
    }

    function add_top_panel_menu($id, $menu)
    {
        $this->top_panel_menu[$id] = $menu;
    }

    function add_elements($elements, $group, $group_title = "")
    {
        $this->elements = array_merge($this->elements, $elements);
        if (!isset($this->element_groups[$group])) {
            $this->element_groups[$group]['elements'] = array();
            $this->element_groups[$group]['name'] = $group_title;
        }
        foreach ($elements as $key => $val) {
            $this->element_groups[$group]['elements'][] = $key;
        }
    }

    function add_element_set($element, $sets, $order = 0, $tabsetting = 0, $tab = false)
    {
        if ($order) {
            $i = 0;
            $new_set = array();
            if ($tab) $oldset = $this->elements[$element]['tab_setting'];
            else $oldset = (isset($this->elements[$element]['tab_setting'])) ? $this->elements[$element]['tab_setting'][$tabsetting]['setting'] : $this->elements[$element]['setting'];

            foreach ($oldset as $val) {
                if ($i + 1 == $order) {
                    $new_set = array_merge($new_set, $sets);
                    $new_set[$i + 1] = $val;
                    $i++;
                } else $new_set[$i] = $val;
                $i++;
            }
            if ($tab) {
                $this->elements[$element]['tab_setting'] = $new_set;
            } else {
                if (isset($this->elements[$element]['tab_setting'])) $this->elements[$element]['tab_setting'][$tabsetting]['setting'] = $new_set;
                else $this->elements[$element]['setting'] = $new_set;
            }
        } else {
            if ($tab) $this->elements[$element]['tab_setting'] = array_merge($this->elements[$element]['tab_setting'], $sets);
            else $this->elements[$element]['setting'] = array_merge($this->elements[$element]['setting'], $sets);
        }
        //if($element=='image_gallery') print_r($this->elements[$element]['tab_setting']);
    }

    function add_element_set_options($element, $set, $options, $order = 0)
    {
        /*
    if($order) {
        $i=0;
        $new_set=array();
        if(isset($this->elements[$element]['setting'][$set])) {
            foreach($this->elements[$element]['setting'][$set]['options'] as $val) {
                if($i+1==$order) {
                    $new_set=array_merge($new_set,$options);
                    $new_set[$i+1]=$val;
                    $i++;
                }
                else $new_set[$i]=$val;
                $i++;
            }
            $this->elements[$element]['setting'][$set]['options']=$new_set;
        }
    } */
        if (isset($this->elements[$element]['setting'])) {
            $setting = $this->elements[$element]['setting'];
            foreach ($setting as $key => $val) {
                if ($val['id'] == $set) {
                    foreach ($options as $optkey => $opt) {
                        $this->elements[$element]['setting'][$key]['options'][$optkey] = $opt;
                    }
                }
            }
        } else if (isset($this->elements[$element]['tab_setting'])) {
            foreach ($this->elements[$element]['tab_setting'] as $setkey => $setting) {
                foreach ($setting['setting'] as $key => $val) {
                    if ($val['id'] == $set) {
                        foreach ($options as $optkey => $opt) {
                            $this->elements[$element]['tab_setting'][$setkey]['setting'][$key]['options'][$optkey] = $opt;
                        }
                    }
                }
            }

        }


    }
    
    function add_rows($rows)
    {
        $this->rows[]=$rows;
    }

    function add_shortcode_groups($groups)
    {
        $this->shortcode_groups = array_merge($this->shortcode_groups, $groups);
    }

    function add_shortcodes($shortcodes, $group, $group_title = "")
    {
        $this->shortcodes = array_merge($this->shortcodes, $shortcodes);
        if (!isset($this->shortcode_groups[$group])) {
            $this->shortcode_groups[$group]['elements'] = array();
            $this->shortcode_groups[$group]['name'] = $group_title;
        }
        foreach ($shortcodes as $key => $val) {
            $this->shortcode_groups[$group]['elements'][] = $key;
        }
    }

    function add_element_script($name, $script)
    {
        if ($this->edit_mode) {
            if (!isset($this->element_scripts[$name])) return $script;
        }
        $this->element_scripts[$name] = $script;
    }

    function makeatt($q)
    {
        if (get_option('permalink_structure')) $att = "?";
        else $att = "&";
        $return = false;
        if (is_array($q)) {
            foreach ($q as $k => $v) {
                if ($k != "p") {
                    $att .= $k . "=" . urlencode($v) . "&";
                    $return = true;
                }
            }
        }
        if ($return) return $att;
        else return '';
    }

    function create_link($link, $add_args = true)
    {
        $new_link = '';
        $args = array();

        if (!is_array($link)) {
            $old = $link;
            $link = array();
            $link['link'] = $old;
        }

        if (!isset($link['page']) && $link['link']) {
            $link['use_url'] = 1;
        }

        if (isset($link['use_url'])) {
            $new_link = $link['link'];
            /*
        if ($new_link && !preg_match("~^(?:f|ht)tps?://~i", $new_link) && substr($new_link, 0,1)!='#') {
            $new_link = "http://" . $new_link;
        }     */
        } else if (isset($link['page']) && $link['page']) $new_link = get_permalink($link['page']);

        //dont include atributes used by wordpress
        if ($add_args && $new_link) {
            foreach ($_GET as $key => $val) {
                if ($key != 'page_id' || $key != 's' || $key != 'p' || $key != 'author' || $key != 'tag' || $key != 'cat' || $key != 'paged')
                    $args[$key] = $val;
            }
            if (count($args)) $new_link = add_query_arg($args, $new_link);
        }

        return $new_link;
    }

// add custom sizes to media library
    function display_custom_image_sizes($sizes)
    {
        global $_wp_additional_image_sizes;
        if (empty($_wp_additional_image_sizes))
            return $sizes;

        foreach ($_wp_additional_image_sizes as $id => $data) {
            if (!isset($sizes[$id])) {
                if ($id == "mio_columns_c1") $sizes[$id] = __('Sloupec 1', 'cms_ve');
                if ($id == "mio_columns_c2") $sizes[$id] = __('Sloupec 1/2', 'cms_ve');
                if ($id == "mio_columns_c3") $sizes[$id] = __('Sloupec 1/3', 'cms_ve');
                if ($id == "mio_columns_c4") $sizes[$id] = __('Sloupec 1/4', 'cms_ve');
                if ($id == "mio_columns_c5") $sizes[$id] = __('Sloupec 1/5', 'cms_ve');
            }
        }
        return $sizes;
    }

    function create_button($setting, $css_id, $class='', $attrs='') {

        if($class=='') $css_id.=' .ve_content_button';
    
        $content=$this->create_button_styles($setting['style'], $css_id);
        
        $target=(isset($setting['link']['target']) && $setting['link']['target']==1)? 'target="_blank"' : "";   
        if(isset($setting['show']) && $setting['show']=='popup' && $setting['popup']) {
            if($this->edit_mode) {
                if(get_post($setting['popup'])) {
                    $content.=$this->popups->create_popup($setting['popup']);
                    wp_enqueue_script( 've_lightbox_script' );
                    wp_enqueue_style( 've_lightbox_style' );
                } else $content.='<div class="cms_error_box admin_feature">'.__('Pop-up nastavený na tomto tlačítku již neexistuje. Pravděpodobně byl smazán. Prosím, nastavte jiný.','cms_ve').'</div>';
            } else {
                if(get_post($setting['popup']))
                    $this->popups->popups_onpage[$setting['popup']]=1;
            }   
            $content.='';                                     
            $link="#";                
        }
        elseif(isset($setting['link']))
            $link=$this->create_link($setting['link']);
        else
            $link = '';
        
        $class.=' ve_content_button ve_content_button_'.$setting['style']['style'];
        if(isset($setting['align'])) $class.=' ve_content_button_'.$setting['align'];
        if(isset($setting['style']['hover_effect']) && $setting['style']['hover_effect']=='scale') $class.=' ve_cb_hover_'.$setting['style']['hover_effect'];
        
        if(isset($setting['style']['icon']) && $setting['style']['icon']['code']) {
            $class.=' ve_content_button_icon';
            $icon='<span class="ve_but_icon">'.stripslashes($setting['style']['icon']['code']).'</span>';
            $text='<span class="ve_but_text">'.stripslashes($setting['text']).'</span>';
        } else {
          $icon='';
          $text=stripslashes($setting['text']);
        }
        
        $subtext=(isset($setting['style']['subtext']) && $setting['style']['subtext'])? '<span class="ve_button_subtext">'.stripslashes($setting['style']['subtext']).'<span>':'';
        
        
        
        $content.='<a class="'.$class.'" '.$target. (empty($link) ? '' : ' href="'.$link.'"').' '.$attrs.'>'.$icon.$text.$subtext.'</a>'; 
        return $content;
    }

    function create_button_styles($setting, $css_id) {

        $hover=array();
        
        if(isset($setting['hover_effect']) && $setting['hover_effect']=='darker') {
            $hover['background_color']=array('color1'=>$this->shiftColor($setting['background_color']['color1'],0.8),'color2'=>$this->shiftColor($setting['background_color']['color2'],0.8));
            $hover['border-color']=$this->shiftColor($setting['border-color'],0.8);
        } else if(isset($setting['hover_effect']) && $setting['hover_effect']=='lighter') {
            $hover['background_color']=array('color1'=>$this->shiftColor($setting['background_color']['color1'],1.2),'color2'=>$this->shiftColor($setting['background_color']['color2'],1.2));
            $hover['border-color']=$this->shiftColor($setting['border-color'],1.2);
        } else {
            if(isset($setting['hover_color'])) $hover['background_color']=$setting['hover_color'];
            if(isset($setting['hover_font_color'])) $hover['color']=$setting['hover_font_color'];
            if(isset($setting['border_hover-color'])) $hover['border-color']=$setting['border_hover-color'];
        }
        
        $but_styles=array(        
            array(
                'styles'=>$setting,
                'element'=>$css_id,
            ),
            array(
                'styles'=>(isset($setting['subtext_font']) && $setting['subtext'])? array('font'=>$setting['subtext_font']):array(),
                'element'=>$css_id." .ve_button_subtext",
            ),
            
            array(
                'styles'=>$hover,
                'element'=>$css_id.":hover",
            ),
        );
        
        if(isset($setting['font']) && $setting['font']['font-size']>50) $this->add_style(
            $css_id,
            array('font'=>array('font-size'=>'50')), 
            '640'
        );
        if(isset($setting['font']) && $setting['font']['font-size']>35) $this->add_style(
            $css_id,
            array('font'=>array('font-size'=>'35')), 
            '480'
        );
        
        if(isset($setting['icon'])) {
            $but_styles[]=array(
                'styles'=>array('width'=>$setting['icon']['size']."px",'height'=>$setting['icon']['size']),
                'element'=>$css_id." svg, ".$css_id." .ve_but_icon",
            );
            $but_styles[]=array(
                'styles'=>array('font'=>array('font-size'=>$setting['icon']['size'])),
                'element'=>$css_id." .ve_but_icon",
            );
            $but_styles[]=array(
                'styles'=>array('fill'=>$setting['icon']['color']),
                'element'=>$css_id." path, ".$css_id." circle",
            );
        }
        if(isset($setting['icon_hover-color'])) {
            $but_styles[]=array(
                'styles'=>array('fill'=>$setting['icon_hover-color']),
                'element'=>$css_id.":hover path, ".$css_id.":hover circle",
            );
        }
        if (isset($setting['width_padding'])) {
            $but_styles[] = array(
                'styles' => array('paddingem' => array('top' => $setting['height_padding'], 'bottom' => $setting['height_padding'], 'left' => (isset($setting['icon']) ? $setting['width_padding'] - 0.8 : $setting['width_padding']), 'right' => $setting['width_padding'])),
                'element' => $css_id,
            );
        }

        $content = $this->print_styles_array($but_styles);
        return $content;
    }

// generate image
    function generate_image($image, $class = "")
    {
        if (isset($image['imageid'])) {
            $alt = get_post_meta($image['imageid'], '_wp_attachment_image_alt', true);
        }

        if ($class) $class = 'class="' . $class . '"';
        $image_url = $this->get_image_url($image['image']);

        return '<img ' . $class . ' src="' . $image_url . '" alt="' . ((isset($alt)) ? $alt : '') . '" />';
    }
    
function get_image_url($image) {
    return (substr($image, 0, 4)=='http')?$image:home_url().$image;
}

function add_editable_type($type) {
  $this->editable_type[]=substr($type,0,10);
}
function is_editable() {  
    return (in_array($this->page_type, $this->editable_type))? true : false;
}

    function select_page($pages, $meta, $name, $id, $class = '', $empty = ' - ', $get = false)
    {

        $sel = '<select class="cms_select_page ' . $class . '" name="' . $name . '" id="' . $id . '">';
        if ($empty) $sel .= '<option value="" ' . ((!$meta) ? ' selected="selected"' : '') . '>' . $empty . '</option>';
        $parent[0] = '';
        foreach ($pages as $page) {
            $parent[$page->ID] = $parent[$page->post_parent] . '&mdash;';
            $sel .= '<option value="' . $page->ID . '" ' . (($meta == $page->ID) ? ' selected="selected"' : '') . ' data-title="' . $page->post_title . '"> ' . $parent[$page->post_parent] . ' ' . (($page->post_title) ? $page->post_title : __("(bez názvu)", 'cms')) . (($page->post_status == "draft") ? __("(koncept)", 'cms') : "") . ' ' . ($page->post_name == 'page' ? '(' . $page->post_name . ')' : '') . '</option>';
        }
        $sel .= '</select>';

        if ($get) return $sel;
        else echo $sel;

    }
    
/* Web Actions
***************************************************************************** */

    function send_contact_form($args)
    {
        $time=(int)current_time( 'timestamp' )-(int)$_POST['contact_sended'];
        if ($_POST['contact_text'] && $_POST['contact_email'] && !$_POST['send_email'] && $time>5) {
            $send_to = unserialize(base64_decode($_POST['data']));

            $headers = 'From: ' . $_POST['contact_name'] . ' <' . $_POST['contact_email'] . '>' . "\r\n";
            $message = $_POST['contact_text'] . "

        " . $_POST['contact_name'] . "
        " . $_POST['contact_email'] . "
        " . $_POST['contact_phone'];
        
        if(isset($_POST['gdpr_accept']))
            $message .= "
            
        ".$_POST['gdpr_accept'];
            
            wp_mail($send_to['email'], __('Dotaz z webu','cms_ve') .' '. get_bloginfo('name'), $message, $headers);
            echo __('Zpráva byla úspěšně odeslána.','cms_ve');
        } else {
          echo __('Zpráva se nepodařila odeslat.','cms_ve');
          if($time<=5) echo __('Formulář byl odeslán příliš rychle (ochrana proti botům).','cms_ve');
        }
        die();
    }

    function web_actions()
    {
        // send custom email
        if (isset($_POST['ve_customform_structure']) && $_POST['ve_customform_email'] == '') {

            $structure = unserialize(base64_decode($_POST['ve_customform_structure']));
            $content = '';
            $email = '';
            $error = '';
            $send=true;
            
            $time=(int)current_time( 'timestamp' )-(int)$_POST['ve_sended_time'];
            if($time<=5) {
                $send=false;
                $error='time';
            }
            if(isset($_POST['ve_customform_email']) && !empty($_POST['ve_customform_email'])) {
                $send=false;
                $error='hidden_field';
            }
            
            foreach ($structure['form'] as $key => $field) {
              
                if(isset($field['required'])) {
                    if(empty($_POST['ve_custom_form_field_' . $key])) $send=false;
                    $error='norequired';
                }
                
                if (isset($field['email'])) $email = $_POST['ve_custom_form_field_' . $key];
                $content .= $field['title'] . ':
        ';
                if ($field['type'] != 'checkbox') $content .= $_POST['ve_custom_form_field_' . $key] . '

        ';
                else if (isset($_POST['ve_custom_form_field_' . $key]) && is_array($_POST['ve_custom_form_field_' . $key])) {
                    foreach ($_POST['ve_custom_form_field_' . $key] as $f_val) {
                        $content .= $f_val . '
               ';
                    }
                    $content .= '
            ';
                }
            }
            
            if($send) {
                $headers = 'From: ' . get_option('blogname', true) . ' <' . get_option('admin_email', true) . '>' . "\r\n";
                wp_mail($structure['email'], $_POST['ve_customform_subject'], $content, $headers);

                $redirect_url = $_POST['ve_customform_url'];
                if ($email) {
                    $query = parse_url($redirect_url, PHP_URL_QUERY);
                    $redirect_url .= ($query ? '&' : '?') . 'email=' . urlencode($email);
                }
            } else {
                $redirect_url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $query = parse_url($redirect_url, PHP_URL_QUERY);
                $redirect_url .= ($query ? '&' : '?') . 'custom_form_error=' . $error;
            }
            wp_redirect($redirect_url);
            die();
        }
    }

    /* Admin Actions
***************************************************************************** */

    function delete_page_ajax()
    {
        $totrash=(isset($_POST['delete_totrash']))? false : true;
        wp_delete_post($_POST['page_id'], $totrash);
        die();
    }

    function delete_menu_ajax()
    {
        wp_delete_nav_menu($_POST['page_id']);
        die();
    }

    function actions()
    {

        // create page
        if (isset($_POST['ve_create_page']) && $_FILES['import_template_upload']['tmp_name']) {
            global $webInstalator;
            $webInstalator->import_page_zip();

        } else if (isset($_POST['ve_create_page'])) {
            $current_user = wp_get_current_user();

            $new_post = array(
                'post_title' => $_POST['ve_post_title'],
                'post_name' => $_POST['ve_post_url'],
                'comment_status' => 'open',
                'post_excerpt' => $_POST['ve_post_excerpt'], 
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => $current_user->ID,
                'post_parent' => $_POST['ve_post_parent_id'],
                'menu_order' => $_POST['ve_post_menu_order'],
            );

            $post_id = $this->save_new_page($new_post, $_POST['ve_page_template']['directory']);  
            
            // page thumbnail
            if (intval($_POST['ve_post_thumbnail']['imageid'])>0) {
                set_post_thumbnail($post_id, intval($_POST['ve_post_thumbnail']['imageid']));
            } else {
                delete_post_thumbnail($post_id);
            }                        

            wp_redirect(get_permalink($post_id));
            die();
        }

        // create page copy
        if (isset($_POST['ve_create_page_copy'])) {
            global $wpdb;
            $copy_id = $_POST['ve_create_page_copy'];

            $result = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "ve_posts_layer WHERE vpl_post_id=" . $copy_id);
            $copy_post = get_post($copy_id);
            
            $current_user = wp_get_current_user();

            $new_post = array(
                'post_title' => $_POST['ve_post_title'],
                'post_name' => $_POST['ve_post_url'],                   
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'open',
                'post_author' => $current_user->ID,
                'post_content' => $copy_post->post_content,
                'post_excerpt' => $copy_post->post_excerpt,
                'post_parent' => $_POST['ve_post_parent_id'],
                'menu_order' => $_POST['ve_post_menu_order'],
            );

            $post_id = wp_insert_post($new_post);

            // save layer
            $wpdb->insert(
                $wpdb->prefix . "ve_posts_layer",
                array(
                    'vpl_post_id' => $post_id,
                    'vpl_type' => $result->vpl_type,
                    'vpl_layer' => $result->vpl_layer,
                )
            );

            $post_meta = get_post_meta($copy_id);
            foreach ($post_meta as $key => $val) {

                if ($key != "_edit_last" && $key != "_edit_lock" && $key != "mioweb_campaign")
                    add_post_meta($post_id, $key, @unserialize($val[0]));
            }

            do_action('ve_create_page_copy', $post_id);

            wp_redirect(get_permalink($post_id));
            die();
        }

        //change page template
        if (isset($_POST['ve_change_template_action'])) {
            global $wpdb;
            global $cms;

            $type = isset($_POST['post_type']) ? $_POST['post_type'] : "page";

            $post_id = $_POST['post_id'];

            $page_template = $_POST['ve_page_template'];

            if (!isset($page_template['directory'])) $page_template['directory'] = 'page/1/';

            $temp = explode("/", $page_template['directory']);
            require_once($this->get_template_dir($temp[0]) . $cms->p_templates[$temp[0]]['path'] . $temp[1] . '/config.php');

            foreach ($cms->p_set['ve_page_appearance'] as $key => $val) {
                if ((!isset($_POST['keep_header']) || $val['id'] != 've_header') && (!isset($_POST['keep_footer']) || $val['id'] != 've_footer') && (!isset($_POST['keep_appearance']) || $val['id'] != 've_appearance'))
                    delete_post_meta($post_id, $val['id']);
            }

            if (!empty($config['setting'])) {
                foreach ($config['setting'] as $key => $val) {
                    if ((!isset($_POST['keep_header']) || $key != 've_header') && (!isset($_POST['keep_footer']) || $key != 've_footer') && (!isset($_POST['keep_appearance']) || $key != 've_appearance'))
                        update_post_meta($post_id, $key, $val);
                }
                if ($type == "cms_popup") {
                    $set = get_post_meta($post_id, 've_popup', true);
                    foreach ($cms->p_set['popup_set'][0]['fields'] as $val) {
                        if (!isset($set[$val['id']])) $set[$val['id']] = isset($val['content']) ? $val['content'] : '';
                    }
                    update_post_meta($post_id, 've_popup', $set);
                }
            }
            delete_post_meta($post_id, 've_page_config');
            delete_post_meta($post_id, 've_page_template');
            if (isset($config['config'])) add_post_meta($post_id, 've_page_config', $config['config']);
            add_post_meta($post_id, 've_page_template', $page_template);

            // change layer
            if (!isset($_POST['keep_content'])) {

                $wpdb->query("DELETE FROM " . $wpdb->prefix . "ve_posts_layer WHERE vpl_post_id=" . $post_id);
                $wpdb->insert(
                    $wpdb->prefix . "ve_posts_layer",
                    array(
                        'vpl_post_id' => $post_id,
                        'vpl_type' => $type,
                        'vpl_layer' => $config['layer'],
                    )
                );
            }
            if (isset($_GET['window_editor'])) $url = add_query_arg($_GET, get_home_url());
            else if (is_admin()) $url = admin_url('post.php?post=' . $post_id . '&action=edit');
            else $url = get_permalink($post_id);
            wp_redirect($url);
            die();

        }

        // save page setting
        if (isset($_POST['ve_save_page_setting'])) {
            global $cms;

            $post_id = $_POST['post_id'];
            if (isset($_POST['set_id'])) {

                if ($_POST['set_id'] == "page_set") {
                    $my_post = array(
                        'ID' => $post_id,
                        'post_title' => $_POST['ve_post_title'],
                        'post_excerpt' => $_POST['ve_post_excerpt'],
                        'post_name' => $_POST['ve_post_url'],
                        'post_parent' => (isset($_POST['ve_post_parent_id']) ? $_POST['ve_post_parent_id'] : ''),
                        'menu_order' => (isset($_POST['ve_post_menu_order']) ? $_POST['ve_post_menu_order'] : '0')
                    );

                    // page thumbnail
                    if (intval($_POST['ve_post_thumbnail']['imageid'])>0) {
                        set_post_thumbnail($post_id, intval($_POST['ve_post_thumbnail']['imageid']));
                    } else {
                        delete_post_thumbnail($post_id);
                    }

                    wp_update_post($my_post);
                }

                $this->save_colors($_POST);

                if (isset($_POST['set_id'])) $cms->save_sets($post_id, $_POST['set_id']);
            }
            if (isset($_GET['window_editor'])) wp_redirect(get_home_url() . $this->makeatt($_GET));
            else wp_redirect(get_permalink($post_id) . $this->makeatt($_GET));
            die();
        }

        // save page single setting
        if (isset($_POST['ve_save_page_single_setting'])) {
            global $cms;

            $post_id = $_POST['post_id'];
            if (isset($_POST['tab_id'])) {
                //update_post_meta($post_id, $_POST['tab_id'], $_POST[$_POST['tab_id']]);
                $cms->save_sets($post_id, 'all', $_POST['tab_id']);
            }

            $this->save_colors($_POST);

            wp_redirect(get_permalink($post_id));
            die();
        }

        // delete page
        if (isset($_GET['ve_delete_page'])) {
            wp_delete_post($_GET['ve_delete_page']);
            wp_redirect(home_url());
            die();
        }

        // set home
        if (isset($_GET['ve_set_home'])) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $_GET['ve_set_home']);
            wp_redirect(home_url());
            die();
        }

        // save global setting

        if (isset($_POST['ve_save_global_setting'])) {
            if (isset($_POST['ve_save_global_setting_nonce']) && wp_verify_nonce($_POST['ve_save_global_setting_nonce'], 've_save_global_setting_nonce')) {
                global $cms;
                foreach ($cms->subpages as $pageset) {
                    if ($pageset['parent_slug'] == $_POST['ve_save_global_setting']) {
                        foreach ($cms->page_set_groups[$pageset['menu_slug']] as $id => $value) {
//                            mwlog(MWLS_GENERAL, '[SAVE GLOBAL] option='.$id, MWLL_DEBUG);
                            if (get_option($id) !== false) {
                                update_option($id, (isset($_POST[$id]) ? $_POST[$id] : ''));
                            } else {
                                if (isset($_POST[$id])) add_option($id, $_POST[$id]);
                            }
                        }
                    }
                }

                $this->save_colors($_POST);

                do_action('ve_after_save_options');
                wp_redirect("http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                die();
            }
        }

        // save global setting group
        if (isset($_POST['ve_save_global_setting_group'])) {
            
            if (isset($_POST['ve_save_global_setting_nonce']) && wp_verify_nonce($_POST['ve_save_global_setting_nonce'], 've_save_global_setting_nonce')) {
                global $cms;
                foreach ($cms->page_set_groups[$_POST['ve_save_global_setting_group']] as $id => $value) {
                    if (get_option($id) !== false) {
                        $option=get_option( $id,true );
                        if(isset($_POST[$id]) && isset($_POST['save_single_members']) && is_array($option)) {
                            foreach($_POST[$id]['members'] as $mem_id=>$mem) {                               
                                $option['members'][$mem_id]=$mem; 
                            }
                            $save=$option;  
                        } else if(isset($_POST[$id])) $save=$_POST[$id];
                        else $save='';

                        foreach ($cms->page_set[$id] as $settingKey => $settingField) {
                            
                            if(isset($settingField['save']) && isset($settingField['id']) && !empty($settingField['id'])) {
                              
                              $fieldName = $settingField['id'];
                              $fieldValue = &$save[$fieldName];
                              $fieldSaved = false;
                              
                              if($settingField['save']=='option') {
                                $fieldSaved = true;
                                update_option($fieldName,$fieldValue);
                              } 
                              if($fieldSaved) unset($save[$fieldName]);
                              
                            } 
                          } 
                        
                        
                        update_option( $id, $save );
                    } else {
                        if (isset($_POST[$id])) add_option($id, $_POST[$id]);
                    }
                    
                }

                $this->save_colors($_POST);

                do_action('ve_after_save_options');
                wp_redirect("http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                die();
            }
        }
        // save global setting single
        if (isset($_POST['ve_save_global_setting_single'])) {
            if (isset($_POST['ve_save_global_setting_nonce']) && wp_verify_nonce($_POST['ve_save_global_setting_nonce'], 've_save_global_setting_nonce')) {
                $slug = $_POST['ve_save_global_setting_single'];
                if (get_option($slug) !== false) {
                    $option=get_option( $slug,true );
                    if(isset($_POST[$slug]) && isset($_POST['save_single_members']) && is_array($option)) {
                        foreach($_POST[$slug]['members'] as $mem_id=>$mem) {
                            $option['members'][$mem_id]=$mem; 
                        }
                        $save=$option;   
                    } else if(isset($_POST[$slug])) $save=$_POST[$slug];
                    else $save='';
                    update_option( $slug, $save );
                } else {
                    if (isset($_POST[$slug])) add_option($slug, $_POST[$slug]);
                }

                $this->save_colors($_POST);

                do_action('ve_after_save_options');
                wp_redirect("http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
                die();
            }
        }

        if (isset($_POST['add_license_key_field']) && wp_verify_nonce($_POST['add_license_key_field'], 'add_license_key')) {
            delete_option('web_option_license');
            add_option('web_option_license', array('license' => $_POST['licence_key']));
            wp_redirect("http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"]);
            die();
        }
        
        // save google api key from element
        if (isset($_POST['ve_save_google_api_key'])) {
            $gmap_api=get_option('ve_google_api');
            if($_POST['ve_save_google_api_key']) {
                $gmap_api['api_key']=$_POST['ve_save_google_api_key'];
                update_option('ve_google_api',$gmap_api);
            }
            
        }


    }

    /* Create page setting
************************************************************************** */

    function save_new_page($post_setting, $template, $layer = "", $type = "page")
    {    
        $post_id = wp_insert_post($post_setting);        
        $this->create_page_setting($post_id, $template, $layer, $type);
        do_action('ve_create_page', $post_id);
        return $post_id;
    }

    function save_new_window_post($post_setting, $template, $layer = "", $type = "editor")
    {
        $post_id = wp_insert_post($post_setting);
        $this->create_page_setting($post_id, $template, $layer, $type);

        if ($type == "cms_popup") {
            global $cms;
            $set = get_post_meta($post_id, 've_popup', true);
            foreach ($cms->p_set['popup_set'][0]['fields'] as $val) {
                if (!isset($set[$val['id']])) $set[$val['id']] = isset($val['content']) ? $val['content'] : '';
            }
            update_post_meta($post_id, 've_popup', $set);
        }

        return $post_id;
    }

    function create_page_setting($post_id, $template, $layer, $type = "page")
    {
        global $cms;      
        if ($template) {
            $temp = explode("/", $template);
            if(!isset($cms->p_templates[$temp[0]]) || !file_exists($this->get_template_dir($temp[0]).$cms->p_templates[$temp[0]]['path'].$temp[1].'/config.php')) { 
              $temp[0]='page';
              $temp[1]='1';
            } 
            require($this->get_template_dir($temp[0]).$cms->p_templates[$temp[0]]['path'].$temp[1].'/config.php');
            if (!empty($config['setting'])) {
                foreach ($config['setting'] as $key => $val) {
                    update_post_meta($post_id, $key, $val);
                }
            }
            $newlayer = ($layer) ? $layer : ((isset($config['layer'])) ? $config['layer'] : '');
            if (isset($config['config'])) add_post_meta($post_id, 've_page_config', $config['config']);
            add_post_meta($post_id, 've_page_template', array('type' => $type, 'directory' => $template));
        } else $newlayer = ($layer) ? $layer:'';
        // save layer
        $this->save_layer($post_id, $type, $newlayer);
    }

    function save_layer($post_id, $type, $layer, $rewrite=false)
    {
        global $wpdb;
        
        $saved=false;
        
        if($rewrite) {
            $result=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ve_posts_layer WHERE vpl_type='".$type."' AND vpl_post_id=".$post_id);
            if($wpdb->num_rows) {
                $wpdb->update( $wpdb->prefix . "ve_posts_layer", array( 'vpl_layer' => $layer ), array( 'vpl_post_id' => $post_id, 'vpl_type' => $type ));
                $saved=true;
            }
        }
        if(!$saved) {
            $wpdb->insert(
                $wpdb->prefix . "ve_posts_layer",
                array(
                    'vpl_post_id' => $post_id,
                    'vpl_type' => $type,
                    'vpl_layer' => $layer,
                )
            );
        }
    }


    /* Admin edit page
************************************************************************** */

    function admin_page_edit()
    {
        global $post;
        global $current_screen;
        if (get_post_type($post) == 'page') {
            ?>
            <div class="postbox ve_admin_editbut_container">
                <a class="cms_button"
                   href="<?php echo get_permalink($post->id); ?>"><?php echo __('Spustit editor vzhledu', 'cms_ve'); ?></a>
            </div>
            <style>
                #postdivrich {
                    display: none;
                }
            </style>
            <?php

            if ($current_screen->action == 'add') {
                ?>
                <script>
                    jQuery(document).ready(function ($) {
                        openCmsLightbox({
                            title: '<?php echo __('Vytvořit novou stránku', 'cms_ve'); ?>',
                            storno: false,
                            ajax_action: 'create_new_page',
                            width: '98%'
                        });
                        $.post(ajaxurl, {action: 've_create_page'}, function (content) {
                            addContentCmsLightbox(content);
                        });
                    });
                </script>
                <?php
            }
        }
    }

function mw_print_breadcrumbs($crumbs, $separator='/') {
    $content='<ul class="mw_breadcrumbs">';
    $i=1;
    foreach($crumbs as $crumb) {
        $class='mw_breadcrumb_item mw_breadcrumb_item_'.$crumb['type'];
        $title= $crumb['title'];
        if($i==1) {
            $class=' mw_breadcrumb_item_home';
            $title=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/home.svg", true);
        } 
        $content.='<li class="'.$class.'">';
        if(isset($crumb['href'])) {
            $content.='<a href="' . $crumb['href'] . '" title="' . $crumb['title'] . '">' . $title . '</a>';
        } else {
            $content.='<span>' . $title . '</span>';
        }
        
        $content.='</li>';
        if(isset($crumb['href'])) $content.= '<li class="mw_breadcrumb_separator"> ' . $separator . ' </li>';
        $i++;
    }
    $content.='</ul>';
    
    return $content;
}

/* breadcrumbs
************************************************************************** */

function mw_breadcrumbs($separator='/', $custom_taxonomy = '') {
    global $post;
    
    $crumbs=array(
        array(
            'href' => get_home_url(),
            'title' => __('Domovská stránka','cms_ve'),
            'type' => 'home'
        )
    );
           
    if ( is_archive() && !is_tax() && !is_category() && !is_tag() ) {
              
        $crumbs[]= array(
            'title' => post_type_archive_title($prefix, false),
            'type' => 'archive'
        );
              
    } else if ( is_archive() && is_tax() && !is_category() && !is_tag() ) {
              
            // If post is a custom post type
            $post_type = get_post_type();
              
            // If it is a custom post type display name and link
            if($post_type != 'post') {
                  
                $post_type_object = get_post_type_object($post_type);
                $post_type_archive = get_post_type_archive_link($post_type);
                
                $crumbs[]= array(
                    'href' => $post_type_archive,
                    'title' => $post_type_object->labels->name,
                    'type' => 'post_type_'.$post_type
                );
              
            }
              
            $custom_tax_name = get_queried_object()->name;

            $crumbs[]= array(
                'title' => $custom_tax_name,
                'type' => 'archive'
            );
              
    } else if ( is_single() ) {
              
            // If post is a custom post type
            $post_type = get_post_type();
              
            // If it is a custom post type display name and link
            
            if($post_type != 'post') {
                  
                $post_type_object = get_post_type_object($post_type);
                $post_type_archive = get_post_type_archive_link($post_type);

                $crumbs[]= array(
                    'href' => $post_type_archive,
                    'title' => $post_type_object->labels->name,
                    'type' => 'post_type_'.$post_type
                );
              
            }     
              
            // Get post category info
            $category = get_the_category();
             
            if(!empty($category)) {
              
                // Get last category post is in
                $last_category = end(array_values($category));
                  
                // Get parent any categories and create array
                $get_cat_parents = rtrim(get_category_parents($last_category->term_id, true, ','),',');
                $cat_parents = explode(',',$get_cat_parents);
                  
                // Loop through parent categories and store in variable $cat_display
                foreach($cat_parents as $parents) {
                    $crumbs[]= array(
                        'href' => $post_type_archive,
                        'title' => $parents,
                        'type' => 'category'
                    );
                }
             
            }
              
            // If it's a custom post type within a custom taxonomy
            $taxonomy_exists = taxonomy_exists($custom_taxonomy);
            if(empty($last_category) && !empty($custom_taxonomy) && $taxonomy_exists) {
            
                   
                $taxonomy_terms = get_the_terms( $post->ID, $custom_taxonomy );
                if(isset($taxonomy_terms[0])) {
                    $cat_id         = $taxonomy_terms[0]->term_id;
                    $cat_nicename   = $taxonomy_terms[0]->slug;
                    $cat_link       = get_term_link($taxonomy_terms[0]->term_id, $custom_taxonomy);
                    $cat_name       = $taxonomy_terms[0]->name;  
                }
               
            }
              
            // Check if the post is in a category
            if(!empty($last_category)) {
                  
            // Else if post is in a custom taxonomy
            } else if(!empty($cat_id)) {
                
                $crumbs[]= array(
                    'href' => $cat_link,
                    'title' => $cat_name,
                    'type' => 'category_'.$cat_id
                );
              
            } 
            
            $crumbs[]= array(
                'title' => get_the_title(),
                'type' => 'item'.$post->ID
            );
              
    } else if ( is_category() ) {
               
            // Category page
            $crumbs[]= array(
                'title' => single_cat_title('', false),
                'type' => 'category'
            );
               
    } else if ( is_page() ) {
               
            // Standard page
            if( $post->post_parent ){
                   
                // If child page, get parents 
                $anc = get_post_ancestors( $post->ID );
                   
                // Get parents in the right order
                $anc = array_reverse($anc);
                   
                // Parent page loop
                foreach ( $anc as $ancestor ) {
                    $crumbs[]= array(
                        'href' => get_permalink($ancestor),
                        'title' => get_the_title($ancestor),
                        'type' => 'page'
                    );
                }
      
            }  
            
            // Current page
            $crumbs[]= array(
                'title' => get_the_title(),
                'type' => 'page'
            ); 
               
    } else if ( is_tag() ) {
               
            // Tag page
               
            // Get tag information
            $term_id        = get_query_var('tag_id');
            $taxonomy       = 'post_tag';
            $args           = 'include=' . $term_id;
            $terms          = get_terms( $taxonomy, $args );
            $get_term_id    = $terms[0]->term_id;
            $get_term_slug  = $terms[0]->slug;
            $get_term_name  = $terms[0]->name;
               
            // Display the tag name
            $crumbs[]= array(
                'title' => $get_term_name,
                'type' => 'tag'
            ); 
    } 
    
    $crumbs=apply_filters( 'mw_breadcrumb_items', $crumbs );
 
    return $this->mw_print_breadcrumbs($crumbs, $separator);;
       
}  



/* Aktivace šablony
************************************************************************** */

    function ve_activation($versions)
    {
        if (empty($versions) || !isset($versions['visualeditor'])) {
            global $wpdb;
            $temp_dir = str_replace(home_url(), '', get_bloginfo('template_url'));

            $db_table_name = $wpdb->prefix . 've_posts_layer';
            if ($wpdb->get_var("SHOW TABLES LIKE '$db_table_name'") != $db_table_name) {
                if (!empty($wpdb->charset))
                    $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                if (!empty($wpdb->collate))
                    $charset_collate .= " COLLATE $wpdb->collate";

                $sql = "CREATE TABLE IF NOT EXISTS  " . $db_table_name . " (
        vpl_id bigint(20) NOT NULL AUTO_INCREMENT,
        vpl_post_id bigint(20) NOT NULL,
        vpl_type varchar(10) NOT NULL,
        vpl_layer longtext NOT NULL,
        PRIMARY KEY (`vpl_id`)) $charset_collate;";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }

            // set layer and template for all pages
            $pages = get_pages(array('post_status' => 'publish,inherit,pending,private,future,draft,trash'));
            foreach ($pages as $page) {
                $oldcontent = array(
                    0 => array(
                        'class' => '',
                        'style' => array('font' => array()),
                        'content' => array(
                            0 => array(
                                'type' => 'col-one',
                                'class' => '',
                                'content' => array(
                                    0 => Array(
                                        'type' => 'text',
                                        'content' => $page->post_content,
                                        'style' => array(
                                            'font' => array(
                                                'font-size' => '',
                                                'font-family' => '',
                                                'weight' => '',
                                                'line-height' => '',
                                                'color' => '',
                                            ),
                                            'li' => '',
                                        ),
                                        'config' => Array('margin_top' => 0, 'margin_bottom' => 20)
                                    )

                                )

                            )

                        )

                    )

                );
                $this->create_page_setting($page->ID, 'page/1/', $this->code($oldcontent));
            }
            // 404
            $this->save_layer(0, '404', 'YToxOntpOjA7YTozOntzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7YTo4OntzOjQ6ImZvbnQiO2E6NDp7czo5OiJmb250LXNpemUiO3M6MDoiIjtzOjExOiJmb250LWZhbWlseSI7czowOiIiO3M6Njoid2VpZ2h0IjtzOjA6IiI7czo1OiJjb2xvciI7czowOiIiO31zOjEwOiJsaW5rX2NvbG9yIjtzOjA6IiI7czoxNjoiYmFja2dyb3VuZF9jb2xvciI7YToyOntzOjY6ImNvbG9yMSI7czo3OiIjZWJlYmViIjtzOjY6ImNvbG9yMiI7czowOiIiO31zOjE2OiJiYWNrZ3JvdW5kX2ltYWdlIjthOjM6e3M6ODoicG9zaXRpb24iO3M6MTM6ImNlbnRlciBjZW50ZXIiO3M6NjoicmVwZWF0IjtzOjk6Im5vLXJlcGVhdCI7czo1OiJpbWFnZSI7czowOiIiO31zOjEwOiJib3JkZXItdG9wIjthOjM6e3M6NDoic2l6ZSI7czoxOiIwIjtzOjU6InN0eWxlIjtzOjU6InNvbGlkIjtzOjU6ImNvbG9yIjtzOjA6IiI7fXM6MTM6ImJvcmRlci1ib3R0b20iO2E6Mzp7czo0OiJzaXplIjtzOjE6IjAiO3M6NToic3R5bGUiO3M6NToic29saWQiO3M6NToiY29sb3IiO3M6MDoiIjt9czoxMToicGFkZGluZ190b3AiO3M6MzoiMTUwIjtzOjE0OiJwYWRkaW5nX2JvdHRvbSI7czozOiIyMDAiO31zOjc6ImNvbnRlbnQiO2E6MTp7aTowO2E6Mzp7czo0OiJ0eXBlIjtzOjc6ImNvbC1vbmUiO3M6NToiY2xhc3MiO3M6MDoiIjtzOjc6ImNvbnRlbnQiO2E6Mzp7aTowO2E6NDp7czo0OiJ0eXBlIjtzOjU6InRpdGxlIjtzOjc6ImNvbnRlbnQiO3M6NjE6IjxwIHN0eWxlPVwidGV4dC1hbGlnbjogY2VudGVyO1wiPlVwcywgc3Ryw6Fua2EgbmVuYWxlemVuYTwvcD4iO3M6NToic3R5bGUiO2E6MTp7czo0OiJmb250IjthOjU6e3M6OToiZm9udC1zaXplIjtzOjI6IjQ1IjtzOjExOiJmb250LWZhbWlseSI7czowOiIiO3M6Njoid2VpZ2h0IjtzOjA6IiI7czo1OiJjb2xvciI7czowOiIiO3M6MTE6InRleHQtc2hhZG93IjtzOjQ6Im5vbmUiO319czo2OiJjb25maWciO2E6NTp7czo5OiJtYXhfd2lkdGgiO3M6MDoiIjtzOjEwOiJtYXJnaW5fdG9wIjtzOjE6IjAiO3M6MTM6Im1hcmdpbl9ib3R0b20iO3M6MjoiMTAiO3M6NToiZGVsYXkiO3M6MDoiIjtzOjU6ImNsYXNzIjtzOjA6IiI7fX1pOjE7YTo0OntzOjQ6InR5cGUiO3M6NDoidGV4dCI7czo3OiJjb250ZW50IjtzOjcwOiI8cCBzdHlsZT1cInRleHQtYWxpZ246IGNlbnRlcjtcIj5TdHLDoW5rYSBuYSB0w6l0byB1cmwgbmVleGlzdHVqZS48L3A+IjtzOjU6InN0eWxlIjthOjI6e3M6NDoiZm9udCI7YTo0OntzOjk6ImZvbnQtc2l6ZSI7czoyOiIxNyI7czoxMToiZm9udC1mYW1pbHkiO3M6MDoiIjtzOjY6IndlaWdodCI7czowOiIiO3M6NToiY29sb3IiO3M6MDoiIjt9czoyOiJsaSI7czowOiIiO31zOjY6ImNvbmZpZyI7YTo1OntzOjk6Im1heF93aWR0aCI7czowOiIiO3M6MTA6Im1hcmdpbl90b3AiO3M6MToiMCI7czoxMzoibWFyZ2luX2JvdHRvbSI7czoyOiIzMCI7czo1OiJkZWxheSI7czowOiIiO3M6NToiY2xhc3MiO3M6MDoiIjt9fWk6MjthOjM6e3M6NDoidHlwZSI7czo2OiJidXR0b24iO3M6NzoiY29udGVudCI7czozMDoiUMWZZWrDrXQgbmEgZG9tb3Zza291IHN0csOhbmt1IjtzOjU6InN0eWxlIjthOjM6e3M6NDoibGluayI7YToxOntzOjQ6ImxpbmsiO3M6MToiLyI7fXM6NjoiYnV0dG9uIjthOjQ6e3M6NToic3R5bGUiO3M6MToiMyI7czo0OiJmb250IjthOjU6e3M6OToiZm9udC1zaXplIjtzOjI6IjE3IjtzOjExOiJmb250LWZhbWlseSI7czowOiIiO3M6Njoid2VpZ2h0IjtzOjA6IiI7czo1OiJjb2xvciI7czo3OiIjZmZmZmZmIjtzOjExOiJ0ZXh0LXNoYWRvdyI7czo0OiJub25lIjt9czoxNjoiYmFja2dyb3VuZF9jb2xvciI7YToyOntzOjY6ImNvbG9yMSI7czo3OiIjN2Y4ODhmIjtzOjY6ImNvbG9yMiI7czowOiIiO31zOjY6ImJvcmRlciI7YToyOntzOjQ6InNpemUiO3M6MToiMCI7czo1OiJjb2xvciI7czowOiIiO319czo1OiJhbGlnbiI7czo2OiJjZW50ZXIiO319fX19fX0=');
        }

    }

    function check_version()
    {
        $versions = get_option('cms_versions');
        if (isset($versions['visualeditor']) && $versions['visualeditor'] != VS_VERSION) {
            global $wpdb;
            if ($versions['visualeditor'] == '0.9') {
                $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 've_posts_layer ADD `vpl_type` VARCHAR( 10 ) NOT NULL AFTER `vpl_post_id` ');
                $wpdb->query("UPDATE " . $wpdb->prefix . "ve_posts_layer SET vpl_type = 'page'");
            }
            if (version_compare($versions['visualeditor'], '0.9.2', '<')) {
                $pages = get_pages(array('post_status' => 'publish,private,draft'));
                foreach ($pages as $page) {
                    $template = get_post_meta($page->ID, 've_page_template', true);
                    $update = array('sale/1/', 'sale/2/', 'sale/3/', 'sale/4/', 'others/1/', 'others/thx2/', 'squeeze/1/', 'squeeze/4/', 'member/login1/');
                    if (in_array($template['directory'], $update)) {
                        $template_config = get_post_meta($page->ID, 've_page_config', true);
                        $template_config['body_class'] = 'fixed_template';
                        update_post_meta($page->ID, 've_page_config', $template_config);
                    }
                }
            }
            if (version_compare($versions['visualeditor'], '0.9.3', '<')) {
                update_option('ve_installed_web', array('web_theme' => 'empty'));
            }
            if (version_compare($versions['visualeditor'], '0.9.4', '<')) {
                global $apiConnection;
                $login=get_option('ve_connect_se');
                if($login['connection']['status'] && $login['connection']['login'] && $login['connection']['password']) {
                
                    $client = $apiConnection->getClient('se'); 
                    $new_api=$client->getNewApi($login['connection']['login'],$login['connection']['password']);
                    if($new_api) {
                        $login['connection']['password']=$new_api;
                        $login['password']=$new_api;
                        update_option('ve_connect_se', $login);
                    }   
                }
            }
            if (version_compare($versions['visualeditor'], '0.9.5', '<')) {
                // repair fixed on background of page and web
                
                //web
                $option=get_option('ve_appearance');  
                if($option){
                    $option['background_image']['fixed']='fixed';
                    update_option('ve_appearance',$option);
                }  
                
                //blog
                $option=get_option('blog_appearance');  
                if($option){
                    $option['background_image']['fixed']='fixed';
                    update_option('blog_appearance',$option);
                }
                
                //member
                $option=get_option('member_appearance');  
                if($option){
                    foreach($option['members'] as $key=>$val) {
                        $option['members'][$key]['background_image']['fixed']='fixed';
                    }
                    update_option('member_appearance',$option);
                }
                
                //eshop
                $option=get_option('eshop_appearance');  
                if($option){
                    $option['background_image']['fixed']='fixed';
                    update_option('eshop_appearance',$option);
                }
                
                //pages
                $pages = get_pages(array('post_status' => 'publish,private,draft'));
                foreach($pages as $page) {
                    $option=get_post_meta($page->ID,'ve_appearance',true);  
                    if($option){
                        $option['background_image']['fixed']='fixed';
                        update_post_meta($page->ID, 've_appearance', $option);
                    }   
                }
            }
            if (version_compare($versions['visualeditor'], '0.9.6', '<')) {
                // GDPR
                global $cms;
                if(!get_option( 'web_option_gdpr' )) {
                    $setting=$cms->get_default_option($cms->page_set['web_option_gdpr']);  
                    if(!empty($setting)) add_site_option( 'web_option_gdpr', $setting); 
                }
                
            }
            $versions['visualeditor'] = VS_VERSION;
            update_option('cms_versions', $versions);
        }
    }

    /* Others */
    function layer_revision($post_id)
    {
        $post = get_post($post_id);
        if ($post->post_type == 'page') {
            global $wpdb;
            $wpdb->update($wpdb->prefix . "ve_posts_layer", array('vpl_layer' => $post->post_content), array('vpl_post_id' => $post_id));
        }
    }

    function is_blog()
    {
        return (((is_archive()) || (is_author()) || (is_category()) || (is_home()) || (is_tag()) || (is_search()))) ? true : false;
    }


}
