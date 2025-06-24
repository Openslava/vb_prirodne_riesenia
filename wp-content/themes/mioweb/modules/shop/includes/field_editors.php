<?php
/**
 * Support to generate HTML elements for typed custom-fields.
 *
 * Date: 11.02.16
 * Time: 9:11
 */

class MwsFieldEditor {
	/**
	 * Renders HTML editor (input/s) according to field definition.
	 * As ID of HTML element is used field's ID generator.
	 * As NAME of HTML element is used field's NAME generator.
	 * @param $definition MwsPropDef			Definition of the field.
	 * @param $value mixed						Current value of the field.
	 */
	public static function renderEditor($definition, $value) {
		$type='';
		if ($definition instanceof MwsPropFieldDef) {
			$type=$definition->type;
		} else
			trigger_error('Unsupported type of definition [' . (gettype($definition)) .'].', E_USER_WARNING);

		if(empty($type))
			trigger_error('Editor type for field was not specified.');

		$funcName = "mws_field_editor_$type";
		$htmlId = MWS_ID.$definition->asInputId();
		if(function_exists($funcName)) {
			//TODO add some wrapper?
			echo '<p'.
				' class="'.MWS_CSS.'field_editor '.MWS_CSS.'field_type_'.$type.'"'.
				' id="'.$htmlId.'"'.
				'>';
			mws_field_label($definition, ': ', '', array('for'=>$htmlId.'_value'));
			call_user_func_array($funcName, array($definition, $value));
			echo '</p>';
		}
		else {
			echo '<p'.
				' class="'.MWS_CSS.'field_editor '.MWS_CSS.'field_type_'.$type.'"'.
				' id="'.$htmlId.'"'.
				'>';
			echo '<span style="color:red">';
			mws_field_label($definition, ': ', '', array('for'=>$htmlId.'_value'));
			echo " <i>[$type]</i> ";
			mws_field_editor_static($definition, $value);
			echo '</span>';
//			trigger_error('Unsupported field editor type [' . $type . '].', E_USER_WARNING);
		}
	}
}

/**
 * Consolidates all items within array into one dimensional array of non-array items.
 * @param $arr array Array with items or recursively nested arrays.
 * @return array Linearized array.
 */
function array_linear($arr) {
	if(is_array($arr)) {
		$lin = array();
		while ($arr) {
			$item = array_pop($arr);
			if(is_array($item)){
				foreach ($item as $key=>$val ) { array_push($arr, $val); }
			} else
				$lin[]=$item;
		}
		$lin=array_reverse($lin);
		return $lin;
	} else
		return $arr;
}

/**
 * Helper function to format HTML CSS classes string from several sources. Output is usable directly in HTML code.
 * @param array|string $arrayCss     List of CSS classes or a single CSS string. Can be empty.
 * @param null|array $options        Array of options. Only value of 'css' key is taken and included within the result.
 * @param string $nonemptyPrefix     If generated list of CSS classes is not empty, then this string is prepended before the result.
 * @return string If a CSS is generated from input values, then string [$nonemptyPrefix.class="$generatedCSS"] is returned.
 */
function htmlFormatCss($arrayCss, $options=null, $nonemptyPrefix = ' ') {
	if (is_array($arrayCss))
		$all = array_linear($arrayCss);
	else
		$all = array($arrayCss);
	if (!empty($options) && is_array($options) && isset($options['css'])) {
		if(is_array($options['css']))
			$all = array_merge($all, $options['css']);
		else
			$all[] = $options['css'];
	}
	$all = array_map(function($item) {return trim($item);}, $all);
	$all = array_filter($all); // Drop out empty items.
	$css = implode(' ', $all);
	return (empty($css)) ? '' : $nonemptyPrefix.'css="'.$css.'"';
}

/**
 * Helper function to format HTML string for "placeholder" for text input elements.Output is usable directly in HTML code.
 * @param $placeholder string Default placeholder text.
 * @param null|array $options    Array of options. Only value of 'placeholder' key is used in case that $placeholder is empty.
 * @param string $nonemptyPrefix If generated placeholder valueis not empty, then this string is prepended before the result.
 * @return string If a placeholder will have a value, then string [$nonemptyPrefix.placeholder="$value"] is returned.
 */
function htmlFormatPlaceholder($placeholder, $options=null, $nonemptyPrefix=' ') {
	if(!empty($placeholder))
		$val = $placeholder;
	else if (isset($options['placeholder']))
		$val = $options['placeholder'];
	else
		$val = '';
	return (empty($val)&&!is_numeric($val)) ? '' : $nonemptyPrefix.'placeholder="'.$val.'"';
}

/**
 * Helper function to format HTML string for "placeholder" for text input elements.Output is usable directly in HTML code.
 */
