<?php

function field_type_member_levels($field, $meta, $group_name, $group_id) {
    global $member_module;
    
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']: ''); 
    $pages = get_pages(array('post_status'=>'publish'));
    $tagname=$group_name.'['.$field['id'].']';
    $tagid=$group_id.'_'.$field['id'];
    
    $i=0;
    if(isset($content) && $content && is_array($content)) {
        foreach($content as $pid=>$level) { 
            echo '<div class="member_level_row member_level_row_'.$pid.'">';
            $member_module->generate_level($level, $pid, $tagname, $tagid, $pages);     
            echo '</div>';
            if($i<=$pid) $i=$pid+1;               
        }
                
    }    
    $newid=$i; 
    ?>
    <div class="member_add_level_container">
        <input class="cms_text_input member_input_level_name" type="text" placeholder="<?php echo __("Zadejte název členské úrovně","cms_member"); ?>" />
        <button class="member_add_level cms_button_secondary" data-id="<?php echo $newid; ?>" data-name="<?php echo $tagname; ?>"  data-tagid="<?php echo $tagid; ?>"><?php echo __("Přidat členskou úroveň","cms_member"); ?></button>
    </div>
    <?php
}

function field_type_selectmember($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:'');
    $members = get_option('member_basic');
    if(isset($members['members'])) {
        ?>       
        <select name="<?php echo $group_id.'[',$field['id'].']'; ?>" id="<?php echo $group_id.'_'.$field['id']; ?>">
             <?php 
             if(isset($field['empty']))  echo '<option value="" '.(($content==='')? 'selected="selected"': '').'>'.$field['empty'].'</option>';
             foreach($members['members'] as $id=>$member) { 
                echo $id.'-'.$content;
                echo '<option value="'.$id.'" '.(($content!=='' && $content==$id)? 'selected="selected"': '').'>'.$member['name'].'</option>';
             }
             ?>
        </select>
        <?php
    } else{
        echo '<div class="cms_error_box">'.__('Není vytvořena žádná členská sekce. Členskou sekci lze vytvořit v nastavení členské sekce.','cms_member').'</div>';
    }
}

function field_type_member_selectmenu($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:'');
    $members = get_option('member_basic');
    $menus = get_terms( 'nav_menu', array() );
    if(isset($members['members'])) {
        foreach($members['members'] as $id=>$member) {
            echo '<div class="sublabel">'.__('Menu členské sekce:','cms_member').' <strong>'.$member['name'].'</strong></div>';
            cms_generate_field_selectmenu($group_id.'['.$field['id'].']['.$id.']',$group_id.'_'.$field['id'].'_'.$id, $menus, (isset($content[$id]))?$content[$id]:'');   
        } 
    } else echo __('Nejdříve je potřeba vytvořit členskou sekci.','cms_member');
}


function field_type_selectmemberlevel($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']:array());
   
    $members = get_option('member_basic');
    if(isset($members['members'])) {
    ?>       
        <select class="member_select_member_level" name="<?php echo $group_id.'[',$field['id'].'][section]'; ?>" id="<?php echo $group_id.'_'.$field['id'].'_section'; ?>">
             <?php 
             foreach($members['members'] as $id=>$member) { 
                echo '<option value="'.$id.'" '.( isset($content['section']) && ($content['section']==$id)? 'selected="selected"': '').'>'.$member['name'].'</option>';
             }
             ?>
        </select>
    <?php
    $i==1;
    foreach($members['members'] as $id=>$member) { 
        if(isset($member['levels'])) {  ?>

            <div id="member_levels_container_<?php echo $id; ?>" class="member_levels_container <?php if((isset($content['section']) && $i==1) || $content['section']==$id) echo 'member_levels_container_v'; ?>">
                <small><?php echo __('Členské úrovně:','cms_member'); ?></small>
                <?php 
                foreach($member['levels'] as $lid=>$level) {  ?>
                    <div class="member_level_item">
                        <input id="<?php echo $group_id.'_'.$field['id'].'_'.$id.'_levels_'.$lid; ?>" type="checkbox" name="<?php echo $group_id.'[',$field['id'].']['.$id.'][levels]['.$lid.']'; ?>" <?php if(isset($content[$id]) && isset($content[$id]['levels'][$lid])) echo 'checked="checked"'; ?> />
                        <label for="<?php echo $group_id.'_'.$field['id'].'_'.$id.'_levels_'.$lid; ?>">                        
                            <?php echo $level['name']; ?>
                        </label> 
                     </div>       
                <?php } ?>  
                
            </div>
        <?php
        $i++;
        }
    }
    
    } else{
        echo '<div class="cms_error_box">'.__('Není vytvořena žádná členská sekce. Členskou sekci lze vytvořit ve správě členských sekcí.','cms_member').'</div>';
    }
}

// Multi file

