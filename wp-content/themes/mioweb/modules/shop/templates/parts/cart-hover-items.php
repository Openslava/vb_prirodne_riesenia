<?php
/**
 * Template part used in cart info in header.
 * Content of the cart item is stored in "MWS()->current()->cartItem".
 */

$cartItem = MWS()->current()->cartItem;
if(empty($cartItem))
	return;

$product = $cartItem->product;
echo '<tr id="mws_product_id-'.$product->id.'">'; //mws_product_item_'.$product->id.'
echo '<td class="mws_product_thumb"><a href="'.($product->detailUrl).'">'.$product->getThumbnail(MWS()->thumb_name.'5').'</a></td>';
echo '<td class="mws_cart_item_title"><a href="'.($product->detailUrl).'">'.$cartItem->count.' x '.$product->name.'</a></td>';
echo '<td class="mws_cart_item_price">'.$product->price->htmlPriceVatIncluded(null,$cartItem->count).'</td>';
echo '</tr>';  