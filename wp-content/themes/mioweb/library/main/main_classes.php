<?php

class Cms {
    public $p_types;
    /** @var array  */
    public $p_set;
    /** @var array Contains definitions of tabs for a metabox. */
    public $p_sets;
    public $p_templates=array();
    public $pages=array();
    public $subpages=array();
    public $page_set=array();
    public $page_set_groups=array();
    public $frontedit_support=false;
    public $shortcodes=array();
    public $fonts=array();
    public $google_fonts=array();
    public $sidebars;
    public $scripts=array();
    public $modules;
    public $versions;
    public $license=array();
    public $license_info=array();
    public $is_mobile;
    public $container=array();
    public $script_version;
    
    function __construct(){  
    
      $this->script_version=filemtime(get_template_directory().'/style.css');

      // delete license info after change on license server
      if(isset($_POST['mw_update_license'])) {
          delete_transient('cms_license');
          die();
      }
    
      if(current_user_can('edit_pages')) {
          $licence=get_option('web_option_license');
          //delete_transient('cms_license');
          init_check_license($licence['license']);     
      }
      $this->license=get_transient('cms_license');
      $this->license['modules']=get_option('cms_license_modules');
      $this->licence_info=get_option('mw_licence_info'); 
      if(!isset($this->license['modules']) || count($this->license['modules'])<2)
          $this->license['modules']=array('cms','blog','mioweb','member','smartselling','shop');

      remove_action( 'wp_head', 'rel_canonical' );
      remove_action( 'wp_head', 'index_rel_link' );
      remove_action( 'wp_head', 'start_post_rel_link' );
      remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
      remove_action( 'wp_head', 'wlwmanifest_link' );
      remove_action( 'wp_head', 'wp_generator' );
      
      add_action('init', 'add_custom_post_types');  // add custom post types    
      add_action('phpmailer_init', array($this,'cms_init_smtp'));  
      add_action('admin_menu', 'page_add_meta');  //add custom fields
      add_action("admin_menu", array($this,"create_menu"));  //add admin pages and setting fields
       
      add_action( 'wp_head', array($this,'generate_head'));
      add_action('wp_footer', array($this,'generate_footer'));
      add_action( 'template_redirect', array($this,'cms_redirect_page') );
      
      add_action('wp', array($this,'add_scripts'));  // add header, css, footer scripts

      //widgets
      add_action( 'widgets_admin_page', array($this,'widgets_content'));
      add_action( 'widgets_init', array($this,'register_sidebars') );
      if(isset($_POST['widget_action'])) add_action( 'init', array($this, 'widget_actions'), 1 );
       
      //widgets-ajax
      add_action('wp_ajax_cms_delete_sidebar', array($this, 'delete_sidebar'));
      
      // theme activation
      add_action("after_switch_theme", array($this, 'cms_activation'));
      


      //Image meta update
      if( current_user_can( 'edit_posts' ) )
        add_action( 'wp_ajax_mio_image_gallery_edit_meta', array( $this, 'update_image_meta' ) );
      
      $this->add_version('cms',CMS_VERSION);    
      
      $this->is_mobile=wp_is_mobile(); 
      
      $this->add_custom_google_fonts(); 
    
    }
    
    function cms_init_smtp($phpmailer) {
        $smtp=get_option('web_option_smtp');
        if( !$smtp || !is_email($smtp['smtp_email']) || empty($smtp['smtp_host']) ){
      		return;
      	}
        if(isset($smtp['use_smtp'])) {
            $phpmailer->Mailer = "smtp";
          	$phpmailer->From = $smtp["smtp_email"];
          	$phpmailer->FromName = $smtp["smtp_name"];
          	$phpmailer->Sender = $phpmailer->From; 
          	$phpmailer->AddReplyTo($phpmailer->From,$phpmailer->FromName); 
          	$phpmailer->Host = $smtp["smtp_host"];
          	$phpmailer->SMTPSecure = $smtp["smtp_secure"];
          	$phpmailer->Port = $smtp["smtp_port"];
          	$phpmailer->SMTPAuth = ($smtp["smtp_authentication"]=="yes") ? TRUE : FALSE;
          	if($phpmailer->SMTPAuth){
          		$phpmailer->Username = $smtp["smtp_login"];
          		$phpmailer->Password = $smtp["smtp_password"];
          	}
        }
    }
    
    function is_module_active($module) {
        return (in_array($module,$this->license['modules']))?true:false;
    }
    
    // language
    function load_theme_lang($domain, $path){
        load_theme_textdomain( $domain, $path );
        $locale = get_locale();
        $options['lang'] = $locale; 
        $locale_file = $path."/$locale.php";
        if ( is_readable( $locale_file ) ) require_once( $locale_file );
    }
    
    function add_type($args) {
      $this->p_types[]=$args;
    }
    
    function define_set($args) {
      $this->p_sets[]=$args;
    }
    function add_set($args, $set, $pos=false) {
      if($pos && count($this->p_set[$set])) {
          $i=1;
          $neworder=array();
          foreach($this->p_set[$set] as $key=>$val) {
              if($i==$pos) {
                  $neworder[$i]=$args;
                  $i++;
              }
              $neworder[$i]=$val;
              $i++;
          }
          $this->p_set[$set]=$neworder; 
      }
      else $this->p_set[$set][]=$args;
    }
    
