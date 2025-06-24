<?php
global $vePage;

$image_lang=(get_locale()=='en_US')? '_en' : '';


$vePage->add_element_groups(array(
    'mioweb'=>array(
        'name'=>__('Kampaně', 'cms_mioweb'),
        'subelement'=>true,
    ),
));
$vePage->add_element_set('countdown',array(
   array(
       'id' => 'campaign_evergreen',
       'title' => __('Nastavit odpočet relativně od vstupu návštěvníka do kampaně','cms_mioweb'),
       'type' => 'row_set',
       'setting' => array(
          array(
              'id'=>'evergreen_days',
              'title'=>__('Dní','cms_mioweb'),
              'type'=>'text',
          ),
          array(
             'id'=>'evergreen_hours',
             'title'=>__('Hodin','cms_mioweb'),
             'type'=>'text',
          ),
          array(
              'id'=>'evergreen_minutes',
              'title'=>__('Minut','cms_mioweb'),
              'type'=>'text',
          ),
       ),
  ),
),2);
$vePage->add_element_set('countdown',array(
  array(
      'id'=>'evergreen_start',
      'title' => __('Začít odpočet od','cms_mioweb'),
      'type' => 'radio',
      'options' => array(
          'mid' => __('půlnoci dne vstupu do kampaně','cms_ve'),
          'start' => __('začátku dne vstupu do kampaně','cms_ve'),
          'enter' => __('času vstupu do kampaně','cms_ve'),
      ),
      'content' => 'mid',
  ), 
),3);
$vePage->add_elements(array(
    'mioweb_nav'=>array(
          'name'=>__('Navigace kampaně', 'cms_mioweb'),
          'description'=>__('Vypíše seznam stránek s obsahem zdarma vybrané kampaně. Slouží jako navigace mezi těmito stránkami.','cms_mioweb'),
          'setting'=>array(
                array(
                    'id'=>'campaign',
                    'title'=>__('Kampaň','cms_mioweb'),
                    'type'=>'selectcampaign'
                ),
                array(
                    'id'=>'style',
                    'title'=>__('Vzhled navigace','cms_mioweb'),
                    'type'=>'imageselect',
                    'content'=>'1',
                    'options' => array(
                        '1' => MIOWEB_DIR.'images/image_select/mionav1'.$image_lang.'.png',
                        '2' => MIOWEB_DIR.'images/image_select/mionav2'.$image_lang.'.png',
                        '3' => MIOWEB_DIR.'images/image_select/mionav3'.$image_lang.'.png',
                        '4' => MIOWEB_DIR.'images/image_select/mionav4'.$image_lang.'.png',
                        '5' => MIOWEB_DIR.'images/image_select/mionav5'.$image_lang.'.png',
                    ),
                ),
                array(
                    'id'=>'font',
                    'title'=>__('Písmo','cms_mioweb'),
                    'type'=>'font',
                    'content'=>array(
                        'font-size'=>'',
                        'font-family'=>'',
                        'weight'=>'',
                        'color'=>'#888888',
                    )
                ), 
                array(
                    'id'=>'color-active',
                    'title'=>__('Barva textu aktivní položky','cms_mioweb'),
                    'type'=>'color',
                    'content'=>'#111111'
                ), 
          ),
    ),
    'se_count'=>array(
          'name'=>__('Počet stažení / koupení', 'cms_mioweb'),
          'description'=>__('Vypíše kontakty z vybraného seznamu ze SmartEmailingu. Můžete tak vypsat informaci o tom, kolik lidí si stáhlo váš ebook nebo koupilo váš produkt (pokud je ukládáte do SmartEmailingu).','cms_mioweb'),
          'tab_setting'=>array(
                array(
                    'id'=>'content',
                    'name'=>__('Obsah','cms_mioweb'),
                    'setting'=>array(
                        array(
                            'id'=>'list',
                            'title'=>__('Vyberte seznam, pro který chcete vypsat počet kontaktů.','cms_mioweb'),
                            'type'=>'list_select'
                        ),                       
                        array(
                            'id'=>'text1',
                            'title'=>__('Text před číslem','cms_mioweb'),
                            'type'=>'text',
                            'content'=>__('Tento ebook si stáhlo již','cms_mioweb'),
                        ),
                        array(
                            'id'=>'text2',
                            'title'=>__('Text za číslem','cms_mioweb'),
                            'type'=>'text',
                            'content'=>__('lidí','cms_mioweb'),
                        ), 
                        array(
                            'id'=>'limit',
                            'title'=>__('Odečítat počet kontaktů od čísla','cms_mioweb'),
                            'type'=>'text',
                            'desc'=>__('Počet kontaktů z vybraného seznamu se bude odečítat od tohoto čísla. Pokud zde zadáte například číslo 100 a v seznamu bude 20 kontaktů, tak se vypíše číslice 80. Pokud zde žádné číslo nezadáte, bude se zobrazovat klasicky počet kontaktů ze seznamu.','cms_mioweb'),
                        ), 
                        array(
                            'id'=>'limit_redirect',
                            'title'=>__('Po dosažení nuly přesměrovat návštěvníka na','cms_mioweb'),
                            'type'=>'page_link',
                            'target'=>false
                        ),                   
                    )
                ),
                array(
                    'id'=>'look',
                    'name'=>__('Vzhled','cms_mioweb'),
                    'setting'=>array(
                        array(
                            'id'=>'font',
                            'title'=>__('Písmo','cms_mioweb'),
                            'type'=>'font',
                            'content'=>array(
                                'font-size'=>'',
                                'font-family'=>'',
                                'weight'=>'',
                                'color'=>'',
                            )
                        ),
                    )
                )
                
                 
          ),
    ),
    'campaign_date'=>array(
          'name'=>__('Proměnlivé datum', 'cms_mioweb'),
          'description'=>__('Vypíše datum závislé na vstupu do kampaně.','cms_mioweb'),
          'setting'=>array(
                array(
                    'id'=>'days',
                    'title'=>__('Vypsat datum posunuté o x dní od vstupu do kampaně','cms_mioweb'),
                    'type'=>'text',
                    'content'=>'2'
                ),
                array(
                    'id'=>'time',
                    'title'=>__('Čas (ve formátu hh:mm)','cms_mioweb'),
                    'type'=>'text',
                    'content'=>__('20:00','cms_mioweb'),
                ),
                array(
                    'id'=>'font',
                    'title'=>__('Písmo','cms_mioweb'),
                    'type'=>'font',
                    'content'=>array(
                        'font-size'=>'30',
                        'font-family'=>'',
                        'weight'=>'',
                        'align'=>'center',
                        'color'=>'',
                    )
                ), 
          ),
    ),
),'mioweb');
