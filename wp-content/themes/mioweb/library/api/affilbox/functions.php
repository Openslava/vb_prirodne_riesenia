<?php
function cms_connectFapiAffilbox($vs, $code){
	$login=get_option('ve_connect_fapi');

	$fapiUsername = $login['login'];
	$fapiPassword = $login['password'];
	
	if(!isset($fapiUsername) or !isset($fapiPassword))
		return 0;

	require_once(FAPI_API);
	
	$fapi = new FAPIClient($fapiUsername, $fapiPassword, 'http://api.fapi.cz');

	$invoices = $fapi->invoice->search(array('variable_symbol' => intval($vs), 'single' => true));

	if(!$invoices)
		return false;

  $replacements=array();

  if ($invoices['total_czk'] !== null) {
    	$replacements['CENA'] = number_format(round($invoices["total_czk"]-$invoices["total_vat_czk"],2), 2, '.', '');
    	$replacements['OZNACENI_MENY'] = 'CZK';
  } else {
    	$replacements['CENA'] = number_format(round($invoices["total"]-$invoices["total_vat"],2), 2, '.', '');
    	$replacements['OZNACENI_MENY'] = $invoices['currency'];
  }
      
  $code = strtr($code, $replacements); 
  
	return $code;
}