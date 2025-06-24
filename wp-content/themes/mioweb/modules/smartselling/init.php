<?php 

global $cms;
global $smartselling_module;
define('SMARTSERLLING_VERSION','0.9');
$cms->add_version('smartselling',SMARTSERLLING_VERSION);  

define('SMARTSERLLING_DIR',get_template_directory_uri().'/modules/smartselling/');

require_once(TEMPLATEPATH . '/modules/smartselling/smartselling_class.php');  

$smartselling_module = New SmartSelling(); 



// Top panel menu
//***********************************************************************************

//$vePage->add_top_panel_menu(11,array('id'=>'campaign','title'=>__('Kampaň', 'cms_mioweb'),'submenu'=>$mioweb_module->create_mioweb_menu(),'url'=>((isset($mioweb_module->first_campaign['squeeze']) && $mioweb_module->first_campaign['squeeze'])? get_permalink($mioweb_module->first_campaign['squeeze']) : "#")));

