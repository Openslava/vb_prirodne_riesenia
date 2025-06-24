<?php
$web=array(
    'title'=>__('Interierový designer','cms_ve'),
    'desc'=>__('Šablona je vhodná pro vytvoření osobního nebo firemního webu interierového designu, ale jiných podobných oborů jako zahradní architekt atd.','cms_ve'),
    'demo'=>'http://demo-interier.mioweb.cz',
    'tags'=>array('personal','business'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/interier/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/interier/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'story'=>array(
            'title'=>__('O nás','cms_ve'),
        ),
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),  
        'project'=>array(
            'title'=>__('Stránka projektu','cms_ve'),
        ),       
        'mywork'=>array(
            'title'=>__('Projekty','cms_ve'),
        ), 
        'home'=>array(
            'title'=>__('Úvodní stránka','cms_ve'),
            'page'=>'home'
        ),
        'blog'=>array(
            'title'=>__('Blog','cms_ve'),
            'page'=>'blog'
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
                    'page'=>'mywork'
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
                        'color1'=>'#a89b83',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
