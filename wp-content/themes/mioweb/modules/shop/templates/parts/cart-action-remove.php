<?php
/**
 * Template part used to generate "REMOVE FROM CART" button.
 * Product ID to be added is stored in "MWS()->current()->productId".
 */

$prodId = MWS()->current()->productId;
if (empty($prodId)) {
	// No product specified.
	return;
}
$url = MWS()->getUrl_CartRemove($prodId);
if (empty($url)) {?>
	<span class="mws_config_error"><?php echo __('Košík není nakonfigurován.', 'mwshop');?></span>
	<?php
	return;
}
?>

<a href="#" class="mws_cart_remove shop-action-remove" title="<?php echo __('Odebrat zboží z košíku', 'mwshop'); ?>"
   data-operation="mws_cart_remove" data-product="<?php echo $prodId;?>" data-count="1"
   data-backurl="<?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];?>"
	>
  <?php echo file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true); ?>
</a>