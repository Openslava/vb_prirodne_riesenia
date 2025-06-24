<?php 
/**
 * Template Title: Jednoduchá stránka s videem 1
 * Template Description: Stránka obsahující video.
 */
__('Jednoduchá stránka s videem 1','cms_ve');
__('Stránka obsahující video.','cms_ve');
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

