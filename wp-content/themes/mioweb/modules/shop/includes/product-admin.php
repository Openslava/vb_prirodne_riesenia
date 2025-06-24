<?php
/**
 * Support for administration of products.
 * User: kuba
 * Date: 02.06.16
 * Time: 10:32
 */


add_action('add_meta_boxes', 'mwsProduct_AddMetaboxes');
//add_action('save_post', 'mwsProduct_SaveMetaboxes');
//add_filter('the_posts', 'mwsProduct_filterPosts', 10, 2);


function mwsProduct_AddMetaboxes() {
//	add_meta_box('mwsProduct_general', __( 'Základní informace', 'mwshop'), 'mwsProduct_renderBoxGeneral', MWS_ORDER_SLUG);
	if (defined('MW_SHOW_DEBUGS') && MW_SHOW_DEBUGS) {
		add_meta_box('mwsProduct_debug', 'Devel and debug', 'mwsProduct_renderBoxDebug', MWS_PRODUCT_SLUG);
		add_meta_box('mwsVariant_debug', 'Devel and debug', 'mwsVariant_renderBoxDebug', MWS_VARIANT_SLUG);
	}
}

function mwsProduct_renderBoxGeneral($post, $metabox) {
//	$product = MwsProduct::createNew($post);
}

function mwsProduct_renderBoxDebug($post, $metabox) {
	$product = MwsProduct::getById($post->ID);
	echo '<h3>Product</h3>';
	mwsDebugProduct($product);
}

function mwsProduct_saveMetaboxes() {}

function mwsProduct_filterPosts($posts, $wpQuery) {
}

function mwsVariant_renderBoxDebug($post, $metabox) {
	$product = MwsProductVariant::getById($post->ID);
	echo '<h3>Variant of product</h3>';
	mwsDebugProduct($product);
}

/**
 * @param MwsProduct $product
 */
function mwsDebugProduct($product) {
	if(!$product) {
		echo 'no product instance';
		return;
	}

	$print = function($title, $value) {
		$value = var_export($value, true);
		echo "<div><strong>$title</strong>: $value</div>";
	};
	$printObj = function($title, $obj) {
		echo "<div><strong>$title</strong>:"
			. (!empty($obj) ? '<pre>'.esc_html(print_r($obj, true)).'</pre>' : ' empty')
			. "</div>";
	};
	$printSpace = function() {
		echo '<hr />';
	};

	try {
		$print('Title', $product->name);
		$print('Detail URL', $product->detailUrl);
		$print('Availability', $product->getAvailabilityMessage());
		$print('Availability CSS', $product->getAvailabilityCSS());

		$printSpace();
		$price = $product->price;
		if (!empty($price)) {
			$print('Price', $price->htmlPriceVatIncluded() . ' [' . $price->htmlPriceVatExcluded() . '] ' . $price->vatPercentage*100 . '%');
		} else {
			$print('Price', $price);
		}
		$print('Is discounted now', $product->isDiscountedNow);
		if ($product->isDiscountedNow) {
			$print('Discount percentage', $product->discountPercentage);
			$priceFull = $product->priceFull;
			if (!empty($priceFull)) {
				$print('Full price', $priceFull->htmlPriceVatIncluded());
			}
		}

		$printSpace();
		$print('Stock enabled', $product->stockEnabled);
		if ($product->stockEnabled) {
			$print('Stock allow backorders', $product->stockAllowBackorders);
			$print('Stock count', $product->stockCount);
		}
	} catch (Exception $e) {
		echo '<div class="mws_admin_error">';
		$print('CHYBA při čtení vlastností', $e->getMessage());
		echo '</div>';
	}

	$printSpace();
//	$printObj('Sync', $product->sync);
	$printObj('Product data', $product);
}
