<?php 
global $cms;
global $mioweb_module;
define('MIOWEB_VERSION','0.9');
$cms->add_version('mioweb',MIOWEB_VERSION);  

define('MIOWEB_DIR',get_template_directory_uri().'/modules/mioweb/');

// language
$cms->load_theme_lang('cms_mioweb', get_template_directory() . '/modules/mioweb/languages');

require_once(TEMPLATEPATH . '/modules/mioweb/functions.php');
require_once(TEMPLATEPATH . '/modules/mioweb/elements.php');
require_once(TEMPLATEPATH . '/modules/mioweb/elements_print.php');
require_once(TEMPLATEPATH . '/modules/mioweb/mioweb_class.php');

add_theme_support('menus');

$mioweb_module = New MioWeb(); 

$cms->add_templates_topos(6,'campaign',array(
      'name'=>__('Kampaňové', 'cms_mioweb'),
      'path'=>'/modules/mioweb/templates/campaign/',
      'list'=>array(
          'sale_letters'=>array(
              'name'=>__('Video stránky', 'cms_mioweb'),
              'list'=>array('1','2')
          ),          
      )
));

// Top panel menu
//***********************************************************************************

$vePage->add_top_panel_menu(20,array('id'=>'campaign','title'=>__('Kampaň', 'cms_mioweb'),'submenu'=>$mioweb_module->create_mioweb_menu(),'url'=>((isset($mioweb_module->first_campaign['squeeze']) && $mioweb_module->first_campaign['squeeze'])? get_permalink($mioweb_module->first_campaign['squeeze']) : "#")));


// Nastavení stránek
//***********************************************************************************




// Nastavení
//***********************************************************************************


$cms->add_page(array(
    'page_title' => __('Kampaně', 'cms_mioweb'),
    'menu_title' => __('Kampaně', 'cms_mioweb'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'campaign_option',
    'icon_url' => '',
    'position' => 205
));
$cms->add_subpage(array(
    'parent_slug' => 'campaign_option',
    'page_title' => __('Kampaně', 'cms_mioweb'),
    'menu_title' => __('Kampaně', 'cms_mioweb'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'campaign_option',
));
$cms->add_page_group(array(
    'id' => 'campaign_basic',
    'page' => 'campaign_option',
    'name' => __('Kampaně','cms_mioweb'),
));
$cms->add_page_setting('campaign_basic',array(
    array(
        'name' => '',
        'id' => 'campaigns',
        'type' => 'campaigns',
        'print'=> 'full'
    ),
)); 

