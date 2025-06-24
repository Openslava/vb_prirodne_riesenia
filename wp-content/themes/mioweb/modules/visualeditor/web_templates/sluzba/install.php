<?php
$web=array(
    'title'=>__('Prodej služby','cms_ve'),
    'desc'=>__('Šablona je vhodná pro vytvoření osobního nebo firemního webu zaměřeného na prodej služby.','cms_ve'),
    'demo'=>'http://demo-servis.mioweb.cz',
    'tags'=>array('personal','business','product'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/sluzba/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/sluzba/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'contact'=>array(
            'title'=>__('Objednávka služby','cms_ve'),
        ),       
        'mywork'=>array(
            'title'=>__('Ukázky prací','cms_ve'),
        ), 
        'home'=>array(
            'title'=>__('Úvodní stránka','cms_ve'),
            'page'=>'home'
        ),
    ), 
    // content blocks  
    'content_blocks'=>array(
        'footer'=>array(),
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
                    'page'=>'mywork'
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
                        'color1'=>'#1d9fe0',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
