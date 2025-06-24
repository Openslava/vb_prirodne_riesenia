<?php
$web=array(
    'title'=>__('Jednoduchý expertní web','cms_ve'),
    'desc'=>__('Jednoduchý expertní web pro vaše podnikání. Obsahuje úvodní stránku, blog, reference, obsah zdarma, příběh, seznam služeb nebo produktů, obsahovou stránku, kontaktní stránku, dotazník a děkovačku.','cms_ve'),
    'demo'=>'http://demo-expert.mioweb.cz',
    'tags'=>array('expert'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert/thumb.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'home'=>array(
            'title'=>__('Úvodní stránka','cms_ve'),
            'page'=>'home'
        ),
        'blog'=>array(
            'title'=>__('Blog','cms_ve'),
            'page'=>'blog'
        ),
        'sq'=>array(
            'title'=>__('Zdarma','cms_ve'),
        ),
        'service'=>array(
            'title'=>__('Služby','cms_ve'),
        ),
        'testimonials'=>array(
            'title'=>__('Reference','cms_ve'),
        ),
        'story'=>array(
            'title'=>__('Příběh','cms_ve'),
        ),
        'questions'=>array(
            'title'=>__('Dotazník','cms_ve'),
        ), 
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),
        'content'=>array(
            'title'=>__('Obsah','cms_ve'),
        ),
        'thx'=>array(
            'title'=>__('Poděkování','cms_ve'),
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
                    'page'=>'sq'
                ),
                array(
                    'type'=>'page',
                    'page'=>'service'
                ),
                array(
                    'type'=>'page',
                    'page'=>'testimonials'
                ),
                array(
                    'type'=>'page',
                    'page'=>'story'
                ),
                array(
                    'type'=>'page',
                    'page'=>'blog'
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
    ),
    // color variants
    'variants'=>array(
        'blue'=>array(
            'color'=>'#41a0a9',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert/variants/blue.jpg',
        ),
        'green'=>array(
            'color'=>'#259072',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert/variants/green.jpg',
        ),
        'blue2'=>array(
            'color'=>'#1f547e',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert/variants/blue2.jpg',
        ),
        'purple'=>array(
            'color'=>'#524e73',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert/variants/purple.jpg',
        ),
        'pink'=>array(
            'color'=>'#c0466f',
            'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/expert/variants/pink.jpg',
        )
    ),
);