    function update_set_tab($set, $tab, $field, $order=false ) {

        foreach($this->p_set as $p_id=>$p_set) {
            foreach($p_set as $sub_id=>$sub_set) {
                if($sub_set['id']==$set) {                    
                    foreach($sub_set['fields'] as $f_id=>$f_set) {
                        if(isset($f_set['id']) && $f_set['id']==$field) {                    
                            if($order!==false) {
                                $new_tab=array();
                                $i=1;   
                                foreach($f_set['tabs'] as $t_id=>$t_val) {
                                    if($i==$order) {
                                        $new_tab=array_merge($new_tab,$tab);
                                        $new_tab[$t_id]=$t_val;
                                        $i++; 
                                    }
                                    else $new_tab[$t_id]=$t_val;
                                    $i++;  
                                }
                                $this->p_set[$p_id][$sub_id]['fields'][$f_id]['tabs']=$new_tab; 
    
                            }
                            else $this->p_set[$p_id][$sub_id][$f_id]['tabs']=array_merge($this->p_set[$p_id][$sub_id]['fields'][$f_id]['tabs'],$tab); 
                                                       
                        }
                    }  
                }    
            }
            
        }
        
    }
    
    function add_shortcode($args) {
      $this->shortcodes[$args['id']]=$args;
    }
    
    function add_fonts($args) {
      $this->fonts=array_merge($this->fonts, $args);
    }
    function add_google_fonts($args) {
      $this->google_fonts=array_merge($this->google_fonts, $args);
    }
    function add_custom_google_fonts() {
      $fonts_s=get_option('mw_custom_fonts');
      if(isset($fonts_s['fonts']) && count($fonts_s['fonts'])) {
      foreach($fonts_s['fonts'] as $font) {
        preg_match('/href=["\']?([^"\'>]+)["\']?/', stripslashes($font['font_code']), $link);
        $url=parse_url(str_replace('"','',$link[0]));
        
        $gf=explode('&',str_replace('family=','',$url['query']));
        $fonts=explode('|',$gf[0]);
        foreach($fonts as $f) {
            $font_parts=explode(':',$f);
            $font_title=str_replace('+',' ',$font_parts[0]);
            if(isset($font_parts[1])) $font_weights=explode(',',$font_parts[1]);
            
            $weights=array();
            if(count($font_weights)) {
              foreach($font_weights as $w) {
                $t='Normal';
                if($w==100) $t='Thin';
                if($w==200) $t='Extra-light';
                if($w==300) $t='Light';
                if($w==500) $t='Medium';
                if($w==600) $t='Semi-bold';
                if($w==700) $t='Bold';
                if($w==800) $t='Extra-bold';
                if($w==700) $t='Black';
                
                $weights[$w]=$t;
              }
            } else $weights[400]='Normal';
            
            $this->google_fonts[$font_title]=array(
              'weights'=>$weights,
              'custom_font'=>1
            );
        }

      }
      }
      
      //$this->google_fonts=array_merge($this->google_fonts, $args);
    }
    function create_init() {
      
      // rewritable function for init child_theme setting, modules ...
      load_child_theme(); 
      do_action( 'cms_load_plugin' );        
     
        /* shortcodes 
       if($this->shortcodes){
           foreach($this->shortcodes as $key=>$value) {
              add_shortcode($key, $value['function']);
           }
           add_action( 'admin_footer', 'create_shortcode_popups' );
       } */
       
   }
   
   function add_exclude($set,$exclude) {
       foreach($this->p_sets as $key=>$p_sets) {
          if($p_sets['id']==$set) {
              $this->p_sets[$key]['exclude'][]=$exclude;
          }
       }
       
   }
   function add_page($args) {
      $this->pages[$args['menu_slug']]=$args;
   }
   function add_subpage($args) {
      $this->subpages[$args['menu_slug']]=$args;
   }

  /**
   * Add new page group (=tab-page of settings). Page group is saved as unique option within WP.
   * @param $args array Associative array. Should contain "page" with name of parenting page and "id" as string. Value of "id"
   *                    is used as $page argument in {@link add_page_setting()} and also as the name of the option saved
   *                    into WP database.
   */
   function add_page_group($args) {
      $this->page_set_groups[$args['page']][$args['id']]=$args;
   }
  /**
   * Add content of a page grou (=settings tab-page) or merges with current definition.
   * @param $page string Name must be registered with {@link add_page_group()}.
   * @param $args array
   */
   function add_page_setting($page, $args) {
      if(isset($this->page_set[$page])) $this->page_set[$page]=array_merge($this->page_set[$page], $args);
      else $this->page_set[$page]=$args;
   }
   function add_frontedit_support() {
      $this->frontedit_support=true;   
      add_action('wp_ajax_openec', 'openec_callback');
      add_action('wp_ajax_saveec', 'saveec_callback');
      add_thickbox();
      add_action('wp_head', 'editable_content_scripts');   
   }
   function add_templates($templates) {  
      if(count($this->p_templates)) $this->p_templates=array_merge($this->p_templates, $templates);   
      else $this->p_templates=$templates;                             
   }
   function add_templates_topos($pos,$id,$templates) {  
      if(count($this->p_templates)) {
          $i=1;
          $neworder=array();
          foreach($this->p_templates as $key=>$val) {
              if($i==$pos) $neworder[$id]=$templates;
              $neworder[$key]=$val;
              $i++;
          }
          $this->p_templates=$neworder; 
      }  
      else $this->p_templates=$templates;                             
   }
   function add_templates_tocat($templates) { 
      if(count($this->p_templates)) $this->p_templates=array_merge($this->p_templates, $templates);   
      else $this->p_templates=$templates;     
      foreach($templates as $cat=>$tempcat) {
          foreach($tempcat['list'] as $subcat=>$tempsubcat) {
              if(!isset($this->p_templates[$tempcat['cat']]['list'][$subcat])) 
                  $this->p_templates[$tempcat['cat']]['list'][$subcat]['name']=$tempsubcat['name'];              
              foreach($tempsubcat['list'] as $temp)
                  $this->p_templates[$tempcat['cat']]['list'][$subcat]['list'][]=array('folder'=>$temp, 'cat'=>$cat); 
          }
      }                              
   }

// Saving Custom fields
// ******************************************************************************************
   
