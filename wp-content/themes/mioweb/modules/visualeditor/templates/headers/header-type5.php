<?php 
global $vePage;
?>
<div id="header" class="header_in_s2">

    <div id="header_in" class="fix_width header_in_nologo">
        <?php $vePage->header_menu($vePage->h_menu); ?>
        <?php do_action('cms_after_menu');  ?>
    </div>
    
</div> 