<?php   

class cmsWEditor {
    var $window_setting;
    var $edit_mode;
    var $popup;
    var $popups_onpage=array();
    var $popup_script;
      
    function __construct(){
      if ( current_user_can('administrator') ) $this->edit_mode=true;  
      else $this->edit_mode=false; 
       
      if($this->edit_mode) {
          add_action( 'wp_enqueue_scripts', array($this, 'load_admin_scripts')) ;  
          add_action( 'admin_enqueue_scripts', array($this, 'load_admin_scripts') );
          
          add_action('wp_ajax_ve_create_window_post_form',  array($this, 'create_window_post_form'));
          add_action('wp_ajax_ve_create_window_post',  array($this, 'create_window_post')); 
          
          add_action('wp_ajax_ve_open_weditor_setting',  array($this, 'open_weditor_setting'));   
          add_action('wp_ajax_ve_save_weditor_setting',  array($this, 'save_weditor_setting'));             
      }
      
      add_action( 'init', array($this, 'register_weditor_post_type') );
    }
    
    function load_admin_scripts() {
      wp_enqueue_script( 've_weditor_admin_script' );
    } 
    
    function create_content($id,$pre,$option='',$key='',$post_id='',$editable=false) {
        global $vePage;
        $content='';
        if(get_post($id)) {
            $layer=$vePage->get_layer($id, $pre); 
            $content='';
            if($editable && $this->edit_mode) {
                $content.='<div class="edit_weditor_content_container">
                <div class="post_edit_bar"><a class="post_edit ve_open_weditor_setting" data-postid="'.$post_id.'" data-type="'.$pre.'" data-option="'.$option.'" data-key="'.$key.'" title="Editovat příspěvek" href="#"></a></div>';
                
            }  
            $content.=$vePage->write_content($layer, false, $pre.'_'.$id);  
            if($editable && $this->edit_mode) {
                $content.='</div>';
            }  
        }
        return $content;               
    }
    
    function register_weditor_post_type() {
  
    	$args = array(
    		'labels'             => array(),
    		'public'             => false,
    		'publicly_queryable' => true,
    		'show_ui'            => false,
    		'show_in_menu'       => false,
    		'query_var'          => true,
    		'capability_type'    => 'post',
    		'has_archive'        => false,
    		'hierarchical'       => false,
    		'supports'           => array( 'title' )
    	);
    
    	register_post_type( 'cms_footer', $args );
      register_post_type( 'weditor', $args );
      register_post_type( 've_header', $args );
      register_post_type( 've_elvar', $args );
      register_post_type( 'mw_slider', $args );
    }
    
    /* create window post */
    function create_window_post_form() {
        global $vePage;
        ?>
            <div class="cms_setting_block_content">
                <div class="set_form_row">
                    <div class="label"><?php echo __('Název','cms_ve'); ?></div>
                    <input class="cms_text_input cms_text_input_s required" type="text" id="ve_post_title" name="ve_post_title" value="" />
                    <input type="hidden" name="ve_post_type" value="<?php echo $_POST['post_type']; ?>" >
                </div>
                
                <?php if(!isset($_POST['copy']) && $_POST['theme_file']) { ?>
                    <div class="set_form_row">
                        <div class="label"><?php echo __('Šablona','cms_ve'); ?></div>
                        <div class="ve_create_page_selector">
                        <?php $vePage->get_template_selector(0, $_POST['post_type'], $_POST['theme_file'].'/1/'); ?> 
                        </div>
                    </div>
                <?php } else if(isset($_POST['post_id'])) { ?>
                    <input type="hidden" name="ve_create_window_post_copy" value="<?php echo $_POST['post_id']; ?>" >   
                <?php                                                                                                                              
                } else echo '<input type="hidden" name="ve_page_template[directory]" value="" >';
        die();
    }
    function create_window_post() {
        global $vePage;
        $new_post = array(
            'post_title' => $_POST['ve_post_title'],
            'post_status' => 'publish',
            'post_type'=>$_POST['ve_post_type'],
            'post_author' => 1, 
        );
        
        if(isset($_POST['ve_create_window_post_copy'])) {
            global $wpdb;
            $copy_id=$_POST['ve_create_window_post_copy'];
               
            $template=get_post_meta($copy_id,'ve_page_template', true);
            $result=$wpdb->get_row("SELECT * FROM ".$wpdb->prefix."ve_posts_layer WHERE vpl_post_id=".$copy_id);
    
            $post_id=$vePage->save_new_window_post($new_post, $template['directory'],$result->vpl_layer,$_POST['ve_post_type']);  
            
            $post_meta=get_post_meta($copy_id);
            
            foreach($post_meta as $key=>$val) {    
                if($key!="_edit_last" && $key!="_edit_lock") 
                    update_post_meta( $post_id, $key,@unserialize($val[0]));
            }  
        } else {
            $post_id=$vePage->save_new_window_post($new_post, $_POST['ve_page_template']['directory'],'',$_POST['ve_post_type']); 
        }
        
        $return['url']=home_url().'/?window_editor='.$_POST['ve_post_type'].'&id='.$post_id;
        $return['id']=$post_id;
        $return['title']=$_POST['ve_post_title'];
        wp_send_json( $return );
        die();
    }
    
