<?php
global $vePage;

$vePage->add_elements(array(   
      'recent_posts'=>array(
            'name'=>__('Články blogu','cms_blog'),
            'description'=>__('Výpis posledních nebo nejčtenějších článků z blogu','cms_blog'),
            'tab_setting'=>array(
                array(
                    'id'=>'setting',
                    'name'=>__('Nastavení výpisu článků','cms_blog'),
                    'setting'=>array(                
                        array(
                            'id'=>'type',
                            'title'=>__('Typ článků', 'cms_blog'),
                            'type'=>'radio',
                            'content'=>'last_posts',
                            'options' => array(
                                'last_posts' => __('Poslední články','cms_blog'),
                                'most_viewed_posts' => __('Nejčtenější články','cms_blog'),
                            ),
                            'show' => 'number',
                        ),  
                        array(
                            'id'=>'category',
                            'title'=>__('Vypisovat články z kategorie','cms_blog'),
                            'type'=>'category_select',
                        ),                       
                        array(
                            'id'=>'number',
                            'title'=>__('Počet článků','cms_blog'),
                            'type'=>'text',
                            'content'=>3
                        ),  
                        array(
                            'id'=>'excerpt_words',
                            'title'=>__('Počet slov v popisku','cms_blog'),
                            'type'=>'text',
                            'content'=>17
                        ),                        
                        array(
                            'id'=>'show',
                            'title'=>__('Zobrazení','cms_blog'),
                            'type' => 'multiple_checkbox',
                            'options' => array(
                                array('name' => __('Skrýt popisek','cms_blog'), 'value' => 'excerpt'),
                                array('name' => __('Skrýt tlačítko','cms_blog'), 'value' => 'more'),
                                array('name' => __('Skrýt obrázek','cms_blog'), 'value' => 'images'),
                            ),
                        ), 
                        array(
                            'id'=>'but_text',
                            'title'=>__('Text tlačítka','cms_blog'),
                            'type'=>'text',
                            'content'=>__('Celý článek','cms_blog'),
                        ),                                             
                    )
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled výpisu článků','cms_blog'),
                    'setting'=>array( 
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled','cms_blog'),                        
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => BLOG_DIR.'images/image_select/posts1.jpg',
                                '2' => BLOG_DIR.'images/image_select/posts2.jpg',
                                '3' => BLOG_DIR.'images/image_select/posts3.jpg',
                                '4' => BLOG_DIR.'images/image_select/posts4.jpg',
                            ),
                            'show' => 'blog_style',
                        ),
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_blog'),
                            'options' => array(     
                                array('name' => '1', 'value' => 'one'),
                                array('name' => '2', 'value' => 'two'),
                                array('name' => '3', 'value' => 'three'),
                                array('name' => '4', 'value' => 'four'),
                            ),
                            'type'=>'select',
                            'content'=>'three',
                            'show_group' => 'blog_style',
                            'show_val' => '1,2,3', 
                        ),
                        array(
                            'id'=>'font',
                            'title'=>__('Font nadpisu','cms_blog'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'20',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            ),
                            'show_group' => 'blog_style',
                            'show_val' => '1,2,3',
                        ),
                        array(
                            'id'=>'font_text',
                            'title'=>__('Font textu','cms_blog'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            ),
                        ),
                    )
                )                    
            )
      )
),'basic');
