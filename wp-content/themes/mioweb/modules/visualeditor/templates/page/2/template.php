<?php 
/**
 * Template Title: Prázdná stránka - styl 2
 * Template Description: Prázdná stránka s pozadím po stranách. Vhodná pro výstavbu vlastních stránek.
 */
__('Prázdná stránka - styl 2','cms_ve');
__('Prázdná stránka s pozadím po stranách. Vhodná pro výstavbu vlastních stránek.','cms_ve');
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

