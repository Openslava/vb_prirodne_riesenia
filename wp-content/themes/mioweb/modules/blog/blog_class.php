<?php   

class CmsBlog {
var $edit_mode;
var $appearance;
var $setting;
var $templates=array();
var $template;
var $template_path;
var $script_version;
var $top_panel=array();
  
function __construct(){   
    if ( current_user_can('edit_pages') ) $this->edit_mode=true;  
    else $this->edit_mode=false;  
    
    $this->check_version();
    
    $this->script_version=filemtime(get_template_directory().'/style.css');
    
    if($this->edit_mode) {     
        
        //after save global or local setting
        add_action( 'init', array($this, 'after_save_options' ));  
        add_action( 've_after_save_options', array($this, 'after_save_options' )); 
    }
    
    //visual setting
    add_action( 've_global_setting', array($this, 'use_blog_setting') );
    
    add_action( 'wp', array($this,'init'));  //init
    add_filter('pre_get_posts', array($this, 'search_filter'));

    //theme activation
    add_action("cms_activation", array($this, 'blog_activation'));
    
    // user contact fields
    add_filter('user_contactmethods', array($this, 'extra_contact_info'));
    
    // load scripts
    add_action( 'wp_enqueue_scripts', array($this, 'load_front_scripts')) ; 
    
    // Excerpt
    add_filter('excerpt_more', array($this, 'new_excerpt_more'));
    add_filter( 'excerpt_length', array($this, 'new_excerpt_length'), 999 );
    
    // category setting
    add_action ( 'edit_category_form_fields', array($this, 'add_category_setting'));
    add_action ( 'edit_tag_form_fields', array($this, 'add_tag_setting'));
    add_action ( 'edited_category', array($this, 'save_category_setting'));
    add_action ( 'edited_post_tag', array($this, 'save_tag_setting'));
    
    // coments 
    add_filter('comment_form_submit_field', array($this, 'add_accept_field'), 999);
    //add_action('pre_comment_on_post', array($this, 'checkPost'));
    add_action('comment_post', array($this, 'add_accepted_to_comment_meta'));
    add_filter('manage_edit-comments_columns', array($this, 'display_accpted_column'));
    add_action('manage_comments_custom_column', array($this, 'display_accpted_in_column'), 10, 2);
    
    // post format support
    add_theme_support( 'post-formats', array( 'video', 'quote' ) );
    
    // for template post thumbnails on url
    add_filter ( 'post_thumbnail_html', array($this, 'get_custom_post_thumbnail_html'), 10, 5);
    
    add_filter( 'nav_menu_css_class', array($this, 'fix_blog_link_on_cpt'), 10, 3 );
    
    add_filter('is_protected_meta', array($this, 'mw_protected_meta_filter'), 10, 2);

    // html tags to user description
    remove_filter('pre_user_description', 'wp_filter_kses');
          
}
function get_custom_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr ){
  if(strpos($post_thumbnail_id, 'http')!==false) {
    return '<img src="'.$post_thumbnail_id.'" alt="" />';
  }
  else return $html;
}

function init() {
    $this->appearance=get_option('blog_appearance');
    
    if(!isset($this->appearance['post_look'])) $this->appearance['post_look']=1;
    if(!isset($this->appearance['post_detail_look'])) $this->appearance['post_detail_look']=1;
    if(isset($this->appearance['masonry']) && $this->appearance['post_look']!=3) {
        unset($this->appearance['masonry']);
    }
    
    $this->template=$this->appearance['appearance'];
    $this->template_path=$this->templates[$this->template]['path'];
    $this->template_directory=$this->templates[$this->template]['directory'];
    //require_once(get_blog_directory() . 'loop.php'); 
        
    if($this->is_blog()) {
        global $vePage, $cms, $post;
        //if(is_home()) $post->ID=0;
        $vePage->modul_type='blog';
        
        add_filter('body_class', array($this, 'add_bodyclass'));  //add body class                                  
        add_filter( 'the_content', array($this, 'blog_content_filter'));
        
        $blog_id=get_option('page_for_posts');
        $this->setting = get_option('blog_comments');

        if(isset($this->setting['blog_logolink']) && $this->setting['blog_logolink'] == 'blog')
          if($blog_id) $vePage->home_url=get_permalink($blog_id); 
        
        //add blog setting codes
        $codes=get_option('blog_codes');
        $cms->add_script('header', $codes['head_scripts']);
        $cms->add_script('footer', $codes['footer_scripts']);
        $cms->add_script('css', $codes['css_scripts']);
        
        //$this->appearance=get_option('blog_appearance');

        if(is_single() && !isset($_COOKIE['mioweb_post_visited_'.$post->ID])) {
            $post_visited = get_post_meta( $post->ID, 'mioweb_post_visited', true );
            if($post_visited) $post_visited++;
            else $post_visited=1;
            update_post_meta($post->ID, 'mioweb_post_visited', $post_visited);
            setcookie('mioweb_post_visited_'.$post->ID, 1 ,time() + (60*60*24*2), "/");
        }                     
    }     
}

