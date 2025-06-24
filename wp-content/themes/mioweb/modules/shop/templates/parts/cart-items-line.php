<?php
/**
 * Template part used in cart loop to print one item within the cart.
 * Content of the cart item is stored in "MWS()->current()->cartItem".
 */

$cartItem = MWS()->current()->cartItem;
if(empty($cartItem))
	return;
$product = $cartItem->product;
//$productId = $product->id();

if(!$product) {
	?>
	<tr class="mws_cart_item mws_product_id-<?php echo $product->id; ?>">
		<td colspan="6"><?php echo sprintf(__('Produkt [%d] se nepodařilo nalézt v naší nabídce.', 'mwshop'), $cartItem->productId); ?></td>
	</tr>
	<?php
} else {
	$countInCart = $cartItem->count;
	$status = $cartItem->availabilityStatus;// $product->getAvailabilityStatus($countInCart);
	$canBuy = $product->canBuy($status);
	?>

	<tr class="mws_cart_item mws_product_id-<?php echo $product->id . ' ' . $product->getAvailabilityCSS($status); ?>">
		<td class="mws_cart_item_thumb"><a
				href="<?php echo $product->detailUrl; ?>"><?php echo $product->getThumbnail(MWS()->thumb_name . '5'); ?></a>
		</td>
		<td class="mws_cart_item_title"><h2><a
					href="<?php echo $product->detailUrl; ?>"><?php echo $product->name; ?></a></h2></td>
		<td class="mws_cart_item_count">
			<div class="mws_count_container">
				<input type="text" name="count[<?php echo $product->id ?>]" class="mws_cart_edit_count title_element_container"
							 value="<?php echo $cartItem->count; ?>" placeholder="?"/>
				<a class="mws_count_reload eshop_color_svg_hover"
					 href="#"><?php echo file_get_contents(MWS()->getTemplateFileDir("img/icons/reload.svg"), true); ?></a>
			</div>
			<?php
	if(!$canBuy) {
		// Product can not be bought in specified amount.
		$errorMsg = $cartItem->availabilityError;
		echo '<span class="mw_input_error_text">' . $errorMsg . '</span>';
	}
		?>
		</td>
		<?php /*
		<td class="mws_cart_item_availability">
			<?php
			$statusMsg = $product->getAvailabilityMessage($status);
			$img = $canBuy ? 'img/available_yes.svg' : 'img/available_no.svg';
			$css = $canBuy ? 'mws_available' : 'mws_unavailable'; //TOTO Or omit availability on row and use it here.
//			echo ''
//			  . '<a class="'.$css.'" title="'.$statusMsg.'">'
//				. file_get_contents(MWS()->getTemplateFileDir($img), true)
//			  . '</a>';
			?>
		</td>
 		*/ ?>
		<td class="mws_cart_item_price">
			<?php
			//Total price
			$price = $cartItem->storedTotalPrice;
			if (is_null($price)) {
				echo __('(neznámo)', 'mwshop');
			} else {
				echo $price->htmlPriceFull('mws_product_price');
				echo '<input type="hidden" name="priceVatIncluded[' . $product->id . ']" value="' . $price->priceVatIncluded . '" />';
			}
			?>
		</td>
		<td class="mws_cart_item_remove">
			<?php
			MWS()->current()->productId = $product->id;
			mwsRenderParts('cart', 'action-remove');
			?>
		</td>
	</tr>
	<?php
}
