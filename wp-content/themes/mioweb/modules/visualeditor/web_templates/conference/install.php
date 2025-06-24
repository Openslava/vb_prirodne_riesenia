<?php
$web=array(
    'title'=>__('Konference','cms_ve'),
    'desc'=>__('Šablona je vhodná pro výstavbu jednostránkového osobního webu a portfolia.','cms_ve'),
    'demo'=>'http://demo-conference.mioweb.cz',
    'tags'=>array('event'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/conference/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/conference/thumb_en.jpg',
    'home'=>'page', 
    // list of web pages   
    'pages'=>array(
        'home'=>array(
            'title'=>__('Stránka konference','cms_ve'),
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
                    'link'=>'#uvod',
                    'title'=>'Úvod',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#okonferenci',
                    'title'=>'O konferenci',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#program',
                    'title'=>'Program',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#recnici',
                    'title'=>'Řečníci',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#rezervovat',
                    'title'=>'Rezervovat místo',
                ),
                array(
                    'type'=>'link',
                    'link'=>'#kontakt',
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
                        'color1'=>'#d12e3e',
                        'color2'=>'',
                    ),

                ),
                'search'=>array('title'=>__( 'Hledat' ,'cms_ve')),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve'))
            )
        )
    ), 
);
