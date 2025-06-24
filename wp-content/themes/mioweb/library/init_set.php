<?php 

$seo=get_option('seo_basic');
$foption=get_option('social_option_fac');

$cms->add_fonts(array(
    'Arial',
    'Arial Black',
    'Comic Sans MS',
    'Courier',
    'Georgia', 
    'Impact',
    'Tahoma',
    'Times New Roman', 
    'Trebuchet MS',   
    'Verdana',
));
$cms->add_google_fonts(array(
    
    'Alegreya Sans'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/alegreya.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '500'=>'Medium',
            '600'=>'Semi-Bold',
            '700'=>'Bold',
            '800'=>'Extra-Bold',
            '900'=>'Ultra-Bold',
        ) 
    ),
    'Allura'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/allura.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Amatic SC'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/amatic_sc.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Anton'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/anton.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Arbutus Slab'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/arbutus_slab.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Archivo Narrow'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/archivo_narrow.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Archivo Black'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/archivo_black.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Arimo'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/arimo.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        ) 
    ),
    'Autour One'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/autour_one.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Baloo'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/baloo.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Bree Serif'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/bree_serif.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Capriola'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/capriola.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Caveat Brush'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/caveat_brush.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Clicker Script'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/clicker_script.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Courgette'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/courgette.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Crete Round'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/crete_round.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Dosis'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/dosis.jpg',
        'weights'=>array(
            '200'=>'Extra-Light',
            '300'=>'Light',
            '400'=>'Normal',
            '500'=>'Medium',
            '600'=>'Semi-Bold',
            '700'=>'Bold',
            '800'=>'Extra-Bold',
        )
    ),
    'Enriqueta'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/enriqueta.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        ) 
    ),
    'Exo'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/exo.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '600'=>'Semi-Bold',
            '700'=>'Bold',
            '800'=>'Extra-Bold',
        ) 
    ),
    'Fira Sans'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/fira_sans.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '500'=>'Medium',
            '700'=>'Bold',
        )
    ),
    'Inder'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/inder.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Grand Hotel'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/grand_hotel.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Jaldi'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/jaldi.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Just Me Again Down Here'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/just_me_again_down_here.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Kaushan Script'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/kaushan_script.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Lora'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/lora.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold', 
        ) 
    ),
    'McLaren'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/mclaren.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Merriweather'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/merriweather.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '700'=>'Bold',
            '900'=>'Ultra-Bold',
        )
    ),
    'Mouse Memoirs'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/mouse_memoirs.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Noticia Text'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/noticia_text.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Noto Sans'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/noto_sans.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        ) 
    ),
    'Noto Serif'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/noto_serif.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Open Sans'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/open_sans.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '600'=>'Semi-Bold',
            '700'=>'Bold',
            '800'=>'Extra-Bold',
        )
    ),
    'Open Sans Condensed'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/open_sans_condensed.jpg',
        'weights'=>array(
            '300'=>'Light',
            '700'=>'Bold',
        )
    ), 
    'Oswald'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/oswald.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Pacifico'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/pacifico.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Parisienne'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/parisienne.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Patrick Hand'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/patrick_hand.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Patrick Hand SC'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/patrick_hand_sc.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Petit Formal Script'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/petit_formal.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Play'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/play.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Playfair Display'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/playfair_display.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
            '900'=>'Black',
        )
    ),
    'Ribeye'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/ribeye.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Roboto'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/roboto.jpg',
        'weights'=>array(
            '100'=>'Thin',
            '300'=>'Light',
            '400'=>'Normal',
            '500'=>'Medium',
            '700'=>'Bold',
            '900'=>'Ultra Bold',
        )
    ),
    'Roboto Condensed'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/roboto_condensed.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Roboto Slab'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/roboto_slab.jpg',
        'weights'=>array(
            '100'=>'Thin',
            '300'=>'Light',
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Sacramento'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/sacramento.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Signika'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/signika.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '600'=>'Semi-Bold',
            '700'=>'Bold',
        ) 
    ),
    'Slabo 27px'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/slabo.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Stint Ultra Condensed'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/stint_ultra_condensed.jpg',
        'weights'=>array(
            '400'=>'Normal',
        ) 
    ),
    'Tinos'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/tinos.jpg',
        'weights'=>array(
            '400'=>'Normal',
            '700'=>'Bold',
        )
    ),
    'Unica One'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/unica_one.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Ubuntu'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/ubuntu.jpg',
        'weights'=>array(
            '300'=>'Light',
            '400'=>'Normal',
            '500'=>'Medium',
            '700'=>'Bold',
        ) 
    ),
    'Ubuntu Condensed'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/ubuntu_condensed.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    ),
    'Voces'=>array(
        'img'=>get_template_directory_uri() . '/library/admin/images/fonts/voces.jpg',
        'weights'=>array(
            '400'=>'Normal',
        )
    )
));

