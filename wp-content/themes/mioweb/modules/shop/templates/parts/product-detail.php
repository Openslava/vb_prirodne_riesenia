<?php
/**
 * Template part used in product detail page ane product detail element. 
*/
global $vePage, $cms;

/** @var MwsProduct $product */
$product = MWS()->current()->product;

//TODO Nonexisting product scenario?
if(!$product) {
	return;
}

wp_enqueue_script( 've_lightbox_script' );
wp_enqueue_style( 've_lightbox_style' );


$thumb=(has_post_thumbnail($product->id))? true : false;
$full_image=wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'large' );
$count = 1;
$availability = $product->getAvailabilityStatus($count);
?>
<div class="mws_product mws_single_product mws_product-<?php echo $product->post->ID
				. ' ' .$product->getAvailabilityCSS($availability); ?>">
        <div class="mws_thumb col col-2">
    			 <a href="<?php echo $full_image[0]; ?>" class="thumb open_lightbox responsive_image" rel="mws_product_gallery"><?php echo $product->getThumbnail(MWS()->thumb_name.'2');?></a>
           <div class="mws_product_sale"><?php echo $product->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice')); ?></div>
           <?php 
           if($product->gallery && isset($product->gallery['gallery'])) {
              $gal_rows = array_chunk( $product->gallery['gallery'], 3 );
              $gallery_slider=false;
              if(count($gal_rows)>1) {
                  $gallery_slider=true;   
                  wp_enqueue_script( 've_miocarousel_script' );
                  wp_enqueue_style( 've_miocarousel_style' );
              }
              
              echo '<div class="mws_product_image_gallery">';
              if($gallery_slider) {
                  echo '<div class="miocarousel miocarousel_style_2" data-autoplay="0" data-animation="slide" data-indicators="0">';
                  echo '<div class="miocarousel-inner">';
              }
              foreach($gal_rows as $gal_row) {
                  echo '<div class="'.(($gallery_slider)?'slide':'').'">';
                  foreach($gal_row as $gal_image) {
                      $target = wp_get_attachment_image_src( $gal_image, 'large' );                      
                      echo '<a class="open_lightbox col responsive_image" href="'.$target[0].'" rel="mws_product_gallery">'.wp_get_attachment_image( $gal_image, MWS()->thumb_name.'5' ).'</a>';
                  }
                  echo '<div class="cms_clear"></div></div>'; 
              } 
              if($gallery_slider) {
                  echo '</div>';
                  echo '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
                  echo '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
                  echo '</div>';
              }
              echo '</div>';  
           } 
           ?>
    		</div>
        <div class="col col-2">
        		<h2 class="mws_product_title"><?php echo get_the_title($product->post->ID);?></h2>
            <?php echo $vePage->mw_breadcrumbs('/',MWS_PRODUCT_CAT_SLUG); ?>
        		<?php if($product->post->post_excerpt) { ?>
                <p class="mws_product_excerpt"><?php echo $product->post->post_excerpt; ?></p>
            <?php } 
            if($product->showSocial) { ?>
            <div class="mws_product_socials">
                <div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
                <div class="g-like"><div class="g-plusone" data-size="medium"></div></div>
                <script src="https://apis.google.com/js/platform.js" async defer>
                  {lang: 'cs'}
                </script>
            </div>
            <?php } ?>
        		<div class="mws_product_prices"><?php echo $product->htmlPriceSaleFull(null, $count); ?></div>
        		<div class="mws_product_tocart">
                <?php 
                mwsRenderParts('cart', 'action-add'); 
                echo $product->htmlAvailabilityMessage($product->getAvailabilityStatus($count)); 
                ?>
            </div>

            <?php   /*
            $orderedCount = $product->orderedCount;
            if($orderedCount) echo '<div class="mws_product_ordered_count">'.sprintf(__('Zakoupeno: %d×'), $orderedCount).'</div>';    */
							
            ?>
        </div>
        <div class="cms_clear"></div>
    </div>
