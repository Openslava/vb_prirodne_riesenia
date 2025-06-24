<?php 
/**
 * Template Title: Stránka kontakty 2
 * Template Description: Stránka s kontaktním formulářem a základními kontakty.
 */

__('Stránka kontakty 2','cms_ve');
__('Stránka s kontaktním formulářem a základními kontakty.','cms_ve');
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

