<?php
$image_lang=(get_locale()=='en_US')? '_en' : '';


if(get_locale()=='sk_SK') {
    $guarantee=array (
       'guarantee2_sk' => VS_DIR.'images/image_select/guarantee2_sk.png',
       'guarantee3_sk' => VS_DIR.'images/image_select/guarantee3_sk.png',
       'guarantee4_sk' => VS_DIR.'images/image_select/guarantee4_sk.png',
       'guarantee5_sk' => VS_DIR.'images/image_select/guarantee5_sk.png',
       'guarantee6_sk' => VS_DIR.'images/image_select/guarantee6_sk.png',
       'guarantee7_sk' => VS_DIR.'images/image_select/guarantee7_sk.png',
       'guarantee8_sk' => VS_DIR.'images/image_select/guarantee8_sk.png',
       'guarantee9_sk' => VS_DIR.'images/image_select/guarantee9_sk.png',
    );
} else if(get_locale()=='en_US') {
    $guarantee=array (
       'guarantee2_en' => VS_DIR.'images/image_select/guarantee2_en.png',
       'guarantee3_en' => VS_DIR.'images/image_select/guarantee3_en.png',
       'guarantee4_en' => VS_DIR.'images/image_select/guarantee4_en.png',
       'guarantee5_en' => VS_DIR.'images/image_select/guarantee5_en.png',
       'guarantee6_en' => VS_DIR.'images/image_select/guarantee6_en.png',
       'guarantee7_en' => VS_DIR.'images/image_select/guarantee7_en.png',
       'guarantee8_en' => VS_DIR.'images/image_select/guarantee8_en.png',
       'guarantee9_en' => VS_DIR.'images/image_select/guarantee9_en.png',
    );
} else if(get_locale()=='de_DE') {
    $guarantee=array (
       'guarantee2_de' => VS_DIR.'images/image_select/guarantee2_de.png',
       'guarantee3_de' => VS_DIR.'images/image_select/guarantee3_de.png',
       'guarantee4_de' => VS_DIR.'images/image_select/guarantee4_de.png',
       'guarantee5_de' => VS_DIR.'images/image_select/guarantee5_de.png',
       'guarantee6_de' => VS_DIR.'images/image_select/guarantee6_de.png',
       'guarantee7_de' => VS_DIR.'images/image_select/guarantee7_de.png',
       'guarantee8_de' => VS_DIR.'images/image_select/guarantee8_de.png',
       'guarantee9_de' => VS_DIR.'images/image_select/guarantee9_de.png',
    );
} else {
    $guarantee=array (
       'guarantee1' => VS_DIR.'images/image_select/guarantee1.png',
       'guarantee2' => VS_DIR.'images/image_select/guarantee2.png',
       'guarantee3' => VS_DIR.'images/image_select/guarantee3.png',
       'guarantee4' => VS_DIR.'images/image_select/guarantee4.png',
       'guarantee5' => VS_DIR.'images/image_select/guarantee5.png',
       'guarantee6' => VS_DIR.'images/image_select/guarantee6.png',
       'guarantee7' => VS_DIR.'images/image_select/guarantee7.png',
       'guarantee8' => VS_DIR.'images/image_select/guarantee8.png',
       'guarantee9' => VS_DIR.'images/image_select/guarantee9.png',
    );
}

$vePage->add_element_groups(array(
    'basic'=>array(
        'name'=>__('Základní','cms_ve'),
        'subelement'=>true,
    ),
    'social'=>array(
        'name'=>__('Sociální sítě','cms_ve'),
        'subelement'=>true,
    ),
    'structure'=>array(
        'name'=>__('Struktura','cms_ve'),
        'subelement'=>false,
    ),
));

