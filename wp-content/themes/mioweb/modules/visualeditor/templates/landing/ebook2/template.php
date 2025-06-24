<?php 
/**
 * Template Title: Prodej ebooku nebo knihy
 * Template Description: Stránka zaměřená na prodej ebooku nebo knihy. Obsahuje informace o obsahu, možnost stažení kapitoly zdarma, reference a informace o autorovi.
 */
__('Prodej ebooku nebo knihy','cms_ve');
__('Stránka zaměřená na prodej ebooku nebo knihy. Obsahuje informace o obsahu, možnost stažení kapitoly zdarma, reference a informace o autorovi.','cms_ve');
global $vePage;

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
