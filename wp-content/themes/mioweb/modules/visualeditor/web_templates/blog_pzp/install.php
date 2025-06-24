<?php
$web=array(
    'title'=>__('Osobní blog s magnetem pro podnikatele z pláže','cms_ve'),
    'desc'=>__('Speciální šablona určená jako výchozí bod pro účastníky kurzu podnikání z pláže.','cms_ve'),
    'demo'=>'http://demo-blog-pzp.mioweb.cz',
    'tags'=>array('blog','personal'),
    'modules'=>array('blog'),
    'group'=>array('plazova-platforma'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/blog_pzp/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/blog_pzp/thumb_en.jpg',
    'home'=>'posts', 
    // list of web pages   
    'pages'=>array(
      'blog'=>array(
          'title'=>__('Blog','cms_ve'),
      ),  
        'about'=>array(
            'title'=>__('Můj příběh','cms_ve'),
        ),
        'free'=>array(
            'title'=>__('eBook zdarma','cms_ve'),
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
                    'page'=>'free'
                )
            )
        ),
    ),
    // posts
    'posts'=>array(
        'p1'=>array(
            'image'=>MW_IMAGE_LIBRARY.'gallery/tree-flowers.jpeg',
            'title'=>__( 'Proč jsem se rozhodl/a psát blog?' ,'cms_ve'),
        ), 
        'p2'=>array(
            'image'=>MW_IMAGE_LIBRARY.'gallery/smiling-child.jpeg',
        ),  
        'p3'=>array(
            'image'=>MW_IMAGE_LIBRARY.'gallery/legs-window-car.jpeg',
        ),
    ), 
    // sidebars
    'sidebars'=>array(
        'main'=>array(
            'name' => __( 'Hlavní' ,'cms_ve'),
            'desc' => '',
            'widgets'=>array(
                'cms_posts_widget'=>array(
                    'title'=>__( 'Nejnovější články' ,'cms_ve'),
                    'number'=>5,
                    'show_date'=>1,
                    'image'=>'thumbnail',
                    'posts'=>'last',
                ),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve')),
            )
        ),
        'article'=>array(
            'name' => __( 'S formulářem' ,'cms_ve'),
            'desc' => '',
            'widgets'=>array(
                'cms_option_widget'=>array(
                    'title'=>__( 'Název eBooku' ,'cms_ve'),
                    'text'=>__('Přitažlivý krátký text (max 2. věty), díky kterému se klient rozhodne stáhnout eBook.' ,'cms_ve'),
                    'button_text' => __( 'Stáhnout eBook ZDARMA' ,'cms_ve'),
                    'font'=>array(
                        'font-size'=>'20',
                        'color'=>'#ffffff',
                    ),
                    'bg'=>array(
                        'color1'=>'#c42040',
                        'color2'=>'',
                    ),
                ),
                'cms_posts_widget'=>array(
                    'title'=>__( 'Nejnovější články' ,'cms_ve'),
                    'number'=>5,
                    'show_date'=>1,
                    'image'=>'thumbnail',
                    'posts'=>'last',
                ),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve')),
            )
        )
    ), 
);
