<?php
/**
 * Some general routines for templates.
 * Date: 18.02.16
 * Time: 15:14
 */

/**
 * Returns <code>true</code> when function is called during autosave or when post is not published. This is useful
 * for hooks to detect premature calls form WP API.
 * @param int|WP_Post $postId
 * @return bool
 */
function mwsIsPostAutosaveOrUnpublished($postId) {
	if(wp_is_post_autosave($postId)) {
		return true;
	} else {
		$postStatus = get_post_status($postId);
		if(!$postStatus || $postStatus != 'publish')
			return true;
	}
	return false;
}

/**
 * Format price of the product with currency unit. If price equals zero then result is "for free".
 * @param float $price Value of price to print.
 * @param null|string $unit Unit appended to price. Empty string means "no unit". Default null value means "use global currency unit".
 * @param bool $use0text When true and $price==0 then "free" text will be printed.
 * @param null|string $divCSS If not null then result will be wrapped within SPAN element and value of this parameter
 *                            will be used as value of element's CSS "class" attribute.
 * @return string
 */
function htmlPriceSimple($price, $unit=null, $use0text=false, $divCSS=null, $afterText='', $beforeText='') {
  $unit = is_null($unit) ? MWS()->getCurrency() : $unit;

  $dec=((float)$price!=floor($price))? 2: 0;  
  $price = number_format(round((float)$price,2),$dec, ',', ' ');
  $price = str_replace(" ", "&nbsp;", $price);
  $res = ($price==0 && $use0text)
    ? '<span class="num">'.__('zdarma', 'mwshop').'</span>'
    : '<span class="num">'.$price.'</span>' . (!empty($unit)?'&nbsp;' . $unit : '')
  ;
  if (!is_null($divCSS) && !empty($res))
    $res = '<span class="'.$divCSS.'"><span class="mw_before_price">'.$beforeText.'</span>'.$res.$afterText.'</span>';
  return $res;
}

function htmlPriceSimpleIncluded($price, $unit=null, $use0text=true, $divCSS=null, $beforeText='') {
  return htmlPriceSimple($price, $unit, $use0text,
    'mws_price_vatincluded title_element_container'.(!empty($divCSS)?' '.$divCSS:''), '', $beforeText
  );
}

function htmlPriceSimpleExcluded($price, $unit=null, $use0text=false, $divCSS=null) {
	if (MWS()->getVATs()->isUsingVatAccounting())
		return htmlPriceSimple($price, $unit, $use0text,
			'mws_price_vatexcluded'.(!empty($divCSS)?' '.$divCSS:''),' '.__('bez&nbsp;DPH', 'mwshop')
		);
	else
		return '';
}

function htmlPriceSimpleSale($priceSale, $priceNormal, $unit=null, $divCSS=null) {
  $discount = ($priceNormal>0) ? round($priceSale/$priceNormal*100,0) : 0;
  if($discount == 0)
    return '';
  else {
    $unit = is_null($unit) ? MWS()->getCurrency() : $unit;
    $res = '<span>'.$discount .'%</span>';
    $res .= ' <span><span class="num">'.$priceNormal.'</span>' . (!empty($unit)?'&nbsp;' . $unit : '') .'</span>';

    $res = '<span class="mws_price_sale'.(!empty($divCSS)?' '.$divCSS:'').'">'.$res.'</span>';
    return $res;
  }
}

/**
 * Generate edit button for administration using a link. Open the link in specified target window, new window as default.
 * @param string $caption Text of the button
 * @param string $link HREF
 * @param string $css Optional CSS class.
 * @param bool $large User big or small button? Default is big.
 * @param string $targetWindow Default HTML target.
 * @return string
 */
function htmlAdminEditButton($caption, $link, $css='', $large=true, $targetWindow='_blank') {
	if(!empty($link))
		return
			'<a href="'.$link.'" class="button'.($large ? ' button-large':'').' '.$css.'"'
			.(!empty($targetWindow) ? ' target="'.$targetWindow.'"' : '')
			.'>'
			.esc_html($caption)
			.'</a>'
			;
	else
		return '';
}

/**
 * Generate edit button for administration using a link. Open the link in shared target window.
 * @param string $caption Text of the button
 * @param string $link HREF
 * @param string $css Optional CSS class.
 * @param bool $large User big or small button? Default is big.
 * @return string
 */
function htmlAdminEditButton_SharedWindow($caption, $link, $css='', $large=true) {
  return htmlAdminEditButton($caption, $link, $css, $large, MW_HREF_TARGET_SHARED_ADMIN_EDIT);
}

/**
 * Strip decimal places up to count of $decimals.
 * @param $float
 * @param int $decimals
 * @return float
 */
function floordec($float,$decimals=2){
	return floor($float*pow(10,$decimals))/pow(10,$decimals);
}

/**
 * Returns the timezone string for a site, even if it's set to a UTC offset
 *
 * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
 * Credit to https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
 *
 * @return string valid PHP timezone string
 */
function wp_get_timezone_string() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) )
		return $timezone;

	// get UTC offset, if it isn't set then return UTC
	if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
		return 'UTC';

	// adjust UTC offset from hours to seconds
	$utc_offset *= 3600;

	// attempt to guess the timezone string from the UTC offset
	if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
		return $timezone;
	}

	// last try, guess timezone string manually
	$is_dst = date( 'I' );

	foreach ( timezone_abbreviations_list() as $abbr ) {
		foreach ( $abbr as $city ) {
			if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
				return $city['timezone_id'];
		}
	}

	// fallback to UTC
	return 'UTC';
}

