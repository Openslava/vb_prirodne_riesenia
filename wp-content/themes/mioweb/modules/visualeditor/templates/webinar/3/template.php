<?php 
/**
 * Template Title: Registrace na webinář 3
 * Template Description: Registrace na webinář s popisem a účastníky. 
 */
  __('Registrace na webinář 3','cms_ve');
__('Registrace na webinář s popisem a účastníky.','cms_ve');
global $vePage;

 if ( have_posts() ) while ( have_posts() ) : the_post(); 
	    the_content(); 
        
 endwhile; 
