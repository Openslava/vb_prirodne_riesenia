<?php
$shortcodes = New MioWebShortcodes();

class MioWebShortcodes {

    function __construct(){  
        add_action( 'init', array($this,'register_shortcodes'));            
        add_action('wp_ajax_save_shortcode_setting', array($this, 'save_shortcode_setting'));          
    }
    
    function register_shortcodes(){
       global $vePage;
       if($vePage->edit_mode) {
           add_filter( 'mce_external_plugins', array($this, 'mw_add_buttons' ));
           add_filter( 'mce_buttons', array($this, 'mw_register_buttons' ));
           add_filter('mce_external_languages', array($this, 'locale_tinymce_plugin'));
       }
       foreach($vePage->shortcodes as $key => $val) {   
            add_shortcode($key, array($this,'print_shortcode_'.$key));
       }
    }    
    
    function mw_add_buttons( $plugin_array ) {    
        $plugin_array['mwshortcodes'] = get_template_directory_uri() . '/modules/visualeditor/lib/shortcodes/shortcodes.js'; 
        return $plugin_array;
    }  
    function mw_register_buttons( $buttons ) {
        array_push( $buttons, 'mw_addshortcode' ); 
        return $buttons;
    }
    function save_shortcode_setting() {
        global $vePage;
        
        $type=$_POST['element_type'];
        
        $attrs='';
        if(isset($_POST['ve_style']) && is_array($_POST['ve_style'])) {
            foreach($_POST['ve_style'] as $key=>$set) {
                if(is_array($set)) {
                    foreach($set as $subkey=>$subset) {
                        if($subset) $attrs.=' '.$subkey.'=1';
                    }
                }
                else if($set) $attrs.=' '.$key.'='.$set;
            }
        }
        if(isset($vePage->shortcodes[$type]['type']) && $vePage->shortcodes[$type]['type']=='text')
            $text=($_POST['text'])? $_POST['text'] : __('Váš text','cms_ve');
        else $text='';
        
        echo '['.$type.$attrs.']'.$text.'[/'.$type.']';
        
        die();
    }
    
    function locale_tinymce_plugin($locales) {
        $locales ['mwshortcodes'] = plugin_dir_path ( __FILE__ ) . 'shortcode_langs.php';
        return $locales;
    }


    
    // writeshortcodes
    // ************************************************************************************************************
    
    function print_shortcode_popup($atts, $text = null) {
        global $vePage;
        
        extract(shortcode_atts(array(
          'id' => '',
        ), $atts));
        
        $content='';                                         
        
        if($id && get_post($id)) {
            
            $vePage->popups->popups_onpage[$id]=1; 
       
            $content.='<a class="open_text_popup_'.$id.'" href="#">'.$text.'</a>';
            $content.='';
        }
        else $content.=$text;
        return $content;
    }
    
    function print_shortcode_box($atts, $text = null) {
        global $vePage;
        extract(shortcode_atts(array(
          'background' => '',
          'color' => '',
        ), $atts));
        
        if(substr($text, 0, 4)=='</p>') $text=substr($text,4);
        
        $style="";
        if($color) $style.='color:'.$color.';';
        if($background) $style.='background:'.$background.';';
        if($style) $style='style="'.$style.'"';

        $content='<div class="mw_text_box" '.$style.'>'.$text.'</div>';

        return $content;
    }
    
    function print_shortcode_mwvideo($atts) {
        global $vePage;

        extract(shortcode_atts(array(
          'url' => '',
          'autoplay' => 0,
          'showinfo' => 0,
          'hide_control' => 0,
          'rel' => 0,
        ), $atts));
        
        $setting=array();
        if($autoplay) $setting['autoplay']=1;
        if($showinfo) $setting['showinfo']=1;
        if($hide_control) $setting['hide_control']=1;
        if($rel) $setting['rel']=1;
        
        $set=array(
            'content'=>$url,
            'style'=>array(
                'code'=>'',
                'setting'=>$setting
            )
        );
        
        $content=ve_element_video($set, '#shortcode_video');
        
        return $content;
    }
    
    function print_shortcode_content($atts) {
        global $vePage;
        extract(shortcode_atts(array(
          'id' => $atts['id'],
        ), $atts));
        
        $content='';
        if($id) {
            $layer=$vePage->get_layer($id, 've_elvar');  
            $var=$layer[0]['content'][0]['content'];
            $i=0;
            foreach($var as $content_key=>$code) {   
                $shortcode_id='shortcode_'.$i.'_'.$id;
                if(!$vePage->is_mobile || !isset($code['config']['mobile_visibility']))  $content.=$vePage->generate_element($code, $shortcode_id, '', false);
                $i++;
            } 
        }

        return $content;
    }
    
    

   
}
?>