function mw_protected_meta_filter($protected, $meta_key) {
    return $meta_key == 'mioweb_post_visited' ? true : $protected;  // protect meta key with number of visitors of blog post
}

function load_front_scripts() {  
    if($this->is_blog()) { 
        wp_enqueue_style('blog_content_css',get_blog_url().$this->templates[$this->template]['style'].'.css',array(),$this->script_version );  
        wp_enqueue_script( 've_lightbox_script' );
        wp_enqueue_style( 've_lightbox_style' );
        
        if(isset($this->appearance['masonry']))
            wp_enqueue_script( 've_masonry_script' );
    }
} 

function blog_content_filter($content) {
  return add_lightbox($content);
}

function edit_post_bar($post_id) {
    if($this->edit_mode){
        $content='<div class="post_edit_bar"><a target="_blank" class="post_edit" title="Editovat příspěvek" href="'.get_edit_post_link($post_id).'"></a></div>';
        return $content;
    } else return ''; 
}

function extra_contact_info($contactmethods) {
    unset($contactmethods['aim']);
    unset($contactmethods['yim']);
    unset($contactmethods['jabber']);
    $contactmethods['facebook'] = 'Facebook';
    $contactmethods['twitter'] = 'Twitter';
    $contactmethods['linkedin'] = 'LinkedIn';
    $contactmethods['google'] = 'Google+';
    $contactmethods['youtube'] = 'YouTube';
 
    return $contactmethods;
}

function add_bodyclass( $classes ) {
  if(isset($this->appearance['blog_sidebar'])) $classes[] = 'blog-structure-sidebar-'.$this->appearance['structure'];
  else {
    $classes[] = 'blog-structure-sidebar-none';  
  }
  $classes[] = 'blog-appearance-'.$this->appearance['appearance'];
  $classes[] = 'blog-posts-list-style-'.$this->appearance['post_look'];
  $classes[] = 'blog-single-style-'.$this->appearance['post_detail_look'];

	return $classes;
}
function search_filter($query) {  
    if ($query->is_search && ! is_admin()) {
        $query->set('post_type', 'post');
    }
    return $query;       
}
function create_blog_menu() {
  return '<ul> 
      <li><a href="'.admin_url('post-new.php').'">'.__( 'Nový příspěvek blogu', 'cms_blog' ).'</a></li>
      <li><a href="'.admin_url('edit.php').'">'.__( 'Seznam příspěvků', 'cms_blog' ).'</a></li>
      <li><a href="'.admin_url('edit-tags.php?taxonomy=category').'">'.__( 'Kategorie', 'cms_blog' ).'</a></li>
      <li><a href="'.admin_url('edit-tags.php?taxonomy=post_tag').'">'.__( 'Štítky (tagy)', 'cms_blog' ).'</a></li>
      <li><a href="'.admin_url('widgets.php').'">'.__( 'Sidebary a widgety', 'cms_blog' ).'</a></li>
      <li><a class="open-setting" data-type="group" data-setting="appearanceblog_option" title="'.__( 'Vzhled blogu', 'cms_blog' ).'" href="'.admin_url('admin.php?page=blog_appearance').'">'.__( 'Vzhled blogu', 'cms_blog' ).'</a></li>
      <li><a class="open-setting" data-type="group" data-setting="blog_option" title="'.__( 'Nastavení blogu', 'cms_blog' ).'" href="'.admin_url('admin.php?page=blog_option').'">'.__( 'Nastavení blogu', 'cms_blog' ).'</a></li>
</ul>';
}

