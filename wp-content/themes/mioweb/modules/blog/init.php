<?php

require_once(TEMPLATEPATH . '/modules/blog/widgets.php');

add_action( 'widgets_init', 'myplugin_register_widgets' );

global $cms;
global $blog_module;
global $vePage;

define('BLOG_VERSION','1.0');
$cms->add_version('blog',BLOG_VERSION); 

define('BLOG_DIR',get_template_directory_uri().'/modules/blog/');

// language
$cms->load_theme_lang('cms_blog', get_template_directory() . '/modules/blog/languages'); 
                                                                                             
require_once(__DIR__ .'/functions.php');
require_once(__DIR__ .'/elements.php');
require_once(__DIR__ .'/elements_print.php');
require_once(__DIR__ .'/blog_class.php');

add_theme_support( 'post-thumbnails' );
add_image_size( 'blog_medium',260, 260, true );
//add_image_size( 'blog_small', 85, 85, true );  
//add_image_size( 'blog_large', 680, 1000 ); 
//add_image_size( 'blog_element',300, 200, true );

$blog_module = New CmsBlog();

$blog_module->add_template('style3',array(
    'folder'=>'blog2',
    'style'=>'style',
    'thumb'=>BLOG_DIR.'images/image_select/blog3.jpg',
    'path'=>get_bloginfo('template_url').'/modules/blog/templates/',
    'directory'=>get_template_directory().'/modules/blog/templates/'
));
$blog_module->add_template('style4',array(
    'folder'=>'blog2',
    'style'=>'style',
    'thumb'=>BLOG_DIR.'images/image_select/blog4.jpg',
    'path'=>get_bloginfo('template_url').'/modules/blog/templates/',
    'directory'=>get_template_directory().'/modules/blog/templates/'
));
$blog_module->add_template('style1',array(
    'folder'=>'blog1',
    'style'=>'style1',
    'thumb'=>BLOG_DIR.'images/image_select/blog1.jpg',
    'path'=>get_bloginfo('template_url').'/modules/blog/templates/',
    'directory'=>get_template_directory().'/modules/blog/templates/'
));
$blog_module->add_template('style2',array(
    'folder'=>'blog1',
    'style'=>'style2',
    'thumb'=>BLOG_DIR.'images/image_select/blog2.jpg',
    'path'=>get_bloginfo('template_url').'/modules/blog/templates/',
    'directory'=>get_template_directory().'/modules/blog/templates/'
));

// Top panel menu
//***********************************************************************************
if($blog_module->edit_mode) {   
    if( get_option( 'show_on_front' ) == 'page' ) $blogurl=get_permalink( get_option('page_for_posts' ) ); else $blogurl=home_url();
    $vePage->add_top_panel_menu(10,array('id'=>'blog','title'=>'Blog', 'url'=>$blogurl, 'submenu'=>$blog_module->create_blog_menu()));
}
// Sidebar
//***********************************************************************************

$cms->add_sidebar(array(
    'name' => __( 'Defaultní sidebar', 'cms_blog' ),
		'id' => 'default_sidebar',
    'description' => '',
));

// Nastavení
//***********************************************************************************

$cms->add_set(array(
    'id' => 'page_comments',
    'include'=>array('post'),
    'title' => __('Komentáře', 'cms_blog' ),      
    'info' => sprintf(__( 'Zobrazení komentářů lze nastavit v <a target="_blank" href="%s">nastavení blogu</a>.', 'cms_blog' ),admin_url('admin.php?page=blog_option')),
    'fields' => array(  
        array(
            'name' => __('Komentáře', 'cms_blog' ),
            'id' => 'hide_comments',
            'type' => 'multiple_checkbox',
            'options' => array(
                array('name' => __('Skrýt wordpressové komentáře', 'cms_blog' ), 'value' => 'wordpress'),
                array('name' => __('Skrýt facebookové komentáře', 'cms_blog' ), 'value' => 'facebook'),
            ),
        ),
        array(
            'name' => __( 'Pořadí komentářů', 'cms_blog' ),
            'id' => 'comments_order',
            'type' => 'select',
            'options' => array(
                array('name' => __( 'Použít globální nastavení', 'cms_blog' ), 'value' => ''),
                array('name' => __( 'První facebookové komentáře, druhé wordpressové komentáře', 'cms_blog' ), 'value' => 'facebook'),
                array('name' => __( 'První wordpressové komentáře, druhé facebookové komentáře', 'cms_blog' ), 'value' => 'wordpress'),
            ),
        ),
    )
),"page_set");



