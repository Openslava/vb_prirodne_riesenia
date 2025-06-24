<?php

global $cms;
global $vePage;
global $webInstalator;

define('VS_VERSION', '0.9.6');
$cms->add_version('visualeditor', VS_VERSION);

define('VS_DIR', get_bloginfo('template_url') . '/modules/visualeditor/');
define('VS_SERVER_DIR', get_template_directory() . '/modules/visualeditor/');
define('VS_DEFAULT_DIR', str_replace(home_url(), '', get_bloginfo('template_url')) . '/modules/visualeditor/');
define('MW_IMAGE_LIBRARY', 'https://media.mioweb.com/images/');

// language
$cms->load_theme_lang('cms_ve', get_template_directory() . '/modules/visualeditor/languages');

//Classes loading
require_once('lib/models/NavMenu.php');

require_once(__DIR__ . '/visual_editor_class.php');
require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/elements-print.php');
require_once(__DIR__ . '/lib/intro/intro_class.php');
require_once(__DIR__ . '/lib/weditor/weditor_class.php');
require_once(__DIR__ . '/lib/weditor/popups_class.php');
require_once(__DIR__ . '/lib/install/install_class.php');
require_once(__DIR__ . '/lib/shortcodes/shortcodes.php');
require_once(__DIR__ . '/lib/upload_limit.php');
require_once(__DIR__ . '/lib/event_calendar.php');

// Image sizes
add_image_size('mio_columns_1', 970, 727, true); //4:3 crop
add_image_size('mio_columns_2', 461, 346, true); //4:3 crop
add_image_size('mio_columns_3', 297, 223, true); //4:3 crop
add_image_size('mio_columns_4', 213, 160, true); //4:3 crop
add_image_size('mio_columns_5', 171, 128, true); //4:3 crop
add_image_size('mio_columns_c1', 970); //dont crop
add_image_size('mio_columns_c2', 461); //dont crop
add_image_size('mio_columns_c3', 297); //dont crop
add_image_size('mio_columns_c4', 213); //dont crop
add_image_size('mio_columns_c5', 171); //dont crop

$vePage = New visualEditorPage();
$webInstalator = new webInstallator();

require_once(__DIR__ . '/shortcodes.php');


if ($vePage->edit_mode || !$webInstalator->installed_web) {
    $webInstalator->add_web_tags(
        array(
          'personal'=>__('Osobní web','cms_ve'),
          'business'=>__('Firemní web','cms_ve'),
          'blog'=>__('Blog','cms_ve'),
          'expert'=>__('Expertní web','cms_ve'),
          'product'=>__('Produktový web','cms_ve'),
          'portfolio'=>__('Portfolio','cms_ve'),         
          'fitness'=>__('Fitness web','cms_ve'),
          'photographer'=>__('Pro fotografy','cms_ve'),
          'reality'=>__('Realitní web','cms_ve'),
          'event'=>__('Web události','cms_ve'),
          'empty'=>__('Prázdný web','cms_ve'),

          'restaurant'=>array(__('Restaurace','cms_ve')),
          'wedding'=>array(__('Svatební web','cms_ve')),
          'hotel'=>array(__('Hotel a cestování','cms_ve')),
          'flower'=>array(__('Květinářství','cms_ve')),
          'beauty'=>array(__('Krása a wellness','cms_ve')),
          'music'=>array(__('Pro muzikanty','cms_ve')),
        )
    );             


    //Webs
    //***********************************************************************************

    $webInstalator->add_webs(
        array(
            'personal' => get_template_directory() . '/modules/visualeditor/web_templates/personal/',
            
            'firm' => get_template_directory() . '/modules/visualeditor/web_templates/firm/',
            'firm2' => get_template_directory() . '/modules/visualeditor/web_templates/firm2/',
        
            'expert3' => get_template_directory() . '/modules/visualeditor/web_templates/expert3/',
            'expert4' => get_template_directory() . '/modules/visualeditor/web_templates/expert4/',
            'expert2' => get_template_directory() . '/modules/visualeditor/web_templates/expert2/',
            'expert' => get_template_directory() . '/modules/visualeditor/web_templates/expert/',
            
            'blog_pzp' => get_template_directory() . '/modules/visualeditor/web_templates/blog_pzp/',
            'blog' => get_template_directory() . '/modules/visualeditor/web_templates/blog/',
            'blog2' => get_template_directory() . '/modules/visualeditor/web_templates/blog2/',
            'blog3' => get_template_directory() . '/modules/visualeditor/web_templates/blog3/',
            
            'photographer' => get_template_directory() . '/modules/visualeditor/web_templates/photograph/',
            'photographer2' => get_template_directory() . '/modules/visualeditor/web_templates/photographer2/',
            'photographer3' => get_template_directory() . '/modules/visualeditor/web_templates/photographer3/',
            
            'portfolio1' => get_template_directory() . '/modules/visualeditor/web_templates/portfolio1/',
            'portfolio3' => get_template_directory() . '/modules/visualeditor/web_templates/portfolio3/',
            'portfolio2' => get_template_directory() . '/modules/visualeditor/web_templates/portfolio2/',
            'portfolio4' => get_template_directory() . '/modules/visualeditor/web_templates/portfolio4/',
            
            'fitness' => get_template_directory() . '/modules/visualeditor/web_templates/fitness/',
            'yoga' => get_template_directory() . '/modules/visualeditor/web_templates/yoga/',
            
            'book' => get_template_directory() . '/modules/visualeditor/web_templates/book/',
            'servis' => get_template_directory() . '/modules/visualeditor/web_templates/sluzba/',
            'interier' => get_template_directory() . '/modules/visualeditor/web_templates/interier/',
            'jewelry' => get_template_directory() . '/modules/visualeditor/web_templates/jewelry/',
            
            'conference' => get_template_directory() . '/modules/visualeditor/web_templates/conference/',
            
            'reality' => get_template_directory() . '/modules/visualeditor/web_templates/reality/',
            'reality2' => get_template_directory() . '/modules/visualeditor/web_templates/reality2/',
            'reality3' => get_template_directory() . '/modules/visualeditor/web_templates/reality3/',
            
            'empty' => get_template_directory() . '/modules/visualeditor/web_templates/empty/',
        )
    );
}
// Templates
//***********************************************************************************

