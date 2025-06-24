<?php 
global $vePage, $member_module;

?>
<div id="header">
 <?php if((isset($vePage->header_setting['logo']) && $vePage->header_setting['logo']) || (isset($vePage->header_setting['logo_text']) && $vePage->header_setting['logo_text'] && isset($vePage->header_setting['logo_setting']) && $vePage->header_setting['logo_setting']=='text') || $member_module->member_page || $vePage->h_menu) { ?>
  <div id="header_in" class="fix_width header_in_s1">
    
        <a href="<?php echo $vePage->home_url; ?>" id="site_title" title="<?php echo get_bloginfo('name'); ?>">
            <?php 
            if(isset($vePage->header_setting['logo_setting']) && $vePage->header_setting['logo_setting']=='text') {
                echo $vePage->header_setting['logo_text'];
            } else if($vePage->header_setting['logo']) {
                echo '<img src="'.$vePage->get_image_url($vePage->header_setting['logo']).'" alt="'.get_bloginfo('name').'" />';
            } 
            ?>
        </a>
    
    
    <?php
    do_action('cms_after_menu'); 
    $vePage->header_menu($vePage->h_menu); ?>
    
   <div class="cms_clear"></div> 
   </div>   
<?php } ?>
</div>
