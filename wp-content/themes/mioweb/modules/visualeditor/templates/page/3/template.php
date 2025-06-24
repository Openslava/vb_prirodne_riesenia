<?php 
/**
 * Template Title: Stránka s nadpisem 1
 * Template Description: Jednoduchá stránka s nadpisem a podnadpisem v horním barevném pruhu ve stylu 1.
 */
__('Stránka s nadpisem 1','cms_ve');
__('Jednoduchá stránka s nadpisem a podnadpisem v horním barevném pruhu ve stylu 1.','cms_ve');
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

