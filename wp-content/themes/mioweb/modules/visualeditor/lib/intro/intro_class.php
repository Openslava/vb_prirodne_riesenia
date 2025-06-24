<?php   

class introTutorials {
var $option;
  
function __construct(){
  add_action( 'wp_enqueue_scripts', array($this, 'load_scripts')) ;      
  add_action( 'wp_footer', array($this, 'footer')) ;  
  add_action('wp_ajax_intro_save_tutorial',  array($this, 'save_tutorial'));
  
  $this->option=get_option('cms_intro_tutorials');  
}
function footer() {   
  if(!isset($this->option['tutorials']['start_tutorial'])) { 
    $this->first_tutorial();
  }
}
function save_tutorial() {
  $options=get_option('cms_intro_tutorials');  
  $options['tutorials'][$_POST['id']]=1;
  update_option('cms_intro_tutorials',$options);
}
function load_scripts() {
  wp_register_script( 've_intro_script',get_bloginfo('template_url').'/modules/visualeditor/includes/intro/intro.js',array('jquery'));
  wp_register_script( 've_intro_tutorials',get_bloginfo('template_url').'/modules/visualeditor/lib/intro/intro.js',array('jquery'));
  wp_register_style( 've_intro_style',get_bloginfo('template_url').'/modules/visualeditor/includes/intro/intro.css' );
  
  wp_enqueue_script( 've_intro_script' );
  wp_enqueue_script( 've_intro_tutorials' );
  wp_enqueue_style('ve_intro_style');
  
  wp_localize_script( 've_intro_tutorials', 'ajaxurl', admin_url( 'admin-ajax.php' ));
}

function first_tutorial() {
    global $cms;
    if($cms->valid_license() && get_option('ve_installed_web')) {
    ?>
    <script type="text/javascript">      
      jQuery(document).ready(function($) {
          startIntroTut('start');
      });
      <?php include __DIR__.'/intros/start.php'; ?>
    </script>
    <?php
    }
}

}

?>