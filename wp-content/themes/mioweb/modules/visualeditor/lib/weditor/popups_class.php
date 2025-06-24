<?php   

class cmsPopups {
    var $popups_setting;
    var $popups_page_setting;
    var $edit_mode;
    var $popup_mode;
    var $popup;
    var $popups_onpage=array();
    var $popup_script;
      
    function __construct(){
      if ( current_user_can('administrator') ) $this->edit_mode=true;  
      else $this->edit_mode=false; 
      
      if ( isset($_GET['window_editor']) &&  $_GET['window_editor']=='cms_popup') $this->popup_mode=true;  
      else $this->popup_mode=false; 
       
      if($this->edit_mode) {
          add_action( 'wp_enqueue_scripts', array($this, 'load_admin_scripts')) ;  
          add_action( 'admin_enqueue_scripts', array($this, 'load_admin_scripts') );
          add_action( 'edit_form_after_title', array($this, 'admin_page_edit'));              
      }
      
      if($this->popup_mode) {
          add_action( 'wp', array($this, 'get_popup_setting')) ;               
      }
      add_action( 'init', array($this, 'register_popup_post_type') );
      add_action( 'wp_footer', array( $this, 'generate_popups' ));
    }
    
    function load_admin_scripts() {

      wp_register_style( 've_popup_admin_style',get_bloginfo('template_url').'/modules/visualeditor/lib/weditor/popups_admin.css' );
      
      wp_enqueue_script( 've_weditor_admin_script' );
      wp_enqueue_style('ve_popup_admin_style');

    } 
    
    function get_popup_setting() {
        global $vePage;
                
        $this->popup=get_post_meta( $_GET['id'], 've_popup', true);     
 
        $vePage->add_styles(array(
            ".cms_popup_content_container #content"=>array(
                'max-width'=>(isset($this->popup['width'])?$this->popup['width']['size'].$this->popup['width']['unit']:'800px'),
                'background-color'=>'#fff',
                'corner'=>(isset($this->popup['corner'])?$this->popup['corner']:'0').'px',
            ),
        ));        
        $vePage->page_setting['background_color']=isset($this->popup['background'])?$this->popup['background']:'#000000';
    }  
    
    function add_popups_script($script) {
        $this->popup_script.=$script;
    }  
    
