function startIntroTut(tut){
    if(tut='start') {
    
        jQuery('.sh-editor-features').html('<?php echo __('Skrýt ovládání','cms_ve'); ?>');
        jQuery('body').removeClass('ve_hidden_features');
        jQuery('.sh-editor-features').removeClass('show-editor-features');
        jQuery('.sh-editor-features').addClass('hide-editor-features');
        document.cookie = 've_hidden_features=0; path=/';   
    
        var intro = introJs();
          intro.setOptions({
            steps: [
              { 
                intro: "<strong><?php echo __('SEZNAMTE SE S MIOWEBEM 2.0','cms_ve'); ?></strong><p><?php echo __('Ukážeme si a vysvětlíme několik základních prvků MioWebu, tak aby pro vás byla tvorba webu hračkou. Pokračujte kliknutím na tlačítko Další.','cms_ve'); ?></p>"
              },  
              {
                element: '#element_0_0_0',
                intro: "<strong><?php echo __('ELEMENTY','cms_ve'); ?></strong><p><?php echo __('Stránky se skládají z tzv. elementů, což jsou jednotlivé obsahové prvky jako nadpisy, texty, videa a mnoho dalších. Pomocí těchto elementů vytváříte obsah stránek.','cms_ve'); ?></p>",
                position: 'bottom'
              },                          
              {
                element: '#element_0_0_0 .ce_editbar',
                intro: "<strong><?php echo __('EDITACE ELEMENTŮ','cms_ve'); ?></strong><p><?php echo __('Při najetí na každý element se objeví tento editační panel, pomocí kterého můžete s elementy pracovat. Elementy lze:<ul><li>Přesouvat a měnit jejich pořadí.</li><li>Editovat jejich obsah a vzhled</li><li>Nastavit jim šířku, odsazení a další vlastnosti.</li><li>Duplikovat.</li><li>A mazat.</li></ul>','cms_ve'); ?></p>",
                position: 'left'
              },
              {
                element: '#row_0 .add_element',
                intro: '<strong><?php echo __('PŘIDAT ELEMENT','cms_ve'); ?></strong><p><?php echo __('Kliknutím na tlačítko „Přidat element“ můžete na stránku přidávat další elementy.','cms_ve'); ?></p>',
                position: 'top'
              },
              {
                element: '#row_0 .row_edit_container',
                intro: "<strong><?php echo __('ŘÁDKY','cms_ve'); ?></strong><p><?php echo __('Dalším stavebním kamenem stránek jsou takzvané řádky. Každý řádek má svou strukturu (počet sloupců) a nastavení vzhledu (pozadí, odsazení, písmo,...). Na stránku můžete přidávat libovolně další řádky s různým počtem sloupců a tak vytvářet různé rozložení obsahu. Do jednotlivých sloupců a řádků potom vkládáte elementy.','cms_ve'); ?></p>",
                position: 'bottom'
              },
              {
                element: '#row_0 .row_edit_bar',
                intro: "<strong><?php echo __('EDITACE ŘÁDKŮ','cms_ve'); ?></strong><p><?php echo __('Po najetí na řádek se vždy zobrazí tento panel nástrojů, pomocí kterého můžete řádek editovat. Řádky lze: <ul><li>Přesouvat a měnit jejich pořadí.</li><li>Editovat vzhled.</li><li>Duplikovat řádek včetně jeho obsahu.</li><li>Zkopírovat řádek do paměti a potom jej vložit na jinou stránku.</li><li>Odstranit řádek ze stránky.</li></ul>','cms_ve'); ?></p>",
                position: 'left'
              },
              {
                element: '#row_0 .row_add_container',
                intro: '<strong><?php echo __('PŘIDAT ŘÁDEK','cms_ve'); ?></strong><p><?php echo __('Pomocí tlačítka „Přidat řádek“ můžete na stránku přidávat další řádky.','cms_ve'); ?></p>',
                position: 'bottom'
              },
              {
                element: '.sh-editor-features',
                intro: '<strong><?php echo __('NÁHLED STRÁNKY','cms_ve'); ?></strong><p><?php echo __('Pokud si chcete stránku prohlédnout bez rušivých editačních prvků, klikněte na „Skrýt ovládání“. Levý panel potom můžete skrýt kliknutím na šipku na jeho pravém okraji. Znovukliknutím pak ovládací prvky zase zobrazíte.','cms_ve'); ?></p>',
                position: 'right'
              },
              {
                element: '#ev_save_page',
                intro: '<strong><?php echo __('ULOŽIT STRÁNKU','cms_ve'); ?></strong><p><?php echo __('Nezapomeňte si stránku vždy uložit kliknutím na tlačítko „Uložit změny“.','cms_ve'); ?></p>',
                position: 'right'
              },
              {
                element: '.create-new-page',
                intro: "<strong><?php echo __('NOVÁ STRÁNKA','cms_ve'); ?></strong><p><?php echo __('Novou stránku vytvoříte kliknutím na Web->Nová stránka v horním panelu. Při tvorbě stránek máte na výběr z celé řady předpřipravených šablon, které můžete dále editovat. Můžete si ale vytvářet i vlastní stránky z prázdných šablon.','cms_ve'); ?></p>",
                position: 'right'
              },
              {
                element: '.ve_editor_first_menu',
                intro: "<strong><?php echo __('GLOBÁLNÍ NASTAVENÍ','cms_ve'); ?></strong><p><?php echo __('V horní části levého panelu se nachází globální nastavení sekce, ve které se nacházíte (web, členská sekce, blog). Globální nastavení ovlivňuje vždy všechny stránky. Zde tedy můžete nastavit například vzhled hlavičky, formátování a patičky, které budou platit pro všechny stránky.','cms_ve'); ?></p>",
                position: 'right'
              },
              {
                element: '.ve_editor_second_menu',
                intro: "<strong><?php echo __('NASTAVENÍ A AKCE STRÁNKY','cms_ve'); ?></strong><p><?php echo __('Ve spodní části levého panelu se potom nachází nastavení týkající se pouze stránky, na které se zrovna nacházíte. Zde můžete nastavit stránce například SEO, facebookové atributy nebo i vlastní hlavičku a patičku, které se budou lišit od zbytku webu. Poslední položkou jsou akce stránky, kde můžete nastavit stránku jako domovskou, smazat nebo ji zduplikovat.','cms_ve'); ?></p>",
                position: 'right'
              },
              {
                element: '#ve_change_page',
                intro: "<strong><?php echo __('PŘEPÍNÁNÍ MEZI STRÁNKAMI','cms_ve'); ?></strong><p><?php echo __('Pomocí tohoto selectu se můžete přepínat mezi jednotlivými stránkami.','cms_ve'); ?></p>",
                position: 'right'
              },
              {
                element: '#ve_editor_top_panel',
                intro: "<strong><?php echo __('HORNÍ PANEL','cms_ve'); ?></strong><p><?php echo __('V horním panelu naleznete záložky jednotlivých sekcí webů, pomocí kterých se mezi těmito sekcemi můžete snadno přepínat. Také zde naleznete nastavení celého webu a nástroje pro správu jednotlivých sekcí. V nastavení najdete například možnost nastavit Google Analytics kód nebo tracking a jiné kódy.','cms_ve'); ?></p>",
                position: 'bottom'
              },
              {
                element: '.ve_etp_wp',
                intro: "<strong><?php echo __('ADMINISTRACE WORDPRESSU','cms_ve'); ?></strong><p><?php echo __('Tímto tlačítkem se přepnete do administrace wordpressu.','cms_ve'); ?></p>",
                position: 'left'
              },
              {
                element: '.ve_etp_help',
                intro: "<strong><?php echo __('NÁPOVĚDA','cms_ve'); ?></strong><p><?php echo __('Pokud si nebudete s něčím vědět rady, zkuste Nápovědu.','cms_ve'); ?></p>",
                position: 'left'
              },
            ]
          });
          
          intro.start().setOptions({          
              exitOnOverlayClick: false            
          }).onbeforechange(function(targetElement) {          
              jQuery('.intro_tip').removeClass('intro_tip');     
              if(jQuery(targetElement).hasClass("row_edit_bar")) {
                  jQuery(".row_edit_bar").addClass('intro_tip');
              }
              if(jQuery(targetElement).hasClass("ce_editbar")) {
                  jQuery("#element_0_0_0 .content_element_editbar").addClass('intro_tip');
              }
              if(jQuery(targetElement).hasClass("create-new-page")) {
                  jQuery(".ve_top_menu_web ul").addClass('intro_tip');
              }
              if(jQuery(targetElement).hasClass("ev_save_page")) {
                  jQuery('html').animate({scrollTop:0}, 'fast');
                  jQuery('body').animate({scrollTop:0}, 'fast');
              }    
              if(jQuery(targetElement).hasClass("sh-editor-features")) {
                  jQuery('html').animate({scrollTop:0}, 'fast');
                  jQuery('body').animate({scrollTop:0}, 'fast');
              }            
          }).oncomplete(function() {
              end_intro_tut('start_tutorial');
          }).onexit(function() {
              end_intro_tut('start_tutorial');
          });
    }
}