function use_blog_setting() {
  global $vePage, $cms;

  if($this->is_blog()){ 
      $blog_appearance=get_option('blog_appearance');
      if(!isset($blog_appearance['custom_blog_fonts'])) {
        $blog_appearance['title_font']=$vePage->page_setting['title_font']; 
        $blog_appearance['font']=$vePage->page_setting['font']; 
        $blog_appearance['link_color']=$vePage->page_setting['link_color']; 
      }
      if($blog_appearance['appearance']=='style3') {
        $blog_appearance['background_color']='#f1f1f1';
        $blog_appearance['background_image']=array();
      } else if($blog_appearance['appearance']=='style4') {
        $blog_appearance['background_color']='#fff';
        $blog_appearance['background_image']=array();
      }
      $vePage->page_setting=$blog_appearance;

      $setting=get_option('blog_header');
      if($setting['show']=='blog') {
          $vePage->header_setting=$setting;
          $vePage->h_menu=(isset($vePage->header_setting['menu']))? $vePage->header_setting['menu']: '';  
      } 
      $setting=get_option('blog_footer');
      if($setting['show']=='blog') {
          $vePage->footer_setting=$setting;
          $vePage->f_menu=(isset($vePage->footer_setting['menu']))? $vePage->footer_setting['menu']: '';  
      }
      
      $vePage->popups->popups_setting=get_option('blog_popups');  
      
      //add blog setting google fonts
      if(isset($cms->google_fonts[$vePage->page_setting['tb_font']['font-family']])) $vePage->google_fonts[$vePage->page_setting['tb_font']['font-family']][$vePage->page_setting['tb_font']['weight']]=$vePage->page_setting['tb_font']['weight'];
      if(isset($cms->google_fonts[$vePage->page_setting['sidebar_font']['font-family']])) $vePage->google_fonts[$vePage->page_setting['sidebar_font']['font-family']][$vePage->page_setting['sidebar_font']['weight']]=$vePage->page_setting['sidebar_font']['weight'];
      
      if(is_category()) {
         global $wp_query;
         $cat_set=get_option('mw_category_setting_'.$wp_query->query_vars['cat']);
         if(isset($cat_set['image']) && $cat_set['image'])
            $this->top_panel['image']=$cat_set['image'];
      }
      else if(is_tag()) {
         global $wp_query;
         $tag_set=get_option('mw_tag_setting_'.$wp_query->query_vars['tag_id']);
         if(isset($tag_set['image']) && $tag_set['image'])
            $this->top_panel['image']=$tag_set['image'];
      }
      
      
      if(isset($this->top_panel['image'])) {
        $vePage->add_style("#blog_top_panel",array(
            'bg'=>array(
                'background_color'=>$vePage->page_setting['tb_background'],
                'background_image'=>array(
                    'image'=>$this->top_panel['image'],
                    'cover'=>1,
                    'repeat'=>'no-repeat',
                    'position'=>'center center',
                ),
            ),
        )); 
        
        unset($vePage->page_setting['tb_font']['color']);
        
      } else {
          $vePage->add_style("#blog_top_panel",array(
              'bg'=>array(
                  'background_color'=>$vePage->page_setting['tb_background'],
              ),
          )); 
      }
      $vePage->add_style("#blog_top_panel h1",array(
          'font'=>$vePage->page_setting['tb_font']
      )); 
      $vePage->add_style("#blog_top_panel .blog_top_panel_text, #blog_top_panel .blog_top_panel_subtext, #blog_top_panel .blog_top_author_title small, #blog_top_panel .blog_top_author_desc",array(
          'font'=>array('color'=>$vePage->page_setting['tb_font']['color'])
      )); 
      $vePage->add_style("#blog-sidebar .widgettitle",array(
          'font'=>$vePage->page_setting['sidebar_font']
      )); 
      $vePage->add_style(".article h2 a",array(
          'font'=>$vePage->page_setting['article_font']
      )); 
      $vePage->add_style(".entry_content",array(
          'line-height'=>(isset($vePage->page_setting['font']['line-height']) && $vePage->page_setting['font']['line-height'])? $vePage->page_setting['font']['line-height']:'',
      )); 
      $vePage->add_style(".article_body .excerpt",array(
          'font'=>(isset($vePage->page_setting['article_font_text']))? $vePage->page_setting['article_font_text']:'',
      )); 

      // article button
      $vePage->add_style(".article .article_button_more",array(
          'background-color'=>(isset($vePage->page_setting['button_color']))? $vePage->page_setting['button_color']:'',
      )); 
      $vePage->add_style(".article .article_button_more:hover",array(
          'background-color'=>(isset($vePage->page_setting['button_color']))? $vePage->shiftColor($vePage->page_setting['button_color'],0.8):'',
      )); 

  }  
}

