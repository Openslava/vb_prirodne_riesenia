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
<div id="content">
    <?php echo $vePage->create_content(); ?>
</div>
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
