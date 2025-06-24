<?php
/**
 * AJAX call handlers.
 * User: kuba
 * Date: 14.03.16
 * Time: 18:11
 */

define('MWS_QUICK_BUY_NONCE', 'quick_buy');
define('MWS_GATEWAY_SYNC_NONCE', 'gateway_sync');

class MwsAjax {
	/**
	 * Removes item from the cart.
	 * @param int $_REQUEST['product'] ID of the product that should be removed.
	 *
	 * @return JSON Fields "bool success" whether updated cart does not contain product;
	 * "int productId" id of removed product; "bool realyRemoved" it the item was really removed
	 */
	public static function cartRemoveItem() {
		$productId = isset($_REQUEST['product']) ? $_REQUEST['product'] : null;
		$item = MWS()->cart->items->getById($productId);
		if(is_null($item)) {
			// Removed product is not in the cart.
			$res = json_encode(array(
				'productId' => $productId,
				'result' => true,
				'realyRemoved' => false,
				'message' => __('Košík neobsahuje odebírané zboží.', 'mwshop'),
			));
		} else {
			// Removed product is in the cart.
			$removed = 0;
			try
			{
				$cart = MWS()->cart;
				$removed = MWS()->cart->items->remove($productId);
				$cart->recount(false, true, 'default', true);

				//Render new cart content
				$newContent = '<div class="mws_cart_container '. ($cart->items->isEmpty() ? 'mws_cart_empty' : '') . '"
				<form id="mws_order_form">
					'. mwsRenderParts('cart', 'loop', true). '
				</form></div>';

				$res = json_encode(array(
					'productId' => $productId,
					'result' => true,
					'realyRemoved' => ($removed > 0),
          'cart_count' => MWS()->cart->items->count(),
					'message' => __('Zboží bylo odebráno z košíku', 'mwshop' ),
					'newCart' => $newContent,
				));
} catch(Exception $e) {
				$res = json_encode(array(
					'productId' => $productId,
					'result' => false,
					'realyRemoved' => ($removed > 0),
					'message' => __('Při odebírání zboží z košíku došlo k chybě.', 'mwshop') ."\n".$e->getMessage(),
				));
			}
		}
		wp_send_json($res);
	}

	/**
	 * Buy a product directly skipping the steps of full order. Validate input at first.
	 * @return JSON Json object with
	 */
	public static function quickBuy() {
		$nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
		if(!wp_verify_nonce($nonce, MWS_QUICK_BUY_NONCE)) {
			wp_send_json_error(array(
				'flashMessage' => '<div class="mws_error">'.__('Neověřený požadavek.', 'mwshop').'</div>',
			));
		}

		$form = isset($_REQUEST['form']) ? $_REQUEST['form'] : '';
		parse_str($form, $formData);
		$priceStr = isset($_REQUEST['price']) ? $_REQUEST['price'] : '';
		if(!is_array($priceStr)) {
			wp_send_json_error(array(
				'flashMessage' => '<div class="mws_error">'.__('Chyba při zpracování - chybí cena', 'mwshop').'</div>',
			));
		}
		$isShippingRequired = isset($_REQUEST['isShippingRequired']) ? filter_var($_REQUEST['isShippingRequired'], FILTER_VALIDATE_BOOLEAN) : true;

		$ok = true;
		$errors = array();

		// validate contact
		$res = static::validateContactForm($formData, $isShippingRequired);
		if(!$res['success']) {
			$ok = false;
			$errors = array_merge($errors, $res['errors']);;
		}
		// validate shipping and payment
		$res = static::validateShippingAndPayment($formData, $isShippingRequired);
		if(!$res['success']) {
			$ok = false;
			$errors = array_merge($errors, $res['errors']);
		}
		if(isset($res['shipping']))
			$shipping = $res['shipping'];
		if(isset($res['payType']))
			$payType = $res['payType'];
		// validate terms and conditions
		$res = static::validateTermsAndConditions($formData);
		if (!$res['success']) {
			$ok = false;
			$errors = array_merge($errors, $res['errors']);
		}
		// validate purposes
		$res = static::validatePurposes( $formData );
		if ( ! $res['success'] ) {
			$ok     = false;
			$errors = array_merge( $errors, $res['errors'] );
			$purposes = null;
		} else {
			// Update with validated (normalized) data.
			$purposes = $formData['purposes'] = $res['purposes'];
		}

		if(!$ok) {
			wp_send_json_error(array(
				'errors' => $errors,
			));
		}

		// order
		$productId = isset($_REQUEST['productId']) ? $_REQUEST['productId'] : 0;
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 1;
		$price = new MwsPrice($priceStr);

		$product = MwsProduct::getById($productId);
		if(is_null($product))
			wp_send_json_error(array(
				'flashMessage' => '<div class="mws_error">'.__('Produkt se nepodařilo nalézt v nabídce.', 'mwshop').'</div>',
			));
		$productId = $product->id;

		if($price->priceVatIncluded == 0)
			wp_send_json_error(array(
				'flashMessage' => '<div class="mws_error">'.__('U produktu je nulová cena? To je podezřelé.', 'mwshop').'</div>',
			));


		/**
		 * Send flash error message and die. Optionally add detailed message, different for user and for administrator.
		 * @param string $mainMsg Overridable message
		 * @param string $detailUser Optional message for user.
		 * @param string $detailAdmin Optional message for admin. If empty, then $detailUser is used.
		 */
		$sendErrorAndDie = function($mainMsg = '', $detailUser = '', $detailAdmin = '') {
			if(empty($mainMsg))
				$mainMsg = __('Objednávku se nepodařilo odeslat. %s', 'mwshop');

			wp_send_json_error(array(
				'flashMessage' => '<div class="mws_error">'.sprintf($mainMsg,
						(MWS()->edit_mode ? (empty($detailAdmin) ? $detailUser : $detailAdmin) : $detailUser)) . '</div>',
			));
		};

		$ok = false;

		$gw = MWS()->gateways()->getDefault();
		if(is_null($gw))
			$sendErrorAndDie('', '[gate]', __('Není nastavena platební brána.', 'mwshop'));

		try {// Prepare temporary cart
			$cartTemp = new MwsCartTemporary();
			$cartTemp->addItem($productId, $count);
			$item = $cartTemp->items->getById($productId);
			if (is_null($item))
				$sendErrorAndDie('', __('Chyba při vkládání do košíku.', 'mwshop'));
			$item->storedPrice = $price;
			/** @var MwsShipping $shipping */
			if (isset($shipping)) {
				$cartTemp->shipping = $shipping->id;
				$cartTemp->shippingPrice = $shipping->price;
			} else {
				$cartTemp->shippingPrice = null;
			}
			$cartTemp->contact = isset($formData['order_contact']) ? $formData['order_contact'] : array();
			/** @var MwsPayType $payType */
			if (!isset($payType))
				$sendErrorAndDie('', __('Selhalo určení platební metody.', 'mwshop'));
			$cartTemp->payment = $payType;
			$cartTemp->purposes = $purposes;

				// Make the order
			$res = $gw->sharedInstance()->makeOrder($cartTemp);
			$ok = $res['success'];

			if($ok) {
				// Update statistics
				$cartTemp->incOrderedCount();

				wp_send_json_success(array(
					'nextUrl' => (isset($res['nextUrl'])
						? $res['nextUrl']
						: add_query_arg(array('success' => true), MWS()->getUrl_Cart(MwsOrderStep::ThankYou))
					),
				));
			} else {
				if (isset($res['message'])) {
					$errMsg = $res['message'];
					$errMainMsg = '%s';
				}
			}
		} catch (Exception $e) {
			$errMsg = __('Neočekávaná chyba při zpracování.', 'mwshop');
			$errAdmin = $errMsg . ' ' . $e->getMessage();
		}

		$sendErrorAndDie(
			(isset($errMainMsg) ? $errMainMsg : ''),
			(empty($errMsg) ? __('Zopakujte objednání.', 'mwshop') : $errMsg),
			(isset($errAdmin) ? $errAdmin : '')
		);
	}
  