$cms->define_set(array(
  'id' => 'page_set',
  'title' => __('Nastavení','cms'),
  'context' => 'normal',
  'priority' => 'high',
  'include'=>array('page','post','mwproduct',),
));

if(!isset($seo['seo'])) 
$cms->add_set(array(
    'id' => 'page_seo',
    'title' => __('SEO stránky','cms'),
    'fields' => array(          
        array(
        'name' => __('Meta Title','cms'),
        'id' => 'metatitle',
        'type' => 'text',  
        'desc' => __('Maximální doporučená délka pro titulek je 70 znaků. Pokud necháte toto pole prázdné, bude tag <code>title</code> obsahovat název stránky.','cms'),
        'tooltip' => __('Tag <code>title</code> je druhým nejdůležitějším prvkem, který ovlivňuje on-page SEO. Jeho obsah se zároveň zobrazuje v záhlaví prohlížeče a jako název stránky při vyhledávání.','cms'),
        ),
        array(
        'name' => __('Meta Description','cms'),
        'id' => 'metadesc',
        'type' => 'textarea',
        'desc' => __('Maximální doporučená délka je 150 znaků.','cms'),
        'tooltip' => __('Meta tag <code>description</code> slouží jako krátký popis obsahu stránky. Některé vyhledávače tento tag používají pro zobrazení popisku stránky ve výsledku vyhledávání. Obsah by měl být tvořen souvislými větami s vhodně zvolenými klíčovými slovy.','cms'),
        ),
        array(
        'name' => __('Meta Keywords','cms'),
        'id' => 'metakey',
        'type' => 'textarea',
        'tooltip' => __('Vyplnění meta tag <code>keywords</code> je další možností, jak zvýšit on-page SEO stránky. Napište zde několik klíčových slov, které souvisejí s obsahem stránky. Nepřehánějte to ale s jejich množstvím.','cms'),
        ),
        array(
        'name' => __('Meta Robots','cms'),
        'id' => 'robots',
        'type' => 'multiple_checkbox',
        'options' => array(
            array('name' => __('<code>noindex</code> pro tuto stránku','cms'), 'value' => 'noindex'),
            array('name' => __('<code>nofollow</code> pro tuto stránku','cms'), 'value' => 'nofollow'),
            array('name' => __('<code>noarchive</code> pro tuto stránku','cms'), 'value' => 'noarchive'),
            ),
        'tooltip' => __('Meta tag <code>robots</code> umožňuje zakázat robotům indexování obsahu (noindex), sledování odkazů (nofollow) a ukládání casch kopií webu (noarchive).','cms'),
        ),
    )
),"page_set");

