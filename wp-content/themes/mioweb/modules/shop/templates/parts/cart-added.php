<?php
/**
 * Template part used to print result of adding an item into the cart.
 * Content of the cart item is stored in "MWS()->current()->cartItem".
 */

$cartItem = MWS()->current()->cartItem;
if(empty($cartItem)) {
  $product = MWS()->current()->product;
  if(empty($product))
    return;
} else
  $product = $cartItem->product;

?>
<table>
	<tr class="mws_cart_item">
		<td class="mws_cart_item_thumb">
        <?php echo $product->getThumbnail(MWS()->thumb_name.'5'); ?>
    </td>
		<td class="mws_cart_item_title">
			<h2><?php echo $product->post->post_title; ?></h2>
			<div class="mws_cart_item_price">
					<?php echo  $product->price->htmlPriceVatIncluded(); ?>
			</div>
			<?php if(MWS()->current()->showAvailabilityInAdded)
				echo $product->htmlAvailabilityMessage();
			?>
	</td>
  </tr>
</table>

