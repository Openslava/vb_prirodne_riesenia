<?php
$web=array(
    'title'=>__('Osobní web pro fotografy','cms_ve'),
    'desc'=>__('Stylový osobní web pro fotografy a prezentaci jejich práce.','cms_ve'),
    'demo'=>'http://demo-photographer2.mioweb.cz',
    'tags'=>array('photographer','personal'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/photographer2/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/photographer2/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'story'=>array(
            'title'=>__('O mně','cms_ve'),
        ),
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),  
        'gallery'=>array(
            'title'=>__('Galerie','cms_ve'),
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
                    'page'=>'gallery'
                ),
                array(
                    'type'=>'page',
                    'page'=>'story'
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
                        'color1'=>'#000',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