if(!isset($foption['hide_facebook'])) 
$cms->add_set(array(
    'id' => 'page_facebook',
    'title' => __('Facebook atributy','cms'),
    'info' => __('Facebook atributy definují, jak se bude stránka zobrazovat na Facebooku při jejím sdílení.','cms'),
    'fields' => array(  
        array(
        'id' => 'fac_info',
        'type' => 'info',
        'color' => 'blue',
        'content' => __('Pro kontrolu zobrazení na Facebooku můžete použít <a target="_blank" href="https://developers.facebook.com/tools/debug/">Ladící nástroj pro sdílení</a>, kde stačí zadat URL stránky, kterou chcete zkontrolovat.','cms'),
        ),
        array(
        'name' => __('Facebook titulek','cms'),
        'id' => 'fac_title',
        'type' => 'text',
        'tooltip' => __('Meta tag <code>og:title</code> určuje nadpis stránky při jejím sdílení na Facebooku. Pokud jej nenastavíte, použije se název stránky.','cms'),
        ),
        array(
        'name' => __('Facebook popis','cms'),
        'id' => 'fac_desc',
        'type' => 'textarea',
        'tooltip' => __('Meta tag <code>og:description</code> určuje popis stránky při jejím sdílení na Facebooku.','cms'),
        ),
        array(
        'name' => __('Facebook obrázek (og:image)','cms'),
        'id' => 'fac_image',
        'type' => 'upload',
        'tooltip' => __('Pomocí meta tagu <code>og:image</code> můžete Facebooku přikázat, jaký obrázek má použít při sdílení této stránky.','cms'),
        'desc' => __('Pokud obrázek nezadáte, použije se náhledový obrázek. Pokud není zadán ani náhledový obrázek, použije se defaultní facebookový obrázek, který můžete zadat v nastavení webu. Minimální šířka obrázku by měla být 470 px.','cms')
        )
    )
),"page_set");

$cms->add_set(array(
    'id' => 'page_codes',  
    'include' => array('page', 'post'),
    'title' => __('Vlastní kódy','cms'),
    'fields' => array(  
        array(
        'name' => __('Konverzní kód','cms'),
        'id' => 'codes_conversion',
        'type' => 'textarea',
        'desc' => __('Zde můžete umístit konverzní kód platný pouze pro tuto stránku. Pokud chcete do kódu dynamicky umístit hodnotu z URL adresy, vložte do kódu na místo, kde chcete hodnotu vypsat, řetězec ve tvaru: %%nazev_promenne%%. Pokud tedy budete chtít do kódu vložit například e-mailovou adresu z atributu e-mail (URL adresa bude obsahovat řetězec ve tvaru email=jmeno@poskytovatel.cz), vložte do kódu proměnnou %%email%%. V případě konverzního kódu AFFILBOXU, můžete nechat v konverzním kódu proměnné CENA a ID_TRANSAKCE. CENA se nahradí cenou staženou z faktury FAPI (pokud máte zadané propojení s FAPI) a ID_TRANSAKCE se nahradí variabilním symbolem objednávky nebo emailovou adresou (pokud je v url zadaná).','cms')
        ),
        array(
        'name' => __('Kódy v hlavičce','cms'),
        'id' => 'codes_header',
        'type' => 'textarea',
        'desc' => __('Zde můžete vložit kódy, které je potřeba umístit před tag <code>&lt;/head&gt;</code> a které chcete, aby byly platné pouze pro tuto stránku.','cms')
        ),
        array(
        'name' => __('Kódy v patičce','cms'),
        'id' => 'codes_footer',
        'type' => 'textarea',
        'desc' => __('Zde můžete vložit kódy, které je potřeba umístit před tag <code>&lt;/body&gt;</code> a které chcete, aby byly platné pouze pro tuto stránku.','cms')
        ),
        array(
        'name' => __('Vlastní CSS styly','cms'),
        'id' => 'codes_css',
        'type' => 'textarea',
        'desc' => __('Zde můžete vložit vlastní CSS styly, které budou platit pouze pro tuto stránku.','cms')
        ),
    )
),"page_set");
    