    function save_sets($post_id, $save="all", $save_setting='all') {

        mwlog('cms', "saving sets for [$post_id]", MWLL_DEBUG, 'save');

        foreach($this->p_sets as $set) {   

           if($save=="all" || $set['id']==$save) {

              foreach ($this->p_set[$set['id']] as $tabs) {
              
                if(isset($_POST[$tabs['id']]) && ($save_setting=="all" || $tabs['id']==$save_setting)) {    
                  // Mechanism to store special values into different places according to field definition.
                  // According to optional "save" key within field definition, its corresponding value will be saved
                  // into POST as a field (use "post") or as a separate POST META field (use "post_meta").
                  // Name of the field of meta key is the value of "id" within field's definition.

                  foreach($tabs['fields'] as $field) {
                    if(isset($field['save']) && isset($field['id']) && !empty($field['id'])) {
                      $fieldName = $field['id'];
                      $fieldValue = &$_POST[$tabs['id']][$fieldName];
                      $fieldSaved = false;
                      if(isset($field['savehook']) && is_callable($field['savehook'])) {
                        $func = $field['savehook'];
                        $func($post_id, $field, $fieldValue, $fieldSaved);
                      }
                      if($fieldSaved) {
                        // Nothing to do
                      } elseif($field['save']=='post') {
                        // Save into POST data field.
                        $new_post = array(
                          'ID' => $post_id,
                          $fieldName => $fieldValue,
                        );
                        cms_save_disable();
                        wp_update_post($new_post);
                        cms_save_enable();
                        $fieldSaved = true;
                      } elseif ($field['save'] == 'post_meta'){
                        // Save into single meta field.
                        mwlog('cms', "savesets META for [$post_id][$fieldName]", MWLL_DEBUG, 'save');
                        update_post_meta($post_id, $fieldName, $fieldValue);
                        $fieldSaved = true;
                      } else {
                        // Coding error.
                        echo "Incorrect 'field save value' for field [{$fieldName}], value [{$field['save']}].";
                      }
                      if ($fieldSaved) unset($_POST[$tabs['id']][$fieldName]);
                    }
                    // Special case for SETTING array within a field, like "toogle_group".
                    if (isset($field['setting']) && is_array($field['setting'])) {
                      foreach ($field['setting'] as $settingKey => $settingField) {
                        if(isset($settingField['save']) && isset($settingField['id']) && !empty($settingField['id'])) {
                          $fieldName = $settingField['id'];
                          $fieldValue = &$_POST[$tabs['id']][$fieldName];
                          $fieldSaved = false;
                          if(isset($settingField['savehook']) && is_callable($settingField['savehook'])) {
                            $func = $settingField['savehook'];
                            $func($post_id, $settingField, $fieldValue, $fieldSaved);
                          }
                          if($fieldSaved) {
                            // Nothing to do
                          } elseif ($settingField['save'] =='post') {
                            // Save into POST data field.
                            $new_post = array(
                              'ID' => $post_id,
                              $fieldName => $fieldValue,
                            );
                            $fieldSaved = true;
                            cms_save_disable();
                            wp_update_post($new_post);
                            cms_save_enable();
                          } elseif ($settingField['save'] == 'post_meta'){
                            // Save into single meta field.
                            mwlog('cms', "savesets META for [$post_id][$fieldName]", MWLL_DEBUG, 'save');
                            update_post_meta($post_id, $fieldName, $fieldValue);
                            $fieldSaved = true;
                          } else {
                            // Coding error.
                            echo "Incorrect 'field save value within SETTING definition' for field " .
                              "[{$field['id']}{$fieldName}], value [{$settingField['save']}].";
                          }
                          if($fieldSaved) unset($_POST[$tabs['id']][$fieldName]);
                        }
                      }
                    }
                  }
                  if(isset($_POST[$tabs['id']])) {
                    // Save tabs as separate meta fields.
                    update_post_meta($post_id, $tabs['id'], $_POST[$tabs['id']]);
                  }
                  
                }
                else if($tabs['id']=='product_gallery') {
                    update_post_meta($post_id, $tabs['id'], array());
                }
              }
           }
        }  
    }
   
   
/* Select page */
   