function field_type_multifiles($field, $meta, $group_id) {
    global $member_module;
    
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content'] : '');
    $newid=0;

    ?>       
    <div id="member_downloadfiles_container" class="ve_sortable_items">
        <?php
        $i=0;
        if(is_array($content)) {            
            foreach($content as $key=>$file) {
                ?>
                <div id="member_downloadfile_<?php echo $id; ?>" class="member_downloadfile_container ve_item_container ve_setting_container ve_sortable_item">
                    <?php member_generate_downloadfile($group_id.'['.$field['id'].']['.$key.']',$group_id.'_'.$field['id'].'_'.$key,$file); ?>
                </div>
                <?php
                $i++;
            }
        }
        ?>   
    </div>
    <button id="member_add_new_file" class="cms_button_secondary" data-id="<?php echo $i; ?>" data-name="<?php echo $group_id.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Přidat soubor ke stažení','cms_member'); ?></button>
    <?php
}

function member_generate_downloadfile($name,$id,$file) {  
    global $vePage;
    ?>
    <div class="ve_item_head">
        <span class="ve_sortable_handler"></span>
        <?php echo stripslashes($file['name']); ?>
        <a class="member_delete_downloadfile ve_delete_setting" href="#" title="<?php echo __('Smazat soubor','cms_member'); ?>"></a>
    </div>
    <div class="ve_item_body <?php if($file['name']=='') echo 've_item_body_v'; ?>">
        <div class="label"><?php echo __('Název souboru','cms_member'); ?></div>
        <?php echo cms_generate_field_text($name.'[name]',$id.'_name',(isset($file['name']))? stripslashes($file['name']):''); ?>
        <div class="label"><?php echo __('Soubor','cms_member'); ?></div>
        <?php cms_generate_field_upload_file($name.'[file]',$id.'_file',(isset($file['file']))? stripslashes($file['file']):''); ?>
        <div class="label"><?php echo __('Popisek souboru','cms_member'); ?></div>
        <?php cms_generate_field_textarea($name.'[desc]',$id.'_desc',(isset($file['desc']))? stripslashes($file['desc']):''); ?>
        <div class="label"><?php echo __('Ikona','cms_member'); ?></div>
        <?php echo cms_generate_field_imageselect($name.'[icon]',$id.'_icon',$vePage->elements['member_download']['tab_setting'][0]['setting'][0]['options'],$file['icon']); ?>
    </div>  
    <?php
}

function member_generate_downloadfile_ajax() {
    $file=array('icon'=>'1','name'=>'');
    member_generate_downloadfile($_POST['tagname'].'['.$_POST['id'].']',$_POST['tagid'].'_'.$_POST['id'],$file);
    die();
}
add_action('wp_ajax_member_generate_downloadfile_ajax', 'member_generate_downloadfile_ajax');  

// Fapi notification
                                                       
function field_type_fapi_notification($field, $meta, $group_id, $tagid, $post_id, $member_id) {
    $members = get_option('member_basic');
    $fapi_connect=get_option('ve_connect_fapi');
    if(!$fapi_connect['connection']['status']) {
        echo '<div class="cms_error_box">'.__('Aby bylo možné vytvářet nové účty pomocí FAPI, je potřeba nejprve zadat přihlašovací údaje k FAPI v <strong>Nastavení webu -> Propojení aplikací -> FAPI</strong>.','cms_member').'</div><br />'; 
    }

    if(isset($members['members'])) {
        if(isset($members['members'][$member_id])) 
            $member=$members['members'][$member_id];
        else
            $member='';
        $id=$member_id;  
            echo '<div class="member_notification">
                <div class="member_notification_url"><strong>'.home_url().'/?add_new_member='.$id.'</strong></div>';
            if(isset($member['levels'])) {  ?>

                <table class="member_level_notifications_url">
                    <?php foreach($member['levels'] as $lid=>$level) {  
                     echo '<tr>
                        <td class="mlnu_label">'.__('Do členské úrovně:','cms_member').' <strong>'.$level['name'].'</strong></td>
                        <td>'.home_url().'/?add_new_member='.$id.'&level='.$lid.'</td>'; 
      
                    } ?>   
                </table>
            <?php
            }
            
            echo '</div>';
        
    }
    else echo '<div class="cms_error_box">'.__('Není vytvořena žádná členská sekce.','cms_member').'</div>';   
}

function field_type_fapi_notification_log($field, $meta, $group_id, $tagid, $post_id, $member_id) {
    $notifications_option=get_option('mem_notification_debug');

    $notifications=$notifications_option[$member_id];
    
    if($notifications && is_array($notifications)) {
        $notifications=array_reverse($notifications);
        ?>
        <table class="ve_page_statistic_field ve_inside_setting_table">
                <tr>
                    <th><?php echo __('Spuštěno','cms_member'); ?></th>
                    <th><?php echo __('Status','cms_member'); ?></th>
                </tr>
        <?php
        
        $i=1;
        foreach($notifications as $not) {
            if($i) $class='class="odd"';
            else $class='';
            ?>
            
                <tr <?php echo $class; ?>>
                    <td><?php echo date('d.m.Y h:i:s',$not['time']); ?><?php if(isset($not['url'])) echo '<br><small>'.$not['url'].'</small>'; ?></td>
                    <td style="color: <?php echo ($not['status'])?"#4ea600":"#d30000"; ?>;"><?php echo $not['error']; ?></td>
                </tr>
            
            <?php
            $i=($i)? 0:1;
        }
        ?>
        </table>
        <?php
    } else {
        ?>
            <div><?php echo __('Zatím nebyly provedeny žádné notifikace.','cms_member'); ?></div>
        <?php
    }
    
}

            
