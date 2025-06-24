<?php  
global $cms;
global $member_module;
define('MEMBER_VERSION','0.9.1');
$cms->add_version('member',MEMBER_VERSION); 

define('MEMBER_DIR',get_template_directory_uri().'/modules/member/'); 

// language
$cms->load_theme_lang('cms_member', get_template_directory() . '/modules/member/languages');      

require_once(__DIR__ .'/functions.php');
require_once(__DIR__ .'/elements.php');
require_once(__DIR__ .'/elements_print.php');

require_once(__DIR__ .'/member_class.php'); 

$member_module = New MemberSection(); 

add_theme_support( 'post-thumbnails' );
add_image_size( 'member_page',350, 220, true );

// Templates
//***********************************************************************************
$cms->add_templates_topos(8,'member',array(
      'name'=>__('Členské', 'cms_member'),
      'path'=>'/modules/member/templates/member/',
      'list'=>array(
          'dashboard'=>array(
              'name'=>__('Šablony nástěnky', 'cms_member'),
              'list'=>array('dashboard1')
          ),
          'lesson'=>array(
              'name'=>__('Šablony stránky s výpisem lekcí', 'cms_member'),
              'list'=>array('list1')
          ),
          'list'=>array(
              'name'=>__('Šablony stránky s obsahem lekce', 'cms_member'),
              'list'=>array('lesson1')
          ),
          'login'=>array(
              'name'=>__('Šablony přihlašovacích stránek', 'cms_member'),
              'list'=>array('login1')
          ),
      )
));

// Top panel menu
//***********************************************************************************
$vePage->add_top_panel_menu(13,array('id'=>'member','title'=>__('Členská sekce', 'cms_member'),'submenu'=>$member_module->create_member_menu(),'url'=>((isset($member_module->first_member['dashboard']) && $member_module->first_member['dashboard'])? get_permalink($member_module->first_member['dashboard']) : "#")));


// Nastavení stránek
//***********************************************************************************

$cms->add_set(array(
    'id' => 'page_member',
    'title' => __('Členská stránka','cms_member'),
    'include'=>array('page'),
    'fields' => array(    
        array(
            'name' => __('Členská stránka','cms_member'),
            'id' => 'member_page',
            'type' => 'checkbox',  
            'desc' => __('Po zaškrtnutí bude tato stránka součástí vybrané členské sekce a budou k ní mít přístup jen její členové.','cms_member'),
            'show'=>'memberpage',
            'label' => __('Zpřístupnit stránku pouze registrovaným uživatelům.','cms_member')
        ),      
        array(
            'name' => __('Zařadit do členské sekce','cms_member'),
            'id' => 'member_section',
            'type' => 'selectmemberlevel',  
            'tooltip' => __('Tato stránka bude zařazena do vybrané členské sekce a bude přistupná pouze těm registrovaným uživatelům, kteří mají do této členské sekce přístup.','cms_member'),
            'show_group' => 'memberpage',
            'desc' => __('Pokud členská sekce obsahuje členské úrovně a některou z nich zaškrtnete, bude stránka přístupná pouze pro uživatele s danou členskou úrovní. Pokud nezaškrtnete nic, bude stránka přístupná pro všechny členy vybrané členské sekce.','cms_member'),
        ),
        array(
            'type' => 'tabs',
            'id' => 'member_page_setting',
            'show_group' => 'memberpage',
            'tabs'=>array(
                'look'=>array(
                    'name' => __('Zobrazení','cms_ve'),
                    'setting'=>array(
                        array(
                            'name' => __('Popisek stránky','cms_member'),
                            'id' => 'description',
                            'type' => 'textarea',  
                            'desc' => __('Popisek stránky se zobrazuje ve výpisu seznamu lekcí (podstránek). Například v elementu „Navigace kampaně“.','cms_member'),
                        ),
                        array(
                            'name' => __('Náhledový obrázek','cms_member'),
                            'id' => 'thumbnail',
                            'type' => 'upload',  
                            'desc' => __('Zadaný obrázek se bude zobrazovat ve výpisu lekcí (podstránek). Například v elementu „Navigace kampaně“.','cms_member'),
                        ),
                    )
                ),
                'evergreen'=>array(
                    'name' => __('Evergreen','cms_ve'),
                    'setting'=>array(
                        array(
                            'name' => __('Zobrazit po x dnech od registrace','cms_member'),
                            'id' => 'evergreen',
                            'type' => 'text',  
                            'desc' => __('Zadejte, po jakém počtu dní od registrace se má stránka členům zpřístupnit. Pokud pole nevyplníte, bude stránka přístupná od okamžiku registrace.','cms_member'),
                        ),   
                        array(
                            'name' => __('Zobrazit dne','cms_member'),
                            'id' => 'evergreen_datetime',
                            'type' => 'datetime',  
                            'desc' => __('Zadejte datum a čas chvíle, od které má být stránka viditelná.','cms_member'),
                        ),     
                    )
                ),
                'checklist'=>array(
                    'name' => __('Seznam úkolů','cms_member'),
                    'setting'=>array(
                          array(
                              'id'=>'checklist',
                              'type'=>'multielement',
                              'texts'=>array(
                                  'add'=>__('Přidat úkol','cms_member'),
                              ),
                              'setting'=>array(                             
                                  array(
                                      'id'=>'text',
                                      'title'=>__('Úkol','cms_member'),
                                      'type'=>'textarea',
                                  ),
                             ),
                        ),    
                    )
                ),
            )
        )
    )
),"page_set");