function print_blog_comments($style=1) {
      global $post;
      $blog_setting=$this->setting;
      $page_comments=get_post_meta($post->ID, 'page_comments', true);
      $wordpress=(isset($blog_setting['comments']['wordpress']) && (!isset($page_comments['hide_comments']) || !isset($page_comments['hide_comments']['wordpress'])))? true : false;
      $facebook=(isset($blog_setting['comments']['facebook']) && (!isset($page_comments['hide_comments']) || !isset($page_comments['hide_comments']['facebook'])))? true : false;
      $order=(isset($blog_setting['comments_order']) && $blog_setting['comments_order'])? $blog_setting['comments_order'] : 'wordpress';
      $order=(isset($page_comments['comments_order']) && $page_comments['comments_order'])? $page_comments['comments_order'] : $order;
      if ( ((comments_open() || get_comments_number()) && $wordpress) || $facebook  ) {
          $mw_comment_set=array(
            'comment_style'=>$style
          );
          echo '<div class="commenttitle title_element_container">'.__('Komentáře','cms_blog').'</div>';
          if($order=="wordpress") {
            if($wordpress) {
              echo '<div class="element_comment_'.$style.' blog_comments">';
              comments_template('/skin/comments.php');  
              echo '</div>';
            }
            if($facebook) echo cms_facebook_comments(get_permalink());
          } else {            
						if($facebook) echo cms_facebook_comments(get_permalink());
            if($wordpress) {
              echo '<div class="element_comment_'.$style.' blog_comments">';
              comments_template('/skin/comments.php');  
              echo '</div>';
            }
          }
			}
}

function is_blog () {
  global $post;
  $posttype = get_post_type($post);
  return ( ((is_archive() && $posttype == 'post') || (is_author()) || (is_category()) || (is_home()) || (is_tag()) || (is_search()) || ( (is_single()) && ( $posttype == 'post') ) ) && !isset($_GET['window_editor']) ) ? true : false ;
}

function fix_blog_link_on_cpt( $classes, $item, $args ) {
	if( !$this->is_blog() ) {
		$blog_page_id = intval( get_option('page_for_posts') );
		if( $blog_page_id != 0 && $item->object_id == $blog_page_id )
			unset($classes[array_search('current_page_parent', $classes)]);
	}
	return $classes;
}

function get_visit_number($post_id) {
    $post_visited = get_post_meta( $post_id, 'mioweb_post_visited', true );
    if(!$post_visited) $post_visited=1;
    return $post_visited;
}

function add_template($id, $set) {
    $this->templates[$id]=$set;
}

function after_save_options() {
    if(isset($_POST["blog_comments"])) {
        if($_POST["blog_comments"]['blog_page']['show_on_front']=='posts') {
            update_option( 'show_on_front', 'posts' );
            update_option( 'page_on_front', '0' ); 
            update_option( 'page_for_posts', '0' );          
        } 
        else {
            update_option( 'show_on_front', 'page' );
            update_option( 'page_for_posts', $_POST["blog_comments"]['blog_page']['page_for_posts'] );
            $page_on_front=get_option( 'page_on_front' );
            if(!$page_on_front || $page_on_front==$_POST["blog_comments"]['blog_page']['page_for_posts']) {
                $pages = get_pages(array('post_status'=>'publish'));
                if(is_array($pages) && count($pages)>1) {
                    foreach($pages as $page) {                    
                        if($page->ID!=$_POST["blog_comments"]['blog_page']['page_for_posts']) {
                            update_option( 'page_on_front', $page->ID );
                            break; 
                        }
                    }
                }
            }
        }    
    }
}

