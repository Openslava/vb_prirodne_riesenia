<?php 
/**
 * Template Title: Stránka s nadpisem a podmenu 2
 * Template Description: Jednoduchá stránka s nadpisem a s menu s podstránkama na levé straně.
 */
__('Stránka s nadpisem a podmenu 2','cms_ve');
__('Jednoduchá stránka s nadpisem a s menu s podstránkama na levé straně.','cms_ve');
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

