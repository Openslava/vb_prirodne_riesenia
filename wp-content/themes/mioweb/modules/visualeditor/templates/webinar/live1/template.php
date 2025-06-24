<?php 
/**
 * Template Title: Vysílání webináře 1
 * Template Description: Stránka pro živé vysílání webináře.
 */
  __('Vysílání webináře 1','cms_ve');
__('Stránka pro živé vysílání webináře.','cms_ve');
global $vePage;



?>
    <div id="sq-content-container">  
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	      <?php the_content(); ?>
        <?php endwhile; ?>
        
    </div>
  

<?php
 
?>
