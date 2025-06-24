<?php
/**
 * Template part used in cart loop to print header for cart items.
 * Content of the cart is stored in "MWS()->cart".
 */

$cart = MWS()->cart;
if(empty($cart))
	return;
?>
<tr class="mws_cart_items_footer">
	<td colspan="2"><?php echo __('Celkem', 'mwshop'); ?></td>
	<td colspan="3" class="mws_cart_item_price">
    <?php
		$price = $cart->storedTotalPrice;
		$unit = MwsCurrency::getSymbol($price->currency);
		if(!is_null($price)) {
			echo htmlPriceSimpleIncluded($price->priceVatIncluded, $unit);
			echo htmlPriceSimpleExcluded($price->priceVatExcluded, $unit);
		} else {
			echo __('(nutno přepočítat)', 'mwshop');
		}
		?>
	</td>
</tr>



