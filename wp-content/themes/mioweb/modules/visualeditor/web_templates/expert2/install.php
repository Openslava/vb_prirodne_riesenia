<?php
$web=array(
    'title'=>__('Expertní web','cms_ve'),
    'desc'=>__('Expertní web obsahující úvodní stránku, blog, reference, magnet, příběh, seznam služeb nebo produktů, objednávku, faq, webinář, kontaktní stránku, dotazník a děkovačku.','cms_ve'),
    'demo'=>'http://demo-expert2.mioweb.cz',
    'tags'=>array('expert'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert2/thumb.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'home'=>array(
            'title'=>__('Vítejte','cms_ve'),
            'page'=>'home'
        ),
        'blog'=>array(
            'title'=>__('Blog','cms_ve'),
            'page'=>'blog'
        ),
        'magnet'=>array(
            'title'=>__('Vstup','cms_ve'),            
        ),
        'magnetthx'=>array(
            'title'=>__('Děkujeme','cms_ve'),
        ),
        'products'=>array(
            'title'=>__('Produkty/služby','cms_ve'),
        ),
        'product'=>array(
            'title'=>__('Můj produkt nebo služba','cms_ve'),
        ),
        'order'=>array(
            'title'=>__('Objednávka','cms_ve'),
        ),
        'orderthx'=>array(
            'title'=>__('Děkujeme za objednávku','cms_ve'),
        ),
        'aboutme'=>array(
            'title'=>__('O mně','cms_ve'),
        ),
        'testimonials'=>array(
            'title'=>__('Reference','cms_ve'),
        ),
        'faq'=>array(
            'title'=>__('Otázky a odpovědi','cms_ve'),
        ),
        'questions'=>array(
            'title'=>__('Hodnocení','cms_ve'),
        ),
        'questionsthx'=>array(
            'title'=>__('Děkujeme','cms_ve'),
        ),
        'webinar_landing'=>array(
            'title'=>__('Přednáška','cms_ve'),
        ), 
        'webinar_broadcast'=>array(
            'title'=>__('Vysílání','cms_ve'),
        ), 
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),
        
    ), 
    // content blocks  
    'content_blocks'=>array(
        'footer'=>array(),
    ), 
    // menus  
    'menus'=>array(
        'main_header'=>array(
            'name'=>__('Hlavní menu','cms_ve'),
            'items'=>array(
                array(
                    'type'=>'page',
                    'page'=>'home'
                ),
                array(
                    'type'=>'page',
                    'page'=>'magnet',
                    'target'=>'_blank',
                ),
                array(
                    'type'=>'page',
                    'page'=>'blog'
                ),
                array(
                    'type'=>'page',
                    'page'=>'products'
                ),
                array(
                    'type'=>'page',
                    'page'=>'testimonials'
                ),
                array(
                    'type'=>'page',
                    'page'=>'aboutme'
                ),
                
                array(
                    'type'=>'page',
                    'page'=>'contact'
                )
            )
        ),
    ), 
    // sidebars
    'sidebars'=>array(
        'main'=>array(
            'name' => __( 'Hlavní' ,'cms_ve'),
            'desc' => '',
            'widgets'=>array(
                'cms_option_widget'=>array(
                    'title'=>__( 'Nadpis formuláře' ,'cms_ve'),
                    'text'=>__( 'Text formuláře' ,'cms_ve'),
                    'font'=>array(
                        'font-size'=>'20',
                        'color'=>'#ffffff',
                    ),
                    'bg'=>array(
                        'color1'=>'#e4960e',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ),  /*
    // color variants
    'variants'=>array(
        'blue'=>array(
            'color'=>'#41a0a9',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/ea_web/variants/blue.jpg',
        ),
        'green'=>array(
            'color'=>'#259072',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/ea_web/variants/green.jpg',
        ),
    ),  */
);