/**
 * Convert datetimestring in WP local timezone into UTC timestamp.
 * @param string $dateTimeLocalString Datetime string in WP timezone, e.g. as set in admin interace.
 * @return string
 * @throws Exception conversion error, mostly incorrect format of datetimestring
 */
function mwConvDateTimeLocal2TimestampUTC($dateTimeLocalString) {
	try {
		// get datetime object from site timezone
		$datetime = new DateTime($dateTimeLocalString, new DateTimeZone( wp_get_timezone_string()));
		// get the unix timestamp (adjusted for the site's timezone already)
		$timestamp = $datetime->format('U');
		return $timestamp;
	} catch ( Exception $e ) {
		// you'll get an exception most commonly when the date/time string passed isn't a valid date/time
		throw $e;
	}
}

/**
 * Convert datetimestring in UTC timezone into UTC timestamp.
 * @param string $dateTimeUTCString Datetime string in UTC, e.g. $post->post_date_gmt
 * @return string
 * @throws Exception conversion error, mostly incorrect format of datetimestring
 */
function mwConvDateTimeUTC2TimestampUTC($dateTimeUTCString) {
	try {
		// get datetime object from site timezone
		$datetime = new DateTime($dateTimeUTCString, new DateTimeZone('UTC'));
		// get the unix timestamp (adjusted for the site's timezone already)
		$timestamp = $datetime->format('U');
		return $timestamp;
	} catch ( Exception $e ) {
		// you'll get an exception most commonly when the date/time string passed isn't a valid date/time
		throw $e;
	}
}

/**
 * Convert Unix timestamp in UTC into Unix timestamp in WP local timezone.
 * @param $timestampUTC
 * @return mixed
 * @throws Exception
 */
function mwConvTimestampUTC2TimestampLocal($timestampUTC) {
	try {
		// get datetime object from unix timestamp
		$datetime = new DateTime("@{$timestampUTC}", new DateTimeZone('UTC'));
		// set the timezone to the site timezone
		$datetime->setTimezone(new DateTimeZone(wp_get_timezone_string()));
		// return the unix timestamp adjusted to reflect the site's timezone
		return ($timestampUTC + $datetime->getOffset());
	} catch (Exception $e) {
		// something broke
		throw $e;
	}
}

/**
 * Convert timestamp into string representation of date according to WP settings. Before it is printed it is converted into local
 * timezone.
 * @param int $timestamp       Unix timestamp, in UTC defaultly.
 * @param bool $convertFromUTC Determines, whether $timestamp is in UTC (default) or not, meaning already converted to local
 *                      timezone.
 * @return string
 */
function mwFormatAsDate($timestamp, $convertFromUTC = true) {
	if($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}
	return date_i18n(get_option('date_format'), $timestamp);
}

/**
 * Convert timestamp into string representation of time according to WP settings. Before it is printed it is converted into local
 * timezone.
 * @param int $timestamp       Unix timestamp, in UTC defaultly.
 * @param bool $convertFromUTC Determines, whether $timestamp is in UTC (default) or not, meaning already converted to local
 *                      timezone.
 * @return string
 */
function mwFormatAsTime($timestamp, $convertFromUTC = true) {
	if($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}
	return date_i18n(get_option('time_format'), $timestamp);
}

/**
 * Convert timestamp into string representation of date and time according to WP settings. Before it is printed it is converted into local
 * timezone.
 * @param int $timestamp       Unix timestamp, in UTC defaultly.
 * @param bool $convertFromUTC Determines, whether $timestamp is in UTC (default) or not, meaning already converted to local
 *                      timezone.
 * @return string
 */
function mwFormatAsDateTime($timestamp, $convertFromUTC = true) {
	if($convertFromUTC) {
		$timestamp = mwConvTimestampUTC2TimestampLocal($timestamp);
	}
	return date_i18n(get_option('date_format') . ' ' .get_option('time_format'), $timestamp);
}

/**
 * Extract datetime value as timestamp from MioWeb "datetime field". If settings are empty or invalid, value of
 * $defaultTimestamp is use for date part, 0 is used for hours and minutes.
 * @param array $array          Array with field values from the field type "datetime" of Mioweb. Value is expected to be
 *                              in local timezone.
 * @param int $defaultTimestamp Unix timestamp local timezone for the case the $array settings are not correct. It is used only for the
 *                              "date" part. If argument is not set then today midnight is used (=start of the day).
 * @return int Unix epoch timestamp UTC.
 */
