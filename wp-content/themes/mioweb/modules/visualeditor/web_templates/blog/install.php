<?php
$web=array(
    'title'=>__('Jednoduchý blog','cms_ve'),
    'desc'=>__('Tato šablona je dobrý začátek pro vytvoření vašeho blogu. Začněte jednoduchým blogem a postupně ho doplňujte o další funkcionalitu.','cms_ve'),
    'demo'=>'http://demo-blog.mioweb.cz',
    'tags'=>array('blog','personal'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/blog/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/blog/thumb_en.jpg',
    'home'=>'posts', 
    // list of web pages   
    'pages'=>array(
        'about'=>array(
            'title'=>__('O mně','cms_ve'),
        ),
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),  
        'blog'=>array(
            'title'=>__('Blog','cms_ve'),
        ),  
    ), 
    // menus  
    'menus'=>array(
        'main'=>array(
            'name'=>__('Hlavní menu','cms_ve'),
            'items'=>array(
                array(
                    'type'=>'link',
                    'title'=>__('Blog','cms_ve'),
                    'link'=>get_home_url(),
                ),
                array(
                    'type'=>'page',
                    'page'=>'about'
                ),
                array(
                    'type'=>'page',
                    'page'=>'contact'
                )
            )
        ),
    ),
    // posts
    'posts'=>array(
        
        'p1'=>array(
            'image'=>MW_IMAGE_LIBRARY.'gallery/bwtunel.jpeg',
        ),  
        'p2'=>array(
            'image'=>MW_IMAGE_LIBRARY.'gallery/bwroom.jpeg',
        ), 
        'p3'=>array(
            'image'=>MW_IMAGE_LIBRARY.'gallery/bwtown.jpg',
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
