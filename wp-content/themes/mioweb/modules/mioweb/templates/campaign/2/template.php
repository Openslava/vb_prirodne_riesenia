<?php 
/**
 * Template Title: Jednoduchá video stránka
 * Template Description: Video stránka s menu nad videem.
 */
 __('Jednoduchá video stránka','cms_mioweb');
__('Video stránka s menu nad videem.','cms_mioweb');
global $vePage;



?>
    <div id="content-container">  
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	      <?php the_content(); ?>
        <?php endwhile; ?>
        
    </div>
  

<?php
 
?>