function mwExtractDateTimeFromField($array, $defaultTimestamp = -1) {
	$tz = new DateTimeZone(wp_get_timezone_string());
	try {
		if (!isset($array['date']) || empty($array['date'])) {
			if ($defaultTimestamp === -1) {
				$dt = new DateTime('today midnight', $tz);
				$timestampLocal = $dt->format('U');
			} else {
				$timestampLocal = $defaultTimestamp;
			}
		} else {
			$dt = new DateTime((string)$array['date'], $tz);
			$timestampLocal = $dt->format('U');
		}

//		$timestampLocal = !isset($array['date']) || empty($array['date'])
//			? ($defaultTimestamp === -1
//				? (new DateTime('today midnight', $tz))->format('U')
//				: $defaultTimestamp)
//			: (new DateTime((string)$array['date'], $tz))->format('U');
	} catch (Exception $e) {
		$timestampLocal = $defaultTimestamp;
	}
	if(!$timestampLocal) $timestampLocal = $defaultTimestamp;
	$timestampLocal += (isset($array['hour']) && !empty($array['hour'])) ? ((int)$array['hour']) * 3600 : 0;
	$timestampLocal += (isset($array['minute']) && !empty($array['minute'])) ? ((int)$array['minute']) * 60 : 0;
	$date = new DateTime("@{$timestampLocal}", $tz);
	$date->setTimezone(new DateTimeZone('UTC'));
	return $date->format('U');
}

/**
 * Returns true whe viewing single product.
 * @return bool
 */
function mwsIsProduct(){
	return is_singular(array(MWS_PRODUCT_SLUG));
}

/**
 * Returns true when viewing single order.
 * @return bool
 */
function mwsIsOrder(){
	return is_singular(array(MWS_ORDER_SLUG));
}

/**
 * Returns true when viewing products or shop page.
 * @return bool
 */
function mwsIsShop(){
	return (is_post_type_archive(MWS_PRODUCT_SLUG));  //TODO doplnit podmínku že aktuální stránka je home eshopu a nebo objednávky
}

function mwsRenderParts($slug, $name='', $toString=false) {
	$str = MWS()->renderTplParts($slug, $name, $toString);
	if($toString)
		return $str;
}

// paygate selection
function field_type_paygate($field, $meta, $group_id) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
	if(!$content) $content=array(MWS()->gatewaySelectedId);

	$options = array();
	$gates = MWS()->gateways();
	/** @var MwsGatewayMeta $gate */
	foreach ($gates->items as $gate) {
		$options[$gate->id] = array(
			'value'=> $gate->id,
			'id' => $group_id.'_'.$field['id'].'_'.$gate->id,
			'name' => $gate->caption,
			'settings' => $gate->getSettingsButton(
				"gate_settings_{$gate->id}",
				"gate_settings[{$gate->id}]",
//				$field['id'].'_'.$gate->id, //id
//				"{$group_id}[{$field['id']}][{$gate->id}_settings]", //name
				(isset($content[$gate->id.'_settings']) ? $content[$gate->id.'_settings'] : array())
				),
		);
	}

	// Print radiobuttons for each gateway type
	$gateStgs = '';
	foreach ($options as $key=>$option) {
		echo '<div>' .
			'<input type="radio" id="'.$option['id'].'" '. //,$field['id'],'_',$option['value'],'" ' .
			'name="'.$group_id.'['.$field['id'].'][selected]'.
//			'['.$option['value'].']'. //for checkbox = multiple gateways enabled
      '" ' .
			'value="',$option['value'],'"',
			in_array($option['value'],$content) ? ' checked="checked"' : '',
			' />';
		echo '<label for="'.$option['id'].'"> '.$option['name'].'</label>';
		if(!empty($option['settings']))
			$gateStgs .= '<div id="'.$option['id'].'_settings" class="'.$option['id'].'">'
//				.'<div class="label">'.htmlspecialchars(sprintf(__('Konfigurace %s', 'mwshop'), $option['name'])).'</div>'
				.$option['settings']
				.'</div>';
		echo '</div>';
	}

	// Print special settings for all gateways
	if(!empty($gateStgs)) {
		echo '<div class="mws_gate_settings_form">'
			. $gateStgs
			. '</div>'
			;
	}
}

// static printout of selected payment gateway
function field_type_paygate_selected($field, $meta, $group_id) {
	$gates = MWS()->gateways();
	$gw = $gates->getDefault();
	if(is_null($gw))
		$content = '<div class="cms_info_box_gray">'.
			__('Před nastavením platebních metod vyberte platební bránu na záložce "ESHOP/Základní nastavení".','mwshop').
			'</div>';
	else
		$content = '<div>'.$gw->caption.'</div>';

	echo $content;
}

// setting of enabled payment methods
function field_type_payment_methods($field, $meta, $group_id) {
	$gates = MWS()->gateways();
	/** @var MwsGatewayMeta $gw */
	foreach ($gates->items as $gw) {
		$supported = $gw->getSupportedPayTypes();
		$enabled = $gw->getEnabledPayTypes();
		if (count($supported) > 0) {
			$baseId = "gate_settings_payments_{$gw->id}";
			$baseName = "gate_settings[{$gw->id}][payments]";
//		$baseId = "$group_id_payments";
//		$baseName = "$group_id[payments]";
			echo '<div class="gate_settings_payments '.$baseId.'">';
			foreach ($supported as $type) {
				echo '<div><input type="checkbox" ' .
//			' id="'.$baseId."_$type".'" ' .
					' name="' . $baseName . '[]" ' .
					' value="' . htmlspecialchars($type) . '" ' .
					(in_array($type, $enabled) ? ' checked="checked" ' : '') .
					'>' .
					MwsPayType::getCaption($type) .
					'</input></div>';
			}
			echo '</div>';
		}
	}
}

