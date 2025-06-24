<?php 
/**
 * Template Title: Stáhnutí ebooku za kontakt
 * Template Description: Stránka nabízející ebook ke stažení zdarma výměnou za kontakt.
 */
__('Stáhnutí ebooku za kontakt','cms_ve');
__('Stránka nabízející ebook ke stažení zdarma výměnou za kontakt.','cms_ve');
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
