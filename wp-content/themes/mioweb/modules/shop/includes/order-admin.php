<?php
/**
 * Support for administration of orders.
 * User: kuba
 * Date: 06.05.16
 * Time: 11:21
 */


add_action('add_meta_boxes', 'mwsOrder_AddMetaboxes');
//add_action('save_post', 'mwsOrder_SaveMetaboxes');
add_filter('the_posts', 'mwsOrder_filterPosts', 10, 2);


function mwsOrder_AddMetaboxes() {
	add_meta_box('mwsOrder_general', __( 'Základní informace', 'mwshop'), 'mwsOrder_renderBoxGeneral', MWS_ORDER_SLUG);
	add_meta_box('mwsOrder_contact', __( 'Kontakt', 'mwshop'), 'mwsOrder_renderBoxContact', MWS_ORDER_SLUG);
	add_meta_box('mwsOrder_items', __( 'Položky', 'mwshop'), 'mwsOrder_renderBoxItems', MWS_ORDER_SLUG);
	if (defined('MW_SHOW_DEBUGS') && MW_SHOW_DEBUGS)
	  add_meta_box('mwsOrder_debug', 'Devel and debug', 'mwsOrder_renderBoxDebug', MWS_ORDER_SLUG);
}

function mwsOrder_renderBoxGeneral($post, $metabox) {
	$order = MwsOrder::createNew($post);
  echo '<div class="mws_order_head">';
  
  echo '<div class="mws_order_number_container mws_order_head_left">';
  echo __('Objednávka č.:', 'mwshop');
  echo  '<span>'.$order->orderNum.'</span>';
  echo '</div>';
  
	echo '<div class="mws_order_status_box mws_order_head_right"><label>'.__('Stav', 'mwshop').':</label> ';
	echo '<select name="order_status">';
	$curStatus = $order->status;
	foreach (MwsOrderStatus::getAll() as $item) {
		echo '<option value="'.$item.'"'.($item==$curStatus ? ' selected="selected"' : '').'>'
			.MwsOrderStatus::getCaption($item)
			.'</option>';
	}
	echo '</select>';
	echo '</div><div class="cms_clear"></div>';
  
  echo '</div>';
  
  echo '<div class="mws_order_content">';
  
  echo '<table class="mws_order_info">';
  
  $orderLive = $order->gateLive;
	if($orderLive && $orderLive->price) {
		$priceIncludingVat = $orderLive->price->priceVatIncluded;
	}
	if(isset($priceIncludingVat) && $priceIncludingVat!==false)
    echo '<tr><td>'.__('Cena (s DPH)', 'mwshop').'</td><td><strong>'.htmlPriceSimpleIncluded($priceIncludingVat).'</strong></td></tr>';
	else
    echo '<tr><td>'.__('Cena (s DPH)', 'mwshop').'</td><td><span class="mws_admin_error">'.__('(chyba při zjišťování hodnoty)', 'mwshop').'</span></td></tr>';
  
	if($order->gateId) {
		$gate = MWS()->gateways()->getById($order->gateId);
		if ($gate) {
			$gateCaption = $gate->caption;
			echo '<tr><td>'.__('Platební brána', 'mwshop').'</td><td>'.$gateCaption.'</td></tr>';
		}
	}

//	echo mwsOrder_formatField(__('Zaplaceno', 'mwshop'),
//		($order->isPaid ? __('ano', 'mwshop') : __('ne', 'mwshop'))
//	);
  if($order->customerNote)
    echo '<tr><td>'.__('Poznámka zákazníka', 'mwshop').'</td><td>'.wpautop(esc_html($order->customerNote)).'</td></tr>';

  echo '</table>';
    
  echo '</div>';

	//Documents
	echo '<h3 class="mws_order_title">'.__('Doklady', 'mwshop').'</h3>';
  echo '<div class="mws_order_content">';
	if(!$orderLive) {
		echo sprintf(__('Nepodařilo se načíst data z platební brány [%s].', 'mwshop'), $order->gateId);
	} else {
		$docs = $orderLive->getDocuments();
		if(empty($docs)) {
			echo '<span class="mws_admin_error">'
				.sprintf(__('Platební brána [%s] neobsahuje žádný relevantní doklad. Doklad byl patrně vymazán.', 'mwshop'), $order->gateId)
				.'</span>'
			;
		} else {
			echo '<table class="mws_order_invoice"><tbody>';
			foreach ($docs as $doc) {
				echo '<tr>'
					.'<td><a href="'.$doc['urlShow'].'" target="'.MW_HREF_TARGET_SHARED_ADMIN_EDIT.'">'.esc_html($doc['title']).'</a></td>'
//					.'<td>'.(isset($doc['dateCreated']) ? $doc['dateCreated']:'').'</td>'
					.'<td>'.(isset($doc['total']) ? htmlPriceSimpleIncluded($doc['total']):'').'</td>'
					.'<td>'.(isset($doc['isPaid']) && $doc['isPaid']
						? '<span class="mws_order_payed">'.__('uhrazeno', 'mwshop').'</span>'
						: '<span class="mws_order_notpayed">'.__('neuhrazeno', 'mwshop').'</span>')
					.'</td>'
					.'<td align="center">'
//					.(!empty($doc['urlShow'])
//						? htmlAdminEditButton_SharedWindow('Zobrazit', $doc['urlShow'],'mws_admin_show',false)
//						: '')
					.(!empty($doc['urlDownload'])
						? htmlAdminEditButton_SharedWindow('PDF', $doc['urlDownload'],'mws_admin_download',false)
						: '')
					.(!empty($doc['urlEdit'])
						? htmlAdminEditButton_SharedWindow('Upravit', $doc['urlEdit'],'mws_admin_edit',false)
						: '')
					.'</td>'
					.'</tr>';
				
			}
			echo '</table>';
		}
	}
  echo '</div>';
  
	//History
	echo '<h3 class="mws_order_title">'.__('Historie', 'mwshop').'</h3>';
  echo '<div class="mws_order_content">';
	echo '<table class="mws_order_history">';
	echo '<tr><td>'
		.(isset($order->post->post_date_gmt)
			? mwFormatAsDateTime(mwConvDateTimeUTC2TimestampUTC($order->post->post_date_gmt))
			: '---')
		.'</td>'
		.'<td>'.__('objednáno', 'mwshop').'</td></tr>';
	if($order->isPaid && (($paidOn = $order->paidOn) != false)) {
		//Values is stored as Unix timestamps in UTC. User current timezone and formatting according to WP settings.

		$timeLocal = mwFormatAsDateTime($paidOn, false);

		echo '<tr><td>'
			.$timeLocal
			.'</td>'
		  .'<td>'.__('zaplaceno', 'mwshop').'</td></tr>';
	}
	echo '</table>';
  echo '</div>';
}

