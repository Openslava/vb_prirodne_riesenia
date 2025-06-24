<?php
$web=array(
    'title'=>__('Realitní web','cms_ve'),
    'desc'=>__('Šablona je vhodná pro realitní kancelář s vlastní nabídkou nemovitostí.','cms_ve'),
    'demo'=>'http://demo-reality3.mioweb.cz',
    'tags'=>array('reality'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/reality3/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/reality3/thumb_en.jpg',
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
        'offer'=>array(
            'title'=>__('Nabídka','cms_ve'),
        ),
        'service'=>array(
            'title'=>__('Služby','cms_ve'),
        ),
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),
        'thx'=>array(
            'title'=>__('Poděkování','cms_ve'),
        ),
        'product'=>array(
            'title'=>__('Nemovitost','cms_ve'),
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
                    'page'=>'offer'
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
                        'color1'=>'#b8a528',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
