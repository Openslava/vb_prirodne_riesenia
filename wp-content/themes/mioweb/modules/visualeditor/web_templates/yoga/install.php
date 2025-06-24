<?php
$web=array(
    'title'=>__('Lektorka Jógy','cms_ve'),
    'desc'=>__('Osobní web pro lektorku jógy.','cms_ve'),
    'demo'=>'http://demo-yoga.mioweb.cz',
    'tags'=>array('fitness','personal'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/yoga/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/yoga/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),  
        'story'=>array(
            'title'=>__('O mně','cms_ve'),
        ),
        'free'=>array(
            'title'=>__('Zdarma','cms_ve'),
        ),  
        'lessons'=>array(
            'title'=>__('Lekce','cms_ve'),
        ),
        'blog'=>array(
            'title'=>__('Blog','cms_ve'),
            'page'=>'blog'
        ),
        'home'=>array(
            'title'=>__('Úvod','cms_ve'),
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
                    'page'=>'free'
                ),
                array(
                    'type'=>'page',
                    'page'=>'lessons'
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
    // posts
    'posts'=>array(
        'p1'=>array(
            'image'=>MW_IMAGE_LIBRARY.'webs/yoga/yoga-pic1.jpg',
        ), 
        'p2'=>array(
            'image'=>MW_IMAGE_LIBRARY.'webs/yoga/yoga-pic2.jpg',
        ),  
        'p3'=>array(
            'image'=>MW_IMAGE_LIBRARY.'webs/yoga/yoga-pic3.jpg',
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
                        'color1'=>'#d67cb8',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