$cms->add_page(array(
    'page_title' => __( 'Nastavení blogu', 'cms_blog' ),
    'menu_title' => __( 'Nastavení blogu', 'cms_blog' ),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'blog_option',
    'icon_url' => '',
    'position' => 202
));

$cms->add_subpage(array(
    'parent_slug' => 'blog_option',
    'page_title' => __( 'Blog', 'cms_blog' ),
    'menu_title' => __( 'Blog', 'cms_blog' ),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'blog_option',
));
$cms->add_subpage(array(
    'parent_slug' => 'blog_option',
    'page_title' => __( 'Vzhled blogu', 'cms_blog' ),
    'menu_title' => __( 'Vzhled blogu', 'cms_blog' ),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'appearanceblog_option',
));



$cms->add_page_group(array(
    'id' => 'blog_comments',
    'page' => 'blog_option',
    'name' => __( 'Základní nastavení', 'cms_blog' ),
)); 
$cms->add_page_group(array(
    'id' => 'blog_sidebars',
    'page' => 'blog_option',
    'name' => __( 'Sidebary', 'cms_blog' ),
    'info' => sprintf(__( 'Každému typu stránky můžete přiřadit jiný sidebar. Nové sidebary lze vytvořit v administraci wordpressu v menu <a target="_blank" href="%s">Vzhled -> Widgety</a>.', 'cms_blog' ),admin_url('widgets.php')),
)); 
$cms->add_page_group(array(
    'id' => 'blog_codes',
    'page' => 'blog_option',
    'name' => __( 'Vlastní kódy', 'cms_blog' ),
)); 
$cms->add_page_group(array(
    'id' => 'blog_appearance',
    'page' => 'appearanceblog_option',
    'name' => __( 'Vzhled blogu', 'cms_blog' ),
));
$cms->add_page_group(array(
    'id' => 'blog_header',
    'page' => 'appearanceblog_option',
    'name' => __( 'Hlavička blogu', 'cms_blog' ),
));
$cms->add_page_group(array(
    'id' => 'blog_footer',
    'page' => 'appearanceblog_option',
    'name' => __( 'Patička blogu', 'cms_blog' ),
)); 
$cms->add_page_group(array(
    'id' => 'blog_popups',
    'page' => 'blog_option',
    'name' => __('Pop-upy blogu','cms_blog'),
));  
$cms->add_page_group(array(
    'id' => 'mw_blog_seo',
    'page' => 'blog_option',
    'name' => __('SEO blogu','cms_blog'),
));  
$cms->add_page_group(array(
    'id' => 'blog_facebook',
    'page' => 'blog_option',
    'name' => __('Facebook atributy','cms_blog'),
));  
$cms->add_page_setting('mw_blog_seo',array(  
        array(
        'id' => '',
        'type' => 'title',
        'name' => __('SEO úvodní stránky blogu','cms'),
        ),
        array(
        'name' => __('Meta Title','cms'),
        'id' => 'home_metatitle',
        'type' => 'text',
        'desc' => __('Maximální doporučená délka pro titulek je 70 znaků.','cms_blog'),
        'tooltip' => __('Výchozí hodnota tagu <code>title</code> je „název webu | popis webu“. V případě potřeby můžete obsah tagu <code>title</code> přepsat zde zadaným textem.','cms'),
        ),
        array(
        'name' => __('Meta Desription','cms'),
        'id' => 'home_metadesc',
        'type' => 'textarea',
        'desc' => __('Maximální doporučená délka je 150 znaků.','cms'),
        'tooltip' => __('Meta tag <code>description</code> slouží pro krátký popis obsahu stránky. Některé vyhledávače tento tag používají pro zobrazení popisku stránky ve výsledku vyhledávání. Obsah by měl být tvořen souvislými větami s vhodně zvolenými klíčovými slovy.','cms'),
        ),
        array(
        'name' => __('Meta Keywords','cms'),
        'id' => 'home_metakey',
        'type' => 'textarea',
        'tooltip' => __('Vyplnění meta tag <code>keywords</code> je další možností, jak zvýšit on-page SEO stránky. Napište zde několik klíčových slov, které souvisejí s obsahem stránky. Nepřehánějte to ale s jejich množstvím.','cms'),
        ),
        array(
        'name' => __('Meta Robots','cms'),
        'id' => 'home_robots',
        'type' => 'multiple_checkbox',
        'options' => array(
            array('name' => __('<code>noindex</code> pro tuto stránku','cms'), 'value' => 'noindex'),
            array('name' => __('<code>nofollow</code> pro tuto stránku','cms'), 'value' => 'nofollow'),
            array('name' => __('<code>noarchive</code> pro tuto stránku','cms'), 'value' => 'noarchive'),
            ),
        'tooltip' => __('Meta tag <code>robots</code> umožňuje zakázat robotům indexování obsahu (noindex), sledování odkazů (nofollow) a ukládání casch kopií webu (noarchive).','cms'),
        ),
));
$cms->add_page_setting('blog_facebook',array(
    array(
      'name' => __('Facebookové atributy hlavní stránky blogu','cms_blog'),
      'type' => 'title'
    ),
    array(
        'name' => '',
        'id' => 'info',
        'type' => 'info',
        'content' => __('Toto nastavení, určí facebookové atributy úvodní stránky blogu. Pro kontrolu zobrazení na Facebooku můžete použít debugger na adrese: <a href="https://developers.facebook.com/tools/debug/" target="_blank">https://developers.facebook.com/tools/debug/</a>, kde stačí zadat URL stránky, kterou chcete zkontrolovat.','cms_blog'),
    ),
    array(
        'name' => __('Facebookový titulek','cms_blog'),
        'id' => 'fac_title',
        'type' => 'text',
        'tooltip' => __('Meta tag <code>og:title</code> určuje nadpis stránky při jejím sdílení na Facebooku. Pokud jej nenastavíte, použije se název stránky.','cms_blog'),
    ),
    array(
        'name' => __('Facebookový popis','cms_blog'),
        'id' => 'fac_desc',
        'type' => 'textarea',
        'tooltip' => __('Meta tag <code>og:description</code> určuje popis stránky při jejím sdílení na Facebooku.','cms_blog'),
    ),
    array(
        'name' => __('Facebookový obrázek (og:image)','cms_blog'),
        'id' => 'fac_image',
        'type' => 'upload',
        'tooltip' => __('Pomocí meta tagu <code>og:image</code> můžete Facebooku přikázat, jaký obrázek má použít při sdílení této stránky.','cms_blog'),
    )
));  
$cms->add_page_setting('blog_popups',$cms->container['popup_setting']);  


