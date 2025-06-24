<?php
/**
 * Template showing the cart's content. Cart is accessible through "MWS()->cart".
 */
if (MWS()->inMioweb) {
	get_skin_header('mwshop');
	get_blog_sidebar('mwshop');
} else {
	get_header('mwshop');
}

$cart = MWS()->cart;
$cartItemsCount = $cart->items->count();
//Update first step fulfillment every time.
$cart->setFulfilledStep(MwsOrderStep::Cart, ($cartItemsCount>0));

// Use template according to selected step
$step = (isset($_REQUEST['step'])) ? (int)$_REQUEST['step'] : '';
$step = MwsOrderStep::checkedValue($step, MwsOrderStep::Cart);

$isQuick = (isset($_REQUEST['isQuick'])) ? (bool)$_REQUEST['isQuick'] : false;
$fulfilment = $cart->getStepsFulfillment();
mwshoplog('step=' . $step . ' fullfilled=['
  . implode(',', array_map(function($key, $item) {return ($item ? $key : '');}, array_keys($fulfilment), $fulfilment))
  .']', MWLL_DEBUG, 'order');
//mwshoplog('fulfillment='.print_r($fulfilment, true), MWLL_DEBUG, 'order');

//We request nonempty cart for further processing of order. Otherwise keep up with cart content only.
if($step==MwsOrderStep::ThankYou) {
    // Thank you page only when "success" argument is present.
    if (isset($_REQUEST['success'])) {
        $success = (bool)filter_var($_REQUEST['success'], FILTER_VALIDATE_BOOLEAN);
    } else {
        $step = MwsOrderStep::Cart;
    }
} elseif($cartItemsCount==0) {
    $step = MwsOrderStep::Cart;
} elseif (! $cart->areFulfilledPriorSteps($step)) {
    // Incorrect direct jump to following step without previous step is fulfilled.
	$step = MwsOrderStep::Cart;
}

MWS()->current()->orderStep = $step;
$currency = $cart->getCurrency();
$unit = MwsCurrency::getSymbol($currency);

?>