$vePage->add_elements(array(
    'text'=>array(
            'name'=>__('Textové pole','cms_ve'),
            'description'=>__('Vložte na stránku textové pole, editovatelné pomocí textového editoru.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'text',
                    'name'=>__('Text','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'content',
                            'type'=>'editor'
                        ),
                    ),
                ), 
                array(
                    'id'=>'format',
                    'name'=>__('Formátování textu','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'font',
                            'title'=>__('Písmo','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'li',
                            'title'=>__('Styl odrážek','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'',
                            'options' => $vePage->list_icons,
                        ),
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled textu','cms_ve'),
                            'type'=>'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/text.png',
                                '2' => VS_DIR.'images/image_select/text2.png',
                            ),
                            'content'=> '1',
                            'show' => 'p_style',
                        ),  
                        array(
                            'id'=>'p-background-color',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'background',
                            'content'=>array('color1'=>'#e8e8e8','color2'=>'','transparency'=>'100'),
                            'show_group' => 'p_style',
                            'show_val' => '2', 
                        ),
                    ),
                ),
            ),
      ),
      'title'=>array(
            'name'=>__('Nadpis','cms_ve'),
            'description'=>__('Pro vkládání nadpisů do stránky. Každému nadpisu lze nastavit font, barvu a velikost.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'text',
                    'name'=>__('Nadpis','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'font',
                            'title'=>__('Písmo','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'30',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'1.2',
                                'letter-spacing'=>'0',
                                'color'=>'',
                                'text-shadow'=>'',
                            )
                        ),
                        array(
                            'id'=>'content',
                            'title'=>__('Text nadpisu','cms_ve'),
                            'type'=>'editor',
                            'desc'=>__('Pokud chcete zvýšit důležitost nadpisu pro SEO, nastavte text v editoru jako „Nadpis“. Čím vyšší číslo nadpisu, tím menší důležitost. Na stránce je doporučeno mít maximálně jeden Nadpis 1.','cms_ve'),
                        ),                        
                    ),
                ), 
                array(
                    'id'=>'format',
                    'name'=>__('Vzhled nadpisu','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled nadpisu','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/title1.png',
                                '2' => VS_DIR.'images/image_select/title2.png',
                                '3' => VS_DIR.'images/image_select/title3.png',
                                '4' => VS_DIR.'images/image_select/title4.png',
                                '5' => VS_DIR.'images/image_select/title5.png',
                                '6' => VS_DIR.'images/image_select/title6.png',
                            ),
                            'content' => '1',
                            'show' => 'title_style', 
                        ), 
                        array(
                            'id'=>'border',
                            'title'=>__('Formátování čar','cms_ve'),
                            'type'=>'border',
                            'content' => array(
                                'size'=>'1',
                                'color'=>'#d5d5d5'
                            ),
                            'show_group' => 'title_style',
                            'show_val' => '4,5', 
                        ),
                        array(
                            'id'=>'background-color',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'background',
                            'content'=>array('color1'=>'#efefef','color2'=>'','transparency'=>'100'),
                            'show_group' => 'title_style',
                            'show_val' => '2,3', 
                        ),
                        array(
                            'id'=>'decoration-color',
                            'title'=>__('Barva podrtžení','cms_ve'),
                            'type'=>'color',
                            'content'=>'#158ebf',
                            'show_group' => 'title_style',
                            'show_val' => '6', 
                        ),
                        array(
                            'id'=>'align',
                            'title'=>__('Zarovnání nadpisu','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                'left' => __('Nalevo','cms_ve'),
                                'center' => __('Doprostřed','cms_ve'),
                                'right' => __('Napravo','cms_ve'),
                            ),
                            'content' => 'center',
                            'show_group' => 'title_style',
                            'show_val' => '3,6',
                        ),
                    ),
                ),
            )
            
      ),
      'button'=>array(
            'name'=>__('Tlačítko','cms_ve'),
            'description'=>__('Vyberte si z několika typů tlačítek a přizpůsobte ho barevně podle svých představ.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Tlačítko','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'content',
                            'title'=>__('Text tlačítka','cms_ve'),
                            'type'=>'text',
                            'content'=>__('Text tlačítka','cms_ve')
                        ),
                        array(
                            'title' => __('Po kliknutí na tlačítko','cms_ve'),
                            'id' => 'show',
                            'type' => 'radio',
                            'show'=>'buttonaction',
                            'options' => array(
                                'url'=>__('Otevřít stránku', 'cms_ve'),
                                'popup'=>__('Zobrazit pop-up', 'cms_ve'),
                            ), 
                            'content' => 'url',
                        ),
                        array(
                            'id'=>'link',
                            'title'=>__('Odkazovat na','cms_ve'),
                            'type'=>'page_link',
                            'show_group' => 'buttonaction', 
                            'show_val' => 'url', 
                        ),
                        array(
                            'title' => __('Zobrazit pop-up','cms_ve'),
                            'id' => 'popup',
                            'type' => 'popupselect',
                            'show_group' => 'buttonaction', 
                            'show_val' => 'popup',  
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
                                    'font-size'=>'30',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#2b2b2b',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ffde21',
                                    'color2'=>'#ffcc00',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border-color'=>'',
                                'hover_effect'=>'lighter',
                                'corner'=>'0',
                                'size'=>'1',
                            )
                        ),                                        
                    )
                ), 
                array(
                    'id'=>'double',
                    'name'=>__('Dvojtlačítko','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'show_but2',
                            'title'=>'',
                            'type'=>'checkbox',
                            'label'=>__('Zobrazit druhé tlačítko','cms_ve'),
                            'show'=>'but2_setting',
                        ),
                        array(
                            'id'=>'image_group',
                            'type'=>'group',  
                            'show_group' => 'but2_setting', 
                            'setting'=>array( 
                            
                                array(
                                    'id'=>'text2',
                                    'title'=>__('Text tlačítka','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Text tlačítka','cms_ve')
                                ),
                                array(
                                    'title' => __('Po kliknutí na tlačítko','cms_ve'),
                                    'id' => 'show2',
                                    'type' => 'radio',
                                    'show'=>'buttonaction2',
                                    'options' => array(
                                        'url'=>__('Otevřít stránku', 'cms_ve'),
                                        'popup'=>__('Zobrazit pop-up', 'cms_ve'),
                                    ), 
                                    'content' => 'url',
                                ),
                                array(
                                    'id'=>'link2',
                                    'title'=>__('Odkazovat na','cms_ve'),
                                    'type'=>'page_link',
                                    'show_group' => 'buttonaction2', 
                                    'show_val' => 'url', 
                                ),
                                array(
                                    'title' => __('Zobrazit pop-up','cms_ve'),
                                    'id' => 'popup2',
                                    'type' => 'popupselect',
                                    'show_group' => 'buttonaction2', 
                                    'show_val' => 'popup',  
                                ), 
                                array(
                                    'id'=>'button2',
                                    'title'=>__('Styl druhého tlačítka','cms_ve'),
                                    'type'=>'button',
                                    'options' => $vePage->list_buttons,
                                    'content'=>array( 
                                        'style'=>'1',                       
                                        'font'=>array(
                                            'font-size'=>'30',
                                            'font-family'=>'',
                                            'weight'=>'',
                                            'color'=>'#2b2b2b',
                                            'text-shadow'=>'',
                                        ),
                                        'background_color'=>array(
                                            'color1'=>'#ffde21',
                                            'color2'=>'#ffcc00',
                                        ),
                                        'hover_color'=>array(
                                            'color1'=>'',
                                            'color2'=>'',
                                        ),
                                        'border-color'=>'',
                                        'corner'=>'0',
                                        'hover_effect'=>'lighter',
                                        'size'=>'1',
                                    )
                                ),
                            
                            )
                            
                        )
 
                    )
                ),                              
            )
      ),

        

      'video'=>array(
            'name'=>__('Video','cms_ve'),
            'description'=>__('Vložte na stránku video jednoduše zadáním odkazu na YouTube nebo Vimeo stránku s videem. Můžete zadat i vlastní embed kód videa.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'video',
                    'name'=>__('Video','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'content',
                            'title'=>__('URL videa','cms_ve'),
                            'type'=>'text',
                            'desc'=>__('Vložte URL stránky s YouTube nebo Vimeo videem.','cms_ve'),
                        ),
                        array(
                            'id'=>'more',
                            'title'=>__('Pokročilé nastavení','cms_ve'),
                            'type' => 'more', 
                            'setting'=>array(
                                array(
                                    'id'=>'setting',
                                    'title'=>__('Nastavení videa','cms_ve'),
                                    'type' => 'multiple_checkbox',
                                    'options' => array(
                                        array('name' => __('Přehrát automaticky','cms_ve'), 'value' => 'autoplay'),                                       
                                        array('name' => __('Zobrazit název videa','cms_ve'), 'value' => 'showinfo'),
                                        array('name' => __('Skrýt ovládání videa (funguje pouze pro YouTube)','cms_ve'), 'value' => 'hide_control'),
                                        array('name' => __('Zobrazit související videa na konci (funguje pouze pro YouTube)','cms_ve'), 'value' => 'rel'),
                                    ),
                                ),
                                array(
                                    'id'=>'noclick',
                                    'title'=>__('Webinářové video','cms_ve'),
                                    'type' => 'checkbox',
                                    'label'=>__('Na video nelze kliknout','cms_ve'),
                                ),
                                array(
                                    'id'=>'code',
                                    'title'=>__('Vlastní kód videa','cms_ve'),
                                    'type'=>'textarea',
                                    'desc'=>__('Zde můžete vložit kód videa. Video se vygeneruje podle tohoto kódu a bude ignorovat ostatní nastavení elementu, kromě zarovnání. Video bude responzivní, a proto bude ignorovat nastavení velikosti.','cms_ve'),
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
                            )
                        )                         
                    )
                ),
                array(
                    'id'=>'popup',
                    'name'=>__('Pop-up video','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'popup',
                            'title'=>'',
                            'label'=>__('Otevírat video ve vyskakovacím (pop-up) okně','cms_ve'),
                            'type'=>'checkbox',
                            'show'=>'popupset',
                        ), 
                        array(
                            'id'=>'image_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'popup_type',
                                    'title'=>__('Otevírat pomocí','cms_ve'),
                                    'type' => 'radio',
                                    'show'=>'popup_type',
                                    'options' => array(
                                        'image' => __('Obrázek','cms_ve'),
                                        'button' => __('Tlačítko','cms_ve'),
                                    ),
                                    'content' => 'image',                                   
                                ),                
                                array(
                                    'id'=>'image',
                                    'title'=>__('Obrázek videa','cms_ve'),
                                    'type'=>'upload', 
                                    'show_group' => 'popup_type',
                                    'show_val' => 'image',   
                                ), 
                                array(
                                    'id'=>'play',
                                    'title'=>__('Styl tlačítka „Play“','cms_ve'),
                                    'type'=>'svg_iconselect',
                                    'content' => array(
                                        'icon'=>'play1',
                                        'size'=>'60',
                                        'color'=>'#ffffff'
                                    ), 
                                    'icons' => array( 
                                        'play1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                        'play2' => get_template_directory().'/modules/visualeditor/images/icons/',
                                        'play3' => get_template_directory().'/modules/visualeditor/images/icons/',
                                        'play5' =>get_template_directory().'/modules/visualeditor/images/icons/',  
                                        'play6' =>get_template_directory().'/modules/visualeditor/images/icons/',     
                                    ),
                                    'show_group' => 'popup_type',
                                    'show_val' => 'image',                                                                            
                                ),
                                array(
                                    'id'=>'button_text',
                                    'title'=>__('Text tlačítka','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Spustit video','cms_ve'),
                                    'show_group' => 'popup_type',
                                    'show_val' => 'button',     
                                ),             
                                array(
                                    'id'=>'popupbutton',
                                    'title'=>__('Styl tlačítka','cms_ve'),
                                    'type'=>'button',
                                    'options' => $vePage->list_buttons,
                                    'content'=>array( 
                                        'style'=>'1',                       
                                        'font'=>array(
                                            'font-size'=>'30',
                                            'font-family'=>'',
                                            'weight'=>'',
                                            'color'=>'#2b2b2b',
                                            'text-shadow'=>'',
                                        ),
                                        'background_color'=>array(
                                            'color1'=>'#ffde21',
                                            'color2'=>'#ffcc00',
                                        ),
                                        'hover_color'=>array(
                                            'color1'=>'',
                                            'color2'=>'',
                                        ),
                                        'border-color'=>'',
                                        'corner'=>'0',
                                        'hover_effect'=>'lighter',
                                        'size'=>'1',
                                    ),
                                    'show_group' => 'popup_type',
                                    'show_val' => 'button', 
                                ),
                            ),
                            'show_group' => 'popupset', 
                        ),  
                    )
                )
            )
      ),
      'bullets'=>array(
            'name'=>__('Odrážky','cms_ve'),
            'description'=>__('Grafické i číselné seznamy s možností zadat nadpis u každé odrážky','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'list',
                    'name'=>__('Odrážky','cms_ve'),
                    'setting'=>array(  
                        array(
                            'id'=>'bullets',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat odrážku','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'title',
                                    'title'=>__('Nadpis','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'text',
                                    'title'=>__('Text','cms_ve'),
                                    'type'=>'textarea',
                                ),
                            ),
                        ),                           
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled seznamu','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'type',
                            'title'=>__('Typ odrážek','cms_ve'),
                            'type' => 'radio',
                            'show'=>'typeset',
                            'options' => array(
                                'image' => __('Obrázkové odrážky','cms_ve'),
                                'decimal' => __('Číselné odrážky','cms_ve'),
                                'own_image' => __('Vlastní odrážky','cms_ve'),
                            ),
                            'content' => 'image',
                        ),
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled odrážek','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '2' => VS_DIR.'images/image_select/bullet2.jpg',
                                '3' => VS_DIR.'images/image_select/bullet3.jpg',
                                '1' => VS_DIR.'images/image_select/bullet1.jpg',
                                '5' => VS_DIR.'images/image_select/bullet5.jpg',
                                '4' => VS_DIR.'images/image_select/bullet4.jpg',
                            ),
                            'content' => '2',
                        ),
                        array(
                            'id'=>'start_number',
                            'title'=>__('Začít od čísla','cms_ve'),
                            'type'=>'text',
                            'content'=>'1',
                            'show_group' => 'typeset',
                            'show_val' => 'decimal', 
                        ),            
                        array(
                            'id'=>'bullet_icon',
                            'title'=>__('Ikonka','cms_ve'),
                            'type'=>'simple_iconselect',
                            'content' => array(
                                'icon'=>'right1',
                            ), 
                            'icons' => array( 
                                'right1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'right2' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'right3' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'right4' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'check1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'check2' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'circle1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'plus1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'cross1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'cross2' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'minus1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'star1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                'heart1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                
                            ),     
                            'show_group' => 'typeset',
                            'show_val' => 'image',                                                                      
                        ),
                        array(
                            'id'=>'custom_image',
                            'title'=>__('Vlastní obrázek odrážky','cms_ve'),
                            'type' => 'image',
                            'show_group' => 'typeset',
                            'show_val' => 'own_image', 
                            'desc'=>__('Obrázek o maximální velikosti 80 × 80 px','cms_ve'),
                        ), 
                        array(
                              'id'=>'size',
                              'title'=>__('Velikost','cms_ve'),
                              'type'=>'slider',
                              'setting'=>array(
                                  'min'=>'10',
                                  'max'=>'40',
                                  'unit'=>''
                              ),
                              'content'=>'20',
                        ), 
                        array(
                              'id'=>'space',
                              'title'=>__('Rozestup','cms_ve'),
                              'type'=>'slider',
                              'setting'=>array(
                                  'min'=>'0',
                                  'max'=>'40',
                                  'unit'=>'px'
                              ),
                              'content'=>'15',
                        ),         
                        array(
                            'id'=>'bullet_color',
                            'title'=>__('Barva odrážek','cms_ve'),
                            'type'=>'color',
                            'content'=>'#219ED1',
                        ), 
                        array(
                            'id'=>'title_font',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),   
                        array(
                            'id'=>'text_font',
                            'title'=>__('Font textů','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),               
                    ),                    
                ),               
            ),
      ),
      /*
      'bullets'=>array(
            'name'=>__('Odrážky','cms_ve'),
            'description'=>__('Grafické i číselné seznamy s možností zadat nadpis u každé odrážky','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'list',
                    'name'=>__('Odrážky','cms_ve'),
                    'setting'=>array(  
                        array(
                            'id'=>'bullets',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat odrážku','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'title',
                                    'title'=>__('Nadpis','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'text',
                                    'title'=>__('Text','cms_ve'),
                                    'type'=>'textarea',
                                ),
                            ),
                        ),                           
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled seznamu','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'type',
                            'title'=>__('Typ odrážek','cms_ve'),
                            'type' => 'radio',
                            'show'=>'typeset',
                            'options' => array(
                                'decimal' => __('Číselné odrážky','cms_ve'),
                                'image' => __('Obrázkové odrážky','cms_ve'),
                            ),
                            'content' => 'decimal',
                        ),
                        array(
                            'id'=>'style_decimal',
                            'title'=>__('Vzhled číselných odrážek','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/bullet1.png',
                                '2' => VS_DIR.'images/image_select/bullet2.png',
                                '3' => VS_DIR.'images/image_select/bullet3.png',
                                '4' => VS_DIR.'images/image_select/bullet4.png',
                            ),
                            'content' => '1',
                            'show_group' => 'typeset',
                            'show_val' => 'decimal', 
                        ), 
                        array(
                            'id'=>'start_number',
                            'title'=>__('Začít od čísla','cms_ve'),
                            'type'=>'text',
                            'content'=>'1',
                            'show_group' => 'typeset',
                            'show_val' => 'decimal', 
                        ),  
                        array(
                            'id'=>'style_image',
                            'title'=>__('Tvar a velikost odrážek','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/bullet-t1.png',
                                '2' => VS_DIR.'images/image_select/bullet-t2.png',
                                '3' => VS_DIR.'images/image_select/bullet-t3.png',
                                '4' => VS_DIR.'images/image_select/bullet-t4.png',
                            ),
                            'content' => '1',
                            'show_group' => 'typeset',
                            'show_val' => 'image', 
                        ),  
                        array(
                            'id'=>'icon',
                            'title'=>__('Ikona odrážky','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/bullet-i1.png',
                                '2' => VS_DIR.'images/image_select/bullet-i2.png',
                                '3' => VS_DIR.'images/image_select/bullet-i3.png',
                                '4' => VS_DIR.'images/image_select/bullet-i4.png',
                            ),
                            'content' => '1',
                            'show_group' => 'typeset',
                            'show_val' => 'image', 
                        ),                       
                        array(
                            'id'=>'bullet_color',
                            'title'=>__('Barva odrážek','cms_ve'),
                            'type'=>'color',
                            'content'=>'#219ED1'
                        ), 
                        array(
                            'id'=>'custom_image',
                            'title'=>__('Vlastní obrázek odrážky','cms_ve'),
                            'type' => 'image',
                            'show_group' => 'typeset',
                            'show_val' => 'image', 
                            'desc'=>__('Obrázek o maximální velikosti 80 × 80 px','cms_ve'),
                        ), 
                        array(
                            'id'=>'title_font',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),   
                        array(
                            'id'=>'text_font',
                            'title'=>__('Font textů','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),               
                    ),                    
                ),               
            ),
      ),
      */
      'image'=>array(
            'name'=>__('Obrázek','cms_ve'),
            'description'=>__('Pro vkládání obrázků. Můžete zadat popisek, vybrat z několika druhů rámečků a zadat velký obrázek, který se otevře po kliknutí. Obrázek může sloužit i jako odkaz.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'basic',
                    'name'=>__('Obrázek','cms_ve'),
                    'setting'=>array(  
                          array(
                              'id'=>'image',
                              'title'=>__('Obrázek','cms_ve'),
                              'type'=>'image'
                          ),    
                          array(
                              'id'=>'click_action',
                              'title'=>__('Akce po kliku na obrázek','cms_ve'),
                              'type'=>'radio',
                              'options' => array(
                                  'none' => __('Žádná','cms_ve'),
                                  'image' => __('Otevřít velký obrázek','cms_ve'),
                                  'link' => __('Otevřít odkaz','cms_ve'),
                                  'alert' => __('Vyskakovací zpráva (alert)','cms_ve'),
                                  'popup' => __('Vlastní pop-up','cms_ve'),
                              ),
                              'content' => 'none',
                              'show' => 'caction',
                          ), 
                          array(
                              'id'=>'alert',
                              'title'=>__('Vyskakovací zpráva','cms_ve'),
                              'type' => 'text', 
                              'show_group' => 'caction',
                              'show_val' => 'alert',  
                          ),
                          array(
                              'title' => __('Zobrazit pop-up','cms_ve'),
                              'id' => 'popup',
                              'type' => 'popupselect',
                              'show_group' => 'caction', 
                              'show_val' => 'popup',  
                          ), 
                          array(
                              'id'=>'link',
                              'title'=>__('Odkaz (URL adresa)','cms_ve'),
                              'type' => 'page_link',
                              'show_group' => 'caction',
                              'show_val' => 'link',  
                          ),
                          array(
                              'id'=>'large_image',
                              'title'=>__('Velký obrázek','cms_ve'),
                              'type'=>'image',
                              'desc'=>__('Zde zadaný obrázek se bude po kliknutí otevírat v pop-up okně.','cms_ve'),
                              'show_group' => 'caction',
                              'show_val' => 'image', 
                          ), 
                    ),
                ),
                array(
                    'id'=>'advanced',
                    'name'=>__('Pokročilé nastavení','cms_ve'),
                    'setting'=>array(                                 
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
                          array(
                              'id'=>'hover',
                              'title'=>__('Efekt po najetí myši','cms_ve'),
                              'type'=>'select',
                              'content'=> '',
                              'options' => array(
                                  array('name' => 'Žádný', 'value' => ''),
                                  array('name' => 'Zoom', 'value' => 'zoom'),
                                  array('name' => 'Zvětšení', 'value' => 'scale'),
                                  array('name' => 'Podbarvení s ikonkou', 'value' => 'overlay_icon'),
                              ),
                              'show' => 'hover_efect',
                          ),
                          array(
                              'id'=>'hover_color',
                              'title'=>__('Barva podbarvení po najetí myši','cms_ve'),
                              'type'=>'color',
                              'content'=> '#179edc',
                              'show_group' => 'hover_efect',
                              'show_val' => 'overlay_icon'
                          ),
                          array(
                              'id'=>'max-width',
                              'title'=>__('Šířka obrázku (v px)','cms_ve'),
                              'type' => 'text',
                              'desc'=>__('Obrázek je responzivní, přizpůsobuje se tedy velikosti dostupného místa. Zde můžete omezit jeho maximální velikost.','cms_ve'),
                          ), 
                          array(
                              'id'=>'label',
                              'title'=>__('Popisek pod obrázkem','cms_ve'),
                              'type' => 'text',
                          ), 
                          array(
                              'id'=>'style',
                              'title'=>__('Rámeček obrázku','cms_ve'),
                              'type' => 'imageselect',
                              'options' => array(
                                  '1' => VS_DIR.'images/image_select/image1.png',
                                  '2' => VS_DIR.'images/image_select/image2.png',
                                  '3' => VS_DIR.'images/image_select/image3.png',
                                  '4' => VS_DIR.'images/image_select/image4.png',
                                  '5' => VS_DIR.'images/image_select/image5.png',
                                  '6' => VS_DIR.'images/image_select/image6.png',
                              ),
                              'content' => '1',
                          ), 
                      ), 
                  ),
              ),
          
      ),
      'image_gallery' => array(
          'name' => __( 'Galerie obrázků', 'cms_ve' ),
          'description' => __( 'Vloží na stránku galerii obrázků.', 'cms_ve' ),
          'tab_setting' => array(    
              array(
                  'id' => 'basic',
                  'name' => __( 'Galerie', 'cms_ve' ),
                  'setting' => array(
                      array(
                          'id' => 'image_gallery_items',
                          'title' => '',
                          'type' => 'image_gallery'
                      ),
                  ),  
              ),              
              array(
                  'id' => 'style',
                  'name' => __( 'Vzhled', 'cms_ve' ),
                  'setting' => array(
                      array(
                          'id'=>'cols',
                          'title'=>__('Počet sloupců galerie','cms_ve'),
                          'type'=>'select',
                          'content'=> 4,
                          'options' => array(
                              array('name' => '1', 'value' => 1),
                              array('name' => '2', 'value' => 2),
                              array('name' => '3', 'value' => 3),
                              array('name' => '4', 'value' => 4),
                              array('name' => '5', 'value' => 5),
                          ),
                      ),
                      array(
                          'id'=>'cols_type',
                          'title'=>__('Mezery mezi obrázky','cms_ve'),
                          'type'=>'select',
                          'content'=> 's',
                          'options' => array(
                              array('name' => 'Velké', 'value' => ''),
                              array('name' => 'Malé', 'value' => 's'),
                              array('name' => 'Žádné', 'value' => 'full'),
                          ),
                      ),
                      array(
                          'id'=>'hover',
                          'title'=>__('Efekt po najetí myši','cms_ve'),
                          'type'=>'select',
                          'content'=> 'zoom',
                          'options' => array(
                              array('name' => 'Žádný', 'value' => ''),
                              array('name' => 'Zoom', 'value' => 'zoom'),
                              array('name' => 'Zvětšení', 'value' => 'scale'),
                              array('name' => 'Podbarvení s ikonkou', 'value' => 'overlay_icon'),
                          ),
                          'show' => 'hover_efect',
                      ),
                      array(
                          'id'=>'hover_color',
                          'title'=>__('Barva podbarvení po najetí myši','cms_ve'),
                          'type'=>'color',
                          'content'=> '#179edc',
                          'show_group' => 'hover_efect',
                          'show_val' => 'overlay_icon'
                      ),
                      array(
                          'id'=>'gallery_style',
                          'title'=>__('Způsob zobrazení','cms_ve'),
                          'type' => 'imageselect',
                          'options' => array(
                              'no_captions' => VS_DIR.'images/image_select/gallery1.png',
                              'captions_over' => VS_DIR.'images/image_select/gallery2.png',
                              'captions_below' => VS_DIR.'images/image_select/gallery3.png',
                          ),
                          'content' => 'no_captions',
                      ),
                      array(
                          'id'=>'thumb_name',
                          'title'=>__('Zobrazit obrázky v poměru:','cms_ve'),
                          'type'=>'select',
                          'content'=> '32',
                          'options' => array(
                              array('name' => __('Původní','cms_ve'), 'value' => 'mio_columns_c'),
                              array('name' => __('Široký (16:9)','cms_ve'), 'value' => '169'),
                              array('name' => __('Základní (3:2)','cms_ve'), 'value' => '32'),
                              array('name' => __('Střední (4:3)','cms_ve'), 'value' => 'mio_columns_'),
                              array('name' => __('Čtverec (1:1)','cms_ve'), 'value' => '11'),
                              array('name' => __('Základní na výšku (2:3)','cms_ve'), 'value' => '23'),
                              array('name' => __('Střední na výšku (3:4)','cms_ve'), 'value' => '34'),
                          ),
                      ),
                      array(
                          'id'=>'font',
                          'title'=>__('Písmo popisků','cms_ve'),
                          'type'=>'font',
                          'content'=>array(
                              'font-size'=>'16',
                              'font-family'=>'',
                              'color'=>'',
                              'align'=>'',
                          )
                      ),
                  ),
              ),
              array(
                  'id' => 'slider',
                  'name' => __( 'Slider', 'cms_ve' ),
                  'setting' => array(
                      array(
                          'id' => 'use_slider',
                          'title' => '',
                          'type' => 'checkbox',
                          'label' => __('Zobrazit jako slider','cms_ve'),
                          'show'=>'sliderset',
                      ),
                      array(
                            'id'=>'sliderset_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'animation',
                                    'title'=>__('Typ animace','cms_ve'),
                                    'type'=>'select',
                                    'content'=> 'fade',
                                    'options' => array(
                                      array('name' => __('Prolínání','cms_ve'), 'value' => 'fade'),
                                      array('name' => __('Zprava doleva','cms_ve'), 'value' => 'slide'),
                                    ),
                                ),                      
                                array(
                                    'id' => 'delay',
                                    'title' => __('Zpoždění slidů','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '3500',
                                ),
                                array(
                                    'id' => 'speed',
                                    'title' => __('Délka animace','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '1000',
                                ),
                                array(
                                    'id' => 'off_autoplay',
                                    'title' => __('Autoplay','cms_ve'),
                                    'type' => 'checkbox',
                                    'label' => __('Vypnout autoplay','cms_ve'),
                                ),
                                array(
                                    'id'=>'color_scheme',
                                    'title'=>__('Barva ovládacích prvků','cms_ve'),
                                    'type'=>'select',
                                    'content'=> '',
                                    'options' => array(
                                        array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
                                        array('name' => __('Tmavé','cms_ve'), 'value' => ''),
                                    ),
                                ),
                            
                            ),
                            'show_group' => 'sliderset',
                      ),
                      
                  ),
              ),
          )
      ),
      'image_text'=>array(
            'name'=>__('Obrázek s textem','cms_ve'),  
            'description'=>__('Obrázek s textem na levé nebo pravé straně je vhodný do situací, kdy chcete obsah rozdělit na dvě části, kdy na jedné straně bude obrázek a na druhé text.','cms_ve'),          
            'tab_setting'=>array(
                array(
                    'id'=>'text',
                    'name'=>__('Obsah','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'image',
                            'title'=>__('Obrázek','cms_ve'),
                            'type'=>'image'
                        ),
                        array(
                            'title'=>__('Nadpis','cms_ve'),
                            'id'=>'title',
                            'type'=>'text'
                        ),
                        array(
                            'title'=>__('Text','cms_ve'),
                            'id'=>'content',
                            'type'=>'editor'
                        ),
                        array(
                            'title'=>__('Text tlačítka','cms_ve'),
                            'id'=>'button_text',
                            'type'=>'text',
                            'content'=>__('Více informací','cms_ve'),
                        ),   
                        array(
                            'title'=>__('Odkaz tlačítka','cms_ve'),
                            'id'=>'button_link',
                            'type'=>'page_link'
                        ),  

                    ),
                    
                ), 
                array(
                    'id'=>'format',
                    'name'=>__('Vzhled a formátování','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'visual_style',
                            'title'=>__('Styl elementu','cms_ve'),
                            'type' => 'imageselect',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/image-text1'.$image_lang.'.jpg',
                                '2' => VS_DIR.'images/image_select/image-text2'.$image_lang.'.jpg',
                            ),
                            'content' => '1',
                            'show' => 'visual_style'
                        ),  
                        array(
                            'id'=>'style',
                            'title'=>__('Velikost obrázku','cms_ve'),
                            'type' => 'select',
                            'options' => array(
                                array('name' => '1/2', 'value' => 'two'),
                                array('name' => '1/3', 'value' => 'three'),
                                array('name' => '2/3', 'value' => 'twothree'),
                                array('name' => '1/4', 'value' => 'four'),
                                array('name' => '1/5', 'value' => 'five'),
                            ),
                            'content' => '1',
                        ), 
                        array(
                            'id'=>'align',
                            'title'=>__('Umístění obrázku','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                'left' => __('Doleva','cms_ve'),
                                'right' => __('Doprava','cms_ve'),
                            ),
                            'content' => 'left',
                        ), 
                        array(
                            'id'=>'text-align',
                            'title'=>__('Zarovnání textu','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                'left' => __('Doleva','cms_ve'),
                                'center' => __('Na střed','cms_ve'),
                                'right' => __('Doprava','cms_ve'),
                            ),
                            'content' => 'left',
                        ), 
                        array(
                            'id'=>'background_color',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type' => 'color',
                            'show_group' => 'visual_style',
                            'show_val' => '2'
                        ), 
                        array(
                            'id'=>'font',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'30',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'font_text',
                            'title'=>__('Font textu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),
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
                                    'color'=>'#2b2b2b',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ffde21',
                                    'color2'=>'#ffcc00',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border-color'=>'',
                                'corner'=>'0',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            )
                        ), 
     
                    ),
                ),
            )
      ),

      'graphic'=>array(
            'name'=>__('Grafika','cms_ve'),
            'description'=>__('Vyberte si z předdefinovaných grafických prvků, jako jsou oddělovače, razítka garancí atd.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'style',
                    'title'=>__('Grafický prvek','cms_ve'),
                    'type' => 'multi_imageselect',
                    'content'=>array(
                        'item'=>'1',
                        'itemtype'=>'hr'
                    ),
                    'tabs' => array(
                        array(
                            'name'=> __('Oddělovače','cms_ve'),
                            'type'=> 'hr',
                            'options'=> array (
                                '1' => VS_DIR.'images/image_select/hr1.png',
                                '2' => VS_DIR.'images/image_select/hr2.png',
                                '3' => VS_DIR.'images/image_select/hr3.png',
                                '4' => VS_DIR.'images/image_select/hr4.png',
                                '5' => VS_DIR.'images/image_select/hr5.png',
                                '6' => VS_DIR.'images/image_select/hr6.png',
                                '7' => VS_DIR.'images/image_select/hr7.png',
                                '8' => VS_DIR.'images/image_select/hr8.png',
                                '9' => VS_DIR.'images/image_select/hr9.png',
                                '10' => VS_DIR.'images/image_select/hr10.png',
                                '11' => VS_DIR.'images/image_select/hr11.png',
                                '12' => VS_DIR.'images/image_select/hr12.png',
                            )
                        ),  
                        array(
                            'name'=> __('Garance','cms_ve'),
                            'type'=>'img',
                            'height'=>'201px',
                            'options'=> $guarantee
                        ), 
                    ),
                ), 
                
            ),
      ),
      'seform'=>array(
            'name'=>__('Formulář','cms_ve'),
            'description'=>__('Na své stránky můžete pomocí API jednoduše vložit SmartEmailingový formulář. Lze ale také vytvářet vlastní formuláře a po vyplnění je poslat na zadaný e-mail nebo URL.','cms_ve'),
            'tab_setting'=>array(              
                array(
                    'id'=>'form',
                    'name'=>__('Formulář','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'type',
                            'title'=>__('Použít','cms_ve'),
                            'type'=>'radio',
                            'content'=>'smartemailing',                            
                            'options' => array(
                                'smartemailing' => __('E-mail marketingový nástroj','cms_ve'),
                                'custom' => __('Vlastní formulář na e-mail','cms_ve'),
                                'custom_url' => __('Vlastní formulář na URL','cms_ve'),
                                'html' => __('Vlastní HTML formuláře','cms_ve'),
                            ),
                            'show' => 'seform',
                        ),
                        array(
                            'id'=>'info_se',
                            'title'=>'',
                            'type'=>'info',
                            'class'=>'set_form_row_nomarg',
                            'content'=> __('Formulář se vygeneruje podle vybraného formuláře, který jste si vytvořili ve svém e-mail marketingovém nástroji. Vyplněný formulář odešle data do vámi používaného nástroje, kde se uloží a poté uživatele přesměruje na zadanou děkovací stránku.','cms_ve'),
                            'show_group' => 'seform',
                            'show_val' => 'smartemailing', 
                        ),                        
                        array(
                            'id'=>'info_custom',
                            'title'=>'',
                            'type'=>'info',
                            'class'=>'set_form_row_nomarg',
                            'content'=> __('Zde si můžete vytvořit vlastní formulář, jehož obsah se bude odesílat na e-mail. Po odeslání formuláře se zadaná data odešlou v podobě e-mailové zprávy na níže zadanou e-mailovou adresu.','cms_ve'),
                            'show_group' => 'seform',
                            'show_val' => 'custom', 
                        ),
                        array(
                            'id'=>'info_custom',
                            'title'=>'',
                            'type'=>'info',
                            'class'=>'set_form_row_nomarg',
                            'content'=> __('Zde si můžete vytvořit vlastní formulář, jehož obsah se bude odesílat na zadanou URL adresu. Tato možnost je určena pro pokročilé uživatele, kteří chtějí data formuláře odeslat na svůj vlastní skript.','cms_ve'),
                            'show_group' => 'seform',
                            'show_val' => 'custom_url', 
                        ),
                        array(
                            'id'=>'info_custom',
                            'title'=>'',
                            'type'=>'info',
                            'class'=>'set_form_row_nomarg',
                            'content'=> __('Pokud využíváte e-mailovou aplikaci, kterou MioWeb nepodporuje, tak zde můžete vložit HTML kód formuláře této aplikace.','cms_ve'),
                            'show_group' => 'seform',
                            'show_val' => 'html', 
                        ),
                        array(
                            'id'=>'content',
                            'title'=>__('Zvolte formulář, který chcete zobrazit.','cms_ve'),
                            'type'=>'form_select',
                            'api'=>'se',
                            'show_group' => 'seform',
                            'show_val' => 'smartemailing', 
                        ), 
                        array(
                            'id'=>'html',
                            'title'=>__('HTML kód formuláře','cms_ve'),
                            'type'=>'textarea',
                            'show_group' => 'seform',
                            'show_val' => 'html', 
                        ),
                        array(
                            'id'=>'email',
                            'title'=>__('E-mailová adresa na kterou chcete vyplněný formulář zaslat','cms_ve'),
                            'type'=>'text',
                            'show_group' => 'seform',
                            'show_val' => 'custom', 
                        ),
                        array(
                            'id'=>'subject',
                            'title'=>__('Předmět e-mailu','cms_ve'),
                            'type'=>'text',
                            'show_group' => 'seform',
                            'show_val' => 'custom', 
                        ),
                        array(
                            'id'=>'thx_url',
                            'title'=>__('Děkovací stránka','cms_ve'),
                            'type'=>'page_link',
                            'target'=>false,
                            'desc'=>__('Zadejte URL stránky, na kterou se má uživatel přesměrovat po odeslání formuláře.','cms_ve'),
                            'show_group' => 'seform',
                            'show_val' => 'custom', 
                        ),
                        array(
                            'id'=>'custom_form',
                            'title'=>__('Pole vlastního formuláře','cms_ve'),
                            'type'=>'customform',
                            'setting'=>array(
                                'type'=>'email'
                            ),
                            'show_group' => 'seform',
                            'show_val' => 'custom', 
                        ),
                        array(
                            'id'=>'url',
                            'title'=>__('URL skriptu, na který se má formulář zaslat.','cms_ve'),
                            'type'=>'text',
                            'show_group' => 'seform',
                            'show_val' => 'custom_url', 
                        ),
                        array(
                            'id'=>'custom_form_url',
                            'title'=>__('Pole vlastního formuláře','cms_ve'),
                            'type'=>'customform',
                            'setting'=>array(
                                'type'=>'url'
                            ),
                            'show_group' => 'seform',
                            'show_val' => 'custom_url', 
                        ),
                    ),
                ), 
                array(
                    'id'=>'look',
                    'class'=>'form_look_setting',
                    'name'=>__('Vzhled formuláře','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'form-style',
                            'title'=>__('Zarovnání formulářových polí','cms_ve'),
                            'type'=>'radio',
                            'content'=>'1',
                            'options' => array(
                                '1' => __('Pod sebou','cms_ve'),
                                '2' => __('Vedle sebe','cms_ve'),
                            ),
                        ),
                        array(
                            'id'=>'form-labels',
                            'title'=>__('Zobrazení popisku polí','cms_ve'),
                            'type'=>'radio',
                            'content'=>'1',
                            'options' => array(
                                '1' => __('Uvnitř polí (vhodné pro textové pole)','cms_ve'),
                                '2' => __('Nad poli','cms_ve'),
                            ),
                        ),
                        array(
                            'id'=>'form-look',
                            'title'=>__('Vzhled formulářových polí','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/forminput1.png',
                                '2' => VS_DIR.'images/image_select/forminput2.png',
                                '3' => VS_DIR.'images/image_select/forminput3.png',
                                '4' => VS_DIR.'images/image_select/forminput4.png',
                                '5' => VS_DIR.'images/image_select/forminput5.png',
                                '6' => VS_DIR.'images/image_select/forminput6.png',
                                '7' => VS_DIR.'images/image_select/forminput7.png',
                                '8' => VS_DIR.'images/image_select/forminput8.png',
                                '9' => VS_DIR.'images/image_select/forminput9.png',
                                '10' => VS_DIR.'images/image_select/forminput10.png',
                                '11' => VS_DIR.'images/image_select/forminput11.png',
                            ),
                        ),
                        array(
                            'id'=>'form-font',
                            'title'=>__('Písmo formulářových polí','cms_ve'),
                            'type'=>'font',
                            'group'=>'input',
                            'content'=>array(
                                'font-size'=>'15',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'background',
                            'title'=>__('Barva formulářových polí','cms_ve'),
                            'type'=>'color',
                            'group'=>'input',
                            'content' => '#eeeeee'
                        ),  
                        array(
                            'id'=>'button_text',
                            'title'=>__('Text tlačítka','cms_ve'),
                            'type'=>'text',
                            'desc' => __('Pokud text tlačítka nezadáte, použije se text nastavený ve formuláři SmartEmailingu.','cms_ve'),
                        ),   
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
                                    'color'=>'#2b2b2b',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ffde21',
                                    'color2'=>'#ffcc00',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border-color'=>'',
                                'corner'=>'0',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            )
                        ),                            
                    ),
                ),
                
                array(
                    'id'=>'popup',
                    'name'=>__('Pop-up formulář','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'popup',
                            'title'=>'',
                            'label'=>__('Otevírat formulář ve vyskakovacím (pop-up) okně','cms_ve'),
                            'type'=>'checkbox',
                            'show'=>'popupset',
                        ), 
                        array(
                            'id'=>'popup_title',
                            'title'=>__('Nadpis nad formulářem ve vyskakovacím okně','cms_ve'),
                            'type'=>'text',
                            'content'=>__('Zadejte svůj e-mail a registrujte se.','cms_ve'),
                            'show_group' => 'popupset',
                        ), 
                        array(
                            'id'=>'textinpopup',
                            'title'=>__('Text nad formulářem ve vyskakovacím okně','cms_ve'),
                            'type'=>'textarea',
                            'show_group' => 'popupset',
                        ), 
                        array(
                            'id'=>'popup_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'popup_type',
                                    'title'=>__('Otevírat pomocí','cms_ve'),
                                    'type' => 'radio',
                                    'show'=>'popup_type',
                                    'options' => array(
                                        'button' => __('Tlačítko','cms_ve'),
                                        'image' => __('Obrázek','cms_ve'),
                                        'link' => __('Odkaz','cms_ve'),                                        
                                    ),
                                    'content' => 'button',                                   
                                ),                
                                array(
                                    'id'=>'image',
                                    'title'=>__('Obrázek','cms_ve'),
                                    'type'=>'upload', 
                                    'show_group' => 'popup_type',
                                    'show_val' => 'image',   
                                ),
                                array(
                                    'id'=>'popup_text',
                                    'title'=>__('Text tlačítka, kterým se otevírá formulář','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Registrovat se','cms_ve'), 
                                    'show_group' => 'popup_type',
                                    'show_val' => 'button',    
                                ),                                                 
                                array(
                                    'id'=>'popupbutton',
                                    'title'=>__('Styl tlačítka','cms_ve'),
                                    'type'=>'button',
                                    'options' => $vePage->list_buttons,
                                    'content'=>array( 
                                        'style'=>'1',                       
                                        'font'=>array(
                                            'font-size'=>'30',
                                            'font-family'=>'',
                                            'weight'=>'',
                                            'color'=>'#2b2b2b',
                                            'text-shadow'=>'',
                                        ),
                                        'background_color'=>array(
                                            'color1'=>'#ffde21',
                                            'color2'=>'#ffcc00',
                                        ),
                                        'hover_color'=>array(
                                            'color1'=>'',
                                            'color2'=>'',
                                        ),
                                        'border-color'=>'',
                                        'corner'=>'0',
                                        'size'=>'1',
                                    ),
                                    'show_group' => 'popup_type',
                                    'hover_effect'=>'lighter',
                                    'show_val' => 'button',

                                ),   
                                array(
                                    'id'=>'link_text',
                                    'title'=>__('Text odkazu, který otevírá formulář','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Registrovat se','cms_ve'), 
                                    'show_group' => 'popup_type',
                                    'show_val' => 'link',    
                                ),                            
                                array(
                                    'id'=>'link_font',
                                    'title'=>__('Font odkazu','cms_ve'),
                                    'type'=>'font',
                                    'content'=>array(
                                        'font-size'=>'',
                                        'color'=>'',
                                    ),
                                    'show_group' => 'popup_type',
                                    'show_val' => 'link',
                                ),  
                        
                           ),
                           'show_group' => 'popupset',
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
                            'show_group' => 'popupset',
                        ),    
                    ),
                ),
            ),
      ),
      'contactform'=>array(
            'name'=>__('Kontaktní formulář','cms_ve'),
            'description'=>__('Pomocí kontaktního formuláře vám mohou návštěvníci stránek zasílat zprávy na váš e-mail.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'form',
                    'name'=>__('Formulář','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'email',
                            'title'=>__('E-mailová adresa','cms_ve'),
                            'type'=>'text',
                            'content'=>'@',
                            'desc'=>__('Zadejte e-mailovou adresu, na kterou se bude formulář odesílat.','cms_ve'),
                        ),
                        array(
                            'id'=>'button_text',
                            'title'=>__('Text tlačítka','cms_ve'),
                            'type'=>'text',
                            'content'=>__('Odeslat dotaz','cms_ve'),
                        ),
                        array(
                            'name' => __('Zobrazení','cms_ve'),
                            'id' => 'hide',
                            'type' => 'multiple_checkbox',
                            'options' => array(
                                  array('name' => __('Skrýt telefon','cms_ve'), 'value' => 'phone'),
                            ),
                        ), 
                    ),
                ), 
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled formuláře','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'form-appearance',
                            'title'=>__('Vzhled formuláře','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'3',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/contactform1.png',
                                '2' => VS_DIR.'images/image_select/contactform2.png',
                                '3' => VS_DIR.'images/image_select/contactform3.png',
                            ),
                        ),
                        array(
                            'id'=>'form-style',
                            'title'=>__('Vzhled formulářových polí','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/forminput1.png',
                                '2' => VS_DIR.'images/image_select/forminput2.png',
                                '3' => VS_DIR.'images/image_select/forminput3.png',
                                '4' => VS_DIR.'images/image_select/forminput4.png',
                                '5' => VS_DIR.'images/image_select/forminput5.png',
                                '6' => VS_DIR.'images/image_select/forminput6.png',
                                '7' => VS_DIR.'images/image_select/forminput7.png',
                                '8' => VS_DIR.'images/image_select/forminput8.png',
                                '9' => VS_DIR.'images/image_select/forminput9.png',
                                '10' => VS_DIR.'images/image_select/forminput10.png',
                            ),
                        ),
                        array(
                            'id'=>'form-font',
                            'title'=>__('Písmo formulářových polí','cms_ve'),
                            'type'=>'font',
                            'group'=>'input',
                            'content'=>array(
                                'font-size'=>'15',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'background',
                            'title'=>__('Barva pozadí formulářových polí','cms_ve'),
                            'type'=>'color',
                            'group'=>'input',
                            'content' => '#eeeeee'
                        ),      
                    ),
                ),
                array(
                    'id'=>'button',
                    'name'=>__('Tlačítko formuláře','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'button',
                            'title'=>__('Styl tlačítka','cms_ve'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'30',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#2b2b2b',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ffde21',
                                    'color2'=>'#ffcc00',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border-color'=>'',
                                'corner'=>'0',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            )
                        ),    
                    ),
                ),
                
            ),
      ),
      'wpcomments'=>array(
            'name'=>__('WP komentáře','cms_ve'),
            'description'=>__('Můžete si vybrat z několika vzhledů klasických komentářů.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'style',
                    'title'=>__('Vzhled komentářů','cms_ve'),
                    'type'=>'imageselect',
                    'content'=>'3',
                    'options' => array(
                        '3' => VS_DIR.'images/image_select/comment3.png',
                        '1' => VS_DIR.'images/image_select/comment1.png',
                        '2' => VS_DIR.'images/image_select/comment2.png',
                    ),
                ), 
                array(
                    'id'=>'button',
                    'title'=>__('Styl tlačítka','cms_ve'),
                    'type'=>'button',
                    'options' => $vePage->list_buttons,
                    'content'=>array( 
                        'style'=>'1',                       
                        'font'=>array(
                            'font-size'=>'25',
                            'font-family'=>'',
                            'weight'=>'',
                            'color'=>'#2b2b2b',
                            'text-shadow'=>'',
                        ),
                        'background_color'=>array(
                            'color1'=>'#ffde21',
                            'color2'=>'#ffcc00',
                        ),
                        'hover_color'=>array(
                            'color1'=>'',
                            'color2'=>'',
                        ),
                        'border-color'=>'',
                        'corner'=>'0',
                        'hover_effect'=>'lighter',
                        'size'=>'1',
                    )
                ),    
            ),
      ),
      'menu'=>array(
            'name'=>__('Navigace/Menu','cms_ve'),
            'description'=>__('Pro vkládání navigace do obsahu stránek. Pomocí tohoto elementu můžete jako menu vypsat seznam podstránek určité stránky anebo klasické wordpressové menu.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'menu',
                    'name'=>__('Menu','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'type',
                            'title'=>__('Vypsat','cms_ve'),
                            'type' => 'radio',
                            'show'=>'menuset',
                            'options' => array(
                                'menu' => __('Wordpressové menu','cms_ve'),
                                'subpage' => __('Seznam podstránek jako menu','cms_ve'),
                            ),
                            'content' => 'menu',
                        ), 
                        array(
                            'id'=>'menu',
                            'title'=>__('Menu','cms_ve'),
                            'type'=>'selectmenu',
                            'show_group' => 'menuset',
                            'show_val' => 'menu', 
                        ),
                        array(
                            'id'=>'page',
                            'title'=>__('Vypsat podstránky od','cms_ve'),
                            'type'=>'selectpage',
                            'show_group' => 'menuset',
                            'show_val' => 'subpage', 
                            'desc'=>__('Pokud nic nezvolíte, vypíšou se podstránky této stránky.','cms_ve'),
                        ),
                        array(
                            'id'=>'title',
                            'title'=>__('Nadpis menu','cms_ve'),
                            'type'=>'text',
                        ), 
                    )
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled menu','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled menu','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/menu1'.$image_lang.'.png',
                                '2' => VS_DIR.'images/image_select/menu2'.$image_lang.'.png',
                                '3' => VS_DIR.'images/image_select/menu3'.$image_lang.'.png',
                                '4' => VS_DIR.'images/image_select/menu4'.$image_lang.'.png',
                                '5' => VS_DIR.'images/image_select/menu5'.$image_lang.'.png',
                                '8' => VS_DIR.'images/image_select/menu8'.$image_lang.'.png',
                                '6' => VS_DIR.'images/image_select/menu6'.$image_lang.'.png',
                                '7' => VS_DIR.'images/image_select/menu7'.$image_lang.'.png',
                            ),
                        ),
                        array(
                            'id'=>'font',
                            'title'=>__('Font položek menu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'14',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            ),
                        ),
                        array(
                            'id'=>'title_font',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'15',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            ),
                        ),
                        array(
                            'id'=>'color-active',
                            'title'=>__('Barva aktivní položky','cms_ve'),
                            'type'=>'color',
                            'content'=>'#219ed1'
                        ), 
                    )
                ),
            )
      ),
      'countdown'=>array(
            'name'=>__('Odpočet','cms_ve'),
            'description'=>__('Časový odpočet události nebo akce. Stačí zadat datum a čas vypršení a vybrat z několika vzhledů.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'setting',
                    'name'=>__('Nastavení','cms_ve'),
                    'setting'=>array(
                    
                        array(
                            'id'=>'content',
                            'title'=>__('Datum odpočtu','cms_ve'),
                            'type'=>'datetime',
                        ),
                        array(
                            'id'=>'redirect',
                            'title'=>__('Po skončení odpočtu přesměrovat na:','cms_ve'),
                            'type'=>'page_link',
                            'target'=>false,
                            'desc'=>__('Pokud nenastavíte žádnou URL, přesměrování se neprovede.','cms_ve'),
                        ),
                        
                   ), 
              ),
              array(
                  'id'=>'look',
                  'name'=>__('Vzhled odpočtu','cms_ve'),
                  'setting'=>array( 
                      array(
                            'id'=>'style',
                            'title'=>__('Vzhled odpočtu','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/countdown1'.$image_lang.'.png',
                                '2' => VS_DIR.'images/image_select/countdown2'.$image_lang.'.png',
                                '3' => VS_DIR.'images/image_select/countdown3'.$image_lang.'.png',
                                '4' => VS_DIR.'images/image_select/countdown4'.$image_lang.'.png',
                                '5' => VS_DIR.'images/image_select/countdown5'.$image_lang.'.png',
                                '6' => VS_DIR.'images/image_select/countdown6'.$image_lang.'.png',
                                '7' => VS_DIR.'images/image_select/countdown7'.$image_lang.'.png'
                            ),
                            'show'=>'countdown_style'
                      ),
                      array(
                            'id'=>'text_before',
                            'title'=>__('Text na začátku','cms_ve'),
                            'type'=>'text',
                            'content'=>__('Akce končí za','cms_ve'),
                            'show_group' => 'countdown_style',
                            'show_val' => '7', 
                      ),
                      array(
                            'id'=>'background-color',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'color',
                            'desc'=>__('Barva pozadí změní defaultní pozadí číslic.','cms_ve'),
                            'show_group' => 'countdown_style',
                            'show_val' => '1,2,3,4,6', 
                      ),
                      array(
                            'id'=>'font',
                            'title'=>__('Font čísel','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'40',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            ),
                            'desc'=>__('Velikost písma ovlivňuje i velikost celého elementu.','cms_ve'),
                      ),
                      array(
                            'id'=>'font-text',
                            'title'=>__('Font popisků','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'color'=>'',
                            ),
                            'desc'=>__('Toto nastavení ovlivní barvu textu pod čísly.','cms_ve'),
                      ),      
                  ),
              )
          )
      ),
      'features'=>array(
            'name'=>__('Vlastnosti','cms_ve'),
            'description'=>__('Popisek s ikonou, který lze formátovat do více sloupců a který je vhodný například jako výpis vlastností produktu.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'features',
                    'name'=>__('Vlastnosti','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'features',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat vlastnost','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'icon',
                                    'title'=>__('Ikona','cms_ve'),
                                    'type'=>'iconselect',
                                    'content'=>array(
                                        'icon'=>'glass',
                                        'size'=>'30',
                                        'color'=>'#111111',
                                    ),
                                ),
                                array(
                                    'id'=>'title',
                                    'title'=>__('Nadpis','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'text',
                                    'title'=>__('Text','cms_ve'),
                                    'type'=>'textarea',
                                ),
                                array(
                                    'id'=>'button_text',
                                    'title'=>__('Text tlačítka (pokud je zobrazeno)','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Více informací','cms_ve').' →',
                                ),
                                array(
                                    'id'=>'link',
                                    'title'=>__('Odkaz','cms_ve'),
                                    'type'=>'page_link',
                                ),
                            ),
                        ),  
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(  
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_ve'),
                            'type'=>'select',
                            'content'=>'three',
                            'options' => array(
                                array('name' => '1', 'value' => 'one'),
                                array('name' => '2', 'value' => 'two'),
                                array('name' => '3', 'value' => 'three'),
                                array('name' => '4', 'value' => 'four'),
                                array('name' => '5', 'value' => 'five'),
                            ),
                        ),                      
                        array(
                            'id'=>'style',
                            'title'=>__('Styl','cms_ve'),                        
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/feature1.png',
                                '2' => VS_DIR.'images/image_select/feature2.png',
                                '3' => VS_DIR.'images/image_select/feature3.png',
                                '4' => VS_DIR.'images/image_select/feature4.png',
                                '5' => VS_DIR.'images/image_select/feature5.png',
                            ),
                        ),
                        array(
                            'id'=>'background-color',
                            'title'=>__('Pozadí ikony (pokud ji vybraný styl podporuje)','cms_ve'),
                            'type'=>'color',
                            'content'=>'#209ccf'
                        ),   
                        array(
                            'id'=>'font',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'18',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font_text',
                            'title'=>__('Font textu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),    
                        array(
                            'id'=>'show_button',
                            'title'=>__('Zobrazení tlačítka','cms_ve'),
                            'label'=>__('Zobrazit pod vlastnostmi tlačítko','cms_ve'),
                            'type'=>'checkbox',
                            'show'=>'feature_button',
                        ), 
                        array(
                            'id'=>'button',
                            'title'=>__('Styl tlačítka','cms_ve'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'2',                       
                                'font'=>array(
                                    'font-size'=>'13',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#737373',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'#ebebeb',
                                ),
                                'border-color'=>'#bdbdbd',
                                'corner'=>'90',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            ),
                            'show_group' => 'feature_button',
                        ),    
                    ),
                ),               
            ),
      ),  
      'testimonials'=>array(
            'name'=>__('Reference','cms_ve'),
            'description'=>__('Výpis textových referencí, které lze formátovat do více sloupců.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'testimonials',
                    'name'=>__('Reference','cms_ve'),
                    'setting'=>array(                         
                        array(
                            'id'=>'testimonials',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat referenci','cms_ve'),
                            ),
                            'setting'=>array(   
                                array(
                                    'id'=>'text',
                                    'title'=>__('Text reference','cms_ve'),
                                    'type'=>'textarea',
                                ),                          
                                array(
                                    'id'=>'name',
                                    'title'=>__('Jméno','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'company',
                                    'title'=>__('Firma/Pozice','cms_ve'),
                                    'type'=>'text',
                                ),
                                
                                array(
                                    'id'=>'image',
                                    'title'=>__('Fotografie','cms_ve'),
                                    'type'=>'image',
                                ),
                            ),
                        ), 
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled referencí','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_ve'),
                            'type'=>'select',
                            'content'=>'one',
                            'options' => array(
                                array('name' => '1', 'value' => 'one'),
                                array('name' => '2', 'value' => 'two'),
                                array('name' => '3', 'value' => 'three'),
                                array('name' => '4', 'value' => 'four'),
                            ),
                        ),                        
                        array(
                            'id'=>'style',
                            'title'=>__('Styl referencí','cms_ve'),                        
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '7' => VS_DIR.'images/image_select/testimonial7.png',
                                '6' => VS_DIR.'images/image_select/testimonial6.png',
                                '1' => VS_DIR.'images/image_select/testimonial1.png',
                                '2' => VS_DIR.'images/image_select/testimonial2.png',
                                '3' => VS_DIR.'images/image_select/testimonial3.png',
                                '4' => VS_DIR.'images/image_select/testimonial4.png',
                                '5' => VS_DIR.'images/image_select/testimonial5.png',
                            ),
                        ),
                        array(
                            'id'=>'font',
                            'title'=>__('Font reference','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'15',
                                'font-family'=>'',
                                'line-height'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font-author',
                            'title'=>__('Font autora','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),   
                    ),
                ),   
                
                array(
                  'id' => 'slider',
                  'name' => __('Slider', 'cms_ve'),
                  'setting' => array(
                      array(
                          'id' => 'use_slider',
                          'title' => '',
                          'type' => 'checkbox',
                          'label' => __('Zobrazit jako slider', 'cms_ve'),
                          'show' => 'sliderset',
                      ),
                      array(
                          'id' => 'sliderset_group',
                          'type' => 'group',
                          'setting' => array(
                              array(
                                  'id' => 'animation',
                                  'title' => __('Typ animace', 'cms_ve'),
                                  'type' => 'select',
                                  'content' => 'fade',
                                  'options' => array(
                                    array('name' => __('Prolínání','cms_ve'), 'value' => 'fade'),
                                    array('name' => __('Zprava doleva','cms_ve'), 'value' => 'slide'),
                                  ),
                              ),
                              array(
                                  'id' => 'delay',
                                  'title' => __('Zpoždění slidů', 'cms_ve'),
                                  'type' => 'size',
                                  'unit' => 'ms',
                                  'content' => '3500',
                              ),
                              array(
                                  'id' => 'speed',
                                  'title' => __('Délka animace', 'cms_ve'),
                                  'type' => 'size',
                                  'unit' => 'ms',
                                  'content' => '1000',
                              ),
                              array(
                                  'id' => 'off_autoplay',
                                  'title' => __('Autoplay', 'cms_ve'),
                                  'type' => 'checkbox',
                                  'label' => __('Vypnout autoplay', 'cms_ve'),
                              ),
                              array(
                                  'id' => 'color_scheme',
                                  'title' => __('Barva ovládacích prvků', 'cms_ve'),
                                  'type' => 'select',
                                  'content' => '',
                                  'options' => array(
                                    array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
                                    array('name' => __('Tmavé','cms_ve'), 'value' => ''),
                                  ),
                              ),
          
                          ),
                          'show_group' => 'sliderset',
                      ),
          
                  ),
              ),            
            ),
      ),  
      'numbers'=>array(
            'name'=>__('Čísla','cms_ve'),
            'description'=>__('Chcete se pochlubit nějakými čísly? Například kolikrát si někdo stáhl váš ebook nebo kolik má stran? Použijte tento element.','cms_ve'),
            'tab_setting'=>array(  
                array(
                    'id'=>'form',
                    'name'=>__('Čísla','cms_ve'),
                    'setting'=>array(                               
                        array(
                            'id'=>'numbers',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat číslo','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'type',
                                    'title'=>'',
                                    'type'=>'radio',
                                    'content'=>'custom',
                                    'options' => array(
                                        'custom' => __('Zadat číslo','cms_ve'),
                                        'load' => __('Načíst číslo','cms_ve'),                                
                                    ),
                                    'show' => 'number',
                                ),
                                array(
                                    'id'=>'se',
                                    'title'=>__('Zobrazit počet kontaktů v seznamu','cms_ve'),
                                    'type'=>'list_select',
                                    'show_group' => 'number',
                                    'show_val' => 'load', 
                                ),
                                array(
                                    'id'=>'number',
                                    'title'=>__('Číslo','cms_ve'),
                                    'type'=>'text',
                                    'show_group' => 'number',
                                    'show_val' => 'custom', 
                                ),
                                array(
                                    'id'=>'unit',
                                    'title'=>__('Jednotka (zobrazí se za číslem)','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'title',
                                    'title'=>__('Text pod číslem','cms_ve'),
                                    'type'=>'text',
                                    'multielement_title'=>1
                                ),
                            ),
                        ),
                    ),
                ), 
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/number1.png',
                                '2' => VS_DIR.'images/image_select/number2.png',
                            ),
                        ), 
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_ve'),
                            'type'=>'select',
                            'content'=>'four',
                            'options' => array(
                                array('name' => '1', 'value' => 'one'),
                                array('name' => '2', 'value' => 'two'),
                                array('name' => '3', 'value' => 'three'),
                                array('name' => '4', 'value' => 'four'),
                                array('name' => '5', 'value' => 'five'),
                            ),
                        ),  
                        array(
                            'id'=>'number_font',
                            'title'=>__('Font čísla','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'40',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'text_font',
                            'title'=>__('Font textu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'15',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),                           
                    ),
                ),

            ),
            
      ),
        'faq'=>array(
            'name'=>__('FAQ','cms_ve'),
            'description'=>__('Časté dotazy. Tímto je přidáte.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'form',
                    'name'=>__('FAQ','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'faqs',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat otázku','cms_ve'),
                            ),
                            'setting'=>array(
                                array(
                                    'id'=>'question',
                                    'title'=>__('Otázka','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'answer',
                                    'title'=>__('Odpověď','cms_ve'),
                                    'type'=>'textarea',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_ve'),
                            'type'=>'select',
                            'content'=>'one',
                            'options' => array(
                                array('name' => '1', 'value' => 'one'),
                                array('name' => '2', 'value' => 'two'),
                                array('name' => '3', 'value' => 'three'),
                                array('name' => '4', 'value' => 'four'),
                                array('name' => '5', 'value' => 'five'),
                            ),
                        ),
                        array(
                            'id'=>'clickable',
                            'title'=>__('Zobrazení odpovědi', 'cms_ve'),
                            'type'=>'checkbox',
                            'label'=>__('Skrýt odpověď a zobrazit ji až po rozkliknutí otázky', 'cms_ve'),
                        ),
                        array(
                            'id'=>'style',
                            'title'=>__('Vzhled','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/faq1.png',
                                '2' => VS_DIR.'images/image_select/faq2.png',
                                '3' => VS_DIR.'images/image_select/faq3.png',
                            ),
                            'show'=>'faq_style',
                        ),
                        array(
                            'id'=>'background-color',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'color',
                            'content'=>'#efefef',
                            'show_group' => 'faq_style',
                            'show_val' => '2',
                        ),
                        array(
                            'id'=>'question_font',
                            'title'=>__('Font otázky','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'20',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'answer_font',
                            'title'=>__('Font odpovědi','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),  
                    ),
                ),
            ),
        ),
      'peoples'=>array(
            'name'=>__('Autor/Lidé','cms_ve'),
            'description'=>__('Pro výpis informací jedné nebo více osob. Výpis lze formátovat do více sloupců. Tento element je vhodný například jako info o autorovi knihy nebo jako přehled lidí ve firmě.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'peoples',
                    'name'=>__('Autor/Lidé','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'peoples',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat osobu','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'title',
                                    'title'=>__('Jméno','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'position',
                                    'title'=>__('Pozice','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'text',
                                    'title'=>__('Popis','cms_ve'),
                                    'type'=>'textarea',
                                ),
                                array(
                                    'id'=>'image',
                                    'title'=>__('Fotografie','cms_ve'),
                                    'type'=>'image',
                                ),
                                array(
                                    'id'=>'link',
                                    'title'=>__('Odkaz','cms_ve'),
                                    'type'=>'page_link',
                                ),
                            ),
                        ),  
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_ve'),
                            'type'=>'select',
                            'content'=>'four',
                            'options' => array(
                                array('name' => '1', 'value' => 'one'),
                                array('name' => '2', 'value' => 'two'),
                                array('name' => '3', 'value' => 'three'),
                                array('name' => '4', 'value' => 'four'),
                                array('name' => '5', 'value' => 'five'),
                            ),
                        ),                        
                        array(
                            'id'=>'style',
                            'title'=>__('Styl','cms_ve'),                        
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/peoples1.png',
                                '2' => VS_DIR.'images/image_select/peoples2.png',
                                '3' => VS_DIR.'images/image_select/peoples3.png',
                                '5' => VS_DIR.'images/image_select/peoples5.png',
                                '4' => VS_DIR.'images/image_select/peoples4.png',
                            ),
                        ),
                        array(
                            'id'=>'font',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'22',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font_position',
                            'title'=>__('Font pozice','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'13',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'#888',
                            )
                        ), 
                        array(
                            'id'=>'font_text',
                            'title'=>__('Font popisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ),     
                    ),
                ),          
            ),
      ),    
      'pricelist'=>array(
            'name'=>__('Ceník','cms_ve'),
            'description'=>__('Vícesloupcový ceník pro výpis různých cenových variant produktu nebo služby.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'pricelist',
                    'name'=>__('Ceník','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'title' => __('Typ ceníku','cms_ve'),
                            'id' => 'pricelist_type',
                            'type' => 'radio',
                            'show'=>'pricelist_type',
                            'options' => array(
                                'cols'=>__('Sloupcový ceník', 'cms_ve'),
                                'rows'=>__('Řádkový ceník', 'cms_ve'),
                            ), 
                            'content' => 'cols',
                        ),
                        array(
                            'id'=>'pricelist',
                            'type'=>'multielement',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'cols',
                            'texts'=>array(
                                'add'=>__('Přidat sloupec ceníku','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'title',
                                    'title'=>__('Název položky','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'price',
                                    'title'=>__('Cena','cms_ve'),
                                    'type'=>'text',
                                    'content'=>'0.00 Kč'
                                ),
                                array(
                                    'id'=>'sale_price',
                                    'title'=>__('Cena před slevou','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'per',
                                    'title'=>__('Časové období','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'features',
                                    'title'=>__('Vlastnosti','cms_ve'),
                                    'type'=>'simple_feature',
                                ),
                                array(
                                    'id'=>'button_text',
                                    'title'=>__('Text tlačítka','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Objednat','cms_ve'),
                                ), 
                                array(
                                    'id'=>'link',
                                    'title'=>__('Odkaz tlačítka','cms_ve'),
                                    'type'=>'page_link',
                                ),
                                array(
                                    'id'=>'text',
                                    'title'=>__('Popis','cms_ve'),
                                    'type'=>'textarea',
                                ),
                                array(
                                    'id'=>'popular',
                                    'title'=>__('Nejoblíbenější','cms_ve'),
                                    'type'=>'checkbox',
                                    'label'=>__('Označit jako nejoblíbenější','cms_ve'),
                                    'show'=>'popular',
                                ),
                                array(
                                    'id'=>'popular_text',
                                    'title'=>__('Popisek zvýrazněné položky','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('NEJPRODÁVANĚJŠÍ','cms_ve'),
                                    'show_group' => 'popular', 
                                ),                              
                            ),
                        ),  
                        array(
                            'id'=>'row_pricelist',
                            'type'=>'multielement',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'rows',
                            'texts'=>array(
                                'add'=>__('Přidat řádek ceníku','cms_ve'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'title',
                                    'title'=>__('Název položky','cms_ve'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'text',
                                    'title'=>__('Popis','cms_ve'),
                                    'type'=>'textarea',
                                ), 
                                array(
                                    'id'=>'price',
                                    'title'=>__('Cena','cms_ve'),
                                    'type'=>'text',
                                    'content'=>'0.00 Kč'
                                ),                         
                            ),
                        ), 
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled ceníku','cms_ve'),
                    'setting'=>array(                       
                        array(
                            'id'=>'style',
                            'title'=>__('Styl','cms_ve'),                        
                            'type'=>'imageselect',
                            'content'=>'1',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'cols',
                            'show'=>'pricelist_style',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/pricelist1'.$image_lang.'.png',
                                '2' => VS_DIR.'images/image_select/pricelist2'.$image_lang.'.png',
                                '3' => VS_DIR.'images/image_select/pricelist3'.$image_lang.'.png',
                                //'4' => VS_DIR.'images/image_select/pricelist4.png',
                            ),
                        ),
                        array(
                            'id' => 'row_table_style',
                            'title' => __('Styl', 'cms_ve'),
                            'type' => 'imageselect',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'rows',
                            'content' => '3',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/table1.png',
                                '2' => VS_DIR.'images/image_select/table2.png',
                                '3' => VS_DIR.'images/image_select/table3.png',
                            ),
                        ),
                        array(
                            'id'=>'col_group',
                            'type'=>'group',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'cols',
                            'setting'=>array(  
                                array(
                                    'id'=>'popular_color',
                                    'title'=>__('Barva nejoblíbenější položky','cms_ve'),
                                    'type'=>'color',
                                    'content'=>'#158ebf'
                                ),
                            )
                        ),
                        array(
                            'id'=>'background_color',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'color',
                            'content'=>'#333333',
                            'show_group'=>'pricelist_style',
                            'show_val'=>'3'
                        ),
                        array(
                            'id'=>'text_color',
                            'title'=>__('Základní barva textů','cms_ve'),
                            'type'=>'color',
                            'content'=>'',
                            'desc'=>__('Pokud je barva pozadí tmavá, je potřeba nastavit barvu textu na světlou a naopak, tak aby byl text čitelný.','cms_ve'),
                            'show_group'=>'pricelist_style',
                            'show_val'=>'3,4'
                        ),   
                        array(
                            'id'=>'button',
                            'title'=>__('Styl tlačítka','cms_ve'),
                            'type'=>'button',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'cols',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'25',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#2b2b2b',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ffde21',
                                    'color2'=>'#ffcc00',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border-color'=>'',
                                'corner'=>'0',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            )
                        ),                      
                        array(
                            'id'=>'font_title',
                            'title'=>__('Font nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'line-height'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'font',
                            'title'=>__('Font ceny','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'line-height'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font_features',
                            'title'=>__('Font vlastností','cms_ve'),
                            'type'=>'font',
                            'show_group'=>'pricelist_type',
                            'show_val'=>'cols',
                            'content'=>array(
                                'font-size'=>'',
                                'line-height'=>'',
                            )
                        ),
                        array(
                            'id'=>'font_description',
                            'title'=>__('Font popisku','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'line-height'=>'',
                            )
                        ),                                                                        
                    ),
                ),               
            ),
      ),   
      'progressbar'=>array(
            'name'=>__('Progress bar','cms_ve'),
            'description'=>__('Grafické znázornění procentuálního postupu. Vhodné jako ukazatel, kolik procent je už splněno, nebo jako znázornění úrovně znalostí na osobním webu.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'progressbar',
                    'name'=>__('Progress bar','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'percent',
                            'title'=>__('Procento','cms_ve'),
                            'type'=>'size',
                            'content'=>'50',
                            'unit'=>'%'
                        ),  
                        array(
                            'id'=>'text',
                            'title'=>__('Text','cms_ve'),
                            'type'=>'text',
                        ),  
                    ),
                ),
                array(
                    'id'=>'style',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(                       
                        array(
                            'id'=>'style',
                            'title'=>__('Styl','cms_ve'),                        
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/progressbar1.png',
                                '2' => VS_DIR.'images/image_select/progressbar2.png',
                                '3' => VS_DIR.'images/image_select/progressbar3.png',
                                '4' => VS_DIR.'images/image_select/progressbar4.png',
                                '5' => VS_DIR.'images/image_select/progressbar5.png',
                                '6' => VS_DIR.'images/image_select/progressbar6'.$image_lang.'.png',
                            ),
                        ),                        
                        array(
                            'id'=>'color1',
                            'title'=>__('Barva progressbaru','cms_ve'),
                            'type'=>'color',
                            'content'=>'#158dbf'
                        ), 
                        array(
                            'id'=>'color2',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'color',
                            'content'=>'#eeeeee'
                        ),
                        array(
                            'id'=>'font',
                            'title'=>__('Font','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'rounded',
                            'title'=>__('Kulaté rohy','cms_ve'),
                            'type'=>'checkbox',
                            'label'=>__('Zakulatit rohy','cms_ve'),
                        ),   
                    ),
                ),               
            ),
      ),    
      'fapi'=>array(
            'name'=>__('Prodejní FAPI formulář','cms_ve'),
            'description'=>__('Vložte na své stránky jednoduše prodejní FAPI formulář, který obstará vše kolem prodeje vašich produktů a služeb.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'fapi',
                    'name'=>__('FAPI formulář','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'content',
                            'title'=>__('Zvolte prodejní formulář, který chcete zobrazit.','cms_ve'),
                            'type'=>'fapi_form_select'
                        ),
                    ),
                ),
                array(
                    'id'=>'setting',
                    'name'=>__('Vzhled formuláře','cms_ve'),
                    'setting'=>array( 
                        array(
                            'id'=>'form-style',
                            'title'=>__('Styl formuláře','cms_ve'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/fapiform1.png',
                                '2' => VS_DIR.'images/image_select/fapiform2.png',
                                '3' => VS_DIR.'images/image_select/fapiform3.png',
                                '4' => VS_DIR.'images/image_select/fapiform4.png',
                            ),
                        ),
                        array(
                            'id'=>'background-color',
                            'title'=>__('Barva pozadí formuláře (pokud ji vybraný styl podporuje)','cms_ve'),
                            'type'=>'color',
                            'content'=>'#ffffff',
                            'desc'=>__('Pokud změníte barvu pozadí, nezapomeňte nastavit také barvu textů níže, tak aby byly na pozadí čitelné.','cms_ve'),
                        ),
                        array(
                            'id'=>'font_title',
                            'title'=>__('Font nadpisů','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'19',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font_text',
                            'title'=>__('Font textů','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'14',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'button',
                            'title'=>__('Styl tlačítka','cms_ve'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'24',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#2b2b2b',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ffde21',
                                    'color2'=>'#ffcc00',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border-color'=>'',
                                'corner'=>'0',
                                'hover_effect'=>'lighter',
                                'size'=>'1',
                            )
                        ),
                    )
                )
            )
      ),
      'link'=>array(
            'name' => __('Odkaz', 'cms_ve'),
            'description' => __('Vytvořte odkaz na stránku nebo na pop-up.', 'cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Odkaz','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'content',
                            'title'=>__('Text odkazu','cms_ve'),
                            'type'=>'text',
                            'content'=>__('Text odkazu','cms_ve')
                        ),
                        array(
                            'title' => __('Po kliknutí na odkaz','cms_ve'),
                            'id' => 'show',
                            'type' => 'radio',
                            'show'=>'linkaction',
                            'options' => array(
                                'url'=>__('Otevřít stránku', 'cms_ve'),
                                'popup'=>__('Zobrazit pop-up', 'cms_ve'),
                            ), 
                            'content' => 'url',
                        ),
                        array(
                            'id'=>'link',
                            'title'=>__('Odkazovat na','cms_ve'),
                            'type'=>'page_link',
                            'content'=>array(
                                ''=>'http://',
                            ),
                            'show_group' => 'linkaction', 
                            'show_val' => 'url',
                        ),
                        array(
                            'title' => __('Zobrazit pop-up','cms_ve'),
                            'id' => 'popup',
                            'type' => 'popupselect',
                            'show_group' => 'linkaction', 
                            'show_val' => 'popup',  
                        ), 
                        array(
                            'id'=>'align',
                            'title'=>__('Zarovnání odkazu','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                'left' => __('Nalevo','cms_ve'),
                                'center' => __('Doprostřed','cms_ve'),
                                'right' => __('Napravo','cms_ve'),
                            ),
                            'content' => 'center',
                        ),
                    )
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(                        
                        array(
                            'id'=>'font',
                            'title'=>__('Font odkazu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'line-height'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),
                    )
                ),
            )
      ),
      'table' => array(
            'name' => __('Tabulka', 'cms_ve'),
            'description' => __('Pro vykreslení jednoduchých dvousloupcových tabulek', 'cms_ve'),
            'tab_setting' => array(
                array(
                    'id' => 'items',
                    'name' => __('Tabulka', 'cms_ve'),
                    'setting' => array(
                        array(
                            'id' => 'lines',
                            'type' => 'multielement',
                            'texts' => array(
                                'add' => __('Přidat řádek tabulky', 'cms_ve'),
                            ),
                            'setting' => array(
                                array(
                                    'id' => 'title',
                                    'title' => __('První sloupec (nadpis)', 'cms_ve'),
                                    'type' => 'text',
                                ),    
                                array(
                                    'id' => 'text',
                                    'title' => __('Druhý sloupec', 'cms_ve'),
                                    'type' => 'textarea',
                                ),
                                
                            ),
                        ),   
                    ),
                ),
                array(
                    'id' => 'style',
                    'name' => __('Vzhled', 'cms_ve'),
                    'setting' => array(
                        array(
                            'id' => 'width',
                            'title' => __('Šířka prvního sloupce', 'cms_ve'),
                            'type' => 'size',
                            'content'=>array(
                                'size'=>'20',
                                'unit'=>'%'
                            )
                        ),
                        array(
                            'id' => 'style',
                            'title' => __('Styl', 'cms_ve'),
                            'type' => 'imageselect',
                            'content' => '3',
                            'options' => array(
                                '1' => VS_DIR.'images/image_select/table1.png',
                                '2' => VS_DIR.'images/image_select/table2.png',
                                '3' => VS_DIR.'images/image_select/table3.png',
                            ),
                        ),
                        array(
                            'id' => 'font',
                            'title' => __('Barva textů', 'cms_ve'),
                            'type' => 'font',
                            'content'=>array(
                                'font-size'=>'',
                                'color'=>''
                            )
                        ),
                    ),
                ),
            ),
      ),
      'catalog' => array(
            'name' => __('Katalog', 'cms_ve'),
            'exclude' => array('slide'),
            'description' => __('Pomocí tohoto elementu můžete vytvářet katalogové výpisy nebo vypsat seznam stránek a odkazovat na ně.', 'cms_ve'),
            'tab_setting' => array(
                array(
                    'id' => 'items',
                    'name' => __('Položky katalogu', 'cms_ve'),
                    'setting' => array(
                        array(
                            'title' => __('Typy položek','cms_ve'),
                            'id' => 'item_type',
                            'type' => 'radio',
                            'show'=>'type',
                            'options' => array(
                                'own'=>__('Vlastní položky', 'cms_ve'),
                                'subpage'=>__('Položky jako podstránky', 'cms_ve'),
                            ),
                            'content' => 'own',
                        ),
                        array(
                            'id' => 'items',
                            'type' => 'multielement',
                            'texts' => array(
                                'add' => __('Přidat položku', 'cms_ve'),
                            ),
                            'setting' => array(
                                array(
                                    'id' => 'title',
                                    'title' => __('Název', 'cms_ve'),
                                    'type' => 'text',
                                ),
                                array(
                                    'id' => 'image',
                                    'title' => __('Obrázek', 'cms_ve'),
                                    'type' => 'image',
                                ),
                                array(
                                    'id' => 'subtitle',
                                    'title' => __('Podnázev', 'cms_ve'),
                                    'type' => 'text',
                                ),
                                array(
                                    'id' => 'description',
                                    'title' => __('Popisek', 'cms_ve'),
                                    'type' => 'textarea',
                                ),
                                array(
                                    'id' => 'price',
                                    'title' => __('Cena', 'cms_ve'),
                                    'type' => 'text',
                                ),
                                array(
                                    'id' => 'link',
                                    'title' => __('Odkaz', 'cms_ve'),
                                    'type' => 'page_link',
                                ),
                            ),
                            'show_group' => 'type',
                            'show_val' => 'own'
                        ),
                        array(
                            'type'=>'info',
                            'show_group' => 'type',
                            'show_val' => 'subpage',
                            'content'=>__('Náhledový obrázek a název položky se načte z nastavení stránky.','cms_ve'),
                        ),
                        array(
                            'id'=>'page',
                            'title'=>__('Vypsat podstránky','cms_ve'),
                            'type'=>'selectpage',
                            'show_group' => 'type',
                            'show_val' => 'subpage',
                            'desc'=>__('Pokud nezvolíte žádnou stránku, vypíšou se podstránky této stránky.','cms_ve'),
                        ),
                        
                    ),
                ),
                array(
                    'id' => 'style',
                    'name' => __('Vzhled', 'cms_ve'),
                    'setting' => array(
                        array(
                            'id' => 'cols',
                            'title' => __('Počet sloupců', 'cms_ve'),
                            'type' => 'select',
                            'content' => 3,
                            'options' => array(
                                array('name' => '1', 'value' => 1),
                                array('name' => '2', 'value' => 2),
                                array('name' => '3', 'value' => 3),
                                array('name' => '4', 'value' => 4),
                                array('name' => '5', 'value' => 5),
                            ),
                        ),
                        array(
                            'id'=>'cols_type',
                            'title'=>__('Mezery mezi obrázky','cms_ve'),
                            'type'=>'select',
                            'content'=> '',
                            'options' => array(
                                array('name' => __('Velké','cms_ve'), 'value' => ''),
                                array('name' => __('Malé','cms_ve'), 'value' => 's'),
                                array('name' => __('Žádné','cms_ve'), 'value' => 'full'),
                            ),
                        ),
                        array(
                            'id'=>'hover',
                            'title'=>__('Efekt po najetí myši','cms_ve'),
                            'type'=>'select',
                            'content'=> 'zoom',
                            'options' => array(
                                array('name' => __('Žádný','cms_ve'), 'value' => ''),
                                array('name' => __('Zoom','cms_ve'), 'value' => 'zoom'),
                            ),
                            'show' => 'hover_efect',
                        ),
                        array(
                            'id' => 'style',
                            'title' => __('Styl', 'cms_ve'),
                            'type' => 'imageselect',
                            'content' => '3',
                            'options' => array(
                                '3' => VS_DIR.'images/image_select/gallery3.png',
                                '2' => VS_DIR.'images/image_select/gallery2.png',
                                '7' => VS_DIR.'images/image_select/catalog7.png',
                                '5' => VS_DIR.'images/image_select/gallery5.png',
                                '4' => VS_DIR.'images/image_select/gallery4.png',
                                '1' => VS_DIR.'images/image_select/gallery1.png',
                                '6' => VS_DIR.'images/image_select/catalog6.png',
                            ),
                            'show'=>'style',
                        ),
                        array(
                            'id'=>'image_size',
                            'title'=>__('Velikost obrázku','cms_ve'),
                            'type'=>'select',
                            'content'=> '2',
                            'options' => array(
                                array('name' => '1/2', 'value' => '2'),
                                array('name' => '1/3', 'value' => '3'),
                                array('name' => '1/4', 'value' => '4'),
                            ),
                            'show_group' => 'style',
                            'show_val' => '6'
                        ),
                        array(
                            'id'=>'hover_color',
                            'title'=>__('Barva podbarvení po najetí myši','cms_ve'),
                            'type'=>'color',
                            'content'=> '#179edc',
                            'show_group' => 'style',
                            'show_val' => '1'
                        ),
                        array(
                            'id' => 'text_align',
                            'title' => __('Zarovnání textů', 'cms_ve'),
                            'type'=>'select',
                            'content'=> 'fade',
                            'options' => array(
                                array('name' => __('Nalevo', 'cms_ve'), 'value' => 'left'),
                                array('name' => __('Na střed', 'cms_ve'), 'value' => 'center'),
                            ),
                        ),
                        array(
                            'id' => 'font_title',
                            'title' => __('Velikost názvu', 'cms_ve'),
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '',
                            ),
                            'show_group' => 'style',
                            'show_val' => '2,3,4,5,6',
                        ),
                        array(
                            'id' => 'font_description',
                            'title' => __('Velikost popisu', 'cms_ve'),
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '',
                            ),
                            'show_group' => 'style',
                            'show_val' => '3,4,6',
                        ),
                        array(
                            'id' => 'font_color',
                            'title' => __('Barva textů', 'cms_ve'),
                            'type' => 'color',
                            'show_group' => 'style',
                            'show_val' => '3,6',
                        ),
                        array(
                            'id' => 'font_price',
                            'title' => __('Font ceny', 'cms_ve'),
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '',
                                'color' => '',
                            ),
                            'show_group' => 'style',
                            'show_val' => '3,4',
                        ),
                    ),
                ),
                array(
                  'id' => 'slider',
                  'name' => __( 'Slider', 'cms_ve' ),
                  'setting' => array(
                      array(
                          'id' => 'use_slider',
                          'title' => '',
                          'type' => 'checkbox',
                          'label' => __('Zobrazit jako slider','cms_ve'),
                          'show'=>'sliderset',
                      ),
                      array(
                            'id'=>'sliderset_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'animation',
                                    'title'=>__('Typ animace','cms_ve'),
                                    'type'=>'select',
                                    'content'=> 'fade',
                                    'options' => array(
                                        array('name' => __('Prolínání','cms_ve'), 'value' => 'fade'),
                                        array('name' => __('Zprava doleva','cms_ve'), 'value' => 'slide'),
                                    ),
                                ),                      
                                array(
                                    'id' => 'delay',
                                    'title' => __('Zpoždění slidů','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '3500',
                                ),
                                array(
                                    'id' => 'speed',
                                    'title' => __('Délka animace','cms_ve'),
                                    'type' => 'size',
                                    'unit' => 'ms',
                                    'content'=> '1000',
                                ),
                                array(
                                    'id' => 'off_autoplay',
                                    'title' => __('Autoplay','cms_ve'),
                                    'type' => 'checkbox',
                                    'label' => __('Vypnout autoplay','cms_ve'),
                                ),
                                array(
                                    'id'=>'color_scheme',
                                    'title'=>__('Barva ovládacích prvků','cms_ve'),
                                    'type'=>'select',
                                    'content'=> '',
                                    'options' => array(
                                        array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
                                        array('name' => __('Tmavé','cms_ve'), 'value' => ''),
                                    ),
                                ),
                            
                            ),
                            'show_group' => 'sliderset',
                      ),
                      
                  ),
              ),
            ),
      ),
      'event_calendar' => array(
            'name' => __('Seznam akcí', 'cms_ve'),
            'exclude' => array('slide'),
            'description' => __('Pomocí tohoto elementu můžete vytvářet výpis chystaných akcí.', 'cms_ve'),
            'tab_setting' => array(
                array(
                    'id' => 'items',
                    'name' => __('Kalendář akcí', 'cms_ve'),
                    'setting' => array(
                        array(
                            'id'=>'page',
                            'title'=>__('Seznam akcí','cms_ve'),
                            'type'=>'events_list',
                            //'show_group' => 'type',
                            //'show_val' => 'events',
                        ),
                        
                    ),
                ),
                array(
                    'id' => 'setting',
                    'name' => __('Nastavení', 'cms_ve'),
                    'setting' => array(
                        array(
                            'id'=>'show',
                            'title'=>__('Zobrazit','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                '>' => __('Jen budoucí akce','cms_ve'), 
                                '<' => __('Jen minulé akce','cms_ve'), 
                                '' => __('Budoucí i minulé akce','cms_ve'), 
                             ),  
                            'content'=> '>',
                        ),
                        array(
                            'id' => 'num',
                            'title' => __('Omezit počet položek ve výpisu na', 'cms_ve'),
                            'type' => 'number',
                            'unit' => __('akcí', 'cms_ve'),
                        ),
                    ),
                ),
                array(
                    'id' => 'style',
                    'name' => __('Vzhled', 'cms_ve'),
                    'setting' => array(
                        array(
                            'id' => 'cols',
                            'title' => __('Počet sloupců', 'cms_ve'),
                            'type' => 'select',
                            'content' => 3,
                            'options' => array(
                                array('name' => '1', 'value' => 1),
                                array('name' => '2', 'value' => 2),
                                array('name' => '3', 'value' => 3),
                                array('name' => '4', 'value' => 4),
                                array('name' => '5', 'value' => 5),
                            ),
                        ),
                        array(
                            'id'=>'cols_type',
                            'title'=>__('Mezery mezi obrázky','cms_ve'),
                            'type'=>'select',
                            'content'=> '',
                            'options' => array(
                                array('name' => __('Velké','cms_ve'), 'value' => ''),
                                array('name' => __('Malé','cms_ve'), 'value' => 's'),
                                array('name' => __('Žádné','cms_ve'), 'value' => 'full'),
                            ),
                        ),
                        array(
                            'id'=>'hover',
                            'title'=>__('Efekt po najetí myši','cms_ve'),
                            'type'=>'select',
                            'content'=> 'zoom',
                            'options' => array(
                                array('name' => __('Žádný','cms_ve'), 'value' => ''),
                                array('name' => __('Zoom','cms_ve'), 'value' => 'zoom'),
                            ),
                            'show' => 'hover_efect',
                        ),
                        array(
                            'id' => 'style',
                            'title' => __('Styl', 'cms_ve'),
                            'type' => 'imageselect',
                            'content' => '3',
                            'options' => array(
                                '3' => VS_DIR.'images/image_select/gallery3.png',
                                '2' => VS_DIR.'images/image_select/gallery2.png',
                                '7' => VS_DIR.'images/image_select/catalog7.png',
                                '5' => VS_DIR.'images/image_select/gallery5.png',
                                '4' => VS_DIR.'images/image_select/gallery4.png',
                                '6' => VS_DIR.'images/image_select/catalog6.png',
                            ),
                            'show'=>'style',
                        ),
                        array(
                            'id'=>'hide_image',
                            'title'=>__('Skrýt obrázek','cms_ve'),
                            'type'=>'checkbox',
                            'label' => __('Skrýt obrázky ve výpisu','cms_ve'),
                            'show_group' => 'style',
                            'show_val' => '3,7,4,6'
                        ),
                        array(
                            'id'=>'hide_description',
                            'title'=>__('Skrýt popis','cms_ve'),
                            'type'=>'checkbox',
                            'label' => __('Skrýt popis ve výpisu','cms_ve'),
                            'show_group' => 'style',
                            'show_val' => '3,7,4,6'
                        ),
                        array(
                            'id'=>'image_size',
                            'title'=>__('Velikost obrázku','cms_ve'),
                            'type'=>'select',
                            'content'=> '2',
                            'options' => array(
                                array('name' => '1/2', 'value' => '2'),
                                array('name' => '1/3', 'value' => '3'),
                                array('name' => '1/4', 'value' => '4'),
                            ),
                            'show_group' => 'style',
                            'show_val' => '6'
                        ),
                        array(
                            'id' => 'text_align',
                            'title' => __('Zarovnání textů', 'cms_ve'),
                            'type'=>'select',
                            'content'=> 'fade',
                            'options' => array(
                                array('name' => __('Nalevo','cms_ve'), 'value' => 'left'),
                                array('name' => __('Na střed','cms_ve'), 'value' => 'center'),
                            ),
                        ),
                        array(
                            'id' => 'font_title',
                            'title' => __('Velikost názvu', 'cms_ve'),
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '',
                            ),
                            'show_group' => 'style',
                            'show_val' => '2,3,4,5,6',
                        ),
                        array(
                            'id' => 'font_description',
                            'title' => __('Velikost popisu', 'cms_ve'),
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '',
                            ),
                            'show_group' => 'style',
                            'show_val' => '3,4,6',
                        ),
                        array(
                            'id' => 'font_color',
                            'title' => __('Barva textů', 'cms_ve'),
                            'type' => 'color',
                            'show_group' => 'style',
                            'show_val' => '3,6',
                        ),
                        array(
                            'id' => 'font_price',
                            'title' => __('Font ceny', 'cms_ve'),
                            'type' => 'font',
                            'content' => array(
                                'font-size' => '',
                                'color' => '',
                            ),
                            'show_group' => 'style',
                            'show_val' => '3,4',
                        ),
                    ),
                ),
            ),
      ),
      'google_map'=>array(
            'name'=>__('Google mapa','cms_ve'),
            'description'=>__('Vložte si na svou stránku mapu.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'map_setting',
                    'title'=>'',
                    'type'=>'google_map',
                    'content' => array('address'=>__('Praha', 'cms_ve')),
                ),
                array(
                    'id'=>'height',
                    'title'=>__('Výška mapy','cms_ve'),
                    'type'=>'size',
                    'content'=>'400',
                    'unit'=>'px',
                    'show_group' => 'google_map',
                ),
                array(
                    'id'=>'setting',
                    'title'=>__('Nastavení mapy','cms_ve'),
                    'type'=>'multiple_checkbox',
                    'options' => array(
                        array('name' => __('Povolit zoomování myší','cms_ve'), 'value' => 'scrollwheel'),
                    ),
                    'show_group' => 'google_map',
                ),
            )
            
      ),
      'html'=>array(
            'name'=>__('HTML','cms_ve'),
            'description'=>__('Pomocí tohoto elementu můžete na stránky vkládat vlastní HTML nebo Javascript kódy.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'content',
                    'title'=>__('HTML/Javascript','cms_ve'),
                    'type'=>'textarea',
                    'desc'=>__('Dávejte pozor na to, aby byl HTML kód validní. Neukončené tagy mohou narušit strukturu stránky a při uložení pak může docházet k chybám. Pokud kód obsahuje Javascript, může se stát, že se projeví až po znovunačtení stránky.','cms_ve'),
                ),
            )
            
      ),
),'basic');


