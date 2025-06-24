<?php 
/**
 * Template Title: Vstupní stránka s ebookem
 * Template Description: Vstupní stránka nabízející ebook za kontakt.
 */
  __('Vstupní stránka s ebookem','cms_ve');
__('Vstupní stránka nabízející ebook za kontakt.','cms_ve');
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
<?php
 
?>
