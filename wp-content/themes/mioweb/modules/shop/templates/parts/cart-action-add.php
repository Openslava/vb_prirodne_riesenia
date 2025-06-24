<?php
/**
 * Template part used to generate "ADD TO CART" form with the count edit field.
 * Product ID to be added is stored in "MWS()->current()->productId".
 */

$product = MWS()->current()->product;
if (empty($product)) {
	// No product specified.
	return;
}
$url = MWS()->getUrl_CartAdd($product->id);
if (empty($url)) { ?>
	<span class="mws_config_error" xmlns="http://www.w3.org/1999/html"><?php echo __('Košík není nakonfigurován.', 'mwshop');?></span>
	<?php
	return;
}

$status = $product->getAvailabilityStatus(1);
$canBuy = $product->canBuy($status);
$isVariantRoot = $product->structure === MwsProductStructureType::Variants;

if($isVariantRoot) {
	wp_enqueue_script('ve_lightbox_script');
	wp_enqueue_style('ve_lightbox_style');
}

echo '<div class="mws_add_to_cart_part">';
if($canBuy) {
	?>
	<a href="#" class="add_tocart_button ve_content_button ve_content_button_1 shop-action"
	 title="<?php echo __('Vložit zboží do košíku', 'mwshop'); ?>"
	 data-operation="mws_cart_add"
	 <?php echo ($isVariantRoot ? 'data-variant-product' : 'data-product') . '="'.$product->id.'"';?>
	 data-count="1"
	 data-backurl="<?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>"
	>
	<?php
	?>
		<span class="ve_but_icon"><?php echo file_get_contents(MWS()->getTemplateFileDir("img/cart.svg"), true); ?></span>
		<span class="ve_but_text"><?php echo esc_html($product->getBuyButtonText($status)); ?></span>
	</a>
	<?php
	if($isVariantRoot) {
		$variantPricesAreEqual = $product->variantPricesAreEqual;

		echo '<div class="mws_variant_list_container">';
    /*
		echo '	<a href="#" class="mws_dropdown_button ve_content_button ve_content_button_1 add_tocart_button" title="'
			. __('Zvolte variantu', 'mwshop').'">'
			.'v' //TODO Use dropdown image?
			.'</a>';   */
		$varProduct = MwsProductRoot::getById($product->id);
		echo '<div class="mws_variant_list_content"'
			. ' data-all-availability-css="'.esc_attr(implode(' ', MwsProductAvailabilityStatus::getAllCSSArray())).'"'
			.'>';

    echo '<div class="mws_add_to_cart_header mws_variant_list_header">Vybrat variantu pro <strong>'.$product->post->post_title.'</strong>
								<a href="#" class="mws_close_cart_box">' . file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true) . '</a>
						</div>';
		/** @var MwsProductVariant $variant */
		foreach ($varProduct->variants as $variant) {
			$count = 1;
			$availability = $variant->getAvailabilityStatus($count);
			if(!$variant->isVisible($availability)) {
				continue;
			}
			$css = $variant->getAvailabilityCSS($availability);
			echo '<a href="#" class="shop-variant-select shop-action '.$css.'"'
				. ($variant->canBuy() ? ' data-product="'.$variant->id.'"' : '')
        . ' data-operation="mws_cart_add"'
        . ' data-count="1"'
        . ' data-backurl="'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"].'"'
        /*
				. ' data-msg-buy-button="'.esc_attr(esc_html($variant->getBuyButtonText($availability))).'"'
				. ' data-msg-availability="'.esc_attr($variant->htmlAvailabilityMessage($availability)).'"'
				. ' data-availability-css="'.esc_attr($css).'"'
				. ' data-msg-price="'.esc_attr($variant->htmlPriceSaleFull()).'"'
				. ' data-msg-sale="'.esc_attr($variant->htmlPriceSaleFull(null,$count,array('vatExcluded','vatIncluded','salePrice'))).'"'
        */
				. '>';
        ?>
        <table class="mws_variant_list_item">
        	<tr>
        		<td class="mws_variant_list_item_thumb">
                <?php echo $variant->getThumbnail(MWS()->thumb_name.'5'); ?>
            </td>
        		<td class="mws_variant_list_info">
        			<?php
							/** @var MwsPropertyValue $variant_value */
							foreach($variant->variantVals as $variant_value) {
                  echo '<div class="mw_variant_info">';
                  echo '<span class="mw_variant_info_name">'.$variant_value->propertyDef->name.'</span>';
                  echo '<span class="mw_variant_info_value">'.$variant_value->name.'</span>';
                  echo '</div>';   
              }
              ?>
        	  </td>
            <td class="mws_variant_list_price">
        			<?php
              echo '<div class="mws_product_price">'.$variant->htmlPriceSaleFull(null,1,array('vatExcluded')).'</div>';
				      echo $variant->htmlAvailabilityMessage($availability);
        			?>
        	  </td>
          </tr>
        </table>
        
        <span class="ve_but_icon"></span>
        <?php
				echo '</a>';
		}
		echo '</div>';
		echo '</div>';
	}
} else {
	// Can not be bought
	?>
	<a class="add_tocart_button ve_content_button ve_content_button_1"
		 title="<?php echo __('Zboží není dostupné', 'mwshop'); ?>"
	>
		<span class="ve_but_icon"><?php echo file_get_contents(MWS()->getTemplateFileDir("img/cart.svg"), true); ?></span>
		<?php echo esc_html($product->getBuyButtonText($status)); ?>
	</a>
<?php
}
echo '</div>'; // div.mws_add_to_cart_part
