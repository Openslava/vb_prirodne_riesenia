<?php 
/**
 * Template Title:Stránka lekce 1
 * Template Description:Stránka lekce s výpisem dalších lekcí na levé straně.
 */
 __('Stránka lekce 1','cms_member');
__('Stránka lekce s výpisem dalších lekcí na levé straně.','cms_member');
?>
<div id="content-container">   
        <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
	      <?php the_content(); ?>
        <?php endwhile; ?>        
</div>