function htmlFormatUnit($value, $options=null, $nonemptyPrefix='&nbsp') {
	if(!empty($value))
		$val = $value;
	else if (isset($options['unit']))
		$val = $options['unit'];
	return isset($val) ? '<span class="'.MWS_CSS.'unit">&nbsp'.$val.'</span>' : '';
}

/**
 * Helper function to format HTML string for linking label to input element using "for". Output is usable directly in HTML code.
 */
function htmlFormatFor($value, $options=null, $nonemptyPrefix=' ') {
	if(!empty($value))
		$val = $value;
	else if (isset($options['for']))
		$val = $options['for'];
	return isset($val)&&!empty($val) ? $nonemptyPrefix.'for="'.$val.'""' : '';
}

//=========================================================================

/**
 * Generator for an HTML element "SELECT". If $valueSelected is not present within $items then new disabled item is appended to
 * the list of options with class "mws_value_error".
 * @param $id                 string      ID of the input element.
 * @param $name               string      NAME of the input element.
 * @param null $valueSelected Value of selected item within items.
 * @param $items              array     Array of items of the select elements. Items are expected to by in ($value => $caption) format.
 * @param $css                string         Space-separated list of CSS classes.
 * @param null|array $options Array of key-indexed options.
 */
function mws_editor_select($id, $name, $valueSelected = null, $items = array(), $css = '', $options=null) {
	$code = '<select ' .
		htmlFormatCss($css, $options, ' ').
		($id ? ' id="' . $id . '"' : '') .
		($name ? ' name="' . $name . '"' : '') .
		' >';
	$selValueUsed = false;
	foreach ($items as $value => $caption) {
		$value = htmlspecialchars($value);
		$caption = htmlspecialchars($caption);
		if ($value == $valueSelected) {
			$selected = ' selected="selected"';
			$selValueUsed = true;
		} else
			$selected = '';
		$code .= '<option value="' . $value . '"' . $selected . '>' . $caption . '</option>';
	}
	// Add additional option for current value, that is missing in items list.
	if (!$selValueUsed && !empty($selValueUsed))
		$code .= '<option value="' . $valueSelected . '" selected="selected" class="'.MWS_CSS.'value_error"' .
			' disabled="disabled" style="color:red">' .
			htmlspecialchars($valueSelected . ' '. _x('(invalid value)', 'invalid field value', 'mwshop')) . '</option>';
	$code .= '</select>';
	echo $code;
}

/**
 * Generator editor for one-line text.
 * @param $id    string      ID of the input element.
 * @param $name  string      NAME of the input element.
 * @param $value mixed Value of selected item within items.
 * @param $css   string         Space-separated list of CSS classes.
 * @param $attrs string     Additional HTML attributes for the input element. Values of attributes must be enclosed in "".
 * @param string $subname  Optional subname, that will be used as array index appended to $name ("$name[$subname]").
 * @param null|array $options Array of key-indexed options.
 */
function mws_editor_text($id, $name, $value, $css='', $attrs='', $subname='', $options=null) {
	if(!is_string($subname))
		trigger_error('Atrribute [subname] must be string', E_USER_WARNING);
	$code = '<input '.
		htmlFormatCss($css, $options, ' ').
		' id="'.$id.($subname?'_'.$subname:'')/*.'_value'*/.'" ' .
		' type="text"'.
		' name="'.$name.($subname?"[$subname]":'').'" ' .
		($attrs ? ' '.$attrs : '').
		htmlFormatPlaceholder('', $options, ' ').
		(!empty($value) || is_numeric($value) ? ' value="'.htmlspecialchars($value).'"' : ''). // Print possible "0" value.
		'/>'.
		htmlFormatUnit('', $options)
	;
	echo $code;
}

function mws_editor_check($id, $name, $value, $css='', $attrs='', $subname='', $options=null) {
	if(!is_string($subname))
		trigger_error('Atrribute [subname] must be string', E_USER_WARNING);
	$code = '<input '.
		htmlFormatCss($css, $options, ' ').
		' id="'.$id.($subname?'_'.$subname:'')/*.'_value'*/.'" ' .
		' type="checkbox"'.
		' name="'.$name.($subname?"[$subname]":'').'" ' .
		($attrs ? ' '.$attrs : '').
		htmlFormatPlaceholder('', $options, ' ').
		(!empty($value) ? ' checked="checked"' : '').
		'/>'
	;
	echo $code;
}

function mws_editor_textLong($id, $name, $value, $css='', $attrs='', $subname='', $options=null) {
	$code = '<textarea '.
		htmlFormatCss($css, $options, ' ').
		' id="'.$id.($subname?'_'.$subname:'').//'_value" ' .
		' name="'.$name.($subname?"[$subname]":'').'" ' .
		($attrs ? ' '.$attrs : '').
		htmlFormatPlaceholder('', $options, ' ').
		'>'.
		(!empty($value) || is_numeric($value) ? htmlspecialchars($value) : '').
		'</textarea>'.
		htmlFormatUnit('', $options)
	;
	echo $code;
}

