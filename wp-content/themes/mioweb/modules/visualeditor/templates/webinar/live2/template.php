<?php 
/**
 * Template Title: Vysílání webináře 2
 * Template Description: Stránka pro živé vysílání webináře. 
 */
  __('Vysílání webináře 2','cms_ve');
__('Stránka pro živé vysílání webináře.','cms_ve');
global $vePage;

 if ( have_posts() ) while ( have_posts() ) : the_post(); 
	    the_content(); 
        
 endwhile; 