   function select_page($pages, $meta, $name, $id, $class='', $empty=' - ', $get=false) {
   
      $sel= '<select class="cms_select_page '.$class.'" name="'.$name.'" id="'.$id.'">';
      if($empty) $sel.= '<option value="" '. ((!$meta) ? ' selected="selected"' : ''). '>'.$empty.'</option>';
      $parent[0]='';
      foreach ($pages as $page) {
          $parent[$page->ID]=$parent[$page->post_parent].'&mdash;';  
          $sel.= '<option value="'.$page->ID.'" '. (($meta == $page->ID) ? ' selected="selected"' : ''). ' data-title="'.$page->post_title.'"> '.$parent[$page->post_parent].' '.(($page->post_title)? $page->post_title : __("(bez názvu)",'cms')). (($page->post_status=="draft")? __("(koncept)",'cms') : "").' '.($page->post_name=='page'?'('.$page->post_name.')':'').'</option>';
      }
      $sel.= '</select>'; 
      
      if($get) return $sel;
      else echo $sel;
   }
   
   function valid_license() {
      if($this->license && $this->license['code']=='success') return true;
      else return false;
   }
   

/* Scripts
**************************************************************************** */
function add_scripts() {
    global $post;  

    $option=get_option('web_option_codes');
    if(isset($post->ID)) $page_codes=get_post_meta($post->ID, "page_codes", true);
    
    //header
    if(isset($option['head_scripts']) && $option['head_scripts']) $this->scripts['header'][]=$option['head_scripts'];
    if(isset($page_codes['codes_header']) && $page_codes['codes_header']) $this->scripts['header'][]=$page_codes['codes_header']; 

    //ga
    if(isset($option['ga_id']) && $option['ga_id']) $this->scripts['ga']=$option['ga_id']; 
    //footer
    if(isset($option['footer_scripts']) && $option['footer_scripts']) $this->scripts['footer'][]=$option['footer_scripts'];
    if(isset($page_codes['codes_footer']) && $page_codes['codes_footer']) $this->scripts['footer'][]=$page_codes['codes_footer']; 
    //conversion
    if(isset($page_codes['codes_conversion']) && $page_codes['codes_conversion']!="") {
        $code=$page_codes['codes_conversion'];        
        preg_match_all('/(%%)([a-zA-Z]+)(%%)/', $code,  $matches);       
        $show=true; 
        if(isset($_GET['email'])) $code=str_replace("ID_TRANSAKCE", $_GET['email'], $code);
        else if(isset($_GET['vs'])) $code=str_replace("ID_TRANSAKCE", $_GET['vs'], $code);
        else if(strstr($code,"ID_TRANSAKCE")) $show=false;

		    if (isset($_GET["cena"])) { 
           
           $replacements=array();     
			     $replacements['CENA'] = number_format((float) $_GET["cena"]);
			     $replacements['OZNACENI_MENY'] = isset($_GET["mena"]) ? htmlspecialchars($_GET["mena"]) : 'CZK';	 
           $code = strtr($code, $replacements); 
        }
        elseif (isset($_GET["vs"])){
    			$code=cms_connectFapiAffilbox($_GET["vs"],$code);	
    		}	
        
        foreach($matches[2] as $val) {
            if(isset($_GET[$val])) $code=str_replace("%%".$val."%%", $_GET[$val], $code);
            else $show=false;
        }
        if($show) $this->scripts['footer'][]=$code;
    }
    //css
    if(!empty($option['css_scripts'])) $this->scripts['css'][]=$option['css_scripts'];
    if(!empty($page_codes['codes_css'])) $this->scripts['css'][]=$page_codes['codes_css'];  

}

function add_script($id, $script) {
    if($id) $this->scripts[$id][]=$script;
}
function print_scripts($id) {
  if(isset($this->scripts[$id]) && is_array($this->scripts[$id]))
    foreach($this->scripts[$id] as $script) echo stripslashes($script);
}
function print_script($id) {
  if(isset($this->scripts[$id])) echo stripslashes($this->scripts[$id]);
}
function print_css() {
  if(isset($this->scripts['css'])) {
    echo '<style>';
    foreach($this->scripts['css'] as $script) echo stripslashes($script);
    echo '</style>';
  }
}
   
/* Head
**************************************************************************** */
   
function generate_head() {
      global $post, $page, $paged;
    
      $option=get_option('web_option_basic');
      
      //favicon
      if(isset($option['favicon']) && $option['favicon']!="") { ?>
          <link rel="icon" type="image/png" href="<?php echo home_url().$option['favicon']; ?>">
          <link rel="apple-touch-icon" href="<?php echo home_url().$option['favicon']; ?>">
      <?php } else {
          echo '<link rel="apple-touch-icon" href="'.get_template_directory_uri().'/library/mioweb_icon.png">';
      }
      
      $seo=get_option('seo_basic');
      $foption=get_option('social_option_fac');
      if(!isset($seo['seo'])) 
        $this->get_cms_seometa();  // metadescription

      if(!isset($foption['hide_facebook'])) 
          $this->get_facebook_meta(); //facebook  
          
      do_action('cms_after_facebook_meta');  
      
      //google site verification
      if(isset($option['site_verification']) && $option['site_verification']!="") { ?>
      <meta name="google-site-verification" content="<?php echo esc_attr($option['site_verification']); ?>">
      <?php } 
      
      // google+ author
      $goption=get_option('social_option_g');
      if(isset($goption['gauthor']) && $goption['gauthor']!="") $gauthor=$goption['gauthor'];
      if(is_single()) {
          $page_gauthor=get_the_author_meta( 'google', $post->post_author );
          if($page_gauthor) $gauthor=$page_gauthor;
      }    
      if(isset($gauthor) && $gauthor) { ?>
          <link rel="author" href="<?php echo $gauthor; ?>" />
      <?php } 
      $can_url=get_permalink();
      if(is_home() && get_option('show_on_front')=='posts') {
          $can_url=get_home_url();
      } else if(is_home()) {
          $can_url=get_permalink(get_option('page_for_posts'));    
      } else if(is_category()) {
          global $wp_query;
          $can_url=get_category_link($wp_query->query_vars['cat']);
      } else if(is_tag()) {
          global $wp_query;
          $can_url=get_term_link($wp_query->query_vars['tag_id']);
      } else if(is_tax()) {
          $can_url=get_term_link(get_queried_object()->term_id);
      } else if(is_author()) {
          global $wp_query;
          $can_url=get_author_posts_url($wp_query->query_vars['author']);
      }
      
      ?>   
      <link rel="canonical" href="<?php echo $can_url; ?>" />
      <?php
      if ( is_singular() && get_option( 'thread_comments' ) )
  		    wp_enqueue_script( 'comment-reply' );
      
      $this->print_scripts('header'); 
      $this->print_script('ga'); 
}
function get_cms_title($sep='|') {
    global $post, $page, $paged;
    $title=wp_title( $sep, false, 'right' );
    if($sep) $title.=get_bloginfo( 'name' );
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
    		$title.=" | $site_description";
    if ( $paged >= 2 || $page >= 2 )
    		$title.= ' | ' . sprintf( __( 'Strana %s', 'wpcms' ), max( $paged, $page ) );
    
    $seo=get_option('seo_basic');
    if(!isset($seo['seo'])) {
        if(is_home()) {
            $hometitle=get_option('mw_blog_seo');
            if(isset($hometitle['home_metatitle']) && $hometitle['home_metatitle']!="") $title=esc_attr($hometitle['home_metatitle']);
        } 
        else if(isset($post->ID) && (is_single() || is_page())) {
            $metatitle=get_post_meta($post->ID, 'page_seo', true);
            if(isset($metatitle["metatitle"]) && $metatitle["metatitle"]!="") $title=esc_attr($metatitle["metatitle"]);
        } 
    }
    return $title;
}
function get_cms_seometa() {
    global $post;
    if(isset($post->ID) && (is_single() || is_page())) {
        $page_meta=get_post_meta($post->ID, 'page_seo', true);  
    } else $page_meta='';
    
    $blog_home_meta=get_option('mw_blog_seo');
    
    // description
    $metadesc="";    

    if((is_home())) {
        if(isset($blog_home_meta['home_metadesc'])) $metadesc=esc_attr($blog_home_meta['home_metadesc']);
    } if(is_category()) {
        $metadesc=strip_tags(category_description());
    } else if ( is_tax() ) {
        $metadesc=strip_tags(term_description());
    } else if(is_tag()) {
        $metadesc=strip_tags(tag_description());
    } else if(is_author()) {
        $metadesc=strip_tags(get_the_author_meta( 'description' ));
    } else {
        if(isset($page_meta['metadesc']) && $page_meta['metadesc']!="") $metadesc=esc_attr($page_meta['metadesc']);
        if(isset($post->ID) && !$metadesc && is_single()) $metadesc=strip_tags(get_the_excerpt());
    }
    if($metadesc!="") {
        echo '<meta name="description" content="'.$metadesc.'" />';
    }
    
    // robots
    $robots=array();
    if((is_home())) {
        if(isset($blog_home_meta['home_robots'])) $robots=$blog_home_meta['home_robots'];
    }
    else if(isset($page_meta['robots'])) $robots=$page_meta['robots'];
    
    if(isset($robots['noindex']) || isset($robots['nofollow']) || isset($robots['noarchive'])) { 
        if(isset($robots['is_saved'])) unset($robots['is_saved']);
        ?>
        <meta name="robots" content="<?php echo implode(", ", $robots); ?>" />
    <?php }

    // keywords
    $keywords="";   
    if((is_home())) {
      if(isset($blog_home_meta['home_metakey'])) $keywords=esc_attr($blog_home_meta['home_metakey']);
    }
    else if(isset($page_meta['metakey']) && $page_meta['metakey']!="") $keywords=esc_attr($page_meta['metakey']);
    
    if($keywords!="") {
        echo '<meta name="keywords" content="'.$keywords.'" />';
    } 
}


function get_facebook_meta(){
    global $post;
    $page_fac=array();
    $global_fac=get_option('social_option_fac');
    if(isset($post->ID) && (is_single() || is_page())) $page_fac=get_post_meta($post->ID, "page_facebook", true);
    
    if(is_category()) {
        global $wp_query;
        $can_url=get_category_link($wp_query->query_vars['cat']);
    } else if(is_tag()) {
        global $wp_query;
        $can_url=get_term_link($wp_query->query_vars['tag_id']);
    } else if(is_author()) {
        global $wp_query;
        $can_url=get_author_posts_url($wp_query->query_vars['author']);
    } else if(is_tax()) {
        $can_url=get_term_link(get_queried_object()->term_id);
    } else {
      $can_url=get_permalink();
    }
    // blog home page facebook
    if(is_home()) {
        $home_blog_fac=get_option('blog_facebook');
        $page_fac=$home_blog_fac;
        if(get_option('show_on_front')=='posts') {            
            $can_url=get_home_url();
        } else {
            $can_url=get_permalink(get_option('page_for_posts'));
        }
    }
    // facebook title
    if(isset($page_fac['fac_title']) && $page_fac['fac_title']!="") 
        $title=esc_attr($page_fac['fac_title']);
    else  
        $title=$this->get_cms_title('');
    
    // facebook image
    $ogimage=false;
    if(isset($page_fac['fac_image']) && $page_fac['fac_image']!="") 
        $ogimage=esc_attr(home_url().$page_fac['fac_image']); 
    else if(isset($post->ID) && has_post_thumbnail() && (is_single() || is_page()))
        $ogimage=wp_get_attachment_url( get_post_thumbnail_id($post->ID,'facebook') ); 
    else if(is_category() || is_tag()) {
        global $blog_module;
        if(isset($blog_module->top_panel['image']) && $blog_module->top_panel['image']) 
            $ogimage=esc_attr(home_url().$blog_module->top_panel['image']);
    } 
    else if(isset($global_fac['fac_img']) && $global_fac['fac_img'])
        $ogimage=esc_attr(home_url().$global_fac['fac_img']);
        
    if(is_tax()) {
        $t_id=get_queried_object()->term_id;
        $tax_meta = get_option("mws_eshop_category_fields_" . $t_id);
        if(isset($tax_meta['category_image']) && isset($tax_meta['category_image']['image']) && $tax_meta['category_image']['image'])
            $ogimage=esc_attr(home_url().$tax_meta['category_image']['image']);
    }
        
    if ( is_singular() ) {
			 $type = 'article';
		} else {
			 $type = 'website';
		}
    ?>   
    <meta property="og:title" content="<?php echo $title; ?>"/> 
    
    <?php 
    if(isset($ogimage) && $ogimage) {  ?>
    <meta property="og:image" content="<?php echo $ogimage; ?>" />
    <?php } 
    // facebook description
    
    if(is_tax()) {
        ?>
        <meta property="og:description" content="<?php echo strip_tags(term_description()); ?>" />
        <?php
    }
    else if(isset($page_fac['fac_desc']) && $page_fac['fac_desc']!="") { ?> 
    <meta property="og:description" content="<?php echo esc_attr($page_fac['fac_desc']); ?>" />
    <?php }
    // facebook admin id
    if(isset($global_fac['fac_admin_id']) && $global_fac['fac_admin_id']) { ?>
    <meta property="fb:admins" content="<?php echo esc_attr($global_fac['fac_admin_id']); ?>" /> 
    <?php }
    // facebook url
    ?> 
    <?php if($can_url) { ?>
      <meta property="og:url" content="<?php echo $can_url; ?>" /> 
    <?php } ?>
    <meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>"/>
    <meta property="og:locale" content="<?php echo get_locale(); ?>"/>
    <?php
}       

function facebook_script() {
    $fac=get_option('social_option_fac');
    ?>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/<?php echo get_locale(); ?>/sdk.js#xfbml=1&version=v2.8<?php if(isset($fac['fac_api']) && $fac['fac_api']) echo "&appId=".$fac['fac_api']; ?>";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
<?php
}

/* Footer
**************************************************************************** */

function generate_footer() {
  $this->print_scripts('footer'); 
  $this->print_css();    
}
  
/* Global setting
**************************************************************************** */  
function create_menu() {
    foreach($this->pages as $page) {
        add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], 'create_page',$page['icon_url'],$page['position']);
        if(isset($this->subpages[$page['menu_slug']])) {
            foreach($this->subpages as $subpage) {
                if($subpage['parent_slug']==$page['menu_slug']) add_submenu_page($subpage['parent_slug'], $subpage['page_title'], $subpage['menu_title'], $subpage['capability'], $subpage['menu_slug'], 'create_page');
            }
        }
    }
      
    add_action( 'admin_init', 'register_page_setting' );
}  
   
   
   /* Sidebars
   **************************************************************************** */
   function add_sidebar($args) {
      $this->sidebars[]=$args;
   }
   
   function widgets_content() { 
      $sidebars=$this->get_sidebars();
      $selector=array();
      foreach($sidebars as $sidebar) {
        $selector[]=$sidebar['id'];
      }     
      ?>
      <div class="widgets-holder-wrap cms_add_new_sidebar">
          
          <form action="" method="post">
              <h3><?php echo __('Vytvořit nový sidebar','cms'); ?></h3>
              <?php 
              if(isset($_GET['add_sidebar_error'])) {
                  $errors=array(
                      1=>__('Musíte vyplnit název sidebaru.','cms'),
                      2=>__('Sidebar nebyl vytvořen, nepodařilo se ověřit oprávnění akce.','cms'),
                      3=>__('Sidebar s tímto názvem již existuje. Zkuste jiný název.','cms')
                  );
                  echo '<div class="cms_error_box">'.$errors[$_GET['add_sidebar_error']].'</div>';
              }
              if(isset($_GET['ok'])) {
                  echo '<div class="cms_confirm_box">'.__('Sidebar byl vytvořen.','cms').'</div>';
              }  
              ?>
              <input id="cms_new_sidebar_name" class="cms_text_input" type="text" name="sidebar_name" placeholder="<?php echo __('Název nového sidebaru','cms'); ?>" />
              <input type="hidden" name="widget_action" value="cms_create_new_sidebar" />
              <?php wp_nonce_field('cms_create_new_sidebar'); ?>
              <input type="submit" class="cms_button_secondary cms_create_new_sidebar" name="cms_create_new_sidebar" value="<?php echo __('Vytvořit sidebar','cms'); ?>" />
          </form>
      </div>
      <script>
      jQuery(document).ready(function($) {
          $("#<?php echo implode(', #',$selector); ?>").append('<div class="cms_delete_widget_container">(<a class="cms_delete_widget" href="#" data-question="<?php echo __('Opravdu chcete tento sidebar smazat?', 'cms'); ?>"><?php echo __("Smazat","cms"); ?></a>)</div>');     
      });
      </script>
      <?php
   }
   function get_sidebars(){
		  $sidebars = get_option('cms_sidebars');
		  if($sidebars)
			   return $sidebars;
		  return array();
	 }
   function register_sidebars() {
      $sidebars = $this->get_sidebars();
      foreach($sidebars as $sidebar) {
          $this->add_sidebar($sidebar);  
      }
  		if(!empty($this->sidebars)){
  			foreach($this->sidebars as $sidebar){
          $sidebar['before_title'] = '<div class="title_element_container widgettitle">';
	        $sidebar['after_title'] = '</div>'; 
  				register_sidebar($sidebar);
  			}
  		}
   }
   function widget_actions() {

      $action = $_POST['widget_action'];
      $nonce = $_REQUEST['_wpnonce'];
      $err=0;
      if($action=="cms_create_new_sidebar") {   
          if(wp_verify_nonce($nonce, 'cms_create_new_sidebar')){              
              $name = stripslashes(trim($_POST['sidebar_name']));
              if(empty($name)) $err=1;  
              else {
                  $sidebars = $this->get_sidebars();
                  $id = 'cms_' . sanitize_html_class(sanitize_title_with_dashes($name));
                  $exist=false;
                  foreach($sidebars as $sidebar) {
                      if($sidebar['id']==$id) $exist=true;
                  }
                  if(!$exist) {
                      $sidebars[]=array(
            						'name' => __( $name ,'cms'),
            						'id' => $id,
            						'description' => '',
            					);
                      update_option('cms_sidebars', $sidebars );
                  } else $err=3; 
              }  
          }
          else $err=2;
          $attr=($err)? '?add_sidebar_error='.$err : '?ok=1'; 
          wp_redirect(admin_url('widgets.php'.$attr));
      }
      
      
   }
   function delete_sidebar(){
      $id=$_POST['id'];
		  $sidebars = $this->get_sidebars();
      $deleted=array();
		  foreach($sidebars as $sidebar) {
          if($sidebar['id']!=$id) $deleted[]=$sidebar;
      }
      update_option('cms_sidebars', $deleted );
	 }
   