// Nastavení blogu
//***********************************************************************************

$cms->add_page_setting('blog_comments',array(
    array(
        'id'=>'blog_basic',
        'type'=>'toggle_group',  
        'open'=>true,                    
        'title'=>__('Základní nastavení','cms_blog'),
        'setting'=>array(  
            array(
                'name' => __('Stránka blogu','cms_blog'),
                'id' => 'blog_page',
                'type' => 'blog_selectpage',
                'options' => array(
                    'posts' => __('Zobrazit blog na úvodní stránce','cms_blog'),
                    'page' => __('Zobrazit blog na stránce','cms_blog'),
                ),
            ),
            array(
                'name' => __('Odkaz v logu v hlavičce blogu','cms_blog'),
                'id' => 'blog_logolink',
                'type' => 'radio',
                'options' => array(
                    'blog' => __('Odkazovat na úvodní stránku blogu','cms_blog'),
                    'web' => __('Odkazovat na úvodní stránku webu','cms_blog'),
                ),
                'content' => 'blog',
                'description' => __('Vyberte cíl odkazu loga blogu.','cms_blog'),
            ),            
        )
    ),
    array(
        'id'=>'after_post_content',
        'type'=>'toggle_group',                    
        'title'=>__('Obsah za článkem blogu','cms_blog'),
        'setting'=>array(  
            array(
                'name' => '',
                'id' => 'info',
                'type' => 'info',
                'content' => __('Tento obsah se bude zobrazovat na konci každého článku blogu, což je skvělé místo k umístění magnetu nebo reklamy.','cms_blog'),
            ),
            array(
                'id'=>'content_after_post',
                'title'=>__('Obsah', 'cms_ve'),
                'type'=>'weditor',
                'setting'=>array(
                    'post_type'=>'weditor',
                    'templates'=>'',
                    'texts'=>array(
                        'empty'=>__( ' - Bez obsahu - ', 'cms_ve' ),
                        'edit'=>__( 'Upravit vybraný obsah', 'cms_ve' ),
                        'duplicate'=>__( 'Duplikovat vybraný obsah', 'cms_ve' ),
                        'create'=>__( 'Vytvořit nový obsah', 'cms_ve' ),
                        'delete'=>__( 'Smazat vybraný obsah', 'cms_ve' ),
                    ),
                )
            ),
        )
    ),
    array(
        'id'=>'blog_comments',
        'type'=>'toggle_group',                    
        'title'=>__('Komentáře','cms_blog'),
        'setting'=>array(  
            array(
                'name' => __('Komentáře','cms_blog'),
                'id' => 'comments',
                'type' => 'multiple_checkbox',
                'options' => array(
                    array('name' => __('Zobrazit wordpressové komentáře pod každým článkem','cms_blog'), 'value' => 'wordpress'),
                    array('name' => __('Zobrazit facebookové komentáře pod každým článkem','cms_blog'), 'value' => 'facebook'),
                ),
                'content'=>array('wordpress'=>'wordpress')
            ),
            array(
                'name' => __('Pořadí komentářů','cms_blog'),
                'id' => 'comments_order',
                'type' => 'select',
                'options' => array(
                    array('name' => __('První facebookové komentáře, druhé wordpressové komentáře','cms_blog'), 'value' => 'facebook'),
                    array('name' => __('První wordpressové komentáře, druhé facebookové komentáře','cms_blog'), 'value' => 'wordpress'),
                ),
            ), 
            array(
                'name' => __('Wordpress nastavení komentářů','cms_blog'),
                'id' => 'wordpress_link_setting',
                'type' => 'static',
                'content'=> '<a href="'.admin_url('options-discussion.php').'" target="_blank">'.__('Wordpress nastavení komentářů','cms_blog').'</a>'
            ), 
        )
    ),
    array(
        'id'=>'blog_socials',
        'type'=>'toggle_group',                    
        'title'=>__('Tlačítka sociálních sítí','cms_blog'),
        'setting'=>array(  
            array(
                'name' => __('V detailu článku','cms_blog'),
                'id' => 'show_share',
                'type' => 'multiple_checkbox',
                'options' => array(
                        array('name' => __('Zobrazit tlačítko Facebooku','cms_blog'), 'value' => 'facebook'),
                        array('name' => __('Zobrazit tlačítko sdílet na Facebooku','cms_blog'), 'value' => 'facebook_share'),
                        array('name' => __('Zobrazit tlačítko Twitteru','cms_blog'), 'value' => 'twitter'),
                        array('name' => __('Zobrazit tlačítko Google+','cms_blog'), 'value' => 'google'),
                ),
            ),  
            array(
                'name' => __('Ve výpisu článků','cms_blog'),
                'id' => 'show_share_list',
                'type' => 'multiple_checkbox',
                'options' => array(
                      array('name' => __('Zobrazit tlačítko Facebooku','cms_blog'), 'value' => 'facebook'),
                      array('name' => __('Zobrazit tlačítko sdílet na Facebooku','cms_blog'), 'value' => 'facebook_share'),
                ),
            ), 
        )
    ),       
    array(
        'id'=>'blog_showing',
        'type'=>'toggle_group',                  
        'title'=>__('Zobrazení','cms_blog'),
        'setting'=>array(  
            array(
                'name' => '',
                'id' => 'hide',
                'type' => 'multiple_checkbox',
                'options' => array(
                      array('name' => __('Skrýt autorbox v článcích','cms_blog'), 'value' => 'autorbox'),
                      array('name' => __('Skrýt výpis podobných článků','cms_blog'), 'value' => 'related_posts'),
                      array('name' => __('Skrýt popisek ve výpis podobných článků','cms_blog'), 'value' => 'related_posts_text'),
                      array('name' => __('Skrýt datum zveřejnění článků','cms_blog'), 'value' => 'date'),
                ),
            ),
            array(
                'name' => '',
                'id' => 'show',
                'type' => 'multiple_checkbox',
                'options' => array(
                        array('name' => __('Zobrazit počet návštěvníků článku','cms_blog'), 'value' => 'visitors'),
                ),
            ),   
        )
    )
)); 
$cms->add_page_setting('blog_sidebars',array(
    array(
        'name' => __('Sidebar blogu (úvodní stránky)','cms_blog'),
        'id' => 'sidebar_blog',
        'type' => 'sidebarselect',
        'content'=>'default_sidebar'
    ), 
    array(
        'name' => __('Sidebar kategorií','cms_blog'),
        'id' => 'sidebar_category',
        'type' => 'sidebarselect',
        'content'=>'default_sidebar'
    ), 
    array(
        'name' => __('Sidebar příspěvků','cms_blog'),
        'id' => 'sidebar_post',
        'type' => 'sidebarselect',
        'content'=>'default_sidebar'
    ),   
    array(
        'name' => __('Sidebar autorů','cms_blog'),
        'id' => 'sidebar_author',
        'type' => 'sidebarselect',
        'content'=>'default_sidebar'
    ),  
    array(
        'name' => __('Sidebar tagů','cms_blog'),
        'id' => 'sidebar_tag',
        'type' => 'sidebarselect',
        'content'=>'default_sidebar'
    ),  
    array(
        'name' => __('Sidebar vyhledávání','cms_blog'),
        'id' => 'sidebar_search',
        'type' => 'sidebarselect',
        'content'=>'default_sidebar'
    ),   
)); 
$cms->add_page_setting('blog_codes',array(
    array(
        'name' => __('Skripty v hlavičce','cms_blog'),
        'id' => 'head_scripts',
        'type' => 'textarea'
        ),
    array(
        'name' => __('Skripty v patičce','cms_blog'),
        'id' => 'footer_scripts',
        'type' => 'textarea'
        ),
    array(
        'name' => __('Vlastní css styly (platné pro blog)','cms_blog'),
        'id' => 'css_scripts',
        'type' => 'textarea'
        )
));
$cms->add_page_setting('blog_appearance',array( 
    array(
        'id'=>'appearance_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Vzhled blogu','cms_blog'),
        'setting'=>array(       
            array(
                  'name' => __('Vzhled blogu','cms_blog'),
                  'id' => 'appearance',
                  'type' => 'blogselect',
                  'content' => 'style3',
                  'show' => 'blog_style'
            ),
            array(
                'name' => __('Barva pozadí','cms_blog'),
                'id' => 'background_color',
                'type' => 'color',
                'content'=>'#ebebeb',
                'show_group'=>'blog_style',
                'show_val'=>'style1,style2'
            ),
            array(
                'name' => __('Obrázek na pozadí','cms_blog'),
                'id' => 'background_image',
                'type' => 'bgimage',
                'content'=>array(
                      'pattern'=>0,
                      'fixed'=>'fixed'
                ),
                'show_group'=>'blog_style',
                'show_val'=>'style1,style2'
            ),  
        )
    ),
    array(
        'id'=>'blog_sidebar',
        'type'=>'toggle_group',
        'checkbox'=>1,
        'content'=>1,
        'title'=>__('Zobrazit sidebar blogu','cms_blog'),
        'setting'=>array(       
            array(
                    'name' => __('Zarovnání sidebaru','cms_blog'),
                    'id' => 'structure',
                    'type' => 'imageoption',  
                    'options' => array(
                        'right'=>array( 
                              'image'=>BLOG_DIR.'images/image_select/sidebar1.png',
                              'text'=>__('Sidebar napravo','cms_blog'),
                        ),
                        'left'=>array( 
                              'image'=>BLOG_DIR.'images/image_select/sidebar2.png',
                              'text'=>__('Sidebar nalevo','cms_blog'),
                        ),
                    ),
                    'desc'=>__('Můžete zvolit, zda chcete mít sidebar na pravé nebo levé straně.','cms_blog'),
                    'content' => 'right',
            ),
            array(
                'name' => __('Font nadpisu sidebaru','cms_blog'),
                'id' => 'sidebar_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'16',
                    'font-family'=>'',
                    'weight'=>'',
                    'line-height'=>'',
                    'color'=>'',
                ),
            ),
        )
    ),
    array(
        'id'=>'title_setting',
        'type'=>'toggle_group',
        'title'=>__('Vzhled pruhu s nadpisem','cms_blog'),
        'setting'=>array(
            array(
                'name' => __('Barva pozadí','cms_blog'),
                'id' => 'tb_background',
                'type' => 'background',
                'content'=>array('color1'=>'#111111','color2'=>'')
            ),
            array(
                'name' => __('Font nadpisu','cms_blog'),
                'id' => 'tb_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'35',
                    'font-family'=>'',
                    'weight'=>'',
                    'line-height'=>'',
                    'color'=>'#ffffff',
                ),
            ),     
            
        )
    ),
    array(
        'id'=>'posts_feed_setting',
        'type'=>'toggle_group',
        'title'=>__('Vzhled výpisu příspěvků','cms_blog'),
        'setting'=>array(       
            array(
                    'name' => __('Struktura výpisu příspěvků','cms_blog'),
                    'id' => 'post_look',
                    'content'=>'1',
                    'type' => 'imageoption',  
                    'options' => array(
                        '1'=>array( 
                              'image'=>BLOG_DIR.'images/image_select/post1.png',
                              'text'=>__('Výpis s obrázkem nad textem','cms_blog'),
                        ),
                        '2'=>array( 
                              'image'=>BLOG_DIR.'images/image_select/post2.png',
                              'text'=>__('Výpis s obrázkem napravo','cms_blog'),
                        ),
                        '3'=>array( 
                              'image'=>BLOG_DIR.'images/image_select/post3.png',
                              'text'=>__('Více sloupcový výpis článků','cms_blog'),
                        ),
                    ),
                    'show'=>'post_look',
            ),
            array(
                'id'=>'masonry',
                'title'=>__('Mansory zobrazení','cms_blog'),
                'type'=>'checkbox',
                'label'=>__('Aktivovat mansory zobrazení','cms_blog'),
                'desc'=>__('Masonry zobrazení znamená, že jednotlivé boxy s články se budou inteligentně skládat do mřížky pod sebe podle jejich délky a podle dostupného místa.','cms_blog'),
                'show_group'=>'post_look',
                'show_val'=>'3',
            ),
            array(
                'id'=>'excerpt_length',
                'title'=>__('Délka popisku článku (počet slov)','cms_blog'),
                'type'=>'size',
                'unit'=>__('Slov','cms_blog'),
                'desc'=>__('Defaultně 55 slov.','cms_blog'),
            ),
            array(
                'id'=>'show_button',
                'title'=>__('Tlačítko ve výpisu příspěvků','cms_blog'),
                'type'=>'checkbox',
                'label'=>__('Zobrazit tlačítko "Celý článek" ve výpisu článků','cms_blog'),
                'show'=>'show_button'
            ),
            array(
                'id'=>'button_color',
                'title'=>__('Barva tlačítka','cms_blog'),
                'type'=>'color',
                'content'=>'#209bce',
                'show_group'=>'show_button'
            ),
            array(
                'name' => __('Font nadpisu ve výpisu článků','cms_blog'),
                'id' => 'article_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'27',
                    'font-family'=>'',
                    'weight'=>'',
                    'line-height'=>'',
                    'color'=>'',
                ),
            ),
            array(
                'name' => __('Font popisku ve výpisu článků','cms_blog'),
                'id' => 'article_font_text',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'',
                    'font-family'=>'',
                    'weight'=>'',
                    'line-height'=>'',
                    'color'=>'',
                ),
            ),
        )
    ),
    
    array(
        'id'=>'post_detail_setting',
        'type'=>'toggle_group',
        'title'=>__('Vzhled detailu článku','cms_blog'),
        'setting'=>array(
            array(
                'id'=>'post_detail_look',
                'title'=>__('Vzhled detailu článku','cms_blog'),
                'type'=>'imageselect',
                'options' => array(
                    '3' => BLOG_DIR.'images/image_select/post_detail2.jpg',
                    '4' => BLOG_DIR.'images/image_select/post_detail3.jpg',
                    '2' => BLOG_DIR.'images/image_select/post_detail1.jpg',
                    '1' => BLOG_DIR.'images/image_select/post_detail4.jpg',
                    '5' => BLOG_DIR.'images/image_select/post_detail5.jpg',
                ),
                'content'=> '3',
            ),    
        )
    ),
    array(
        'id'=>'element_text_setting',
        'type'=>'toggle_group',
        'title'=>__('Nadpisy a odrážky v textech','cms_blog'),
        'setting'=>array(        
            array(
                'name' => __('Nadpis 1 (H1)','cms_blog'),
                'id' => 'h1_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'30',
                    'color'=>'',
                ),
            ),    
            array(
                'name' => __('Nadpis 2 (H2)','cms_blog'),
                'id' => 'h2_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'23',
                    'color'=>'',
                ),
            ), 
            array(
                'name' => __('Nadpis 3 (H3)','cms_blog'),
                'id' => 'h3_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'18',
                    'color'=>'',
                ),
            ), 
            array(
                'name' => __('Nadpis 4 (H4)','cms_blog'),
                'id' => 'h4_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'14',
                    'color'=>'',
                ),
            ),    
            array(
                'name' => __('Nadpis 5 (H5)','cms_blog'),
                'id' => 'h5_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'14',
                    'color'=>'',
                ),
            ), 
            array(
                'name' => __('Nadpis 6 (H6)','cms_blog'),
                'id' => 'h6_font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'14',
                    'color'=>'',
                ),
            ),
            array(
                'name' => __('Odrážky v textu','cms_blog'),
                'type' => 'title',
            ),
            array(
                'id'=>'li',
                'name'=>__('Styl odrážek','cms_blog'),
                'type'=>'imageselect',
                'content'=>'1',
                'options' => $vePage->list_icons,
            ),  
        )
    ),
    array(
        'id'=>'custom_blog_fonts',
        'type'=>'toggle_group',
        'checkbox'=>true,
        'title'=>__('Vlastní fonty pro blog (nepřebírat fonty webu)','cms_blog'),
        'setting'=>array(       
            array(
                'name' => __('Font nadpisů','cms_blog'),
                'id' => 'title_font',
                'type' => 'font',
                'content'=>array(
                    'font-family'=>'Open Sans',
                    'weight'=>'600',
                    'color'=>'',
                ),
                'desc'=>__('Globálně nastaví font u všech nadpisů v obsahu stránek blogu.','cms_blog')
            ), 
            array(
                'name' => __('Font textů','cms_blog'),
                'id' => 'font',
                'type' => 'font',
                'content'=>array(
                    'font-size'=>'16',
                    'font-family'=>'Open Sans',
                    'weight'=>'400',
                    'line-height'=>'',
                    'color'=>'#111111',
                ),
            ),
            array(
                'name' => __('Barva odkazů','cms_blog'),
                'id' => 'link_color',
                'type' => 'color',
                'content'=>'#158ebf'
            ),
        )
    ),
)); 
$cms->add_page_setting('blog_footer',array(
    array(
            'name' => __('Použít','cms_blog'),
            'id' => 'show',
            'type' => 'radio',
            'show'=>'footerset',
            'options' => array(
                'global'=>__('Patičku webu','cms_blog'),
                'blog'=>__('Vlastní patičku','cms_blog'),
            ),
            'content' => 'global',
    ),
    array(
            'id'=>'footer_group',
            'type'=>'group',
            'setting'=>$cms->container['footer_setting'],
            'show_group' => 'footerset',
            'show_val' => 'blog',
        )

)); 
$cms->add_page_setting('blog_header',array(
        array(
            'name' => __('Použít','cms_blog'),
            'id' => 'show',
            'type' => 'radio',
            'show'=>'headerset',
            'options' => array(
                'global'=>__('Hlavičku webu','cms_blog'),
                'blog'=>__('Vlastní hlavičku','cms_blog'),
            ),
            'content' => 'global',            
        ),
        array(
            'id'=>'header_group',
            'type'=>'group',
            'setting'=>$cms->container['header_setting'],
            'show_group' => 'headerset',
            'show_val' => 'blog', 
        ),
));    