function mws_editor_static($id, $name, $value, $css='', $attrs='', $options=null) {
	$classes = MWS_CSS . (empty($value) ? 'value_empty' : 'value_filled');
	$code = '<span'.
		htmlFormatCss(array($css, $classes), $options, ' ').
		' id="'.$id.'_value" ' .
		($attrs ? ' '.$attrs : '').
		'>'.
		(empty($value)&&!is_numeric($value)
			? (isset($options['placeholder'])?$options['placeholder']:'')
			: htmlspecialchars($value)).
		htmlFormatUnit('', $options).
		'</span>'
	;
	echo $code;
}

//=========================================================================

/**
 * Prints a HTML label of the field, if the field has caption assigned.
 *
 * Supported $options: "for" = htmlId of element to link the label to
 * @param $def MwsPropDef		Field definition
 * @param string $suffix		Optional suffix that will be appended if the labels is not empty.
 * @param $css   string         Space-separated list of CSS classes.
 * @param null|array $options Array of key-indexed options.
 */
function mws_field_label($def, $suffix='', $css='', $options=null) {
	if (!empty($def->caption)) {
		addOptionValues($options, array());
		$options = $def->getOptionsArr() + $options;
		echo '<label' .
			htmlFormatCss(array(MWS_CSS . 'label', $css, $def->getOption('css')), $options, ' ') .
			' id="' . MWS_ID . $def->asInputId() . '_label' . '"'.
			htmlFormatFor('', $options).
			'>' .
			$def->caption . $suffix . '</label>';
	}
}

/**
 * Field editor for one-line text.
 * @param $def   MwsPropDef		Definition of the field.
 * @param $value mixed			Current value
 * @param $css   string         Space-separated list of CSS classes.
 * @param null|array $options Array of key-indexed options.
 */
function mws_field_editor_text($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_text(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'text', $css),
		'', '',
		$options
	);
}

/**
 * Field editor for multi-line text.
 * @param $def   MwsPropDef		Definition of the field.
 * @param $value mixed			Current value
 * @param $css   string         Space-separated list of CSS classes.
 * @param null|array $options Array of key-indexed options.
 */
function mws_field_editor_textLong($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_textLong(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'text_long', $css),
		'', '',
		$options
	);
}

function mws_field_editor_check($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_check(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'check', $css),
		'', '',
		$options
	);
}



/**
 * Static field editor - only shows value. No editing is possible.
 * If value is set, then CSS class MWS(value_filled) is added.
 * If value is not set, then CSS class MWS(value_empty) is added.
 */
function mws_field_editor_static($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_static(
		MWS_ID.$def->asInputId().'_value', MWS_ID.$def->asInputName(),
		$value,
		array(MWS_CSS.'static', $css),
		'', $options
	);
}

function mws_field_editor_number($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_text(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'number', $css),
		'maxlength="10" size="8"', '', $options
	);
	if ($def->getOption('unit'))
		echo $def->getOption('unit');
}

function addOptionValue(&$options, $key, $value) {
	if(!is_array($options))
		$options = array($key => $value);
	else if (!isset($options[$key]))
		$options[$key] = $value;
}

function addOptionValues(&$options, $keyVals=array()) {
	if(!is_array($options))
		$options = array();
	if(is_array($keyVals))
		$options = array_merge($options, $keyVals);
}

function mws_field_editor_price($def, $value, $css='', $options=null) {
	addOptionValues($options, array('placeholder'=>0, 'unit'=>MWS()->getCurrency()));
	$options = $def->getOptionsArr() + $options;
	mws_editor_text(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'price', $css),
		'maxlength="10" size="8"', '', $options
	);
}

function mws_field_editor_priceStatic($def, $value, $css='', $options=null) {
	addOptionValues($options, array('placeholder'=>0,'unit'=>MWS()->getCurrency()));
	$options = $def->getOptionsArr() + $options;
	mws_editor_static(
		MWS_ID.$def->asInputId().'_value',
		MWS_NAME.$def->asInputName(),
		$value,
		array(MWS_CSS.'price', $css),
		'', $options
	);
}

function mws_field_editor_date($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_text(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'date', $css),
		'', '', $options
	);
}

function mws_field_editor_color($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_text(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'color', $css),
		'', '', $options
	);
}

function mws_field_editor_syncedLast($def, $value, $css='', $options=null) {
	addOptionValues($options, array('placeholder'=>__('never', 'mwshop'),));
	$options = $def->getOptionsArr() + $options;
	mws_editor_static(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'synced_last', $css),
		'', $options
	);
}

