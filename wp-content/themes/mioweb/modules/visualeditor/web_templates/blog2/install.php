<?php
$web=array(
    'title'=>__('Osobní blog s magnetem','cms_ve'),
    'desc'=>__('Tato šablona je vhodným základem pro osobní nebo expertní blog.','cms_ve'),
    'demo'=>'http://demo-blog2.mioweb.cz',
    'tags'=>array('blog','personal'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/blog2/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/blog2/thumb_en.jpg',
    'home'=>'posts', 
    // list of web pages   
    'pages'=>array(
      'blog'=>array(
          'title'=>__('Blog','cms_ve'),
      ),  
        'about'=>array(
            'title'=>__('O mně','cms_ve'),
        ),
        'contact'=>array(
            'title'=>__('Kontakt','cms_ve'),
        ),  
        'free'=>array(
            'title'=>__('Zdarma','cms_ve'),
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
            'image'=>MW_IMAGE_LIBRARY.'gallery/tree-flowers.jpeg',
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
                'search'=>array('title'=>''),
                'cms_option_widget'=>array(
                    'title'=>__( 'Ebook zdarma' ,'cms_ve'),
                    'text'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec eget libero luctus, consectetur nulla eu, elementum metus.',
                    'font'=>array(
                        'font-size'=>'20',
                        'color'=>'#ffffff',
                    ),
                    'bg'=>array(
                        'color1'=>'#c42040',
                        'color2'=>'',
                    ),
                ),
                'categories'=>array('title'=>__( 'Kategorie' ,'cms_ve')),
                'cms_posts_widget'=>array(
                    'title'=>__( 'Nejnovější články' ,'cms_ve'),
                    'number'=>5,
                    'show_date'=>1,
                    'image'=>'mio_columns_5',
                    'posts'=>'last',
                ),
            )
        )
    ), 
);
