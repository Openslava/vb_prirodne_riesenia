<?php 
/**
 * Template Title: Seznam lekcí
 * Template Description: Stránka s výpisem podstránek jako seznamem lekcí.
 */
 __('Seznam lekcí','cms_member');
__('Stránka s výpisem podstránek jako seznamem lekcí.','cms_member');
?>
<div id="content-container">   
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	      <?php the_content(); ?>
        <?php endwhile; ?>        
</div>

