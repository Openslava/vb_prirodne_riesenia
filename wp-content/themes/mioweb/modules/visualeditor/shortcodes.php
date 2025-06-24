<?php
global $vePage;

$vePage->add_shortcode_groups(array(
    'basic'=>array(
        'name'=>__('Základní','cms_ve'),
        'subelement'=>true,
    ),
));

$vePage->add_shortcodes(array(
  'popup'=>array(
          'name'=>__('Odkaz s pop-upem','cms_ve'),
          'type'=>'text',
          'description'=>__('Tento shortcode vytvoří v textu odkaz, který bude otevírat vybraný pop-up.','cms_ve'),
          'setting'=>array(
              array(
                  'id' => 'popupinfo',
                  'type' => 'info',
                  'content' => __('Pop-up v textu začne správně fungovat až po uložení a znovunačtení stránky.','cms_ve'),
              ), 
              array(
                  'title' => __('Zobrazit pop-up','cms_ve'),
                  'id' => 'id',
                  'type' => 'popupselect',
              ), 
          ),      
  ),
  'box'=>array(
            'name'=>__('Text na pozadí','cms_ve'),
            'type'=>'text',
            'visibility'=>'blog',
            'description'=>__('Vybraný text vloží do boxu, kterému můžete nastavit libovolnou barvu pozadí.','cms_ve'),

                    'setting'=>array(
                        array(
                            'id'=>'background',
                            'title'=>__('Barva pozadí','cms_ve'),
                            'type'=>'color',
                            'content'=>'#e8e8e8',
                        ),  
                        array(
                            'id'=>'color',
                            'title'=>__('Barva textu','cms_ve'),
                            'type'=>'color',
                        ),                     

            )
  ),
  'mwvideo'=>array(
            'name'=>__('Video','cms_ve'),
            'visibility'=>'blog',
            'description'=>__('Vložte do článku video jednoduše zadáním odkazu na YouTube nebo Vimeo stránku s videem.','cms_ve'),

                    'setting'=>array(
                        array(
                            'id'=>'url',
                            'title'=>__('URL videa','cms_ve'),
                            'type'=>'text',
                            'desc'=>__('Vložte URL stránky s YouTube nebo Vimeo videem.','cms_ve'),
                        ),
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

            )
  ),
  'content'=>array(
          'name'=>__('Předdefinovaný obsah', 'cms_ve'),
          'visibility'=>'blog',
          'description'=>__('Pomocí tohoto shortcodu můžete na stránku vložit obsah vytvořený pomocí vizuálního editoru.', 'cms_ve'),
          'setting'=>array(
                array(
                  'id' => 'contentinfo',
                  'type' => 'info',
                  'content' => __('Obsah je vždy ovlivněn nastavením vzhledu stránky. Pokud tedy stejný obsah umístíte na různě nastavené stránky, může se lišit například písmem, šířkou atd.','cms_ve'),
                ), 
                array(
                    'id'=>'id',
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
),'basic');