$cms->add_set(array(
    'id' => 'page_redirect',
    'title' => __('Přesměrovat stránku','cms'),
    'fields' => array(          
        array(
        'name' => __('Přesměrovat na','cms'),
        'id' => 'redirect_url',
        'type' => 'page_link',
        'target'=>false,
        'tooltip' => __('Zde můžete zadat URL adresu, na kterou chcete, aby byl uživatel přesměrován. Budou přesměrováni všichni uživatelé na všech zařízeních.','cms'),
        ),  
        array(
        'name' => __('Druh přesměrování','cms'),
        'id' => 'redirect_type',
        'type' => 'select',
        'content'=> '302',
        'options' => array(
            array('name' => __('Dočasné přesměrování','cms'), 'value' => '302'),
            array('name' => __('Trvalé přesměrování','cms'), 'value' => '301'),
        ),
        'tooltip' => __('Informace zda jde o dočasné nebo trvalé přesměrování je důležitá pro SEO.','cms'),
        'show' => 'redirect_type',
        ),  
        array(
        'name' => __('Přesměrovávat ode dne','cms'),
        'id' => 'redirect_date',
        'type' => 'datetime',
        'desc' => __('Pokud zadáte datum, bude přesměrování stránky platné až od tohoto data a času.','cms'),
        'show_group' => 'redirect_type',
        'show_val' => '302',
        ),  
        array(
        'name' => __('Přesměrovávat po půlnoci po X dnech od vstupu do kampaně.','cms'),
        'id' => 'redirect_campaign',
        'type' => 'text',
        'desc' => __('Zde můžete zadat číslo odpovídající počtu dní, které určí, po kolika dnech od vstupu do kampaně se má začít uživatel přesměrovávat z této stránky. Lze použít například, když chcete po X dnech v evergreenové kampani zakázat uživateli přístup k nějaké stránce. Například k objednávce. Stránka, ze které chcete přesměrovávat, musí být zařazena do kampaně jako stránka s obsahem zdarma.','cms'),
        'show_group' => 'redirect_type',
        'show_val' => '302',
        ), 
        array(
        'name' => __('Přesměrování mobilního zařízení','cms'),
        'type' => 'title',
        ),         
        array(
        'name' => __('Přesměrovat mobilní zařízení na','cms'),
        'id' => 'redirect_mobile_url',
        'type' => 'page_link',
        'target'=>false,
        'tooltip' => __('Zde můžete zadat URL adresu, na kterou chcete, aby byl uživatel na mobilním zařízení přesměrován. Návštěvníci, kteří na stránku přistupují pomocí notebooků a klasických stolních počítačů, nebudou přesměrováni. Toto nastavení je vhodné použít, například když chcete místo této stránky na mobilních zařízeních zobrazit jinou stránku.','cms'),
        )
    )
),"page_set");

/* Option pages
********************************************
********************************************
********************************************
*/

