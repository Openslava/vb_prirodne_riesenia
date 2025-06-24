<?php
$web=array(
    'title'=>__('Prázdný web','cms_ve'),
    'desc'=>__('Předinstalovaný web bude obsahovat pouze stránku „Již brzy“ a blog. Tato instalace je vhodná pro vytvoření vlastního webu.','cms_ve'),
    'demo'=>'http://demo-empty.mioweb.cz',
    'tags'=>array('empty'),
    'modules'=>array('blog'),
    'thumb'=>get_template_directory_uri().'/modules/visualeditor/web_templates/empty/thumb.jpg',
    'thumb_en'=>get_template_directory_uri().'/modules/visualeditor/web_templates/empty/thumb_en.jpg',
    'home'=>'page',
    'pages'=>array(
        'comming'=>array(
            'title'=>__('Již brzy','cms_ve'),
            'page'=>'home'
        ),
        'blog'=>array(
            'title'=>__('Blog','cms_ve'),
            'page'=>'blog'
        )
    ),
);