// REDIRECT
// **********************************************************************

function cms_redirect_page()
{
    global $post,$vePage;


    if(isset($post->ID) && !current_user_can('administrator') && (is_page() || is_single())) {
      $redirect=get_post_meta($post->ID, "page_redirect", true);
      
      $redirect_url=(isset($redirect['redirect_url']))? $vePage->create_link($redirect['redirect_url'],false):'';
      
      if($redirect_url) {
          $red=true;
          
          if(isset($redirect['redirect_type']) && $redirect['redirect_type']) $red_type=$redirect['redirect_type'];
          else $red_type=302;
          
          if($red_type==302) {
              // redirect after date
              if(isset($redirect['redirect_date']) && $redirect['redirect_date']['date'] && strtotime($redirect['redirect_date']['date'].' '.$redirect['redirect_date']['hour'].':'.$redirect['redirect_date']['minute'].'')>current_time( 'timestamp' )) {
                  $red=false;
              }
              // redirect x days after enter to campaign
              if(isset($redirect['redirect_campaign']) && $redirect['redirect_campaign']) {
                  if(isset($_COOKIE['mioweb_campaign_access'])) {
                      $campaign_id = get_post_meta( $post->ID, 'mioweb_campaign',true );
                      $access=unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
                      if(isset($access['time'][$campaign_id['campaign']])) $time=$access['time'][$campaign_id['campaign']]+($redirect['redirect_campaign']*3600*24);  
                      else $time=current_time( 'timestamp' );         
                                            
                      if(strtotime(Date('d.m.Y',$time).' 23:59')>current_time( 'timestamp' )) $red=false;
                  } else $red=false;
              }
          }
          
          if($red) {
              wp_redirect( $redirect_url, $red_type);
              exit();
          }
      }
      $redirect_mobile_url=(isset($redirect['redirect_mobile_url']))? $vePage->create_link($redirect['redirect_mobile_url'],false):'';
      if($this->is_mobile && $redirect_mobile_url) {
          wp_redirect( $redirect_mobile_url );
          exit();
      }
    }  
}  


