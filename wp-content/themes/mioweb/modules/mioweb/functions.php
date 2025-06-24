<?php
function field_type_campaigns($field, $meta, $group_id) {
    global $mioweb_module;
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    $pages = get_pages(array('post_status'=>'publish'));
    $newid=0;
    $campaign_pages=array();
    $conflikt=0;
    ?>       
    <div id="mioweb_select_campaign_container">
        <select id="mioweb_select_campaign" class="<?php if(!is_array($content)) echo 'cms_nodisp'; ?> mioweb_input_campaing_name">
             <?php 
             foreach($content as $id=>$campaign) { 
                echo '<option value="'.$id.'" '.(($content['active']==$id)? 'selected="selected"': '').'>'.$campaign['name'].'</option>';
                $newid=$id+1;
                
                //find page conflikt
                if(isset($campaign_pages[$campaign['squeeze']])) $conflikt=$campaign['squeeze'];
                else $campaign_pages[$campaign['squeeze']]=1;
                foreach($campaign['page'] as $pid=>$page) {
                    if(isset($campaign_pages[$page['page']])) $conflikt=$page['page'];
                    else $campaign_pages[$page['page']]=1;    
                }
             }
             ?>
        </select>
        <button id="mioweb_show_add_new_campaign" class="cms_button_secondary"><?php echo __('Vytvořit novou kampaň','cms_mioweb'); ?></button>
    </div>
    <?php if($conflikt) echo '<div class="cms_error_box campaign_error_box">'.sprintf( __( 'Dochází ke konfliktu stránky %s. Pravděpodobně jste tuto stránku vybrali na více místech kampaně nebo ve více kampaních. Použijte ji jen jednou, jinak nebude kampaň fungovat správně.', 'cms_mioweb' ), '<a target="_blank" href="'.get_permalink($conflikt).'">'.get_the_title($conflikt).'</a>' ).'</div>' ?>
    <div id="mioweb_add_new_container" class="cms_nodisp">
        <input class="cms_text_input mioweb_input_campaing_name" type="text" id="mioweb_add_new_campaing_name" placeholder="<?php echo __('Zadejte název nové kampaně','cms_mioweb'); ?>" />
        <button id="mioweb_save_campaign" class="cms_button_secondary" data-id="<?php echo $newid; ?>" data-name="<?php echo $group_id.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Vytvořit kampaň','cms_mioweb'); ?></button>
        <button id="mioweb_storno_new_campaign" class="cms_button_secondary"><?php echo __('Storno','cms_mioweb'); ?></button>
    </div>
    <div id="mioweb_campaigns_container">
    <?php
    if(is_array($content)) {
      $i=0;
      foreach($content as $id=>$campaign) {
          echo '<div id="mioweb_campaign_'.$id.'" class="mioweb_campaign '.(($i==0)? 'mioweb_campaign_v':'').'">';
          $mioweb_module->generate_campaing_setting($campaign, $id, $pages, $group_id.'['.$field['id'].']['.$id.']',$group_id.'_'.$field['id'].'_'.$id);  
          echo '</div>';  
          $i++;
      }
    }
    ?>
    <input type="hidden" name="mioweb_save_campaign_setting" value="" />
    </div>
    <script>
    jQuery(document).ready(function($) {
        $("#mioweb_campaign_"+$("#mioweb_select_campaign").val()).show();
    });
    </script>
    <?php
}

function field_type_selectcampaign($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:'');
    $campaigns = get_option('campaign_basic');
    if(isset($campaigns['campaigns'])) {
    ?>       
        <select name="<?php echo $group_id.'[',$field['id'].']'; ?>" id="<?php echo $group_id.'_'.$field['id']; ?>">
             <?php 
             foreach($campaigns['campaigns'] as $id=>$campaign) { 
                echo '<option value="'.$id.'" '.(($content==$id)? 'selected="selected"': '').'>'.$campaign['name'].'</option>';
             }
             ?>
        </select>
    <?php
    } else{
        echo '<div class="cms_error_box">'.__('Není vytvořena žádná kampaň. Kampaň lze vytvořit v nastavení webu v záložce kampaně.','cms_mioweb').'</div>';
    }
}

