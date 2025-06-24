<?php 
/**
 * Template Title: Dvousloupcová video stránka
 * Template Description: Video stránka s dvousloupcovým obsahem a s menu vedle videa.
 */
 __('Dvousloupcová video stránka','cms_mioweb');
__('Video stránka s dvousloupcovým obsahem a s menu vedle videa.','cms_mioweb');
global $vePage;



?>
    <div id="content-container">  
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	      <?php the_content(); ?>
        <?php endwhile; ?>
        
    </div>
  

<?php
 
?>
