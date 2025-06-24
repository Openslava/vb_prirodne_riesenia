<?php global $vePage; ?>
<div id="footer" class="footer_<?php echo $vePage->footer_setting['appearance']; ?>">
    <div id="footer-in" class="fix_width">
        <?php $vePage->footer_menu($vePage->f_menu); ?>
        <div id="site_copyright"><?php echo (isset($vePage->footer_setting['text']) && $vePage->footer_setting['text'])? stripslashes($vePage->footer_setting['text']): '&copy; '.date('Y').' '.get_bloginfo( 'name' ); ?></div>
        
       <?php if(isset($vePage->f_menu) && $vePage->f_menu) { ?><div class="cms_clear"></div><?php }
       else if($vePage->edit_mode) { ?><div class="cms_clear admin_feature"></div><?php } ?> 
       <?php 
        $aff=get_option('web_option_affiliate');
        if(isset($aff['affiliate_link']) && $aff['affiliate_link']!='') { 
            $aff_link=add_query_arg( 'utm_campaign', 'mioweb_footer', $aff['affiliate_link'] );
            ?>
            <div id="site_poweredby">
                <?php echo __('Vytvořeno na platformě','cms_ve').' <a target="_blank" href="'.$aff_link.'">MioWeb</a>'; ?>
            </div>
        <?php } ?>
    </div>
</div>