// Modules
// **********************************************************************

function add_module($name,$license=1, $path=TEMPLATEPATH) {
    if($name=='visualeditor' || $name=='smartselling' || in_array($name,$this->license['modules']) || $license==0) {  
        require_once($path . '/modules/'.$name.'/init.php');
        $this->modules[$name]['module']=$name;
        $this->modules[$name]['license']=$license;
    }
}
function add_version($name, $version) {
    $this->versions[$name]=$version;
} 

// Theme activation
// **********************************************************************
function cms_activation() {
  $versions=get_option('cms_versions');  
  //first activation
  if(empty($versions)) {   
      foreach($this->page_set as $id=>$set_group) {
        if(!get_option( $id )) {
            $setting=$this->get_default_option($set_group);  
            if(!empty($setting)) add_site_option( $id, $setting); 
        }
      } 
       
  }
   
  do_action('cms_activation', $versions); 
  
  if(empty($versions)) add_option('cms_versions',$this->versions);
  else update_option('cms_versions',$this->versions);   
  
  //wp_redirect(home_url());
  //die();  
}
function get_default_option($set_group, $setting=array()) {
    foreach($set_group as $set) {  
        //print_r($set);
        if(isset($set['tabs'])) {
            foreach($set['tabs'] as $id=>$tab) {

                if(isset($set['content'])) $setting[$set['id']]=$set['content'];
                $setting=$this->get_default_option($tab['setting'], $setting);
            }
        }
        else if(isset($set['type']) && $set['type']=='toggle_group') {
            if(isset($set['content'])) $setting[$set['id']]=$set['content'];
            foreach($set['setting'] as $id=>$tab) {
                
                $setting=$this->get_default_option($set['setting'], $setting);
            }
        }
        else if(isset($set['type']) && $set['type']=='group') {
            foreach($set['setting'] as $id=>$tab) {

                if(isset($set['content'])) $setting[$set['id']]=$set['content'];
                $setting=$this->get_default_option($set['setting'], $setting);
            }
        }
        else if(isset($set['content'])) $setting[$set['id']]=$set['content'];
    }
    return $setting;
}

    public function update_image_meta() {
        if( !current_user_can( 'edit_posts' ) || !isset( $_POST[ 'id' ] ) ) return false;

        update_post_meta( intval( $_POST[ 'id' ] ), '_wp_attachment_image_alt', sanitize_text_field( $_POST[ 'alt' ] ) );
        wp_update_post( array(
            'ID' => intval( $_POST[ 'id' ] ),
            'post_excerpt' => sanitize_text_field( $_POST[ 'caption' ] )
        ) );

        wp_die();

    }

  /**
   * @var int Counter how many times has been disabled saving of sets.
   */
  private $_save_lock_count = 0;

  public function is_save_disabled() {
    $res = (bool)($this->_save_lock_count);
//    mwlog('cms', 'is save disabled = ' . $res, MWLL_DEBUG, 'save');
    return $res;
  }

  /**
   * Disable automatic saving of field set. Reentrant.
   *
   * Counterpart of this method is {@link save_enable()}. Call these methods to effectively disable automatic saving
   * mechanism. This supports to disable saving more times in a sequence and to reenable it afterwards.
   * Un/hooking does not work correctly for this case.
   */
  public function save_disable() {
    mwlog('cms', 'save disable', MWLL_DEBUG, 'save');
    if($this->_save_lock_count == 0) {
//      mwlog('cms', 'UNHOOK cms_save_data', MWLL_DEBUG, 'save');
//      remove_action('save_post', 'cms_save_data');
    }
    $this->_save_lock_count++;
  }

  /** Enable automatic saving of field set. Reentrant. */
  public function save_enable() {
    mwlog('cms', 'save enable', MWLL_DEBUG, 'save');
    $this->_save_lock_count--;
    if($this->_save_lock_count == 0) {
//      add_action('save_post', 'cms_save_data');
//      mwlog('cms', 'HOOK cms_save_data', MWLL_DEBUG, 'save');
    }
  }

  /**
   * @var bool Flag that tells that saving of sets is currently in progress. This can be used to check if custom save
   * WP hooks should be skipped or not. Typically not when called within saving operation.
   */
  public $is_saving = false;
    
}

/** Suspend autosave of field sets. Reentrant. */
function cms_save_disable() {
  global $cms;
  $cms->save_disable();
}

/** Reenable autosave of field sets. Reentrant. */
function cms_save_enable() {
  global $cms;
  $cms->save_enable();
}

function cms_is_saved_disabled() {
  global $cms;
  return $cms->is_save_disabled();
}

function cms_is_saving() {
  global $cms;
  return $cms->is_saving;
}

?>
