<?php
/**
 * Template for product catalog = archive of shop's product custom type.
 */
if (MWS()->inMioweb) {
	get_skin_header('mwshop');
	get_blog_sidebar('mwshop');
} else {
	get_header('mwshop');
}
?>

<div class="mws_shop_container">
	<div class="mws_shop_content">
		<h1>Shop window</h1>

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