/* Categories and tags
************************************************************************** */

function add_category_setting($tag) {
    $cat_meta = get_option( "mw_category_setting_".$tag->term_id);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="mw_cat_meta_image"><?php _e('Obrázek kategorie', 'cms_blog'); ?></label>
        </th>
        <td>
            <?php cms_generate_field_upload('mw_cat_meta[image]','mw_cat_meta_image',isset($cat_meta['image'])?$cat_meta['image']:''); ?>
            <span class="description"><?php _e('Obrázek se zobrazí na stránce kategorie na pozadí pruhu s nadpisem','cms_blog'); ?></span>
        </td>
    </tr>
    <?php
}

function save_category_setting( $term_id ) {
    if ( isset( $_POST['mw_cat_meta'] ) ) {
        update_option( "mw_category_setting_".$term_id, $_POST['mw_cat_meta'] );
    }
}

function add_tag_setting($tag) {
    $cat_meta = get_option( "mw_tag_setting_".$tag->term_id);
    ?>
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="mw_tag_meta_image"><?php _e('Obrázek štítku', 'cms_blog'); ?></label>
        </th>
        <td>
            <?php cms_generate_field_upload('mw_tag_meta[image]','mw_tag_meta_image',isset($cat_meta['image'])?$cat_meta['image']:''); ?>
            <span class="description"><?php _e('Obrázek se zobrazí na stránce štítku na pozadí pruhu s nadpisem','cms_blog'); ?></span>
        </td>
    </tr>
    <?php
}

function save_tag_setting( $term_id ) {
    if ( isset( $_POST['mw_tag_meta'] ) ) {
        update_option( "mw_tag_setting_".$term_id, $_POST['mw_tag_meta'] );
    }
}

/* Excerpt
************************************************************************** */

function new_excerpt_more( $more ) {
	return '...';
}
function new_excerpt_length( $more ) {
	if(isset($this->appearance['excerpt_length']) && $this->appearance['excerpt_length']['size']) return $this->appearance['excerpt_length']['size'];
  else return $more;
}

/* Aktivace šablony
************************************************************************** */