/** Select box to select currency from the list of supported currencies. */
function field_type_currency($field, $meta, $group_id, $group_name) {
	$baseId = $group_id.'_'.$field['id'];
	$baseName = $group_name . '[' . $field['id'] . ']';
	$content = (isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
	$content = MwsCurrency::checkedValue($content, MwsCurrency::getDefault());
	if(isset($field['show'])) { ?>
      <script>
          jQuery(document).ready(function($) {
              $("#<?php echo $baseId; ?>").change(
                  function(){
                      var value=$(this).val();
                      $(".cms_show_group_<?php echo $group_id.'_'.$field['show']; ?>").hide();
                      $(".cms_show_group_<?php echo $group_id.'_'.$field['show']; ?>_"+value).show();
                  });
          });
      </script>
      <style>
          .cms_show_group_<?php echo $group_id.'_'.$field['show']; ?>:not(.cms_show_group_<?php echo $group_id.'_'.$field['show']; ?>_<?php echo $content ?>) {display: none;}
      </style>
	<?php }
	if(empty($field['options'])) {
		$arr = array();
		foreach (MwsCurrency::getAll() as $val) {
			$arr[] = array(
				'name' => esc_html(MwsCurrency::getCaption($val)),
				'value' => esc_attr($val),
			);
		}
		$field['options'] = $arr;
	}

	cms_generate_field_select($baseName, $baseId, $content, $field, 'mws_currency_select');
}

/** Currency conversion editors. */
function field_type_currency_conversion($field, $meta, $group_id, $group_name) {
	$baseId = $group_id.'_'.$field['id'];
	$baseName = $group_name . '[' . $field['id'] . ']';
	$content = (isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');

	$primaryCurrency = MWS()->getCurrency('key');
	$secondaryCurrency = ($primaryCurrency == MwsCurrency::czk ? MwsCurrency::eur : MwsCurrency::czk);
	$from = isset($field['from']) ? MwsCurrency::checkedValue($field['from'], $primaryCurrency) : $primaryCurrency;
	$to = isset($field['to']) ? MwsCurrency::checkedValue($field['to'], $secondaryCurrency) : $secondaryCurrency;

	echo '<div class="currency_conversion"><span class="value">1</span> <span class="unit">'.MwsCurrency::getSymbol($from).'</span>'
        . ' = ';
	$field['placeholder'] = MwsCurrency::getDefaultConversion($from, $to);
	if(!isset($field['step'])) {
	    $field['step'] = 0.1;
    }
    $field['min'] = 0;
	field_type_number($field, $content, $group_name, $group_id);
	echo ' '.MwsCurrency::getSymbol($to).'</div>';
}

// company info (ID, VAT ID, name)
function field_type_company_info($field, $meta, $group_id, $group_name) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: array());
	if(!is_array($content))
		$content = array();

	$baseId = $group_id.'_'.$field['id'];
	$baseName = $group_name . '[' . $field['id'] . ']';

	$i = 'company_name'; $field['placeholder']=__('Název společnosti', 'mwshop');
	echo '<div class="">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'', $field)
		. '</div>';
	echo '</div>';

	echo '<div class="cms_clear"></div>';

	$i = 'company_id'; $field['placeholder']=__('IČ', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'mw_company_id', $field)
		. '</div>';
	echo '</div>';

	$i = 'company_vat_id'; $field['placeholder']=__('DIČ', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'mw_company_vat_id', $field)
		. '</div>';
	echo '</div>';

	echo '<div class="cms_clear"></div>';
}

// order address (firstname,...,city)
function field_type_order_address($field, $meta, $group_id, $group_name) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: array());
	if(!is_array($content))
		$content = array();

	$baseId = $group_id.'_'.$field['id'];
	$baseName = $group_name . '[' . $field['id'] . ']';

	$i = 'firstname'; $field['placeholder']=__('Jméno', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
		$baseName."[$i]", $baseId."_$i",
		isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
		'', $field)
		. '</div>';
	echo '</div>';

	$i = 'surname'; $field['placeholder']=__('Příjmení', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'', $field)
		. '</div>';
	echo '</div>';

	$i = 'phone'; $field['placeholder']=__('Telefon', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'', $field)
		. '</div>';
	echo '</div>';

	$i = 'street'; $field['placeholder']=__('Ulice', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'', $field)
		. '</div>';
	echo '</div>';

	$i = 'city'; $field['placeholder']=__('Město', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'', $field)
		. '</div>';
	echo '</div>';

	$i = 'zip'; $field['placeholder']=__('PSČ', 'mwshop');
	echo '<div class="float-setting">'
		. '<label class="sublabel" for="'.$baseId."_$i".'">'.$field['placeholder'].'</label>'
	;
	echo '<div>'
		. cms_generate_field_text(
			$baseName."[$i]", $baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'number number_int', $field)
		. '</div>';
	echo '</div>';


	echo '<div class="cms_clear"></div>';

}

