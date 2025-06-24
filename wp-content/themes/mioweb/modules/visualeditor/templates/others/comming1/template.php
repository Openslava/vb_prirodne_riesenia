<?php 
/**
 * Template Title: Web se připravuje
 * Template Description: Informační stránka o tom že se web připravuje.
 */
__('Web se připravuje','cms_ve');
__('Informační stránka o tom že se web připravuje.','cms_ve');
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