	public static function cartAddItem() {
		$productId = isset($_REQUEST['product']) ? $_REQUEST['product'] : null;
		$count = isset($_REQUEST['count']) ? (int)$_REQUEST['count'] : 1;
		$isQuick = isset($_REQUEST['isQuick']) ? (bool)filter_var($_REQUEST['isQuick'], FILTER_VALIDATE_BOOLEAN) : false;
		$canQuickAddToCart = isset($_REQUEST['canQuickAddToCart']) ? (bool)filter_var($_REQUEST['canQuickAddToCart'], FILTER_VALIDATE_BOOLEAN) : false;

    $res=array(
        'cart_count'=> MWS()->cart->items->count()
    );

		//Check product existence.
		$product = MwsProduct::getById($productId);
		if(is_null($product)) {
			$shopUrl = MWS()->getUrl_Home();
      $res['content']='<div class="mws_colorbox_message">'
				. __('Vybraný produkt není v našem obchodě k dispozici.', 'mwshop')
				. ($shopUrl ? ' ' . sprintf(__('Přejete si zobrazit <a href="%s"> naši nabídku?', 'mwshop'), $shopUrl) : '')
				. '</div>';
      $res['added']=0;
		} else {
			// Product exists
			MWS()->current()->product = $product;

			if(!$isQuick) {
				// ORDINARY add to cart

				$buttons = '
						<div class="mws_add_to_cart_footer title_element_container">
							<a class="mws_cart_back_but mws_close_cart_box mws_cart_quickbuy" href="#">' . __('Zavřít', 'mwshop') . '</a>
							<div class="cms_clear"></div>
            </div>';
				$title = __('Nepodařilo se vložit do košíku', 'mwshop');
				$content = '';
				$error = '';

				if($product->canBuy_Count($count)) {
					// Product is available in necessary amount
					$added = MWS()->cart->addItem($productId, $count);
					//TODO Make output pretty.
					if ($added > 0) {

						MWS()->current()->cartItem = MWS()->cart->items->getById($productId);

						$res = array(
							'cart_count' => MWS()->cart->items->count(),
							'added' => $productId,
							'added_hover' => mwsRenderParts('cart', 'hover-items', true)
						);

						$title = sprintf(_n('Zboží jsme vložili do košíku', 'Zboží jsme vložili do košíku v počtu %d kusů.',
							$added, 'mwshop'), $added);
						$content = mwsRenderParts('cart', 'added', true);
						$buttons = '<a class="mws_cart_back_but mws_close_cart_box" href="#">' . __('Pokračovat v nákupu', 'mwshop') . '</a>
              <a class="mws_cart_but eshop_color_background" href="' . MWS()->getUrl_Cart() . '">'
							. __('Přejít do košíku', 'mwshop') . '</a>';
					} elseif ($added == 0) {
						// Although product is available, something stopped from adding required amount of product into the cart.
						MWS()->current()->showAvailabilityInAdded = true;
						$content .= mwsRenderParts('cart', 'added', true);
						$error .= '<div class="mws_error">'
							. __('Do košíku jsme nic nevložili. Zkuste obnovit stránku a pokus opakovat.', 'mwshop')
							. '</div>';
					} else {
						// Unknown error
						MWS()->current()->showAvailabilityInAdded = true;
						$content .= mwsRenderParts('cart', 'added', true);
						$error .= '<div class="mws_error">'
							. __('Při vkládání zboží do košíku došlo k chybě. Zkuste obnovit stránku a pokus opakovat.', 'mwshop')
							. '</div>';
					}
				} else {
					// Cannot add to cart required amount
					MWS()->current()->showAvailabilityInAdded = true;
					$content .= mwsRenderParts('cart', 'added', true);
					$error .= '<div class="mws_error">'
						. __('Do košíku jsme nic nevložili, zboží není v požadovaném množství dostupné. ' .
							'Zkuste obnovit stránku a pokus opakovat později.', 'mwshop')
						. '</div>'
					;
				}

				$res['content'] = '
					<div class="mws_add_to_cart_box">
						<div class="mws_add_to_cart_header"> ' . $title . '
								<a href="#" class="mws_close_cart_box">' . file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true) . '</a>
						</div>
						' . $error . '
						<div class="mws_add_to_cart_content">
								' . $content . '
						</div> 
						<div class="mws_add_to_cart_footer title_element_container"> 
							' . $buttons .'
								<div class="cms_clear"></div>
						</div>
					</div>';
			} else {
				// QUICK BUY element

				$form = '
					<input type="hidden" name="product" value="'.$productId.'" />
					<input type="hidden" name="count" value="'.$count.'" />
					<input type="hidden" name="price" value="'.esc_html($product->price->asJson()).'" />
				';
				$buttons = '';
				$script = '';
				$formId = 'mws_quick_order';

				if($product->canBuy_Count($count)) {
					// Product is available in necessary amount


					/** @var bool $canSimplified Can be simplified invoice used? */
					$canSimplified = MWS()->gateways()->getDefault()->getUseSimplifiedInvoice();
					/** @var bool $isShippingRequired Is shipping required? */
					$isShippingRequired = ($product->type != MwsProductType::Electronic) && ($product->type != MwsProductType::Other);
					/** @var bool $isInvoiceRequired Is invoice required? */
					$isInvoiceRequired = !($canSimplified && $product->price->priceVatIncluded <= 10000 /*&& $country === 'CZ'*/); //TODO MWS()->getDefaultCountry
					if ($isInvoiceRequired) {
						$wantInvoiceChecked = true;
					} else {
						$wantInvoiceChecked = isset($meta['want_invoice']) && $meta['want_invoice'];
					}

					$meta = array();
					$form .= '<input type="hidden" name="order_contact[totalPrice]" value="' . $product->price->priceVatIncluded . '">';
					$form .= '
<script>
	var cartTotalPrice = ' . $product->price->priceVatIncluded . ';
	var canSimplifiedInvoice = ' . ($canSimplified ? 'true' : 'false') . ';

	var productId = ' . $productId . ';
	var count = ' . $count . ';
	var price = ' . $product->price->asJson() . ';
	
	var nonce = "' . wp_create_nonce(MWS_QUICK_BUY_NONCE) . '";
	var formId = "' . $formId . '";
	var isShippingRequired = ' . ($isShippingRequired ? 'true' : 'false') . ';
	console.log("isShippingRequired=", isShippingRequired);
	var textError_AjaxError="' . __('Komunikace se serverem se nezdařila. Prosím opakujte požadavek později.', 'mwshop') . '";
	var text_makeSelectionPayment="' /*. __('Zvolte platební metodu', 'mwshop')*/ . '";
	var text_InvalidPayType = "'/*.__('(zvolte jiný způsob platby)', 'mwshop')*/ . '";
	var priceUnit = "' . MWS()->getCurrency() . '";
	
</script>
					';

					// email
					$form .= '
						<div>
							<input class="ve_form_text" type="text" name="order_contact[email]" 
								value="" placeholder="' . __('Zadejte e-mail', 'cms_ve') . '" 
								id="order_contact_email" />
						</div>';

					if (!$isInvoiceRequired) {
						// Country
						$form .= '            
							<div class="mws_select_container">
								<label for="order_contact_country">' . __('Země', 'mwshop') . ' </label>
								' . mws_generate_country_select('order_contact[address][country]', 'order_contact_country', '', $meta['address']['country'], false) . '
							</div>';
					}

					//shipping
					if ($isShippingRequired) {
						$form .= '
							<div class="mws_select_container mws_with_help">
								<label for="order_shipping_quick">' . __('Způsob dopravy', 'mwshop') . ' </label>
								' . MwsShipping::htmlSelect('order_shipping_quick', 'mws_shipping', '', '',
									__('(vyberte způsob dopravy)', 'mwshop'))
								. '
								<span class="cms_description" title="' . esc_attr(__('Cena za doručení se skládá z ceny za dopravu a ' .
									'případného příplatku při volbě platby "Při převzetí" (za lomítkem).', 'mwshop')) . '">&nbsp;</span>
							</div>';

						// script data for shipping price update
						$text_InvalidPayType = '';//__('(zvolte jiný způsob platby)', 'mwshop');
						$prices = array();
						$shippings = MwsShipping::getAll();
						/** @var MwsShipping $shipping */
						foreach ($shippings as $shipping) {
							$prices[$shipping->id]['price'] = $shipping->price->asArray();
							$prices[$shipping->id]['codPrice'] = $shipping->codPrice->asArray();
						}

						$script .= '
<script>
  // Prices of shipping and payment methods.
	var prices=JSON.parse(\'' . json_encode($prices) . '\');
	var priceUnit="' . MWS()->getCurrency() . '";
	var codPayType="' . MwsPayType::Cod . '";
	var text_zeroPrice="' . ''/*__('zdarma', 'mwshop')*/ . '";
	var text_InvalidPayType="' . $text_InvalidPayType . '";

	jQuery(document).ready(function($) {
		//Simulate selection within shippings to update allowed controls like payments.
		$("select[name=\'mws_shipping\']").change();
		//Recount price upon load is finished.
		updatePrice_quick();
	});
</script>';
					}

					//payment
					$form .= '
						<div class="mws_select_container">
							<label for="order_payment_quick">' . __('Způsob platby', 'mwshop') . ' </label>
							' . MWS()->gateways()->htmlSelect('order_payment_quick', 'mws_payment', '', '',
							(true || $isShippingRequired ? __('(vyberte způsob platby)', 'mwshop') : ''),
							($isShippingRequired ? array() : array(MwsPayType::Cod))
						) . '
						</div>';

					// invoice check box
					if ($canSimplified) {
						$form .= '
							<div id="order_contact_want_invoice_group" ' . ($isInvoiceRequired ? 'class="cms_nodisp"' : '') .'>
								<input class="mw_toggle_container"
											 data-target="order_contact_invoice_container"
											 type="checkbox"
											 name="order_contact[want_invoice]" id="order_contact_want_invoice"
											 '. ($wantInvoiceChecked ? ' checked="checked" ' : '') .'
											 '. ($isInvoiceRequired ? ' onclick="return false" ' : '') .'
								>
								<label for="order_contact_want_invoice">'. __('Potřebuji vystavit fakturu', 'mwshop') .'</label>
							</div>
						';
					} else {
						$form .= '<input type = "hidden" name = "order_contact[want_invoice]" value = "on" >';
					}

					// invoice contact
					$form .= '
					<div id="order_contact_invoice_container" class="' . ($isInvoiceRequired ? '' : 'cms_nodisp') . '">
						<div class="ve_form_row ve_form_row_half">
							<label for="order_contact_firstname">' . __('Jméno', 'mwshop') . '</label>
							<input class="ve_form_text" type="text" name="order_contact[address][firstname]" value="'
						. (isset($meta['address']['firstname']) ? $meta['address']['firstname'] : '')
						. '" id="order_contact_firstname" />
						</div>
						<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
							<label for="order_contact_surname">' . __('Příjmení', 'mwshop') . '</label>
							<input class="ve_form_text" type="text" name="order_contact[address][surname]" value="'
						. (isset($meta['address']['surname']) ? $meta['address']['surname'] : '')
						. '" id="order_contact_surname" />
						</div>
						<div class="cms_clear"></div>
						<div class="ve_form_row">
							<label for="order_contact_phone">' . __('Telefon', 'mwshop') . '</label>
							<input class="ve_form_text" type="text" name="order_contact[address][phone]" value="'
						. (isset($meta['address']['phone']) ? $meta['address']['phone'] : '')
						. '" id="order_contact_phone" />
						</div>
						<div class="ve_form_row">
							<label for="order_contact_street">' . __('Ulice', 'mwshop') . '</label>
							<input class="ve_form_text" type="text" name="order_contact[address][street]" value="'
						. (isset($meta['address']['street']) ? $meta['address']['street'] : '')
						. '" id="order_contact_street" />
						</div>
						<div class="ve_form_row ve_form_row_half">
							<label for="order_contact_city">' . __('Město', 'mwshop') . '</label>
							<input class="ve_form_text" type="text" name="order_contact[address][city]" value="'
						. (isset($meta['address']['city']) ? $meta['address']['city'] : '')
						. '" id="order_contact_city" />
						</div>
						<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
							<label for="order_contact_zip">' . __('PSČ', 'mwshop') . '</label>
							<input class="ve_form_text" type="text" name="order_contact[address][zip]" value="'
						. (isset($meta['address']['zip']) ? $meta['address']['zip'] : '')
						. '" id="order_contact_zip" />
						</div>
						';
					if ($isInvoiceRequired) {
						$form .= '
            <div class="ve_form_row">
                <label for="order_contact_country">' . __('Země', 'mwshop') . '</label>
                ' . mws_generate_country_select('order_contact[address][country]', 'order_contact_country', 've_form_text', $meta['address']['country'], false) . '
            </div>
          	';
					}

					$form .= '
						<div class="cms_clear"></div>

						<div>
							<input class="mw_toggle_container_quick" data-target="order_contact_company_container" value="1" type="checkbox" name="order_contact[is_company]" id="order_contact_is_company">
							<label for="order_contact_is_company">' . __('Nakupuji na firmu', 'mwshop') . '</label>
						</div>
						
						<div id="order_contact_company_container" class="cms_nodisp">
							<div class="ve_form_row">
								<label for="order_contact_company_name">' . __('Název společnosti', 'mwshop') . '</label>
								<input class="ve_form_text" type="text" name="order_contact[company_info][company_name]" value="'
						. (isset($meta['company_info']['company_name']) ? $meta['company_info']['company_name'] : '')
						. '" id="order_contact_company_name" />
							</div>
							<div class="ve_form_row ve_form_half">
								<label for="order_contact_company_id">' . __('IČ', 'mwshop') . '</label>
								<input class="ve_form_text" type="text" name="order_contact[company_info][company_id]" value="'
						. (isset($meta['company_info']['company_id']) ? $meta['company_info']['company_id'] : '')
						. '" id="order_contact_company_id" />
							</div>
							<div class="ve_form_row ve_form_half ve_form_half_r">
								<label for="order_contact_company_vat_id">' . __('DIČ', 'mwshop') . '</label>
								<input class="ve_form_text" type="text" name="order_contact[company_info][company_vat_id]" value="'
						. (isset($meta['company_info']['company_vat_id']) ? $meta['company_info']['company_vat_id'] : '')
						. '" id="order_contact_company_vat_id" />
							</div>
							<div class="ve_form_row ve_form_half ve_form_half_r">
								<label for="order_contact_company_sk_vat_id">' .__('IČ DPH','mwshop') . '</label>
								<input class="ve_form_text" type="text" name="order_contact[company_info][company_sk_vat_id]" value="'
						. (isset($meta['company_info']['company_sk_vat_id']) ? $meta['company_info']['company_sk_vat_id'] : '')
						. '" id="order_contact_company_sk_vat_id" />
							</div>
  					</div>
					</div>';

					// Country selection changed
					$form .= '
<script>
	jQuery(document).ready(function($) {
		// Hook for country selection change.
		$("#order_contact_country").change(function () {
			var elem = $(this);
			console.log("country changed", elem);
			if (elem.length) {
				var country = elem.first().val();
			} else {
			  var country = "CZ";  
			}
			var elSkVatId = $("#order_contact_company_sk_vat_id");
			if(elSkVatId.length) {
				elSkVatId = elSkVatId.parent(); // use parental wrapper element
				if (country == "SK")
					elSkVatId.removeClass("cms_nodisp");
				else
					elSkVatId.addClass("cms_nodisp");
			}
			if (canSimplifiedInvoice) {
				if(cartTotalPrice <= 10000 && country === "CZ") {
					$("#order_contact_want_invoice_group").show();
					$("#order_contact_want_invoice").removeAttr("onclick");
				} else {
					$("#order_contact_want_invoice_group").hide();
					$("#order_contact_want_invoice")
							.attr("onclick", "return false")
							.prop("checked", true)
					;
					$("#order_contact_invoice_container").show();
				}
			}
		}).change();
	});
</script>
					';

					if ($isShippingRequired) {
						// shipping contact
						$form .= '
							<div>
								<input class="mw_toggle_container_quick" data-target="order_contact_shipping_invoice_container"
									 type="checkbox" name="order_contact[has_shipping_addr]" id="order_contact_shipping_is_invoice">
								<label for="order_contact_shipping_is_invoice">' . __('Doručit na jinou než fakturační adresu', 'mwshop') . '</label>
							</div>';
					}

					if ($isShippingRequired) {
						// shipping contact
						$form .= '
							<div id="order_contact_shipping_invoice_container" class="cms_nodisp">
								<div class="ve_form_row ve_form_row_half">
									<label for="order_contact_shipping_firstname">' . __('Jméno', 'mwshop') . '</label>
									<input class="ve_form_text" type="text" name="order_contact[shipping_address][firstname]" value="'
									. (isset($meta['shipping_address']['firstname']) ? $meta['shipping_address']['firstname'] : '')
									. '" id="order_contact_shipping_firstname" />
								</div>
								<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
									<label for="order_contact_shipping_surname">' . __('Příjmení', 'mwshop') . '</label>
									<input class="ve_form_text" type="text" name="order_contact[shipping_address][surname]" value="'
									. (isset($meta['shipping_address']['surname']) ? $meta['shipping_address']['surname'] : '')
									. '" id="order_contact_shipping_surname" />
								</div>
								<div class="cms_clear"></div>
								<div class="ve_form_row">
									<label for="order_contact_shipping_street">' . __('Ulice', 'mwshop') . '</label>
									<input class="ve_form_text" type="text" name="order_contact[shipping_address][street]" value="'
									. (isset($meta['shipping_address']['street']) ? $meta['shipping_address']['street'] : '')
									. '" id="order_contact_shipping_street" />
								</div>
								<div class="ve_form_row ve_form_row_half">
									<label for="order_contact_shipping_city">' . __('Město', 'mwshop') . '</label>
									<input class="ve_form_text" type="text" name="order_contact[shipping_address][city]" value="'
									. (isset($meta['shipping_address']['city']) ? $meta['shipping_address']['city'] : '')
									. '" id="order_contact_shipping_city" />
								</div>
								<div class="ve_form_row ve_form_row_half ve_form_row_half_r">
									<label for="order_contact_shipping_zip">' . __('PSČ', 'mwshop') . '</label>
									<input class="ve_form_text" type="text" name="order_contact[shipping_address][zip]" value="'
									. (isset($meta['shipping_address']['zip']) ? $meta['shipping_address']['zip'] : '')
									. '" id="order_contact_shipping_zip" />
								</div>
								<div class="ve_form_row">
										<label for="order_contact_shipping_country">' . __('Země','mwshop') .'</label>
										'. mws_generate_country_select('order_contact[shipping_address][country]', 'order_contact_shipping_country', 've_form_text', $meta['shipping_address']['country'], false) .'
								</div>
								<div class="cms_clear"></div>
							</div>
							';
					}

					// Terms and conditions
					$termsUrl = MWS()->getUrl_TermsAndCondtions();

					if (!empty($termsUrl)) {
						$form .= '
							<div class="mws_summarize_terms">
								<input class="" type="checkbox" name="summarize[terms]" value="confirmed" id="summarize_terms"/>
								<label for="summarize_terms">
									'. __('Souhlasím s', 'mwshop')
									. ' '
									. '<a href="' . $termsUrl . '" target="_blank">' . __('obchodními podmínkami', 'mwshop') . '</a>'
									. '.'
									. '
								</label>
							</div>
							';
					} else if (MWS()->edit_mode) {
						$form .= '<div class="cms_error_box">' . sprintf(__('Ještě vám chybí nastavit si stránku s obchodními podmínkami. Stránku lze nastavit %s v základním nastavení eshopu.', 'mwshop'), '<a class="open-setting" data-type="group" data-setting="eshop_option" title="Nastavení eshopu" href="#">' . __('zde', 'mwshop') . '</a>') . '</div>';
					}

					// Purposes
					$purposes = MWS()->gateways()->getDefault()->getPurposes();
					$form .= MWS()->renderPurposes( $purposes );

					if ($canQuickAddToCart) {
						$btnAddToCart = '	<a href="#" class="mws_cart_back_but shop-action" title="' . __('Vložit do košíku', 'mwshop') . '"
									data-operation="mws_cart_add" data-product="' . $productId . '" data-count="1"
									data-backurl="' . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] . '">
  							<span class="ve_but_icon">' . '</span>' . __('Vložit do košíku', 'mwshop')
							. '</a>';
					} else {
						$btnAddToCart = ' ';
					}

					$buttons = '
						<div class="mws_add_to_cart_footer title_element_container">
							' . $btnAddToCart . '
							<a class="mws_cart_but eshop_color_background mws_cart_quickbuy" href="#">' . __('Dokončit objednávku', 'mwshop') . '</a>
							<div class="cms_clear"></div>
            </div>';

					$script .= '
<script>
	console.log("QUICK BUY loaded; price=", price);
</script>'
						;

				} else {
					// Product is not available
					$form = '<div>'.__('Omlouváme se, tento produkt momentálně nelze koupit.', 'mwshop').'</div>';
					$buttons = '
						<div class="mws_add_to_cart_footer title_element_container">
							<a class="mws_cart_back_but mws_close_cart_box mws_cart_quickbuy" href="#">' . __('Zavřít', 'mwshop') . '</a>
							<div class="cms_clear"></div>
            </div>';
				}