// global VAT values
function field_type_vatvalues($field, $meta, $group_id) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: array());
	if(!is_array($content))
		$content = array();

	$baseId = $group_id.'_'.$field['id'];
	$baseName = $group_id . '[' . $field['id'] . ']';

	//Make sure that in case that VAT is used then the first VAT level is defined. This hack is just for better UI clarity.
	if(!isset($content[0]) && !empty($content[0])) $content[0]=MWS()->getVATs()->getValueDefault(true, null);

	$field['placeholder'] = __('nepoužívat', 'mwshop');
	for($i=0; $i<5; $i++) {
		echo '<div class="vat-values">';
		echo '<label class="label" for="'.$baseId."_$i".'">'.sprintf(__('Sazba %d'), $i+1).'</label>';
		echo '<div>';
		echo cms_generate_field_text(
			$baseName."[$i]",
			$baseId."_$i",
			isset($content[$i]) && (!empty($content[$i]) || is_numeric($content[$i])) ? htmlspecialchars(stripslashes($content[$i])) : null,
			'number number_int',
			$field);
		echo '%</div></div>';
	}
	echo '<div class="cms_clear"></div>';
}

// product VAT selection from global values
function field_type_vat_select($field, $meta, $group_id, $group_name) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: 0);

	$vats = MWS()->getVATs();
	if(!$vats->hasValues()) {
		field_type_info(array('content'=>
			__('Účtování s DPH není aktivní. Chcete-li účtovat s DPH, zadejte sazby DPH v globálním nastavení obchodu.', 'mwshop')),
			null, null);
		return;
	}

	$items = $vats->toArray();
	$options = array();
	foreach ($items as $vatId => $vatValue) {
		if(!is_null($vatValue))
			$options[]= array(
				'value'=>$vatId,
				'name'=>(empty($vatValue)?'0':(int)$vatValue).'%'
				//TODO Comment out following line to hide VAT level within select box.
				.' ('.sprintf(__('sazba %d'), $vatId+1).')'
			);
	}
	$field['options']=$options;

	cms_generate_field_select(
		$group_name.'['.$field['id'].']',
		$group_id.'_'.$field['id'],
		$content, $field);
}

// category select
function field_type_shop_category_select($field, $meta, $group_name, $group_id) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: 0);


  $items = get_categories( array('taxonomy' => MWS_PRODUCT_CAT_SLUG, 'hide_empty'=>0 )); 
	$options = array();
  $options[]= array(
				'value'=>'',
				'name'=>__('- Vyberte kategorii -','mwshop')
      );
	foreach ($items as $val) {
			$options[]= array(
				'value'=>$val->term_id,
				'name'=>$val->name
      );
	}
	$field['options']=$options;

	cms_generate_field_select(
		$group_name.'['.$field['id'].']',
		$group_id.'_'.$field['id'],
		$content, $field);
}
// product select
function field_type_product_select($field, $meta, $group_name, $group_id) {
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: 0);

  $args = array ( 'post_type' => MWS_PRODUCT_SLUG, 'posts_per_page' => -1 );
  $items = new WP_Query( $args );
	$options = array();
  $options[]= array(
				'value'=>'',
				'name'=>__('- Vyberte produkt -','mwshop')
      );
	foreach ($items->posts as $val) {
			$options[]= array(
				'value'=>$val->ID,
				'name'=>$val->post_title
      );
	}
	$field['options']=$options;

	cms_generate_field_select(
		$group_name.'['.$field['id'].']',
		$group_id.'_'.$field['id'],
		$content, $field);
}