function mws_field_editor_syncStatus($def, $value, $css='', $options=null) {
	addOptionValues($options, array('placeholder'=>__('not synced', 'mwshop'),));
	$options = $def->getOptionsArr() + $options;
	mws_editor_static(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'sync_status', $css),
		'', $options
	);
}

function mws_field_editor_stockStatus($def, $value, $css='', $options=null) {
	addOptionValues($options, array('placeholder'=>0));
	$options = $def->getOptionsArr() + $options;
	mws_editor_text(
		MWS_ID . $def->asInputId().'_value',
		MWS_NAME . $def->asInputName(),
		$value,
		array(MWS_CSS.'stock_status', $css),
		'', '', $options
	);
}

function mws_field_editor_orderStatus($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_select(
		MWS_ID.$def->asInputId().'_value', MWS_NAME . $def->asInputName(), $value,
		array(
			'new' => _x('New', 'order status - unfinished order, before first checkout', 'mwshop'),
			'pendingPayment' => _x('Pending payment', 'order status - checkout finished, expecting payment', 'mwshop'),
			'paid' => _x('Paid', 'order status - fully paid order', 'mwshop'),
			'completed' => _x('Completed', 'order status - completed, closed', 'mwshop'),
			'cancelled' => _x('Cancelled', 'order status - cancelled, closed', 'mwshop'),
		),
		array(MWS_CSS.'order_status', $css),
		$options
	);
}

function mws_field_editor_paymentType($def, $value, $css='', $options=null) {
	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_select(
		MWS_ID.$def->asInputId().'_value', MWS_NAME . $def->asInputName(), $value,
		array(
			'card' => _x('Credit card (GoPay)', 'payment type', 'mwshop'),
			'sms' => _x('SMS Premium (GoPay)', 'payment type', 'mwshop'),
			'paypal' => _x('PayPal (GoPay)', 'payment type', 'mwshop'),
			'transfer_express' => _x('Express bank transfer (GoPay)', 'payment type', 'mwshop'),
			'transfer' => _x('Bank transfer (GoPay)', 'payment type', 'mwshop'),
			'cash' => _x('Cash at desk', 'payment type', 'mwshop'),
			'cod' => _x('Cash on delivery', 'payment type', 'mwshop'),
		),
		array(MWS_CSS.'payment_type', $css),
		$options
	);
}

function mws_field_editor_customer($def, $value, $css='', $options=null) {
	// Get all registered customers
	$customers = array(5 => 'cust1', 7 => 'cust2', 11=>'cust3');
	$customers = array_merge(
		array(null => _x('Guest', 'represents a customer without existing registration', 'mwshop')),
		$customers);

	addOptionValues($options, array());
	$options = $def->getOptionsArr() + $options;
	mws_editor_select(
		MWS_ID.$def->asInputId().'_value', MWS_NAME.$def->asInputName(), $value,
		$customers,
		array(MWS_CSS.'customer', $css),
		$options
	);
}

function mws_field_editor_address($def, $value, $css='', $options=null) {
	$baseId = MWS_ID.$def->asInputId().'_value';
	$baseName = MWS_NAME.$def->asInputName();
	mws_editor_text($baseId, $baseName,
		(isset($value['firstname']) ? $value['firstname'] : null),
		'', '', 'firstname', array('placeholder' => _x('First name', 'address field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['surname']) ? $value['surname'] : null),
		'', '', 'surname', array('placeholder' => _x('Surname', 'address field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['street']) ? $value['street'] : null),
		'', '', 'street', array('placeholder' => _x('Street and number', 'address field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['city']) ? $value['city'] : null),
		'', '', 'city', array('placeholder' => _x('City', 'address field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['zip']) ? $value['zip'] : null),
		'', '', 'zip', array('placeholder' => _x('Postcode', 'address field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['country']) ? $value['country'] : null),
		'', '', 'country', array('placeholder' => _x('Country', 'address field', 'mwshop'))
	);
}

function mws_field_editor_company($def, $value, $css='', $options=null) {
	$baseId = MWS_ID.$def->asInputId().'_value';
	$baseName = MWS_NAME.$def->asInputName();
	mws_editor_text($baseId, $baseName,
		(isset($value['name']) ? $value['name'] : null),
		'', '', 'name', array('placeholder' => _x('Name of the company', 'company field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['ID']) ? $value['ID'] : null),
		'', '', 'ID', array('placeholder' => _x('ID number', 'company field', 'mwshop'))
	);
	mws_editor_text($baseId, $baseName,
		(isset($value['VAT']) ? $value['VAT'] : null),
		'', '', 'VAT', array('placeholder' => _x('VAT number', 'company field', 'mwshop'))
	);
}

