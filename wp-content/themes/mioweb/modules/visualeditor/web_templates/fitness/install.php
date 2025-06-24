<?php
$web=array(
    'title'=>__('Fitness trenér','cms_ve'),
    'desc'=>__('Osobní web pro fitness ternéry a prezentaci jejich práce.','cms_ve'),
    'demo'=>'http://demo-fitness.mioweb.cz',
    'tags'=>array('fitness','personal','expert'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/fitness/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/fitness/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(     
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),
        'story'=>array(
            'title'=>__('O mně','cms_ve'),
        ),  
        'service'=>array(
            'title'=>__('Služby','cms_ve'),
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
                    'page'=>'service'
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
                        'color1'=>'#e0653d',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