function field_type_product_properties($field, $meta, $group_name, $group_id) {
	$content = (isset($meta)) ? $meta : array();

	$id = $group_id . '_' . $field['id'];
	$name = $group_name . '[' . $field['id'] . ']';

	$propDefs = MwsProperty::getAll();
	if (count($propDefs)) {
		echo '<table>';
		/** @var MwsProperty $property */
		foreach ($propDefs as $property) {
			$propId = $property->id;
			$htmlId = $id . '_' . $propId;
			echo '<tr>';
			echo '	<td><label for="' . $htmlId . '">' . $property->name . '</label></td>';
			echo '	<td>' . $property->htmlEditor($name . '[' . $propId . ']', $htmlId,
					isset($content[$propId]) ? $content[$propId] : null,
					'cms_text_input cms_text_input_s', '', '', false, true)
				. '</td>';
			echo '	<td>' . $property->unit . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else echo '<div class="cms_info_box_gray">' . __('Nejsou vytvořeny žádné parametry produktů.', 'mwshop') . '</div>';
}

/** List of all properties. Can be formed as list of checkboxes. */
function field_type_property_list($field, $meta, $group_name, $group_id) {
	$content = (isset($meta)) ? $meta : array();

	$id = $group_id . '_' . $field['id'];
	$name = $group_name . '[' . $field['id'] . ']';
	$checkboxes = (isset($field['checkbox']));

	$propDefs = MwsProperty::getAll();
	if (count($propDefs)) {
		echo '<div class="mws_product_properties">';
		/** @var MwsProperty $property */
		foreach ($propDefs as $property) {
			$propId = $property->id;
			echo '<div class="mws_property mws_property-'.$propId.'">';
			if($checkboxes)
				cms_generate_field_checkbox($name.'['.$propId.']', $id.'_'.$propId,
					isset($content[$propId]) ? $content[$propId] : '',
					$property->name
				);
			else
				echo '	<div>' . $property->name . '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="cms_clear"></div>';
	} else echo '<div class="cms_info_box_gray">' . __('Nejsou vytvořeny žádné parametry produktů.', 'mwshop') . '</div>';
}

function cms_generate_product_code($name, $id, $meta, $class = '', $field = array()) {
    return cms_generate_field_text($name, $id, $meta,
      'mws_product_code' . (isset($field['class']) ? ' ' . $field['class'] : ''),
      $field);
}

/** Product extended code. */
function field_type_product_code($field, $meta, $group_name, $group_id) {
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$content = (isset($meta)) ? $meta : '';
    echo cms_generate_product_code($name, $id, $content, '', $field);
}

/** Product extended codes (accounting, storage) for single product. */
function field_type_product_codes($field, $meta, $group_name, $group_id) {
	$name = $group_name . '[' . $field['id'] . ']';
	$id = $group_id . '_' . $field['id'];

	$content = (isset($meta)) ? $meta : array();

	$gw = MWS()->gateways()->getDefault();
	$enabledCodes = $gw ? $gw->getEnabledCodes(MWS()->canEdit()) : array();

	if (empty($enabledCodes)) {
	    $res = '<div class="label">' . __('Kódy účetní, skladové, evidenční', 'mwshop') . '</div>'
            . '<span class="cms_description">' . __('Pro využití kódů je potřeba v nastavení eshopu v sekci <i>Platby a fakturace</i> povolit, které kódy chcete používat.', 'mwshop') . '</span>'
        ;
    } else {
        $res = '';
        foreach ($enabledCodes as $enabledCode) {
            $res .= '<div class="label">' . MwsProductCode::getCaption($enabledCode) . '</div>';
            $res .= cms_generate_field_text(
                $name . "[$enabledCode]",
                $id . "_$enabledCode",
                (isset($content[$enabledCode]) ? $content[$enabledCode] : ''),
                '', $field);
        }
    }

    echo $res;
}

/** Variants admin
***************************************************************************************** **/

function field_type_variant_list($field, $content, $group_name, $group_id) {

	$tag_name=$group_name.'['.$field['id'].']';
	$tag_id=$group_id.'_'.$field['id'];

	$parameters= MwsProperty::getAll();
	if(empty($parameters)) {
		echo '<div class="mws_variants_param_error">';
		echo '<span>'.__('Nejsou vytvořené žádné parametry produktu ze kterých lze vytvářet varianty. Nejdříve vytvořte parametry, ze kterých chcete varianty vytvářet a potom tuto stránku obnovte.','').'</span>';
		echo '<a target="_blank" href="'.admin_url( 'edit.php?post_type='.MWS_PROPERTY_SLUG ).'" class="cms_button">'.__('Vytvořit parametry produktu','mws_shop').'</a>';
		echo '<div class="cms_clear"></div></div>';
	} else {
		?>

		<div class="mws_variants_params">
			<h3><?php echo __('Vyberte ze kterých parametrů chcete vytvářet varianty','mws_shop'); ?></h3>
			<?php
			/** @var MwsProperty $parametre */
			foreach($parameters as $parametre) {
				echo '<div class="mws_variants_param_item">'
					.'<input type="checkbox" name="'.$tag_name.'[parametres]['.$parametre->id.']" id="'.$tag_id.'_parametres_'.$parametre->id.'" '
						.(isset($content['parametres'][$parametre->id])?'checked="checked"':'').' value="'.$parametre->id
					.'">'
					.'<label for="'.$tag_id.'_parametres_'.$parametre->id.'">'.$parametre->name.'</label>'
					.'</div>';
			}
			?>
			<div class="mw_variants_parametres_buttons">
				<button class="mws_save_params_list cms_button"><?php echo __('Uložit změny','mws_shop'); ?></button>
				<button class="mws_close_params_list cms_button cms_gray_button"><?php echo __('Storno','mws_shop'); ?></button>
			</div>
		</div>
		<div class="mws_variants_container">
			<button class="mws_edit_params_list mws_edit_params_list_button cms_button_secondary <?php if(isset($content['parametres'])) echo 'cms_nodisp'; ?>"><?php echo __('Vybrat parametry pro varianty','mws_shop'); ?></button>

			<div class="mws_varaints_list <?php if(!isset($content['parametres'])) echo 'cms_nodisp'; ?>">

				<div class="label">
					<?php echo __('Varianty produktu','mwshop'); ?>
					<span>(<a class="mws_edit_params_list" href=""><?php echo __('změnit parametry pro varianty','mwshop'); ?></a>)</span>
				</div>

				<div class="ve_multielement_container set_form_row ve_sortable_items">
					<?php

					$params = isset($content['parametres'])
						? $content['parametres']
						: array();

					$variants = isset($content['variants'])
						? $content['variants']
						: array();
					;
					$gw = MWS()->gateways()->getDefault();
					$enabledCodes = $gw ? $gw->getEnabledCodes(MWS()->canEdit()) : array();
					$i=0;
					if(!empty($variants) && is_array($variants)) {
						foreach($variants as $key=>$fd) {
							?>
							<div class="ve_multielement-<?php echo $i; ?> ve_item_container ve_setting_container ve_sortable_item">
								<?php
								$v_id=$tag_id.'_'.$i;
								$v_name=$tag_name.'[variants]['.$i.']';
								mws_generate_variant_item($v_name, $v_id, $fd, $params, $enabledCodes); ?>
							</div>
							<?php
							$i++;
						}
					}
					?>
				</div>
				<button class="mws_add_variant cms_button_secondary"
								data-id="<?php echo $i; ?>"
								data-set="<?php echo implode(',',$params); ?>"
								data-name="<?php echo $tag_name.'[variants]'; ?>"
								data-tagid="<?php echo $tag_id; ?>"
				>
					<?php echo __('Přidat variantu','mwshop'); ?>
				</button>
			</div>
		</div>
		<?php
	}
}
function mws_generate_variant_item($name, $id, $content, $parameters=array(), $enabledCodes = array()) {
	$title='';
  $open=true;
	if(empty($content) || !isset($content['variant_id']) || empty($content['variant_id'])) {
		$title = __('(neuložená varianta)', 'mwshop');
//		$title = sprintf(__('(nová varianta %d)', 'mwshop'), (int)$id + 1); // A little brutal force to extract number from ID HTML attribute ;)
	} else {
		$variant = MwsProductVariant::getById($content['variant_id']);
		if($variant) {
			$title = $variant->name . ' / ' . $variant->price->htmlPriceVatIncluded();
			if($variant->stockEnabled) {
				$stockCnt = $variant->stockCount;
				if($stockCnt > 0) {
					$title .= ' ' . sprintf(_x(' / %dks', 'Part of product variation title, when stock is enabled and full.', 'mwshop'), $stockCnt);
				} elseif($stockCnt < 0) {
					$title .= ' ' . sprintf(_x(' / %dks (v objednávkách)', 'Part of product variation title, when stock is enabled, backordered and stock is below 0.', 'mwshop'), $stockCnt);
				} else {
					$title .= ' ' . sprintf(_x(' / vyprodáno', 'Part of product variation title, when stock is enabled but empty.', 'mwshop'), $stockCnt);
				}
			}
      $open=false;
		}
	}
	?>
	<div class="ve_item_head">
		<?php // <span class="ve_sortable_handler"></span> ?>
		<?php echo $title; ?>
		<a class="ve_delete_setting" href="#" title="<?php echo __('Smazat','cms_ve'); ?>"></a>
	</div>
	<div class="ve_item_body <?php if($open) echo 've_item_body_v'; ?>">
		<?php mws_generate_variant_row($name, $id, $content, $parameters, $enabledCodes); ?>
	</div>
	<?php
}
function mws_generate_variant_ajax () {
	$param=explode(',',$_POST['param']);
	$params_list = array();
	foreach($param as $p) {
		$params_list[$p]=$p;
	}
	mws_generate_variant_item($_POST['tagname'].'['.$_POST['id'].']', $_POST['tagid'].'_'.$_POST['id'], array(), $params_list);
	die();
}
add_action('wp_ajax_mws_generate_variant_ajax', 'mws_generate_variant_ajax');


/** Generate HTML row for a product variant.
 * @param $name
 * @param $id
 * @param $content
 * @param $parametres
 * @param $enabledCodes
 */
function mws_generate_variant_row($name, $id, $content, $parametres, $enabledCodes) {
	$rowname = $name;
	$propertyValues = '';
	$properties = MwsProperty::getAll();
	/** @var MwsProperty $property */
	foreach ($properties as $property) {
    $propertyValues .= '<div class="mws_variant_parameter_input_container mws_variant_parameter_input_container_'.$property->id.' '.((!isset($parametres[$property->id]))? 'cms_nodisp':'').'">';
		$propertyValues .= $property->htmlEditor($rowname."[property][{$property->id}]", '',
			(isset($content['property'][$property->id]) ? $content['property'][$property->id] : ''),
            // css
			'cms_text_input mws_variant_parameter_input',
			// placeholder
			'('.$property->name . (empty($property->unit) ? '' : ' - '.$property->unit) . ')',
			// hint
			sprintf(__('Zadejte hodnotu parametru %s.', 'mwshop'), $property->name),
      // disabled
      ((!isset($parametres[$property->id]))? true:false)
		);
    $propertyValues .= '</div>';
	}

	$codes = '';
	foreach ($enabledCodes as $enabledCode) {
	    $caption = MwsProductCode::getCaption($enabledCode);
	    $codes .= '
			<div class="mws_col mws_col_codes" >
                <div class="sublabel" > ' . esc_html($caption) . ' </div >
                <div class="mws_input_container" >
                    <input type="text" name="'.$rowname.'[codes]['.$enabledCode.']" 
                        class="cms_text_input" '
                        . (isset($content['codes'][$enabledCode]) ? ' value="'.esc_attr($content['codes'][$enabledCode]).'"' : '') . '
                        title="'.esc_attr($caption).'" >
                </div >
			</div >';
	}
  
    if(isset($content['image'])) {
        $image=(substr($content['image'], 0, 7)=='http://')?$content['image']:home_url().$content['image'];
    } else {
	    $image="";
    }

    $image_tag_id=$id.'_img';

	$currencyStep = MwsCurrency::getHtmlInputStepAttribute();

	$variantId = isset($content['variant_id']) && !empty($content['variant_id']) ? (int)$content['variant_id'] : false;
	$res = '
		<div class="mws_variant_definition' . ($variantId ? '' : ' mws_new_item') .'">
			<div class="mws_col mws_col_id">
				<input name="'.$rowname.'[variant_id]" type="hidden" ' . ($variantId ? ' value="'.$variantId.'"' : '') . ' >'
//				. ($variantId ? '<span class="mws_variant_id">#'.$variantId.'</span>' : '')
				. '
			</div>
			<div class="mws_col mws_col_property_values">
            <div class="sublabel">'.__('Parametry','mws_shop').'</div>
				' . $propertyValues . '
			</div>
			<div class="mws_col mws_col_price">
                <div class="sublabel">'.__('Cena','mws_shop').'</div>
                <div class="mws_input_container">
                    <input type="number" name="'.$rowname.'[price]"
                        step="'. $currencyStep .'"
                        placeholder="0"
                        class="cms_text_input" '
                        . (isset($content['price']) ? ' value="'.esc_attr($content['price']).'"' : '') . '
                        title="'.__('Plná cena', 'mwshop').'">
                    <span class="mws_price_unit">'.MWS()->getCurrency('html').'</span>
                </div>
			</div>
      <div class="mws_col mws_col_price">
        <div class="sublabel">'.__('Cena po slevě','mws_shop').'</div>
        <div class="mws_input_container">
    				<input type="text" name="'.$rowname.'[price_sale]" 
    				  placeholder="-"
    					class="cms_text_input" '
    					. (isset($content['price_sale']) ? ' value="'.esc_attr($content['price_sale']).'"' : '') . '
    					title="'.__('Cena po slevě - Zadejde hodnotu ceny po slevě pro aktivaci slevy. Ponechte pole prázdné pro vypnutí slevy.', 'mwshop').'">
    				<span class="mws_price_unit">'.MWS()->getCurrency('html').'</span>
        </div>
			</div>
			<div class="mws_col mws_col_stock'
//				. (isset($content['stock_enabled']) && $content['stock_enabled'] ? '' : ' cms_nodisp')
				.'">
        <div class="sublabel">'.__('Sklad','mws_shop').'</div>
				<input type="number" name="'.$rowname.'[stock_count]" placeholder="0"
					class="cms_text_input" '
					. (isset($content['stock_count']) ? ' value="'.esc_attr($content['stock_count']).'"' : '') . '
					title="'.__('Stav skladových zásob', 'mwshop').'">
			</div>
      <div class="mws_col mws_col_image">
        <div class="sublabel">'.__('Obrázek','mws_shop').'</div>
        <div class="mws_input_container cms_upload_image_container_'.$image_tag_id.' '.((isset($content['image']) && $content['image'])? 'cms_upload_image_uploaded':'').'">
            <div id="image_'.$image_tag_id.'" class="cms_uploaded_image '.((!isset($content['image']) || !$content['image'])? 'cms_nodisp':'').'">
                <img class="cms_upload_image_button" target="'.$image_tag_id.'" src="'.$image.'" alt="" />
                <div class="cms_clear"></div>  
            </div>
            <button type="button" class="cms_upload_image_button cms_button_secondary" target="'.$image_tag_id.'" href="#">'.__('Nahrát obrázek','cms').'</button>
            <a id="cms_clear_image_'.$image_tag_id.'" class="cms_clear_image_button '.((!isset($content['image']) || !$content['image'])? 'cms_nodisp':'')
							.'" target="'.$image_tag_id.'" href="#">'
							.file_get_contents(MWS()->getTemplateFileDir("img/icons/close.svg"), true)
						.'</a>
            <input id="'.$image_tag_id.'" type="hidden" value="'.((isset($content['image']) && $content['image'])? $content['image']:'').'" name="'.$name.'[image]" />
            <input id="'.$image_tag_id.'_imageid" type="hidden" value="'.((isset($content['image_id']) && $content['image_id'])? $content['image_id']:'').'" name="'.$name.'[image_id]" />  
        </div>
      </div>

      ' . ($codes ? $codes : '') .'
      
		</div>
		<div class="cms_clear"></div>'
		. (isset($content['error']) && !empty($content['error']) ? '<div class="cms_error_box">'.($content['error']).'</div>' : '') . '
	';
	echo $res;
}

function mws_generate_country_select($name, $id, $css, $value, $print = true) {
    $res = '';
    $countries = MWS()->getSupportedCountries();
    if(!array_key_exists($value, $countries)) {
        $value = 'CZ';
        //TODO http://stackoverflow.com/questions/12553160/getting-visitors-country-from-their-ip
    }
    $res .= '<select'
        . ($name ? ' name="'.$name.'"' : '')
        . ($id ? ' id="'.$id.'"' : '')
        . ($css ? ' class="'.$css.'"' : '')
        . '>';
	foreach ($countries as $country => $caption) {
      $res .= '<option value="'.$country.'" '.($country == $value ? ' selected="selected"' : '').'>'.$caption.'</option>';
    }
    $res .= '</select>';
	if($print) {
	    echo $res;
    } else {
	    return $res;
    }
}

function field_type_eshop_feeds($field, $content, $group_name, $group_id) {

	$tag_name=$group_name.'['.$field['id'].']';
	$tag_id=$group_id.'_'.$field['id'];
	
	$heureka_url=get_feed_link( 'heureka' );
	$zbozi_url=get_feed_link( 'zbozi' );
	?>
	<table>
			<tr>
					<td><?php echo __('Heureka.cz','mws_shop'); ?></td>
					<td><a href="<?php echo $heureka_url; ?>" target="_blank"><?php echo $heureka_url; ?></a></td>
			</tr>
			<tr>
					<td><?php echo __('Zbozi.cz','mws_shop'); ?></td>
					<td><a href="<?php echo $zbozi_url; ?>" target="_blank"><?php echo $zbozi_url; ?></a></td>
			</tr>
	</table>
	<?php

}
