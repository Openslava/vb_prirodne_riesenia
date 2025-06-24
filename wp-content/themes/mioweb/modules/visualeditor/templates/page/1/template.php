<?php 
/**
 * Template Title:Prázdná stránka - styl 1
 * Template Description: Prázdná stránka bez okrajů, vhodná pro výstavbu vlastních stránek.
 */
 __('Prázdná stránka - styl 1','cms_ve');
 __('Prázdná stránka bez okrajů, vhodná pro výstavbu vlastních stránek.','cms_ve');
?>
<div id="content-container">   
       <?php 
        if ( have_posts() ) {
            while ( have_posts() ) { 
                the_post(); 
                the_content();
            }
        }
        ?>
</div>