    function generate_popups() {
        
        if(!$this->popup_mode) {
            global $vePage;
            $popups=false;
            $content='';    
            
            if(isset($this->popups_setting['clasic_popup']) && $this->popups_setting['clasic_popup'] && get_post($this->popups_setting['clasic_popup']) && !$vePage->is_mobile) {

                $this->popups_onpage[$this->popups_setting['clasic_popup']]=1;    
                
                $content.='var show;
                show=$("#ve_popup_container_'.$this->popups_setting['clasic_popup'].'").attr("data-show");';
                if($this->popups_setting['popup_type']=='onload') 
                    $content.='if(show=="0") { ve_show_popup('.$this->popups_setting['clasic_popup'].');}';
                else {
                    if($this->popups_setting['time']>0) {
                        $content.='if(show=="0") { setTimeout(function() { ve_show_popup('.$this->popups_setting['clasic_popup'].');}, '.($this->popups_setting['time']*1000).'); }';
                    }
                    
                    $scroll='';
                    $show_scroll=false;
                    
                    if($this->popups_setting['scroll']['size']>0) {
                        if($this->popups_setting['scroll']['unit']=='px') {
                            $content.='var height='.$this->popups_setting['scroll']['size'].';';
                        } else {
                            $content.='var height=($( document ).height()/100)*'.$this->popups_setting['scroll']['size'].';';
                        } 
                        $show_scroll=true;
                    } 
                    if($this->popups_setting['selector']) {
                        $content.='var height=0; if($("'.$this->popups_setting['selector'].'").length > 0) height=$("'.$this->popups_setting['selector'].'").offset().top;';  
                        $show_scroll=true;
                    }
                    if($show_scroll) {         
                            $scroll.='if ($(this).scrollTop() >= height || (($(this).scrollTop() + $(window).height()) >= $(document).height())) {
                                show=$("#ve_popup_container_'.$this->popups_setting['clasic_popup'].'").attr("data-show");
                                if(show=="0") { 
                                    ve_show_popup('.$this->popups_setting['clasic_popup'].'); 
                                    $("#ve_popup_container_'.$this->popups_setting['clasic_popup'].'").attr("data-show","1");
                                }}';                 
                    }                  
                    if($scroll) $content.='$(window).scroll(function() {'.$scroll.'});';                    
                }   
                      
            }
            
            if(isset($this->popups_setting['exit_popup']) && $this->popups_setting['exit_popup'] && get_post($this->popups_setting['exit_popup'])) {                
                $this->popups_onpage[$this->popups_setting['exit_popup']]=1;
                
                $content.='$(document).mousemove(function(e) { 
                    if(e.pageY <= 5) { 
                        var show=$("#ve_popup_container_'.$this->popups_setting['exit_popup'].'").attr("data-show");
                        if(show=="0") {
                            ve_show_popup('.$this->popups_setting['exit_popup'].');                             
                        }
                        $("#ve_popup_container_'.$this->popups_setting['exit_popup'].'").attr("data-show","1");   
                    } 
                });';
                
                
            }
                        
            if(count($this->popups_onpage)) {
                foreach($this->popups_onpage as $key=>$val) {
                    echo $this->create_popup($key);
                }
                
                
                echo ''; 
                
                wp_enqueue_script( 've_lightbox_script' );
                wp_enqueue_style( 've_lightbox_style' );
            }
            
            
        }
    } 
    
    function create_popup($key) {
        $content='';
        if(get_post($key)) {
                    global $vePage;
                    $setting=get_post_meta( $key, 've_popup', true);
                    $layer=$vePage->get_layer($key, 'cms_popup');
                    $content=$vePage->print_styles_array(array(        
                        array(
                            'styles'=>array(
                                'corner'=>$setting['corner'],
                            ),
                            'element'=>"#ve_popup_container_".$key,
                        ),
                    ));
                    $content.='<div style="display: none;"><div id="ve_popup_container_'.$key.'" class="ve_popup_container" data-delay="'.(isset($setting['delay'])?$setting['delay']:2).'" data-bg="'.$setting['background'].'" data-width="'.$setting['width']['size'].$setting['width']['unit'].'" data-show="'.(isset($_COOKIE['ve_popup_'.$key])? 1:0).'">'.$vePage->write_content($layer, false,'popup_'.$key).'</div></div>';
        }
        return $content;
                
    }
    
    function register_popup_post_type() {
    	$labels = array(
    		'name'               => __( 'Pop-upy', 'cms_ve' ),
    		'singular_name'      => __( 'Pop-up', 'cms_ve' ),
    		'menu_name'          => __( 'Pop-upy', 'cms_ve' ),
    		'name_admin_bar'     => __( 'Pop-up', 'cms_ve' ),
    		'add_new'            => __( 'Přidat pop-up', 'cms_ve' ),
    		'add_new_item'       => __( 'Přidat nový pop-up', 'cms_ve' ),
    		'new_item'           => __( 'Nový pop-up', 'cms_ve' ),
    		'edit_item'          => __( 'Upravit pop-up', 'cms_ve' ),
    		'view_item'          => __( 'Zobrazit pop-up', 'cms_ve' ),
    		'all_items'          => __( 'Všechny pop-upy', 'cms_ve' ),
    		'search_items'       => __( 'Hledat pop-upy', 'cms_ve' ),
    		'parent_item_colon'  => ':',
    		'not_found'          => __( 'Pop-up nenalezen', 'cms_ve' ),
    		'not_found_in_trash' => __( 'Pop-up nenalezen', 'cms_ve' )
    	);
    
    	$args = array(
    		'labels'             => $labels,
    		'public'             => false,
    		'publicly_queryable' => true,
    		'show_ui'            => true,
    		'show_in_menu'       => true,
    		'query_var'          => true,
    		'rewrite'            => array( 'slug' => 'cms_popup' ),
    		'capability_type'    => 'page',
    		'has_archive'        => false,
    		'hierarchical'       => false,
    		'menu_position'      => 23,
    		'supports'           => array( 'title' )
    	);
    
    	register_post_type( 'cms_popup', $args );
    }
    
    function admin_page_edit() {
        global $post;
        if (get_post_type( $post ) == 'cms_popup') { 
            ?>
            <div class="postbox ve_admin_editbut_container">
                <a class="cms_button open_window_editor" data-type="cms_popup" data-url="<?php echo home_url(); ?>/?window_editor=cms_popup" data-id="<?php echo $post->ID; ?>" href="#"><?php echo __( 'Upravit obsah pop-upu', 'cms_ve' ) ?></a>
            </div>        
            <?php               
        }
    }

}

/* Field type popup
************************************************************************** */

// selectpopup field type 
function field_type_popupselect($field, $meta, $group_id) {   
  $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
  $pages = get_posts(array('post_type'=>'cms_popup','posts_per_page'=>'1000'));
  $texts=array(
      'empty'=>__( ' - Žádný pop-up - ', 'cms_ve' ),
      'edit'=>__( 'Upravit vybraný pop-up', 'cms_ve' ),
      'duplicate'=>__( 'Duplikovat vybraný pop-up', 'cms_ve' ),
      'create'=>__( 'Vytvořit nový pop-up', 'cms_ve' ),
      'delete'=>__( 'Smazat vybraný pop-up', 'cms_ve' ),
  );
  cms_generate_field_weditor($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'], $content,$pages,'cms_popup', 'popups',$texts);  
}  

?>