<div class="mws_shop_container">
<?php
	$steps = MwsOrderStep::getAll();
	if(($key = array_search(MwsOrderStep::ThankYou, $steps)) !== false) {
		unset($steps[$key]);
	}
	?>

	<!-- NAVIGATION -->
	<?php
	$icons = MwsOrderStep::getIcons();
	//get icon for step ----> $icon = $icons[$step];
	?>

	<div class="mws_cart_navigation <?php if($step>3) echo 'eshop_color_background'; ?>">
		<div class="mws_cart_navigation_in title_element_container row_fix_width">
			<?php
			foreach($steps as $sid) {
                $icon=$icons[$sid];

				$isStepFulfilled = $cart->areFulfilledPriorSteps($sid);
				$class='mws_cart_step_item mws_cart_step_item_s'.$sid;
				if($step==$sid)
					$class.=' eshop_color_background mws_cart_step_item_a';
				else if($step>$sid) {
                    $class.=' eshop_color_background mws_cart_step_item_f';
                    $icon='step_ok';
                }
				$step_icon='<span class="icon">'.file_get_contents(MWS()->getTemplateFileDir("img/".$icon.".svg"), true).'</span>';
				$class .= ($isStepFulfilled ? ' mws_order_step_fullfilled' : ' mws_order_step_pending');
				if($step==$sid || !$cart->areFulfilledPriorSteps($sid))
					echo '<div class="'.$class.'">'.$step_icon.'<span class="text">'.MwsOrderStep::getCaption($sid).'</span><span class="arrow"></span></div>';
				else
					echo '<a href="'.MWS()->getUrl_Cart($sid).'" class="'.$class.'">'.$step_icon.'<span class="text">'.MwsOrderStep::getCaption($sid).'</span></a>';
			}
			?>
			<div class="cms_clear"></div>
		</div>
	</div>

	<!-- JAVA SCRIPTs -->
	<script>
		// MWS Order info
		/* Global variables */
		var orderStep=<?php echo $step;?>;
		var orderUrl="<?php echo htmlspecialchars("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");?>";
		var textError_AjaxError="<?php echo __('Komunikace se serverem se nezdařila. Prosím opakujte požadavek později.', 'mwshop'); ?>";
		var stepsFullfilment = JSON.parse('<?php echo json_encode($fulfilment) ?>');
		console.log('fulfillment = ' + stepsFullfilment);
	</script>

	<!-- CONTENT -->
	<div class="mws_shop_content mws_shop_order_content row_fix_width">
		<!-- FLASH MESSAGES LOCATION -->
		<div id="mws_flash_messages"></div>
		<!-- STEP CONTENT -->
		<?php if($step == MwsOrderStep::Cart) { ?>
			<div class="mws_cart_container <?php if($cart->items->isEmpty()) echo 'mws_cart_empty'; ?>">
				<form id="mws_order_form">
					<?php
					$cart = MWS()->cart;
					$cart->recount(false, true, 'default', true);
					mwsRenderParts('cart', 'loop');
					?>
				</form>
			</div>

			<div class="mws_order_footer title_element_container">
				<a class="mws_cart_back_but" href="<?php echo MWS()->getUrl_Home(); ?>"><?php echo __('Zpět k nákupu','mwshop'); ?></a>
				<?php
				if($cartItemsCount>0) {
					if($cart->isRecounted) {
					?>
					<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
						 href="<?php echo MWS()->getUrl_Cart($step+1); ?>"><?php echo __('Pokračovat','mwshop'); ?></a>
					<?php } else { ?>
						<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
							 href="<?php echo MWS()->getUrl_Cart($step); ?>"><?php echo __('Přepočítat','mwshop'); ?></a>
				<?php
					}
				} ?>
				<div class="cms_clear"></div>

			</div>
      <?php
    }
		elseif($step==MwsOrderStep::Contact) { ?>
			<div class="mws_contact">
				<form class="ve_content_form ve_form_style_1 ve_form_input_style_2" id="mws_order_form">
                    <?php
                    global $cms;
                    $fields = $cms->p_set[MWS_FIELDSET_ORDER_CONTACT][0]['fields'];
                    $meta = $cart->contact;

                    $countries = MWS()->getSupportedCountries();
                    reset($countries);
                    $defCountry = key($countries);
                    if (empty($defCountry)) {
                        $defCountry = 'CZ';
                    }
                    $country = isset($meta['address']['country']) ? $meta['address']['country'] : '';
                    if (empty($country)) {
                        $country = $defCountry;
                    }

                    /** @var bool $canSimplified Can be simplified invoice used? */
                    $canSimplified = MWS()->gateways()->getDefault()->getUseSimplifiedInvoice();
                    /** @var bool $shippingNeeded Is shipping required? */
                    $shippingNeeded = true;
                    /** @var bool $mustHaveInvoice Is invoice required? */
                    $mustHaveInvoice = ! ($canSimplified
                      && $cart->storedTotalPrice && $cart->storedTotalPrice->priceVatIncluded <= 10000
                      && $country === 'CZ');
                    if ($mustHaveInvoice) {
                        $wantInvoiceChecked = true;
                    } else {
                        $wantInvoiceChecked = isset($meta['want_invoice']) && $meta['want_invoice'];
                    }
                    ?>

                    <input type="hidden" name="order_contact[totalPrice]" value="<?php
                        echo ($cart->storedTotalPrice ? $cart->storedTotalPrice->priceVatIncluded : 0.1) ?>">

                    <h2><label for="order_contact_email"><?php echo __('E-mail', 'mwshop'); ?></label></h2>
                    <div class="ve_form_row">
                        <input class="ve_form_text" type="text" name="order_contact[email]"
                               value="<?php echo $meta['email'] ?>" id="order_contact_email"/>
                    </div>
                    <?php if (!$mustHaveInvoice) { // COUNTRY for simplified invoice is in front
                        ?>
                        <h2><label for="order_contact_country"><?php echo __('Země', 'mwshop'); ?></label></h2>
                        <div class="ve_form_row">
                            <?php mws_generate_country_select('order_contact[address][country]', 'order_contact_country', 've_form_text', $country) ?>
                        </div>
                    <?php } ?>

                    <script>
                        var cartTotalPrice = <?php echo ($cart->storedTotalPrice ? $cart->storedTotalPrice->priceVatIncluded : 0) ?>;
                        var canSimplifiedInvoice = <?php echo $canSimplified ? 'true' : 'false' ?>;

                        jQuery(document).ready(function ($) {
                            // Hook for country selection change.
                            $('#order_contact_country').change(function () {
                                var elem = $(this);
                                var country = elem.attr("value");
                                var elSkVatId = $('#order_contact_company_sk_vat_id');
                                if (elSkVatId.length) {
                                    elSkVatId = elSkVatId.parent(); // use parental wrapper element
                                    if (country === 'SK')
                                        elSkVatId.removeClass('cms_nodisp');
                                    else
                                        elSkVatId.addClass('cms_nodisp');
                                }
                                if (canSimplifiedInvoice) {
                                    if(cartTotalPrice <= 10000 && country === 'CZ') {
                                        $('#order_contact_want_invoice_group').show();
                                        $('#order_contact_want_invoice').removeAttr('onclick');
                                    } else {
                                        $('#order_contact_want_invoice_group').hide();
                                        $('#order_contact_want_invoice')
                                            .attr('onclick', 'return false')
                                            .prop('checked', true)
                                        ;
                                        $('#order_contact_invoice_container').show();
                                    }
                                }
                            }).change();
                        });
                    </script>

                    <?php if ($canSimplified) { ?>
                        <h2 id="order_contact_want_invoice_group" <?php if ($mustHaveInvoice) echo 'class="cms_nodisp"'; ?>>
                            <input class="mw_toggle_container"
                                   data-target="order_contact_invoice_container"
                                   type="checkbox"
                                   name="order_contact[want_invoice]" id="order_contact_want_invoice"
                                   <?php
                                   if ($wantInvoiceChecked) echo ' checked="checked" ';
                                   if ($mustHaveInvoice) echo ' onclick="return false" ';
                                   ?>
                            >
                            <label for="order_contact_want_invoice"><?php echo __('Potřebuji vystavit fakturu', 'mwshop'); ?></label>
                        </h2>
                    <?php } else { ?>
                        <input type="hidden" name="order_contact[want_invoice]" value="on">
                    <?php } ?>

                    <div id="order_contact_invoice_container" <?php echo (!$wantInvoiceChecked ? 'class="cms_nodisp"' : ''); ?>>
                        <h2><?php echo __('Fakturační údaje', 'mwshop'); ?></h2>
                        <div class="ve_form_row ve_form_row_half">
                            <label for="order_contact_firstname"><?php echo __('Jméno', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[address][firstname]"
                                   value="<?php echo $meta['address']['firstname'] ?>" id="order_contact_firstname"/>
                        </div>
                        <div class="ve_form_row ve_form_row_half ve_form_row_half_r">
                            <label for="order_contact_surname"><?php echo __('Příjmení', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[address][surname]"
                                   value="<?php echo $meta['address']['surname'] ?>" id="order_contact_surname"/>
                        </div>
                        <div class="cms_clear"></div>
                        <div class="ve_form_row">
                            <label for="order_contact_phone"><?php echo __('Telefon', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[address][phone]"
                                   value="<?php echo $meta['address']['phone'] ?>" id="order_contact_phone"/>
                        </div>
                        <div class="ve_form_row">
                            <label for="order_contact_street"><?php echo __('Ulice a číslo popisné', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[address][street]"
                                   value="<?php echo $meta['address']['street'] ?>" id="order_contact_street"/>
                        </div>
                        <div class="ve_form_row ve_form_row_half">
                            <label for="order_contact_city"><?php echo __('Město', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[address][city]"
                                   value="<?php echo $meta['address']['city'] ?>" id="order_contact_city"/>
                        </div>
                        <div class="ve_form_row ve_form_row_half ve_form_row_half_r">
                            <label for="order_contact_zip"><?php echo __('PSČ', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[address][zip]"
                                   value="<?php echo $meta['address']['zip'] ?>" id="order_contact_zip"/>
                        </div>
                        <?php if ($mustHaveInvoice) { // COUNTRY for regular invoice is within invoice attributes
                            ?>
                            <label for="order_contact_country"><?php echo __('Země', 'mwshop'); ?></label>
                            <div class="ve_form_row">
                            <?php mws_generate_country_select('order_contact[address][country]', 'order_contact_country', 've_form_text', $country) ?>
                            </div>
                        <?php } ?>
                        <div class="cms_clear"></div>

                        <h2>
                            <input class="mw_toggle_container" data-target="order_contact_company_container"
                                   type="checkbox"
                                   name="order_contact[is_company]" id="order_contact_is_company"
                                    <?php if (isset($meta['is_company']) && $meta['is_company']) echo ' checked="checked" '; ?>
                            >
                            <label for="order_contact_is_company"><?php echo __('Nakupuji na firmu', 'mwshop'); ?></label>
                        </h2>
                        <div id="order_contact_company_container" <?php if (!(isset($meta['is_company']) && $meta['is_company'])) echo ' class="cms_nodisp" '; ?>>
                            <div class="ve_form_row">
                                <label for="order_contact_company_name"><?php echo __('Název společnosti', 'mwshop'); ?></label>
                                <input class="ve_form_text" type="text" name="order_contact[company_info][company_name]"
                                       value="<?php echo $meta['company_info']['company_name'] ?>"
                                       id="order_contact_company_name"/>
                            </div>
                            <div class="ve_form_row ve_form_half">
                                <label for="order_contact_company_id"><?php echo __('IČ', 'mwshop'); ?></label>
                                <input class="ve_form_text" type="text" name="order_contact[company_info][company_id]"
                                       value="<?php echo $meta['company_info']['company_id'] ?>"
                                       id="order_contact_company_id"/>
                            </div>
                            <div class="ve_form_row ve_form_half ve_form_half_r">
                                <label for="order_contact_company_vat_id"><?php echo __('DIČ', 'mwshop'); ?></label>
                                <input class="ve_form_text" type="text" name="order_contact[company_info][company_vat_id]"
                                       value="<?php echo $meta['company_info']['company_vat_id'] ?>"
                                       id="order_contact_company_vat_id"/>
                            </div>
                            <div class="ve_form_row ve_form_half ve_form_half_r">
                                <label for="order_contact_company_sk_vat_id"><?php echo __('IČ DPH', 'mwshop'); ?></label>
                                <input class="ve_form_text" type="text"
                                       name="order_contact[company_info][company_sk_vat_id]"
                                       value="<?php echo isset($meta['company_info']['company_sk_vat_id']) ? $meta['company_info']['company_sk_vat_id'] : '' ?>"
                                       id="order_contact_company_sk_vat_id"/>
                            </div>
                        </div>
                    </div>

                    <h2 <?php if (!$shippingNeeded) echo 'class="cms_nodisp"' ?>>
                        <input class="mw_toggle_container" data-target="order_contact_shipping_container"
                            type="checkbox" name="order_contact[has_shipping_addr]" id="order_contact_has_shipping_addr"
                            <?php if (isset($meta['has_shipping_addr']) && $meta['has_shipping_addr']) echo ' checked="checked" '; ?>
                        >
                        <label for="order_contact_has_shipping_addr"><?php echo __('Doručit na jinou než fakturační adresu', 'mwshop'); ?></label>
                    </h2>
                    <div id="order_contact_shipping_container" <?php
                            if (!$shippingNeeded || !(isset($meta['has_shipping_addr']) && $meta['has_shipping_addr']))
                                echo ' class="cms_nodisp" ';
                            ?>>
                        <div class="ve_form_row ve_form_half">
                            <label for="order_contact_shipping_firstname"><?php echo __('Jméno', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[shipping_address][firstname]"
                                   value="<?php echo $meta['shipping_address']['firstname'] ?>"
                                   id="order_contact_shipping_firstname"/>
                        </div>
                        <div class="ve_form_row ve_form_half ve_form_half_r">
                            <label for="order_contact_shipping_surname"><?php echo __('Příjmení', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[shipping_address][surname]"
                                   value="<?php echo $meta['shipping_address']['surname'] ?>"
                                   id="order_contact_shipping_surname"/>
                        </div>
                        <div class="ve_form_row">
                            <label for="order_contact_shipping_phone"><?php echo __('Telefon', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[shipping_address][phone]"
                                   value="<?php echo $meta['shipping_address']['phone'] ?>"
                                   id="order_contact_shipping_phone"/>
                        </div>
                        <div class="ve_form_row">
                            <label for="order_contact_shipping_street"><?php echo __('Ulice', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[shipping_address][street]"
                                   value="<?php echo $meta['shipping_address']['street'] ?>"
                                   id="order_contact_shipping_street"/>
                        </div>
                        <div class="ve_form_row ve_form_half">
                            <label for="order_contact_shipping_city"><?php echo __('Město', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[shipping_address][city]"
                                   value="<?php echo $meta['shipping_address']['city'] ?>"
                                   id="order_contact_shipping_city"/>
                        </div>
                        <div class="ve_form_row ve_form_half ve_form_half_r">
                            <label for="order_contact_shipping_zip"><?php echo __('PSČ', 'mwshop'); ?></label>
                            <input class="ve_form_text" type="text" name="order_contact[shipping_address][zip]"
                                   value="<?php echo $meta['shipping_address']['zip'] ?>"
                                   id="order_contact_shipping_zip"/>
                        </div>
                        <div class="ve_form_row">
                            <label for="order_contact_shipping_country"><?php echo __('Země', 'mwshop'); ?></label>
                            <?php mws_generate_country_select('order_contact[shipping_address][country]', 'order_contact_shipping_country', 've_form_text', $meta['shipping_address']['country']) ?>
                        </div>
                    </div>

                    <h2><label for="order_contact_note"><?php echo __('Poznámka', 'mwshop'); ?></label></h2>
                    <div class="ve_form_row">
                        <textarea class="ve_form_text" name="order_contact[note]" id="order_contact_note"
                                  rows="4"><?php echo $meta['note'] ?></textarea>
                    </div>
                </form>
			</div>

			<div class="mws_order_footer title_element_container">
				<a class="mws_cart_back_but" href="<?php echo MWS()->getUrl_Cart($step-1); ?>"><?php echo __('Zpět','mwshop'); ?></a>
				<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
					 href="<?php echo MWS()->getUrl_Cart($step+1); ?>"><?php echo __('Uložit a pokračovat','mwshop'); ?></a>
				<div class="cms_clear"></div>
			</div>
		<?php }
		elseif($step==MwsOrderStep::Shipping) { ?>
		<div class="mws_shipping_payment">
			<form id="mws_order_form">
				<div class="mws_shippings">
					<h2><span class="point">1</span><?php echo __('Zvolte způsob doručení', 'mwshop'); ?></h2>
					<?php

					/** @var int $selected Currently selected shipping. */
					$selected = $cart->shipping;
					$currency = $cart->getCurrency();

					$prices = array();
					$prefix = 'mws_shipping';
					//Hidden input for AJAX error messages.
					echo '<input type="hidden" name="' . $prefix . '" />';

                    $isShippingRequired = $cart->isShippingRequired();
                    if ($isShippingRequired) {
                        $shippings = MwsShipping::getAll();
                    } else {
                        $shippings = array(MwsShippingElectronic::getInstance());
                    }
                    $countOfShippings = count($shippings);
                    if ($countOfShippings) {
                        /** @var MwsShipping $shipping */
                        foreach ($shippings as $shipping) {
                            $id = $shipping->id;
                            $title = esc_html($shipping->name);
                            $class = '';
                            $class .= ($shipping->isCodSupported ? " mws_cod_enabled" : '');
                            $class .= ($shipping->isPersonalPickup ? " mws_personal_pickup" : '');
                            $class = trim($class);

//							mwdbg('[SHIPPING] ' . $id . ' = ' . $title);
                            $priceHtml = ''
                                .'<span class="mws_float_right'. (false && $shipping->codPrice->priceVatIncluded > 0 ? ' mws_cursor_help' : '').'" '
//								. ($shipping->codPrice->priceVatIncluded > 0
//									? ' title="'.esc_attr(__('Příplatek za lomítkem je účtován při platbě až při převzetí.', 'mwshop')).'"' : ''
//								)
                                .'>'
                                .$shipping->price->asCurrency($currency)->htmlPriceVatIncluded($unit, 1, true, 'mws_price_inline')
                                . ($shipping->codPrice->priceVatIncluded > 0
                                  ? ''
//									? ' / +' . $shipping->codPrice->htmlPriceVatIncluded($unit, 1, false, 'mws_shipping_price_cod mws_price_inline')
                                    : ''
                                )
                                . '</span>'
                            ;
                            echo '<div class="' . $prefix . '_radio ' . $prefix . '_radio_' . $id . '">'
                                . '<input type="radio" id="' . $prefix . '_' . $id . '" name="' . $prefix . '" value="' . $id . '"' . (($id == $selected) ? ' checked="checked"' : '')
                                . (!empty($class) ? ' class="' . $class . '"' : '')
                                . ' data-codEnabled="' . ($shipping->isCodSupported ? 1 : 0) . '"'
                                . ($countOfShippings===1 ? ' checked="checked"' : '')
                                . '/>'
                                . '<label for="' . $prefix . '_' . $id . '" '
                                . (!empty($class) ? ' class="' . $class . '"' : '')
                                . '>' . $title . $priceHtml . '</label>'

                                . (($shipping->post->post_excerpt)
                                    ? '<div class="mws_shipping_description mws_ship_info mws_shipping_description_' . $id . '">' . $shipping->post->post_excerpt . '</div>'
                                    : '')
                                . '</div>';

                            $prices[$id]['price'] = $shipping->price->asCurrency($currency)->asArray();
                            $prices[$id]['codPrice'] = $shipping->codPrice->asCurrency($currency)->asArray();
                        }
    //						echo '<div class="cms_clear"></div>';
                        } else {
                            if (MWS()->edit_mode)
                                echo '<div class="cms_error_box">' . __('Není definován žádný způsob doručení.', 'mwshop') . ' <a target="_blank" href="' . admin_url('post-new.php?post_type='.MWS_SHIPPING_SLUG) . '">' . __('Vytvořit způsob doručení', 'mwshop') . '</a></div>';
                            else
                                echo '<div class="cms_error_box">' . __('Není definován žádný způsob doručení, objednávku nelze dokončit.', 'mwshop') . '</div>';
                        }
					?>
				</div>
				<div class="mws_payments">
					<h2><span class="point">2</span><?php echo __('Zvolte způsob platby', 'mwshop'); ?></h2>
					<?php

					/** @var int $selected Currently selected payment. */
					$selected = $cart->payment;
					$prefix = 'mws_payment';
					//Hidden input for AJAX error messages.
					echo '<input type="hidden" name="' . $prefix . '" />';


					$payTypes = MWS()->gateways()->getDefault()->getEnabledPayTypes();
					if (!$isShippingRequired) {
                        $idx = array_search(MwsPayType::Cod, $payTypes);
                        if ($idx !== false) {
                            unset($payTypes[$idx]);
                        }
                    }

					/** @var string|MwsPayType $payType */
					if (count($payTypes)) {
						$payDescs = MwsPayType::getDescriptions();
						foreach ($payTypes as $payType) {
							$id = $payType;
							$title = MwsPayType::getCaption($payType);
							$desc = isset($payDescs[$id]) ? $payDescs[$id] : '';
//							mwdbg('[PAYMENT] ' . $id . ' = ' . $title);
							echo '<div class="' . $prefix . '_radio ' . $prefix . '_radio_' . $id . '">'
								. '<input type="radio" id="' . $prefix . '_' . $id . '" name="' . $prefix . '" value="' . $id . '"' . (($id == $selected) ? ' checked="checked"' : '') . ' />'
								. '<label for="' . $prefix . '_' . $id . '">' . $title . '</label>'
								. (($id == 'creditCard') ? '<div class="mws_ship_info ' . $prefix . '_creditcards_image"><img src="' . MWS_URL_BASE . '/img/creditcards.png" alt=""></div>' : '')
								. (($id == 'wireOnline') ? '<div class="mws_ship_info ' . $prefix . '_wireonline_image"><img src="' . MWS_URL_BASE . '/img/banks.png" alt=""></div>' : '')
								. (($desc)
									? '<div class="mws_payment_description mws_ship_info mws_payment_description_' . $id . '">'.$desc.'</div>'
									: '')
								. '</div>';
						}
					} else if(MWS()->edit_mode) {
					    echo '<div class="cms_error_box">'.__('Není definován žádný způsob platby. Možné způsoby platby lze povolit v horní liště v Eshop -> Nastavení eshopu -> Platební metody.','mwshop').'</a></div>';
						//					echo '<div class="cms_clear"></div>';
                    }
						?>
					</div>

					<?php
					$text_makeSelection = __('(proveďte výběr)', 'mwshop');
					$text_InvalidPayType = __('(zvolte jiný způsob platby)', 'mwshop');
					?>
					<script>
						var prices=JSON.parse('<?php echo json_encode($prices); ?>');
						var priceUnit='<?php echo $unit;?>';
						var codPayType='<?php echo MwsPayType::Cod ?>';
						var text_zeroPrice='<?php echo __('zdarma', 'mwshop');?>';
						var text_makeSelection='<?php echo $text_makeSelection;?>';
						var text_InvalidPayType='<?php echo $text_InvalidPayType;?>';

						jQuery(document).ready(function($) {

							function setDisabledPayType(payType, disabled) {
								var elem = $("input[type=radio][value=" + payType + "]");
								elem.prop("disabled", disabled);
								if (disabled) {
									elem.parent().addClass('mws_disabled');
								} else {
									elem.parent().removeClass('mws_disabled');
								}
							}

							$("input[name='mws_shipping']:radio").live("change", function () {
								var elem = $(this);
								var payId = elem.attr('value');
								var checked = elem.attr('checked');
								var isCodEnabled = elem.hasClass('mws_cod_enabled');
								var isPersonal = elem.hasClass('mws_personal_pickup');
								var dbgInfo = ((isCodEnabled) ? ' isCod' : '') + ((isPersonal) ? ' isPersonalPickup' : '');
								console.log('switched to ' + payId + ' ' + (dbgInfo.length > 0 ? 'options:'+dbgInfo : ''));
								//Update correct method
								setDisabledPayType(codPayType, !isCodEnabled);
								updatePrice();
							});

							$("input[name='mws_payment']:radio").live("change", function () {
								updatePrice();
							});

							function updatePrice() {
								console.log('updating price');
								var selected = null;
								var shipId = 0;
								var payType = '';
								var isAllowedPayType = true;
								selected = $("input[name='mws_shipping']:checked");
								if(selected.length > 0) {
									shipId = selected.val();
								}
								selected = $("input[name='mws_payment']:checked");
								if(selected.length > 0) {
									isAllowedPayType = !selected.prop('disabled');
									if(isAllowedPayType) {
										payType = selected.val();
									}
								}
								console.log('ship&pay: ' + shipId + ' & ' + payType);

								var newPrice=0;
								if(shipId!=0 && payType!='') {
									//Both settings are present and are valid (COD is checked when getting value)
									newPrice = prices[shipId].price.priceVatIncluded;
									if (payType == codPayType) {
										newPrice += prices[shipId].codPrice.priceVatIncluded;
									}
									newPrice = Math.round(newPrice * 100) / 100;
									if (newPrice != 0) {
										newPrice += ' ' + priceUnit;
									}
								} else if (!isAllowedPayType) {
									newPrice = text_InvalidPayType;
								} else {
									newPrice = text_makeSelection;
								}
								if(newPrice===0) {
									newPrice=text_zeroPrice;
								}
								$(".mws_shipping_price span.mws_price_vatincluded").html(newPrice);

								//Update visibility of descriptions
								$('.mws_shipping_radio .mws_shipping_description').hide();
								$('.mws_payment_radio .mws_payment_description').hide();
								$('.mws_shipping_description_'+shipId).show();
								$('.mws_payment_description_'+payType).show();

							}

							//Recount price upon load is finished.
							updatePrice();
						});
					</script>
				</form>
                <div class="mws_shipping_price">
                    <?php echo __('Cena dopravy') . ': ' . (htmlPriceSimpleIncluded(!is_null($cart->shippingPrice)? $cart->shippingPrice->priceVatIncluded : 0)); ?>
				</div>
			</div>
			<div class="mws_order_footer title_element_container">
				<a class="mws_cart_back_but" href="<?php echo MWS()->getUrl_Cart($step-1); ?>"><?php echo __('Zpět','mwshop'); ?></a>
				<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
					 href="<?php echo MWS()->getUrl_Cart($step+1); ?>"><?php echo __('Uložit a pokračovat','mwshop'); ?></a>
				<div class="cms_clear"></div>
			</div>
		<?php }
		elseif($step==MwsOrderStep::Summarize) { ?>
			<div class="mws_summarize_order">
				<?php
                $error = '';

                $isShippingRequired = $cart->isShippingRequired();
                $contactAsFormInput = array('order_contact' => $cart->contact);
                $contactAsFormInput['order_contact']['totalPrice'] = 1; // supress error message of empty order
                $ok = true;
                $errors = array();
                $res = MwsAjax::validateContactForm($contactAsFormInput, $isShippingRequired);
                if (!$res['success']) {
                    $ok = false;
                    if (isset($res['flashMessage'])) {
                        $errors += $res['flashMessage'];
                    } else if (isset($res['errors']) && !empty($res['errors'])) {
                        $errors += $res['errors'];
                    }
                }
                $res = MwsAjax::validateShippingAndPayment(array('mws_shipping' => $cart->shipping, 'mws_payment' => $cart->payment), $isShippingRequired);
                if (!$res['success']) {
                    $ok = false;
                    if (isset($res['flashMessage'])) {
                        $errors += $res['flashMessage'];
                    } else if (isset($res['errors']) && !empty($res['errors'])) {
                        $errors += $res['errors'];
                    }
                }

                if (!$ok) {
                    // Cannot continue because of invalid order.
                    echo '<div class="mws_error">';
                    if (!empty($errors)) {
                        echo __('Vaše objednávka obsahuje následující nesrovnalosti. Vraťte se zpět a potřebné údaje upravte.', 'mwshop');
                        echo '<ul>';
                        foreach ($errors as $errorLine) {
                            echo '<li>'. esc_html(strip_tags($errorLine)) . '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo __('Při kontrole objednávky došlo k blíže neurčené chybě. Zkontrolujte prosím, zda váš košík není prázdný ' .
                          'a jsou korektně vyplněné kontaktní a platební údaje.', 'mwshop');
                    }
                    echo '</div>';
                } else {
                 // Recount cart content including price for shipping
                    $cart->recount($isShippingRequired, false, 'default', true);
                    if(!$cart->isRecounted) {
                        echo '<div class="mws_error">';
                        echo empty($cart->recountError)
                            ? __('Omlouváme se, nepodařilo se spočítat cenu objednávky. Proveďte přepočítání.', 'mwshop')
                            : $cart->recountError;
                        if ($cart->availabilityErrorsCount)
                            echo ' ' . sprintf(__('Upravte obsah <a href="%s">košíku</a>.', 'mwshop'), MWS()->getUrl_Cart(MwsOrderStep::Cart));
                        if (MWS()->edit_mode && !empty($cart->recountAdminError))
                            echo '<small>' . __('Technický popis chyby:', 'mwshop') . ' <span>' . $cart->recountAdminError . '</span></small>';
                        echo '</div>';
                    }
                    ?>
                    <div class="mws_info_box"><span>i</span><?php echo __('Prosíme o pečlivou kontrolu vaší objednávky', 'mwshop'); ?></div>
                    <form id="mws_order_form">
                        <div class="mws_summarize_cart">

                            <h2><?php echo __('Vaše objednávka', 'mwshop'); ?></h2>
                            <?php

                            /** @var MwsCartItem $cartItem */
                            echo '<table>';
                            foreach($cart->items->data as $cartItem) {
                                $product = $cartItem->product;
                                $status = $cartItem->availabilityStatus;
                                echo '<tr class="mws_product_id-'. $product->id.' ' . $product->getAvailabilityCSS($status) . '">'
                                    . '<td><span class="mws_cart_item_count">' . $cartItem->count . '</span> x </td>'
                                    . '<td class="mws_cart_item_title"><span>' . get_the_title($product->post) . '</span>'
                                    . ($cartItem->availabilityError ? '<span class="mw_input_error_text">'.$cartItem->availabilityError.'</span>' : '')
                                    . '</td>'
                                    . '<td class="mws_cart_item_price">'
                                    . (!is_null($cartItem->storedPrice)
                                        ? $cartItem->storedPrice->htmlPriceFull('mws_product_price', $cartItem->count)
                                        : $cartItem->product->price->htmlPriceFull('mws_product_price', $cartItem->count)
                                        )
                                    . '</td>'
                                    . '</tr>';
                            }
                            $shipping = $cart->shippingInstance;
                            $payType = $cart->payment;
                            if(is_null($shipping) || is_null($payType)) {
                                echo '<tr class="mws_shipping"><td class="mws_input_error" colspan="3">'.__('doprava či platba nebyla určena', 'mwshop').'</td></tr>';
                            } else {
                                $shippingTotal = $cart->shippingPrice;
                                echo '<tr class="mws_shipping">'
                                    . '<td colspan="2">'.esc_html($shipping->name)
                                    . (($payType===MwsPayType::Cod && $shipping->codPrice->priceVatIncluded > 0)
                                        ? ' '.__('(platba při převzetí)')
                                        : ' <br /> '.(MwsPayType::getCaption($payType))
                                    )
                                    .'</td>'
                                    . '<td  class="mws_cart_item_price">'
                                    . (is_null($shippingTotal) ? __('neznámá', 'mwshop') : $shippingTotal->htmlPriceFull('mws_product_price', 1)) . '</td>'
                                    . '</tr>';
                            }
                            echo '<tr class="mws_cart_items_footer">'
                                . '<td colspan="2">'. __('Celková cena', 'mwshop').'</td>'
                                . '<td colspan="1" class="mws_cart_item_price">';
                            $price = $cart->storedTotalPrice;
                            if($cart->isRecounted && !is_null($price)) {
                                $currency = MwsCurrency::getSymbol($price->currency);
                                echo htmlPriceSimpleIncluded($price->priceVatIncluded, $currency);
                                echo htmlPriceSimpleExcluded($price->priceVatExcluded, $currency);
                            } else {
                                echo __('(nutno přepočítat)', 'mwshop');
                            }
                            echo	'</td>'
                                . '</tr>';
                            echo '</table>';
                            ?>
                        </div>
                        <?php
                        // ----- CONTACT ----
                        $contact = $cart->contact;
                        ?>
                        <div class="mws_summarize_client">
                            <div class="mws_summarize_invoice">
                                <?php
                                    echo (isset($contact['email']) && !empty($contact['email']))
                                        ? '<h2>' . esc_html(__('Email', 'mwshop')) . '</h2><div>' . esc_html($contact['email']).'</div>'
                                        : '';
                                    if(isset($contact['want_invoice']) && $contact['want_invoice']) {
                                        echo '<h2>' . __('Fakturační údaje', 'mwshop') . '</h2>';
                                        echo '<div class="mws_company_info">'
                                            . $cart->formatCompanyInfo(true)
                                            . '</div>';
                                        echo $cart->formatAddress($contact['address'], true);
                                    } else {
                                        echo '<h2>' . __('Zjednodušený doklad', 'mwshop') . '</h2>';
                                        $country = isset($contact['address']['country']) && !empty($contact['address']['country'])
                                          ? $contact['address']['country'] : '';
                                        // Check if still possible to use simplified invoice
                                        $useSimplifiedInvoice = $cart->useSimplifiedInvoice();
                                        if (! $useSimplifiedInvoice) {
                                            $error = sprintf(__(
                                                'Použití zjednodušeného dokladu není povoleno. ' .
                                                'Je potřeba <a href="%s">vyplnit fakturační údaje</a>.', 'mwshop'),
                                                MWS()->getUrl_Cart(MwsOrderStep::Contact)
                                            );
                                        } else if (!($country === 'CZ' && $cart->storedTotalPrice && $cart->storedTotalPrice->priceVatIncluded <= 10000)) {
                                            $error = sprintf(__(
                                                'Použití zjendodušeného dokladu není přípustné pro vaši objednávku. <br />' .
                                                'Je potřeba <a href="%s">vyplnit fakturační údaje</a>', 'mwshop'),
                                                MWS()->getUrl_Cart(MwsOrderStep::Contact)
                                            );
                                        }
                                        if ($error) {
                                            echo '<div class="mws_error">'.$error.'</div>';
                                        }

                                        echo isset($contact['address']['country']) && !empty($contact['address']['country'])
                                          ? '<div>' . esc_html(__('Země:', 'mwshop') . ' ' . MWS()->getCountryByCode($contact['address']['country'])) . '</div>'
                                          : '';
                                    }
                                ?>
                            </div>
                            <div class="mws_summarize_shipping">
                                <?php
                                if(isset($contact['has_shipping_addr']) && $contact['has_shipping_addr']) {
                                    echo '<h2>' . __('Dodací adresa', 'mwshop') . '</h2>';
                                    echo $cart->formatAddress($contact['shipping_address'], true);
                                } elseif ($cart->isShippingRequired()) {
                                    if (!(isset($contact['want_invoice']) && $contact['want_invoice'])) {
                                        $error = sprintf(__(
                                            'Objednáváte zboží, které vyžaduje fyzické doručení. <br />' .
                                            'Je potřeba <a href="%s">vyplnit fakturační anebo doručovací adresu</a>.', 'mwshop'
                                        ), MWS()->getUrl_Cart(MwsOrderStep::Contact)
                                        );
                                        echo '<div class="mws_error">' . $error . '</div>';
                                    }
                                }
                                ?>
                            </div>
                            <?php
                            if(isset($contact['note']) && !empty($contact['note'])) {
                                echo '<div class="mws_summarize_note">'
                                    . '<h2>'. __('Poznámka', 'mwshop').'</h2>'
                                    . '<div class="mws_order_note">'
                                    . wpautop(esc_html($contact['note']))
                                    . '</div></div>'
                                ;
                            }
                            ?>
                        </div>
                        <div class="cms_clear"></div>
                        <?php
                        $termsUrl = MWS()->getUrl_TermsAndCondtions();

                        //terms and conditions

                        if(!empty($termsUrl)) {  ?>
                            <div class="mws_summarize_terms">
                                <input class="" type="checkbox" name="summarize[terms]"
                                       value="confirmed"
                                       id="summarize_terms"/>
                                <label for="summarize_terms" >
                                <?php
                                    echo __('Souhlasím s', 'mwshop')
                                    . ' '
                                    . '<a href="'.$termsUrl.'" target="_blank">'.__('obchodními podmínkami', 'mwshop').'</a>'
                                    . '.'
                                    ;
                                ?>
                                </label>
                            </div>
                        <?php
                        } else if (MWS()->edit_mode) {
                            echo '<div class="cms_error_box">' .
                                 sprintf(__('Ještě vám chybí nastavit si stránku s obchodními podmínkami. Stránku lze nastavit %s v základním nastavení eshopu.','mwshop'),
                                     '<a class="open-setting" data-type="group" data-setting="eshop_option" title="Nastavení eshopu" href="#">'.__('zde','mwshop').'</a>') .
                                 '</div>';
                        }

                        // Purposes
                        ?>
<!--                        <div class="cms_clear"></div>-->
<!--                        <h2>Zpracování osobních údajů</h2>-->
                        <div>
                        </div>
                        <?php
                            $purposes = MWS()->gateways()->getDefault()->getPurposes();
                            $code = MWS()->renderPurposes($purposes);
                            echo $code;
                        ?>
                    </form>
			    <?php } ?>
            </div>
			<div class="mws_order_footer title_element_container">
				<a class="mws_cart_back_but" href="<?php echo MWS()->getUrl_Cart($step-1); ?>"><?php echo __('Zpět','mwshop'); ?></a>
				<?php

                if ($error || !$ok) {
                    // No continue button
                } elseif ($cartItemsCount>0) {
					if($cart->isRecounted) {
						echo '<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
							 href="'.  MWS()->getUrl_Cart($step+1) .'">'
                            . __('Závazně objednat','mwshop');
                        if(get_locale()=='sk_SK')
                            echo '<small>'.__('S poviností platby','mwshop').'</small>';
                        echo '</a>';
					} else {
						echo '<a class="mws_cart_continue_but mws_cart_but eshop_color_background"
							 href="'. MWS()->getUrl_Cart($step). '">'
                            . __('Přepočítat','mwshop')
                            . '</a>';
					}
				} ?>
				<div class="cms_clear"></div>
			</div>
		<?php
		}
		elseif($step==MwsOrderStep::ThankYou) {
			mwshoplog('request=' . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG, 'order');
			//Get associated order
			$gwId = isset($_REQUEST['gw']) ? $_REQUEST['gw'] : '';
			$gw = MWS()->gateways()->getById($gwId);
			if(is_null($gw))
				$gw = MWS()->gateways()->getDefault();
			$order = $gw->sharedInstance()->getOrderFromThankYou();
			if(is_null($order)) {
				// Order is not present or could not be identified.
		?>
				<div class="mws_order_finished">
					<h2><?php echo __('Při zpracování vaší objednávky došlo k chybě. Vraťte se prosím o krok zpět a pokus zopakujte.' .
							' Omlouváme se za nepříjemnosti.', 'mwshop'); ?></h2>

					<p><?php echo sprintf(__('V případě trvajících potíží můžete %s.', 'mwshop'),
						'<a href="mailto:'. get_option('admin_email') .'">'.__('kontaktovat naši podporu', 'mwshop').'</a>'); ?></p>
				</div>
			<?php } else {
				// Order was processed
				add_action('wp_footer', 'hookFooter_ShopConversionCodes', 110);
				$GLOBALS['successfulOrder'] = $order;
				?>
				<div class="mws_order_finished">
					<h2><?php echo $success
							? __('Děkujeme za vaši objednávku, váš nákup byl v pořádku uložen a odeslán ke zpracování.', 'mwshop')
							: __(
								'Vaši objednávku jsme přijali ke zpracování v pořádku, '
								. 'ale během úhrady objednávky došlo k chybě. ', 'mwshop')
						;
						?>
					</h2>

					<p><?php
						echo __('Na váš e-mail bylo zasláno potvrzení s upřesňujícími informacemi.', 'mwshop');
						// Force to check payment status
						if(!$order->isPaid) {
							$gwLive = $order->gateLive;
							if($gwLive && $gwLive->isPaid) {
								$order->setPaid(true, $gwLive->paidOn);
								$order->save();
							}
						}
						if(!$order->isPaid) {
							// Direct pay URL formatting
							$payUrl = $order->urlDirectPay;
							if(!empty($payUrl)) {
								echo '<br />';
								if ($success)
									echo __('Pokud jste tak ještě neučinili, můžete cenu uhradit online', 'mwshop');
								else
									echo __('Objednávku můžete uhradit online ', 'mwshop');
								echo ' <a href="' . $payUrl . '" target="_blank">' . __('zde', 'mwshop') . '</a>.';
							}
						} else {
							echo '<br />'.__('Platbu za objednávku jsme přijali v pořádku. Děkujeme.', 'mwshop');
						}
						?>
					</p>


					<div class="mws_info_box">
						<span>i</span><?php echo __('Číslo vaší objednávky:', 'mwshop') . ' ' . $order->orderNum; ?><br />
					</div>
					<div class="mws_order_finished_info entry_content">
						<?php
              if(!isset(MWS()->setting['thanks_content'])) MWS()->setting['thanks_content']='';
              $args=array(
                  'key'=>'thanks_content',
                  'option'=>MWS_OPTION_SHOP_SETTING
              );
              echo $vePage->weditor->weditor_content(MWS()->setting['thanks_content'], $args);
            ?>
					</div>
				</div>
				<div class="mws_order_finished_footer title_element_container">
					<a class="mws_cart_but eshop_color_background"
						 href="<?php echo MWS()->getUrl_Home(); ?>"><?php echo __('Vrátit se zpět do obchodu', 'mwshop'); ?></a>
				</div>
			<?php }
			}  ?>
	</div>
</div>

<?php

if($step == MwsOrderStep::Cart) {
  // content after cart
  if(!isset(MWS()->setting['cart_content'])) MWS()->setting['cart_content']='';
  $args=array(
      'key'=>'cart_content',
      'option'=>MWS_OPTION_SHOP_SETTING
  );
  echo $vePage->weditor->weditor_content(MWS()->setting['cart_content'], $args);
}

/** Print conversion codes of successfully ordered products. */
function hookFooter_ShopConversionCodes() {
	/** @var MwsOrder $order */
  $order = isset($GLOBALS['successfulOrder']) ? $GLOBALS['successfulOrder'] : null;
	if(is_null($order) || empty($order))
		return;
	/** @var MwsOrderItem $item */
	foreach ($order->items->items as $item) {
		if(!empty($item->conversionCode))
			echo $item->conversionCode .'<br />';
	}

  //add eshop setting codes
  $codes=get_option('eshop_codes');
  if(isset($codes['eshop_conversion'])) echo $codes['eshop_conversion'];

}

if (MWS()->inMioweb) {
	get_skin_footer('mwshop');
} else {
	get_header('mwshop');
}
?>
