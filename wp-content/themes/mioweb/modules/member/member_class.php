<?php

/** Name of meta field of user, where its "billing id" is stored. */
define('META_BILLING_ID','billing_user_id');

class MemberSection {
var $edit_mode;
var $member_page;
var $member_sections;
var $member_section;
var $member_section_id;
var $user;
var $setting;
var $first_member;
var $user_sections;
var $is_login;
var $script_version;
var $js_texts;
  
function __construct(){ 
    if ( current_user_can('edit_pages') ) $this->edit_mode=true;  
    else $this->edit_mode=false; 
    
    $this->check_version();
    
    $this->member_sections = get_option('member_basic');  
    $this->user = wp_get_current_user();   
    
    $this->script_version=filemtime(get_template_directory().'/style.css');
    
    require_once('js/js_texts.php');
    $this->js_texts=$js_texts;
    
    if($this->edit_mode) {    
        if(isset($_POST["member_save_member_section"]) && isset($_POST["ve_save_global_setting"])) {
            add_action( 'init', array($this, 'after_save_admin_member') );  
            add_action( 've_after_save_options', array($this, 'after_save_member') ); 
        }
        //ajax
        add_action('wp_ajax_add_new_member', array($this, 'add_new_member'));
        add_action('wp_ajax_add_new_member_level', array($this, 'add_new_member_level'));
        add_action('wp_ajax_open_member_setting', array($this, 'open_member_setting'));
        
        //profile
        add_action( 'show_user_profile', array($this, 'member_generate_password') );
        add_action( 'edit_user_profile', array($this, 'member_generate_password') );
        add_action( 'show_user_profile', array($this, 'mw_add_profile_fields') );
        add_action( 'edit_user_profile', array($this, 'mw_add_profile_fields') );         
        
        add_action( 'personal_options_update', array($this, 'update_profile_fields') );
        add_action( 'edit_user_profile_update', array($this, 'update_profile_fields') );

        
        // admin pages
        add_action('admin_menu',  array($this, 'add_user_submenu'));
        
        //create page
        add_action( 've_create_page', array($this, 'action_create_page') ); 
        add_action( 've_create_page_copy', array($this, 'action_create_page') ); 
      
        //scripts
        add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts') );
        
        add_action("cms_check_version", array($this, 'member_activation'));
        
        if(isset($_POST['member_action'])) add_action( 'init', array($this,'actions'));
        if(isset($_GET['member_generate_new_pass']) && $_GET['member_generate_new_pass'] == 1) add_action( 'init', array($this,'actions'));
        
        // custom columns in user list
        add_filter('manage_users_columns', array($this,'member_user_list_columns'));
        add_action('manage_users_custom_column',  array($this,'member_user_list_columns_content'), 100, 3);

        //get first member for menu
        if(!empty($this->member_sections) && isset($this->member_sections['members'])) $this->first_member=reset($this->member_sections['members']);   
                
    } 
    add_action( 'wp_enqueue_scripts', array($this, 'load_member_scripts')) ;
    
    // member news
    add_action( 'init', array($this, 'register_member_news') );
    
    //login custom      
    add_action('login_head', array($this, 'custom_login_css'));
      
    //menu
    add_filter( 'wp_nav_menu_objects', array($this, 'member_menu') );
     
    //init
    add_action( 'wp', array($this,'member_init'),2); 
    add_action( 'init', array($this, 'update_user_profile') ); 
    
    //visual setting
    add_action( 've_global_setting', array($this, 'use_member_setting') );
    
    add_role( "member", __("Člen",'cms_member'), array('read' => true, 'edit_posts' => false,'delete_posts' => false)); 
    
    // redirect after login if member login via wp login page
    add_filter( 'login_redirect', array($this, 'login_redirect'), 15, 3 );

    // save element data
    add_action( 'wp_ajax_nopriv_save_element_data', array($this, 'save_element_data') ); 
    add_action( 'wp_ajax_save_element_data', array($this, 'save_element_data') );  
    
    if(isset($_GET['add_new_member'])) add_action('init', array($this, 'fapi_notification'));
    if(isset($_POST['member_free_registration'])) add_action('wp', array($this, 'free_registration'));
}

function use_member_setting() {
  global $vePage;
  global $post;
  
  if($vePage->post_id) {
      $page_member=get_post_meta($vePage->post_id, 'page_member', true);
      if(isset($page_member['member_page'])){ 
          $page_setting=get_option('member_appearance');
          $header_setting=get_option('member_header');
          $footer_setting=get_option('member_footer');
          $popup_setting=get_option('member_popups');  
          
          $vePage->page_setting=$page_setting['members'][$page_member['member_section']['section']];
          $vePage->header_setting=$header_setting['members'][$page_member['member_section']['section']];
          $vePage->footer_setting=$footer_setting['members'][$page_member['member_section']['section']];
          if(is_user_logged_in()) $vePage->popups->popups_setting=$popup_setting['members'][$page_member['member_section']['section']];
          
          $vePage->h_menu=(isset($vePage->header_setting['menu']))?$vePage->header_setting['menu']:''; 
          $vePage->f_menu=(isset($vePage->footer_setting['menu']))?$vePage->footer_setting['menu']:''; 
      } 
  } 
}

function member_init() {
    global $vePage,$post;
    
    $redirect=false;
    
    if($vePage->post_id) {
    
        $default_login=array(
                        '0'=>array(
                          'class'=>'',
                          'style'=>array(
                            'background_color'=>array(
                              'color1'=>'#ffffff',
                              'color2'=>'',
                              'transparency'=>'100',
                            ),
                            'link_color'=>'#a1a1a1',
                            'type'=>'fixed',
                            'padding_top'=>'40',
                            'padding_bottom'=>'20',
                            'padding_left'=>array(
                              'size'=>'40',
                              'unit'=>'px',
                            ),
                            'padding_right'=>array(
                              'size'=>'40',
                              'unit'=>'px',
                            ),
                            'margin_t'=>array(
                              'size'=>'100',
                            ),
                            'margin_b'=>array(
                              'size'=>'100',
                            ),
                          ),
                          'content'=>array(
                            '0'=>array(
                              'type'=>'',
                              'class'=>'',
                              'content'=>array(
                                '0'=>array(
                                  'type'=>'text',
                                  'content'=>'<p style="text-align: center;">'.__('Tato sekce je pouze pro členy. Pokud jste členem, vložte prosím své přihlašovací údaje a přihlaste se.','cms_member').'</p>',
                                  'style'=>array(
                                    'font'=>array(
                                      'font-size'=>'',
                                      'font-family'=>'',
                                      'weight'=>'',
                                      'color'=>'',
                                    ),
                                    'li'=>'1',
                                  ),
                                ),
                                '1'=>array(
                                  'type'=>'member_login',
                                  'content'=>'',
                                  'style'=>array(
                                    'input-style'=>'8',
                                    'form-font'=>array(
                                      'font-size'=>'14',
                                      'color'=>'',
                                    ),
                                    'background'=>'#f0f0f0',
                                    'button'=>array(
                                      'style'=>'8',
                                      'font'=>array(
                                        'font-size'=>'22',
                                        'font-family'=>'',
                                        'weight'=>'',
                                        'color'=>'#ffffff',
                                        'text-shadow'=>'none',
                                      ),
                                      'background_color'=>array(
                                        'color1'=>'#007ea6',
                                        'color2'=>'#006587',
                                      ),
                                      'corner'=>'6',
                                      'border-color'=>'',
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                    );
    
        $page_member=get_post_meta($vePage->post_id, 'page_member', true);   
        $this->member_page=(isset($page_member['member_page']))? true:false ;
        //if is page of member section         
        
        if($this->member_page) {
            $this->setting=$page_member;                            
            $this->member_section=$this->member_sections['members'][$this->setting['member_section']['section']];
            $this->member_section_id=$this->setting['member_section']['section'];
            $this->is_login=($vePage->post_id==$this->member_section['login'])? true:false ; 
            
            $vePage->modul_type='member';
            
            // if user is not logged and page is not login page
            if(0 == $this->user->ID && !$this->is_login) {
                
                if($this->member_section['login']) {
                    $vePage->set_page($this->member_section['login']); 
                                    
                } else {        
                    // print default login page
                    $vePage->page_setting['background_color']='#dbdbdb';
                    $vePage->page_setting['background_image']=array();
                    $vePage->page_setting['page_width']=array('size'=>'400','unit'=>'px');
                    $vePage->header_setting['show']='noheader';
                    $vePage->footer_setting['show']='nofooter';
                    $vePage->template['directory']='page/1/';
                    $vePage->layer=$default_login;                      
                };  
                $this->is_login=true;            
            }
            
            //dont do this if page is login
            if(!$this->is_login) {
                $this->user_sections=get_the_author_meta( 'cms_member', $this->user->ID );
    
                $this->user->access=(isset($this->user_sections[$this->setting['member_section']['section']]))? true: false;
                $this->user->level_access=true;
                
                // time limited member control
                if($this->user->access && isset($this->user_sections[$this->setting['member_section']['section']]['end']) && $this->user_sections[$this->setting['member_section']['section']]['end']!='') {
                    $end=$this->user_sections[$this->setting['member_section']['section']]['end'];
                    if(strtotime($end.' '.$this->user_sections[$this->setting['member_section']['section']]['time'])<current_time( 'timestamp' )) {
                        $this->user->access=false;  
                        wp_logout();
                        if(isset($this->member_section['expire_page']) && $this->member_section['expire_page'])
                            $redirect=get_permalink($this->member_section['expire_page']);
                    }                  
                }
                
                if($this->user->access) { 
                
                    // member levels check
                    if(isset($this->setting['member_section'][$this->setting['member_section']['section']]['levels'])) {
                        $this->user->level_access=false;
                        if(isset($this->user_sections[$this->setting['member_section']['section']]['levels'])) {
                            foreach($this->user_sections[$this->setting['member_section']['section']]['levels'] as $ul_id=>$ul_key) {
                                if(array_key_exists($ul_id,$this->setting['member_section'][$this->setting['member_section']['section']]['levels']))  $this->user->level_access=true;
                            }   
                        } 
                        
                    }
                    
                    if($this->user->level_access) $this->user->member_registered=$this->user_sections[$this->setting['member_section']['section']]['date'].' '.$this->user_sections[$this->setting['member_section']['section']]['time'];
                                        
                }
                
                if($redirect) {
                
                    wp_redirect($redirect);
                    die();
                
                } else {
                
                    add_action( 'body_class', array( $this, 'add_bodyclass' ));
                    add_action( 'wp_footer', array( $this, 'add_user_panel' ));
                    add_action( 'cms_after_menu', array( $this, 'insert_user_avatar' ));   
                    
                    global $vePage;
                    if($this->member_section['dashboard']) $vePage->home_url=get_permalink($this->member_section['dashboard']); 
                    // if is user with no access  
                    if(!$this->edit_mode && !$this->user->access) {
                        if($this->member_section['login'])
                            $vePage->set_page($this->member_section['login']); 
                        else {        
                            // print default login page
                            $vePage->page_setting['background_color']='#dbdbdb';
                            $vePage->page_setting['background_image']=array();
                            $vePage->page_setting['page_width']=array('size'=>'400','unit'=>'px');
                            $vePage->header_setting['show']='noheader';
                            $vePage->footer_setting['show']='nofooter';
                            $vePage->template['directory']='page/1/';
                            $vePage->layer=$default_login;                      
                        };
                        $this->is_login=true; 
                    }
                    if(!$this->edit_mode && !$this->user->level_access) {
                    
                        $content_text='<p style="text-align:center;">'.__("Pro přístup k této stránce nemáte dostatečné oprávnění.",'cms_member').'</p><p style="text-align:center;"><a href="'.get_permalink($this->member_section['dashboard']).'">'.__("Přejít na nástěnku",'cms_member').'</a></p>';
                        if(!empty($this->setting['member_section'][$this->setting['member_section']['section']]['levels'])) {
                            reset($this->setting['member_section'][$this->setting['member_section']['section']]['levels']);
                            $first_key = key($this->setting['member_section'][$this->setting['member_section']['section']]['levels']);   
                            if(isset($this->member_section['levels'][$first_key]['noaccess_text']) && $this->member_section['levels'][$first_key]['noaccess_text']) 
                                $content_text=($this->member_section['levels'][$first_key]['noaccess_text']);                         
                        } 
                                
                        if(isset($this->member_section['levels'][$first_key]['noaccess_page']) && $this->member_section['levels'][$first_key]['noaccess_page']) {
                            $vePage->set_page($this->member_section['levels'][$first_key]['noaccess_page']); 
                        }
                        else {
                            $vePage->template['directory']='page/1/';
                            $vePage->layer=array(
                            '0'=>array(
                              'class'=>'',
                              'style'=>array(
                                'type'=>'fixed',
                                'background_color'=>array(
                                  'color1'=>'#ffffff',
                                  'color2'=>'',
                                  'transparency'=>'100',
                                ),
                                'padding_top'=>'60',
                                'padding_bottom'=>'60',
                                'margin_t'=>array(
                                  'size'=>'40',
                                ),
                                'margin_b'=>array(
                                  'size'=>'40',
                                ),
                              ),
                              'content'=>array(
                                '0'=>array(
                                  'type'=>'col-one',
                                  'class'=>'',
                                  'content'=>array(
                                    '0'=>array(
                                      'type'=>'text',
                                      'content'=>$content_text,
                                      'style'=>array(),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          );
        
        
                        }
                    }
                    //is logged and has no access - evergreen, levels
                    $evergreentime=(isset($this->setting['evergreen']) && $this->setting['evergreen']>0)? (strtotime($this->user->member_registered)+($this->setting['evergreen']*86400)):0;
                    $evergreentime=(isset($this->setting['evergreen_datetime']) && $this->setting['evergreen_datetime']['date'])? strtotime($this->setting['evergreen_datetime']['date'].' '.$this->setting['evergreen_datetime']['hour'].':'.$this->setting['evergreen_datetime']['minute']):$evergreentime;
        
                    if(!$this->edit_mode && $evergreentime && $evergreentime>current_time( 'timestamp' ))
                        add_filter( 've_content', array($this,'no_access_message'));  
            }  
        }
        }
    }
}

function member_menu( $items ) {
  $new_items=array();
  foreach ( $items as $item ) {
    if($this->get_level_access($item->object_id)) $new_items[]=$item;
  }
  return $new_items;    
}

function get_level_access($post_id) {
    $page_setting=get_post_meta($post_id, 'page_member', true);
    $level_access=true;
    if(!$this->edit_mode && isset($page_setting['member_section']) && isset($page_setting['member_section'][$this->member_section_id]['levels'])) {  

              $la_intersect=array();
                    
              foreach($page_setting['member_section'][$this->member_section_id]['levels'] as $key=>$val) {                                        
                  if(isset($this->member_sections['members'][$this->member_section_id]['levels'][$key]['show']) || (isset($this->user_sections[$this->member_section_id]['levels']) && array_key_exists($key,$this->user_sections[$this->member_section_id]['levels'])))
                      $la_intersect[]=$key;   
              }
                    
              if(!count($la_intersect)) $level_access=false;

    } 
    
    return $level_access;      
}

function insert_user_avatar() {
    if(!$this->is_login) {
        global $current_user;
        ?>
            <div id="member_user_avatar" class="member_user_menu_close"> 
                <?php echo get_avatar( $current_user->ID, 30 ); ?>         
                <div id="member_user_menu"> 
                      <h2 class="member_user_name">
                          <?php echo $current_user->display_name . "\n"; ?>   
                      </h2>
                      
                      <ul>
                          <li><a id="member_show_profile" href="#"><?php echo __("Můj profil","cms_member"); ?></a></li>                          
                          <li><a href="<?php echo wp_logout_url(get_permalink($this->member_section['dashboard'])); ?>" title="Logout"><?php echo __("Odhlásit se","cms_member"); ?></a></li>
                      </ul> 
                      <?php if(count($this->user_sections)>1) { ?>
                          <div class="member_user_sections"><?php echo __('Moje členské sekce:','cms_member'); ?></div>
                          <ul>
                              <?php foreach($this->user_sections as $key=>$section) { 
                                  if(isset($this->member_sections['members'][$key]) && $this->member_sections['members'][$key]['dashboard']) echo '<li><a '.(($key==$this->setting['member_section']['section'])?'class="mem_current_member"':'').' href="'.get_permalink($this->member_sections['members'][$key]['dashboard']).'">'.$this->member_sections['members'][$key]['name'].'</a></li>';
                              } ?>
                          </ul>
                      <?php } ?>             
                </div>    
            </div>
        <?php
    }
}
function no_access_message() {
    $content='<div class="member_noaccess_message_box">
        <p>'.__('K obsahu této stránky nemáte přístup.','cms_member').'</p>
        <a href="'.get_permalink($this->member_section['dashboard']).'">'.__('Zpět do členské sekce.','cms_member').'</a>
    </div>';
    return $content;
}

//after save member section
function after_save_member() {
    $this->after_save_action(get_option('member_basic'));
} 
function after_save_admin_member() {
    $mem_set=(isset($_POST['member_basic']))? $_POST['member_basic'] : array();
    $this->after_save_action($mem_set);
} 
function after_save_action($sections) {
    $pages = get_pages();
    foreach($pages as $page) {
        $meta=get_post_meta($page->ID,'page_member', true);
        if(isset($meta['member_page']) && (!isset($sections['members']) || !isset($sections['members'][$meta['member_section']['section']]))) {
            unset($meta['member_page']);
            update_post_meta($page->ID, 'page_member', $meta);
        }
    }
}

function add_user_panel() {
  global $current_user;
  global $post;
  
  $member_fields=get_user_meta($current_user->ID,'member_fields', true);
  $hide_member=get_user_meta($current_user->ID,'mw_hide_member', true);
  $custom_fields = get_option('mw_member_user_custom_fields');
  
  ?>
      <div id="member_profile_background"></div>
      <div id="member_profile">
          <h2><?php echo __("Můj profil","cms_member"); ?></h2>
          <form method="post" action="" enctype="multipart/form-data">        
              <div class="member_profile_row member_profile_row_login">
                  <div class="label"><?php echo __("Uživatelské jméno (nelze změnit)","cms_member"); ?></div>
                  <span class="noinput"><?php echo $current_user->user_login; ?></span>
              </div>
              <div class="member_profile_row member_profile_row_first_name">
                  <label><?php echo __("Křestní jméno","cms_member"); ?> </label>
                  <input class="text" type="text" name="user[first_name]" value="<?php if(!empty($current_user->user_firstname)){ echo $current_user->user_firstname;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_last_name">
                  <label><?php echo __("Příjmení","cms_member"); ?></label>
                  <input class="text" type="text" name="user[last_name]" value="<?php if(!empty($current_user->user_lastname)){ echo $current_user->user_lastname;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_last_domain">
                  <label><?php echo __("Můj obor","cms_member"); ?></label>
                  <input class="text" type="text" name="member_fields[domain]" value="<?php if(isset($member_fields['domain'])){ echo $member_fields['domain'];} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_email">
                  <label for="user_email"><?php echo __("E-mail","cms_member"); ?> <span><?php echo __("(povinný)","cms_member"); ?></span></label>
                  <input class="text" type="text" name="user[user_email]" value="<?php if(!empty($current_user->user_email)){ echo $current_user->user_email;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_description">
                  <label for="description"><?php echo __("O mně","cms_member"); ?></label>
                  <textarea class="text" type="text" name="user[description]" ><?php if(!empty($current_user->description)){ echo $current_user->description;} ?></textarea>
              </div>
              
              <div class="member_profile_row member_profile_row_picture">
                    <label><?php echo __("Profilový obrázek","cms_member"); ?></label>
                    <div class="member_profile_avatar_row"><?php echo get_avatar( get_current_user_id(), 60 ); ?> <?php echo __('Svůj profilový obrázek (avatar) si můžete nastavit na adrese',"cms_member").' <a target="_blank" href="http://cs.gravatar.com/">gravatar.com</a>. <small>'.__('Výhodou této služby je, že si bude váš profilový obrázek pamatovat pro všechny weby postavené na wordpressu, a když se kdekoli registrujete nebo vložíte komentář pod stejným e-mailem, bude se tento profilový obrázek automaticky zobrazovat.',"cms_member"); ?></small></div> 
              </div> 
              
              
              <?php 
              // custom fields
                            
              if(is_array($custom_fields) && count($custom_fields)) {  
                  
                  $user_custom_fields=get_user_meta($current_user->ID,'member_custom_field', true);
                              
                  foreach($custom_fields as $field) { ?>
                      <div class="member_profile_row member_profile_row_<?php echo $field['id']; ?>">
                          <label for="user_<?php echo $field['id']; ?>"><?php echo $field['title'] ?></label>
                          <?php   
                          
                          $val=(isset($user_custom_fields[$field['id']]))? $user_custom_fields[$field['id']] : '';                          
                          if($field['type']=='text') echo '<input type="text" class="text" value="'.$val.'" name="member_custom_field['.$field['id'].']" />';
                          else echo '<textarea class="text" name="member_custom_field['.$field['id'].']" rows="5" cols="30">'.$val.'</textarea>';
                          
                          if($field['description']) echo '<p class="description">'.$field['description'].'</p>';
                          ?>

                      </div>
                  <?php 
                  }    
              }
              ?>
              
              <h2><?php echo __("Kontaktní informace","cms_member"); ?></h2>
              
              <div class="member_profile_row member_profile_row_url">
                  <label for="user_url"><?php echo __("Webové stránky","cms_member"); ?></label>
                  <input class="text" type="text" name="user[user_url]" value="<?php if(!empty($current_user->user_url)){ echo $current_user->user_url;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_facebook">
                  <label for="facebook"><?php echo __("Facebook","cms_member"); ?></label>
                  <input class="text" type="text" name="user[facebook]" value="<?php if(!empty($current_user->facebook)){ echo $current_user->facebook;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_twitter">
                  <label for="twitter"><?php echo __("Twitter","cms_member"); ?></label>
                  <input class="text" type="text" name="user[twitter]" value="<?php if(!empty($current_user->twitter)){ echo $current_user->twitter;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_linkedin">
                  <label for="linkedin"><?php echo __("Linkedin","cms_member"); ?></label>
                  <input class="text" type="text" name="user[linkedin]" value="<?php if(!empty($current_user->linkedin)){ echo $current_user->linkedin;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_google">
                  <label for="google"><?php echo __("Google+","cms_member"); ?></label>
                  <input class="text" type="text" name="user[google]" value="<?php if(!empty($current_user->google)){ echo $current_user->google;} ?>" />
              </div>
              <div class="member_profile_row member_profile_row_youtube">
                  <label for="youtube"><?php echo __("YouTube","cms_member"); ?></label>
                  <input class="text" type="text" name="user[youtube]" value="<?php if(!empty($current_user->youtube)){ echo $current_user->youtube;} ?>" />
              </div>
              
              <h2><?php echo __("Zobrazení v katalogu členů","cms_member"); ?></h2>
              <div class="member_profile_row_hide_email">
                  <input id="mem_fields_hide_email" type="checkbox" value="1" <?php if(isset($member_fields['hide_email'])) echo 'checked="checked"'; ?>" name="member_fields[hide_email]" />
                  <label for="mem_fields_hide_email"><?php echo __('Nezobrazovat můj email v katalogu členů','cms_member') ?></label>
              </div>
              <div class="member_profile_row_hide_member">
                  <input id="mem_fields_hide_member" type="checkbox" value="1" <?php if(isset($hide_member) && $hide_member) echo 'checked="checked"'; ?>" name="hide_member" />
                  <label for="mem_fields_hide_member"><?php echo __('Nezobrazovat můj profil v katalogu členů','cms_member') ?></label>
              </div>               
              
              <h2><?php echo __("Nové heslo","cms_member"); ?></h2>
              <div class="member_profile_row member_profile_row_password">
                  <label><?php echo __("Heslo","cms_member"); ?></label>
                  <input class="text" type="password" name="user[user_pass]" autocomplete="off" value="<?php if(isset($_POST['USER']['user_pass'])) echo $_POST['USER']['user_pass']; ?>" />
              </div>
              <div class="member_profile_row member_profile_row_password2">
                  <label><?php echo __("Znovu heslo","cms_member"); ?></label>
                  <input class="text" type="password" name="pass2" autocomplete="off" value="<?php if(isset($_POST['pass2'])) echo $_POST['pass2']; ?>" />
              </div>  
            
                  
              <div class="member_profile_button_row">
                  <input class="member_profile_button" type="submit" value="<?php echo __("Uložit profil","cms_member"); ?>" name="save_profile"/>
                  <input type="hidden" value="<?php echo $current_user->ID; ?>" name="user[ID]"/>
                  <input type="hidden" value="<?php echo $post->ID; ?>" name="post_id"/>
                  <?php wp_nonce_field('client-file-upload');  ?>
              </div>
        
            </form>
            <a id="member_close_profile" href="#"><?php echo __("Zavřít profil","cms_member"); ?></a>
      </div>
      <?php

}

function update_user_profile(){
    if(isset($_POST['save_profile'])) {
        $user = $_POST['user'];
        $error = 0;
        
        if($user['first_name']!="" && $user['last_name']!="") $user['display_name']=$user['first_name']." ".$user['last_name'];   
        else if($user['first_name']!="") $user['display_name']=$user['first_name'];
                
        if($user['user_email']!="" && is_email($user['user_email']) ) {
            
    
            if(!empty($user['user_pass'])){
              if(strlen($user['user_pass']) > 4){ 
                 if($user['user_pass']!=$_POST['pass2']) $error = 3;         
              }
              else $error = 2;
            } 
            else{ 
                unset($user['user_pass']);
            } 
        }
        else $error=1;

        if(!$error) {
            wp_update_user($user);
            if(isset($_POST['member_custom_field'])) {
                update_user_meta($user['ID'],'member_custom_field',$_POST['member_custom_field']);   
            }
            if(isset($_POST['member_fields'])) {
                update_user_meta($user['ID'],'member_fields',$_POST['member_fields']);   
            }
            if(isset($_POST['hide_member'])) {
                update_user_meta($user['ID'],'mw_hide_member',$_POST['hide_member']);   
            } else delete_user_meta($user['ID'],'mw_hide_member');
        }
        wp_redirect( get_permalink($_POST['post_id']).'?profile_message='.$error );
        die();
    }
}

function delete_profile_img(){
  
  $profile_image=get_user_meta($current_user->ID, 'profile_image', true);
  wp_delete_attachment( $profile_image );
  delete_user_meta( $user_id, 'profile_image' );
  //wp_redirect( home_url('/member/?muj-profil=1'));
}

function load_admin_scripts() {
    $current_screen = get_current_screen();
    if ( (isset($_GET['page']) && ($_GET['page']=="member_option" || $_GET['page']=="appearancemember_option")) || isset($_GET['post']) )  {   
        wp_enqueue_script('member_admin_script', MEMBER_DIR.'js/admin.js', array('jquery'),$this->script_version);
        wp_enqueue_style('member_admin_css',MEMBER_DIR.'css/admin.css',array(),$this->script_version ); 
        
        wp_localize_script( 'member_admin_script', 'mem_texts', $this->js_texts['admin']);
    }
}   
function load_member_scripts() { 
    wp_register_script( 'member_checklist_script',get_bloginfo('template_url').'/modules/member/js/checklist.js',array('jquery'),'2.0.0.705');
    if($this->edit_mode) {  
        wp_enqueue_script('member_admin_script', MEMBER_DIR.'js/admin.js', array('jquery'),$this->script_version);
        wp_enqueue_style('member_admin_css',MEMBER_DIR.'css/admin.css',array(),$this->script_version );
        wp_enqueue_style('member_front_editor_css',MEMBER_DIR.'css/front_editor.css',array(),$this->script_version );  
        
        wp_enqueue_script('member_checklist_script');

        wp_localize_script( 'member_admin_script', 'mem_texts', $this->js_texts['admin']);
    }

    

    wp_enqueue_script('member_front_script', MEMBER_DIR.'js/front.js',array(),$this->script_version);
          
    wp_enqueue_style('member_content_css',MEMBER_DIR.'css/content.css',array(),$this->script_version );   
}

//body class
function add_bodyclass( $classes ) {

  if(isset($this->member_section['levels']) && is_array($this->member_section['levels'])) {
      foreach($this->member_section['levels'] as $key=>$level) {
        //print_r($level);
        $classes[] = 'member_section_level_'.$key;
      }      
  }
  $classes[] = 'member_section_page';
  return $classes;
}


// Top panel member menu

function create_member_menu() {
  
  $menu='<ul>';   
  if(isset($this->member_sections['members'])) { 
      $menu.='<li><a class="create-new-page" data-type="member" title="'.__("Vytvořit novou stránku","cms_member").'" href="#">'.__("Nová členská stránka","cms_member").'</a></li>';
      $count=count($this->member_sections['members']);
      if($count>0) {

            $menu.='<li><a class="ve_prevent_default ve_menu_has_submenu" href="#">'.__("Přejít do členské sekce","cms_member").'</a>';
                $menu.='<ul>';
                foreach($this->member_sections['members'] as $member) {
                    $menu.='<li><a href="'.get_permalink($member['dashboard']).'">'.$member['name'].'</a></li>';    
                }
                $menu.='</ul>
            </li>';        
      } 
  } 
  $menu.='<li><a class="open-member-setting" data-type="" data-setting="" title="'.__("Správa členských sekcí","cms_member").'" href="'.admin_url('admin.php?page=member_option').'">'.(isset($this->member_sections['members'])?__('Správa členských sekcí',"cms_member"):__('Vytvořit členskou sekci',"cms_member")).'</a></li>
  </ul>';
  return $menu;
}   

// New member
function add_new_member() {
    $campaign=array(
        'name'=>$_POST['name'],
        'dashboard'=>'',
        'evergreen_show'=>'1',
        'login'=>'login',
        'email_text'=>__("Dobrý den,\n\nbyl vám vygenerován přístup do členské sekce: \n\n%%login%%","cms_member"),
    );
    $pages = get_pages(array('post_status'=>'publish'));
    $this->generate_member_setting($_POST['id'],$_POST['id'],'member_option',$campaign);
    die();
}

// New member level
function add_new_member_level() {
    $level=array(
        'name'=>$_POST['name'],
        'noaccess_text'=>'<p style="text-align:center;">'.__("Pro přístup k této stránce nemáte dostatečné oprávnění.","cms_member").'</p>',
    );
    $pages = get_pages(array('post_status'=>'publish'));
    $this->generate_level($level, $_POST['id'], $_POST['tagname'], $_POST['tagid'].'_levels_'.$_POST['id'],$pages);
    die();
}
// generate member level setting
function generate_level($level, $id, $tagname, $tagid, $pages) {
    global $cms;
    ?>        
    <input type="hidden" name="<?php echo $tagname; ?>[<?php echo $id; ?>][name]" value="<?php echo $level['name']; ?>">
    <a class="member_level_setting_toggle" href="#" title="<?php echo __("Nastavení členské úrovně","cms_member"); ?>"><?php echo __("Nastavení","cms_member"); ?><span class="icon"></span><span class="arr"></span></a>
    <a class="member_delete_level" href="#" title="<?php echo __("Smazat členskou úroveň","cms_member"); ?>"><?php echo __("Smazat","cms_member"); ?><span class="icon"></span></a>
    
    <?php echo $level['name']; ?>
    
    <table class="member_level_setting">
        <tr>
            <th><label for="<?php echo $tagid.'_'.$id.'_name'; ?>"><?php echo __('Název členské úrovně','cms_member'); ?></label></th>
            <td><?php echo cms_generate_field_text($tagname.'['.$id.'][name]',$tagid.'_'.$id.'_name',isset($level['name'])? stripslashes($level['name']): null); ?></td>
       </tr> 
       <tr>
            <th><label for="<?php echo $tagid.'_'.$id.'_show'; ?>"><?php echo __('Zobrazení','cms_member'); ?></label></th>
            <td><?php cms_generate_field_checkbox($tagname.'['.$id.'][show]',$tagid.'_'.$id.'_show',isset($level['show'])? $level['show']: null,__('Zobrazit stránky této úrovně v menu i pro členy, kteří do ní nemají přístup.','cms_member')); ?></td>
       </tr>  
       <tr>
            <th><label for="<?php echo $tagid.'_'.$id.'_noaccess_text'; ?>"><?php echo __('Obsah stránky pro členy bez oprávnění','cms_member'); ?></label></th>
            <td>
                <div class="sublabel"><?php echo __('Zobrazit stránku:','cms_member'); ?></div>
                <?php $cms->select_page($pages, isset($level['noaccess_page'])? $level['noaccess_page']: '', $tagname.'['.$id.'][noaccess_page]', $tagid.'_'.$id.'_noaccess_page'); ?>
                <div class="sublabel"><?php echo __('Nebo vypsat text (pokud nevyberete stránku):','cms_member'); ?></div>
                <?php wp_editor( stripslashes($level['noaccess_text']), $tagid.'_'.$id.'_noaccess_text', array('textarea_name' => $tagname.'['.$id.'][noaccess_text]') ); ?>
                <script>
                            jQuery(document).ready(function($) { 
                                tinymce.EditorManager.execCommand('mceAddEditor', true, "<?php echo $tagid.'_'.$id.'_noaccess_text'; ?>"); 
                      
                                quicktags({id: "<?php echo $tagid.'_'.$id.'_noaccess_text'; ?>"});
                                //QTags._buttonsInit(); 
                            });
                </script>
            </td>
                            
       </tr>  
    </table>    
    <?php
}

// add first member section to setting on create page
function action_create_page($page_id) {    
    if(isset($_POST['ve_post_member'])) {
            $member_set=get_post_meta($page_id, 'page_member', true);
            $member_set['member_page']=1;
            $member_set['member_section']['section']=$_POST['ve_post_member'];
            if(isset($_POST['ve_post_member_levels'][$_POST['ve_post_member']]['levels'])) {
                $member_set['member_section'][$_POST['ve_post_member']]['levels']=$_POST['ve_post_member_levels'][$_POST['ve_post_member']]['levels'];
            }
            update_post_meta($page_id, 'page_member', $member_set);
    }
}


function member_generate_password($user){

  
} 

function mw_add_profile_fields($user) {
    // generate password
    if(isset($_GET['member_generate_new_pass']) && isset($_GET['member_generate_new_pass']) && $_GET['member_generate_new_pass'] == 2){
      echo '<div class="updated"><p>'.__('Nové heslo odesláno na uživatelův e-mail.','cms_member').'</p></div>';
    }
  
    ?>
      <table class="form-table">
        <tr>
          <th><?php echo __("Automaticky vygenerovat a poslat nové heslo", "cms_member") ?></th>
          <td>
            <a class="button button-primary" href="<?php echo esc_url( admin_url( 'user-edit.php?user_id='.$user->ID ) )?>&member_generate_new_pass=1"><?php echo __("Generovat nové heslo", "cms_member")?></a>
          </td>
        </tr>
      </table>
    <?php
    
    // custom fields
    
    $custom_fields = get_option('mw_member_user_custom_fields');
    if(is_array($custom_fields) && count($custom_fields)) {

    $user_custom_fields=get_user_meta($user->ID,'member_custom_field', true)
    ?>
    <h3><?php echo __("Vlastní pole","cms_member"); ?></h3>
    <table class="form-table">
        <?php foreach($custom_fields as $field) { ?>
            <tr>
              <th><?php echo $field['title'] ?></th>
              <td>
              <?php 
              $val=(isset($user_custom_fields[$field['id']]))? $user_custom_fields[$field['id']] : '';
              if($field['type']=='text') echo '<input type="text" class="regular-text" value="'.$val.'" name="member_custom_field['.$field['id'].']" />';
              else echo '<textarea class="regular-text" name="member_custom_field['.$field['id'].']" rows="5" cols="30">'.$val.'</textarea>';

              if($field['description']) { ?>
                  <p class="description"><?php echo $field['description'] ?></p>
              <?php } ?>  
              </td>
            </tr>
        <?php } ?>
    </table>
    <?php        
    }
    
    // member fields
    
    $member_fields=get_user_meta($user->ID,'member_fields', true);
    $hide_member=get_user_meta($user->ID,'mw_hide_member', true);
    ?>
    <h3><?php echo __("Další nastavení členů","cms_member"); ?></h3>
    <table class="form-table">
        <tr>
            <th><?php echo __('Obor podnikání','cms_member') ?></th>
            <td><input type="text" class="regular-text" value="<?php if(isset($member_fields['domain'])) echo $member_fields['domain']; ?>" name="member_fields[domain]" /></td>
        </tr>
        <tr>
            <th><?php echo __('Zobrazení v katalogu členů','cms_member') ?></th>
            <td>
                <div>
                    <input id="mem_fields_hide_email" type="checkbox" value="1" <?php if(isset($member_fields['hide_email'])) echo 'checked="checked"'; ?>" name="member_fields[hide_email]" />
                    <label for="mem_fields_hide_email"><?php echo __('Nezobrazovat můj email v katalogu členů','cms_member') ?></label>
                </div>
                <div>
                    <input id="mem_fields_hide_member" type="checkbox" value="1" <?php if(isset($hide_member) && $hide_member) echo 'checked="checked"'; ?>" name="hide_member" />
                    <label for="mem_fields_hide_member"><?php echo __('Nezobrazovat můj profil v katalogu členů','cms_member') ?></label>
                </div>               
            </td>
        </tr>
    </table>
    <?php     
    
    $accept=get_user_meta($user->ID, 'mw_member_accepted', true);
    $source=get_user_meta($user->ID, 'mw_member_source', true);
    
    $accept_val = (!empty($accept) && $accept['time']) ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $accept['time']) : __('Bez souhlasu', 'cms_member');
    
    $source_info = __(' Souhlas s účelem zpracování je evidován u původního zdroje.', 'cms_member');
    $source_val = __('Neznámý', 'cms_member').$source_info;
    if ($source == 'by_notify') {
        $source_val = __('Notifikace.', 'cms_member').$source_info;
    } elseif ($source == 'by_admin') {
        $source_val = __('Vytvořen ručně.', 'cms_member').$source_info;
    } elseif ($source == 'by_import') {
        $source_val = __('Import členů.', 'cms_member').$source_info;
    } elseif ($source == 'free_registration') {
        $source_val = __('Formulář pro registraci zdarma.', 'cms_member');
    }
    ?>
    <h3><?php echo __("Souhlas se zpracováním osobních údajů","cms_member"); ?></h3>
    <table class="form-table">
        <tr>
            <th><?php echo __("Souhlas udělen","cms_member"); ?></th>
            <td>
                <?php 
                echo $accept_val; 
                if(!empty($accept) && isset($accept['text'])) {
                    echo '<div class="mw_info_icon mw_info_icon_right">i<span>'.$accept['text'].'</span></div>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th><?php echo __("Zdroj","cms_member"); ?></th>
            <td>
                <?php 
                echo $source_val; ?></td>
        </tr>
    </table>
    <?php     

    
    // member section
    $this->member_profile_fields( $user );
}


function member_profile_fields( $user ) { 
  $members = get_option('member_basic');
  if($user) $value=get_the_author_meta( 'cms_member', $user->ID );

  ?>

  <h3><?php echo __("Členské sekce","cms_member"); ?></h3>
  <?php if(isset($members['members']) && is_array($members['members'])) { ?>
  <table class="wp-list-table widefat fixed pages">    
      <thead>
        <tr>
            <th><?php echo __("Zařadit do členské sekce","cms_member"); ?></th>   
            <th><?php echo __("Zařadit do členské úrovně","cms_member"); ?></th>   
            <th><?php echo __("Datum registrace","cms_member"); ?></th>
            <th><?php echo __("Čas registrace","cms_member"); ?></th>
            <th><?php echo __("Členství do","cms_member"); ?></th>
        </tr>
      </thead>
        <?php
        $i=1;
        foreach($members['members'] as $id=>$member) { ?>
        <tr <?php if($i==1) echo 'class="alt"';  ?>>
          <td>
            <input type="checkbox" id="member_section_<?php echo $id; ?>_level" class="member_section_checkbox" name="member[<?php echo $id; ?>][section]" value="1" <?php if(isset($value[$id]) && isset($value[$id]['section'])) echo 'checked="checked"'; ?> />
            <label for="member_section_<?php echo $id; ?>_level"><strong><?php echo $member['name']; ?></strong> <?php if(!isset($member['dashboard']) || !$member['dashboard']) echo '<div style="color: red;">'.__('Tato členská sekce nemá nastavenou žádnou stránku jako nástěnku.','cms_member').'</div>'; ?></label>
          </td>

          <td>
            <?php
            if(isset($member['levels'])) {
                foreach($member['levels'] as $lid=>$level) { ?>
                    <div>
                        <input type="checkbox" id="member_section_<?php echo $id; ?>_level_<?php echo $lid; ?>" name="member[<?php echo $id; ?>][levels][<?php echo $lid; ?>]" value="1" <?php if(isset($value[$id]) && isset($value[$id]['levels'][$lid])) echo 'checked="checked"'; ?> />
                        <label for="member_section_<?php echo $id; ?>_level_<?php echo $lid; ?>"><?php echo $level['name']; ?></label>
                    </div>
                <?php 
                }
            } 
            else echo __('Členská sekce neobsahuje žádné členské úrovně.','cms_member');
            ?>
          </td>
          <td>
            <input class="cms_datepicker" type="text" name="member[<?php echo $id; ?>][date]" value="<?php if(isset($value[$id]) && isset($value[$id]['date'])) echo $value[$id]['date']; ?>" />
          </td>
          <td>
            <input class="cms_timepicker" type="text" name="member[<?php echo $id; ?>][time]" value="<?php if(isset($value[$id]) && isset($value[$id]['time'])) echo $value[$id]['time']; ?>" />
          </td>
          <td>
            <input class="cms_datepicker" type="text" name="member[<?php echo $id; ?>][end]" value="<?php if(isset($value[$id]) && isset($value[$id]['end'])) echo $value[$id]['end']; ?>" />
          </td>
        </tr>
        <?php 
        $i=$i==1? 2:1;
        } ?>
  </table>
  <?php } else { ?>
        <div class="cms_error_box"><?php echo __('Není vytvořena žádná členská sekce.','cms_member'); ?></div>
  <?php } 
}   

function update_profile_fields( $user_id ) {

  if ( !current_user_can( 'edit_user', $user_id ) )
    return false;
  
  if(isset($_POST['member'])) $member=$_POST['member'];
  else $member=array();
  $this->save_user_members($user_id, $member);
  
  if(isset($_POST['member_custom_field'])) {
      update_user_meta($user_id,'member_custom_field',$_POST['member_custom_field']);   
  }
  if(isset($_POST['member_fields'])) {
      update_user_meta($user_id,'member_fields',$_POST['member_fields']);   
  }
  if(isset($_POST['hide_member'])) {
      update_user_meta($user_id,'mw_hide_member',$_POST['hide_member']);   
  } else delete_user_meta($user_id,'mw_hide_member');
  
}

function save_user_members($user_id, $members) {
  $save=array();
  foreach($members as $id=>$member) {
      if(isset($member['section'])) {
          if(!$member['date']) $member['date']=Date("d.m.Y", current_time( 'timestamp' )); 
          if(!$member['time']) $member['time']=Date("H:i", current_time( 'timestamp' )); 
          $save[$id]=$member;
      }
  }
  update_user_meta( $user_id, 'cms_member', $save );
}

function member_user_list_columns($columns) {
    if(isset($_GET['role']) && $_GET['role']=='member') {
        unset($columns['posts']);
        unset($columns['role']);
    }
    $columns['mw_member_progress'] = __('Pokrok','cms_member');
    return $columns;
}
 

function member_user_list_columns_content($value, $column_name, $user_id) {
    $content='';
	  if ( 'mw_member_progress' == $column_name && isset($this->member_sections['members'])) {
        $checklists=get_user_meta($user_id, 'checklist', true);
        $user_members=get_user_meta($user_id, 'cms_member', true);
        foreach($this->member_sections['members'] as $id=>$member) {
            if(isset($user_members[$id])){
                $all_pages = get_pages( array('meta_key'=>'page_member') );
                $pages=array();
                foreach($all_pages as $page) {  
                    $meta=get_post_meta( $page->ID, 'page_member', true ); 
                    if(isset($meta['member_page']) && $meta['member_section']['section']==$id) {  
                        $pages[]=$page;
                    }
                }
                
                $tasks=0;
                $suc=0;
                
                foreach($pages as $page) {
                    $page_setting=get_post_meta($page->ID, 'page_member', true);          
                    if(isset($page_setting['checklist']) && is_array($page_setting['checklist'])) {
                        $tasks+=count($page_setting['checklist']);
                        if(isset($checklists[$page->ID]) && is_array($checklists[$page->ID])) $suc+=count($checklists[$page->ID]);
                    }
                }
                if($tasks) {
                    $percent=round($suc/$tasks*100);
                    $content.='<div><strong>'.$member['name'].':</strong> '.$percent.'%</div>';
                } 
                
            }
        }
        return $content;
    }
		else return $value;
}

/* Member news
************************************************************************** */

function register_member_news() {
      $labels = array(
        'name'               => __( 'Členské novinky', 'cms_member' ),
        'singular_name'      => __( 'Novinka', 'cms_member' ),
        'menu_name'          => __( 'Členské novinky', 'cms_member' ),
        'name_admin_bar'     => __( 'Novinka', 'cms_member' ),
        'add_new'            => __( 'Přidat novinku', 'cms_member' ),
        'add_new_item'       => __( 'Přidat novinku', 'cms_member' ),
        'new_item'           => __( 'Nová novinka', 'cms_member' ),
        'edit_item'          => __( 'Upravit novinku', 'cms_member' ),
        'view_item'          => __( 'Zobrazit novinku', 'cms_member' ),
        'all_items'          => __( 'Všechny novinky', 'cms_member' ),
        'search_items'       => __( 'Hledat novinku', 'cms_member' ),
        'parent_item_colon'  => ':',
        'not_found'          => __( 'Výsledek hledání je prázdný.', 'cms_member' ),
        'not_found_in_trash' => __( 'Výsledek hledání je prázdný.', 'cms_member' )
      );
    
      $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'member_news' ),
        'capability_type'    => 'page',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 24,
        'supports'           => array( 'title','editor' )
      );
    
      register_post_type( 'member_news', $args );
}

/* Member users page
************************************************************************** */


function add_user_submenu() {
    add_submenu_page( 'users.php', __('Vytvořit nového člena','cms_member'), __('Vytvořit člena','cms_member'), 'list_users', 'add_member_user', array($this,'add_member_user_form') );
    add_submenu_page( 'users.php', __('Import členů','cms_member'), __('Import členů','cms_member'), 'list_users', 'import_member_users', array($this,'import_member_users_form') );
    add_submenu_page( 'users.php', __('Vlastní pole členů','cms_member'), __('Vlastní pole členů','cms_member'), 'list_users', 'custom_fields_member_users', array($this,'custom_fields_member_user_form') );
    add_submenu_page( 'users.php', __('Statistiky','cms_member'), __('Statistiky','cms_member'), 'list_users', 'statistics_member_users', array($this,'statistics_member_user_page') );
}
function add_member_user_form() {
    ?>
    <div class="wrap">
    <h2 id="add-new-user"><?php echo __('Vytvořit nového člena','cms_member'); ?></h2>
    <?php
    if(isset($_GET['error'])) {
        echo '<div class="cms_error_box">';
        if($_GET['error']==1) echo __('Musíte zadat e-mail nového člena.','cms_member'); 
        if($_GET['error']==2) echo __('Musíte vybrat alespoň jednu členskou sekci, do které chcete nového člena zařadit.','cms_member'); 
        if($_GET['error']==3) echo __('Uživatel s touto e-mailovou adresou již existuje.','cms_member'); 
        echo '</div>';
    } else if(isset($_GET['added'])) {
        echo '<div class="cms_confirm_box">'.__('Nový člen byl vytvořen.','cms_member').'</div>';
    }
    $members = get_option('member_basic');
    if(isset($members['members']) && is_array($members['members'])) { ?>
        <p><?php echo __('Vytvoří nového uživatele, vytvoří přístup do vybraných členských sekcí a pošle uživateli přístupové údaje na e-mail.','cms_member'); ?></p>
        <form action="" method="post" name="createuser" id="createuser" class="validate">
        <input name="member_action" type="hidden" value="create_new_member_user" />  
        <?php wp_nonce_field('create_new_member_user','create_new_member_user'); ?>
        <table class="form-table">
          <tr class="form-field form-required">
            <th scope="row"><label for="email"><?php echo __('E-mail','cms_member'); ?> <span class="description">(<?php echo __('vyžadováno','cms_member'); ?>)</span></label></th>
            <td><input name="user[email]" type="text" id="email" value="" /></td>
          </tr>
          <tr class="form-field">
            <th scope="row"><label for="first_name"><?php echo __('Jméno','cms_member'); ?> </label></th>
            <td><input name="user[first_name]" type="text" id="first_name" value="" /></td>
          </tr>
          <tr class="form-field">
            <th scope="row"><label for="last_name"><?php echo __('Příjmení','cms_member'); ?> </label></th>
            <td><input name="user[last_name]" type="text" id="last_name" value="" /></td>
          </tr>
          <tr>
            <th scope="row"><label for="send_password"><?php echo __('Poslat heslo?','cms_member'); ?></label></th>
            <td><label for="send_password"><input type="checkbox" name="send_password" id="send_password" checked="checked" /> <?php echo __('Poslat přístupy novému členu e-mailem.','cms_member'); ?></label></td>
          </tr>
        </table>
        <?php $this->member_profile_fields(false); ?>
           
        <p class="submit"><input type="submit" name="createuser" id="createusersub" class="button button-primary" value="<?php echo __('Vytvořit nového člena','cms_member'); ?>"  /></p>
        </form>
    <?php } else { ?>
        <div class="cms_error_box"><?php echo __('Není zadaná žádná členská sekce, proto nelze přidávat nové členy.','cms_member'); ?></div>
    <?php } ?>
    </div>
    <?php
}
function import_member_users_form() {
    ?>
    <div class="wrap">
    <h2 id="add-new-user"><?php echo __('Importovat členy','cms_member'); ?></h2>
    <?php
    if(isset($_GET['error'])) {
        echo '<div class="cms_error_box">';
        if($_GET['error']=='1a') echo __('Musíte zadat seznam e-mailů.','cms_member'); 
        if($_GET['error']=='2') echo __('Musíte vybrat alespoň jednu členskou sekci, do které chcete nové členy zařadit.','cms_member'); 
        if($_GET['error']=='1b') echo __('Musíte vyplnit text e-mailu.','cms_member'); 
        if($_GET['error']=='3') echo __('Text e-mailové zprávy musí obsahovat proměnnou %%login%%.','cms_member'); 
        echo '</div>';
    } else if(isset($_GET['added'])) {
        $info=unserialize(base64_decode($_GET['info']));
        echo '<div class="cms_confirm_box"><strong>'.sprintf(__('Bylo vytvořeno %s nových členů.','cms_member'),$info['added']).'</strong>';
        if($info['old']) echo '<br>'.sprintf(__('Bylo upraveno %s starých členů.','cms_member'),$info['old']);
        echo '</div>';
    }
    $members = get_option('member_basic');
    if(isset($members['members']) && is_array($members['members'])) { ?>
        <p><?php echo __('Vytvoří účty do členských sekcí pro zadané e-mailové adresy.','cms_member'); ?></p>
        <script>
            jQuery(document).ready(function($) {
                $("#createuser").submit(function(event){
                    var error=false;
                    
                    var selected=false;
                    $(".member_section_checkbox").each(function(){
                        if($(this).prop('checked')) selected=true;
                    });
                    if(!selected) error=4;
                    
                    if($("#send_password").prop('checked')) {
                        if(!$("#email_text").val().contains("%%login%%")) error=3;
                        if($("#email_text").val()=='') error=1;                       
                    }
                    if($("#emails").val()=='') error=2;
                    
                    if(error) {
                      if(error==1) {
                          alert('<?php echo __('Musíte vyplnit text e-mailu.','cms_member'); ?>');
                          $("#email_text").addClass('cms_form_error');
                      }
                      if(error==2) {
                          alert('<?php echo __('Musíte zadat seznam e-mailů, pro které chcete vytvořit členské účty.','cms_member'); ?>');
                          $("#emails").addClass('cms_form_error');
                      }
                      if(error==3) {
                          alert('<?php echo __('Text e-mailu musí obsahovat proměnnou %%login%%.','cms_member'); ?>');
                          $("#email_text").addClass('cms_form_error');
                      }
                      if(error==4) {
                          alert('<?php echo __('Musíte vybrat alespoň jednu členskou sekci, do které chcete nové členy zařadit.','cms_member'); ?>');
                      }
                      return false;
                    }
                });
                $(".cms_form_error").live("change", function(){
                    $(this).removeClass("cms_form_error");
                });
            });
        </script>
        <form action="" method="post" name="createuser" id="createuser">
        <input name="member_action" type="hidden" value="import_new_member_users" />  
        <?php wp_nonce_field('import_new_member_user','import_new_member_user'); ?>
        <table class="form-table">
          <tr class="form-field form-required">
            <th scope="row"><label for="emails"><?php echo __('Seznam e-mailů','cms_member'); ?> <br><span class="description"><?php echo __('(na každý řádek jeden e-mail)','cms_member'); ?></span></label></th>
            <td>
                <textarea name="emails" id="emails" rows="10"></textarea>
                <br><span class="description"><?php echo __('Zadejte zde seznam e-mailů, pro který chcete vygenerovat nové účty. Každý e-mail musí být na novém řádku.','cms_member'); ?></span>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="send_password"><?php echo __('Zaslat přístupy na e-mail?','cms_member'); ?></label></th>
            <td><label for="send_password"><input type="checkbox" name="send_password" id="send_password" checked="checked" /> <?php echo __('Poslat novým členům e-mail','cms_member'); ?></label></td>
          </tr>
          <tr class="form-field form-required">
            <th scope="row"><label for="email_subject"><?php echo __('Předmět e-mailu','cms_member'); ?></label></th>
            <td>
                <input name="email_subject" class="regular-text" type="text" id="email_subject" value="<?php echo __('Přístup do členské sekce','cms_member'); ?>" />               
            </td>
          </tr>
          <tr class="form-field form-required">
            <th scope="row"><label for="email_text"><?php echo __('Text e-mailu','cms_member'); ?></label></th>
            <td>
                <textarea name="email_text" id="email_text" rows="10"><?php echo __('Dobrý den, 

byly vám vygenerovány přihlašovací údaje do členské sekce:
                
%%login%%','cms_member'); ?>
                
                </textarea>
                <br><span class="description"><?php echo __('Proměnná <code>%%login%%</code> bude nahrazena vygenerovanými přihlašovacími údaji a URL adresou s přihlašovacím formulářem do níže zaškrtnutých členských sekcí. Text e-mailu musí tuto proměnnou obsahovat.','cms_member'); ?></span>
            </td>
          </tr>
        </table>
        <?php $this->member_profile_fields(false); ?>
           
        <p class="submit"><input type="submit" name="importusers" id="importuserssub" class="button button-primary" value="<?php echo __('Naimportovat nové členy','cms_member'); ?>"  /></p>
        </form>
    <?php } else { ?>
        <div class="cms_error_box"><?php echo __('Není zadaná žádná členská sekce, proto nelze přidávat nové členy.','cms_member'); ?></div>
    <?php } ?>
    </div>
    <?php
}

function custom_fields_member_user_form() {
    ?>
    <div class="wrap">
    <h2 id="add-new-user"><?php echo __('Vlastní pole členů','cms_member'); ?></h2>
    <?php
    if(isset($_GET['error'])) {
        echo '<div class="cms_error_box">';
        if($_GET['error']==1) echo __('Musíte zadat název vlastního pole.','cms_member'); 
        echo '</div>';
    } else if(isset($_GET['added'])) {
        echo '<div class="cms_confirm_box">'.__('Vlastní pole byly uloženy.','cms_member').'</div>';
    }
    
    ?>
    <p><?php echo __('Seznam polí, které se zobrazují členům v jejich profilech.','cms_member'); ?></p>
       
    <form action="" method="post" name="add_new_member_field" id="add_new_member_field" class="validate">
        <h3><?php echo __('Přidat vlastní pole','cms_member'); ?></h3>
        <input name="member_action" type="hidden" value="add_new_member_field" />  
        <?php wp_nonce_field('add_new_member_field','add_new_member_field'); ?>
        <table class="form-table">
          <tr class="form-field form-required">
            <th scope="row"><label for="custom_field_title"><?php echo __('Název','cms_member'); ?> <span class="description">(<?php echo __('vyžadováno','cms_member'); ?>)</span></label></th>
            <td><input name="custom_field[title]" type="text" id="custom_field_title" value="" /></td>
          </tr>
          <tr class="form-field">
            <th scope="row"><label for="custom_field_type"><?php echo __('Typ','cms_member'); ?> </label></th>
            <td>
                <select name="custom_field[type]" type="text" id="custom_field_type">
                    <option value="text"><?php echo __('Jednoduchý text','cms_member'); ?></option>
                    <option value="textarea"><?php echo __('Textové pole','cms_member'); ?></option>
                </select>
            </td>
          </tr>
          <tr class="form-field">
            <th scope="row"><label for="custom_field_description"><?php echo __('Popisek','cms_member'); ?> </label></th>
            <td><input name="custom_field[description]" type="text" id="custom_field_description" value="" /></td>
          </tr>
        </table>

           
        <p class="submit"><input type="submit" name="add_member_user_custom_field" class="button button-primary" value="<?php echo __('Přidat vlastní pole','cms_member'); ?>"  /></p>
    </form>
    
    
    <?php
    
    $custom_fields = get_option('mw_member_user_custom_fields');
    
    if(is_array($custom_fields) && count($custom_fields)) {
        echo '<form action="" method="post" name="add_new_member_field" id="add_new_member_field" class="validate">';
        echo "<h3>".__('Seznam vlastních polí','cms_member')."</h3>";
        echo '<input name="member_action" type="hidden" value="update_member_fields" />';
        echo '<div class="ve_sortable_items mw_members_custom_fields_container">';
        wp_nonce_field('update_member_fields','update_member_fields');
        foreach($custom_fields as $field) {
            ?>
            <div class="ve_item_container ve_setting_container ve_sortable_item">
                <div class="ve_item_head">  
                    <span class="ve_sortable_handler"></span>
                    <span><?php echo $field['title']; ?></span>
                    <a class="ve_delete_setting" href="#" title="<?php echo __('Smazat','cms_member'); ?>"></a>                   
                </div>
                <div class="ve_item_body">
                    <div class="set_form_row ">
                        <div class="label"><?php echo __('Název','cms_member'); ?></div>
                        <input class="cms_text_input" name="custom_field[<?php echo $field['id'] ?>][title]" type="text" value="<?php echo $field['title']; ?>" />
                        <input name="custom_field[<?php echo $field['id'] ?>][id]" type="hidden" value="<?php echo $field['id']; ?>" />
                    </div>
                    <div class="set_form_row ">
                        <div class="label"><?php echo __('Typ','cms_member'); ?></div>
                        <select name="custom_field[<?php echo $field['id'] ?>][type]" type="text" class="cms_select_input">
                            <option value="text" <?php if($field['title']=='text') echo 'selected="selected"'; ?>><?php echo __('Jednoduchý text','cms_member'); ?></option>
                            <option value="textarea" <?php if($field['title']=='textarea') echo 'selected="selected"'; ?>><?php echo __('Textové pole','cms_member'); ?></option>
                        </select>
                    </div>
                    <div class="set_form_row ">
                        <div class="label"><?php echo __('Popisek','cms_member'); ?></div>
                        <input class="cms_text_input" name="custom_field[<?php echo $field['id'] ?>][description]" type="text" value="<?php echo $field['description']; ?>" />
                    </div>
                </div>
            </div>            
            <?php
        }
        echo '<p class="submit"><input type="submit" name="update_member_user_custom_field" class="button button-primary" value="'.__('Uložit změny','cms_member').'"  /></p>';
        echo '</form>';
    }
        
        
    ?> 

    </div>
    <?php
}

function statistics_member_user_page() {
    ?>
    <div class="wrap">
    <h2 id="add-new-user"><?php echo __('Statistiky','cms_member'); ?></h2>
  
    <?php
    
    $all_pages = get_pages( array('meta_key'=>'page_member') );
    $all_members = get_users( array('role'=>'member') );   
    
    
    
    foreach($this->member_sections['members'] as $m_id => $ms) {
    
        $result=array(
            'users'=>0,
            'tasks'=>0,
            'working'=>0,
            'working_average'=>0,
            'average'=>0
        );

        echo '<h3>'.__('Statistiky členské sekce:','cms_member').' '.$ms['name'].'</h3>';
    
        $m_tasks = array();
        $pages=array();
        
        foreach($all_pages as $page) {  
            $meta=get_post_meta( $page->ID, 'page_member', true ); 
            if(isset($meta['member_page']) && $meta['member_section']['section']==$m_id) {  // add login only if not member page
                $pages[]=$page;
            }
        }
        
        $users=array();
        foreach($all_members as $user) {  
            $user_ms=get_user_meta($user->ID, 'cms_member', true);
            if(isset($user_ms[$m_id])) {  // add login only if not member page
                $users[]=$user;
                $result['users']++;
            }
        }
        
        $tasks=0;
        foreach($pages as $page) {
            $page_setting=get_post_meta($page->ID, 'page_member', true);          
            if(isset($page_setting['checklist']) && is_array($page_setting['checklist'])) {
                foreach($page_setting['checklist'] as $t_id=>$task) {
                    $m_tasks[$page->ID][$t_id]=$t_id;
                    $result['tasks']++;    
                }
            }
        }
        if($result['users'] && $result['tasks']) {
            foreach($users as $user) {
                $suc=0;                
                $checklists=get_user_meta($user->ID, 'checklist', true);
                if($checklists) {
                foreach($checklists as $chcl_id=>$checklist) {
                        if(isset($m_tasks[$chcl_id])) {
                            foreach($checklist as $ch_id=>$check) {
                                if(isset($m_tasks[$chcl_id][$ch_id])) 
                                    $suc++;   
                            }
                        }
                    }
                    $avg=round($suc/$result['tasks']*100);
                    if($suc) {
                        $result['working']++;
                        $result['working_average']=$result['working_average']+$avg;
                    }
                    $result['average']=$result['average']+$avg;
               } 
            }
        }
        ?>
        <table>
            <tr>
                <td><?php echo __('Členů:','cms_member'); ?></td>
                <td><strong><?php echo $result['users']; ?></strong></td>
            </tr>
            <tr>
                <td><?php echo __('Úkolů:','cms_member'); ?></td>
                <td><strong><?php echo $result['tasks']; ?></strong></td>
            </tr>
            <?php if($result['tasks']) { ?>
            <tr>
                <td><?php echo __('Splnilo alespoň jeden úkol:','cms_member'); ?></td>
                <td><strong><?php echo $result['working'].' '.__('uživatelů','cms_member'); ?></strong> (<?php echo __('průměrně splnili:','cms_member').' <strong>'.round($result['working_average']/$result['working']).'% '.__('úkolů','cms_member').'</strong>'; ?>)</td>
            </tr>
            <tr>
                <td><?php echo __('Průměrně splněno:','cms_member'); ?></td>
                <td><strong><?php echo round($result['average']/$result['users']).'% '.__('úkolů','cms_member'); ?></strong></td>
            </tr>
            <?php } ?>
        </table>
        <?php
            
    }
}

function create_user_login_message($email, $password, $members, $member_option) {
    $count=0;
    foreach($members as $id=>$member) {
         if(isset($member['section'])) {
            $count++;
        }
    }  

    $login = __('Jméno','cms_member').': '.$email;
    $login .= "\n";
    $login .= __('Heslo','cms_member').': '.$password;
    $login .= "\n\n";
    
    $message='';

    if($count == 0) $message .= get_option('home') . '/wp-admin' . "\n";
    else {
        foreach($members as $id=>$member) {
          if(isset($member['section'])) {
                if($count==1) {
                    $message .= get_permalink($member_option['members'][$id]['login']);
                    $message .= "\n";  
                }
                else {
                    $message .= sprintf(__('Do členské sekce %s se můžete přihlásit na adrese:','cms_member'),$member_option['members'][$id]['name']);
                    $message .= "\n";              
                    $message .= get_permalink($member_option['members'][$id]['login']);
                    $message .= "\n\n";
                }
          }
        }  
    }
    
      
    
    return $message.$login;

}
function create_new_member_user($user, $members, $send=true, $message='', $subject='', $accepted=false, $source=null) {
    //new user
    $member_option = get_option( 'member_basic' );
                
    $password = (isset($user['password']) && $user['password'])? $user['password'] : wp_generate_password(12, false);
    $user_id = wp_create_user($user['email'], $password, $user['email']); 
                
    if(isset($user['first_name']))
        wp_update_user( array ( 'ID' => $user_id, 'first_name' => $user['first_name'] ) ) ;   
    if(isset($user['last_name']))
        wp_update_user( array ( 'ID' => $user_id, 'last_name' => $user['last_name'] ) ) ;  
                
    //roles
                    
    $u = new WP_User( $user_id );
    $u->remove_role( 'subscriber' );
    $u->add_role( 'member' ); 
    
    // gdpr
    
    if($accepted) {
        $acc_val=array(
            'time'=>current_time( 'timestamp', 0 ),
            'text'=>$accepted
        );
        update_user_meta( $user_id, 'mw_member_accepted', $acc_val);
    }
    if($source) {
        update_user_meta( $user_id, 'mw_member_source', $source);
    }
    
    // save sections
    
    $this->save_user_members($user_id, $members);  
                
    //email                                
    if($send) {          
        if($subject=='') $subject=__('Přístup do členské sekce','cms_member');
        if($message=='') {            
            $message = __('Dobrý den,','cms_member');
            $message .= "\n\n";
            $message .= __('byl vám vygenerován přístup do členské sekce: ','cms_member');
            $message .= "\n\n";
            
            $message .= $this->create_user_login_message($user['email'],$password, $members, $member_option);
            
        } 
        else $message=str_replace("%%login%%", $this->create_user_login_message($user['email'],$password, $members, $member_option), $message);
                 
        $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';
                             
        wp_mail($user['email'], $subject, $message, $header);  
    } 
    return array('user_id'=>$user_id, 'user_login'=>$user['email'], 'user_password'=>$password);
    //return $user_id; 
}


function fapi_notification() {  

    $add_to_mem=$_GET['add_new_member'];    
    $notif_url=$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
           
    $fapi_option=get_option('ve_connect_fapi');  
    $fapi_option = apply_filters( 'mw_notifi_fapi_login', $fapi_option );
    
    $memberApiOptions = get_option('member_api');  
    
    // simple shop notification
    
    if(isset($_GET['simpleshop_hash'])) {
      
      if (isset($memberApiOptions['token'], $_GET['email'], $_GET['first_name'], $_GET['last_name']) && $_GET['email']) {
          
          $array = array($_GET['email'], $_GET['first_name'].' '.$_GET['last_name'], $_GET['voice_id'], $memberApiOptions['token'],'SimpleShopMioWeb');
          $simpleshop_hash = sha1(implode('|',$array));
          
          if($_GET['simpleshop_hash']==$simpleshop_hash) {
              $client = array(
                 'email' => isset($_GET['email']) ? $_GET['email'] : '',
                 'first_name' => isset($_GET['first_name']) ? $_GET['first_name'] : '',
                 'last_name' => isset($_GET['last_name']) ? $_GET['last_name'] : '',
              );
          } else {
              $this->update_debug($add_to_mem,__('Notifikace neproběhla. Neplatný hash notifikace.','cms_member'),0,$notif_url);
              die();
          }
      } else {
          $this->update_debug($add_to_mem,__('Notifikace neproběhla. Notifikace neobsahuje všechny potřebné údaje.','cms_member'),0,$notif_url);
          die();
      }
      
    // fapi notification
    
    } else if(isset($fapi_option['login']) && $fapi_option['login'] && isset($fapi_option['password']) && $fapi_option['password']) {    
       
        if (isset($memberApiOptions['token'], $_GET['token'], $_GET['email'], $_GET['first_name'], $_GET['last_name'])
            && $_GET['token'] === $memberApiOptions['token']
        ) {
            // skip loading of invoice from FAPI
           $client = array(
            'email' => isset($_GET['email']) ? $_GET['email'] : '',
            'first_name' => isset($_GET['first_name']) ? $_GET['first_name'] : '',
            'last_name' => isset($_GET['last_name']) ? $_GET['last_name'] : '',
          );
        } else {

            require_once FAPI_API;
            
            $fapi = new FAPIClient($fapi_option['login'], $fapi_option['password'], 'http://api.fapi.cz');
                  
            try {
                $fapi->checkConnection();
            } catch (FAPIClient_UnauthorizedException $e) {
                $this->update_debug($add_to_mem,__('Notifikace neproběhla. Přihlašovací údaje nejsou správné.','cms_member').'<br><small>'.get_class($e) . ': ' . $e->getMessage().'</small>',0,$notif_url);
                die();
            }
    
            if (empty($_POST['id']) || empty($_POST['time']) || empty($_POST['security']) || !is_numeric($_POST['id']) || !is_numeric($_POST['time'])) {
              $this->update_debug($add_to_mem,__('Notifikace neproběhla. Chybná data.','cms_member'),0,$notif_url);
              die;
            }
            
            try {
              $invoice = $fapi->invoice->get($_POST['id']);
            } catch (Exception $exception) {
              $this->update_debug($add_to_mem,__('Notifikace neproběhla. Nepodařilo se získat fakturu s tímto ID: '.$_POST['id'],'cms_member'),0,$notif_url);
              die;
            }
             
            if (empty($invoice)) {
              $this->update_debug($add_to_mem,__('Notifikace neproběhla. Faktura je prázdná.','cms_member'),0,$notif_url);
              die;
            }
            
            if (!empty($invoice['parent'])) {
                // This is not the first invoice in the order, so we can skip it.
                die;
            }  
            if (!empty($invoice['repayment_number']) && $invoice['repayment_number'] > 1) {
             die;
            }   
            $itemsSecurityHash = '';
            foreach ($invoice['items'] as $item) {
              $itemsSecurityHash .= md5($item['id'] . $item['name']);
            }
            $security = sha1($_POST['time'] . $invoice['id'] . $invoice['number'] . $itemsSecurityHash);
            
            if ($security != $_POST['security']) {
              $this->update_debug($add_to_mem,__('Notifikace neproběhla. Stažená faktura neodpovídá jejímu bezpečnostnímu otisku.','cms_member'),0,$notif_url);
              die;
            }
            
            $client = $fapi->client->get($invoice['client']); 
        
        }
        
    }
    else {
        $this->update_debug($add_to_mem,__('Notifikace neproběhla. Nepodařilo se spojit s FAPI z důvodu nezadaných přihlašovacích údajů.','cms_member'),0,$notif_url);
    }
    
    if(isset($client)) {
        // create notify
        $this->create_notify($client, $add_to_mem, $notif_url);
    }
    die();
}

function create_notify($client, $add_to_mem,$notif_url) {
  
  $debug_status = 0;
  $debug_text=''; 
  
  $client['billing_user_id'] = isset($_GET['billing_user_id']) && !empty($_GET['billing_user_id'])
    ? $_GET['billing_user_id']
    : null;
  
  // send email?          
  $send_email = isset($_GET['send_email']) ? (bool) $_GET['send_email'] : true;
  
  $reg_date=(isset($_GET['date']))? $_GET['date'] : Date("d.m.Y", current_time( 'timestamp' ));
  $reg_time=(isset($_GET['time']))? $_GET['time'] : Date("H:i", current_time( 'timestamp' ));
  if(isset($_GET['days']) && $_GET['days']) {
      $days=(isset($_GET['days']))? (int)$_GET['days'] : '';
      $end=Date("d.m.Y", strtotime( $reg_date )+($days*86400));
  } else $end='';
  
  if(isset($_GET['level'])) {
      $add_level=explode('-',$_GET['level']);
  } else if(isset($_GET['addlevel'])) {
      $add_level=explode('-',$_GET['addlevel']);
  } else $add_level=false;

  /* FIND EXISTING USER */
  //Find correct user
  $user = null;
  if(!$user && !empty($client['billing_user_id'])) {
    $users=get_users(array('meta_key'=>META_BILLING_ID, 'meta_value'=>$client['billing_user_id']));
    if(is_array($users) && count($users) > 0)
      $user = $users[0];
  }
  if(!$user)
    $user=get_user_by('email', $client['email'] );
  if(!$user)
    $user=get_user_by('login', $client['email'] );


  $sectionId = isset($_GET['add_new_member']) ? $_GET['add_new_member'] : -1;

  
  /* CREATE NEW USER
  ******************** */
   if (!$user) {

      $member_option = get_option( 'member_basic' );

      if(isset($member_option['members'][$sectionId])) {

           $levels=array();
           if($add_level) {
                foreach($add_level as $al) {
                    $levels[$al]=1;
                }
           }

           $subject=(isset($member_option['members'][$sectionId]['email_subject']))?$member_option['members'][$sectionId]['email_subject']:"";
           $new_user=$this->create_new_member_user($client,array($sectionId=>array('section'=>1,'levels'=>$levels,'date'=>$reg_date,'time'=>$reg_time,'end'=>$end)),$send_email,$member_option['members'][$sectionId]['email_text'],$subject,false,'by_notify');

           $user_id=$new_user['user_id'];

           // Store billing id.
           update_user_meta($user_id, META_BILLING_ID, $client['billing_user_id']);

           // return json?
           $return_json = isset($_GET['return_json']) ? (bool) $_GET['return_json'] : false;
           if ($return_json) {
              echo json_encode(array(
                'loginUrl' => get_permalink($member_option['members'][$sectionId]['dashboard']),
                'username' => $new_user['user_login'],
                'password' => $new_user['user_password'],
              ));
           }

        $debug_text=sprintf(__('Nový uživatel %s byl vytvořen.','cms_member'),$new_user['user_login']);
        $debug_status = 1;
      } else {
        $debug_text=sprintf(__('Sekce s ID=[%s] neexistuje. ','cms_member'),$sectionId);
      }
  }

  /* UPDATE EXISTING USER
  ******************** */
  else {
      $user_id=$user->ID;
      $member_meta=get_the_author_meta( 'cms_member', $user_id ); 
      $member_option = get_option( 'member_basic' );

      //Update billing user id, only if present.
      if(!empty($client['billing_user_id']))
        update_user_meta($user_id, META_BILLING_ID, $client['billing_user_id']);

      $new_level=false;
      $new_member=false;
      $new_expiration=false;

      if(isset($member_option['members'][$sectionId])) {

           if(isset($_GET['addlevel']) && isset($member_meta[$sectionId]) && isset($member_meta[$sectionId]['levels']))
              $old_levels=$member_meta[$sectionId]['levels'];
           else $old_levels=array();
           
           if(isset($_GET['addlevel']) && (!empty($_GET['addlevel']) || $_GET['addlevel'] === "0")) {
              // Preserve old levels
              $levels=$old_levels;
           } else {
              $levels=array();
           }

           if($add_level) {
                foreach($add_level as $al) {
                    $levels[$al]=1;
                    if(!isset($old_levels[$al])) {
                        $new_level=true;
                    }
                }
           }

           $return_json = isset($_GET['return_json']) ? (bool) $_GET['return_json'] : false;
           if ($return_json) {
              echo json_encode(array(
                'loginUrl' => get_permalink($member_option['members'][$sectionId]['dashboard']),
                'username' => $client['email'],
              ));
           }

      } else $levels=array();

      if(isset($member_meta) && is_array($member_meta)) {
          //if is in member already
          if(isset($member_meta[$sectionId])) {
              $reg_date=$member_meta[$sectionId]['date'];
              $reg_time=$member_meta[$sectionId]['time'];
              if(isset($member_meta[$sectionId]['end'])) $end=$member_meta[$sectionId]['end'];
              // add member time
              if(isset($days) && $days && isset($member_meta[$sectionId]['end'])) {
                  $old_end=strtotime($member_meta[$sectionId]['end']);
                  $end=($old_end>current_time( 'timestamp' ))? Date("d.m.Y", $old_end+($days*86400)) : Date("d.m.Y", current_time( 'timestamp' )+($days*86400));
                  $new_expiration=true;
              }
          } else $new_member=true;

          $member_meta[$sectionId]=array('section'=>1,'levels'=>$levels,'date'=>$reg_date,'time'=>$reg_time,'end'=>$end);
          update_user_meta( $user_id, 'cms_member', $member_meta );
      }
      else {
          add_user_meta( $user_id, 'cms_member', array($sectionId=>array('section'=>1,'levels'=>$levels,'date'=>$reg_date,'time'=>$reg_time,'end'=>$end)) );
      }


      $message = '';
      if($new_member) {
          $subject=$member_option['members'][$_GET['add_new_member']]['email_subject'];
          $message=$member_option['members'][$_GET['add_new_member']]['email_text'];
          
          if(!$subject)
              $subject = __('Přístup do členské sekce','cms_member'); 
          if(!$message) {                  
              $message = __('Dobrý den,','cms_member');
              $message .= "\n\n";
              $message .= __('byly Vám vygenerovány přístupy:','cms_member');
              $message .= "\n\n";
              $message .= "%%login%%";
              $message .= "\n\n";
          } 
             
      } else if($new_level) {

          $subject=(isset($member_option['members'][$_GET['add_new_member']]['level_email_subject'])) ? $member_option['members'][$_GET['add_new_member']]['level_email_subject'] : '';
          $message=(isset($member_option['members'][$_GET['add_new_member']]['level_email_text'])) ? $member_option['members'][$_GET['add_new_member']]['level_email_text'] : '';
          
          if(!$subject)
              $subject = __('Přidány přístupy do členské úrovně','cms_member'); 
          if(!$message) {                    
              $message = __('Dobrý den,','cms_member');
              $message .= "\n\n";
              $message .= __('byl Vám povolen přístup do nové členské úrovně v:','cms_member');
              $message .= "\n\n";
              $message .= "%%login%%";
              $message .= "\n\n";
          }
          
      } else if($new_expiration) {

          $subject=(isset($member_option['members'][$_GET['add_new_member']]['expiration_email_subject'])) ? $member_option['members'][$_GET['add_new_member']]['expiration_email_subject'] : '';
          $message=(isset($member_option['members'][$_GET['add_new_member']]['expiration_email_text'])) ? $member_option['members'][$_GET['add_new_member']]['expiration_email_text'] : '';
          
          if(!$subject)
              $subject = __('Prodloužení členství','cms_member'); 
          if(!$message) {                     
              $message = __('Dobrý den,','cms_member');
              $message .= "\n\n";
              $message .= __('bylo Vám prodlouženo členství:','cms_member');
              $message .= "\n\n";
              $message .= "%%login%%";
              $message .= "\n\n";
          }
          
      } else $send_email=false;
                                          
      if($send_email) { 
      
          $message = str_replace("%%login%%", $this->create_user_login_message($client['email'],__('Vaše heslo bylo již vygenerováno v předchozí registraci na tomto webu. (Pokud si heslo nepamatujete, vygenerujte si na přihlašovací stránce nové.)','cms_member'), array($sectionId=>array('section'=>1)), $member_option), $message);
             

          $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';

          wp_mail($client['email'], $subject, $message, $header);

     }

     $debug_text=sprintf(__('Uživatel %s byl aktualizován.','cms_member'),$client['email']);
     $debug_status = 1;

  }
  if(isset($user_id) && $user_id>0 && isset($client))
    do_action('cms_new_user_fapi_notification', $user_id, $client);
  
  // add to SE list
  if(isset($_GET['se_list']) && isset($client['email'])) {
      global $apiConnection;                

      $apiSE=$apiConnection->getClient('se');
      $apiSE->save_to_list($_GET['se_list'], $client['email']);
                                 
  }

  $this->update_debug($add_to_mem,__('Notifikace proběhla správně.','cms_member').$debug_text,$debug_status,$notif_url);
  
}

function update_debug($mem, $error, $stat, $url='') {
    $debug=get_option('mem_notification_debug');    
    if(!$stat) {
        $debug_option=get_option('fapi_notification');
        if(isset($debug_option['notification_onemail'])) {
        
            $email=($debug_option['notifi_email'])? $debug_option['notifi_email'] : get_bloginfo( 'admin_email' );
        
            $message = __(sprintf('Na webu %s byla spuštěna FAPI notifikace, která selhala: ','"'.get_bloginfo( 'name' ).'"'),'cms_member');
            $message .= $error;
        
             $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';
             wp_mail($email, __('Chybná FAPI notifikace','cms_member'), $message, $header); 
        }
    }
    $debug[$mem][]=array('time'=>current_time( 'timestamp' ), 'error'=>$error, 'status'=>$stat, 'url'=>$url);
    $debug[$mem]=array_slice($debug[$mem], -50, 50);
    update_option('mem_notification_debug', $debug);
}

function free_registration() {
    global $post;
    $error=0;  
    $info=unserialize(base64_decode($_POST['member_free_registration']));
        
    if(!$_POST['user_email']) $error=1;
    else if( !isset($info['update']) && (username_exists($_POST['user_email']) || email_exists($_POST['user_email']))) $error=2;
    else if(!isset($info['generate_password'])){
        if( !$_POST['user_password'] || !$_POST['user_password2']) $error=3;
        else if( $_POST['user_password']!=$_POST['user_password2']) $error=4;
    }
    
    if(!isset($info['level'])) $info['level']=array();
    
    if (!$error) {  
        
        $members=get_option('member_basic');
    
        if($members['members'][$info['id']]['name']===$info['name']) {
        
            if(isset($info['days'])) $end_date=Date("d.m.Y", current_time( 'timestamp' )+($info['days']*86400));
            else $end_date='';
            
            $subject=(isset($members['members'][$info['id']]['email_subject'])) ? $members['members'][$info['id']]['email_subject'] : '';
            $message=(isset($members['members'][$info['id']]['email_text'])) ? $members['members'][$info['id']]['email_text'] : '';
            
            if( isset($info['update']) && (username_exists($_POST['user_email']) || email_exists($_POST['user_email']))) {
            
                if(!$user=get_user_by( 'email', $_POST['user_email'] )) $user=get_user_by( 'login', $_POST['user_email'] );
                $user_id=$user->ID;
                wp_update_user( array( 'ID' => $user_id, 'first_name'=>$_POST['user_name'], 'last_name'=>$_POST['user_last_name'] ) );
                
                $member_meta=get_the_author_meta( 'cms_member', $user_id );

                if(isset($member_meta) && is_array($member_meta)) {
                    if(isset($member_meta[$info['id']])) {                       
                        $new_levels=(isset($member_meta[$info['id']]['levels']))? $member_meta[$info['id']]['levels']:array();
                        foreach($info['level'] as $nl_key=>$nl_val) {
                            $new_levels[$nl_key]=1;
                        }                          
                        $member_meta[$info['id']]=array('section'=>1,'levels'=>$new_levels,'date'=>$member_meta[$info['id']]['date'],'time'=>$member_meta[$info['id']]['time'],'end'=>(isset($member_meta[$info['id']]['end'])?$member_meta[$info['id']]['end']:''));
                    } else {
                        $member_meta[$info['id']]=array('section'=>1,'levels'=>$info['level'],'date'=>Date("d.m.Y", current_time( 'timestamp' )),'time'=>Date("H:i", current_time( 'timestamp' )),'end'=>$end_date);
                    }
                    update_user_meta( $user_id, 'cms_member', $member_meta ); 
                }
                else {
                    add_user_meta( $user_id, 'cms_member', array($info['id']=>array('section'=>1,'levels'=>$info['level'],'date'=>Date("d.m.Y", current_time( 'timestamp' )),'time'=>Date("H:i", current_time( 'timestamp' )),'end'=>$end_date)));    
                } 

                if(!$subject)
                    $subject = __('Nový přístup do členské sekce','cms_member'); 
                if(!$message) {                     
                    $message = __('Dobrý den,','cms_member');
                    $message .= "\n\n";
                    $message .= __('byly vám vygenerovány přístupy:','cms_member');
                    $message .= "\n\n";
                    $message .= "%%login%%";
                    $message .= "\n\n";
                }
                
                $message = str_replace("%%login%%", $this->create_user_login_message($_POST['user_email'],__('Vaše heslo bylo již vygenerováno v předchozí registraci na tomto webu. (Pokud si heslo nepamatujete, vygenerujte si na přihlašovací stránce nové.)','cms_member'), array($info['id']=>array('section'=>1)), $members), $message);   
                       
                $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';
                         
                wp_mail($_POST['user_email'], $subject, $message, $header);    
            
            }
            else {
                $user=array(
                    'email'=>$_POST['user_email'],
                    'password'=>(isset($info['generate_password']))? wp_generate_password() : $_POST['user_password'],
                    'first_name'=>$_POST['user_name'],      
                    'last_name'=>$_POST['user_last_name'],          
                );
                
                if(!isset($info['level'])) $info['level']=array();
                
                $members[$info['id']]=array('section'=>1,'levels'=>(($info['level'])?$info['level']:array()),'date'=>Date("d.m.Y", current_time( 'timestamp' )),'time'=>Date("H:i", current_time( 'timestamp' )),'end'=>$end_date);
                
                $source='free_registration';
                $accept=(isset($_POST['gdpr_accept']))? $_POST['gdpr_accept'] : false;
                
                $this->create_new_member_user($user, $members, true, $message, $subject, $accept, $source); 
            }
            
            // add to SE
            if(isset($info['se'])) {
                global $apiConnection;                
                
                // back compatibility (temporary)
                $info['se']=$apiConnection->repair_content_val($info['se']);
                // back compatibility end
                
                if($info['se']['id']) { 
                    $client=$apiConnection->getClient($info['se']['api']);
                    
                    $fields=array('name'=>$_POST['user_name'],'surname'=>$_POST['user_last_name']);
                    if(isset($_POST['field'])) $fields=array_merge($fields,$_POST['field']); 
                     
                    if(isset($_POST['custom_field'])) $custom_fields=$_POST['custom_field'];
                    else $custom_fields=array();
                    
                    $client->save_to_list_details($info['se']['id'], $_POST['user_email'], $fields, $custom_fields);
    
                }   
            }
            
            if(isset($info['email']) && $info['email']) {
            
                $subject=__('Registrace nového člena na: ','cms_member').get_bloginfo( 'name' );
                $message=__('Do členské sekce byl přidán uživatel s e-mailovou adresou: ','cms_member').$_POST['user_email'];
                $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';
                             
                wp_mail($info['email'], $subject, $message, $header);  
                
            } 
            
            if(isset($_POST['member_registration_redirect']) && $_POST['member_registration_redirect']) wp_redirect($_POST['member_registration_redirect']); 
            else wp_redirect(get_permalink($post->ID));  
            die();
        } 
        else wp_redirect(get_permalink($post->ID).'?mem_registration_error=5');
    }
    else wp_redirect(get_permalink($post->ID).'?mem_registration_error='.$error.'&email='.urlencode($_POST['user_email']));
    die();
}

function actions(){
//generate new password
    if(isset($_GET['user_id']) && isset($_GET['member_generate_new_pass']) && $_GET['member_generate_new_pass'] == 1){
      $user = get_userdata($_GET['user_id']);
      $member_option = get_option( 'member_basic' );
      
      $value = get_the_author_meta('cms_member', $user->ID);
      $section = array();

      $hasSection = true;

      if(isset($member_option['members'])) {
        foreach ($member_option['members'] as $id => $member) {
          if(isset($value[$id]) && isset($value[$id]['section'])){
            $section[$id]['section'] = $member['name'];
          }
        }
      }
      else $hasSection = false;

      $password = wp_generate_password(12, false);
      $update_user = wp_update_user( array( 'ID' => $user->ID, 'user_pass' => $password ) );

      $subject=__('Nové heslo','cms_member');        
      $message = __('Dobrý den,','cms_member');
      $message .= "\n\n";
      $message .= __('bylo vám vygenerováno nové heslo: ','cms_member');
      $message .= "\n\n";
      
      if($hasSection) $message .= $this->create_user_login_message($user->user_email, $password, $section, $member_option); 
      else {
        $message .= get_option('home') . '/wp-admin';
        $message .= "\n";
        $message .= __('Jméno', 'cms_member'). ': ' . $user->user_login;
        $message .= "\n";
        $message .= __('Heslo', 'cms_member'). ': ' . $password;   
      }
      
      $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';
      wp_mail($user->user_email, $subject, $message, $header); 
      
      wp_redirect(admin_url( "user-edit.php?user_id=".$user->ID."&member_generate_new_pass=2"));
    }  
  
    //add new member user

    if($_POST['member_action']=='create_new_member_user') {
        if(!wp_verify_nonce( $_POST['create_new_member_user'], 'create_new_member_user' ))
            die();
        $error=0;
        if($_POST['user']['email']) {
          $members=false;
          $i=1;
          foreach($_POST['member'] as $key=>$val) {
              if(isset($val['section'])) {
                  $members=true;
                  if($i==1) {
                      $member_option = get_option( 'member_basic' );
                      $email_text=$member_option['members'][$key]['email_text'];
                      $email_subject=(isset($member_option['members'][$key]['email_subject']))?$member_option['members'][$key]['email_subject']:'';
                      $i++;
                  }
              }
              
          }
          if($members) {
            if (!username_exists($_POST['user']['email']) && !email_exists($_POST['user']['email'])) {   
                $this->create_new_member_user($_POST['user'],$_POST['member'],isset($_POST['send_password'])? true:false,$email_text,$email_subject,false,'by_admin');
            } else $error=3;
          } else $error=2;
        } else $error=1;
        if(!$error) wp_redirect(admin_url('users.php?page=add_member_user&added=1'));
        else wp_redirect(admin_url('users.php?page=add_member_user&error='.$error));
        exit;
    }
    
    //add member user custom field
    
    if($_POST['member_action']=='add_new_member_field') {
        if(!wp_verify_nonce( $_POST['add_new_member_field'], 'add_new_member_field' ))
            die();
        $error=0;
        if($_POST['custom_field']['title']) {
            $custom_fields = get_option('mw_member_user_custom_fields');
            $new_field=$_POST['custom_field'];
            $new_field['id']=sanitize_title($_POST['custom_field']['title']);
            $custom_fields[]=$new_field;
            update_option( 'mw_member_user_custom_fields', $custom_fields); 
            
        } else $error=1;
        if(!$error) wp_redirect(admin_url('users.php?page=custom_fields_member_users&added=1'));
        else wp_redirect(admin_url('users.php?page=custom_fields_member_users&error='.$error));
        exit;
    }
    //add member user custom field
    
    if($_POST['member_action']=='update_member_fields') {
        if(!wp_verify_nonce( $_POST['update_member_fields'], 'update_member_fields' ))
            die();
        if(isset($_POST['custom_field'])) {
            $new_custom=array();
            foreach($_POST['custom_field'] as $field) {
                $new_custom[]=$field;
            }
            update_option( 'mw_member_user_custom_fields', $new_custom); 
            
        } else update_option( 'mw_member_user_custom_fields', array()); 
        wp_redirect(admin_url('users.php?page=custom_fields_member_users&updated=1'));
        exit;
    }
    
    
    
    //import member users

    else if($_POST['member_action']=='import_new_member_users') {
        if(!wp_verify_nonce( $_POST['import_new_member_user'], 'import_new_member_user' ))
            die();
        $error=0;
        
        $members=false;
        foreach($_POST['member'] as $val) {
          if(isset($val['section'])) $members=true;
        }
        
        if(!$members) $error=2;
        
        if(empty($_POST['email_text'])) $error='1b';
        else if(empty($_POST['emails'])) $error='1a';  
        
        if (strpos($_POST['email_text'], '%%login%%') == false) $error=3;           

        $added=0;
        $old=0;
        
        if(!$error) {            
            foreach (explode("\n",$_POST["emails"]) as $data) {
                $email=trim($data); 
                if (!username_exists($email) && !email_exists($email)) {  
                    $new_user['email']=$email;  
                    $this->create_new_member_user($new_user,$_POST['member'],isset($_POST['send_password'])? true:false, $_POST['email_text'], $_POST['email_subject'],false,'by_import');
                    $added++;
                } else {
                    
                    
                    if(!$user=get_user_by( 'email', $email )) $user=get_user_by( 'login', $email );
                    $user_id=$user->ID;
                    $member_meta=get_the_author_meta( 'cms_member', $user_id );
                    $member_option = get_option( 'member_basic' );
                    
                    $new_section=0;
                    if(isset($member_meta) && is_array($member_meta)) {  
                        foreach($_POST['member'] as $m_key=>$m_val) {   
                            // add member section  
                            if(isset($m_val['section']) && !isset($member_meta[$m_key])) {
                                $levels=(isset($m_val['levels']))? $m_val['levels'] : array();
                                $date=(isset($m_val['date']) && $m_val['date'])? $m_val['date'] : Date("d.m.Y", current_time( 'timestamp' ));
                                $time=(isset($m_val['time']) && $m_val['time'])? $m_val['time'] : Date("H:i", current_time( 'timestamp' ));
                                $member_meta[$m_key]=array('section'=>1,'levels'=>$levels,'date'=>$date,'time'=>$time);
                                $new_section++;
                                $old++;
                                
                            // add only member levels
                            } else if(isset($m_val['section']) && isset($m_val['levels'])) {
                               if(isset($member_meta[$m_key]['levels'])) {
                                  $levels=$member_meta[$m_key]['levels'];
                                  foreach($m_val['levels'] as $kl=>$vl) {
                                      $levels[$kl]=1;
                                  }
                               } else $levels=$m_val['levels'];
                               $member_meta[$m_key]['levels']=$levels;
                               $old++;
                            }
                        }

                        update_user_meta( $user_id, 'cms_member', $member_meta ); 
                    }
                    else {
                        add_user_meta( $user_id, 'cms_member', $_POST['member'] );    
                    } 
                    
                    if(isset($_POST['send_password']) && $new_section>0) {          
                        $subject = __('Nový přístup do členské sekce','cms_member'); 
                
                        if($_POST['email_text']=='') {            
                            $message = __('Dobrý den,','cms_member');
                            $message .= "\n\n";
                            $message .= __('byly vám vygenerovány přístupy:','cms_member');
                            $message .= "\n\n";
                            $message .= create_user_login_message($email,__('Vaše heslo bylo již vygenerováno v předchozí registraci na tomto webu. (Pokud si heslo nepamatujete, vygenerujte si na přihlašovací stránce nové.)','cms_member'), $_POST['member'], $member_option);
                            $message .= "\n\n";

                        } 
                        else $message=str_replace("%%login%%", $this->create_user_login_message($email, __('Vaše heslo, které na tomto webu již používáte. (Pokud si heslo nepamatujete, vygenerujte si na přihlašovací stránce nové.)','cms_member'), $_POST['member'], $member_option), $_POST['email_text']);
                                 
                        $header='From: '.get_bloginfo( 'name' ).' <'.get_bloginfo( 'admin_email' ).'>';
                                             
                        wp_mail($email, $subject, $message, $header);  
                    } 
                    
                    

                } 
            }
            wp_redirect(admin_url('users.php?page=import_member_users&added=1&info='.base64_encode(serialize(array('added'=>$added,'old'=>$old)))));
        }
        else wp_redirect(admin_url('users.php?page=import_member_users&error='.$error));
        exit;
    }
}

function login_redirect($redirect_to, $request, $user) {
    if(!isset($_POST['cms_abort_redirect']) && isset($user->roles) && $redirect_to==home_url() && (is_array($user->roles) && (in_array('member', $user->roles) || in_array('subscriber', $user->roles)) )) {      
        $user_sections=get_the_author_meta( 'cms_member', $user->ID );
        $member_setting=get_option('member_basic');
        if(is_array($user_sections)) {
            reset($user_sections);
            $first_key = key($user_sections);  
            if(isset($member_setting['members'][$first_key]['dashboard']) && $member_setting['members'][$first_key]['dashboard']) $redirect_to=get_permalink($member_setting['members'][$first_key]['dashboard']);
        }       
    }
    return $redirect_to;
}

function custom_login_css() {
  global $vePage;
  $login_css=get_option('member_login');
  echo '<style>';
  echo $vePage->generate_style('body.login', array('bg'=>array('background_color'=>array('color1'=>$login_css['background_color'],'color2'=>''),'background_image'=>$login_css['background_image']))); 
  echo $vePage->generate_style('.login #nav a, .login #backtoblog a, .login #nav a:hover, .login #backtoblog a:hover', array('color'=>$login_css['font-color'])); 

  echo $vePage->generate_style('.login h1 a', array(
      'background-image'=>'url('.$vePage->get_image_url($login_css['logo']).')',
      'background-size'=>$login_css['width']['size'].'px '.$login_css['height']['size'].'px',
      'width'=>$login_css['width']['size'].'px',
      'height'=>$login_css['height']['size'].'px',
  )); 
  
  if(isset($login_css['background_image']['cover']) && $login_css['background_image']['image']) {
        echo $vePage->generate_style('body.login', array('background-attachment'=>'fixed'));         
   } 
  echo 'html {height: 100%;min-height: 100%;} body {min-height: 100%;}';
  echo '</style>';

}

function save_element_data() {
  $meta=get_user_meta( $_POST['element_user_id'], $_POST['element_id'], true );
  if(isset($_POST['element_data'])) $meta[$_POST['page_id']]=$_POST['element_data'];
  else $meta[$_POST['page_id']]=array();
  update_user_meta( $_POST['element_user_id'], $_POST['element_id'], $meta );
  die();
}

/* theme activation
************************************************************************** */

function member_activation($versions) {
  if(empty($versions) || !isset($versions['member'])) {

      // Create first member section
      add_option( 'member_basic', array("members"=>array("1"=>array("name"=>__("Členská sekce",'cms_member'),"login"=>"","dashboard"=>""))));

  }
  $versions['member']=MEMBER_VERSION;
  return $versions;
}

function open_member_setting() {
    global $vePage, $cms, $wpdb;
    
    $meta=get_option('member_basic');
    $content=$meta['members'];
    
    
    if($_POST['edited']) $wpdb->update( $wpdb->prefix . "ve_posts_layer", array( 'vpl_layer' => $vePage->code($vePage->create_post_layer()) ), array( 'vpl_post_id' => $_POST['post_id'] ));

    //print_r($cms->page_set_groups);
    
    $newid=0;
    ?>
    <input type="hidden" name="member_save_member_section" value="1" />       
    <div id="member_select_member_container" class="multisetting_select_container">
        <span class="member_control_select_container <?php if(!is_array($content)) echo 'cms_nodisp'; ?>">
            <select id="member_select" class="member_input_member_name">
                 <?php 
                 $first_id=0;
                 $i=0;
                 foreach($content as $id=>$member) { 
                    echo '<option value="'.$id.'">'.$member['name'].'</option>';
                    $newid=$id+1;
                    if($i==0) $first_id=$id;
                    $i++;
                 }
                 
                 ?>
            </select>
            <a class="member_delete_member_section cms_button_secondary cms_icon_button_secondary cms_icon_button_delete" title="<?php echo __("Smazat členskou sekci","cms_member"); ?>" data-id="<?php echo $first_id; ?>" href="#">&nbsp;</a>
        </span>
        <button id="member_show_add_new_member" class="cms_button_secondary"><?php echo __('Vytvořit novou členskou sekci','cms_member'); ?></button>        
        <div id="member_section_api_key">
            <?php 
            $api=get_option('member_api'); 
            if(!isset($api['token']) || !$api['token']) {
                $api['token']=wp_generate_password(24,false);
                update_option('member_api',$api);
            }
            echo __('API key','cms_member').': <strong>'.$api['token'].'</strong>';
            ?>
        </div>
    </div>
    <div id="member_add_new_container" class="cms_nodisp">
        <input class="cms_text_input member_input_member_name" type="text" id="member_add_new_member_name" placeholder="<?php echo __('Zadejte název nové členské sekce','cms_member'); ?>" />
        <button id="member_save_member" class="cms_button_secondary" data-id="<?php echo $newid; ?>" data-name=""  data-tagid=""><?php echo __('Vytvořit členskou sekci','cms_member'); ?></button>
        <button id="member_storno_new_member" class="cms_button_secondary"><?php echo __('Storno','cms_member'); ?></button>
    </div>
    <div id="member_section_container">
    <?php
    if(is_array($content)) {
      $m=0;
      foreach($content as $id=>$member) { 
          
          $this->generate_member_setting($id,$m,'member_option');

          $m++;
      }
    }
    wp_nonce_field('ve_save_global_setting_nonce','ve_save_global_setting_nonce');
    ?>
    </div>
    <input type="hidden" name="ve_save_global_setting" value="member_option" />
    <script>
    jQuery(document).ready(function($) {
        $("#member_section_"+$("#member_select").val()).show();
    });
    </script>
    <?php
    die();
}

function generate_member_setting($id,$m,$slug,$get_meta=false) {
    global $cms;
    
    $subpages=array();  
    if(count($cms->subpages)) {    
          foreach($cms->subpages as $page) {                 
              if($page['parent_slug']==$cms->subpages[$slug]['parent_slug']) 
                  $subpages[]=$page; 
              if($slug==$page['menu_slug']) 
                  $currentp=$page['parent_slug'];
          }
    }
    
    echo '<div id="member_section_'.$id.'" class="member_section '.(($m==0)? 'member_section_v':'').'">';
              echo '<div class="subpage_nav"><ul class="cms_tabs">';
              $i=1;
              foreach($subpages as $page) {
                  $class=($i==1)? "active" : "";
                  echo '<li class="ve_global_setting_'.$m.'_tab"><a href="#ve_global_setting_'.$m.'_'. $page['menu_slug'].'" data-group="ve_global_setting_'.$m.'" class='.$class.'>'.$page['menu_title'].'</a></li>';  
                  $i++; 
              }
              echo '</ul></div>';
              $i=1;
              foreach($subpages as $page) { 
                  echo '<div id="ve_global_setting_'.$m.'_'.$page['menu_slug'].'" class="ve_global_setting_container ve_global_setting_'.$m.'_container cms_tab_content-'.$i.'">';
                  echo '<ul class="cms_tabs ve_global_setting_subtabs '.$page['menu_slug'].'_tab">';
                  $j=1;
                  foreach($cms->page_set_groups[$page['menu_slug']] as $value) {
                      $class=($j==1)? "active" : "";
                      echo '<li class="'.$page['menu_slug'].'_'.$m.'_tab"><a href="#'. $value['id'].'_'.$m.'" data-group="'.$page['menu_slug'].'_'.$m.'" class='.$class.'>'.$value['name'].'</a></li>';  
                      $j++; 
                  } 
                  echo '</ul>';
                  $j=1;
                  foreach($cms->page_set_groups[$page['menu_slug']] as $value) {     
                      echo '<div id="'.$value['id'].'_'.$m.'" class="'.$page['menu_slug'].'_'.$m.'_container ve_global_setting_subcontainer cms_setting_block_content cms_tab_content cms_tab_content-'.$j.'  '.$value['id'].'_'.$m.'_container"">';    
                      if(!$get_meta) {
                          $meta=get_option($value['id']);
                          $meta=$meta['members'][$id];
                      }
                      else $meta=$get_meta;

                      write_meta($cms->page_set[$value['id']],$meta,$value['id'].'[members]['.$id.']',$value['id'].'_members_'.$id,'','setting',$id);     
                      echo '</div>';
                  $j++; }
                  echo '</div>';
                  $i++; 
              }   
    echo '</div>';  

}

// check version

function check_version() {
  $versions=get_option('cms_versions');
 
  if(isset($versions['member']) && $versions['member']!=MEMBER_VERSION) { 
      if($versions['member']=='0.9') {   
      
          $members = get_option('member_basic');
          
          if($members && isset($members['members']) && is_array($members['members'])) {
              $header = get_option('member_header');
              $footer = get_option('member_footer');
              $popups = get_option('member_popups');
              $appearance = get_option('member_appearance');
              $fapi_notification = get_option('fapi_notification');
              
              //backup
              //if(!get_option('member_basic_backup')) update_option('member_basic_backup',$members);
              if(!get_option('member_appearance_backup')) update_option('member_appearance_backup',$appearance);
              if(!get_option('member_header_backup')) update_option('member_header_backup',$header);
              if(!get_option('member_footer_backup')) update_option('member_footer_backup',$footer);
              if(!get_option('member_popups_backup')) update_option('member_popups_backup',$popups);
              if(!get_option('fapi_notification_backup')) update_option('fapi_notification_backup',$fapi_notification);
              
              // new setting of member sections 
              
              $new_header=array('members');
              $new_footer=array('members');
              $new_popups=array('members');
              $new_appearance=array('members');
              $new_fapi_notification=array('members');
                            
              foreach($members['members'] as $mem_id=>$member) {
                  $new_header['members'][$mem_id]=$header;
                  $new_header['members'][$mem_id]['menu']=$header['menu'][$mem_id]; //menu
                  $new_footer['members'][$mem_id]=$footer;
                  $new_footer['members'][$mem_id]['menu']=$footer['menu'][$mem_id]; //menu
                  $new_popups['members'][$mem_id]=$popups;
                  $new_appearance['members'][$mem_id]=$appearance;
                  $new_fapi_notification['members'][$mem_id]=$fapi_notification;
              }
              
              update_option('member_header',$new_header);
              update_option('member_footer',$new_footer);
              update_option('member_appearance',$new_appearance);
              update_option('member_popups',$new_popups);
              update_option('fapi_notification',$new_fapi_notification);
    
              // notification log
              $notifications=get_option('notification_debug');
              if($notifications && is_array($notifications)) {
                  $new_notifications=array();
                  foreach($members['members'] as $mem_id=>$member) {
                      $new_notifications[$mem_id]=$notifications;
                  }
                  update_option('mem_notification_debug',$new_notifications);
              }
          }
          
      }
      $versions['member']=MEMBER_VERSION; 
      update_option('cms_versions',$versions);
  }   
}

}
