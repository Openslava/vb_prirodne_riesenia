<?php
$web=array(
    'title'=>__('Jednostránkové portfolio','cms_ve'),
    'desc'=>__('Šablona je vhodná pro výstavbu jednostránkového osobního webu a portfolia.','cms_ve'),
    'demo'=>'http://demo-portfolio4.mioweb.cz',
    'tags'=>array('personal','portfolio'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/portfolio4/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/portfolio4/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
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
                    'type'=>'link',
                    'link'=>'#wrapper',
                    'title'=>'Úvodem',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#row_1',
                    'title'=>'Služby',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#row_2',
                    'title'=>'O mně',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#row_4',
                    'title'=>'Moje práce',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#row_6',
                    'title'=>'Kontakt',
                ),
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
                        'color1'=>'#cf731d',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
