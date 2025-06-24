<?php
global $vePage;

$image_lang=(get_locale()=='en_US')? '_en' : '';

$vePage->add_element_groups(array(
    'member'=>array(
        'name'=>__('Členská sekce', 'cms_member'),
        'subelement'=>true,
    ),
));
$vePage->add_elements(array(
    'member_login'=>array(
          'name'=>__('Přihlašovací formulář', 'cms_member'),
          'description'=>__('Formulář pomocí, kterého se mohou návštěvníci přihlašovat do vámi vytvořené členské sekce.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'form',
                    'name'=>__('Základní nastavení','cms_member'),
                    'setting'=>array(
                        array(
                            'id'=>'loginto',
                            'title'=>__('Přihlásit do členské sekce','cms_member'),
                            'type'=>'selectmember',
                            'empty'=>' - ',
                            'desc'=>__('Pokud nevyberete žádnou členskou sekci, bude se uživatel přihlašovat do členské sekce, do které je zařazena tato stránka. Pokud členskou sekci vyberete, bude se uživatel přihlašovat do vybrané členské sekce.','cms_member'),
                        ),
                    ),
                ), 
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled formuláře','cms_member'),
                    'setting'=>array(
                        array(
                            'id'=>'input-style',
                            'title'=>__('Vzhled inputu','cms_member'),
                            'type'=>'imageselect',
                            'content'=>'2',
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
                            'title'=>__('Písmo formulářových polí','cms_member'),
                            'type'=>'font',
                            'group'=>'input',
                            'content'=>array(
                                'font-size'=>'15',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'background',
                            'title'=>__('Barva formulářových polí','cms_member'),
                            'type'=>'color',
                            'group'=>'input',
                            'content' => '#eeeeee'
                        ),                        
                        array(
                            'id'=>'button',
                            'title'=>__('Vzhled tlačítka','cms_member'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'22',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#ffffff',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ff9b05',
                                    'color2'=>'#e8770e',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border'=>array(
                                    'size'=>'',
                                    'color'=>'',
                                ),
                                'size'=>1,
                                'hover_effect'=>'lighter',
                            )
                        ),    
                    ),
                ),
                
            ),
    ),
    'member_regform'=>array(
          'name'=>__('Registrační formulář', 'cms_member'),
          'description'=>__('Formulář pomocí, kterého se mohou návštěvníci zdarma registrovat do členské sekce.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'form',
                    'name'=>__('Základní nastavení','cms_member'),
                    'setting'=>array(
                        array(
                            'id'=>'reginto',
                            'title'=>__('Registrovat do členské sekce:','cms_member'),
                            'type'=>'selectmemberlevel',
                            'desc'=>__('Vyberte členskou sekci (popřípadě členskou úroveň), do které se má uživatel registrovat.','cms_member'),
                        ),
                        array(
                            'id'=>'redirect',
                            'title'=>__('Po registraci přesměrovat uživatele na:','cms_member'),
                            'type'=>'page_link',
                            'target'=>false,
                            'desc'=>__('Zadejte URL adresu stránky, na kterou chcete uživatele po registraci přesměrovat.','cms_member'),
                        ),
                        array(
                            'id'=>'sendtose',
                            'title'=>__('Uložit nově registrovaný kontakt do seznamu','cms_member'),
                            'type'=>'list_select',
                        ),
                    ),
                ), 
                array(
                    'id'=>'advanced',
                    'name'=>__('Pokročilé nastavení','cms_member'),
                    'setting'=>array(
                        array(
                            'id'=>'hide',
                            'title'=>__('Zobrazení','cms_member'),
                            'type' => 'multiple_checkbox',
                            'options' => array(
                                array('name' => __('Skrýt jméno','cms_member'), 'value' => 'name'),                                       
                            ),
                        ),
                        array(
                            'id'=>'generate_password',
                            'title'=>__('Generovat heslo','cms_member'),
                            'type'=>'checkbox',
                            'label'=>__('Generovat heslo automaticky','cms_member'),
                        ),
                        array(
                            'id'=>'days',
                            'title'=>__('Registrovat na X dní','cms_member'),
                            'type'=>'text',
                            'desc'=>__('Pokud chcete registraci zdarma časově omezit, zadejte na kolik dní se má uživatel registrovat. Po vypršení této doby se přístupy zablokují.','cms_member'),
                        ),
                        array(
                            'id'=>'update',
                            'title'=>__('Aktualizovat uživatele','cms_member'),
                            'type'=>'checkbox',
                            'label'=>__('Povolit registraci i pro existující uživatele a přidat ho do vybrané členské sekce nebo úrovně.','cms_member'),
                        ),
                        array(
                            'id'=>'sendtomail',
                            'title'=>__('Odeslat informaci o nové registraci na e-mail:','cms_member'),
                            'type'=>'text',
                            'desc'=>__('Zadejte e-mailovou adresu, na kterou chcete zaslat informaci o nové registraci. Pokud e-mail nevyplníte, nebude se informace zasílat.','cms_member'),
                        ),
                        array(
                            'id'=>'gdpr_info',
                            'title'=>__('Informační text pod formulářem pro přidání komentáře','cms'),
                            'content'=>__('Vaše osobní údaje budou použity pouze pro účely vytvoření a fungování vašeho účtu zdarma.','cms'),
                            'type'=>'textarea',
                        ),
                        array(
                            'id'=>'gdpr_link_text',
                            'title'=>__('Text odkazu na zásady zpracování osobních údajů','cms'),
                            'content'=>__('Zásady zpracování osobních údajů','cms'),
                            'type'=>'text',
                        ),
                    ),
                ), 
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled formuláře','cms_member'),
                    'setting'=>array(
                        array(
                            'id'=>'input-style',
                            'title'=>__('Vzhled inputu','cms_member'),
                            'type'=>'imageselect',
                            'content'=>'2',
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
                            'title'=>__('Písmo formulářových polí','cms_member'),
                            'type'=>'font',
                            'group'=>'input',
                            'content'=>array(
                                'font-size'=>'15',
                                'color'=>'',
                            )
                        ),
                        array(
                            'id'=>'background',
                            'title'=>__('Barva formulářových polí','cms_member'),
                            'type'=>'color',
                            'group'=>'input',
                            'content' => '#eeeeee'
                        ),                        
                        array(
                            'id'=>'button',
                            'title'=>__('Vzhled tlačítka','cms_member'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'22',
                                    'font-family'=>'',
                                    'weight'=>'',
                                    'color'=>'#ffffff',
                                    'text-shadow'=>'',
                                ),
                                'background_color'=>array(
                                    'color1'=>'#ff9b05',
                                    'color2'=>'#e8770e',
                                ),
                                'hover_color'=>array(
                                    'color1'=>'',
                                    'color2'=>'',
                                ),
                                'border'=>array(
                                    'size'=>'',
                                    'color'=>'',
                                ),
                                'size'=>1,
                                'hover_effect'=>'lighter',
                            )
                        ),    
                    ),
                ),
                
            ),
    ),
    'member_subpages'=>array(
          'name'=>__('Seznam lekcí', 'cms_member'),
          'description'=>__('Vypíše podstránky aktuální nebo vybrané stránky jako obrázkový seznam s náhledovým obrázkem a popisem stránky. Tento element lze využít například jako seznam lekcí výukového programu.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'list',
                    'name'=>__('Seznam lekcí','cms_member'),
                    'setting'=>array(
                        array(
                             'id'=>'page',
                             'title'=>__('Vypsat podstránky od','cms_member'),
                             'type'=>'selectpage',
                             'content'=>'',
                             'desc'=>__('Vyberte stránku, jejíž podstránky chcete vypsat jako seznam lekcí. Pokud žádnou nevyberete, vypíšou se podstránky aktuální stránky.','cms_member'),
                        ),
                        array(
                              'id'=>'setting',
                              'title'=>__('Nastavení zobrazení','cms_member'),
                              'type' => 'multiple_checkbox',
                              'options' => array(
                                  array('name' => __('Skrýt počet komentářů','cms_member'), 'value' => 'hide_comments'),
                                  array('name' => __('Skrýt popisek','cms_member'), 'value' => 'hide_desc'),
                                  array('name' => __('Skrýt obrázek','cms_member'), 'value' => 'hide_image'),
                              ),
                        ),
                    ),
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled seznamu','cms_member'),
                    'setting'=>array(
                        array(
                              'id'=>'structure',
                              'title'=>__('Struktura výpisu','cms_member'),
                              'type' => 'radio',
                              'show'=>'structure',
                              'content'=>'2',
                              'options' => array(
                                  '1'=>__('Obrázek nad textem', 'cms_member'),
                                  '2'=>__('Obrázek vedle textu', 'cms_member'),
                              ), 
                        ),
                        array(
                              'id'=>'style',
                              'title'=>__('Vzhled','cms_member'),
                              'type'=>'imageselect',
                              'content'=>'1',
                              'options' => array(
                                  '1' => MEMBER_DIR.'images/image_select/subpage1'.$image_lang.'.png',
                                  '2' => MEMBER_DIR.'images/image_select/subpage2'.$image_lang.'.png',
                                  '3' => MEMBER_DIR.'images/image_select/subpage3'.$image_lang.'.png',
                                  '5' => MEMBER_DIR.'images/image_select/subpage5'.$image_lang.'.png',
                                  '4' => MEMBER_DIR.'images/image_select/subpage4'.$image_lang.'.png',
                              ),
                              'show'=>'style',
                        ),
                        array(
                             'id'=>'color',
                             'title'=>__('Barva pozadí','cms_member'),
                             'type'=>'color',
                             'content'=>'#219ed1',
                             'show_group' => 'style',
                             'show_val' => '4',
                        ),
                        array(
                              'id'=>'cols',
                              'title'=>__('Počet sloupců','cms_member'),
                              'type'=>'select',
                              'options' => array(
                                  array('name' => '1', 'value' => '1'),
                                  array('name' => '2', 'value' => '2'),
                                  array('name' => '3', 'value' => '3'),
                              ),
                              'desc'=>__('Počet sloupců se přizpůsobí šířce obsahu. Například pokud nastavíte více sloupců a element umístíte do úzkého prostoru, nebudou stránky vypsány vedle sebe, ale pod sebou.','cms_member'),                    
                      ),
                      array(
                             'id'=>'image_size',
                             'title'=>__('Velikost obrázku','cms_member'),
                             'type'=>'size',
                             'unit'=>'%',
                             'content'=>array(
                                'size'=>'37',
                                'unit'=>'%',
                             ),
                             'show_group' => 'structure',
                             'show_val' => '2',
                     ),
                      array(
                             'id'=>'font',
                             'title'=>__('Font názvu stránky','cms_member'),
                             'type'=>'font',
                             'content'=>array(
                                'font-size'=>'22',
                                'font-family'=>'',
                                'line-height'=>'',
                                'weight'=>'',
                                'color'=>'',
                             ),
                     ),                                
                    array(
                             'id'=>'default_image',
                             'title'=>__('Defaultní obrázek','cms_member'),
                             'type'=>'upload',
                             'desc'=>__('Tento obrázek se bude ve výpisu zobrazovat jako obrázek stránky v případě, že stránka nebude mít nastavený svůj obrázek.','cms_member'),
                     ),
                )
            )
        ),
    ),
    'member_download'=>array(
          'name'=>__('Ke stažení', 'cms_member'),
          'description'=>__('Vložte na stránku seznam souborů ke stažení. Vhodné k poskytnutí podkladů pro danou lekci.','cms_member'),
          'tab_setting'=>array(
              array(
                'id'=>'multifiles',
                'name'=>__('Soubory ke stažení','cms_member'),
                'setting'=>array(  
                    array(
                        'id'=>'content',
                        'title'=>'',
                        'type'=>'multifiles',
                        'options' => array(
                            '1' => MEMBER_DIR.'images/image_select/download_icon1.png',
                            '2' => MEMBER_DIR.'images/image_select/download_icon2.png',
                            '3' => MEMBER_DIR.'images/image_select/download_icon3.png',
                            '4' => MEMBER_DIR.'images/image_select/download_icon4.png',
                            '5' => MEMBER_DIR.'images/image_select/download_icon5.png',
                            '6' => MEMBER_DIR.'images/image_select/download_icon6.png',
                            '7' => MEMBER_DIR.'images/image_select/download_icon7.png',
                            '8' => MEMBER_DIR.'images/image_select/download_icon8.png',
                            '9' => MEMBER_DIR.'images/image_select/download_icon9.png',
                            '10' => MEMBER_DIR.'images/image_select/download_icon10.png',
                            '11' => MEMBER_DIR.'images/image_select/download_icon11.png',
                            '12' => MEMBER_DIR.'images/image_select/download_icon12.png',
                            '13' => MEMBER_DIR.'images/image_select/download_icon13.png',
                            '14' => MEMBER_DIR.'images/image_select/download_icon14.png',
                            '15' => MEMBER_DIR.'images/image_select/download_icon15.png',
                            '16' => MEMBER_DIR.'images/image_select/download_icon16.png',
                            '17' => MEMBER_DIR.'images/image_select/download_icon17.png',
                        ),
                    ),
                ),
              ),
              array(
                    'id'=>'style',
                    'name'=>__('Vzhled','cms_member'),
                    'setting'=>array(                        
                        array(
                            'id'=>'style',
                            'title'=>__('Styl','cms_member'),
                            'type'=>'imageselect',
                            'content'=>'1',
                            'options' => array(
                                '1' => MEMBER_DIR.'images/image_select/download1.png',
                                '2' => MEMBER_DIR.'images/image_select/download2.png',
                                '3' => MEMBER_DIR.'images/image_select/download3.png',
                                '4' => MEMBER_DIR.'images/image_select/download4.png',
                            ),
                        ),  
                        array(
                            'id'=>'color',
                            'title'=>__('Barva','cms_member'),
                            'type'=>'color',
                            'content'=>'#219ed1',
                        ),  
                        array(
                               'id'=>'font',
                               'title'=>__('Font nadpisu','cms_member'),
                               'type'=>'font',
                               'content'=>array(
                                  'font-size'=>'20',
                                  'font-family'=>'',
                                  'line-height'=>'',
                                  'weight'=>'',
                               ),
                       ),
                       array(
                               'id'=>'font_text',
                               'title'=>__('Font popisku','cms_member'),
                               'type'=>'font',
                               'content'=>array(
                                  'font-size'=>'',
                                  'font-family'=>'',
                                  'line-height'=>'',
                                  'weight'=>'',
                               ),
                       ),
                  ),
            ),               
        ),
      
    ),
    'member_checklist'=>array(
          'name'=>__('Seznam úkolů', 'cms_member'),
          'description'=>__('Tento element můžete umístit na stránku jako seznam úkolů. Uživatel si pak může odškrtnout ty úkoly, které už splnil.','cms_member'),
          'tab_setting'=>array(
              array(
                  'id'=>'checklist',
                  'name'=>__('Seznam úkolů','cms_member'),
                  'setting'=>array(  
                      array(
                          'id'=>'title',
                          'title'=>__('Nadpis seznamu','cms_member'),
                          'type'=>'text',
                      ), 
                      array(
                          'id'=>'use',
                          'title'=>__('Zobrazit','cms_member'),
                          'type'=>'radio',
                          'options' => array(
                              'page' => __('Seznam úkolů stránky','cms_member'),
                              'custom' => __('Vlastní seznam úkolů','cms_member'),
                          ),
                          'content' => 'page',
                          'show' => 'use',
                      ),  
                      array(
                          'id'=>'info',
                          'title'=>'',
                          'type'=>'info',
                          'content' =>  __('Použije se seznam úkolů této stránky. Úkoly stránky můžete nastavit v Nastavení stránky -> Členská stránka. Takto vytvořený seznam úkolů se započítává do výsledku postupu v elementu Progress bar.','cms_member'),
                          'show_group' => 'use',
                          'show_val' => 'page', 
                      ), 
                      array(
                          'id'=>'custom_checklist',
                          'type'=>'group',
                          'setting'=>array(
                              array(
                                  'id'=>'info',
                                  'title'=>'',
                                  'type'=>'info',
                                  'content' =>  __('Vytvořte si vlastní seznam úkolů. Tento seznam úkolů se však nebude započítávat do výsledku postupu.','cms_member'),
                              ),                            
                              array(
                                  'id'=>'content',
                                  'title'=>__('Seznam úkolů','cms_member'),
                                  'type'=>'bullets',
                                  'setting'=>'classic'
                              ), 
                              array(
                                  'id'=>'checklist',
                                  'title'=>'',
                                  'type'=>'id_generator',
                              ),                        
                          ),
                          'show_group' => 'use',
                          'show_val' => 'custom', 
                      )   
                  ), 
              ),
              array(
                    'id'=>'style',
                    'name'=>__('Vzhled','cms_member'),
                    'setting'=>array(
                        
                        array(
                              'id'=>'icon',
                              'title'=>__('Vzhled zaškrtávátka','cms_member'),
                              'type'=>'svg_iconselect',
                              'content' => array(
                                  'icon'=>'ok1',
                                  'size'=>'25',
                                  'color'=>'#ffffff',
                                  'background'=>'#52a303',
                                  'corner'=>'0'
                              ), 
                              'setting' => array(
                                  'max-size'=>'60',
                              ), 
                              'icons' => array( 
                                  'ok1' => get_template_directory().'/modules/visualeditor/images/icons/',  
                                  'ok2' => get_template_directory().'/modules/visualeditor/images/icons/',    
                              ),                                                                          
                        ),                         
                        array(
                               'id'=>'font',
                               'title'=>__('Font nadpisu','cms_member'),
                               'type'=>'font',
                               'content'=>array(
                                  'font-size'=>'30',
                                  'font-family'=>'',
                                  'line-height'=>'',
                                  'weight'=>'',
                               ),
                       ),
                       array(
                               'id'=>'font_text',
                               'title'=>__('Font úkolů','cms_member'),
                               'type'=>'font',
                               'content'=>array(
                                  'font-size'=>'20',
                                  'font-family'=>'',
                                  'line-height'=>'',
                                  'weight'=>'',
                               ),
                       ),
                  ),
            ),               
        ),      
    ),    
    'member_progress'=>array(
          'name'=>__('Ukazatel pokroku', 'cms_member'),
          'description'=>__('Ukazatel pokroku zobrazuje, kolik procent úkolů vašich lekcí uživatel splnil. Úkoly vytváříte v Nastavení stránky -> Členská stránka a jejich seznam můžete zobrazit pomocí elementu Seznamu úkolů.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'progressbar',
                    'name'=>__('Progress bar','cms_member'),
                    'setting'=>array(
                        array(
                            'title' => __('Zobrazit pokrok pro','cms_member'),
                            'id' => 'show',
                            'type' => 'radio',
                            'show'=>'progressfor',
                            'options' => array(
                                'page'=>__('Stránku a její podstránky', 'cms_member'),
                                'member'=>__('Celou členskou sekci', 'cms_member'),
                            ), 
                            'content' => 'page',
                        ),
                        array(
                            'id'=>'member',
                            'title'=>__('Členská sekce','cms_member'),
                            'type'=>'selectmember',
                            'show_group' => 'progressfor', 
                            'show_val' => 'member', 
                            'desc'=>__('Do výsledku se budou započítávat seznamy úkolů všech stránek vybrané členské sekce. Pokud nevyberete žádnou členskou sekci, tak se bude brát členská sekce, do které je zařazená aktuální stránka','cms_member'),
                        ),                          
                        array(
                            'id'=>'page',
                            'title'=>__('Stránka','cms_member'),
                            'type'=>'selectpage',
                            'show_group' => 'progressfor', 
                            'show_val' => 'page', 
                            'desc'=>__('Do výsledku se budou započítávat seznamy úkolů všech podstránek od vybrané stránky. Pokud nevyberete žádnou podstránku, tak se budou brát podstránky aktuální stránky.','cms_member'),
                        ),  
                        array(
                            'id'=>'text',
                            'title'=>__('Popisek pokroku','cms_member'),
                            'type'=>'text',
                        ),  
                    ),
                ),
                $vePage->elements['progressbar']['tab_setting'][1],            
            ),
    ),
    'member_news'=>array(
          'name'=>__('Členské novinky', 'cms_member'),
          'description'=>__('Pomocí tohoto elementu můžete vypsat všechny nebo i jen několik posledních členských novinek.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'news',
                    'name'=>__('Členské novinky','cms_member'),
                    'setting'=>array(                        
                        array(
                              'id'=>'type',
                              'title'=>__('Typ výpisu','cms_member'),
                              'type' => 'radio',
                              'show'=>'type',
                              'content'=>'all',
                              'options' => array(
                                  'all'=>__('Všechny novinky', 'cms_member'),
                                  'last'=>__('Poslední novinky', 'cms_member'),
                              ), 
                        ),
                        array(
                            'id'=>'last_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'number_news',
                                    'title'=>__('Počet novinek','cms_member'),
                                    'type'=>'text',
                                    'content'=>'3',
                                ),                                  
                                array(
                                    'id'=>'words_last',
                                    'title'=>__('Počet slov v náhledu novinky','cms_member'),
                                    'type'=>'text',
                                    'content'=>'25',
                                ),
                            ),
                            'show_group' => 'type',
                            'show_val' => 'last',  
                        ),
                        array(
                            'id'=>'all_group',
                            'type'=>'group',
                            'setting'=>array( 
                                array(
                                    'id'=>'per_page',
                                    'title'=>__('Počet novinek na stránku','cms_member'),
                                    'type'=>'text',
                                    'content'=>'10',
                                ),  
                                array(
                                    'id'=>'words_all',
                                    'title'=>__('Počet slov v náhledu novinky','cms_member'),
                                    'type'=>'text',
                                    'content'=>'100',
                                ),
                            ),
                            'show_group' => 'type',
                            'show_val' => 'all',  
                        ),
                        
                    ),
                ), 
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled','cms_member'),
                    'setting'=>array(  
                        array(
                            'id'=>'font_title',
                            'title'=>__('Font nadpisu','cms_member'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'20',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font',
                            'title'=>__('Font textu','cms_member'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ), 
                    )
                )        
            ),
    ),
    'member_users'=>array(
          'name'=>__('Katalog členů', 'cms_member'),
          'description'=>__('Vypíše seznam členů vybrané členské sekce.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'user_list',
                    'name'=>__('Katalog členů','cms_member'),
                    'setting'=>array(
                        array(
                            'id'=>'title',
                            'title'=>__('Nadpis katalogu','cms_member'),
                            'type'=>'text',
                            'content'=>__('Katalog členů','cms_member'),
                        ),  
                        array(
                              'id'=>'show',
                              'title'=>__('Vypisovat','cms_member'),
                              'type' => 'radio',
                              'show'=>'show_users',
                              'content'=>'1',
                              'options' => array(
                                  '1'=>__('Všechny členy', 'cms_member'),
                                  '2'=>__('Jen vybrané členy', 'cms_member'),
                              ), 
                        ),
                        array(
                            'name' => __('Vypsat uživatele z','cms_member'),
                            'id' => 'member_section',
                            'type' => 'selectmemberlevel',  
                            'show_group' => 'show_users',
                            'show_val' => '2',                        
                        ),
                        array(
                            'id'=>'per_page',
                            'title'=>__('Počet členů na stránku','cms_member'),
                            'type'=>'text',
                            'content'=>'15',
                        ),                                  
                        array(
                            'id'=>'words',
                            'title'=>__('Počet slov v náhledu','cms_member'),
                            'type'=>'text',
                            'content'=>'48',
                        ),
                    ),
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled katalogu','cms_member'),
                    'setting'=>array(
                        array(
                              'id'=>'style',
                              'title'=>__('Vzhled','cms_member'),
                              'type'=>'imageselect',
                              'content'=>'1',
                              'options' => array(
                                  '1' => MEMBER_DIR.'images/image_select/catalog1.jpg',
                                  '2' => MEMBER_DIR.'images/image_select/catalog2.jpg',
                              ),
                              'show'=>'style',
                        ),
                        array(
                            'id'=>'cols',
                            'title'=>__('Počet sloupců','cms_member'),
                            'type'=>'select',
                            'content'=> 3,
                            'options' => array(
                                array('name' => '2', 'value' => 2),
                                array('name' => '3', 'value' => 3),
                                array('name' => '4', 'value' => 4),
                            ),
                            'show_group' => 'style',
                            'show_val' => '2',
                        ),
                        array(
                            'id'=>'button_color',
                            'title'=>__('Barva tlačítka','cms_member'),
                            'type'=>'color',
                            'content' => '#219ED1'
                        ),  
                        array(
                            'id'=>'font_title',
                            'title'=>__('Font jména','cms_member'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'20',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                        ), 
                        array(
                            'id'=>'font',
                            'title'=>__('Font textu','cms_member'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'line-height'=>'',
                                'color'=>'',
                            )
                      ),
                        
                )
            )
        ),
    ),
    'members_list'=>array(
          'name'=>__('Seznam členských sekcí', 'cms_member'),
          'description'=>__('Vypíše seznam členských sekcí s proklikem.','cms_member'),
          'tab_setting'=>array(
                array(
                    'id'=>'list',
                    'name'=>__('Nastavení','cms_member'),
                    'setting'=>array(  
                        array(
                            'id'=>'members',
                            'type'=>'multielement',
                            'texts'=>array(
                                'add'=>__('Přidat členskou sekci','cms_member'),
                            ),
                            'setting'=>array(                             
                                array(
                                    'id'=>'member',
                                    'title'=>__('Členská sekce','cms_member'),
                                    'type'=>'selectmember',
                                ),
                                array(
                                    'id'=>'image',
                                    'title'=>__('Obrázek','cms_member'),
                                    'type'=>'image', 
                                ),
                                array(
                                    'id'=>'title',
                                    'title'=>__('Název členské sekce','cms_member'),
                                    'type'=>'text',
                                ),
                                array(
                                    'id'=>'description',
                                    'title'=>__('Prodejní popis','cms_member'),
                                    'type'=>'textarea',
                                    'desc'=>__('Pokud nemá uživatel do této členské sekce přístup a klikne na ni, otevře se mu popup s tímto popisem a tlačítkem odkazujícím na stránku, kde může přístup získat.','cms_member'),
                                ),
                                array(
                                    'id'=>'link',
                                    'title'=>__('Odkaz na prodejní stránku','cms_member'),
                                    'type'=>'page_link',
                                    'desc'=>__('Na stránce kterou zde nastavíte by měl uživatel mít možnost získat přístup do této členské sekce a to buď nákupem nebo registrací zdarma.','cms_member'),
                                ),
                            ),
                        ),                           
                    ),
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled','cms_member'),
                    'setting'=>array(
                      array(
                          'id'=>'cols',
                          'title'=>__('Počet sloupců','cms_member'),
                          'type'=>'select',
                          'content'=> 3,
                          'options' => array(
                              array('name' => '1', 'value' => 1),
                              array('name' => '2', 'value' => 2),
                              array('name' => '3', 'value' => 3),
                              array('name' => '4', 'value' => 4),
                          ),
                      ),
                      array(
                          'id'=>'style',
                          'title'=>__('Způsob zobrazení','cms_member'),
                          'type' => 'imageselect',
                          'options' => array(                              
                              '1' => VS_DIR.'images/image_select/gallery3.png',
                              '2' => VS_DIR.'images/image_select/gallery2.png',
                          ),
                          'content' => '1',
                      ),
                      array(
                          'id'=>'font',
                          'title'=>__('Písmo Názvu','cms_member'),
                          'type'=>'font',
                          'content'=>array(
                              'font-size'=>'20',
                              'font-family'=>'',
                              'color'=>'',
                              'align'=>'',
                          )
                      ), 
                      array(
                            'id'=>'button',
                            'title'=>__('Styl tlačítka','cms_member'),
                            'type'=>'button',
                            'options' => $vePage->list_buttons,
                            'content'=>array( 
                                'style'=>'1',                       
                                'font'=>array(
                                    'font-size'=>'22',
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
                                'height_padding'=>'0.7',
                                'width_padding'=>'1.4',
                            )
                        ),    
 
                    ),
                ),
                            
            ),
    ),
),'member');