    function weditor_content($id, $args=array()) {
        global $vePage;
        
        $defaults=array(
            'key'=>'',
            'option'=>'',
            'post_id'=>'',
            'type'=>'weditor'  
        );
        
        $r = wp_parse_args( $args, $defaults );
        
        $content='';
        if($this->edit_mode) $content.='<div class="weditor_content_container">';
        if($id) {
        
            $content.=$vePage->weditor->create_content($id,$r['type'],$r['option'],$r['key'],$r['post_id'],true);
            
        } else if($this->edit_mode) {
            $content .= '<div class="row_edit_container admin_feature">';
    
            $content .= '<div class="row_add_container">';
            $content .= '<a class="ve_add_content_button ve_open_weditor_setting" data-postid="'.$r['post_id'].'" data-type="'.$r['type'].'" data-option="'.$r['option'].'" data-key="'.$r['key'].'" href="#" title="'.__('Vyberte nebo vytvořte obsah který chcete zobrazit','cms_ve').'">' . __('Přidat obsah', 'cms_ve') . '</a>';
            $content .= '</div>';
            $content .= '</div>';    
        }
        if($this->edit_mode) $content.='</div>';
        return $content;
    }
    function open_weditor_setting($id,$type) {
        $setting=array(
            array(
                'id'=>'id',
                'title'=>__('Obsah', 'cms_ve'),
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
            )
        );
        
        $option=get_option($_POST['option']);

        ?>
        <div class="cms_setting_block_content">
            <?php echo write_meta($setting, array('id'=>$option[$_POST['key']]), 'weditor', 'weditor', ''); ?>
            <input type="hidden" name="post_id" value="<?php echo $_POST['postid']; ?>" />
            <input type="hidden" name="option_key" value="<?php echo $_POST['key']; ?>" />
            <input type="hidden" name="option_name" value="<?php echo $_POST['option']; ?>" />
            <input type="hidden" name="post_type" value="<?php echo $_POST['type']; ?>" />
        </div>
        <?php
        die();
    }
    function save_weditor_setting() {
        global $vePage;
        
        $id=$_POST['weditor']['id'];

        if($_POST['post_id']) {
        
        } else {
            $option=get_option($_POST['option_name']);
            $option[$_POST['option_key']]=$id;
            update_option($_POST['option_name'],$option);
        }
        
        $wfonts = get_post_meta($id, 've_google_fonts', true);
        if (count($wfonts) > 0) {
            $fonts = array();
            foreach ($wfonts as $key => $val) {
                $fonts[] = str_replace(" ", "+", $key) . ':' . implode(",", array_keys($val));
            }

            $return['font'] = implode("|", $fonts);
        } else $return['font'] = "";
      
        
        
        //$return['content']=$vePage->weditor->create_content($id,$_POST['post_type'],$_POST['option_name'],$_POST['option_key'],$_POST['post_id'],true);
        
        $args=array(
            'key'=>$_POST['option_key'], 
            'option'=>$_POST['option_name'],
            'type'=>$_POST['post_type'],
            'post_id'=>$_POST['post_id'],
        );
        $return['content']=$vePage->weditor->weditor_content($id, $args); 
        
        wp_send_json($return); 
        die();
    }

}



/* Field type weditor
************************************************************************** */

function cms_generate_field_weditor($name,$id,$value,$pages,$type,$theme_file, $texts) {  
  global $cms;
  ?>
  <div class="ve_windowselect_container">
      <?php $cms->select_page($pages, $value, $name, $id, 've_windowselect_selector', $texts['empty']); ?> 
      <span class="ve_window_tools" <?php if(!$value || !get_post($value)) echo 'style="display:none;"'; ?>>
          <a class="cms_button_secondary cms_icon_button_secondary cms_icon_button_edit open_window_editor edit_window_editor" data-type="<?php echo $type; ?>" data-url="<?php echo home_url(); ?>/?window_editor=<?php echo $type; ?>" data-id="<?php echo $value; ?>" href="#">&nbsp;</a>
          <a class="cms_button_secondary cms_icon_button_secondary cms_icon_button_copy create_copy_window_editor" data-type="<?php echo $type; ?>" data-id="<?php echo $value; ?>" href="#" title="<?php echo $texts['duplicate'] ?>">&nbsp;</a>
          <a class="cms_button_secondary cms_icon_button_secondary cms_icon_button_delete delete_window_editor" data-id="<?php echo $value; ?>" href="#">&nbsp;</a>
      </span>
      <button class="cms_button_secondary open_window_editor" data-type="<?php echo $type; ?>" data-themes="<?php echo $theme_file; ?>" title="<?php echo $texts['create'] ?>" data-url="<?php echo home_url(); ?>/?window_editor=<?php echo $type; ?>" data-id=""><?php echo $texts['create'] ?></button>
  </div>
  <?php
}

// footerselect field type 
function field_type_footerselect($field, $meta, $group_id) {   
  $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
  $pages = get_posts(array('post_type'=>'cms_footer','posts_per_page'=>'1000'));
  $texts=array(
      'empty'=>__( ' - Bez patičky - ', 'cms_ve' ),
      'edit'=>__( 'Upravit vybranou patičku', 'cms_ve' ),
      'duplicate'=>__( 'Duplikovat vybranou patičku', 'cms_ve' ),
      'create'=>__( 'Vytvořit novou patičku', 'cms_ve' ),
      'delete'=>__( 'Smazat vybranou patičku', 'cms_ve' ),
  );
  cms_generate_field_weditor($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'], $content, $pages, 'cms_footer', 'footers', $texts);  
} 

function field_type_weditor($field, $meta, $group_id) {   
  $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
  $pages = get_posts(array('post_type'=>$field['setting']['post_type'],'posts_per_page'=>'1000'));
  $templates=isset($field['setting']['templates'])? $field['setting']['templates'] : array();
  cms_generate_field_weditor($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'], $content,$pages,$field['setting']['post_type'], $templates,$field['setting']['texts']);  
}   


?>
