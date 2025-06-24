<?php
/**
 * Template part used in cart loop. It shows items within cart.
 * Content of the cart is present in variable MWS()->cart.
 */

$cart = MWS()->cart;


echo '<div class="mws_cart_empty_info">' . __('Košík je prázdný','mwshop') . '</div>';
if(!$cart->items->isEmpty()) {
	if(!$cart->isRecounted) {
		echo '<div class="mws_error">';
		echo empty($cart->recountError)
			? __('Omlouváme se, nepodařilo se spočítat cenu košíku. Proveďte přepočítání.', 'mwshop')
			: $cart->recountError;
		if($cart->availabilityErrorsCount)
			echo ' ' . __('Upravte obsah košíku.', 'mwshop');
		if(MWS()->edit_mode && !empty($cart->recountAdminError))
			echo '<small>'.__('Technický popis chyby:','mwshop').' <span>'.$cart->recountAdminError.'</span></small>';
		echo '</div>';
	}
	echo '<table class="mws_cart mws_cart_filled">';
	/** @var MwsCartItem $cartItem */
	foreach ($cart->items->data as $cartItem) {
		MWS()->current()->cartItem = $cartItem;
		mwsRenderParts('cart','items-line');
	}
	mwsRenderParts('cart','items-footer');
	echo '</table>';
}




