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

// $paged=get_query_var( 'paged', 1 ); dont work on home page
$paged=isset($wp_query->query['paged'])? $wp_query->query['paged'] : 1;

$args = array ( 'post_type' => MWS_PRODUCT_SLUG, 'paged' => $paged );
query_posts( $args );


?>

<div class="mws_shop_container">
	<div class="mws_shop_content row_fix_width">
    
    <?php 
    
    mwsRenderParts('categories'); 
		mwsRenderParts('product', 'loop');
	  
    ?>

	</div>
</div>

<?php
if (MWS()->inMioweb) {
	get_skin_footer('mwshop');
} else {
	get_header('mwshop');
}
?>