				MWS()->current()->showAvailabilityInAdded = true;
				$content = '
					<form id="'.$formId.'">
						<div class="mws_add_to_cart_box">
							<div class="mws_add_to_cart_header">
								' . __('Koupit', 'mwshop') .'
								<a href="#" class="mws_close_cart_box">' . file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true) . '</a>
							</div>
							<div id="mws_flash_messages"></div>
							<div class="mws_quick_buy">
								<div class="mws_add_to_cart_content">
									' . mwsRenderParts('cart', 'added', true) . '
                </div>
                <div class="mws_add_to_cart_form">
										' . $form . '
								</div>
							</div>
						</div>
					</form>
      		' . $buttons . '
      		' . $script . '
					';
				$res['content'] = $content;
			}
		}
    wp_send_json(json_encode($res));
		wp_die();
	}

	public static function orderStep() {
		mwshoplog(__METHOD__, MWLL_DEBUG);

		//String constants
		$text_IntegerExpected = __('Zadejte celé číslo', 'mwshop');

		$step = isset($_REQUEST['curStep']) ? MwsOrderStep::checkedValue((int)$_REQUEST['curStep'],MwsOrderStep::Cart) : MwsOrderStep::Cart;
		$form = isset($_REQUEST['form']) ? $_REQUEST['form'] : '';
		parse_str($form, $formData);
		$nextUrl = isset($_REQUEST['nextUrl']) ? $_REQUEST['nextUrl'] : MWS()->getUrl_Cart($step+1);
		$cart = MWS()->cart;

		$res = array();
		$errors = array();
		$ok = false;

	  // Validate if step is allowed
		$res['deleteErrors'] = true;
		$continue = $cart->areFulfilledPriorSteps($step);
		if($continue) {
			switch ($step) {
				case MwsOrderStep::Cart:
					$ok = !$cart->isEmpty();
					if ($ok) {
						$deleteProductIds = array();
						//Update modifications of count of items
						foreach ($formData['count'] as $productId => $newCountRaw) {
							if (!ctype_digit((string)$newCountRaw)) {
								//Not an integer number as input.
								$errors['count[' . $productId . ']'] = $text_IntegerExpected;
								$ok = false;
							} else {
								$newCount = (int)$newCountRaw;
								/** @var MwsCartItem $item */
								$item = $cart->items->getById($productId);
								if ($item) {
									if ($newCount > 0) {
										$item->count = $newCount;
										if (!$item->checkAvailability()) {
											$errors['count[' . $productId . ']'] = $item->availabilityError;
											$ok = false;
										}
									} else {
										$cart->items->remove($productId);
										$deleteProductIds[] = $productId;
									}
								} else {
									// Line should be be removed
									$deleteProductIds[] = $productId;
								}
							}
						} // for-end
						$res['deleteProductIds'] = $deleteProductIds;
					}
					break;

				case MwsOrderStep::Contact:
					$res = static::validateContactForm($formData, $cart->isShippingRequired());
					$res['deleteErrors'] = true;
					$ok = $res['success'];
					if (!$ok)
						$errors = array_merge($errors, $res['errors']);

					//Note
//				if(empty($formData['order_contact']['note'])) {
//					$errors['order_contact[note]'] = $text_CanNotBeEmpty;
//					$ok = false;
//				}

					$cart->contact = $formData['order_contact'];
					break;

				case MwsOrderStep::Shipping:
					$res = static::validateShippingAndPayment($formData, $cart->isShippingRequired());
					$res['deleteErrors'] = true;
					$ok = $res['success'];
					/** @var MwsShipping $shipping */
					$shipping = null;
					if (!$ok)
						$errors = array_merge($errors, $res['errors']);
					if (isset($res['shipping']))
						$shipping = $res['shipping'];
					if (isset($res['payType']))
						$payType = $res['payType'];

					//Update price
					if ($ok && isset($payType) && isset($shipping)) {
						$cart->shippingPrice = $shipping->getTotalPrice($payType);
					} else {
						$cart->shippingPrice = null;
					}

					$cart->shipping = (isset($shipping) ? $shipping->id : null);
					$cart->payment = (isset($payType) ? $payType : null);
					break;

				case MwsOrderStep::Summarize:
					$ok = false;

					// Validate terms and conditions
					$res = static::validateTermsAndConditions($formData);
					if (!$res['success']) {
						$errors = array_merge($errors, $res['errors']);
					}
					// Validate purposes
					$res = static::validatePurposes( $formData );
					if ( ! $res['success'] ) {
						$errors = array_merge( $errors, $res['errors'] );
						$cart->purposes = null;
					} else {
						$cart->purposes = $res['purposes'];
					}

					if (empty($errors)) {
						$ok = true;
					}
					// Clear all possible errors from UI.
					$res['deleteErrors'] = true;


					// Input of summary is OK
					if ($ok) {
						$ok = false;
						// If prices are fixed within cart...
						if ($cart->isRecounted) {
							//...make an order
							$res = MWS()->gateways()->getDefault()->sharedInstance()->makeOrder($cart);
							mwshoplog('gw_result=' . json_encode($res, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG, 'order');
							if (isset($res['success']) && $res['success']) {
								// Order created
								mwshoplog(sprintf(__('Vytvořena nová objednávka [%s], v.s. [%s].', 'mwshop'),
									isset($res['orderId']) ? (string)$res['orderId'] : '-',
									isset($res['orderNum']) ? (string)$res['orderNum'] : '-'
								), MWLL_INFO, 'order');
								$closeAndClear = true;
								$nextUrl = isset($res['nextUrl']) && !empty($res['nextUrl'])
									? $res['nextUrl']
									: add_query_arg(array('success' => true), MWS()->getUrl_Cart(MwsOrderStep::ThankYou));

								$ok = true;

								// Update statistics
								$cart->incOrderedCount();
							} else {
								// Creating order failed
								$nextUrl = add_query_arg(array('success' => false), MWS()->getUrl_Cart(MwsOrderStep::Summarize));
								$res['flashMessage'] = '<div class="mws_error">'
									. (isset($res['message'])
										? $res['message']
										: __('Objednávku se nepodařilo odeslat. Zopakujte objednání.', 'mwshop')
									)
									. '</div>';
								unset($res['message']); // just to have clean output
								/** @var MwsCartItem $cartItem */
								foreach ($cart->items->data as $cartItem) {
									if (!empty($cartItem->availabilityError))
										$errors[$cartItem->productId] = $cartItem->availabilityError;
								}
								$res['deleteErrors'] = true;
							}
						} elseif ($cart->availabilityErrorsCount) {
							// Not enough items on stock
							$res['deleteErrors'] = false;
							$res['shouldReload'] = true;
						} else {
							// force recount of the cart upon page reload
							$res['shouldReload'] = true;
						}
					}
					break;
			}

			$cart->setFulfilledStep($step, $ok);
			$fulfillment = $cart->getStepsFulfillment();
			$res['stepsFulfilled'] = $fulfillment;

			if(isset($closeAndClear) && $closeAndClear) {
				// Special handling for the last step.
				$cart->clearAll();
			}

			unset($res['purposes']);
			$res['success'] = $ok;
			// On failures disable forwarding redirect. Then error are displayed by JS at client side.
			if($ok) {
				$res['nextUrl'] = $nextUrl;
				wp_send_json_success($res);
			} else {
				unset( $res['nextUrl'] );
				if ( ! empty( $errors ) ) {
					$res['errors'] = $errors;
				}
				wp_send_json_error($res);
			}
		} else {
			//Incorrect usage. Redirect back first step.
			$nextUrl = MWS()->getUrl_Cart(MwsOrderStep::Cart);
			$res['nextUrl'] = $nextUrl;
			wp_send_json_success($res);
		}

		// Saving of session is done at wp_die automatically.

		wp_die();
	}

	/**
	 * Fire synchronization of shop into gateways.
	 */
	public static function gateSyncAll() {
		$nonce = isset( $_REQUEST['nonce'] ) ? $_REQUEST['nonce'] : '';
//		if ( ! wp_verify_nonce( $nonce, MWS_GATEWAY_SYNC_NONCE ) ) {
//			wp_send_json_error( array(
//				'flashMessage' => '<div class="mws_error">' . __( 'Neověřený požadavek.', 'mwshop' ) . '</div>',
//			) );
//		}
		mwshoplog(__METHOD__, MWLL_DEBUG);

		$gwId = isset( $_REQUEST['gatewayId'] ) ? $_REQUEST['gatewayId'] : '';
		$formId = isset( $_REQUEST['formId'] ) ? $_REQUEST['formId'] : -1;

		$gws = MWS()->gateways();
		$gw = $gws->getById($gwId);
		if ($gw) {
			$gw->isSynced = false;
			//TODO Change to MwsGatewayMeta::changeFormId(). Code used here is specific to FAPI.
			$stgs = $gw->loadSettings();
			unset($stgs['form']);
			$stgs['form']['id'] = (int)$formId;
			$gw->saveSettings($stgs);

			$res = $gws->synchronizeAll( $gwId );
			if ( $res ) {
				// Format HTML as generated by field_type_paygate() function.
				$data['html'] = $gw->getSettingsButton(
					"gate_settings_{$gw->id}",
					"gate_settings[{$gw->id}]",
					array()
				);
				$data['id'] = '#mw_eshop_setting_pay_gate_' . $gw->id . '_settings';
				wp_send_json_success($data);
			} else {
				wp_send_json_error();
			}
		}
	}

	/** Process callback hooks from gateway. Registered as non-admin AJAX call. */
	public static function gateCallback() {
		mwshoplog(__METHOD__.'REQUEST='.json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_DEBUG);
		$res = false;

		//Which gateway?
		$gwId = isset($_REQUEST['gw']) ? $_REQUEST['gw'] : '';
		$gws = MWS()->gateways();
		$gw = $gws->getById($gwId);
		if (!$gw) {
			mwshoplog("Paygate callback for invalid gate id [$gwId] received.", MWLL_WARNING);
			wp_send_json_error(array('message' => "Gateway with id=[$gwId] is no recognized."));
			$gw = $gws->getDefault();
		}
		if(!$gw) {
			mwshoplog("No default paygate available to process paygate callback.", MWLL_ERROR);
			mwshoplog("Received callback data: " . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
			wp_send_json_error(array('message' => "No gateway found."));
		}

		//Operation?
		$order = null;
		$operation = isset($_REQUEST['operation']) ? $_REQUEST['operation'] : '';
		try {
			switch ($operation) {
				case 'paid':
					$order = $gw->sharedInstance()->orderPaied();
					//TODO Mark order as paied, fire successive actions like electronic delivery.
					if($order)
						mwshoplog("Order [$order->id/$order->orderNum] marked as PAID by paygate callback.", MWLL_INFO);
					break;

				case 'cancelled':
					$order = $gw->sharedInstance()->orderCancelled();
					//TODO Mark order as cancelled.
					if($order)
						mwshoplog("Order [$order->id/$order->orderNum] marked as CANCELLED by paygate callback.", MWLL_INFO);
					break;

				default:
					mwshoplog("Unsupported operation [$operation] paygate callback.", MWLL_WARNING);
					throw new Exception('Unsupported operation ['.$operation.'].');
			}
		} catch (Exception $e) {
			mwshoplog("Paygate callback failed. Received callback data: " . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
			wp_send_json_error(array('message'=>'Callback failed. ' . $e->getMessage()));
		}

		if($order)
			wp_send_json_success();
		else
			mwshoplog("Paygate callback failed for unknown reason. Received callback data: " . json_encode($_REQUEST, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE), MWLL_ERROR);
			wp_send_json_error(array('message'=>'Some error occured during callback processing. Look into logs for further details.'));
	}

	/**
	 * Validate contact information from the input form.
	 * @param array $formData                Data from input elements.
	 * @param bool $shippingRequired Whether shipping is required. In that case presence of at least one address will be checked.
	 * @return array Returns array with 'success' status (bool). In case of failure the field 'errors' contains list of
	 *                                       errors indexed by name of fields with error with error text as value.
	 */
	public static function validateContactForm($formData, $shippingRequired=false) {
		//String constants
		$text_CanNotBeEmpty = __('Pole je vyžadované', 'mwshop');
		$text_CountryNotSupported = __('Zvolte zemi dostupnou v nabídce.', 'mwshop');

		$countries = MWS()->getSupportedCountries();
		$canSimplifiedInvoice = MWS()->gateways()->getDefault()->getUseSimplifiedInvoice();

		$ok = true;
		$errors = array();
		$flashMsg = array();

		//Total price
		$totalPrice = isset($formData['order_contact']['totalPrice']) ? (float)$formData['order_contact']['totalPrice'] : 0;
		if ($totalPrice == 0) {
			$flashMsg[] = '<div class="mws_error">' . __('Cena objednávky nemůže být nulová.', 'mwshop') . '</div>';
			$ok = false;
		}

		//Email
		if (empty($formData['order_contact']['email'])) {
			$errors['order_contact[email]'] = $text_CanNotBeEmpty;
			$ok = false;
		} elseif (!filter_var($formData['order_contact']['email'], FILTER_VALIDATE_EMAIL)) {
			$errors['order_contact[email]'] = __('Zadaná hodnota není platný email.', 'mwshop');
			$ok = false;
		}

		//Country
		$country = '';
		if (empty($formData['order_contact']['address']['country'])) {
			$errors['order_contact[address][country]'] = $text_CanNotBeEmpty;
			$ok = false;
		} elseif (!array_key_exists($formData['order_contact']['address']['country'], $countries)) {
			$errors['order_contact[address][country]'] = $text_CountryNotSupported;
			$ok = false;
		} else {
			$country = $formData['order_contact']['address']['country'];
		}

		$fullContactRequired = !($canSimplifiedInvoice && $country === 'CZ' && $totalPrice <= 10000);
		//Want invoice
		if ($fullContactRequired && !(isset($formData['order_contact']['want_invoice']) && $formData['order_contact']['want_invoice'])) {
			$flashMsg[] = '<div class="mws_error">'
				. __('Vyplňte prosím fakturační údaje.', 'mwshop')
				. ($canSimplifiedInvoice ? '<br /><br /> ' . __('Zjednodušený daňový doklad je možné vystavit pouze zákazníkům z ČR při objednávce do 10000Kč včetně DPH.', 'mwshop') : '')
				. '</div>';
			$ok = false;
		}

		$invoiceAddressWillBeFilled = false;
		if($fullContactRequired
			|| (isset($formData['order_contact']['want_invoice']) && $formData['order_contact']['want_invoice'])
		) {
			$invoiceAddressWillBeFilled = true;
			//Primary address
			$formData['order_contact']['want_invoice'] = true;
			if (isset($formData['order_contact']['want_invoice']) && filter_var($formData['order_contact']['want_invoice'], FILTER_VALIDATE_BOOLEAN)) {
//					if (empty($formData['order_contact']['address']['firstname'])) {
//						$errors['order_contact[address][firstname]'] = $text_CanNotBeEmpty;
//						$ok = false;
//					}
				if (empty($formData['order_contact']['address']['surname'])) {
					$errors['order_contact[address][surname]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
//					if (empty($formData['order_contact']['address']['phone'])) {
//						$errors['order_contact[address][phone]'] = $text_CanNotBeEmpty;
//						$ok = false;
//					}
				if (empty($formData['order_contact']['address']['street'])) {
					$errors['order_contact[address][street]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				if (empty($formData['order_contact']['address']['city'])) {
					$errors['order_contact[address][city]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				if (empty($formData['order_contact']['address']['zip'])) {
					$errors['order_contact[address][zip]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
			}

			//Company info
			if (isset($formData['order_contact']['is_company']) && filter_var($formData['order_contact']['is_company'], FILTER_VALIDATE_BOOLEAN)) {
				if (empty($formData['order_contact']['company_info']['company_name'])) {
					$errors['order_contact[company_info][company_name]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				if (empty($formData['order_contact']['company_info']['company_id'])) {
					$errors['order_contact[company_info][company_id]'] = $text_CanNotBeEmpty;
					$ok = false;
				}
				// VAT ID
	//					if(empty($formData['order_contact']['company_info']['company_vat_id'])) {
	//						$errors['order_contact[company_info][company_vat_id]'] = $text_CanNotBeEmpty;
	//						$ok = false;
	//					}
				// SK VAT ID
//				if(isset($formData['order_contact']['address']['country']) && $formData['order_contact']['address']['country'] == 'SK') {
//					if (empty($formData['order_contact']['company_info']['company_sk_vat_id'])) {
//						$errors['order_contact[company_info][company_sk_vat_id]'] = $text_CanNotBeEmpty;
//						$ok = false;
//					}
//				}
			}
		}


		//Secondary address
		$shippingAddrWillBeFilled = false;
		if (isset($formData['order_contact']['has_shipping_addr']) && filter_var($formData['order_contact']['has_shipping_addr'], FILTER_VALIDATE_BOOLEAN)) {
			$shippingAddrWillBeFilled = true;
//			if (empty($formData['order_contact']['shipping_address']['firstname'])) {
//				$errors['order_contact[shipping_address][firstname]'] = $text_CanNotBeEmpty;
//				$ok = false;
//			}
			if (empty($formData['order_contact']['shipping_address']['surname'])) {
				$errors['order_contact[shipping_address][surname]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
//			if (empty($formData['order_contact']['shipping_address']['phone'])) {
//				$errors['order_contact[shipping_address][phone]'] = $text_CanNotBeEmpty;
//				$ok = false;
//			}
			if (empty($formData['order_contact']['shipping_address']['street'])) {
				$errors['order_contact[shipping_address][street]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
			if (empty($formData['order_contact']['shipping_address']['city'])) {
				$errors['order_contact[shipping_address][city]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
			if (empty($formData['order_contact']['shipping_address']['zip'])) {
				$errors['order_contact[shipping_address][zip]'] = $text_CanNotBeEmpty;
				$ok = false;
			}
			if (empty($formData['order_contact']['shipping_address']['country'])) {
				$errors['order_contact[shipping_address][country]'] = $text_CanNotBeEmpty;
				$ok = false;
			} elseif(!array_key_exists($formData['order_contact']['shipping_address']['country'], $countries)) {
				$errors['order_contact[shipping_address][country]'] = $text_CountryNotSupported;
				$ok = false;
			}
		}

		//Check that at least one address is filled when shipping is necessary.
		if($shippingRequired && !$shippingAddrWillBeFilled && !$invoiceAddressWillBeFilled) {
			$errors['order_contact_shipping_is_invoice'] = __('Pro doručení je potřeba zadat fakturační anebo doručovací adresu.', 'mwshop');
			$flashMsg[] = '<div class="mws_error">' . __('Pro doručení zboží je potřeba zadat fakturační anebo doručovací adresu.', 'mwshop') . '</div>';
			$ok = false;
		}

		$res = array('success' => $ok, 'errors' => $errors);
		if (!empty($flashMsg)) {
			$res['flashMessage'] = $flashMsg;
		}
		return $res;
	}

	/**
	 * Validate form input for terms and conditions.
	 * @param $formData
	 * @return array Bool value of "success" tells whether validation is ok.
	 *               Value of "errors" contains array with error messages indexed by "HTML input name" attribute.
	 */
	public static function validateTermsAndConditions($formData) {
		if (!isset($formData['summarize']['terms']) || $formData['summarize']['terms'] !== 'confirmed') {
			$res['errors']['summarize[terms]'] = __('Bez souhlasu s obchodními podmínkami není možné objednávku dokončit.', 'mwshop');
			$res['success'] = false;
		} else {
			$res['success'] = true;
		}
		return $res;
	}

	/**
	 * Validate form input for purposes.
	 * @param $formData
	 * @return array Bool value of "success" tells whether validation is ok.
	 *               Value of "errors" contains array with error messages indexed by "HTML input name" attribute.
	 */
	public static function validatePurposes($formData) {
		//
		/** @var $purposes array purposes[purposeId] = [checked, text, isPrimary] */
		$purposes = (isset($formData['purposes']) && is_array( $formData['purposes'])) ? $formData['purposes'] : array();
		foreach ( $purposes as $id => $purpose ) {
			$purposes[$id]['isPrimary'] = $purpose['isPrimary']
				= isset( $purpose['isPrimary'] ) && (bool) filter_var($purpose['isPrimary'], FILTER_VALIDATE_BOOLEAN);
			if($purpose['isPrimary']) {
				$purposes[$id]['checked'] = true;
			} else {
				$purposes[ $id ]['checked'] = (isset($purpose['checked']) && $purpose['checked']);
			}
			if (!isset($purpose['text']) || empty($purpose['text'])) {
				$res['errors']['purposes']['id']['checked'] = __('V odeslané objednávce chybí text právního účelu sběru osobních údajů.', 'mwshop');
			}
		}
		if (isset($res['errors']) && !empty($res['errors'])) {
			$res['success'] = false;
		} else {
			$res['success'] = true;
			$res['purposes'] = $purposes;
		}
		return $res;
	}

	/**
	 * Validate form input for shipping and payment.
	 * @param $formData
	 * @param bool|true $mustHaveShipping
	 * @return array Value of "success" tells whether validation is ok. Value of "errors" contains array with error messages
	 *               indexed by HTML input name attribute. Optionally "shipping" {@link MwsShipping} and "payType"
	 *							 {@link MwsPayType} values are filled.
	 */
	public static function validateShippingAndPayment($formData, $mustHaveShipping=true) {
		//String constants
		$text_MakeSelectionPayment = __('Zvolte platební metodu', 'mwshop');
		$text_MakeSelectionShipping = __('Zvolte způsob doručení', 'mwshop');
		$text_InvalidValue = __('Zadaná hodnota není platná', 'mwshop');

		$ok = true;
		$errors = array();

		//Shipping
		if ($mustHaveShipping) {
			if (empty($formData['mws_shipping']) || $formData['mws_shipping']=="0") {
				$errors['mws_shipping'] = $text_MakeSelectionShipping;
				$ok = false;
			} else {
				$shippingId = (int)$formData['mws_shipping'];
				// Non electronic delivery is forbidden.
				if ($shippingId === MwsShippingElectronic::id) {
					$errors['mws_shipping'] = __('Pro vaši objednávku není možné použít elektronické doručení, neboť obsahuje produkty vyžadující doručení.', 'mwshop');
					$ok = false;
				} else {
					$post = get_post($shippingId);
					if (is_null($post)) {
						$errors['mws_shipping'] = __('Zvolený způsob dopravy není dostupný.', 'mwshop');
						$ok = false;
					} else {
						try {
							$shipping = MwsShipping::getById($post->ID);
						} catch (Exception $e) {
							$shipping = null;
						}
					}
				}
			}
		} else {
			// No physical shipping. Only electronical shipping is allowed.
			$shippingId = isset($formData['mws_shipping']) ? (int)$formData['mws_shipping'] : null;
			// Non electronic delivery is forbidden.
			if (!is_null($shippingId) && $shippingId !== MwsShippingElectronic::id) {
				$errors['mws_shipping'] = __('Pro vaši objednávku je přípustný pouze elektronický způsob doručení.', 'mwshop');
				$ok = false;
			} else {
				$shipping = MwsShippingElectronic::getInstance();
			}
		}

		//Payment
		if (empty($formData['mws_payment']) || $formData['mws_payment']=="0") {
//			mwdbg('mws_payment=(empty)');
			$errors['mws_payment'] = $text_MakeSelectionPayment;
			$ok = false;
		} else {
//			mwdbg('mws_payment='.$formData['mws_payment']);
			$payType = MwsPayType::checkedValue($formData['mws_payment']);
			if (is_null($payType)) {
				$errors['mws_payment'] = $text_InvalidValue;
				$ok = false;
			} elseif (!in_array($payType, MWS()->gateways()->getDefault()->getEnabledPayTypes())) {
				$errors['mws_payment'] = __('Zvolený způsob platby není dostupný.', 'mwshop');
				$ok = false;
			} elseif (isset($shipping) && $payType === MwsPayType::Cod && !$shipping->isCodSupported) {
				//Check COD delivery only in case shipping method is valid and no other errors concerning payType were detected.
				$errors['mws_payment'] = __('Zvolený způsob platby není přípustný pro zvolenou dopravu.', 'mwshop');
				$ok = false;
			}
		}

		$arr = array('success'=>$ok, 'errors'=>$errors);
		if(isset($shipping))
			$arr['shipping'] = $shipping;
		if(isset($payType))
			$arr['payType'] = $payType;
		return $arr;
	}

	public static function shopActivation() {
      echo '
      <div class="cms_half_setting_block_content cms_half_setting_block_content_first">
          <div class="cms_setting_info_head cms_center">
              <h2>'.__('Mám FAPI účet','mwshop').'</h2>
              <p>'.__('Zadejte přihlašovací jméno a API klíč pro spojení s vašim FAPI.','mwshop').'</p>
          </div>
          <div class="mw_fapi_connection_form">
              <div id="mw_simple_connection_error_container"></div>
              <div class="set_form_row ">
                  <div class="label">'.__('Přihlašovací jméno (e-mail)','mwshop').'</div>
                  <input class="cms_text_input " type="text" name="fapi_login" id="fapi_login">
              </div>
              <div class="set_form_row ">
                  <div class="label">'.__('API klíč','mwshop').'</div>
                  <input class="cms_text_input " type="text" name="fapi_password" id="fapi_password">
                  <span class="cms_description"><a target="_blank" href="https://web.fapi.cz/account-settings/api-tokens?projectId=all">'.__('Získat API klíč z FAPI','mwshop').'</a></span>
              </div>
              <div class="set_form_row cms_center">
                  <button class="cms_button cms_lightbox_main_but mw_save_fapi_connection" target="_blank" href="" type="submit">'.__('Propojit s FAPI','mwshop').'</button>
              </div>
          </div>
      </div>
      <div class="cms_half_setting_block_content">
          <div class="cms_setting_info_head cms_center">
              <h2>'.__('Nemám FAPI účet','mwshop').'</h2>
              <p>'.__('Vytvořte si FAPI účet zdarma.','mwshop').'</p>
          </div>
          <div class="set_form_row cms_center">
              <a target="_blank" href="https://fapi.cz/objednavka-novy-billing/?revenue=0" class="cms_button cms_lightbox_main_but" type="submit">'.__('Vytvořít FAPI účet','mwshop').'</a>
          </div>
      </div>
      <div class="cms_clear"></div>
      <input type="hidden" value="0" id="mw_fapi_connection_status" name="mw_fapi_connection_status" /> 
      <input type="hidden" value="'.get_home_url().'/?create_mw_eshop=1" id="mw_fapi_connection_url_redirect" name="mw_fapi_connection_url_redirect" /> 
      ';

      die();
	}

}