$vePage->add_elements(array(
    'social_icons'=>array(
        'name'=>__('Sociální ikonky','cms_ve'),
        'description'=>__('Výpis ikonek sociálních sítí s odkazy na vaše profily.','cms_ve'),
        'tab_setting'=>array(
            array(
                'id' => 'list',
                'name' => __('Seznam sítí', 'cms_ve'),
                'setting' => array(
                    array(
                        'id'=>'socials',
                        'type'=>'multielement',
                        'texts'=>array(
                            'add'=>__('Přidat ikonku','cms_ve'),
                        ),
                        'setting'=>array(                             
                            array(
                                'id'=>'icon',
                                'title'=>__('Ikonka','cms_ve'),
                                'type'=>'simple_iconselect',
                                'content' => array(
                                    'icon'=>'facebook1',
                                ), 
                                'icons' => array( 
                                    'facebook1' => get_template_directory().'/modules/visualeditor/images/icons/',
                                    'youtube1' =>get_template_directory().'/modules/visualeditor/images/icons/',  
                                    'google-plus1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'linkedin1' =>get_template_directory().'/modules/visualeditor/images/icons/',    
                                    'twitter1' =>get_template_directory().'/modules/visualeditor/images/icons/',   
                                    'twitter2' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'youtube1' =>get_template_directory().'/modules/visualeditor/images/icons/',     
                                    'pinterest1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'instagram1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'vimeo1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'dribbble1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'behance1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'tumblr1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    'flickr1' =>get_template_directory().'/modules/visualeditor/images/icons/', 
                                    
                                ),                                                                          
                            ),
                            array(
                                'id'=>'link',
                                'title'=>__('Odkaz','cms_ve'),
                                'type'=>'text',                                                                      
                            ),
                        ),
                    ),
                ), 
            ),
            array(
                'id' => 'style',
                'name' => __('Vzhled', 'cms_ve'),
                'setting' => array(
                    array(
                        'id'=>'style',
                        'title'=>__('Vzhled','cms_ve'),
                        'type'=>'imageselect',
                        'content'=>'2',
                        'options' => array(
                            '2' => VS_DIR.'images/image_select/social_icons2.jpg',
                            '3' => VS_DIR.'images/image_select/social_icons3.jpg',
                            '1' => VS_DIR.'images/image_select/social_icons1.jpg',
                            '4' => VS_DIR.'images/image_select/social_icons4.jpg',
                        ),
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
                    array(
                          'id'=>'size',
                          'title'=>__('Velikost','cms_ve'),
                          'type'=>'slider',
                          'setting'=>array(
                              'min'=>'15',
                              'max'=>'40',
                              'unit'=>'px'
                          ),
                          'content'=>'20',
                    ), 
                    array(
                          'id'=>'space',
                          'title'=>__('Rozestup','cms_ve'),
                          'type'=>'slider',
                          'setting'=>array(
                              'min'=>'0',
                              'max'=>'40',
                              'unit'=>'px'
                          ),
                          'content'=>'15',
                    ),
                    array(
                          'id'=>'color',
                          'title'=>__('Barva','cms_ve'),
                          'type'=>'color',
                          'content'=>'#158ebf',
                    ), 
                    array(
                          'id'=>'hover_color',
                          'title'=>__('Barva po najetí myši','cms_ve'),
                          'type'=>'color',
                          'content'=>'',
                    ), 
                ), 
            ),
        )  
    ),
    'share'=>array(
            'name'=>__('Sociální tlačítka','cms_ve'),
            'description'=>__('Tento element vloží na stránku tlačítka pro sdílení na sociálních sítích. Můžete zobrazit Facebook, Twitter a Google+ tlačítko.','cms_ve'),
            'setting'=>array(                
                array(
                    'id'=>'show',
                    'title'=>__('Zobrazit','cms_ve'),
                    'type' => 'multiple_checkbox',
                    'options' => array(
                        array('name' => 'Facebook', 'value' => 'facebook'),
                        array('name' => 'Twitter', 'value' => 'twitter'),
                        array('name' => 'Google+', 'value' => 'google'),
                    ),
                    'content'=>array('facebook'=>'facebook','twitter'=>'twitter','google'=>'google')
                ),
                array(
                    'id'=>'scheme',
                    'title'=>__('Styl zobrazení','cms_ve'),
                    'type'=>'imageselect',
                    'content'=>'1',
                    'options' => array(
                        '1' => VS_DIR.'images/image_select/share1.png',
                        '2' => VS_DIR.'images/image_select/share2.png',
                        '3' => VS_DIR.'images/image_select/share3.png',
                    ),
                ), 
                array(
                    'id'=>'content',
                    'title'=>__('Sdílet stránku','cms_ve'),
                    'type'=>'page_link',
                    'desc'=>__('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.','cms_ve'),                    
                ),   
                             
            )
      ),
    'like'=>array(
            'name'=>__('Like button','cms_ve'),
            'description'=>__('Pro vložení facebookového tlačítka &quot;To se mi líbí&quot;, pomocí kterého lze stránku sdílet na Facebooku.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'content',
                    'title'=>__('Lajkovat stránku','cms_ve'),
                    'type'=>'page_link', 
                    'target'=>false,
                    'desc'=>__('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.','cms_ve'), 
                ),
                array(
                    'id'=>'layout',
                    'title'=>__('Vzhled','cms_ve'),
                    'type' => 'imageselect',
                    'options' => array(
                        'standard' => VS_DIR.'images/image_select/like1.png',
                        'button_count' => VS_DIR.'images/image_select/like2.png',
                        'box_count' => VS_DIR.'images/image_select/like3.png',
                        'button' => VS_DIR.'images/image_select/like4.png',                       
                    ),
                    'content'=>'button_count',                                    
                ),
                array(
                    'id'=>'scheme',
                    'title'=>__('Barevné schéma','cms_ve'),
                    'type'=>'select',
                    'options' => array(
                        array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
                        array('name' => __('Tmavé','cms_ve'), 'value' => 'dark'),
                    ),
                ),
                array(
                    'id'=>'setting',
                    'title'=>__('Nastavení','cms_ve'),
                    'type' => 'multiple_checkbox',
                    'options' => array(
                        array('name' => __('Zobrazit obrázky přátel','cms_ve'), 'value' => 'faces'),
                        array('name' => __('Zobrazit tlačítko „Sdílet“','cms_ve'), 'value' => 'share'),
                    ),
                ),
                array(
                    'id'=>'align',
                    'title'=>__('Zarovnání tlačítka','cms_ve'),
                    'type' => 'radio',
                    'options' => array(
                        'left' => __('Nalevo','cms_ve'),
                        'center' => __('Doprostřed','cms_ve'),
                    ),
                    'content' => 'center',
                ),   
                
            )
      ),
      'fac_share'=>array(
            'name'=>__('Sdílet na Facebooku', 'cms_ve'),
            'description'=>__('Facebookové tlačítko pro sdílení stránky na Facebooku. Můžete si vybrat z několika vzhledů tlačítka.','cms_ve'),
            'tab_setting'=>array(
                array(
                    'id'=>'setting',
                    'name'=>__('Nastavení','cms_ve'),
                    'setting'=>array(  
                        array(
                            'id'=>'content',
                            'title'=>__('Stránka, kterou chcete sdílet','cms_ve'),
                            'type'=>'page_link',
                            'target'=>false,
                            'desc'=>__('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.','cms_ve'),  
                        ),
                    )
                ),
                array(
                    'id'=>'appearance',
                    'name'=>__('Vzhled','cms_ve'),
                    'setting'=>array(  
                         array(
                            'id'=>'appearance',
                            'title'=>__('Vzhled tlačítka','cms_ve'),
                            'type'=>'radio',
                            'options' => array(
                                  'classic' => __('Klasické zobrazení','cms_ve'),
                                  'button' => __('Tlačítko','cms_ve'),
                                  'image' => __('Vlastní obrázek','cms_ve'),
                            ),
                            'content' => 'classic',
                            'show' => 'share_style',
                        ),  
                        array(
                            'id'=>'button_group',
                            'type'=>'group',
                            'setting'=>array(  
                                array(
                                    'id'=>'button_text',
                                    'title'=>__('Text tlačítka','cms_ve'),
                                    'type'=>'text',
                                    'content'=>__('Sdílet na Facebooku', 'cms_ve'),
                                ),    
                                array(
                                    'id'=>'button',
                                    'title'=>__('Vzhled tlačítka','cms_ve'),
                                    'type'=>'button',
                                    'options' => $vePage->list_buttons,
                                    'content'=>array( 
                                        'style'=>'1',                       
                                        'font'=>array(
                                            'font-size'=>'18',
                                            'font-family'=>'',
                                            'weight'=>'',
                                            'color'=>'#fff',
                                            'text-shadow'=>'',
                                        ),
                                        'background_color'=>array(
                                            'color1'=>'#3f5db1',
                                            'color2'=>'',
                                        ),
                                        'hover_color'=>array(
                                            'color1'=>'#3451a2',
                                            'color2'=>'',
                                        ),
                                        'icon'=>array(
                                            'color'=>'#fff',
                                            'size'=>'26',
                                            'icon'=>'facebook1',
                                            'icons'=>array( 
                                                'facebook1' => get_template_directory().'/modules/visualeditor/images/icons/',  
                                                'facebook2' => get_template_directory().'/modules/visualeditor/images/icons/',
                                                'facebook3' => get_template_directory().'/modules/visualeditor/images/icons/',   
                                            ),
                                        ),
                                        'border-color'=>'',
                                        'corner'=>'0',
                                        'hover_effect'=>'lighter',
                                        'size'=>'1',
                                    )
                                ),                                  
                            ),
                            'show_group' => 'share_style',
                            'show_val' => 'button', 
                        ),                
                        array(
                            'id'=>'layout',
                            'title'=>'',
                            'type' => 'imageselect',
                            'options' => array(
                                'button_count' => VS_DIR.'images/image_select/fac_share1.png',
                                'button' => VS_DIR.'images/image_select/fac_share2.png',
                                'box_count' => VS_DIR.'images/image_select/fac_share3.png',
                            ),
                            'content'=>'button_count',                                    
                            'show_group' => 'share_style',
                            'show_val' => 'classic', 
                        ),
                        array(
                            'id'=>'image',
                            'title'=>'',
                            'type'=>'upload', 
                            'show_group' => 'popup_type',
                            'show_val' => 'image', 
                            'show_group' => 'share_style',
                            'show_val' => 'image',     
                        ), 
                        
                        array(
                            'id'=>'align',
                            'title'=>__('Zarovnání tlačítka','cms_ve'),
                            'type' => 'radio',
                            'options' => array(
                                'left' => __('Nalevo','cms_ve'),
                                'center' => __('Doprostřed','cms_ve'),
                                'right' => __('Napravo','cms_ve'),
                            ),
                            'content' => 'center',
                        ), 
                    )
                )    
            )
      ),
      'likebox'=>array(
            'name'=>__('Page plugin','cms_ve'),
            'description'=>__('Page plugin zobrazí seznam příspěvků z vaší facebookovské stránky.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'content',
                    'title'=>__('URL vaší facebookové stránky','cms_ve'),
                    'type'=>'text',
                    'desc'=>__('Adresu zadejte včetně <code>https://</code>.','cms_ve'),
                ),
                array(
                    'id'=>'width',
                    'title'=>__('Šířka', 'cms_ve'),
                    'type'=>'text',
                    'content'=>'340',
                    'desc'=>__('Maximální šířka je 500px.','cms_ve'),
                ),
                array(
                    'id'=>'height',
                    'title'=>__('Výška','cms_ve'),
                    'type'=>'text',
                    'content'=>'500',
                    'desc'=>__('Minimální výška je 70px.','cms_ve'),
                ),
                array(
                    'id'=>'tabs',
                    'title'=>__('Zobrazit','cms_ve'),
                    'type' => 'multiple_checkbox',
                    'options' => array(
                            array('name' => __('Timeline','cms_ve'), 'value' => 'timeline'),                                       
                            array('name' => __('Události','cms_ve'), 'value' => 'events'),
                            array('name' => __('Zprávy','cms_ve'), 'value' => 'messages'),
                    ), 
                    'content' => array('timeline'),
                ),
                array(
                    'id'=>'setting',
                    'title'=>__('Nastavení','cms_ve'),
                    'type' => 'multiple_checkbox',
                    'options' => array(
                        array('name' => __('Skrýt úvodní fotku', 'cms_ve'), 'value' => 'cover'),
                        array('name' => __('Skrýt avatary přátel','cms_ve'), 'value' => 'faces'),
                        array('name' => __('Zobrazit tlačítka akcí', 'cms_ve'), 'value' => 'cta'),
                        array('name' => __('Zobrazit malou úvodní fotku', 'cms_ve'), 'value' => 'header'),
                    ),
                    'content' => array(),
                ),
                
            ),
      ),
      'fcomments'=>array(
            'name'=>__('Facebookové komentáře','cms_ve'),
            'description'=>__('Facebookové komentáře jsou vhodné jako nástroj virtuálního šíření stránky na Facebooku.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'content',
                    'title'=>__('URL komentované stránky','cms_ve'), 
                    'type'=>'page_link',
                    'target'=>false,
                    'desc'=>__('Pokud nevyberete stránku nebo nezadáte žádnou adresu, použije se adresa aktuální stránky.','cms_ve'), 
                ),
                array(
                    'id'=>'width',
                    'title'=>__('Šířka komentářů (v px)','cms_ve'),
                    'type'=>'text',
                    'content'=>'100%',
                ), 
                array(
                    'id'=>'per_page',
                    'title'=>__('Počet komentářů na stránku','cms_ve'),
                    'type'=>'text',
                    'content'=>'10',
                ), 
                array(
                    'id'=>'scheme',
                    'title'=>__('Barevné schéma','cms_ve'),
                    'type'=>'select',
                    'options' => array(
                        array('name' => __('Světlé','cms_ve'), 'value' => 'light'),
                        array('name' => __('Tmavé','cms_ve'), 'value' => 'dark'),
                    ),
                ), 
            ),
            
      ),
      'social_sprinters'=>array(
            'name'=>__('Social sprinters','cms_ve'),
            'description'=>__('Vložte na stránku vlastní facebook aplikaci od Social Sprinters.','cms_ve'),
            'setting'=>array(
                array(
                    'id'=>'code',
                    'title'=>__('Zkrácený odkaz sprinte.rs','cms_ve'), 
                    'type'=>'text',
                    'desc'=>__('Vložte zkrácený odkaz sprinte.rs, který najdete v přehledu vašich aplikací po přihlášení na SocialSprinters. Více informací o <a href="https://socialsprinters.com/?a_box=3cc6xhpn" target="_blank">Social Sprinters najdete zde</a>','cms_ve'),
                ),
            ),
            
      )
),'social');