function blog_activation($versions) {
  if(empty($versions) || !isset($versions['blog'])) {
      /*
      global $vePage;

      // Create home page
      $new_post = array(
            'post_title' => __('Blog','cms_blog'),
            'post_name' => 'blog',
            'post_content' => 'YToxOntpOjA7YTozOntzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7YTo4OntzOjQ6ImZvbnQiO2E6NDp7czo5OiJmb250LXNpemUiO3M6MDoiIjtzOjExOiJmb250LWZhbWlseSI7czowOiIiO3M6Njoid2VpZ2h0IjtzOjY6Im5vcm1hbCI7czo1OiJjb2xvciI7czo3OiIjZmZmZmZmIjt9czoxMDoibGlua19jb2xvciI7czowOiIiO3M6MTY6ImJhY2tncm91bmRfY29sb3IiO2E6Mjp7czo2OiJjb2xvcjEiO3M6NzoiIzIxOWVkMSI7czo2OiJjb2xvcjIiO3M6MDoiIjt9czoxNjoiYmFja2dyb3VuZF9pbWFnZSI7YTozOntzOjg6InBvc2l0aW9uIjtzOjEzOiJjZW50ZXIgY2VudGVyIjtzOjY6InJlcGVhdCI7czo5OiJuby1yZXBlYXQiO3M6NToiaW1hZ2UiO3M6MDoiIjt9czoxMDoiYm9yZGVyLXRvcCI7YTozOntzOjQ6InNpemUiO3M6MToiMCI7czo1OiJzdHlsZSI7czo1OiJzb2xpZCI7czo1OiJjb2xvciI7czowOiIiO31zOjEzOiJib3JkZXItYm90dG9tIjthOjM6e3M6NDoic2l6ZSI7czoxOiIwIjtzOjU6InN0eWxlIjtzOjU6InNvbGlkIjtzOjU6ImNvbG9yIjtzOjA6IiI7fXM6MTE6InBhZGRpbmdfdG9wIjtzOjI6IjMwIjtzOjE0OiJwYWRkaW5nX2JvdHRvbSI7czoyOiIzMCI7fXM6NzoiY29udGVudCI7YToxOntpOjA7YTozOntzOjQ6InR5cGUiO3M6NzoiY29sLW9uZSI7czo1OiJjbGFzcyI7czowOiIiO3M6NzoiY29udGVudCI7YToxOntpOjE7YTo0OntzOjQ6InR5cGUiO3M6NToidGl0bGUiO3M6NzoiY29udGVudCI7czoxMzoiPGgxPkJsb2c8L2gxPiI7czo1OiJzdHlsZSI7YToxOntzOjQ6ImZvbnQiO2E6NTp7czo5OiJmb250LXNpemUiO3M6MjoiNDAiO3M6MTE6ImZvbnQtZmFtaWx5IjtzOjA6IiI7czo2OiJ3ZWlnaHQiO3M6Njoibm9ybWFsIjtzOjU6ImNvbG9yIjtzOjA6IiI7czoxMToidGV4dC1zaGFkb3ciO3M6NDoibm9uZSI7fX1zOjY6ImNvbmZpZyI7YTo1OntzOjk6Im1heF93aWR0aCI7czowOiIiO3M6MTA6Im1hcmdpbl90b3AiO3M6MToiMCI7czoxMzoibWFyZ2luX2JvdHRvbSI7czoyOiIxMCI7czo1OiJkZWxheSI7czowOiIiO3M6NToiY2xhc3MiO3M6MDoiIjt9fX19fX19',
            'post_status' => 'publish',
            'post_type'=>'page',
            'post_author' => 1,
          );
      $blog_id=$vePage->save_new_page($new_post, 'page/1/', 'YToxOntpOjA7YTozOntzOjU6ImNsYXNzIjtzOjA6IiI7czo1OiJzdHlsZSI7YTo4OntzOjQ6ImZvbnQiO2E6NDp7czo5OiJmb250LXNpemUiO3M6MDoiIjtzOjExOiJmb250LWZhbWlseSI7czowOiIiO3M6Njoid2VpZ2h0IjtzOjY6Im5vcm1hbCI7czo1OiJjb2xvciI7czo3OiIjZmZmZmZmIjt9czoxMDoibGlua19jb2xvciI7czowOiIiO3M6MTY6ImJhY2tncm91bmRfY29sb3IiO2E6Mjp7czo2OiJjb2xvcjEiO3M6NzoiIzIxOWVkMSI7czo2OiJjb2xvcjIiO3M6MDoiIjt9czoxNjoiYmFja2dyb3VuZF9pbWFnZSI7YTozOntzOjg6InBvc2l0aW9uIjtzOjEzOiJjZW50ZXIgY2VudGVyIjtzOjY6InJlcGVhdCI7czo5OiJuby1yZXBlYXQiO3M6NToiaW1hZ2UiO3M6MDoiIjt9czoxMDoiYm9yZGVyLXRvcCI7YTozOntzOjQ6InNpemUiO3M6MToiMCI7czo1OiJzdHlsZSI7czo1OiJzb2xpZCI7czo1OiJjb2xvciI7czowOiIiO31zOjEzOiJib3JkZXItYm90dG9tIjthOjM6e3M6NDoic2l6ZSI7czoxOiIwIjtzOjU6InN0eWxlIjtzOjU6InNvbGlkIjtzOjU6ImNvbG9yIjtzOjA6IiI7fXM6MTE6InBhZGRpbmdfdG9wIjtzOjI6IjMwIjtzOjE0OiJwYWRkaW5nX2JvdHRvbSI7czoyOiIzMCI7fXM6NzoiY29udGVudCI7YToxOntpOjA7YTozOntzOjQ6InR5cGUiO3M6NzoiY29sLW9uZSI7czo1OiJjbGFzcyI7czowOiIiO3M6NzoiY29udGVudCI7YToxOntpOjE7YTo0OntzOjQ6InR5cGUiO3M6NToidGl0bGUiO3M6NzoiY29udGVudCI7czoxMzoiPGgxPkJsb2c8L2gxPiI7czo1OiJzdHlsZSI7YToxOntzOjQ6ImZvbnQiO2E6NTp7czo5OiJmb250LXNpemUiO3M6MjoiNDAiO3M6MTE6ImZvbnQtZmFtaWx5IjtzOjA6IiI7czo2OiJ3ZWlnaHQiO3M6Njoibm9ybWFsIjtzOjU6ImNvbG9yIjtzOjA6IiI7czoxMToidGV4dC1zaGFkb3ciO3M6NDoibm9uZSI7fX1zOjY6ImNvbmZpZyI7YTo1OntzOjk6Im1heF93aWR0aCI7czowOiIiO3M6MTA6Im1hcmdpbl90b3AiO3M6MToiMCI7czoxMzoibWFyZ2luX2JvdHRvbSI7czoyOiIxMCI7czo1OiJkZWxheSI7czowOiIiO3M6NToiY2xhc3MiO3M6MDoiIjt9fX19fX19','blog');
      
      // wordpress setting          
      update_option( 'page_for_posts', $blog_id );
      */
  }             
}
function check_version()
{
    $versions = get_option('cms_versions');
    if (isset($versions['blog']) && $versions['blog'] != BLOG_VERSION) {
      if (version_compare($versions['blog'], '1.0', '<')) {
          // blog seo
          $home_seo=get_option('home_seo');
          update_option('mw_blog_seo',$home_seo);
          
          // blog fonts
          $blog_appearance=get_option('blog_appearance');
          $blog_appearance['custom_blog_fonts']=1;
          
          // sidebar
          if($blog_appearance['structure']=='nosidebar') {
            $blog_appearance['blog_sidebar']=0;
          } else {
            $blog_appearance['blog_sidebar']=1;
          }
          
          update_option('blog_appearance',$blog_appearance);
      }   
      $versions['blog'] = BLOG_VERSION;
      update_option('cms_versions', $versions);
    }
}

