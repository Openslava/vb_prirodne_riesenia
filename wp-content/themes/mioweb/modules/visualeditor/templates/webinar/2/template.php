<?php 
/**
 * Template Title: Registrace na webinář 2
 * Template Description: Registrace na webinář s popisem a účastníky. 
 */
  __('Registrace na webinář 2','cms_ve');
__('Registrace na webinář s popisem a účastníky.','cms_ve');
global $vePage;
if(isset($vePage->page_setting['page_width']['size']) && $vePage->page_setting['page_width']['size']) {
    $vePage->add_style(".row, .row_fix_width, .fix_width,#row_0 .row_fix_width, #row_0 .fix_width",array(
          'max-width'=>$vePage->page_setting['page_width']['size'].$vePage->page_setting['page_width']['unit'],
    ));   
}

the_content(); 
        