$cms->add_page(array(
    'page_title' => __('Nastavení webu','cms'),
    'menu_title' => __('Nastavení webu','cms'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'web_option',
    'icon_url' => '',
    'position' => 201
));
$cms->add_subpage(array(
    'parent_slug' => 'web_option',
    'page_title' => __('Základní nastavení webu','cms'),
    'menu_title' => __('Základní nastavení webu','cms'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'web_option',
));  
$cms->add_page_group(array(
    'id' => 'web_option_basic',
    'page' => 'web_option',
    'name' => __('Základní nastavení','cms'),
));
$cms->add_page_group(array(
    'id' => 'web_option_codes',
    'page' => 'web_option',
    'name' => __('Kódy','cms'),
));
$cms->add_page_group(array(
    'id' => 'web_option_license',
    'page' => 'web_option',
    'name' => __('Licence','cms'),
));
$cms->add_page_group(array(
    'id' => 'web_option_affiliate',
    'page' => 'web_option',
    'name' => __('Affiliate','cms'),
));
$cms->add_page_group(array(
    'id' => 'web_option_smtp',
    'page' => 'web_option',
    'name' => __('E-mail (SMTP)','cms'),
));
$cms->add_page_group(array(
    'id' => 'web_option_others',
    'page' => 'web_option',
    'name' => __('Cookie info','cms'),
));
$cms->add_page_group(array(
    'id' => 'web_option_gdpr',
    'page' => 'web_option',
    'name' => __('Ochrana osobních údajů','cms'),
));
$cms->add_page_group(array(
    'id' => 'mw_custom_fonts',
    'page' => 'web_option',
    'name' => __('Vlastní fonty','cms'),
));

$cms->add_page_setting('web_option_basic',array(
    array(
        'name' => __('Název webu','cms'),
        'id' => 'blogname',
        'type' => 'text',
        'save' => 'option'
        ),
    array(
        'name' => __('Popisek webu','cms'),
        'id' => 'blogdescription',
        'type' => 'text',
        'save' => 'option'
        ),
    array(
        'name' => __('Zástupce webu (favicon)','cms'),
        'id' => 'favicon',
        'type' => 'upload',
        'desc' => __('Nahrajte ikonu ve formátu .png, ideálně ve velikosti 180 ×180 px.','cms'),
        'tooltip' => __('Favicon je ikona webu zobrazující se vedle URL adresy nebo v záložce prohlížeče vedle názvu stránky.','cms'),
        ),
    array(
        'name' => __('Google Site verification kód','cms'),
        'id' => 'site_verification',
        'type' => 'text',
        'desc' => __('Jedna z možností jak ověřit u googlu že jste vlastníkem tohoto webu, například pro napojení na google analytics, je pomocí google site verification kódů. Tento kód, který vám vygeneruje google, zadejte do tohoto pole. <a href="http://napoveda.mioweb.cz/article/227-jak-vlozit-overovaci-kod-google" target="_blank">Návod na získání a vložení site verification kódu</a>','cms'),
        ),
    array(
        'name' => __('Vlastní chybová stránka (404)', 'cms'),
        'id' => '404page',
        'type' => 'selectpage',
        'desc' => __('Tato stránka se zobrazí v případě že uživatel zadá adresu stránky, která neexistuje. Pokud žádnou stránku nevyberete bude se zobrazovat defaultní stránka.','cms'),
        ),
));

$cms->add_page_setting('web_option_codes',array(
    array(
        'name' => __('Google Analytics kód','cms'),
        'id' => 'ga_id',
        'type' => 'textarea',
        'desc' => __('Vložením Google Analytics měřicího kódu na vaše stránky získáte podrobné statistiky návštěvnosti.','cms'),
        ),  
    array(
        'name' => __('Skripty v hlavičce','cms'),
        'id' => 'head_scripts',
        'type' => 'textarea',
        'desc' => __('Zde vložte všechny kódy, které se mají vypisovat v hlavičce webu před tagem <code>&lt;/head&gt;</code>. Tyto kódy se budou vypisovat na všech stránkách webu.','cms'),
        ),
    array(
        'name' => __('Skripty v patičce','cms'),
        'id' => 'footer_scripts',
        'type' => 'textarea',
        'desc' => __('Zde vložte všechny kódy, které se mají vypisovat v patičce webu před tagem <code>&lt;/body&gt;</code>. Tyto kódy se budou vypisovat na všech stránkách webu.','cms'),
        ),
    array(
        'name' => __('Vlastní CSS styly (platné pro celý web)','cms'),
        'id' => 'css_scripts',
        'type' => 'textarea',
        'desc' => __('Vložením vlastních CSS (kaskádových) stylů můžete ovlivnit vzhled webu.','cms'),
        )
));
$cms->add_page_setting('web_option_license',array(
    array(
        'name' => __('Vaše licenční číslo','cms'),
        'id' => 'license',
        'type' => 'license'
    )    
));
$cms->add_page_setting('web_option_affiliate',array(
    array(
        'name' => __('Váš affiliate odkaz','cms'),
        'id' => 'affiliate_link',
        'type' => 'text',
        'content'=>__('http://mioweb.cz','cms'),
        'desc' => __('Zde vložte odkaz na http://mioweb.cz s vaším affiliate kódem. Odkaz se zobrazí v patičce webu, a pokaždé, když se přes něj někdo proklikne na náš web a koupí MioWeb, dostanete z prodeje provizi. Pokud necháte pole prázdné, text v patičce zmizí. Jako affiliate partner se můžete registrovat v <a target="_blank" href="https://partneri.proaffil.cz/smartselling">našem partnerském programu</a>.','cms'),
        )    
));
$cms->add_page_setting('web_option_smtp',array(
    array(
        'name' => '',
        'id' => 'use_smtp',
        'type' => 'checkbox',  
        'show'=>'smtp',
        'label' => __('Použít k zasílání e-mailů vlastní SMTP server.','cms')
    ),
    array(
        'name' => __('E-mailová adresa','cms'),
        'id' => 'smtp_email',
        'type' => 'text',
        'content'=>'',
        'desc' => __('Zadejte adresu, ze které chcete, aby byly e-maily posílány.','cms'),
        'show_group' => 'smtp',
        'show_val' => '1',
    ),
    array(
        'name' => __('Jméno','cms'),
        'id' => 'smtp_name',
        'type' => 'text',
        'content'=>'',
        'desc' => __('Zadejte jméno, které se má zobrazovat v kolonce „Od:“.','cms'),
        'show_group' => 'smtp',
        'show_val' => '1',
    ),
    array(
        'name' => __('SMTP host','cms'),
        'id' => 'smtp_host',
        'type' => 'text',
        'content'=>'',
        'show_group' => 'smtp',
        'show_val' => '1',
    ), 
    array(
        'name' => __('SMTP zabezpečení','cms'),
        'id' => 'smtp_secure',
        'type' => 'radio',
        'options' => array(
            ''=>__('Nezabezpečené','cms'),
            'ssl'=>__('SSL','cms'),
            'tls'=>__('TLS','cms')
        ), 
        'content' => 'ssl',
        'show_group' => 'smtp',
        'show_val' => '1',
    ), 
    array(
        'name' => __('SMTP port','cms'),
        'id' => 'smtp_port',
        'type' => 'text',
        'content'=>'',
        'show_group' => 'smtp',
        'show_val' => '1',
    ),    
    array(
        'name' => __('SMTP autentikace','cms'),
        'id' => 'smtp_authentication',
        'type' => 'radio',
        'options' => array(
            'yes'=>__('Ano','cms'),
            'no'=>__('Ne','cms')
        ), 
        'content' => 'yes',
        'show_group' => 'smtp',
        'show_val' => '1',
    ),  
    array(
        'name' => __('Přihlašovací jméno','cms'),
        'id' => 'smtp_login',
        'type' => 'text',
        'content'=>'',
        'show_group' => 'smtp',
        'show_val' => '1',
    ),  
    array(
        'name' => __('Heslo','cms'),
        'id' => 'smtp_password',
        'type' => 'password',
        'content'=>'',
        'show_group' => 'smtp',
        'show_val' => '1',
    ),  
));
$cms->add_page_setting('web_option_others',array(
    array(
      'id' => 'cookie_info',
      'name' => '',
      'type' => 'info', 
      'content' => __('U samotného MioWebu není potřeba mít tuto možnost aktivní. MioWeb používá cookie pouze v administraci webu a při A/B testování a do cookie neukládá žádné osobní údaje. Některé skripty (například reklamy) nebo pluginy ale mohou pracovat s osobními údaji v cookie a podle evropského práva je nutné o tomto uživatele informovat.','cms'), 
    ),
    array(
        'name' => '',
        'id' => 'use_cookie',
        'type' => 'checkbox',  
        'show'=>'cookie_info',
        'label' => __('Informovat uživatele o používání cookie na tomto webu','cms')
    ),
    array(
        'id'=>'cookie_info_group',
        'type'=>'group',
        'setting'=>array(            
            array(
                'name' => __('Text','cms'),
                'id' => 'cookie_text',
                'type' => 'text',
                'content'=>__('Při poskytování našich služeb nám pomáhají soubory cookie. Využíváním našich služeb s jejich používáním souhlasíte.','cms'),
            ),
            array(
                'name' => __('Text tlačítka','cms'),
                'id' => 'cookie_button_text',
                'type' => 'text',
                'content'=>__('Rozumím','cms'),
            ),
            array(
                'name' => __('URL stránky s podmínkami užití','cms'),
                'id' => 'cookie_url_info',
                'type' => 'page_link',
                'target'=>false,
                'desc' => __('Zde vložte odkaz na vaše obchodní podmínky nebo podmínky užití, které by měly obsahovat také podrobnosti o užití souborů cookie na vašem webu.','cms'),
            ),
        ),
        'show_group' => 'cookie_info',
    )
));
$cms->add_page_setting('web_option_gdpr',array(
    array(
      'id' => 'gdpr_info',
      'name' => '',
      'type' => 'info', 
      'content' => __('Pod každým formulářem, pomocí kterého vám návštěvník webu může poslat své osobní údaje, musíte informovat, jak s těmito údaji budete nakládat.','cms'), 
    ),
    array(
        'name' => __('URL stránky se zásadami zpracování osobních údajů','cms'),
        'id' => 'gdpr_url',
        'type' => 'page_link',
        'target'=>false,
        'desc' => __('Zde vložte odkaz na stránku, která obsahuje podrobnosti o vašich zásadách zpracování osobních údajů.','cms'),
    ),
    array(
        'id'=>'contact_form_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Kontaktní formulář','cms_ve'),
        'setting'=>array(
            array(
                'id'=>'contact_form_info',
                'title'=>__('Informační text pod kontaktním formulářem','cms'),
                'content'=>__('Vaše osobní údaje budou použity pouze pro účely vyřešení vašeho dotazu.','cms'),
                'type'=>'textarea',
            ),
            array(
                'id'=>'contact_form_link_text',
                'title'=>__('Text odkazu na zásady zpracování osobních údajů','cms'),
                'content'=>__('Zásady zpracování osobních údajů','cms'),
                'type'=>'text',
                'tooltip'=>__('Aby se odkaz zobrazoval je nutné mít vyplněnou URL stránku se zásadami zpracování osobních údajů výše.','cms'),
            ),
        )
    ),
    array(
        'id'=>'comments_setting',
        'type'=>'toggle_group',
        'open'=>true,
        'title'=>__('Komentáře','cms_ve'),
        'setting'=>array(
            array(
                'id'=>'comment_form_info',
                'title'=>__('Informační text pod formulářem pro přidání komentáře','cms'),
                'content'=>__('Vaše osobní údaje budou použity pouze pro účely zpracování tohoto komentáře.','cms'),
                'type'=>'textarea',
            ),
            array(
                'id'=>'comment_form_link_text',
                'title'=>__('Text odkazu na zásady zpracování osobních údajů','cms'),
                'content'=>__('Zásady zpracování osobních údajů','cms'),
                'type'=>'text',
                'tooltip'=>__('Aby se odkaz zobrazoval je nutné mít vyplněnou URL stránku se zásadami zpracování osobních údajů výše.','cms'),
            ),
        )
    ),
));
$cms->add_page_setting('mw_custom_fonts',array(
  array(
    'id' => 'fonts_info',
    'name' => '',
    'type' => 'info', 
    'content' => __('Vložte kódy vlastních google fontů. Kódy můžete získat zde: <a href="https://fonts.google.com/" target="_blank">fonts.google.com</a>. Po přidání se tyto fonty objeví ve výběru fontů v nastavení a editaci elementů.','cms'), 
  ),
  array(
      'id'=>'fonts',
      'type'=>'multielement',
      'texts'=>array(
          'add'=>__('Přidat font','cms'),
      ),
      'setting'=>array(     
        array(
            'id'=>'title',
            'title'=>__('Název fontu','cms'),
            'type'=>'text',
        ),
          array(
              'id'=>'font_code',
              'title'=>__('Kód fontu','cms'),
              'type'=>'text',
          ),
      ),
  ), 
));
$cms->add_subpage(array(
    'parent_slug' => 'web_option',
    'page_title' => __('SEO webu','cms'),
    'menu_title' => __('SEO webu','cms'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'seo_option',
));
$cms->add_subpage(array(
    'parent_slug' => 'web_option',
    'page_title' => __('Sociální sítě','cms'),
    'menu_title' => __('Sociální sítě','cms'),
    'capability' => 'edit_theme_options',
    'menu_slug' => 'social_option',
));
$cms->add_page_group(array(
    'id' => 'social_option_fac',
    'page' => 'social_option',
    'name' => __('Facebook','cms'),
));
$cms->add_page_group(array(
    'id' => 'social_option_g',
    'page' => 'social_option',
    'name' => __('Google+','cms'),
));
$cms->add_page_setting('social_option_fac',array(
    array(
        'name' => '',
        'id' => 'hide_facebook',
        'type' => 'checkbox',
        'show' => 'facebook',
        'show_type' => 'hide',
        'label' => __('Vypnout Facebook atributy MioWeb šablony','cms'),
        'desc' => __('Vypnout Facebook atributy šablony je vhodné, pokud chcete pro nastavení Facebook atributů webu nebo stránek používat některý z Wordpress pluginů.','cms'),
    ),
    array(
        'id'=>'cookie_info_group',
        'type'=>'group',
        'setting'=>array(  
            array(
                'name' => __('Defaultní Facebook obrázek (og:image)','cms'),
                'id' => 'fac_img',
                'type' => 'upload',
                'desc' => __('Tento obrázek se bude zobrazovat na Facebooku při sdílení jakékoli stránky vašeho webu. V nastavení každé stránky ale můžete nastavit jiný obrázek, který bude jedinečný právě pro danou stránku. Minimální šířka obrázku by měla být 470 px.','cms'),
            ),  
            array(
                'name' => __('Facebook Application ID','cms'),
                'id' => 'fac_api',
                'type' => 'text',
            ),  
            array(
                'name' => __('Administrator ID','cms'),
                'id' => 'fac_admin_id',
                'type' => 'text',
                'desc' => __('Zde zadejte ID Facebook uživatele, který bude mít oprávnění moderovat Facebook komentáře. Svoje ID získáte například tak, že se v prohlížeči přepnete na svůj facebookový profil. V URL pak změňte řetězec „www“ na „graph“. Zobrazí se seznam údajů, kdy první z nich je ID.','cms'),
            ),
             
        ),
        'show_group'=>'facebook'  
    )     
));
$cms->add_page_setting('social_option_g',array(
    array(
        'name' => __('URL vašeho Google+ profilu','cms'),
        'id' => 'gauthor',
        'type' => 'text',
        'desc' => __('Zadejte URL adresu svého profilu na Google+. Po zadání se bude ve výsledcích vyhledávání na Googlu zobrazovat zadaný profil jako autor webu.','cms'),
    ),  
));

$cms->add_page_group(array(
    'id' => 'seo_basic',
    'page' => 'seo_option',
    'name' => __('Základní nastavení','cms'),
));
$cms->add_page_setting('seo_basic',array(          
    array(
        'name' => __('SEO šablony','cms'),
        'id' => 'seo',
        'type' => 'checkbox',
        'label' => __('Vypnout SEO MioWeb šablony','cms'),
        'desc' => __('Vypnout SEO šablony je vhodné pokud chcete pro nastavení SEO atributů webu nebo stránek používat některý z Wordpress pluginů.','cms'),
    ),
));