function mwsOrder_renderBoxContact($post, $metabox) {
	$order = MwsOrder::createNew($post);
	$orderLive = $order->gateLive;
  echo '<div class="mws_order_head">';
  
  if(!$orderLive) {
		echo sprintf(__('Nepodařilo se načíst data z platební brány [%s].', 'mwshop'), $order->gateId);
    echo '</div>';
		return;
	}

  echo '<div class="mws_order_head_left">';
  echo '</div>';

  $s = $orderLive->formatContactEditting();
	if ($s) {
    	echo '<div class="mws_order_head_right">';
      echo $s;
    	echo '</div>';
  }      
  echo '<div class="cms_clear"></div>';
  echo '</div>';
  
  $s = $orderLive->formatInvoiceContact();
	if ($s)
	   echo mwsOrder_formatField('', '<div>' . $s . '</div>', '', true);
		
	$s = $orderLive->formatShippingContact();
	if ($s)
		echo mwsOrder_formatField(__('Doručovací adresa', 'mwshop'), '<div>' . $s . '</div>', '', true);
		
}

function mwsOrder_renderBoxItems($post, $metabox) {
	$order = MwsOrder::createNew($post);
	$orderLive = $order->gateLive;
	if(!$orderLive) {
		echo '<div class="mws_order_content">'.sprintf(__('Nepodařilo se načíst data z platební brány [%s].', 'mwshop'), $order->gateId).'</div>';
		return;
	}
	$items = $orderLive->getItems();
	if(is_array($items)&&count($items)>0) {
		$unit = MWS()->getCurrency();
		echo '<table class="mws_order_products"><thead><tr>'
			.'<th align="left">'.__('Název','mwshop').'</th>'
			.'<th>'.__('Kusů','mwshop').'</th>'
			.'<th align="right">'.__('Cena/kus (s DPH)','mwshop').'</th>'
			.'<th>'.__('DPH','mwshop').'</th>'
			.'<th align="right">'.__('Cena celkem (s DPH)','mwshop').'</th>'
			.'</tr></thead><tbody>';
		foreach ($items as $item) {
			echo '<tr>'
				.'<td>'.esc_html($item['title']).'</td>'
				.'<td align="center">'.$item['count'].'</td>'
				.'<td align="right">'.htmlPriceSimpleIncluded($item['priceIncludingVat'], $unit).'</td>'
				.'<td align="center">'.$item['vatPercentage'].'%</td>'
				.'<td align="right">'.htmlPriceSimpleIncluded($item['priceIncludingVat']*$item['count'], $unit).'</td>'
//				.'<td>'.$item['productId'].'</td>'
				.'</tr>';
		}
		echo '</tbody></table>';
	} else
		echo '<div class="mws_order_content">'.__('Objednávka neobsahuje žádné položky.', 'mwshop').'</div>';
}

function mwsOrder_renderBoxDebug($post, $metabox) {
	$order = MwsOrder::createNew($post);
	$orderLive = $order->gateLive;
	if($orderLive) {
		echo '<h3>GateLive</h3><pre>' . esc_html(print_r($orderLive, true)) . '</pre>';
	}
	echo '<h3>GateOrderData</h3><pre>'. esc_html(print_r($order->gateOrderData, true)).'</pre>';
}


function mwsOrder_formatField($label, $value, $css='', $dontEscape=false) {
  return '<div'
		. (!empty($css)?' class="'.$css.'"':'')
		. '>'
		. (!empty($label) ? '<h3 class="mws_order_title">'.$label.'</h3> ' : '')
		. '<div class="mws_order_content">'.($dontEscape ? $value : esc_html($value)).'</div>'
		. '</div>';
}



function mwsOrder_saveMetaboxes() {

}

function mwsOrder_filterPosts($posts, $wpQuery) {
	if(isset($posts[0]) && MWS_ORDER_SLUG==get_post_type($posts[0])) {
		$orders = array_map(
			function($item) {
				return MwsOrder::createNew($item);
			}, $posts);
		MWS()->gateways()->preloadOrdersGateLive($orders);
	}
	return $posts;
}