$cms->add_templates(array(
    'page' => array(
        'name' => __('Základní', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/page/',
        'list' => array(
            'empty' => array(
                'name' => __('Prázdné šablony', 'cms_ve'),
                'list' => array('1', '2')
            ),
        )
    ),
    'landing' => array(
        'name' => __('Domovské', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/landing/',
        'list' => array(
            'personal' => array(
                'name' => __('Osobní domovské stránky', 'cms_ve'),
                'list' => array('personal1', 'personal2', 'personal3', 'personal4', 'personal5', 'personal6')
            ),
            'land' => array(
                'name' => __('Univerzální domovské stránky', 'cms_ve'),
                'list' => array('land1', 'land2')
            ),
            'ebook' => array(
                'name' => __('Domovské stránky vhodné pro prodej knih nebo ebooků', 'cms_ve'),
                'list' => array('book1', 'book2', 'ebook2', 'ebook3')
            ),
        )
    ),

    'squeeze' => array(
        'name' => __('Vstupní', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/squeeze/',
        'list' => array(
            'sq1' => array(
                'name' => __('Základní typy vstupních stránek', 'cms_ve'),
                'list' => array('1', '2', '4', '5', '6', '7')
            ),
        ),
    ),
    'content' => array(
        'name' => __('Obsahové', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/page/',
        'list' => array(
            'content' => array(
                'name' => __('Obsahové šablony', 'cms_ve'),
                'list' => array('3', '4', '5', '6', '7', '8', '9', '10', '11')
            )
        )
    ),
    'webinar' => array(
        'name' => __('Webinářové', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/webinar/',
        'list' => array(
            'registration' => array(
                'name' => __('Stránky pro registraci na webinář', 'cms_ve'),
                'list' => array('4', '1', '2', '3')
            ),
            'live' => array(
                'name' => __('Stránky pro vysílání webináře', 'cms_ve'),
                'list' => array('live4', 'live1', 'live2', 'live3')
            ),
        )
    ),
    'sale' => array(
        'name' => __('Prodejní', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/sale/',
        'list' => array(
            'sale_letters' => array(
                'name' => __('Prodejní dopisy', 'cms_ve'),
                'list' => array('1', '2', '3', '4')
            ),
            'sale_form' => array(
                'name' => __('Stránky s prodejním (FAPI) formulářem', 'cms_ve'),
                'list' => array('form1', 'form2')
            ),
        )
    ),
    'thx' => array(
        'name' => __('Děkovací', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/others/',
        'list' => array(
            'thx' => array(
                'name' => '',
                'list' => array('thx1', 'thx3', 'thx4', 'thx5', 'thx_webinar1', 'thx_webinar2', '1', 'thx2')
            )
        )
    ),
    'others' => array(
        'name' => __('Ostatní', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/others/',
        'list' => array(
            'thx' => array(
                'name' => '',
                'list' => array('comming1', 'comming2')
            ),
        )
    ),
    'popups' => array(
        'name' => __('Klasické pop-upy', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/popups/',
        'type' => 'cms_popup',
        'list' => array(
            'registration' => array(
                'name' => '',
                'list' => array('1', '2', '3', '4', '5', '6', '7')
            ),
        )
    ),
    'el_variables' => array(
        'name' => __('Předdefinovaný obsah', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/element_variables/',
        'type' => 've_elvar',
        'list' => array(
            'registration' => array(
                'name' => '',
                'list' => array('1')
            ),
        )
    ),
    'mw_sliders' => array(
        'name' => __('Slider', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/sliders/',
        'type' => 'mw_slider',
        'list' => array(
            'registration' => array(
                'name' => '',
                'list' => array('1', '2', '3', '4', '5')
            ),
        )
    ),
    'before_headers' => array(
        'name' => __('Základní', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/headers/before/',
        'type' => 've_header',
        'list' => array(
            'bheaders' => array(
                'name' => '',
                'list' => array('1', '2', '3', '4')
            ),
        )
    ),
    'footers' => array(
        'name' => __('Klasické patičky', 'cms_ve'),
        'path' => '/modules/visualeditor/templates/footers/',
        'type' => 'cms_footer',
        'list' => array(
            'cfooters' => array(
                'name' => '',
                'list' => array('1', '2', 'empty')
            ),
        )
    ),
));


$vePage->list_buttons = array(
    '1' => "",
    '2' => "'border=1'",
    '3' => "'border=1'",
    '4' => "'border=1'",
    '5' => "'border=1'",
    '6' => "",
    '7' => "'border=1'",
    '8' => "",
    '9' => "",
    '10' => "",
    '11' => "",
);

$vePage->list_patterns = array(
    '1' => VS_DIR . 'images/patterns/',
    '2' => VS_DIR . 'images/patterns/',
    '3' => VS_DIR . 'images/patterns/',
    '4' => VS_DIR . 'images/patterns/',
    '5' => VS_DIR . 'images/patterns/',
    '6' => VS_DIR . 'images/patterns/',
    '7' => VS_DIR . 'images/patterns/',
    '8' => VS_DIR . 'images/patterns/',
    '9' => VS_DIR . 'images/patterns/',
    '10' => VS_DIR . 'images/patterns/',
    '11' => VS_DIR . 'images/patterns/',
    '12' => VS_DIR . 'images/patterns/',
    '13' => VS_DIR . 'images/patterns/',
    '14' => VS_DIR . 'images/patterns/',
    '15' => VS_DIR . 'images/patterns/',
    '16' => VS_DIR . 'images/patterns/',
    '17' => VS_DIR . 'images/patterns/',
    '18' => VS_DIR . 'images/patterns/',
    '19' => VS_DIR . 'images/patterns/',
    '20' => VS_DIR . 'images/patterns/',
);

$vePage->list_icons = array(
    '' => VS_DIR . 'images/image_select/li0.png',
    '1' => VS_DIR . 'images/image_select/li1.png',
    '2' => VS_DIR . 'images/image_select/li2.png',
    '3' => VS_DIR . 'images/image_select/li3.png',
    '4' => VS_DIR . 'images/image_select/li4.png',
    '5' => VS_DIR . 'images/image_select/li5.png',
    '6' => VS_DIR . 'images/image_select/li6.png',
    '7' => VS_DIR . 'images/image_select/li7.png',
    '8' => VS_DIR . 'images/image_select/li8.png',

    //'9' => VS_DIR . 'images/image_select/li9.png',
    '10' => VS_DIR . 'images/image_select/li10.png',
    '11' => VS_DIR . 'images/image_select/li11.png',
    '12' => VS_DIR . 'images/image_select/li12.png',
    '13' => VS_DIR . 'images/image_select/li13.png',
    '14' => VS_DIR . 'images/image_select/li14.png',
    '15' => VS_DIR . 'images/image_select/li15.png',
    '16' => VS_DIR . 'images/image_select/li16.png',

    '17' => VS_DIR . 'images/image_select/li17.png',
    '18' => VS_DIR . 'images/image_select/li18.png',
    '19' => VS_DIR . 'images/image_select/li19.png',
    '20' => VS_DIR . 'images/image_select/li20.png',
    '21' => VS_DIR . 'images/image_select/li21.png',
    '22' => VS_DIR . 'images/image_select/li22.png',
    '23' => VS_DIR . 'images/image_select/li23.png',
    '24' => VS_DIR . 'images/image_select/li24.png',

    '25' => VS_DIR . 'images/image_select/li25.png',
    '26' => VS_DIR . 'images/image_select/li26.png',
    '27' => VS_DIR . 'images/image_select/li27.png',
    '28' => VS_DIR . 'images/image_select/li28.png',
    '29' => VS_DIR . 'images/image_select/li29.png',
    '30' => VS_DIR . 'images/image_select/li30.png',
    '31' => VS_DIR . 'images/image_select/li31.png',
    '32' => VS_DIR . 'images/image_select/li32.png',

    '33' => VS_DIR . 'images/image_select/li33.png',
    '34' => VS_DIR . 'images/image_select/li34.png',
    '35' => VS_DIR . 'images/image_select/li35.png',
    '36' => VS_DIR . 'images/image_select/li36.png',
    '37' => VS_DIR . 'images/image_select/li37.png',
    '38' => VS_DIR . 'images/image_select/li38.png',
    '39' => VS_DIR . 'images/image_select/li39.png',
    '40' => VS_DIR . 'images/image_select/li40.png',

    '41' => VS_DIR . 'images/image_select/li41.png',
    '42' => VS_DIR . 'images/image_select/li42.png',
    '43' => VS_DIR . 'images/image_select/li43.png',
    '44' => VS_DIR . 'images/image_select/li44.png',
    '45' => VS_DIR . 'images/image_select/li45.png',
    '46' => VS_DIR . 'images/image_select/li46.png',
);
$vePage->set_list['headers'] = array(
    'type1' => array(
        'thumb' => VS_DIR . 'images/image_select/header1.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type1c' => array(
        'thumb' => VS_DIR . 'images/image_select/header14.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type1b' => array(
        'thumb' => VS_DIR . 'images/image_select/header12.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type2' => array(
        'thumb' => VS_DIR . 'images/image_select/header3.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type3' => array(
        'thumb' => VS_DIR . 'images/image_select/header2.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type4' => array(
        'thumb' => VS_DIR . 'images/image_select/header4.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type5' => array(
        'thumb' => VS_DIR . 'images/image_select/header5.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type1.php',
    ),
    'type12' => array(
        'thumb' => VS_DIR . 'images/image_select/header13.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type5.php',
    ),
    'type11' => array(
        'thumb' => VS_DIR . 'images/image_select/header11.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type4.php',
    ),
    'type6' => array(
        'thumb' => VS_DIR . 'images/image_select/header6.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type2.php',
    ),
    'type7' => array(
        'thumb' => VS_DIR . 'images/image_select/header7.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type2.php',
    ),
    'type8' => array(
        'thumb' => VS_DIR . 'images/image_select/header8.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type2.php',
    ),
    'type9' => array(
        'thumb' => VS_DIR . 'images/image_select/header9.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type3.php',
    ),
    'type10' => array(
        'thumb' => VS_DIR . 'images/image_select/header10.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/headers/header-type3.php',
    ),
);

$vePage->set_list['footers'] = array(
    'type1' => array(
        'thumb' => VS_DIR . 'images/image_select/footer1.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/footers/footer1.php',
    ),
    'type2' => array(
        'thumb' => VS_DIR . 'images/image_select/footer2.png',
        'file' => get_template_directory() . '/modules/visualeditor/templates/footers/footer1.php',
    ),
);
$vePage->add_rows(
    array(
        'tab' => __('Obsah', 'cms_ve'),
        'id' => 'content',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Hlavička stránky s nadpisem uprostřed', 'cms_ve'), 'content'=>'page_title'),
            array('title' => __('Hlavička stránky s nadpisem vlevo', 'cms_ve'), 'content'=>'page_title2'),
            array('title' => __('Text s nadpisem', 'cms_ve'), 'content'=>'onecol'),
            array('title' => __('Dva sloupce', 'cms_ve'), 'content'=>'twocols2'),
            array('title' => __('Tři sloupce', 'cms_ve'), 'content'=>'threecols2'),
            array('title' => __('Dva sloupce s nadpisem', 'cms_ve'), 'content'=>'twocols'),
            array('title' => __('Tři sloupce s nadpisem', 'cms_ve'), 'content'=>'threecols'),
            array('title' => __('Důležitý nadpis', 'cms_ve'), 'content'=>'title'),
            array('title' => __('Dva sloupce s obrázky', 'cms_ve'), 'content'=>'twocols_images'),
            array('title' => __('Tři sloupce s obrázky', 'cms_ve'), 'content'=>'threecols_images'),
            array('title' => __('Řádek s obrázkem', 'cms_ve'), 'content'=>'image_row'),
            array('title' => __('Nadpis a text s obrázkem na levo', 'cms_ve'), 'content'=>'image_text1'),
            array('title' => __('Text s obrázkem na pravo', 'cms_ve'), 'content'=>'image_text2'),
            array('title' => __('Text s obrázkem na levo', 'cms_ve'), 'content'=>'image_text3'),
            array('title' => __('Čísla', 'cms_ve'), 'content'=>'numbers'),
            array('title' => __('FAQ', 'cms_ve'), 'content'=>'faq1'),
            array('title' => __('FAQ', 'cms_ve'), 'content'=>'faq2'),
            array('title' => __('Ceník', 'cms_ve'), 'content'=>'pricelist'),
            array('title' => __('Ceník', 'cms_ve'), 'content'=>'pricelist2'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Hlavička', 'cms_ve'),
        'id' => 'heads',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Hlavička s obsahem vlevo', 'cms_ve'), 'content'=>'header1'),
            array('title' => __('Hlavička s obsahem vpravo', 'cms_ve'), 'content'=>'header2'),
            array('title' => __('Hlavička s obsahem uprostřed', 'cms_ve'), 'content'=>'header3'),
            array('title' => __('Hlavička s obsahem nahoře', 'cms_ve'), 'content'=>'header4'),
            array('title' => __('Hlavička s tučným textem', 'cms_ve'), 'content'=>'header5'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Slider', 'cms_ve'),
        'id' => 'sliders',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Slider', 'cms_ve'), 'content'=>'slider'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Galerie', 'cms_ve'),
        'id' => 'gallery',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Galerie přes celou šířku', 'cms_ve'), 'content'=>'gallery'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Magnet', 'cms_ve'),
        'id' => 'magnet',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Stažení e-booku', 'cms_ve'), 'content'=>'magnet1'),
            array('title' => __('Stažení e-booku', 'cms_ve'), 'content'=>'magnet2'),
            array('title' => __('Magnet', 'cms_ve'), 'content'=>'magnet3'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Služby/vlastnosti', 'cms_ve'),
        'id' => 'services',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Služby', 'cms_ve'), 'content'=>'service1'),
            array('title' => __('Služby', 'cms_ve'), 'content'=>'service2'),
            array('title' => __('Služby', 'cms_ve'), 'content'=>'service3'),
            array('title' => __('Vlastnosti', 'cms_ve'), 'content'=>'properties1'),
            array('title' => __('Vlastnosti', 'cms_ve'), 'content'=>'properties2'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Reference', 'cms_ve'),
        'id' => 'testimonials',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Reference', 'cms_ve'), 'content'=>'testimonials1'),
            array('title' => __('Reference', 'cms_ve'), 'content'=>'testimonials2'),
            array('title' => __('Reference', 'cms_ve'), 'content'=>'testimonials3'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Lidé', 'cms_ve'),
        'id' => 'peoples',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('O mně', 'cms_ve'), 'content'=>'people1'),
            array('title' => __('O mně', 'cms_ve'), 'content'=>'people2'),
            array('title' => __('Lidé', 'cms_ve'), 'content'=>'people3'),
            array('title' => __('O mně', 'cms_ve'), 'content'=>'people4'),
            array('title' => __('O mně', 'cms_ve'), 'content'=>'people5'),
        )
    )
);
$vePage->add_rows(
    array(
        'tab' => __('Kontakt', 'cms_ve'),
        'id' => 'contact',
        'type' => 'template',
        'layouts' => array(
            array('title' => __('Google mapa', 'cms_ve'), 'content'=>'google_map'),
            array('title' => __('Kontakt', 'cms_ve'), 'content'=>'contact1'),
            array('title' => __('Kontakt', 'cms_ve'), 'content'=>'contact2'),
            array('title' => __('Kontakt', 'cms_ve'), 'content'=>'contact3'),
            array('title' => __('Kontakt', 'cms_ve'), 'content'=>'contact4'),
        )
    )
);
$vePage->add_rows(
    array(
                'tab' => __('Prázdné', 'cms_ve'),
                'id' => 'basic',
                'layouts' => array(
                    array('title' => __('Jeden sloupec', 'cms_ve'), 'content' => 'one'),
                    array('title' => __('Dva sloupce', 'cms_ve'), 'content' => 'two-two'),
                    array('title' => __('Tři sloupce', 'cms_ve'), 'content' => 'three-three-three'),
                    array('title' => __('Čtyři sloupce', 'cms_ve'), 'content' => 'four-four-four-four'),
                    array('title' => __('Pět sloupců', 'cms_ve'), 'content' => 'five-five-five-five-five'),
                    array('title'=>__('Kombinované sloupce', 'cms_ve'), 'type' => 'title'),
                    array('title' => '1/3 2/3', 'content' => 'three-twothree'),
                    array('title' => '2/3 1/3', 'content' => 'twothree-three'),
                    array('title' => '1/4 1/4 2/4', 'content' => 'four-four-twofour'),
                    array('title' => '2/4 1/4 1/4', 'content' => 'twofour-four-four'),
                    array('title' => '1/4 3/4', 'content' => 'four-threefour'),
                    array('title' => '3/4 1/4', 'content' => 'threefour-four'),
                    array('title' => '1/4 2/4 1/4', 'content' => 'four-twofour-four'),
                    array('title' => '1/5 1/5 1/5 2/5', 'content' => 'five-five-five-twofive'),
                    array('title' => '2/5 1/5 1/5 1/5', 'content' => 'twofive-five-five-five'),
                    array('title' => '1/5 1/5 3/5', 'content' => 'five-five-threefive'),
                    array('title' => '3/5 1/5 1/5', 'content' => 'threefive-five-five'),
                    array('title' => '1/5 4/5', 'content' => 'five-fourfive'),
                    array('title' => '4/5 1/5', 'content' => 'fourfive-five'),
                    array('title' => '1/5 3/5 1/5', 'content' => 'five-threefive-five'),
                    array('title' => '2/5 3/5', 'content' => 'twofive-threefive'),
                    array('title' => '3/5 2/5', 'content' => 'threefive-twofive'),                  
                )
    )
);


require_once('elements.php');

$cms->container['slider_setting']=array(
                      array(
                          'id' => 'use_slider',
                          'title' => '',
                          'type' => 'checkbox',
                          'label' => __('Zobrazit jako slider','cms_ve'),
                          'show'=>'sliderset',
                      ),
                      array(
                            'id'=>'sliderset_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'animation',
                                    'title'=>__('Typ animace','cms_ve'),
                                    'type'=>'select',
                                    'content'=> 'fade',
                                    'options' => array(
                                      array('name' => __('Prolínání','cms_ve'), 'value' => 'fade'),
                                      array('name' => __('Zprava doleva','cms_ve'), 'value' => 'slide'),
                                    ),
                                ),                      
                                array(
                                    'id' => 'delay',
                                    'title' => __('Zpoždění slidů','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '3500',
                                ),
                                array(
                                    'id' => 'speed',
                                    'title' => __('Délka animace','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '1000',
                                ),
                                array(
                                    'id' => 'off_autoplay',
                                    'title' => __('Autoplay','cms_ve'),
                                    'type' => 'checkbox',
                                    'label' => __('Vypnout autoplay','cms_ve'),
                                ),
                                array(
                                    'id'=>'color_scheme',
                                    'title'=>__('Barva ovládacích prvků','cms_ve'),
                                    'type'=>'select',
                                    'content'=> '',
                                    'options' => array(
                                      array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
                                      array('name' => __('Tmavé','cms_ve'), 'value' => ''),
                                    ),
                                ),
                            
                            ),
                            'show_group' => 'sliderset',
                      ),

);

$cms->container['header_setting'] = array(
    array(
        'id'=>'logo_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Logo','cms_ve'),
        'setting'=>array(
            array(
                'type' => 'tabs',  
                'id' => 'logo_setting',
                'content' => 'image',
                'tabs' => array(
                    'image' => array(
                        'name' => __('Obrázkové logo', 'cms_ve'),
                        'setting' => array(
                            array(
                                'id' => 'logo',
                                'type' => 'upload',
                                'content' => VS_DEFAULT_DIR . 'images/default/logo1.png',
                            ),
                            array(
                                'name' => __('Velikost loga', 'cms_ve'),
                                'id' => 'logo_size',
                                'type' => 'size',
                                'content'=>'',
                                'unit'=>'px'
                            ),
                        ),
                    ),
                    'text' => array(
                        'name' => __('Textové logo', 'cms_ve'),
                        'setting' => array(
                            array(
                                'name' => __('Text loga', 'cms_ve'),
                                'id' => 'logo_text',
                                'type' => 'text',
                                'content' => __('Název webu', 'cms_ve'),
                            ),
                            array(
                                'name' => __('Font loga', 'cms_ve'),
                                'id' => 'logo_font',
                                'type' => 'font',
                                'content' => array(
                                    'font-size' => '25',
                                    'font-family' => '',
                                    'weight' => '',
                                    'color' => '',
                                ),
                            ),
                        )
                    ),
                )
            ),
        )
    ),
    array(
        'id'=>'basic_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Základní nastavení hlavičky','cms_ve'),
        'setting'=>array(
            array(
                'name' => __('Menu', 'cms_ve'),
                'id' => 'menu',
                'type' => 'selectmenu',
            ),
            array(
                'name' => __('Barva pozadí hlavičky', 'cms_ve'),
                'id' => 'background_color',
                'type' => 'background',
                'content' => array('color1' => '#ffffff', 'color2' => '', 'transparency' => '100'),
            ),
            
            array(
                'name' => __('Typ hlavičky', 'cms_ve'),
                'id' => 'appearance',
                'type' => 'imageselect',
                'set_list' => 'headers',
                'content' => 'type1',
                'show' => 'header_appearanace'
            ),
            
        )
    ),
    array(
        'id'=>'menu_setting',
        'type'=>'toggle_group',
        'title'=>__('Vzhled menu','cms_ve'),
        'setting'=>array(        
            array(
                'name' => __('Font menu', 'cms_ve'),
                'id' => 'menu_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '15',
                    'font-family' => '',
                    'weight' => '',
                    'color' => '#575757',
                ),
            ),
            array(
                'name' => __('Barva aktivní položky', 'cms_ve'),
                'id' => 'menu_active_color',
                'type' => 'color',
                'content' => '#158ebf',
                'desc' => __('Touto barvou bude nastaveno také pozadí podmenu.', 'cms_ve'),
            ),
            array(
                'name' => __('Barva textu aktivní položky (pokud má položka pozadí)', 'cms_ve'),
                'id' => 'menu_submenu_text_color',
                'type' => 'color',
                'content' => '#FFFFFF',
                'desc' => __('Touto barvou bude nastaven text podmenu.', 'cms_ve'),
                'show_group' => 'header_appearanace',
                'show_val' => 'type4,type5,type8,type9,type10',
            ),
            array(
                'name' => __('Pozadí menu', 'cms_ve'),
                'id' => 'menu_bg',
                'type' => 'background',
                'content' => array('color1' => '#121212', 'color2' => ''),
                'show_group' => 'header_appearanace',
                'show_val' => 'type5,type8,type9,type10',
            ),
        )
    ),
    array(
        'id'=>'advanced_setting',
        'type'=>'toggle_group',
        'title'=>__('Pokročilé nastavení hlavičky','cms_ve'),
        'setting'=>array(
        
            array(
                'id' => 'before_header',
                'title' => __('Obsah před hlavičkou', 'cms_ve'),
                'type' => 'weditor',
                'setting' => array(
                    'post_type' => 've_header',
                    'templates' => 'before_headers',
                    'texts' => array(
                        'empty' => __(' - Bez obsahu - ', 'cms_ve'),
                        'edit' => __('Upravit vybraný obsah', 'cms_ve'),
                        'duplicate' => __('Duplikovat vybraný obsah', 'cms_ve'),
                        'create' => __('Vytvořit nový obsah', 'cms_ve'),
                        'delete' => __('Smazat vybraný obsah', 'cms_ve'),
                    ),
                )
            ),
            array(
                'name' => __('Obrázek na pozadí hlavičky', 'cms_ve'),
                'id' => 'background_image',
                'type' => 'bgimage',
                'hide' => array('color_filter'),
            ),
            array(
                'name' => __('Šířka obsahu hlavičky', 'cms_ve'),
                'id' => 'header_width',
                'type' => 'size',
                'content' => array(
                    'size' => '',
                    'unit' => 'px'
                ),
            ),
            array(
                'name' => __('Horní a spodní odsazení hlavičky (padding)', 'cms_ve'),
                'id' => 'header_padding',
                'type' => 'size',
                'unit' => 'px',
                'content' => '20'
            ), 
        )
    ),
    array(
        'id'=>'fixed_header',
        'type'=>'toggle_group',
        'title'=>__('Fixní hlavička','cms_ve'),
        'checkbox'=>0,
        'setting'=>array(
            array(
                'name' => __('Po odskrolování změnit nastavení hlavičky na', 'cms_ve'),
                'type' => 'title',
            ),
            array(
                'name' => __('Barva pozadí hlavičky', 'cms_ve'),
                'id' => 'background_color_fix',
                'type' => 'background',
                'content' => array('color1' => '', 'color2' => '', 'transparency' => '100'),
            ),
            array(
                'name' => __('Horní a spodní odsazení hlavičky (padding)', 'cms_ve'),
                'id' => 'header_padding_fix',
                'type' => 'size',
                'unit' => 'px',
            ),
            array(
                'name' => __('Stín pod hlavičkou', 'cms_ve'),
                'id' => 'header_shadow_fix',
                'type' => 'checkbox',
                'label' => __('Zobrazit pod hlavičkou stín', 'cms_ve'),
            ),
        ),
    ) 

);

$cms->container['footer_setting'] = array(
                array(
                    'id'=>'footer_content',
                    'type'=>'toggle_group',
                    'open'=>true,
                    'title'=>__('Obsah patičky','cms_ve'),
                    'setting'=>array(
                        array(
                            'name' => __('Obsah patičky', 'cms_ve'),
                            'id' => 'custom_footer',
                            'type' => 'footerselect',
                            'desc' => __('Obsah patičky můžete vytvářet nebo editovat pomocí vizuálního editoru.', 'cms_ve'),
                        ),
                    )
                ),
                array(
                    'id'=>'hide_footer_end',
                    'type'=>'toggle_group',
                    'checkbox'=>true,
                    'invert'=>true,
                    'title'=>__('Zobrazit koncovku patičky','cms_ve'),
                    'setting'=>array(
                        array(
                            'name' => __('Typ koncovky patičky', 'cms_ve'),
                            'id' => 'appearance',
                            'type' => 'imageselect',
                            'set_list' => 'footers',
                            'content' => 'type1',
                        ),
                        array(
                            'name' => __('Copyright text', 'cms_ve'),
                            'id' => 'text',
                            'type' => 'text',
                        ),
                        array(
                            'name' => __('Menu', 'cms_ve'),
                            'id' => 'menu',
                            'type' => 'selectmenu',
                        ),
                        array(
                            'name' => __('Pozadí koncovky patičky', 'cms_ve'),
                            'id' => 'background_color',
                            'type' => 'background',
                            'content' => array('color1' => '', 'color2' => '', 'transparency' => '100'),
                        ),
                        array(
                            'name' => __('Obrázek na pozadí koncovky patičky', 'cms_ve'),
                            'id' => 'background_image',
                            'type' => 'bgimage',
                        ),
                        array(
                            'name' => __('Font', 'cms_ve'),
                            'id' => 'font',
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '15',
                                'font-family' => '',
                                'weight' => '',
                                'color' => '#7a7a7a',
                            ),
                        ),
                    )
                ),
                array(
                    'id'=>'footer_advanced_setting',
                    'type'=>'toggle_group',
                    'title'=>__('Pokročilé nastavení patičky','cms_ve'),
                    'setting'=>array(
                        array(
                            'name' => __('Šířka patičky', 'cms_ve'),
                            'id' => 'footer_width',
                            'type' => 'size',
                            'content' => array(
                                'size' => '',
                                'unit' => 'px'
                            ),
                        ),
                    )
                )
);

$cms->container['appearance_setting'] = array(
    array(
        'id'=>'background_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Pozadí','cms_ve'),
        'setting'=>array(
            array(
                'name' => __('Barva pozadí', 'cms_ve'),
                'id' => 'background_color',
                'type' => 'color',
                'content' => '#ebebeb',
            ),
            array(
                'name' => __('Obrázek na pozadí', 'cms_ve'),
                'id' => 'background_image',
                'type' => 'bgimage',
                'content' => array(
                    'pattern' => 0,
                    'fixed' => 'fixed'
                )
            ),
        )
    ),
    array(
        'id'=>'text_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Formátování textů','cms_ve'),
        'setting'=>array(
            array(
                'name' => __('Font nadpisů', 'cms_ve'),
                'id' => 'title_font',
                'type' => 'font',
                'content' => array(
                    'font-family' => 'Open Sans',
                    'weight' => '600',
                    'color' => '',
                ),
            ),
            array(
                'name' => __('Font textů', 'cms_ve'),
                'id' => 'font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '16',
                    'font-family' => 'Open Sans',
                    'line-height' => '',
                    'weight' => '400',
                    'color' => '#111111',
                ),
            ),        
            array(
                'name' => __('Barva odkazů', 'cms_ve'),
                'id' => 'link_color',
                'type' => 'color',
                'content' => '#158ebf'
            ),
        )
    ),
    array(
        'id'=>'element_text_setting',
        'type'=>'toggle_group',
        'title'=>__('Formátování textu v textovém elementu','cms_ve'),
        'setting'=>array(
            array(
                'name' => __('Nadpis 1 (H1)', 'cms_ve'),
                'id' => 'h1_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '40',
                    'color' => '',
                ),
            ),
            array(
                'name' => __('Nadpis 2 (H2)', 'cms_ve'),
                'id' => 'h2_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '30',
                    'color' => '',
                ),
            ),
            array(
                'name' => __('Nadpis 3 (H3)', 'cms_ve'),
                'id' => 'h3_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '20',
                    'color' => '',
                ),
            ),
            array(
                'name' => __('Nadpis 4 (H4)', 'cms_ve'),
                'id' => 'h4_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '16',
                    'color' => '',
                ),
            ),
            array(
                'name' => __('Nadpis 5 (H5)', 'cms_ve'),
                'id' => 'h5_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '16',
                    'color' => '',
                ),
            ),
            array(
                'name' => __('Nadpis 6 (H6)', 'cms_ve'),
                'id' => 'h6_font',
                'type' => 'font',
                'content' => array(
                    'font-size' => '16',
                    'color' => '',
                ),
            ),
            array(
                'id' => 'li',
                'name' => __('Styl odrážek', 'cms_ve'),
                'type' => 'imageselect',
                'content' => '1',
                'options' => $vePage->list_icons,
            ),
        )
    )
);
$cms->container['popup_setting'] = array(
    array(
        'id'=>'clasic_popup_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Klasický popup','cms_ve'),
        'setting'=>array(
            array(
                'name' => __('Klasický pop-up', 'cms_ve'),
                'id' => 'clasic_popup',
                'type' => 'popupselect',
                'tooltip' => __('Tento pop-up se zobrazí po načtení stránky nebo při splnění zadané podmínky v pokročilém nastavení.', 'cms_ve'),
            ),
            array(
                'id' => 'popup_type',
                'name' => __('Zobrazit pop-up', 'cms_ve'),
                'type' => 'radio',
                'show' => 'popup_type',
                'options' => array(
                    'onload' => __('Po načtení stránky', 'cms_ve'),
                    'advance' => __('Pokročilé nastavení', 'cms_ve'),
                ),
                'content' => 'onload',
            ),
            array(
                'name' => __('Zobrazit po x sekundách', 'cms_ve'),
                'id' => 'time',
                'type' => 'text',
                'desc' => __('Pop-up se zobrazí po x sekundách od načtení stránky.', 'cms_ve'),
                'show_group' => 'popup_type',
                'show_val' => 'advance',
            ),
            array(
                'name' => __('Zobrazit po odskrolování', 'cms_ve'),
                'id' => 'scroll',
                'type' => 'size',
                'content' => array(
                    'size' => '',
                    'unit' => 'px'
                ),
                'desc' => __('Pop-up se zobrazí po odskrolování zadané části stránky (v % nebo v px).', 'cms_ve'),
                'show_group' => 'popup_type',
                'show_val' => 'advance',
            ),
            array(
                'name' => __('Zobrazit po naskrolování na prvek s CSS selektorem', 'cms_ve'),
                'id' => 'selector',
                'type' => 'text',
                'placeholder' => __('.class nebo #id', 'cms_ve'),
                'desc' => __('Pop-up se zobrazí po naskrolování na prvek stránky se zadaným CSS selektorem.', 'cms_ve'),
                'show_group' => 'popup_type',
                'show_val' => 'advance',
            ),
        )
    ),
    array(
        'id'=>'clasic_popup_setting',
        'type'=>'toggle_group',
        'title'=>__('Exit pop-up','cms_ve'),
        'setting'=>array(
            array(
                'name' => __('Exit pop-up', 'cms_ve'),
                'id' => 'exit_popup',
                'type' => 'popupselect',
                'tooltip' => __('Tento pop-up se zobrazí v momentě, kdy uživatel vyjede myší do horní části prohlížeče.', 'cms_ve'),
            ),
        )
    )
);

// Top panel menu
//***********************************************************************************

$vePage->add_top_panel_menu(5, array('id' => 'web', 'title' => 'Web', 'url' => home_url(), 'submenu' => $vePage->create_web_menu()));


// Nastavení stránek
//*********************************************************************************** 

$cms->add_set(array(
    'id' => 'page_statistics',
    'title' => __('A/B testování', 'cms_ve'),
    'include' => array('page'),
    'fields' => array(
        array(
            'name' => __('Výsledky testování', 'cms_ve'),
            'id' => 'statistics',
            'type' => 'page_statistics',
        ),
        array(
            'name' => __('Cílová stránka pro výpočet konverze', 'cms_ve'),
            'id' => 'target',
            'type' => 'publish_selectpage',
            'desc' => __('Pokud nastavíte cílovou stránku, bude se u této stránky počítat konverzní poměr, který bude informovat o tom, kolik procent návštěvníků se z této stránky dostalo na zadanou cílovou stránku. To znamená, kolik jich například kliklo na tlačítko, které odkazovalo na cílovou stránku.', 'cms_ve'),
        ),
        array(
            'name' => __('Nastavení A/B testování', 'cms_ve'),
            'type' => 'title',
        ),
        array(
            'name' => __('Další varianty stránky', 'cms_ve'),
            'id' => 'pages',
            'type' => 'multipageselect',
            'tooltip' => __('Každému návštěvníkovi této URL se zobrazí náhodně vybraná varianta stránky nebo originál stránky.', 'cms_ve'),
            'desc' => __('Zadáním dalších variant stránek se bude návštěvníkům náhodně zobrazovat jedna ze zadaných stránek (včetně té originální). U každé varianty se bude počítat míra konverze. Můžete tak zjistit, která ze stránek lépe konvertuje. Pokud uživatel navštíví stránku, přiřadí se mu jedna z variant, kterou si to zapamatuje po 48 hodin. Po této době, když ten samý uživatel navštíví stránku znovu, může se mu zobrazit jiná varianta a zároveň se stránce přičte další návštěva.', 'cms_ve'),
        ),
    )
), "page_set");

$cms->define_set(array(
    'id' => 'popup_set',
    'title' => __('Nastavení pop-upu', 'cms_ve'),
    'context' => 'normal',
    'priority' => 'high',
    'include' => array('cms_popup'),
));
$cms->add_set(array(
    'id' => 've_popup',
    'title' => __('Pop-upy', 'cms_ve'),
    'include' => array('cms_popup'),
    'fields' => array(
        array(
            'name' => __('Šířka pop-upu', 'cms_ve'),
            'id' => 'width',
            'type' => 'size',
            'content' => array(
                'size' => '800',
                'unit' => 'px'
            )
        ),
        array(
            'name' => __('Pozadí za pop-upem', 'cms_ve'),
            'id' => 'background',
            'type' => 'color',
            'content' => '#000000',
        ),
        array(
            'id' => 'corner',
            'name' => __('Míra zakulacení rohů (v px)', 'cms_ve'),
            'type' => 'text',
            'content' => '0',
            'desc' => __('Pro ostré rohy zadejte nulu.', 'cms_ve'),
        ),
        array(
            'id' => 'delay',
            'name' => __('Znovu zobrazit po x dnech', 'cms_ve'),
            'type' => 'text',
            'content' => '2',
            'desc' => __('Pokud se návštěvníkovi pop-up zobrazí a on jej zavře, tak se mu při další návštěvě znovu zobrazí až po x dnech.', 'cms_ve'),
        ),

    )
), "popup_set");

$cms->add_set(array(
    'id' => 've_popup',
    'title' => __('Pop-upy', 'cms_ve'),
    'include' => array('page', 'post'),
    'fields' => array(
        array(
            'name' => __('Použít', 'cms_ve'),
            'id' => 'show',
            'type' => 'radio',
            'show' => 'popupset',
            'options' => array(
                'global' => __('Globální pop-upy', 'cms_ve'),
                'page' => __('Vlastní pop-upy', 'cms_ve'),
            ),
            'content' => 'global',
        ),
        array(
            'id' => 'popup_group',
            'type' => 'group',
            'setting' => $cms->container['popup_setting'],
            'show_group' => 'popupset',
            'show_val' => 'page',
        )
    )
), "page_set");

$cms->define_set(array(
    'id' => 've_page_appearance',
    'title' => __('Vzhled stránky', 'cms_ve'),
    'context' => 'normal',
    'priority' => 'high',
    'include' => array('page'),
));      

$cms->add_set(array(
    'id' => 've_appearance',
    'title' => __('Pozadí a formátování', 'cms_ve'),
    'fields' => array(
        array(
            'id'=>'page_background_setting',
            'type'=>'toggle_group',
            'open'=>true,
            'title'=>__('Pozadí','cms_ve'),
            'setting'=>array(
                array(
                    'name' => __('Barva pozadí', 'cms_ve'),
                    'id' => 'background_color',
                    'type' => 'color',
                ),
                array(
                    'type' => 'tabs',
                    'id' => 'background_setting',
                    'style' => 'border',
                    'tabs' => array(
                        'image' => array(
                            'name' => __('Obrázek na pozadí', 'cms_ve'),
                            'setting' => array(
                                array(
                                    'name' => __('Obrázek na pozadí', 'cms_ve'),
                                    'id' => 'background_image',
                                    'type' => 'bgimage',
                                    'content' => array(
                                        'pattern' => 0,
                                        'fixed' => 'fixed'
                                    )
                                ),
                            )
                        ),
                        'slider'=>array(
                            'name' => __('Slider na pozadí','cms_ve'),
                            'setting'=>array(
                                array(
                                    'id' => 'background_delay',
                                    'title' => __('Zpoždění slidů','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '3000',
                                ),
                                array(
                                    'id' => 'background_speed',
                                    'title' => __('Délka animace','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '1500',
                                ),
                                array(
                                    'id'=>'background_slides',
                                    'title'=>__('Slidy','cms_ve'),
                                    'type'=>'multielement',
                                    'texts'=>array(
                                        'add'=>__('Přidat slide','cms_ve'),
                                    ),
                                    'setting'=>array(                           
                                        array(
                                            'id'=>'image',
                                            'title'=>'',
                                            'type'=>'image',                                                  
                                        ),
                                    ),
                                ),
                            )
                        ),
                        'video' => array(
                            'name' => __('Video na pozadí', 'cms_ve'),
                            'setting' => array(
                                array(
                                    'id' => 'video_info',
                                    'name' => '',
                                    'type' => 'info',
                                    'content' => __('Doporučuje se vložit video v co nejvíce formátech uvedených níže pro lepší kompatibilitu napříč všemi prohlížeči. Video v jednom formátu nemusí fungovat všude.', 'cms_ve'),
                                ),
                                array(
                                    'name' => __('Video ve formátu .mp4', 'cms_ve'),
                                    'id' => 'background_video_mp4',
                                    'type' => 'upload_file',
                                ),
                                array(
                                    'name' => __('Video ve formátu .webm', 'cms_ve'),
                                    'id' => 'background_video_webm',
                                    'type' => 'upload_file',
                                ),
                                array(
                                    'name' => __('Video ve formátu .ogg', 'cms_ve'),
                                    'id' => 'background_video_ogg',
                                    'type' => 'upload_file',
                                ),
                                array(
                                    'id' => 'video_setting',
                                    'title' => __('Nastavení videa na pozadí', 'cms_ve'),
                                    'type' => 'multiple_checkbox',
                                    'options' => array(
                                        array('name' => __('Zapnout zvuk videa', 'cms_ve'), 'value' => 'sound'),
                                        array('name' => __('Zobrazovat video na mobilních zařízeních', 'cms_ve'), 'value' => 'show_mobile'),
                                    ),
                                ),
                            )
                        ),
                    )
                ),
            )
        ),
        array(
            'id'=>'text_setting',
            'type'=>'toggle_group',
            'open'=>true,
            'title'=>__('Formátování textů','cms_ve'),
            'setting'=>array(
                array(
                    'name' => __('Font nadpisů', 'cms_ve'),
                    'id' => 'title_font',
                    'type' => 'font',
                    'content' => array(
                        'font-family' => '',
                        'weight' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Font stránky', 'cms_ve'),
                    'id' => 'font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'font-family' => '',
                        'weight' => '',
                        'line-height' => '',
                        'color' => '',
                    ),
                ),        
                array(
                    'name' => __('Barva odkazů na stránce', 'cms_ve'),
                    'id' => 'link_color',
                    'type' => 'color',
                ),
            )
        ),
        array(
            'id'=>'element_text_setting',
            'type'=>'toggle_group',
            'title'=>__('Formátování textu v textovém elementu','cms_ve'),
            'setting'=>array(     
                array(
                    'name' => __('Nadpis 1 (H1)', 'cms_ve'),
                    'id' => 'h1_font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Nadpis 2 (H2)', 'cms_ve'),
                    'id' => 'h2_font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Nadpis 3 (H3)', 'cms_ve'),
                    'id' => 'h3_font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Nadpis 4 (H4)', 'cms_ve'),
                    'id' => 'h4_font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Nadpis 5 (H5)', 'cms_ve'),
                    'id' => 'h5_font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Nadpis 6 (H6)', 'cms_ve'),
                    'id' => 'h6_font',
                    'type' => 'font',
                    'content' => array(
                        'font-size' => '',
                        'color' => '',
                    ),
                ),
                array(
                    'name' => __('Odrážky v textu', 'cms_ve'),
                    'type' => 'title',
                ),
                array(
                    'id' => 'li',
                    'name' => __('Styl odrážek', 'cms_ve'),
                    'type' => 'imageselect',
                    'content' => '',
                    'options' => $vePage->list_icons,
                ),           
            )  
        ),              
        array(
            'id'=>'page_advanced_setting',
            'type'=>'toggle_group',
            'title'=>__('Pokročilé nastavení stránky','cms_ve'),
            'setting'=>array(
                array(
                    'name' => __('Šířka stránky', 'cms_ve'),
                    'id' => 'page_width',
                    'type' => 'size',
                    'content' => array(
                        'size' => '',
                        'unit' => 'px'
                    )
                ),
            )
        )
    ),
), "ve_page_appearance");

$page_header_setting=$cms->container['header_setting'];
if($cms->is_module_active('shop')) {
    $page_header_setting[1]['setting'][]=array(
                    'name' => __('Skrýt košík v hlavičce', 'cms_ve'),
                    'id' => 'hide_cart',
                    'type' => 'checkbox',
                    'label' => __('Skrýt ikonu košíku v hlavičce této stránky', 'cms_ve'),
    );

}

$cms->add_set(array(
    'id' => 've_header',
    'title' => __('Hlavička stránky', 'cms_ve'),
    'fields' => array(
        array(
            'name' => __('Použít', 'cms_ve'),
            'id' => 'show',
            'type' => 'radio',
            'show' => 'headerset',
            'options' => array(
                'global' => __('Globální hlavičku', 'cms_ve'),
                'page' => __('Vlastní hlavičku', 'cms_ve'),
                'noheader' => __('Bez hlavičky', 'cms_ve'),
            ),
            'content' => 'global',
        ),
        array(
            'id' => 'header_group',
            'type' => 'group',
            'setting' => $page_header_setting,
            'show_group' => 'headerset',
            'show_val' => 'page',
        ),

    )
), "ve_page_appearance");

$cms->add_set(array(
    'id' => 've_footer',
    'title' => __('Patička stránky', 'cms_ve'),
    'fields' => array(
        array(
            'name' => __('Použít', 'cms_ve'),
            'id' => 'show',
            'type' => 'radio',
            'show' => 'footerset',
            'options' => array(
                'global' => __('Globální patičku', 'cms_ve'),
                'page' => __('Vlastní patičku', 'cms_ve'),
                'nofooter' => __('Bez patičky', 'cms_ve'),
            ),
            'content' => 'global',
        ),
        array(
            'id' => 'footer_group',
            'type' => 'group',
            'setting' => $cms->container['footer_setting'],
            'show_group' => 'footerset',
            'show_val' => 'page',
        )
    )
), "ve_page_appearance");

// Nastavení
//***********************************************************************************

$cms->add_subpage(array(
    'parent_slug' => 'web_option',
    'page_title' => __('Vzhled', 'cms_ve'),
    'menu_title' => __('Vzhled', 'cms_ve'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 've_option',
));
$cms->add_page_group(array(
    'id' => 've_appearance',
    'page' => 've_option',
    'name' => __('Pozadí a formátování', 'cms_ve'),
));
$cms->add_page_group(array(
    'id' => 've_header',
    'page' => 've_option',
    'name' => __('Hlavička webu', 'cms_ve'),
));
$cms->add_page_group(array(
    'id' => 've_footer',
    'page' => 've_option',
    'name' => __('Patička webu', 'cms_ve'),
));

$cms->add_subpage(array(
    'parent_slug' => 'web_option',
    'page_title' => __('Propojení aplikací', 'cms_ve'),
    'menu_title' => __('Propojení aplikací', 'cms_ve'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 've_connect',
));
$cms->add_page_group(array(
    'id' => 've_connect_se',
    'page' => 've_connect',
    'name' => __('E-mail marketing', 'cms_ve'),
));
$cms->add_page_group(array(
    'id' => 've_connect_fapi',
    'page' => 've_connect',
    'name' => __('Prodej a fakturace', 'cms_ve'),
));
$cms->add_page_group(array(
    'id' => 've_google_api',
    'page' => 've_connect',
    'name' => __('Google mapy', 'cms_ve'),
));
$cms->add_subpage(array(
    'parent_slug' => 'web_option',
    'page_title' => __('Pop-upy webu', 'cms_ve'),
    'menu_title' => __('Pop-upy webu', 'cms_ve'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 've_popups',
));

$cms->add_page_group(array(
    'id' => 've_popups',
    'page' => 've_popups',
    'name' => __('Pop-upy webu', 'cms_ve'),
));

$cms->add_page_setting('ve_popups', $cms->container['popup_setting']);

$cms->add_page_setting('ve_connect_se', array(
    array(
        'type' => 'tabs',
        'id' => 'emailing',
        'tabs' => array(
            'se' => array(
                'name' => __('SmartEmailing', 'cms_ve'),
                'setting' => array(
                    array(
                        'name' => __('Přihlašovací jméno', 'cms_ve'),
                        'id' => 'login',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('API token', 'cms_ve'),
                        'id' => 'password',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('Stav spojení', 'cms_ve'),
                        'id' => 'connection',
                        'type' => 'connection_control',
                    ),
                )
            ),
            'getresponse' => array(
                'name' => __('GetResponse', 'cms_ve'),
                'setting' => array(
                    array(
                        'name' => __('Přihlašovací jméno', 'cms_ve'),
                        'id' => 'getresponse_login',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('API klíč', 'cms_ve'),
                        'id' => 'getresponse_password',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('Stav spojení', 'cms_ve'),
                        'id' => 'getresponse_connection',
                        'type' => 'connection_control',
                    ),
                )
            ),
            'mailchimp' => array(
                'name' => __('MailChimp', 'cms_ve'),
                'setting' => array(
                    array(
                        'name' => __('Přihlašovací jméno', 'cms_ve'),
                        'id' => 'mailchimp_login',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('API klíč', 'cms_ve'),
                        'id' => 'mailchimp_password',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('Stav spojení', 'cms_ve'),
                        'id' => 'mailchimp_connection',
                        'type' => 'connection_control',
                    ),
                )
            ),
            'aweber' => array(
                'name' => __('AWeber', 'cms_ve'),
                'setting' => array(
                    array(
                        'name' => __('Získat autorizační kód', 'cms_ve'),
                        'id' => 'aweber_login',
                        'content' => 'https://auth.aweber.com/1.0/oauth/authorize_app/d4198b5e',
                        'type' => 'authorize_api',
                        'desc' => __('Pro propojení je potřeba povolit připojení MioWebu k Vašemu účtu. Po kliknutí na odkaz se přihlaste k Vašemu "AWeber" účtu. '
                            . 'Obdržíte unikátní kód, který zkopírujte do pole "Autorizační kód".', 'cms')
                    ),
                    array(
                        'name' => __('Autorizační kód', 'cms_ve'),
                        'id' => 'aweber_password',
                        'type' => 'textarea',
                    ),
                    array(
                        'name' => __('Stav spojení', 'cms_ve'),
                        'id' => 'aweber_connection',
                        'type' => 'connection_control',
                    ),
                )
            )
        )
    ),


));
$cms->add_page_setting('ve_connect_fapi', array(
    array(
        'type' => 'tabs',
        'id' => 'selling',
        'tabs' => array(
            'fapi' => array(
                'name' => __('FAPI', 'cms_ve'),
                'setting' => array(
                    array(
                        'name' => __('Přihlašovací jméno', 'cms_ve'),
                        'id' => 'login',
                        'type' => 'text',
                    ),
                    array(
                        'name' => __('API klíč', 'cms_ve'),
                        'id' => 'password',
                        'type' => 'text',
                        'desc' => '<a target="_blank" href="https://web.fapi.cz/account-settings/api-tokens?projectId=all">'. __('Získat API klíč z FAPI', 'cms_ve').'</a>',
                    ),
                    array(
                        'name' => __('Stav spojení', 'cms_ve'),
                        'id' => 'connection',
                        'type' => 'connection_control',
                    ),
                )
            ),
        )
    )
));
$cms->add_page_setting('ve_google_api', array(
        array(
              'name' => '',
              'id' => 'gmap_infobox',
              'type' => 'info',
              'content' => '<h3>'.__('Napojení na google maps API', 'cms_ve').'</h3>
              <p>1. <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank">'.__('Vygenerujte si svůj API klíč (zdarma).', 'cms_ve').'</a></p>
              <p>2. '.__('Vložte svůj API klíč níže a dejte uložit.', 'cms_ve').'</p>',
        ),
        array(
              'name' => __('API klíč', 'cms_ve'),
              'id' => 'api_key',
              'type' => 'text',
        ),
));

$cms->add_page_setting('ve_footer', $cms->container['footer_setting']);

$cms->add_page_setting('ve_header', $cms->container['header_setting']);

$cms->add_page_setting('ve_appearance', $cms->container['appearance_setting']);


$vePage->row_setting['slide_set'] = array(
  array(
      'id' => 'background_color',
      'title' => __('Barva pozadí', 'cms_ve'),
      'type' => 'background',
      'content' => array(
          'transparency' => '100'
      )
  ),
  array(
      'id' => 'background_image',
      'title' => __('Obrázek na pozadí', 'cms_ve'),
      'type' => 'bgimage',
      'hide' => array('cover'),
      'content' => array(
          'pattern' => 0,
          'cover' => 1,
      )
  ),       
  array(
      'id' => 'font',
      'title' => __('Písmo', 'cms_ve'),
      'type' => 'font',
      'content' => array(
          'color' => '',
      ),
  ),
  array(
      'title' => __('Barva odkazů', 'cms_ve'),
      'id' => 'link_color',
      'type' => 'color',
  ),       
                        
);
$vePage->row_setting['slider'] = array(
          array(
              'id'=>'slides',
              'type'=>'multielement',
              'texts'=>array(
                  'add'=>__('Přidat slide','cms_ve'),
              ),
              'setting'=>array(                             
                  array(
                      'id'=>'slider_content',
                      'title'=>__('Obsah', 'cms_ve'),
                      'type'=>'weditor',
                      'setting'=>array(
                          'post_type'=>'mw_slider',
                          'templates'=>'mw_sliders',
                          'texts'=>array(
                              'empty'=>__( ' - Bez obsahu - ', 'cms_ve' ),
                              'edit'=>__( 'Upravit vybraný obsah', 'cms_ve' ),
                              'duplicate'=>__( 'Duplikovat vybraný obsah', 'cms_ve' ),
                              'create'=>__( 'Vytvořit nový obsah', 'cms_ve' ),
                              'delete'=>__( 'Smazat vybraný obsah', 'cms_ve' ),
                          ),
                      )
                  ),
              ),
              
          ),                           
);
$vePage->row_setting['slider_set'] = array(
    array(
        'id' => 'slider_height',
        'title' => __('Výška slideru','cms_ve'),
        'type' => 'size',
        'unit' => 'px',
        'content'=> '',
        'desc'=> __('Pokud nezadáte žádnou hodnotu, výška hlavičky se automaticky přizpůsobí výšce stránky','cms_ve'),
    ),
    array(
        'id'=>'animation',
        'title'=>__('Typ animace','cms_ve'),
        'type'=>'select',
        'content'=> 'fade',
        'options' => array(
            array('name' => __('Prolínání','cms_ve'), 'value' => 'fade'),
            array('name' => __('Zprava doleva','cms_ve'), 'value' => 'slide'),
        ),
    ),                      
    array(
        'id' => 'a_delay',
        'title' => __('Zpoždění slidů','cms_ve'),
        'type' => 'size',
        'unit' => 'ms',
        'content'=> '3500',
    ),
    array(
        'id' => 'speed',
        'title' => __('Délka animace','cms_ve'),
        'type' => 'size',
        'unit' => 'ms',
        'content'=> '1000',
    ),
    array(
        'id' => 'off_autoplay',
        'title' => __('Autoplay','cms_ve'),
        'type' => 'checkbox',
        'label' => __('Vypnout autoplay','cms_ve'),
    ),
    array(
        'id'=>'color_scheme',
        'title'=>__('Barva ovládacích prvků','cms_ve'),
        'type'=>'select',
        'content'=> '',
        'options' => array(
            array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
            array('name' => __('Tmavé','cms_ve'), 'value' => ''),
        ),
    ),
);

$vePage->row_setting['basic'] = array(
    array(
        'id' => 'background_color',
        'title' => __('Barva pozadí', 'cms_ve'),
        'type' => 'background',
        'content' => array(
            'transparency' => '100'
        )
    ),
    array(
        'type' => 'tabs',
        'id' => 'background_setting',
        'style' => 'border',
        'tabs' => array(
            'image' => array(
                'name' => __('Obrázek na pozadí', 'cms_ve'),
                'setting' => array(
                    array(
                        'id' => 'background_image',
                        'title' => __('Obrázek na pozadí', 'cms_ve'),
                        'type' => 'bgimage',
                        'content' => array(
                            'pattern' => 0,
                            'fixed' => ''
                        )
                    ),
                )
            ),
            'slider'=>array(
                'name' => __('Slider na pozadí','cms_ve'),
                'setting'=>array(
                    array(
                        'id' => 'background_delay',
                        'title' => __('Zpoždění slidů','cms_ve'),
                        'type' => 'size',
                        'unit' => 'ms',
                        'content'=> '3000',
                    ),
                    array(
                        'id' => 'background_speed',
                        'title' => __('Délka animace','cms_ve'),
                        'type' => 'size',
                        'unit' => 'ms',
                        'content'=> '1500',
                    ),
                    array(
                        'id'=>'background_slides',
                        'title'=>__('Slidy','cms_ve'),
                        'type'=>'multielement',
                        'texts'=>array(
                            'add'=>__('Přidat slide','cms_ve'),
                        ),
                        'setting'=>array(                           
                            array(
                                'id'=>'image',
                                'title'=>'',
                                'type'=>'image',                                                  
                            ),
                        ),
                    ),
                )
            ),
            'video'=>array(
                'name' => __('Video na pozadí','cms_ve'),
                'setting'=>array(
                    array(
                        'id' => 'video_info',
                        'name' => '',
                        'type' => 'info', 
                        'content' => __('Doporučuje se vložit video v co nejvíce formátech níže pro lepší kompatibilitu napříč všemi prohlížeči. Video v jednom formátu nemusí fungovat všude.','cms_ve'), 
                    ),
                    array(
                        'name' => __('Video ve formátu .mp4','cms_ve'),
                        'id' => 'background_video_mp4',
                        'type' => 'upload_file', 
                    ),
                    array(
                        'name' => __('Video ve formátu .webm','cms_ve'),
                        'id' => 'background_video_webm',
                        'type' => 'upload_file', 
                    ),
                    array(
                        'name' => __('Video ve formátu .ogg','cms_ve'),
                        'id' => 'background_video_ogg',
                        'type' => 'upload_file', 
                    ),
                    array(
                        'id'=>'video_setting',
                        'title'=>__('Nastavení videa na pozadí','cms_ve'),
                        'type' => 'multiple_checkbox',
                        'options' => array(
                            array('name' => __('Zapnout zvuk videa','cms_ve'), 'value' => 'sound'),
                            array('name' => __('Zobrazovat video na mobilních zařízeních','cms_ve'), 'value' => 'show_mobile'),
                        ),
                    ),
                )
            ),
        )
    ),
    array(
        'id' => 'font',
        'title' => __('Písmo', 'cms_ve'),
        'type' => 'font',
        'content' => array(
            'font-size' => '',
            'font-family' => '',
            'weight' => '',
            'color' => '',
        ),
    ),
    array(
        'title' => __('Barva odkazů', 'cms_ve'),
        'id' => 'link_color',
        'type' => 'color',
    ),

);
$vePage->row_setting['advance'] = array(
    array(
        'id' => 'type',
        'title' => __('Typ řádku', 'cms_ve'),
        'type' => 'radio',
        'content' => 'basic',
        'options' => array(
            'basic' => __('Pozadí přes celou šířku, obsah na středu', 'cms_ve'),
            'fixed' => __('Pozadí i obsah na středu', 'cms_ve'),
            'full' => __('Pozadí i obsah přes celou šířku', 'cms_ve'),
        ),
    ),
    array(
        'id' => 'padding',
        'title' => __('Odsazení obsahu (padding)', 'cms_ve'),
        'type' => 'row_set',
        'setting' => array(
            array(
                'id' => 'padding_top',
                'title' => __('Horní odsazení', 'cms_ve'),
                'type' => 'size',
                'unit' => 'px',
                'content' => '50',
            ),
            array(
                'id' => 'padding_bottom',
                'title' => __('Spodní odsazení', 'cms_ve'),
                'type' => 'size',
                'unit' => 'px',
                'content' => '50',
            ),
            array(
                'id' => 'padding_left',
                'title' => __('Levé odsazení', 'cms_ve'),
                'type' => 'size',
                'content' => array('size' => '', 'unit' => 'px'),
            ),
            array(
                'id' => 'padding_right',
                'title' => __('Pravé odsazení', 'cms_ve'),
                'type' => 'size',
                'content' => array('size' => '', 'unit' => 'px'),
            ),
        )
    ),
    array(
        'id' => 'margin',
        'title' => __('Odsazení řádku (margin)', 'cms_ve'),
        'type' => 'row_set',
        'setting' => array(
            array(
                'id' => 'margin_t',
                'title' => __('Horní odsazení', 'cms_ve'),
                'type' => 'size',
                'unit' => 'px',
            ),
            array(
                'id' => 'margin_b',
                'title' => __('Spodní odsazení', 'cms_ve'),
                'type' => 'size',
                'unit' => 'px',
            ),
        )
    ),
    array(
        'title' => __('Horní ohraničení', 'cms_ve'),
        'id' => 'border-top',
        'type' => 'border',
        'content' => array(
            'size' => '',
            'style' => 'solid',
            'color' => ''
        )
    ),
    array(
        'title' => __('Spodní ohraničení', 'cms_ve'),
        'id' => 'border-bottom',
        'type' => 'border',
        'content' => array(
            'size' => '',
            'style' => 'solid',
            'color' => ''
        )
    ),
    array(
        'id' => 'height_setting',
        'title' => 'Řádek přes celou obrazovku',
        'type' => 'row_height',
    ),  
    array(
        'id' => 'min-height',
        'title' => __('Minimální výška řádku', 'cms_ve'),
        'type' => 'size',
        'unit' => 'px',
        'content' => '',
    ),
    array(
        'id' => 'css_class',
        'title' => __('Vlastní css třída řádku', 'cms_ve'),
        'type' => 'text',
        'content' => ''
    ),  
    array(
        'id' => 'row_anchor',
        'title' => __('Kotva řádku', 'cms_ve'),
        'type' => 'text',
        'content' => ''
    ),
);
$vePage->row_setting['show'] = array(
    array(
        'id' => 'mobile_visibility',
        'title' => __('Zobrazení na mobilních zařízeních', 'cms_ve'),
        'type' => 'checkbox',
        'label' => __('Skrýt na mobilních zařízeních', 'cms_ve'),
    ),
    array(
        'id' => 'delay',
        'title' => __('Zobrazit se zpožděním (x sekund od načtení stránky)', 'cms_ve'),
        'type' => 'text',
        'content' => '',
    ),
);

$cms->define_set(array(
    'id' => 'event_set',
    'title' => __('Nastavení události', 'cms_ve'),
    'context' => 'normal',
    'priority' => 'high',
    'include' => array('mw_event'),
));
$cms->add_set(array(
    'id' => 've_event',
    'title' => __('Nastavení', 'cms_ve'),
    'include' => array('mw_event'),
    'fields' => array(
        array(
          'id' => 'mw_event_date_start',
          'type' => 'date',
          'name' => __('Datum konání (začátek akce)', 'cms_ve'),
          'save' => 'post_meta',
          'savehook' => function($postId, $field, $fieldValue, &$fieldSaved) {
              update_post_meta($postId, 'mw_event_date_start', strtotime($fieldValue)); 
              $fieldSaved = true;
          },
          'convert' => 1
        ),
        array(
          'id' => 'date_end',
          'type' => 'date',
          'name' => __('Konec akce', 'cms_ve'),
        ),
        array(
          'name' => __('Místo konání', 'cms_ve'),
          'id' => 'where',
          'type' => 'text',
        ),
        array(
          'name' => __('Popisek','cms_ve'),
          'id' => 'post_excerpt',
          'type' => 'textarea',
          'save' => 'post',
        ),
        array(
          'name' => __('Detail akce','cms_ve'),
          'id' => 'event_page',
          'type' => 'page_link',
        ),

    )
), "event_set");