// Nastavení
//***********************************************************************************


$cms->add_page(array(
    'page_title' => __('Členská sekce','cms_member'),
    'menu_title' => __('Členská sekce','cms_member'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'member_option', 
    'icon_url' => '',
    'position' => 206
));

$cms->add_subpage(array(
    'parent_slug' => 'member_option',
    'page_title' => __('Nastavení','cms_member'),
    'menu_title' => __('Nastavení','cms_member'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'member_option',
));
$cms->add_page_group(array(
    'id' => 'member_basic',
    'page' => 'member_option',
    'name' => __('Základní','cms_member'),
));
$cms->add_page_group(array(
    'id' => 'member_popups',
    'page' => 'member_option',
    'name' => __('Pop-upy','cms_member'),
));  

$cms->add_page_group(array(
    'id' => 'fapi_notification',
    'page' => 'member_option',
    'name' => __('Notifikace FAPI','cms_member'),
));
$cms->add_page_setting('member_basic',array( 
    array(
        'id' => 'name',
        'name' => __('Název členské sekce','cms_member'),
        'type' => 'text',
    ), 
    array(                  
        'type' => 'tabs',
        'id' => 'logo_setting',
        'tabs'=>array(
            'm_basic'=>array(
                'name' => __('Základní nastavení','cms_member'),
                'setting'=>array(   
                    array(
                        'id'=>'member_pages',
                        'type'=>'toggle_group',
                        'open'=>true,
                        'title'=>__('Stránky členské sekce','cms_member'),
                        'setting'=>array(                                    

                            array(
                                'id' => 'dashboard',
                                'name' => __('Nástěnka (hlavní stránka)','cms_member'),
                                'type' => 'selectpage',
                            ),
                            array(
                                'id' => 'login',
                                'name' => __('Přihlašovací stránka','cms_member'),
                                'type' => 'selectpage',
                            ),
                        )
                    ),
                    array(
                        'id'=>'member_evergreen',
                        'type'=>'toggle_group',
                        'title'=>__('Evergreen','cms_member'),
                        'setting'=>array(  
                            array(
                                'id'=>'evergreen_show',
                                'title'=>'',
                                'label'=>__('Skrýt nezveřejněné stránky','cms_member'),
                                'type'=>'checkbox',
                                'desc'=> __('Pokud tuto možnost nezaškrtnete, všechny stránky budou viditelné i pro ty, kteří k nim zatím nemají přístup. Budou však zašedlé a nepůjdou rozkliknout.','cms_member')
                            ), 
                        )
                    ),
                    array(
                        'id'=>'member_evergreen',
                        'type'=>'toggle_group',
                        'title'=>__('Časově omezené členství','cms_member'),
                        'setting'=>array(  
                            array(
                                'id' => 'expire_page',
                                'name' => __('Po vypršení členství zobrazit stránku','cms_member'),
                                'type' => 'selectpage',
                            ),
                          )
                    ),                 
                )
            ),
            'm_levels'=>array(
                'name' => __('Členské úrovně','cms_member'),
                'setting'=>array(
                    array(
                        'id' => 'levels',
                        'name' => '',
                        'type' => 'member_levels',
                    ),  
                )
            ),
            'm_emails'=>array(
                'name' => __('E-maily','cms_member'),
                'setting'=>array(
                    array(
                        'id'=>'register_email',
                        'type'=>'toggle_group',
                        'title'=>__('E-mail po nové registraci nebo po přidání do členské sekce','cms_member'),
                        'setting'=>array(             
                            array(
                                'id' => 'email_subject',
                                'name' => __('Předmět e-mailu','cms_member'),
                                'type' => 'text',
                                'content' => __('Přístup do členské sekce','cms_member'),
                            ),
                            array(
                                'id' => 'email_text',
                                'name' => __('Obsah e-mailu','cms_member'),
                                'type' => 'textarea',
                                'content' => __('Dobrý den,\n\nbyly Vám vygenerovány přístupy: \n\n%%login%%','cms_member'),
                                'desc'=>__('Proměnná %%login%% bude nahrazena vygenerovanými přihlašovacími údaji a URL adresou s přihlašovacím formulářem do odpovídající členské sekce. Text e-mailu musí tuto proměnnou obsahovat.','cms_member'),
                            ),
                        )
                    ),
                    array(
                        'id'=>'register_level_email',
                        'type'=>'toggle_group',
                        'title'=>__('E-mail po přidání do členské úrovně','cms_member'),
                        'setting'=>array(               
                            array(
                                'id' => 'level_email_subject',
                                'name' => __('Předmět e-mailu','cms_member'),
                                'type' => 'text',
                                'content' => __('Přidány přístupy do členské úrovně','cms_member'),
                            ),
                            array(
                                'id' => 'level_email_text',
                                'name' => __('Obsah e-mailu','cms_member'),
                                'type' => 'textarea',
                                'content' => __('Dobrý den,
        
byl Vám povolen přístup do nové členské úrovně v: 
        
%%login%%','cms_member'),
                                'desc'=>__('Proměnná %%login%% bude nahrazena vygenerovanými přihlašovacími údaji a URL adresou s přihlašovacím formulářem do odpovídající členské sekce. Text e-mailu musí tuto proměnnou obsahovat.','cms_member'),
                            ), 
                        )
                    ),
                    array(
                        'id'=>'expiration_email',
                        'type'=>'toggle_group',                      
                        'title'=>__('E-mail po prodloužení členství','cms_member'),
                        'setting'=>array(          
                            array(
                                'id' => 'expiration_email_subject',
                                'name' => __('Předmět e-mailu','cms_member'),
                                'type' => 'text',
                                'content' => __('Prodloužení členství','cms_member'),
                            ),
                            array(
                                'id' => 'expiration_email_text',
                                'name' => __('Obsah e-mailu','cms_member'),
                                'type' => 'textarea',
                                'content' => __('Dobrý den,
        
bylo Vám prodlouženo členství: 
        
%%login%%','cms_member'),
                                'desc'=>__('Proměnná %%login%% bude nahrazena vygenerovanými přihlašovacími údaji a URL adresou s přihlašovacím formulářem do odpovídající členské sekce. Text e-mailu musí tuto proměnnou obsahovat.','cms_member'),
                            ), 
                        )
                    ) 
                )
            ),
        )
    ),
    /*
    array(
        'name' => '',
        'id' => 'members',
        'type' => 'members',
        'print'=> 'full'
    ),*/
)); 
$cms->add_subpage(array(
    'parent_slug' => 'member_option',
    'page_title' => __('Vzhled členské sekce','cms_member'),
    'menu_title' => __('Vzhled členské sekce','cms_member'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'appearancemember_option',
));
$cms->add_page_group(array(
    'id' => 'member_appearance',
    'page' => 'appearancemember_option',
    'name' => __('Pozadí a formátování','cms_member'),
));
$cms->add_page_group(array(
    'id' => 'member_header',
    'page' => 'appearancemember_option',
    'name' => __('Hlavička','cms_member'),
));
$cms->add_page_group(array(
    'id' => 'member_footer',
    'page' => 'appearancemember_option',
    'name' => __('Patička','cms_member'),
)); 



$cms->add_page_setting('member_popups',$cms->container['popup_setting']); 



$cms->add_page_setting('fapi_notification',array(
    array(
        'name' => __('URL pro notifikace FAPI k automatickému vytváření členských účtů po zaplacení faktury','cms_member'),
        'type' => 'title',
    ), 
    array(
        'name' => '',
        'id' => 'fapi_notification',
        'type' => 'fapi_notification',
    ),  
    array(
        'name' => __('Log proběhlých notifikací','cms_member'),
        'type' => 'title',
    ), 
    array(
        'name' => __('Upozornění','cms_member'),
        'id' => 'notification_onemail',
        'type' => 'checkbox',
        'label' => __('Posílat upozornění na neúspěšné notifikace na e-mail','cms_member'),
    ),     
    array(
        'name' => __('E-mail pro upozornění','cms_member'),
        'id' => 'notifi_email',
        'type' => 'text',  
        'desc' => __('Zde zadejte e-mailovou adresu, na kterou chcete notifikace zasílat.','cms_member'),
    ),
    array(
        'name' => __('Tabulka notifikací','cms_member'),
        'id' => 'fapi_notification_log',
        'type' => 'fapi_notification_log',
    ),
));  

$cms->add_page_setting('member_appearance',$cms->container['appearance_setting']); 
$cms->add_page_setting('member_footer',$cms->container['footer_setting']);
 
$cms->add_page_setting('member_header',
    $cms->container['header_setting']
); 

$cms->add_page_group(array(
    'id' => 'member_login',  
    'page' => 've_option',
    'name' => __('Wordpressový login','cms_member'),
)); 

$cms->add_page_setting('member_login',array( 
    array(
        'id'=>'wplogin_logo',
        'type'=>'toggle_group',  
        'open'=>true,                    
        'title'=>__('Logo','cms_member'),
        'setting'=>array(  
            array(
                'name' => __('Logo','cms_member'),
                'id' => 'logo',
                'type' => 'upload',
                'content'=>get_bloginfo('template_url').'/modules/member/images/login-logo.png',
            ),   
            array(
                'name' => __('Šířka loga','cms_member'),
                'id' => 'width',
                'type' => 'size',
                'unit'=>'px',
                'content'=>array(
                    'size'=>'159',
                    'unit'=>'px',
                )
            ), 
            array(
                'name' => __('Výška loga','cms_member'),
                'id' => 'height',
                'type' => 'size',
                'unit'=>'px',
                'content'=>array(
                    'size'=>'36',
                    'unit'=>'px',
                )
            ),  
        )
    ),
    array( 
        'id'=>'wplogin_format',
        'type'=>'toggle_group',                      
        'title'=>__('Pozadí a formátování přihlašovací stránky','cms_member'),
        'setting'=>array(  
            array(
                'name' => __('Barva pozadí','cms_member'),
                'id' => 'background_color',
                'type' => 'color',
                'content'=>'#158ebf',
            ),
            array(
                'name' => __('Obrázek na pozadí','cms_member'),
                'id' => 'background_image',
                'type' => 'bgimage',
                'content'=>array(
                      'pattern'=>0
                )
            ),
            array(
                'name' => __('Barva textu','cms_member'),
                'id' => 'font-color',
                'type' => 'color',
                'content'=>'#bdd5e4',
            ),
        )
    )
)); 
