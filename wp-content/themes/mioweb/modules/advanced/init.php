<?php

global $cms;
global $vePage;

define('AD_VERSION','0.9.3');
$cms->add_version('advanced',VS_VERSION);  
  
define('AD_DIR',get_bloginfo('template_url').'/modules/advanced/');    
define('AD_DEFAULT_DIR',str_replace ( home_url() , '' , get_bloginfo('template_url') ).'/modules/advanced/');

require_once(__DIR__.'/advanced_class.php');
require_once(__DIR__.'/elements-print.php'); 
require_once(__DIR__.'/elements.php');

$advanced_module = New advancedMioWeb(); 
