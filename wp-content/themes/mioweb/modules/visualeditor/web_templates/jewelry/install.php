<?php
$web=array(
    'title'=>__('Ruční výroba','cms_ve'),
    'desc'=>__('Šablona je vhodná pro vytvoření prodejního nebo prezentačního webu pro vaše ručně vyráběné produkty.','cms_ve'),
    'demo'=>'http://demo-jewelry.mioweb.cz',
    'tags'=>array('personal','business','product'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/jewelry/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/jewelry/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'order'=>array(
            'title'=>__('Objednávka produktu','cms_ve'),
        ),       
        'story'=>array(
            'title'=>__('O autorovi','cms_ve'),
        ), 
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ), 
        'home'=>array(
            'title'=>__('Úvodní stránka','cms_ve'),
            'page'=>'home'
        ),
    ), 
    // menus  
    'menus'=>array(
        'main'=>array(
            'name'=>__('Hlavní menu','cms_ve'),
            'items'=>array(
                array(
                    'type'=>'page',
                    'page'=>'home'
                ),
                array(
                    'type'=>'page',
                    'page'=>'story'
                ),
                array(
                    'type'=>'page',
                    'page'=>'order'
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
                        'color1'=>'#d6521e',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
