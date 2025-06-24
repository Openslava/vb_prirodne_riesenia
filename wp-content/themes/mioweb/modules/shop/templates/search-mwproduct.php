<?php
/**
 * Template for product catalog = archive of shop's product custom type.
 */
if (MWS()->inMioweb) {
	get_skin_header('mwshop');
} else {
	get_header('mwshop');
}

the_content();

//$args = array ( 'post_type' => MWS_PRODUCT_SLUG );
//query_posts( $args );
?>

<div class="mws_shop_container">
	<div class="mws_shop_content row_fix_width">
    
    <?php mwsRenderParts('categories'); ?>
    
		<div class="mws_product_list">
      <?php
			mwsRenderParts('product', 'loop');
			?>
		</div>
    
	</div>
</div>

<?php
if (MWS()->inMioweb) {
	get_skin_footer('mwshop');
} else {
	get_header('mwshop');
}
?>
