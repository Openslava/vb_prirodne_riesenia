<?php
global $vePage, $cms;

$vePage->add_element_groups(array(
    'eshop'=>array(
        'name'=>__('Eshop','mwshop'),
        'subelement'=>true,
    ),
));

$vePage->add_elements(array(
      'pay_button'=>array(
            'name'=>__('Tlačítko koupit','mwshop'),
            'description'=>__('Vyberte si z několika typů tlačítek a přizpůsobte ho barevně podle svých představ.','mwshop'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Tlačítko','cms_ve'),
                    'setting'=>array(
                        array(
                            'id' => 'kind',
                            'title' => __('Typ tlačítka', 'mwshop'),
                            'type' => 'radio',
                            'options' => array(
                                'quick' => __('Koupit', 'mwshop'),
                                'cart' => __('Vložit do košíku', 'mwshop'),
                                /*'quick_with_cart' => __('Koupit s možností vložit do košíku', 'mwshop'),*/
                            ),
                            'content' => 'quick',
                            'desc' => __('Typ "Koupit" umožní přímý nákup zboží bez nutnosti vkládat zboží do košíku a projít celým objednávkovým procesem.', 'mwshop'),
                        ),
                        array(
                            'id'=>'product_id',
                            'title'=>__('Produkt','mwshop'),
                            'type'=>'product_select',
                        ),
                        array(
                            'id'=>'content',
                            'title'=>__('Text tlačítka','mwshop'),
                            'type'=>'text',
                            'content'=>__('Koupit','mwshop')
                        ),
                        array(
                            'id'=>'align',
                            'title'=>__('Zarovnání','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                'left' => __('Nalevo','cms_ve'),
                                'center' => __('Doprostřed','cms_ve'),
                                'right' => __('Napravo','cms_ve'),
                            ),
                            'content' => 'center',
                        ),                        
//                        array(
//                            'id' => 'count_default',
//                            'title' => __('Výchozí počet kusů', 'cms_ve'),
//                            'type' => 'number',
//                            'content' => 1,
//                        ),
//                        array(
//                            'id' => 'count_enable',
//                            'label' => __('Zákazník může určit počet kusů', 'cms_ve'),
//                            'type' => 'checkbox',
//                            'content' => false,
//                        ),
                    )
                ),                
                array(
                    'id'=>'format',
                    'name'=>__('Vzhled tlačítka','cms_ve'),
                    'setting'=>array(
                        
                        array(
                            'id'=>'button',
                            'title'=>__('Styl tlačítka','cms_ve'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'20',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#fff',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#158ebf',
                                    'color2'=>'',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'icon'=>array(
                                    'color'=>'#fff',
                                    'size'=>'23',
                                    'icon'=>'cart',
                                    'icons'=>array( 
                                        'cart' => get_template_directory().'/modules/visualeditor/images/icons/',  
                                        //'cart2' => get_template_directory().'/modules/visualeditor/images/icons/', 
                                    ),
                                ),
                                'border-color'=>'',
                                'corner'=>'0',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            )
                        ),                                        
                    )
                ), 
                                            
            )
      ),
      'product_list'=>array(
            'name'=>__('Výpis produktů','mwshop'),
            'description'=>__('Vypíše buď všechny produkty, vybrané produkty, nejprodávanější produkty nebo produkty určité kategorie.','mwshop'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Výpis','mwshop'),
                    'setting'=>array(
                        array(
                            'id'=>'show',
                            'title'=>__('Vypsat','mwshop'),
                            'type' => 'radio',
                            'options' => array(
                                'custom' => __('Vybrané produkty','mwshop'),
                                'bestsellers' => __('Nejprodávanější produkty','mwshop'),
                                'category' => __('Produkty z kategorie','mwshop'),
                                'all' => __('Všechny produkty','mwshop'),
                            ),
                            'content' => 'custom',
                            'show' => 'show_product',
                        ), 
                        array(
                            'id'=>'custom_products',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat produkt','mwshop'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'product_id',
                                    'title'=>__('Produkt','mwshop'),
                                    'type'=>'product_select',
                                ),
                            ),
                            'show_group' => 'show_product',
                            'show_val' => 'custom',
                        ),
                        array(
                            'id'=>'category',
                            'title'=>__('Kategorie produktu','mwshop'),
                            'type' => 'shop_category_select',
                            'show_group' => 'show_product',
                            'show_val' => 'category',
                        ), 
                        array(
                            'id'=>'bestsellers_count',
                            'title'=>__('Počet produktů','mwshop'),
                            'type' => 'text',
                            'content' => '3',
                            'show_group' => 'show_product',
                            'show_val' => 'bestsellers',
                        ),     
                        array(
                            'id'=>'order',
                            'title'=>__('Řadit zboží podle','mwshop'),
                            'type'=>'select',
                            'content'=> 'date',
                            'options' => array(
                                array('name' => __('Data vytvoření', 'mwshop'), 'value' => 'date'),
                                array('name' => __('Názvu', 'mwshop'), 'value' => 'title'),
                                array('name' => __('Vlastního řazení', 'mwshop'), 'value' => 'menu_order'),
                                array('name' => __('Nejprodávanější', 'mwshop'), 'value' => 'bestseller'),
                            ),
                            'desc'=> __('Pořadí pro vlastní řazení se určuje podle hodnoty "Pořadí" v nastavení každého produktu.', 'mwshop'),
                            'show_group' => 'show_product',
                            'show_val' => 'category,all',
                        ),                  
                    )
                ),                
                array(
                    'id'=>'format',
                    'name'=>__('Vzhled','mwshop'),
                    'setting'=>array(
                        array(
                            'id'=>'product_style',
                            'title'=>__('Vzhled formulářových polí','mwshop'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => MWS_URL_BASE.'/img/image_select/product1.png',
                                '3' => MWS_URL_BASE.'/img/image_select/product3.png',
                                '2' => MWS_URL_BASE.'/img/image_select/product2.png',
                            ),
                            'show' => 'p_style',
                        ),
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','mwshop'),
                            'type'=>'select',
                            'content'=>3,
                            'options' => array(
                                array('name' => '1', 'value' => 1),
                                array('name' => '2', 'value' => 2),
                                array('name' => '3', 'value' => 3),
                                array('name' => '4', 'value' => 4),
                                array('name' => '5', 'value' => 5),
                             ),
                             'show_group' => 'p_style',
                             'show_val' => '1,3', 
                             
                        ),  
                        array(
                            'name' => __('Maximální počet slov popisku','mwshop'),
                            'id' => 'excerpt_length',
                            'type' => 'text',
                            'content'=>'',
                            'desc' => __('Pokud nezadáte žádnou hodnotu, použije se počet slov z nastavení eshopu','mwshop'),
                        ), 
                        array(
                            'id'=>'background',
                            'title'=>__('Barva pozadí','mwshop'),
                            'type'=>'color',
                            'content'=>array(
                                'color'=>'',
                            )
                        ),  
                        array(
                            'id'=>'font',
                            'title'=>__('Barva textů','mwshop'),
                            'type'=>'font',
                            'content'=>array(
                                'color'=>'',
                            )
                        ),                                          
                    )
                ), 
                array(
                  'id' => 'slider',
                  'name' => __( 'Slider', 'cms_ve' ),
                  'setting' => $cms->container['slider_setting']
                ),                            
            )
      ),
      'product_detail'=>array(
            'name'=>__('Detail produktu','mwshop'),
            'description'=>__('Vypíše detail produktu','mwshop'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Detail produktu','mwshop'),
                    'setting'=>array(
                        array(
                            'id'=>'product_id',
                            'title'=>__('Vypsat detail produktu','mwshop'),
                            'type' => 'product_select',
                        ),                     
                    )
                ),                                          
            )
      ),
      'eshop_category_list'=>array(
            'name'=>__('Kategorie eshopu','mwshop'),
            'description'=>__('Vypíše menu s kategoriemi eshopu.','mwshop'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Seznam kategorií','mwshop'),
                    'setting'=>array(
                        array(
                            'id' => 'show',
                            'title' => __('Zobrazit', 'mwshop'),
                            'type' => 'radio',
                            'show' => 'show_cat',
                            'options' => array(
                                'all' => __('Všechny kategorie', 'mwshop'),
                                'sub' => __('Pouze podkategorie od', 'mwshop'),
                            ),
                            'content' => 'all',
                        ),
                        array(
                            'id'=>'category_parent',
                            'title'=>__('Zobrazit podkategorie od','mwshop'),
                            'type'=>'shop_category_select',
                            'show_group' => 'show_cat',
                            'show_val' => 'sub',
                        ),
                    )
                ),                
                array(
                    'id'=>'format',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'style',
                            'title'=>__('Způsob zobrazení','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/gallery1.png',
                                '2' => VS_DIR.'images/image_select/gallery2.png',
                                '3' => VS_DIR.'images/image_select/gallery3.png',
                                '4' => VS_DIR.'images/image_select/gallery4.png',
                                '5' => VS_DIR.'images/image_select/gallery5.png',
                                'v1' => VS_DIR.'images/image_select/vmenu1.png',
                            ),
                            'content' => '1',
                            'show'=> 'style'
                        ), 
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','mwshop'),
                            'type'=>'select',
                            'content'=>3,
                            'options' => array(
                                array('name' => '1', 'value' => 1),
                                array('name' => '2', 'value' => 2),
                                array('name' => '3', 'value' => 3),
                                array('name' => '4', 'value' => 4),
                                array('name' => '5', 'value' => 5),
                             ),
                             'show_group' => 'style',
                             'show_val' => '1,2,3',                             
                        ),  
                        array(
                          'id'=>'font',
                          'title'=>__('Písmo textu','cms_ve'),
                          'type'=>'font',
                          'content'=>array(
                              'font-size'=>'16',
                              'font-family'=>'',
                              'color'=>'',
                              'align'=>'center',
                          )
                      ),  
                                        
                    )
                ), 
                                            
            )
      ),
      'product_price'=>array(
            'name'=>__('Cena produktu','mwshop'),
            'description'=>__('Zobrazte vždy aktuální cenu produktu.','mwshop'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Cena','mwshop'),
                    'setting'=>array(
                        array(
                            'id'=>'product_id',
                            'title'=>__('Zobrazit cenu produktu','mwshop'),
                            'type'=>'product_select',
                        ),
                        array(
                            'id'=>'hide',
                            'title'=>__('Skrýt','mwshop'),
                            'type' => 'multiple_checkbox',
                            'options' => array(
                                array('name' => __('Původní cenu','mwshop'), 'value' => 'salePrice'),
                                array('name' => __('Cenu bez DPH','mwshop'), 'value' => 'vatExcluded'),                                         
                             ),
                        ),
                    )
                ),                
                array(
                    'id'=>'format',
                    'name'=>__('Formátování','mwshop'),
                    'setting'=>array(
                        array(
                            'id'=>'font',
                            'title'=>__('Barva textů','mwshop'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'32',
                                'color'=>'',
                                'align'=>'left',
                            )
                        ),    
                                        
                    )
                ), 
                                            
            )
      ),  
),'eshop');