/* gdpr */
function add_accept_field($submitField = '') {
    global $vePage;
    $gdpr=get_option('web_option_gdpr');
    $field='';
    if($gdpr && $gdpr['comment_form_info']) {
        $field.='<p class="mw_field_gdpr_accept">';
        $field.='<input type="hidden" value="'.$gdpr['comment_form_info'].'" name="mw_comment_gdpr" />';
        $field.=$gdpr['comment_form_info'];
        if($gdpr['comment_form_link_text'] && isset($gdpr['gdpr_url']) && $gdpr['gdpr_url']) $field.=' <a href="'.$vePage->create_link($gdpr['gdpr_url']).'" target="_blank">'.$gdpr['comment_form_link_text'].'</a>';
        $field.='</p>';
    }
    return $field . $submitField;
}
function add_accepted_to_comment_meta($comment_id = 0){
  if (isset($_POST['mw_comment_gdpr']) && !empty($comment_id)) {
      $val=array(
          'time' => current_time( 'timestamp', 0 ),
          'text' => $_POST['mw_comment_gdpr']
      );
      add_comment_meta($comment_id, '_mw_comment_gdpr', $val);
  }
}
function display_accpted_column($columns = array()) {
    $columns['mw_accept_gdpr'] = __('GDPR souhlas', 'cms_blog');
    return $columns;
}
function display_accpted_in_column($column = '', $comment_id = 0) {
    if ($column === 'mw_accept_gdpr') {
        $content='';
        $val = get_comment_meta($comment_id, '_mw_comment_gdpr', true);
        if($val) {
            $content .= (!empty($val) && isset($val['time'])) ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $val['time']) : '';
            $content .= '<div class="mw_info_icon">i<span>'.$val['text'].'</span></div>';
            echo $content;
        }
    }
    return $column;
}


}

?>
