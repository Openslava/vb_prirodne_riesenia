<?php
$web=array(
    'title'=>__('Osobní web','cms_ve'),
    'desc'=>__('Šablona je vhodná pro výstavbu vašeho osobního webu.','cms_ve'),
    'demo'=>'http://demo-personal.mioweb.cz',
    'tags'=>array('personal'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/personal/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/personal/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'story'=>array(
            'title'=>__('O mně','cms_ve'),
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
                        'color1'=>'#c90a30',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