$vePage->add_elements(array(
    'twocols'=>array(
          'name'=>__('Dva sloupce','cms_ve'),
          'subelements'=>1,
          'description'=>__('Rozdělí obsah na dva sloupce, do kterých lze vkládat další elementy.','cms_ve'),
          'setting'=>array(
          ),
    ),
    'box'=>array(
          'name'=>__('Blok','cms_ve'),
          'subelements'=>1,
          'description'=>__('Bloku lze nastavit vlastní formátování včetně barvy pozadí a fontu. Do bloku lze potom vkládat další elementy a vytvořit tak například formulář s jinak barevným pozadím, než je pozadí okolí.','cms_ve'),
          'tab_setting'=>array(
                array(
                    'id'=>'setting',
                    'name'=>__('Nastavení','cms_ve'),
                    'setting'=>array(
                        array(
                              'id'=>'background_color',
                              'title'=>__('Barva pozadí','cms_ve'),
                              'type'=>'background',
                              'content'=>array('color1'=>'#eeeeee','color2'=>'','transparency'=>'100'),
                        ), 
                        array(
                              'id'=>'background_image',
                              'title'=>__('Obrázek na pozadí','cms_ve'),
                              'type'=>'bgimage',
                              'content'=>array(
                                'pattern'=>0
                              )
                        ), 
                        array(
                            'id'=>'font',
                            'title'=>__('Písmo','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'line-height'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),
                        array(
                              'id'=>'link-color',
                              'title'=>__('Barva odkazů','cms_ve'),
                              'type'=>'color',
                              'content'=>'',
                        ), 
                        array(
                              'id'=>'border',
                              'title'=>__('Ohraničení bloku','cms_ve'),
                              'type'=>'border',
                              'group'=>'input',
                              'content' => array(
                                  'size'=>'0',
                                  'color'=>''
                              )
                        ), 
                        array(
                              'id'=>'corner',
                              'title'=>__('Míra zakulacení rohů','cms_ve'),
                              'type'=>'slider',
                              'setting'=>array(
                                  'min'=>'0',
                                  'max'=>'100',
                                  'unit'=>'px'
                              ),
                              'content'=>'0',
                              'desc'=>__('Pro ostré rohy zadejte nulu.','cms_ve'),
                        ), 
                        array(
                              'id'=>'padding',
                              'title'=>__('Odsazení vnitřního obsahu (padding) v px','cms_ve'),
                              'type'=>'padding',
                              'content'=>array('top'=>'40','right'=>'40','bottom'=>'30','left'=>'40'),
                              'desc'=>__('Určuje, jak daleko bude obsah odsazen od okraje elementu.','cms_ve'),
                        ),  
                        array(
                              'id'=>'box-shadow',
                              'title'=>__('Stín','cms_ve'),
                              'type'=>'shadow',
                              'content'=>array('horizontal'=>'0','vertical'=>'0','size'=>'0','left'=>'10'),
                              'desc'=>__('Pokud je velikost stínu nastavena na 0, pak je element bez stínu.','cms_ve'),
                        ),
                    
                    )
                ),
                array(
                    'id'=>'title',
                    'name'=>__('Nadpis bloku','cms_ve'),
                    'setting'=>array(
                        array(
                              'id'=>'title',
                              'title'=>__('Nadpis bloku','cms_ve'),
                              'type'=>'text',
                        ), 
                        array(
                            'id'=>'title-font',
                            'title'=>__('Písmo nadpisu','cms_ve'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'20',
                                'font-family'=>'',
                                'line-height'=>'',
                                'align'=>'center',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),
                        array(
                              'id'=>'title_bg',
                              'title'=>__('Barva pozadí nadpisu','cms_ve'),
                              'type'=>'background',
                              'content'=>array('color1'=>'#eeeeee','color2'=>'','transparency'=>'100'),
                        ),
                        array(
                              'id'=>'title_border',
                              'title'=>__('Spodní ohraničení nadpisu','cms_ve'),
                              'type'=>'border',
                              'group'=>'input',
                              'content' => array(
                                  'size'=>'1',
                                  'color'=>'#dddddd'
                              )
                        ), 
                    
                    )
                )    
                        
          ),
    ),  
    'variable_content'=>array(
          'name'=>__('Předdefinovaný obsah', 'cms_ve'),
          'description'=>__('Pomocí tohoto elementu můžete na stránku vložit předem vytvořený obsah, který lze umístit na více stránek. Změna předdefinovaného obsahu se projeví ve všech jeho umístěních.', 'cms_ve'),
          'setting'=>array(
                array(
                  'id' => 'contentinfo',
                  'type' => 'info',
                  'content' => __('Obsah je vždy ovlivněn nastavením vzhledu stránky. Pokud tedy stejný obsah umístíte na různě nastavené stránky, může se lišit například písmem, šířkou atd.','cms_ve'),
                ), 
                array(
                    'id'=>'content',
                    'title'=>__('Obsah', 'cms_ve'),
                    'type'=>'weditor',
                    'setting'=>array(
                        'post_type'=>'ve_elvar',
                        'templates'=>'el_variables',
                        'texts'=>array(
                            'empty'=>__( ' - Bez obsahu - ', 'cms_ve' ),
                            'edit'=>__( 'Upravit vybraný obsah', 'cms_ve' ),
                            'duplicate'=>__( 'Duplikovat vybraný obsah', 'cms_ve' ),
                            'create'=>__( 'Vytvořit nový obsah', 'cms_ve' ),
                            'delete'=>__( 'Smazat vybraný obsah', 'cms_ve' ),
                        ),
                    )
                ),
          ),
    ), 
),'structure');
