<?php
// title
function field_type_title($field, $meta) {
    echo '<h4>'.$field['name'].'</h4>';
}

// text
function field_type_text($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    echo cms_generate_field_text($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],htmlspecialchars(stripslashes($content)),'',$field);
}

function cms_generate_field_text($name,$id,$value,$class='', $field=array()) {
    return '<input class="cms_text_input '.$class.'" type="text" name="'.$name.'" id="'.$id.'" '
        .(isset($field['placeholder'])? 'placeholder="'.htmlspecialchars(stripslashes($field['placeholder'])).'"' : '')
        .' value="'.$value.'" />';
}

// file
function field_type_file($field, $meta, $group_name, $group_id) {
    echo '<input type="file" id="'.$group_id.'_'.$field['id'].'" name="'.$field['id'].'" multiple="false" />';
}

// hidden input
function field_type_hidden($field, $meta, $group_name, $group_id) {
    echo '<input type="hidden" id="'.$group_id.'_'.$field['id'].'" name="'.$field['id'].'" value="'.$field['content'].'" />';
}

// info
function field_type_info($field, $meta, $group_id) {
    if(!isset($field['color'])) $field['color']='gray';
    $class="cms_message_box";
    if($field['color']=='gray') $class.=" cms_info_box_gray";
    else $class.=" cms_info_box";
    echo '<div class="'.$class.'">'.$field['content'].'</div>';
}

// static, non-editable
function field_type_static($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    echo '<div class="cms_static">'.$content.'</div>';
}

/**
 * Generate NUMBER editor.
 * @param $field array          Supported options: id, content, unit, min, step, placeholder
 * @param $meta array|number    Value of number
 * @param $group_id
 */
function field_type_number($field, $meta, $group_name, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    $content='<input class="cms_text_input cms_text_input_size_auto cms_text_input_size" type="number" name="'.$group_name.'['.$field['id'].']"'
        .' id="'.$group_id.'_'.$field['id'].'"'
        .' value="'.$content.'"'
        .(isset($field['placeholder'])?' placeholder="'.esc_attr($field['placeholder']).'"':'')
        .(isset($field['min'])?' min="'.esc_attr((float)$field['min']).'"':'')
        .(isset($field['step'])?' step="'.esc_attr((string)$field['step']).'"':'')
        .' />';
    if(isset($field['unit']))
        $content .= ' '.$field['unit'];

    echo $content;
}

// id generator for elements savign and writing user data
function field_type_id_generator($field, $meta, $group_name, $group_id) {
    $content=(isset($meta) && $meta)? $meta: $field['id'].'_'.md5(microtime());
    echo '<input type="hidden" name="'.$group_name.'['.$field['id'].']" id="'.$group_id.'_'.$field['id'].'" value="'.$content.'" />';
}

// size
function field_type_size($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: array('size'=>'','unit'=>'px'));
    if(is_array($content)) echo cms_generate_field_size($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field);
    else echo cms_generate_field_simple_size($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field);
}
function cms_generate_field_size($name,$id,$value, $field, $units=array('px','%')) {
    $content='<input class="cms_text_input cms_text_input_size" type="text" name="'.$name.'[size]" id="'.$id.'_size" value="'.$value['size'].'" />';
    if(!isset($field['unit'])) {
        $content.='<select name="'.$name.'[unit]" id="'.$id.'_unit">';
            foreach($units as $unit) {
                $content.='<option value="'.$unit.'" '.(($value['unit']==$unit)? 'selected="selected"':'').'>'.$unit.'</option>';
            }
        $content.='</select>';
    } else $content.=' '.$field['unit'];
    return $content;
}
function cms_generate_field_simple_size($name,$id,$value, $field) {
    $placeholder = isset($field['placeholder']) ? 'placeholder="'.htmlspecialchars(stripslashes($field['placeholder'])).'"' : '';
    return '<input class="cms_text_input cms_text_input_size" type="text" name="'.$name.'" id="'.$id.'" ' .
        'value="'.$value.'" '.$placeholder.' /> '.$field['unit'];
}

//password
function field_type_password($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    echo cms_generate_field_password($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],stripslashes($content));
}
function cms_generate_field_password($name,$id,$value,$class='') {
    return '<input class="cms_text_input '.$class.'" type="password" autocomplete="off" name="'.$name.'" id="'.$id.'" value="'.$value.'" />';
}

//date
function field_type_date($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    if(isset($field['convert']) && $content) $content=date( 'd.m.Y', $content );
    echo cms_generate_field_text($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],stripslashes($content),'cms_datepicker',$field);
}

//datetime
function field_type_datetime($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: array('date'=>'','hour'=>'','minute'=>''));
    cms_generate_field_datetime($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content);
}
function cms_generate_field_datetime($name,$id,$value,$class='') {
    echo '<div class="float-setting"><div class="sublabel">'.__('Datum','cms').'</div>';
    echo cms_generate_field_text($name.'[date]',$id.'_date',((isset($value['date']))? stripslashes($value['date']):''),'cms_datepicker');
    echo '</div><div class="float-setting"><div class="sublabel">'.__('Hodin','cms').'</div>';
    echo '<select name="'.$name.'[hour]">';
    for($i=0;$i<25;$i++) {
        echo '<option '.(($value['hour']==$i)? 'selected="selected"':'').' value="'.$i.'">'.$i.'</option>';
    }
    echo '</select></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Minut','cms').'</div>';
    echo '<select name="'.$name.'[minute]">';
    for($i=0;$i<60;$i++) {
        echo '<option '.(($value['minute']==$i)? 'selected="selected"':'').' value="'.$i.'">'.$i.'</option>';
    }
    echo '</select></div><div class="cms_clear"></div>';
}

//licence
function field_type_license($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    echo '<input class="cms_text_input" type="text" name="',$group_id,'[',$field['id'],']" id="',$group_id,'_', $field['id'],'" value="', stripslashes($content) , '" />';
    cms_check_license_code($content);
}

//text editor
function field_type_editor($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    wp_editor( stripslashes($content), $group_id.'_'.$field['id'], array('textarea_name' => $group_id.'['.$field['id'].']') );
    ?>
    <script>
    jQuery(document).ready(function($) {      
          tinymce.EditorManager.execCommand('mceAddEditor', true, '<?php echo $group_id.'_'.$field['id']; ?>'); 
          quicktags({id: '<?php echo $group_id.'_'.$field['id']; ?>'});
          QTags._buttonsInit(); 
     });          
    </script>
    <?php
}

//checkbox
function field_type_checkbox($field, $meta, $group_id, $tagid) {  
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $("#<?php echo $tagid.'_'. $field['id']; ?>").change(function(){ 
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").toggle();
          });
      });
      </script>      
    <?php }
    if((!$content && !isset($field['show_type'])) || (isset($content) && $content && isset($field['show_type']))) {
        if(!isset($field['show'])) $field['show']='';
    ?>
    <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?> {display: none;} 
      </style>
    <?php
    }
    cms_generate_field_checkbox($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$content,$field['label']);    
}
function cms_generate_field_checkbox($name,$id,$content,$label='',$class='') {
    echo '<input value="1" type="checkbox" name="', $name,'" id="',$id,'"', ($content ? ' checked="checked"' : ''),
      ($class ? ' class="'.$class.'"': ''), ' />';
    if($label) echo '<label for="'.$id.'">'.$label.'</label>';
}

// multiple checkbox

function field_type_multiple_checkbox($field, $meta, $group_name, $group_id) { 
   $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
   if(!$content) $content=array();
   echo '<input type="hidden" name="',$group_name,'[',$field['id'],'][is_saved]" value="1" checked="checked" />';
   foreach ($field['options'] as $key=>$option) {     
      echo '<div><input type="checkbox" id="',$field['id'],'_',$option['value'],'" name="',$group_name,'[',$field['id'],'][',$option['value'],']" value="',$option['value'],'"', in_array($option['value'],$content) ? ' checked="checked"' : '', ' />';
      echo '<label for="',$field['id'],'_',$option['value'],'"> ',$option['name'], '</label></div>';
   }
}

//textarea
function field_type_textarea($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');                    
    cms_generate_field_textarea($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content);
}
function cms_generate_field_textarea($name,$id,$content,$class='') {
    echo '<textarea class="cms_text_textarea '.$class.'" name="'.$name.'" id="'.$id.'" rows="4">'.htmlspecialchars(stripslashes($content)).'</textarea>';
}

//select
function field_type_select($field, $meta, $group_id, $tagid) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');  
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $("#<?php echo $tagid.'_'. $field['id']; ?>").change(
          function(){ 
              var value=$(this).val();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>:not(.cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?>) {display: none;} 
      </style>
    <?php }
    cms_generate_field_select($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field);
} 
function cms_generate_field_select($name,$id,$content,$field,$class='') {
    echo '<select class="'.$class.'" name="'.$name.'" id="'.$id.'">';
    foreach ($field['options'] as $option) {
        echo '<option value="'.$option['value'].'" '. ($content == $option['value'] ? ' selected="selected"' : ''). '>'. $option['name']. '</option>';
    }
    echo '</select>'; 
}
//sidebar select
function field_type_sidebarselect($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    echo '<select name="',$group_id,'[',$field['id'],']" id="',$group_id,'_', $field['id'],'">';
    global $cms;
    foreach ($cms->sidebars as $option) {
        echo '<option value="',$option['id'],'" ', $content == $option['id'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
    }
    echo '</select>';   
} 

//link
function field_type_link($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    $target=(isset($field['target']) ? $field['target'] : true);
    cms_generate_field_link($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, '',$target);
}
function cms_generate_field_link($name,$id,$content,$class='', $target=true) {  
    echo '<input class="cms_text_input ',$class,'" type="text" name="',$name,'[link]" id="',$id,'_link" value="', isset($content['link']) ? stripslashes($content['link']) : '' , '" />';
    if($target) echo '<input value="1" type="checkbox" name="', $name,'[target]" id="',$id,'_target"', isset($content['target']) ? ' checked="checked"' : '', ' /><label for="',$id,'_target">',__('Otevřít v novém okně', 'cms'),'</label>';
}

function field_type_page_link($field, $meta, $group_id, $tagid) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    $target=(isset($field['target']) ? $field['target'] : true);
    
    if(!is_array($content)) {
        $old=$content;
        $content=array();
        $content['link']=$old;
    }
    
    if(!isset($content['page'])) {
        $content['page']='';
        if(isset($content['link']) && $content['link']) $content['use_url']=1;
    }
    
    cms_generate_field_page_link($group_id.'['.$field['id'].']', $group_id.'_'.$field['id'],$content, '', $target);
}

function cms_generate_field_page_link($name, $id, $content, $class='', $target=true) {  
 
  global $cms;
  $pages = get_pages(array('post_status'=>'publish'));
  ?>
  <div class="field_link_container">
      <div class="float-setting fl_page_selector_container <?php echo isset($content['use_url']) ? 'cms_nodisp' : ''; ?>">
          <?php $cms->select_page($pages, $content['page'], $name.'[page]',$id.'_page'); ?>
      </div>
      <div class="float-setting fl_custom_url_container <?php echo !isset($content['use_url']) ? 'cms_nodisp' : ''; ?>">
          <input class="cms_text_input <?php echo $class; ?>" type="text" name="<?php echo $name; ?>[link]" id="<?php echo $id; ?>_link" value="<?php echo isset($content['link']) ? stripslashes($content['link']) : ''; ?>" placeholder="<?php echo __('Zadejte URL včetně http://', 'cms'); ?>" />
      </div>
      <div class="float-setting">
          <div><input class="fl_switch_url_type" value="1" type="checkbox" name="<?php echo $name; ?>[use_url]" id="<?php echo $id; ?>_use_url"<?php echo isset($content['use_url']) ? ' checked="checked"' : ''; ?>/><label for="<?php echo $id; ?>_use_url"><?php echo __('Zadat vlastní URL', 'cms'); ?></label></div>      
          <?php if($target) echo '<input value="1" type="checkbox" name="', $name,'[target]" id="',$id,'_target"', isset($content['target']) ? ' checked="checked"' : '', ' /><label for="',$id,'_target">',__('Otevřít v novém okně', 'cms'),'</label>'; ?>
      </div>
      <div class="cms_clear"></div>
  </div>
  <?php
}

function field_type_permalink($field, $meta, $group_id, $tagid) {
    $content = (isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    $placeholder = (isset($field['placeholder'])) ? $field['placeholder'] : '';
    $nested_text = (isset($field['nested_text']) ? $field['nested_text'] : '');
    $use_nested = (isset($content['use_nested']) ? true : false);
    $attrBaseUri = ' data-base-uri="'.htmlspecialchars(get_home_url()).'"';
    $previewSpan = '<span class="field_permalink_preview" title="'.esc_attr(__('Náhled URL', 'mwshop')).'"></span>';
    $script = '';

    $baseName = $group_id.'['.$field['id'].']';
    $baseId = $group_id.'_'.$field['id'];

    echo '
    <div class="field_permalink_container field_permalink_id_'.$field['id'].'" id="'.$baseId.'" '.$attrBaseUri.'>
      <input type="text" name="'.$baseName.'[value]" id="'.$baseId.'_basic"
             '. (!empty($placeholder) ? 'placeholder="'.esc_attr($placeholder).'"' : '') .'
             value="'.(isset($content['value'])?$content['value']:'').'"
             class="cms_text_input cms_text_input_third field_permalink_basic '.($use_nested?'cms_nodisp':'').'"
        >
      ';

    if($nested_text) {
      $nestedParentPermalinkId = ($nested_text && isset($field['nested_parent_permalink'])) ? $field['nested_parent_permalink'] : '';
      $nested_placeholder = ($nested_text && isset($field['nested_placeholder'])) ? $field['nested_placeholder'] : '';
      echo '
        <input type="text" name="'.$baseName.'[value_nested]" id="'.$baseId.'_nested"
            ' . (!empty($nested_placeholder) ? 'placeholder="'.esc_attr($nested_placeholder).'"' : '') .'
            value="'.(isset($content['value_nested'])?$content['value_nested']:'').'"
           '.(isset($nestedParentPermalinkId) ? 'data-parent-id="'.esc_attr($nestedParentPermalinkId).'"' : '').'
           class="cms_text_input cms_text_input_third field_permalink_nested '.(!$use_nested?'cms_nodisp':'').'"
        >
        '.$previewSpan
        .'<div class="cms_clear"></div>'
      ;
      cms_generate_field_checkbox($baseName.'[use_nested]', $baseId.'_use_nested',
        (isset($content['use_nested'])?$content['use_nested']:''), $nested_text, 'field_permalink_use_nested');
      $script .= 'jQuery(document).ready(function($) {
        $("#'.$baseId.'_nested").keyup();
        $("#'.$baseId.'_use_nested").change();
      });
      ';
    } else {
      echo $previewSpan;
      $script .= 'jQuery(document).ready(function($) {
        $("#'.$baseId.'_basic").keyup();
      });
      ';
    }
    echo '
    </div>
    '.($script ? '' : '');

}

//image select
function field_type_imageselect($field, $meta, $group_id,$tagid) {
    global $vePage;
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $("#cms_image_selector_<?php echo $tagid.'_'.$field['id']; ?> a").click(
          function(){ 
              var value=$(this).attr('data-value');
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?> {display: none;} 
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?> {display: table-row;}   
      </style>
    <?php }
    $options=(isset($field['set_list']))? $vePage->set_list[$field['set_list']]:$field['options'];
    cms_generate_field_imageselect($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$options,$content);
}
function cms_generate_field_imageselect($name,$id,$fields,$content) {
    ?>
    <div id="cms_image_select_<?php echo $id; ?>" class="cms_style_selector_container cms_image_select">
    <div class="cms_image_selected cms_open_style_selector">
        <?php 
        if(is_array($fields[$content])) $current=$fields[$content]['thumb']; 
        else $current=$fields[$content]; 
        
        ?>
        <div class="cms_image_select_container"> <img src="<?php echo $current; ?>" alt="" /></div>
        <?php echo '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.$content.'" />'; ?>    
        <a class="cms_image_select_arr cms_image_select_oc" href="#"></a>        
    </div>
    <div class="cms_style_selector_bg cms_close_style_selector"></div>
    <div id="cms_image_selector_<?php echo $id; ?>" class="cms_style_selector">
    <?php
    foreach ($fields as $key=>$val) {
        echo '<div id="cms_is_item_'.$id.'_'.$key.'" class="cms_is_item '.(($content==$key)?"cms_is_item_active":"").'">';
        if(is_array($val)) $value=$val['thumb']; 
        else $value=$val;
        echo '<a class="cms_close_style_selector" href="#" data-value="'.$key.'" data-group="'.$id.'"><img src="'.$value.'" alt=""></a>';
        echo '</div>';
    }
    ?>
    <div class="cms_clear"></div>
    </div>
    
    </div>
    <?php
}

//image option
function field_type_imageoption($field, $meta, $group_id,$tagid) {
    global $vePage;
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: 'right');
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $("#cms_image_options_<?php echo $tagid.'_'.$field['id']; ?> a").click(
          function(){ 
              var value=$(this).attr('data-value');
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?> {display: none;} 
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?> {display: table-row;}   
      </style>
    <?php }
    $options=(isset($field['set_list']))? $vePage->set_list[$field['set_list']]:$field['options'];
    cms_generate_field_imageoption($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$options,$content);
}
function cms_generate_field_imageoption($name,$id,$options,$content) {
    ?>
    <div id="cms_image_options_<?php echo $id; ?>" class="cms_style_options_container">
    <?php
    foreach ($options as $key=>$val) {
        echo '<a id="cms_image_option_item_'.$id.'_'.$key.'" class="cms_image_option_item '.(($content==$key)?"cms_current_image_option_item":"").'" href="#" data-value="'.$key.'" data-group="'.$id.'">
        <img src="'.$val['image'].'" alt="">
        <input type="radio" name="'.$name.'" value="'.$key.'" '.(($content==$key)?'checked="checked"':"").' />
        </a>'; 
    }
    ?>
    <div class="cms_clear"></div>
    </div>
    <?php
}

// svg icon select
function field_type_svg_iconselect($field, $meta, $group_id,$tagid) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $("#cms_image_selector_<?php echo $tagid.'_'.$field['id']; ?> a").click(
          function(){ 
              var value=$(this).attr('data-value');
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?> {display: none;} 
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?> {display: table-row;}   
      </style>
    <?php }
    cms_generate_field_svg_iconselect($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$field,$content);
}
function cms_generate_field_svg_iconselect($name,$id,$field,$content) {     
    $icons=$field['icons'];
    
    $max_size=(isset($field['setting']['max-size']))? $field['setting']['max-size']:'150';
    ?>
    <div id="cms_icon_select_<?php echo $id; ?>" class="cms_icon_select cms_svg_icon_select">
    <div class="cms_icon_selected_setting cms_style_selector_container">
        <div class="cms_icon_preview_<?php echo $id; ?> cms_icon_preview">
            <div class="cms_icon_background<?php if(!isset($content['showbg'])) echo ' cms_icon_background_hide'; ?>"><?php include($icons[$content['icon']].$content['icon'].".svg"); ?></div> 
        </div>      
        <div class="cms_icon_select_setting_container">
        
            <ul class="cms_small_tabs">                
                <li class="cms_tab ft_is_<?php echo $id; ?>_tab">
                    <a class="active" data-group="ft_is_<?php echo $id; ?>" href="#select_ft_is_<?php echo $id; ?>_1"><?php echo __('Ikona','cms') ?></a>
                </li>
                <?php if(isset($field['content']['background'])) { ?>
                <li class="cms_tab ft_is_<?php echo $id; ?>_tab">
                    <a data-group="ft_is_<?php echo $id; ?>" href="#select_ft_is_<?php echo $id; ?>_2"><?php echo __('Pozadí ikony','cms') ?></a>
                </li>
                <?php }
                if(isset($field['content']['hover'])) { ?>
                <li class="cms_tab ft_is_<?php echo $id; ?>_tab">
                    <a data-group="ft_is_<?php echo $id; ?>" href="#select_ft_is_<?php echo $id; ?>_3"><?php echo __('Hover','cms') ?></a>
                </li>
                <?php } ?>
            </ul>
            
            <div id="select_ft_is_<?php echo $id; ?>_1" class="cms_setting_block_content cms_tab_container ft_is_<?php echo $id; ?>_container" style="display: block;">

                <a class="cms_svg_icon_selector cms_open_style_selector">
                    <div class="cms_change_icon_container_<?php echo $id; ?> cms_icon_select_container"><?php include($icons[$content['icon']].$content['icon'].".svg"); ?></div>
                    <?php echo '<input type="hidden" id="'.$id.'" name="'.$name.'[icon]" value="'.$content['icon'].'" />'; ?>    
                    <input type="hidden" id="<?php echo $id.'_code'; ?>" name="<?php echo $name.'[code]'; ?>" value='<?php include($icons[$content['icon']].$content['icon'].".svg"); ?>' />         
                </a> 
                <div class="ve_half_set">
                    <div class="sublabel"><?php echo __('Barva ikony','cms'); ?></div>
                    <input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[color]'; ?>" id="<?php echo $id.'_color'; ?>" value="<?php echo $content['color']; ?>" />
                </div>
                <div class="ve_half_set">
                    <div class="sublabel"><?php echo __('Velikost ikony','cms'); ?></div>
                    <?php 
                    $size=(isset($content['size']))? $content['size']:'0';
                    $script='var container=jQuery(this).closest(".cms_icon_selected_setting");
                            jQuery(".cms_icon_preview .cms_icon_background svg",container).css("width", ui.value+"px");
                            jQuery(".cms_icon_preview .cms_icon_background svg",container).css("height", ui.value+"px");
                            jQuery(".cms_icon_preview .cms_icon_background",container).css("width", ui.value+"px");
                            jQuery(".cms_icon_preview",container).css("font-size",ui.value+"px");'; 
                    $script='jQuery(".cms_icon_preview_'.$id.' .cms_icon_background svg").css("width", ui.value+"px");
                            jQuery(".cms_icon_preview_'.$id.' .cms_icon_background svg").css("height", ui.value+"px");
                            jQuery(".cms_icon_preview_'.$id.' .cms_icon_background").css("width", ui.value+"px");
                            jQuery(".cms_icon_preview_'.$id.'").css("font-size",ui.value+"px");';            
                    cms_generate_field_slider($name.'[size]',$id.'_size',$size, array('setting'=>array('min'=>'15','max'=>$max_size,'unit'=>'px')),$script); 
                    ?> 
                </div>
                <div class="cms_clear"></div>
            </div>
            <div id="select_ft_is_<?php echo $id; ?>_2" class="cms_setting_block_content cms_tab_container ft_is_<?php echo $id; ?>_container">
                <?php 
                
                // background
                
                if(isset($field['content']['background'])) { ?>
                <div class="sublabel"><input type="checkbox" class="cms_icon_use_background" name="<?php echo $name.'[showbg]'; ?>" id="<?php echo $id.'_showbg'; ?>" value="1" <?php if(isset($content['showbg'])) echo 'checked="checked"'; ?> /><label for="<?php echo $id.'_showbg'; ?>">Zobrazit pozadí ikonky</label></div>
                <div class="cms-icon-bg-setting">    
                    <div class="sublabel"><?php echo __('Barva pozadí ikony','cms'); ?></div>
                    <input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[background]'; ?>" id="<?php echo $id.'_background'; ?>" value="<?php echo $content['background']; ?>" />
                </div>
                <?php }
                if(isset($field['content']['corner'])) { ?>
                <div class="ve_half_set cms-icon-bg-setting">    
                    <div class="sublabel"><?php echo __('Zakulacení rohů','cms'); ?></div>
                    <?php 
                    $corner=(isset($content['corner']))? $content['corner']:'0';
                    $script='var container=jQuery(this).closest(".cms_icon_selected_setting");
                            jQuery(".cms_icon_background",container).css("-moz-border-radius",ui.value+"px"); 
                            jQuery(".cms_icon_background",container).css("-webkit-border-radius",ui.value+"px");
                            jQuery(".cms_icon_background",container).css("-khtml-border-radius",ui.value+"px");
                            jQuery(".cms_icon_background",container).css("border-radius",ui.value+"px");';            
                    cms_generate_field_slider($name.'[corner]',$id.'_corner',$corner, array('setting'=>array('min'=>'0','max'=>$max_size,'unit'=>'px')),$script); 
                    ?> 
                </div>
                <?php } 
                if(isset($field['content']['bg-padding'])) { ?>
                <div class="ve_half_set cms-icon-bg-setting">    
                    <div class="sublabel"><?php echo __('Odsazení pozadí','cms'); ?></div>
                    <?php 
                    $bg_padding=(isset($content['bg-padding']))? $content['bg-padding']:'0.4';
                    $script='var container=jQuery(this).closest(".cms_icon_selected_setting");
                            jQuery(".cms_icon_background",container).css("padding",(ui.value)+"em");';           
                    cms_generate_field_slider($name.'[bg-padding]',$id.'_bg_padding',$bg_padding, array('setting'=>array('min'=>'0','max'=>'2', 'step'=> '0.1','unit'=>'em')),$script); 
                    ?> 
                </div>
                <?php } else $bg_padding='0.4';?>
                
                <div class="cms_clear"></div>
                <?php 
                
                // border
                
                if(isset($field['content']['border-color'])) { ?>
                <div class="ve_half_set cms-icon-bg-setting">    
                    <div class="sublabel"><?php echo __('Barva ohraničení','cms'); ?></div>
                    <input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[border-color]'; ?>" id="<?php echo $id.'_border_color'; ?>" value="<?php echo $content['border-color']; ?>" />
                </div>
                <?php }
                if(isset($field['content']['border-size'])) { ?>
                <div class="ve_half_set cms-icon-bg-setting">    
                    <div class="sublabel"><?php echo __('Tloušťka ohraničení','cms'); ?></div>
                    <?php 
                    $border_size=(isset($content['border-size']))? $content['border-size']:'0';
                    $script='var container=jQuery(this).closest(".cms_icon_selected_setting");
                            jQuery(".cms_icon_background",container).css("border-width",ui.value+"px");';            
                    cms_generate_field_slider($name.'[border-size]',$id.'_border_size',$border_size, array('setting'=>array('min'=>'0','max'=>'5','unit'=>'px')),$script); 
                    ?> 
                </div>
                <?php } ?>
                <div class="cms_clear"></div>
            </div>
            <div id="select_ft_is_<?php echo $id; ?>_3" class="cms_setting_block_content cms_tab_container ft_is_<?php echo $id; ?>_container">
                <?php 
                // hover
                if(isset($field['content']['hover'])) {
                
                    ?>
                    <div class="sublabel"><?php echo __('Barva ikony po najetí myši','cms'); ?></div>
                    <input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[color-hover]'; ?>" id="<?php echo $id.'_color_hover'; ?>" value="<?php if(isset($content['color-hover'])) echo $content['color-hover']; ?>" />
                    <?php
                    
                    if(isset($field['content']['background'])) { ?>  
                        <div class="cms-icon-bg-setting">
                        <div class="sublabel"><?php echo __('Barva pozadí po najetí myši','cms'); ?></div>
                        <input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[background-hover]'; ?>" id="<?php echo $id.'_background_hover'; ?>" value="<?php if(isset($content['background-hover'])) echo $content['background-hover']; ?>" />
                        </div>
                    <?php }
                    
                    if(isset($field['content']['border-color'])) { ?>  
                        <div class="cms-icon-bg-setting">
                        <div class="sublabel"><?php echo __('Barva ohraničení po najetí myši','cms'); ?></div>
                        <input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[border-color-hover]'; ?>" id="<?php echo $id.'_border_color_hover'; ?>" value="<?php if(isset($content['border-color-hover'])) echo $content['border-color-hover']; ?>" />
                        </div>
                    <?php }
                
                }
                ?>
                <div class="cms_clear"></div>
            </div>
        </div>
        <div class="cms_clear"></div>
        <div class="cms_style_selector_bg cms_close_style_selector"></div>
        <div id="cms_icon_selector_<?php echo $id; ?>" class="cms_style_selector">
            <?php
            foreach ($icons as $key=>$val) {
                echo '<div id="cms_icon_item_'.$id.'_'.$key.'" data-value="'.$key.'" data-group="'.$id.'" class="cms_close_style_selector cms_svg_icon_item '.(($content['icon']==$key)?"cms_icon_item_active":"").'">';
                ?>
                <?php include($val.$key.".svg"); ?>
                <?php
                echo '</div>';
            }
            ?>
            <div class="cms_clear"></div>
        </div>
    </div>
    
    
    </div>
    <style>
    .cms_icon_preview_<?php echo $id; ?> { 
        font-size: <?php echo $content['size']; ?>px;        
    }
    .cms_icon_preview_<?php echo $id; ?> .cms_icon_background {
        width: <?php echo $content['size']; ?>px; 
        padding:<?php echo $bg_padding; ?>em;
        <?php 
        if(isset($content['background'])) echo 'background-color: '.$content['background'].';';
        if(isset($corner) && $corner) {
            echo '-moz-border-radius: '.$corner.'px; 
            -webkit-border-radius: '.$corner.'px;
            -khtml-border-radius: '.$corner.'px;
            border-radius:'.$corner.'px;';
        }       
        if(isset($content['border-color'])) echo 'border-color: '.$content['border-color'].';';    
        if(isset($content['border-size'])) echo 'border-width: '.$content['border-size'].'px;';   
        ?>       
    }
    .cms_icon_preview_<?php echo $id; ?> svg {
        width: <?php echo $content['size']; ?>px;
        height: <?php echo $content['size']; ?>px;                
    }
    .cms_icon_preview_<?php echo $id; ?> svg path {
        fill: <?php echo $content['color']; ?>;
    }
    <?php if(!isset($content['showbg'])) {
       echo '#cms_icon_select_'.$id.' .cms-icon-bg-setting {display: none;}';    
    } ?>
    </style>
          <script>

              
              jQuery(document).ready(function($) {
                  $("#<?php echo $id; ?>_color").on("change paste keyup", function() {
                      $(".cms_icon_preview_<?php echo $id; ?> svg path").css( 'fill', $(this).val() );
                  }); 
                  $("#<?php echo $id; ?>_background").on("change paste keyup", function() {
                      var color=$(this).val();
                      if($(this).val()=='') color='transparent';
                      $(".cms_icon_preview_<?php echo $id; ?> .cms_icon_background").css( 'background-color', color );
                  }); 
                  $("#<?php echo $id; ?>_border_color").on("change paste keyup", function() {
                      $(".cms_icon_preview_<?php echo $id; ?> .cms_icon_background").css( 'border-color', $(this).val() );
                  }); 
              });
              

          </script>
    <?php
}

// svg icon select
function field_type_simple_iconselect($field, $meta, $group_id, $tagid) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_simple_iconselect($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$field,$content);
}
function cms_generate_field_simple_iconselect($name,$id,$field,$content) {     
    $icons=$field['icons'];
    ?>
    <div class="cms_simple_icon_selected_setting cms_style_selector_container">
        <a class="cms_svg_icon_selector cms_open_style_selector">
            <div class="cms_change_icon_container_<?php echo $id; ?> cms_icon_select_container"><?php include($icons[$content['icon']].$content['icon'].".svg"); ?></div>
            <?php echo '<input type="hidden" id="'.$id.'" name="'.$name.'[icon]" value="'.$content['icon'].'" />'; ?>    
            <input type="hidden" id="<?php echo $id.'_code'; ?>" name="<?php echo $name.'[code]'; ?>" value='<?php include($icons[$content['icon']].$content['icon'].".svg"); ?>' />         
        </a> 
        <div class="cms_style_selector_bg cms_close_style_selector"></div>
        <div id="cms_icon_selector_<?php echo $id; ?>" class="cms_style_selector">
            <?php
            foreach ($icons as $key=>$val) {
                echo '<div id="cms_icon_item_'.$id.'_'.$key.'" data-value="'.$key.'" data-group="'.$id.'" class="cms_close_style_selector cms_svg_icon_item '.(($content['icon']==$key)?"cms_icon_item_active":"").'">';
                ?>
                <?php include($val.$key.".svg"); ?>
                <?php
                echo '</div>';
            }
            ?>
            <div class="cms_clear"></div>
        </div>
    </div>

    <?php
}


//icon select
function field_type_iconselect($field, $meta, $group_id,$tagid) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $("#cms_image_selector_<?php echo $tagid.'_'.$field['id']; ?> a").click(
          function(){ 
              var value=$(this).attr('data-value');
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?> {display: none;} 
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?> {display: table-row;}   
      </style>
    <?php }
    cms_generate_field_iconselect($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$content);
}
function cms_generate_field_iconselect($name,$id,$content) {     

    $icons=array( 'glass','wallet','search','mail-1','mail-alt','heart-1','heart-empty','star-1','star-empty','user','users','male','female','video','videocam-1','picture','camera','camera-alt','ok','cancel','help','home','link','attach','lock-1','lock-open','lock-open-alt',
    'pin','eye-1','eye-off','tag','tags','flag','flag-empty','flag-checkered','thumbs-up','thumbs-down','thumbs-up-alt','thumbs-down-alt','download','upload','download-cloud','upload-cloud','quote-left','quote-right','code','pencil','pencil-squared',
    'print','retweet','keyboard','gamepad','comment','chat','comment-empty','chat-empty','bell','bell-alt','attention-alt','attention','location','direction','compass','trash','doc','docs','doc-text','doc-inv','doc-text-inv','folder','folder-open','folder-empty',
    'folder-open-empty','box','rss','rss-squared','phone','phone-squared','cog','cog-alt','wrench','basket','calendar','calendar-empty','login','logout','mic','mute','volume-off','volume-down','volume-up','headphones','clock','lightbulb-1','cw','arrows-cw','play','play-circled','play-circled2','target','signal','award','desktop','laptop','tablet','mobile','inbox-1','globe','sun','cloud-1','flash','moon','umbrella','flight','fighter-jet','leaf','table','scissors','briefcase','suitcase','off','road','qrcode','barcode',
    'book-1','tint','asterisk','gift','magnet','ticket','credit-card','floppy-1','megaphone','hdd','key-1','music','rocket','bug','certificate','filter','beaker-1','magic','truck-1','money-1','euro','pound','dollar','sort','hammer','gauge','sitemap','coffee','food','beer','user-md','stethoscope',
    'ambulance','medkit','h-sigh','hospital','building','anchor','terminal','puzzle','shield','extinguisher','bullseye','android','apple','bitbucket','facebook','facebook-squared','gittip','gplus-squared','gplus','html5','instagramm','linkedin-squared','linkedin','tumblr','tumblr-squared','twitter-squared','twitter','windows','youtube','youtube-squared','youtube-play','note','note-beamed','music-2','user-2','users-1','user-add','picture-1','pencil-2','vcard','address',
    'location-2','map','direction-1','compass-1','trash-2','docs-1','cog-2','tools','progress-2','battery','back-in-time','monitor','mobile-1','lifebuoy','mouse','chart-pie','chart-line','chart-bar-1','chart-area','graduation-cap','floppy','database-1','bucket',
    'thermometer','gauge-1','music-1','search-1','mail','heart','star','user-1','videocam','camera-2','photo','attach-1','lock','eye','tag-1','thumbs-up-1','pencil-1','comment-1','location-1','cup','trash-1','doc-1','note-1','cog-1','params','calendar-1','sound','clock-1','lightbulb','tv','desktop-1','mobile-2','cd','inbox','globe-1','cloud','paper-plane','fire','graduation-cap-1','megaphone-1','database','key','beaker','truck','money','food-1','shop','diamond','t-shirt','fork');
    
    if(!isset($content['tab'])) $content['tab']='icon';
    if(!isset($content['image'])) $content['image']='';
    ?>
    <div id="cms_icon_select_<?php echo $id; ?>" class="cms_icon_select cms_style_selector_container">
        <ul class="cms_icon_select_tabs">
            <li><a class="<?php if($content['tab']=='icon') echo 'active'; ?>" data-target="icon" href="#"><?php echo __('Ikona','cms'); ?></a></li>
            <li><a class="<?php if($content['tab']=='image') echo 'active'; ?>" data-target="image" href="#"><?php echo __('Vlastní obrázek','cms'); ?></a></li>
        </ul>
        <div class="cms_icon_select_tab cms_icon_select_tab_icon <?php if($content['tab']=='icon') echo 'cms_icon_select_tab_active'; ?>">
            <div class="cms_icon_selected_setting">
                <div class="cms_icon_selected cms_open_style_selector">
                    <div class="cms_icon_select_container"> <i style="font-size: <?php echo $content['size']; ?>px; color: <?php echo $content['color']; ?>;" id="<?php echo $id; ?>_size_i" class="icon-<?php echo $content['icon']; ?>"></i></div>
                    <?php echo '<input type="hidden" id="'.$id.'" name="'.$name.'[icon]" value="'.$content['icon'].'" />'; ?>    
                    <a class="cms_icon_select_arr cms_icon_select_oc" href="#"></a>        
                </div>
                <input type="hidden" name="<?php echo $name; ?>[size]" value="<?php echo $content['size']; ?>" id="<?php echo $id; ?>_size">
                <div class="cms_icon_color_set"><input class="cms_text_input cms_color_input" type="text" name="<?php echo $name.'[color]'; ?>" id="<?php echo $id.'_color'; ?>" value="<?php echo $content['color']; ?>" /></div>
                <div id="<?php echo $id; ?>_slider"></div>
                <div id="<?php echo $id; ?>_slider_val" class="cms_slider_val"><?php echo $content['size']; ?> px</div>
                                
                <div class="cms_clear"></div>
            </div>
            <div class="cms_style_selector_bg cms_close_style_selector"></div>
            <div id="cms_icon_selector_<?php echo $id; ?>" class="cms_style_selector">
            <?php
            foreach ($icons as $val) {
                echo '<div id="cms_icon_item_'.$id.'_'.$val.'" class="cms_icon_item '.(($content['icon']==$val)?"cms_icon_item_active":"").'">';
                echo '<i class="icon-'.$val.' cms_close_style_selector" data-value="'.$val.'" data-group="'.$id.'"></i>';
                echo '</div>';
            }
            ?>
            <div class="cms_clear"></div>
            </div>
        </div>
        <div class="cms_icon_select_tab cms_icon_select_tab_image <?php if($content['tab']=='image') echo 'cms_icon_select_tab_active'; ?>">
            <?php
            if(!is_array($content['image'])) $content['image']=array('image'=>$content['image']); // temporary
            ?>
             <div id="image_<?php echo $id; ?>_image" class="cms_uploaded_image <?php if(!$content['image']['image']) echo 'cms_nodisp'; ?>">
                <img class="cms_upload_image_button" target="<?php echo $id; ?>_image" src="<?php echo home_url().$content['image']['image']; ?>" alt="" />
                <div class="cms_clear"></div>  
            </div>
            <button type="button" class="cms_upload_image_button cms_button_secondary" target="<?php echo $id; ?>_image" href="#"><?php echo __('Nahrát obrázek','cms'); ?></button>
            <button type="button" id="cms_clear_image_<?php echo $id; ?>_image" class="cms_clear_image_button cms_button_secondary <?php  if(!$content['image']['image']) echo 'cms_nodisp'; ?>" target="<?php echo $id; ?>_image" href="#"><?php echo __('Smazat obrázek','cms'); ?></button>
            <input id="<?php echo $id; ?>_image" type="hidden" value="<?php if($content['image']['image']) echo $content['image']['image']; ?>" name="<?php echo $name; ?>[image][image]" />
            <input id="<?php echo $id; ?>_image_imageid" type="hidden" value="<?php if(isset($content['image']['imageid'])) echo $content['image']['imageid']; ?>" name="<?php echo $name.'[image][imageid]'; ?>" />
        </div>
        <?php echo '<input class="cms_icon_select_tab_input" type="hidden" id="'.$id.'_tab" name="'.$name.'[tab]" value="'.$content['tab'].'" />'; ?> 
    </div>
          <script>
          jQuery(function() {
              jQuery( "#<?php echo $id; ?>_slider" ).slider({
                  value: <?php echo $content['size']; ?>,
                  min: 12,
                  max: 100,
                  slide: function( event, ui ) {
                      jQuery( "#<?php echo $id; ?>_size_i" ).css( 'font-size', ui.value+'px' );
                      jQuery( "#<?php echo $id; ?>_size" ).val( ui.value );
                      jQuery( "#<?php echo $id; ?>_slider_val" ).html( ui.value+' px' );
                  }
              });
              
              jQuery(document).ready(function($) {
                  $("#<?php echo $id; ?>_color").on("change paste keyup", function() {
                      $("#<?php echo $id; ?>_size_i").css( 'color', $(this).val() );
                  });   
              });
              
          });
          </script>
    <?php
}


//multi image select 
function field_type_multi_imageselect($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    echo '<input type="hidden" id="'.$group_id.'_'.$field['id'].'_item" name="'.$group_id.'['.$field['id'].'][item]" value="'.$content['item'].'" />';    
    echo '<input type="hidden" id="'.$group_id.'_'.$field['id'].'_itemtype" name="'.$group_id.'['.$field['id'].'][itemtype]" value="'.$content['itemtype'].'" />';    
    echo '<ul class="cms_tabs">';
    foreach($field['tabs'] as $id=>$tab) {
        echo '<li class="cms_tab cms_multiple_imageselect_tab"><a href="#cms_multiple_imageselect_'.$id.'" data-group="cms_multiple_imageselect" '.(($tab['type']==$content['itemtype'])? 'class="active"': '').'>'.$tab['name'].'</a>';
    }
    echo '</ul>';
    foreach($field['tabs'] as $id=>$tab) {
        ?>
        <div id="cms_multiple_imageselect_<?php echo $id ?>" class="cms_tab_container cms_multiple_imageselect_container <?php  if($tab['type']==$content['itemtype']) echo 'cms_tab_container_active'; ?>">
        <?php
        foreach ($field['tabs'][$id]['options'] as $key=>$val) {
              if(isset($field['tabs'][$id]['height'])) $style="height:".$field['tabs'][$id]['height'].";";
              else $style="";
              echo '<div style="'.$style.'" id="cms_mis_item_'.$key.'" class="cms_mis_item '.(($content['item']==$key)?"cms_mis_item_active":"").'">';
              echo '<a href="#" data-type="'.$tab['type'].'" data-value="'.$key.'" data-group="'.$group_id.'_'.$field['id'].'"><img src="'.$val.'" alt=""></a>';
              echo '</div>';
        }
        ?>
        </div>
        <?php
    }
} 


//radio
function field_type_radio($field, $meta, $group_id, $tagid) {       
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    if(isset($field['show'])) { ?>
      <script>
      jQuery(document).ready(function($) {
          $(".cms_radio_container_<?php echo $tagid.'_'. $field['id']; ?> input").change(
          function(){ 
              var value=$(this).val(); 
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
              $(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>:not(.cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?>) {display: none;} 
      </style>
    <?php }
    foreach ($field['options'] as $key=>$option) {
        echo '<div class="cms_radio_container cms_radio_container_',$tagid,'_', $field['id'],'"><input type="radio" id="',$tagid,'_', $field['id'],'_',$key,'" name="',$group_id,'[',$field['id'],']" value="',$key,'"', ($key==$content) ? ' checked="checked"' : '', ' />';
        echo '<label for="',$tagid,'_', $field['id'],'_',$key,'"> ',$option, '</label></div>';
    }
    echo '<div class="cms_clear"></div>';
} 

// background image
function field_type_bgimage($field,$meta, $name_id, $group_id) {
    global $vePage;

    $value=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');

    if(isset($value['image']) && $value['image']) $image=(substr($value['image'], 0, 4)=='http')?$value['image']:home_url().$value['image'];
    else $image="";
    
    if(isset($value['pattern']) && $value['pattern']) $pat=$value['pattern'];
    else $pat="";
    
    if(isset($value['color_filter'])) $color_filter_visibility_class='';
    else $color_filter_visibility_class='cms_nodisp';
    
    if(!isset($value['overlay_transparency'])) $value['overlay_transparency']=80;
    if(!isset($value['overlay_color'])) $value['overlay_color']='#158ebf';
    
    ?>
    <div class="cms_style_selector_container">
    <div id="image_<?php echo $group_id.'_'.$field['id']; ?>" class="cms_uploaded_image <?php if(!$image) echo 'cms_nodisp'; ?>">

        <div class="cms_bgimage_image_preview_container">
            <img class="cms_bgimage_image_preview cms_upload_image_button" target="<?php echo $group_id.'_'.$field['id']; ?>" src="<?php if(isset($value['image']) && $value['image']) echo $image; ?>" alt="" />
            <span class="<?php echo $color_filter_visibility_class; ?> cms_upload_image_button" target="<?php echo $group_id.'_'.$field['id']; ?>"></span>
        </div> 
        <style>
            #image_<?php echo $group_id.'_'.$field['id']; ?> .cms_bgimage_image_preview_container span {
                background-color: <?php echo $value['overlay_color']; ?>;
                filter: alpha(opacity=<?php echo $value['overlay_transparency'] ?>);
                  opacity: <?php echo $value['overlay_transparency']/100; ?>;  
            }   
        </style>
 
        <div class="image_setting">
            
            <?php if(!isset($field['hide']) || !in_array('cover',$field['hide'])) { ?>
                <div class="label"><?php echo __('Zobrazení','cms'); ?></div>
                <div><input class="mw_bgimage_check_cover" name="<?php echo $name_id.'['.$field['id'].']'; ?>[cover]" id="cover_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>" type="checkbox" value="cover" <?php if(isset($value['cover']) && $value['cover']=="cover") echo 'checked="checked"'; ?>> <label for="cover_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Roztáhnout obrázek přes celou plochu','cms'); ?></label></div>
            <?php } else if(isset($field['content']['cover']) && $field['content']['cover']) { ?>
              <input name="<?php echo $name_id.'['.$field['id'].']'; ?>[cover]" type="hidden" value="cover">
            <?php } ?>
            
            <div><?php if(isset($field['content']['fixed'])) { ?><input name="<?php echo $name_id.'['.$field['id'].']'; ?>[fixed]" id="fixed_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>" type="checkbox" value="fixed" <?php if(isset($value['fixed']) && $value['fixed']=="fixed") echo 'checked="checked"'; ?>> <label for="fixed_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Fixní pozadí','cms'); ?></label><?php } ?></div>
            
            <?php if(!isset($field['hide']) || !in_array('color_filter',$field['hide'])) { ?>
            <div class="mw_bgimage_color_filter <?php if(!isset($value['cover'])) echo 'cms_nodisp' ?>">
                <div class="label"><?php echo __('Barevný filtr','cms'); ?></div>
                <input class="mw_bgimage_check_color_filter" name="<?php echo $name_id.'['.$field['id'].']'; ?>[color_filter]" id="color_filter_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>" type="checkbox" value="color_filter" <?php if(isset($value['color_filter'])) echo 'checked="checked"'; ?>> <label for="color_filter_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Použít barevný filtr','cms'); ?></label>          
                
                <div class="mw_bgimage_color_filter_setting <?php echo $color_filter_visibility_class; ?>">
                    <div style="padding-top: 7px;">
                        <div class="float-setting" style="width: 150px;">
                            <?php cms_generate_field_color($name_id.'['.$field['id'].'][overlay_color]',$group_id.'_'.$field['id'].'_overlay_color',$value['overlay_color']); ?>
                            <script>
                                jQuery(document).ready(function($) {
                                    $('#<?php echo $group_id.'_'.$field['id'].'_overlay_color' ?>').change(function(){
                                        var color=$(this).val();
                                        if(!color) color='transparent';
                                        $("#image_<?php echo $group_id.'_'.$field['id']; ?> .cms_bgimage_image_preview_container span").css('background-color',color);
                                    });
                                });
                            </script>
                        </div>
                        <div class="float-setting" style="width: 160px;">
                            <?php 
                            $script='var container=jQuery(this).closest(".cms_uploaded_image");
                                      jQuery(".cms_bgimage_image_preview_container span",container).css("opacity",ui.value/100);';
        
                            cms_generate_field_slider($name_id.'['.$field['id'].'][overlay_transparency]',$group_id.'_'.$field['id'].'_overlay_transparency',$value['overlay_transparency'], array('setting'=>array('min'=>'0','max'=>'100','unit'=>'%')),$script); ?> 
                        </div>
                        <div class="cms_clear"></div>
                    </div>
                </div>
                
            </div>
            <?php } ?>
            <div class="label"><?php echo __('Zarovnání obrázku','cms'); ?></div>
            <select name="<?php echo $name_id.'['.$field['id'].']'; ?>[position]">
                <option <?php if(isset($value['position']) && $value['position']=="center center") echo 'selected="selected"'; ?> value="center center"><?php echo __('Umístit doprostřed', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="left top") echo 'selected="selected"'; ?> value="left top"><?php echo __('Umístit nahoru doleva', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="center top") echo 'selected="selected"'; ?> value="center top"><?php echo __('Umístit nahoru doprostřed', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="right top") echo 'selected="selected"'; ?> value="right top"><?php echo __('Umístit nahoru doprava', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="left center") echo 'selected="selected"'; ?> value="left center"><?php echo __('Umístit doprostřed doleva', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="right center") echo 'selected="selected"'; ?> value="right center"><?php echo __('Umístit doprostřed doprava', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="left bottom") echo 'selected="selected"'; ?> value="left bottom"><?php echo __('Umístit dolů doleva', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="center bottom") echo 'selected="selected"'; ?> value="center bottom"><?php echo __('Umístit dolů doprostřed', 'cms'); ?></option>
                <option <?php if(isset($value['position']) && $value['position']=="right bottom") echo 'selected="selected"'; ?> value="right bottom"><?php echo __('Umístit dolů doprava', 'cms'); ?></option>
            </select>
            <div class="mw_bgimage_repeat_container <?php if(isset($value['cover'])) echo 'cms_nodisp' ?>">
            <div class="label"><?php echo __('Opakovaní obrázku','cms'); ?></div>
                <select name="<?php echo $name_id.'['.$field['id'].']'; ?>[repeat]">
                    <option <?php if(isset($value['repeat']) && $value['repeat']=="no-repeat") echo 'selected="selected"'; ?> value="no-repeat"><?php echo __('Neopakovat', 'cms'); ?></option>
                    <option <?php if(isset($value['repeat']) && $value['repeat']=="repeat") echo 'selected="selected"'; ?> value="repeat"><?php echo __('Opakovat všemi směry', 'cms'); ?></option>
                    <option <?php if(isset($value['repeat']) && $value['repeat']=="repeat-x") echo 'selected="selected"'; ?> value="repeat-x"><?php echo __('Opakovat po ose X', 'cms'); ?></option>
                    <option <?php if(isset($value['repeat']) && $value['repeat']=="repeat-y") echo 'selected="selected"'; ?> value="repeat-y"><?php echo __('Opakovat po ose Y', 'cms'); ?></option>
                </select>
            </div>
            <div class="label"><?php echo __('Pro mobilní zařízení','cms'); ?></div>
            <div><input name="<?php echo $name_id.'['.$field['id'].']'; ?>[mobile_hide]" id="mobile_hide_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>" type="checkbox" value="mobile_hide" <?php if(isset($value['mobile_hide'])) echo 'checked="checked"'; ?>> <label for="mobile_hide_image_checkbox_<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Skrýt na mobilních zařízeních','cms'); ?></label></div>
        </div>
        <div class="cms_clear"></div>
    </div>
    
    <div id="cms_bgimage_pattern_<?php echo $group_id.'_'.$field['id']; ?>" class="cms_bgimage_pattern_preview <?php if(!$pat) echo 'cms_nodisp'; ?>" <?php if($pat) { ?>style="background: url('<?php echo $vePage->list_patterns[$pat].$pat.'_p.png'; ?>');" <?php } ?>></div> 
    
    <button type="button" class="cms_upload_image_button cms_button_secondary" target="<?php echo $group_id.'_'.$field['id']; ?>" href="#"><?php echo __('Nahrát obrázek','cms'); ?></button>
    <?php if(isset($field['content']['pattern'])) {  ?>
        <button type="button" class="cms_open_style_selector cms_button_secondary" target="<?php echo $group_id.'_'.$field['id']; ?>" href="#"><?php echo __('Použít vzorek','cms'); ?></button>
    <?php } ?>
    <button type="button" id="cms_clear_image_<?php echo $group_id.'_'.$field['id']; ?>" class="cms_clear_image_button cms_button_secondary <?php  if(!$image && !$pat) echo 'cms_nodisp'; ?>" target="<?php echo $group_id.'_'.$field['id']; ?>" href="#"><?php echo __('Smazat pozadí','cms'); ?></button>
    <input id="<?php echo $group_id.'_'.$field['id']; ?>" type="hidden" value="<?php if(isset($value['image']) && $value['image']) echo $value['image']; ?>" name="<?php echo $name_id.'['.$field['id'].'][image]'; ?>" />
    <input id="<?php echo $group_id.'_'.$field['id'].'_imageid'; ?>" type="hidden" value="<?php if(isset($value['imageid']) && $value['imageid']) echo $value['imageid']; ?>" name="<?php echo $name_id.'['.$field['id'].'][imageid]'; ?>" />

<?php
    if(isset($field['content']['pattern'])) {
    ?>
    <div class="cms_style_selector_bg cms_close_style_selector"></div>
    <div class="pattern_select cms_style_selector">
        <?php foreach($vePage->list_patterns as $key=>$url) { ?>
            <a class="cms_close_style_selector" href="#" data-pattern="<?php echo $url.$key.'_p.png'; ?>" data-group="<?php echo $group_id.'_'.$field['id']; ?>" data-value="<?php echo $key; ?>">
                <img src="<?php echo $url.$key; ?>.png" alt="" />                  
            </a>    
        <?php } ?>   
        <div class="cms_clear"></div>   
    </div>
    <input id="<?php echo $group_id.'_'.$field['id'].'_pattern'; ?>" class="pattern_input" type="hidden" value="<?php echo $pat; ?>" name="<?php echo $name_id.'['.$field['id'].'][pattern]'; ?>" <?php if($pat==$key) echo 'checked="checked"'; ?> />
    <?php } ?>
    </div> 
    <?php
} 


// upload image
function field_type_upload($field,$meta, $group_name, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_upload($group_name.'['.$field['id'].']',$group_id.'_'.$field['id'],$content);  
}
function cms_generate_field_upload($name,$id,$value,$class='') {

    if(isset($value)) $image=(substr($value, 0, 4)=='http')?$value:home_url().$value;
    else $image="";
    ?>
    
    <div id="image_<?php echo $id; ?>" class="cms_uploaded_image <?php if(!$value) echo 'cms_nodisp'; ?>">
        <img class="cms_upload_image_button" target="<?php echo $id; ?>" src="<?php echo $image; ?>" alt="" />
        <div class="cms_clear"></div>  
    </div>
    <button type="button" class="cms_upload_image_button cms_button_secondary" target="<?php echo $id; ?>" href="#"><?php echo __('Nahrát obrázek','cms'); ?></button>
    <button type="button" id="cms_clear_image_<?php echo $id; ?>" class="cms_clear_image_button cms_button_secondary <?php  if(!$value) echo 'cms_nodisp'; ?>" target="<?php echo $id; ?>" href="#"><?php echo __('Smazat obrázek','cms'); ?></button>
    <input class="text-upload cms_text_input" id="<?php echo $id; ?>" type="hidden" value="<?php if($value) echo $value; ?>" name="<?php echo $name; ?>" />    
<?php
}

// new upload image
function field_type_image($field, $meta, $group_id, $tagid) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_image($group_id.'['.$field['id'].']',$tagid.'_'.$field['id'],$content);  
}
function cms_generate_field_image($name,$id,$value,$class='') {

    if(!is_array($value)) $value=array('image'=>$value);

    if(isset($value['image'])) $image=(substr($value['image'], 0, 4)=='http')?$value['image']:home_url().$value['image'];
    else $image="";
    ?>
    <div id="image_<?php echo $id; ?>" class="cms_uploaded_image <?php if(!$value['image']) echo 'cms_nodisp'; ?>">
        <img class="cms_upload_image_button" target="<?php echo $id; ?>" src="<?php echo $image; ?>" alt="" />
        <div class="cms_clear"></div>  
    </div>
    <button type="button" id="cms_upload_image_<?php echo $id; ?>" class="cms_upload_image_button cms_button_secondary" target="<?php echo $id; ?>" href="#"><?php echo __('Nahrát obrázek','cms'); ?></button>
    <button type="button" id="cms_clear_image_<?php echo $id; ?>" class="cms_clear_image_button cms_button_secondary <?php  if(!$value['image']) echo 'cms_nodisp'; ?>" target="<?php echo $id; ?>" href="#"><?php echo __('Smazat obrázek','cms'); ?></button>
    <input id="<?php echo $id; ?>" type="hidden" value="<?php if($value['image']) echo $value['image']; ?>" name="<?php echo $name.'[image]'; ?>" />
    <input id="<?php echo $id; ?>_imageid" type="hidden" value="<?php if(isset($value['imageid'])) echo $value['imageid']; ?>" name="<?php echo $name.'[imageid]'; ?>" />
<?php
}

//Upload gallery
function field_type_image_gallery($field,$meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_upload_gallery($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content,$field);
}
function cms_generate_field_upload_gallery($name,$id,$value,$field) {

    if(isset($field['editable']) && !$field['editable']) 
        $editable=false;
    else 
        $editable=true;
    ?>

    <div id="image_<?php echo $id; ?>" class="cms_uploaded_image cms_image_gallery <?php if(!$value) echo 'cms_nodisp'; ?>">
        <div class="cms_image_gallery__wrap">
            <?php if( !empty( $value ) ): foreach( $value as $image ): ?>
                <div class="cms_image_gallery__item">
                    <?php 
                    if(substr($image, 0, 4) == 'http') {
                        $src = $image;  
                        $editable=false;
                    }
                    else {
                        $image_src = wp_get_attachment_image_src( $image, 'thumbnail' ); 
                        $src = $image_src[0];
                    }
                    ?>
                    <img src="<?php echo $src; ?>">
                    <?php if($editable) { ?><button title="Upravit obrázek" class="cms_image_gallery__item__edit_button">Upravit obrázek</button><?php } ?>
                    <button title="Odstranit obrázek" class="cms_image_gallery__item__close_button">Odstranit obrázek</button>
                    <input type="hidden" name="<?php echo $name; ?>[]" value="<?php echo $image; ?>" style="display: none;">
                </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="cms_image_gallery__spinner"></div>
    </div>
    <button type="button" class="cms_upload_gallery_button cms_button_secondary" target="<?php echo $id; ?>" data-name="<?php echo $name; ?>" data-editable="<?php echo (isset($editable) && $editable)? '1':'0'; ?>" href="#"><?php echo __('Přidat obrázky','cms'); ?></button>
<!--    <button type="button" id="cms_clear_image_--><?php //echo $id; ?><!--" class="cms_clear_image_button cms_button_secondary --><?php // if(!$value) echo 'cms_nodisp'; ?><!--" target="--><?php //echo $id; ?><!--" href="#">--><?php //echo __('Smazat obrázky','cms'); ?><!--</button>-->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $( '.cms_image_gallery__wrap' ).trigger( 'init_sortable' );
        });
    </script>
<?php
}

// upload file
function field_type_upload_file($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_upload_file($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content);
} 
function cms_generate_field_upload_file($name,$id,$value,$class='') {
  ?>
  <input class="cms_text_upload cms_text_input" id="<?php echo $id; ?>" type="text" value="<?php if($value) echo $value; ?>" name="<?php echo $name; ?>" />
  <input class="cms_upload_file_button cms_button_secondary" type="button" target="<?php echo $id; ?>" value="<?php echo __('Vložit soubor','cms'); ?>" /> 
  <input class="cms_clear_upload_button cms_button_secondary" type="button" target="<?php echo $id; ?>" value="<?php echo __('Smazat','cms'); ?>" />  
<?php
}      

// select menu
function field_type_selectmenu($field, $meta, $group_id) { 
  $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
  $menus = get_terms( 'nav_menu', array('hide_empty'=>false) );
  cms_generate_field_selectmenu($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$menus, $content);
}
function cms_generate_field_selectmenu($name,$id,$menus, $value) {
  ?>
  <div class="ve_menuselect_container">
      <?php 
      echo '<select class="ve_menuselect_selector" name="'.$name.'" id="'.$id.'">';
      echo '<option value="" '. ((!$value) ? ' selected="selected"' : ''). '>'.__('Bez menu','cms').'</option>';
      foreach ($menus as $menu) {
            echo '<option value="'.$menu->term_id.'" '. (($value == $menu->term_id) ? ' selected="selected"' : ''). '>'. $menu->name. '</option>';
      }
      echo '</select>';

      ?> 
      <span class="ve_menuselect_tools" <?php if(!$value || !wp_get_nav_menu_object($value)) echo 'style="display:none;"'; ?>>
          <a class="cms_button_secondary cms_icon_button_secondary cms_icon_button_edit open_menuselect_editor edit_menuselect_editor" data-id="<?php echo $value; ?>" title="<?php echo __('Upravit menu','cms') ?>" href="#">&nbsp;</a>
          <a class="cms_button_secondary cms_icon_button_secondary cms_icon_button_delete delete_menuselect_editor" data-id="<?php echo $value; ?>" title="<?php echo __('Smazat menu','cms') ?>" href="#">&nbsp;</a>          
      </span>
      <button class="cms_button_secondary open_menuselect_editor" data-id="" title="<?php echo __('Vytvořit nové menu','cms') ?>" ><?php echo __('Vytvořit nové menu','cms') ?></button>
  </div>
  <?php
}  

// select page  
function field_type_selectpage($field, $meta, $group_id) { 
  global $cms;
  $pages = get_pages(array('post_status'=>'publish,private,draft'));
  $cms->select_page($pages, $meta, $group_id.'['.$field['id'].']',$group_id.'_'.$field['id']);
}
function field_type_publish_selectpage($field, $meta, $group_id) { 
  global $cms;
  $pages = get_pages(array('post_status'=>'publish'));
  $cms->select_page($pages, $meta, $group_id.'['.$field['id'].']',$group_id.'_'.$field['id']);
}

// background
function field_type_background($field, $meta, $group_name, $group_id) {  
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: array('color1'=>'','color2'=>''));  
    cms_generate_field_background($group_name.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field); 
}
function cms_generate_field_background($name,$id,$content, $field) {
    echo '<div class="float-setting"><div class="sublabel">'.__('Počáteční barva pozadí','cms').'</div><input class="cms_text_input cms_color_input" type="text" name="'.$name.'[color1]" id="'.$id.'_color1" value="'. $content['color1']. '" /></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Koncová barva pozadí','cms').'</div><input class="cms_text_input cms_color_input" type="text" name="'.$name.'[color2]" id="'.$id.'_color2" value="'. $content['color2']. '" /></div>';
    if(isset($field['content']['transparency'])) {
        $transparency=(isset($content['transparency']))? $content['transparency']:'100'; ?>
        <div class="float-setting" style="width: 180px;">
            <div class="sublabel"><?php echo __('Průhlednost pozadí','cms'); ?></div>
            <?php cms_generate_field_slider($name.'[transparency]',$id.'_transparency',$transparency, array('setting'=>array('min'=>'0','max'=>'100','unit'=>'%'))); ?> 
        </div>
        <?php
    }
    echo '<div class="cms_clear"></div>'; 
} 

// color
function field_type_color($field, $meta, $name_id, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_color($name_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field); 
}
function cms_generate_field_color($name, $id, $content, $field=array()) {
    echo '<input class="cms_text_input cms_color_input" type="text" name="'.$name.'" id="'.$id.'" value="'. $content. '" />'; 
}

// padding
function field_type_padding($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_padding($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content,$field['content']);
}
function cms_generate_field_padding($name,$id,$content) {
    echo '<div class="float-setting"><div class="sublabel">'.__('Nahoře','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[top]" id="'.$id.'_top" value="'. $content['top']. '" /></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Dole','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[bottom]" id="'.$id.'_bottom" value="'. $content['bottom']. '" /></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Vlevo','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[left]" id="'.$id.'_top" value="'. $content['left']. '" /></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Vpravo','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[right]" id="'.$id.'_bottom" value="'. $content['right']. '" /></div>';
    echo '<div class="cms_clear"></div>'; 
} 

// shadow
function field_type_shadow($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_shadow($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content,$field['content']);
}
function cms_generate_field_shadow($name,$id,$content) {
    echo '<div class="float-setting"><div class="sublabel">'.__('Horizontální posunutí','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[horizontal]" id="'.$id.'_horizontal" value="'. $content['horizontal']. '" /></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Vertikální posunutí','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[vertical]" id="'.$id.'_vertical" value="'. $content['vertical']. '" /></div>';
    echo '<div class="float-setting"><div class="sublabel">'.__('Velikost stínu','cms').'</div><input class="cms_text_input" type="text" name="'.$name.'[size]" id="'.$id.'_size" value="'. $content['size']. '" /></div>';
    ?>
    <div class="float-setting" style="width: 180px;">
            <div class="sublabel"><?php echo __('Průhlednost stínu','cms'); ?></div>
            <?php 
            $transparency=(isset($content['transparency']))? $content['transparency']:'10';
            cms_generate_field_slider($name.'[transparency]',$id.'_transparency',$transparency, array('setting'=>array('min'=>'0','max'=>'100','unit'=>'%'))); 
            ?>
    </div>

        <?php
    echo '<div class="cms_clear"></div>'; 
} 

// shadow
function field_type_slider($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_slider($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content, $field);
}
function cms_generate_field_slider($name,$id,$content, $field, $slide_action='') { 
      ?>
      <input id="<?php echo $id; ?>" type="hidden" name="<?php echo $name; ?>" value="<?php echo $content; ?>" />
      <div id="<?php echo $id; ?>_slider"></div>
      <div id="<?php echo $id; ?>_val" class="cms_slider_val"><?php echo '<span>'.$content.'</span> '.$field['setting']['unit']; ?></div>
      <div class="cms_clear"></div>      
      <script>
              jQuery(function() {
                  jQuery( "#<?php echo $id; ?>_slider" ).slider({
                      value: <?php echo $content; ?>,
                      min: <?php echo $field['setting']['min']; ?>,
                      max: <?php echo $field['setting']['max']; ?>,
                      step: <?php echo (isset($field['setting']['step']))? $field['setting']['step']: 1; ?>,
                      slide: function( event, ui ) {
                          <?php echo $slide_action; ?>
                          jQuery( "#<?php echo $id; ?>" ).val( ui.value );
                          jQuery( "#<?php echo $id; ?>_val" ).html( ui.value+' <?php echo $field['setting']['unit']; ?>' );
                          
                      }
                  });              
              });
      </script>
      <?php
} 

// font
function field_type_font($field, $meta, $group_id) {
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    cms_generate_field_font($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$content,$field['content']);
}
function cms_generate_field_font($name,$id,$value,$setting,$class='cms_font_set') {
    global $cms;
    echo '<div class="cms_font_setting_container">';
    if(isset($setting['font-size'])) {
        echo '<div class="float-setting"><div class="sublabel">'.__('Velikost','cms').'</div>';
        echo '<select id="'.$id.'_size" class="'.$class.'_size" name="'.$name.'[font-size]">';
        echo '<option '.(($value['font-size']=='')? 'selected="selected"':'').' value="">-</option>';
        for($i=9;$i<161;$i++) {   
            echo '<option '.(($value['font-size']==$i)? 'selected="selected"':'').' value="'.$i.'">'.$i.'px</option>';
        }
        echo '</select>';
        echo '</div>';
    }
    $basic_weights=array('normal'=>__('Normal','cms'),'bold'=>__('Bold','cms'));
    if(isset($setting['font-family'])) {
        echo '<div class="float-setting"><div class="sublabel">'.__('Písmo','cms').'</div>';
        ?>
            <div id="<?php echo $id; ?>_font" class="font_select_container cms_style_selector_container">
                <a class="font_selected cms_open_style_selector" href="#"><?php echo ($value['font-family']=="")? __('Defaultní','cms') : $value['font-family']; ?></a>
                <input type="hidden" class="font_selected_input" name="<?php echo $name.'[font-family]'; ?>" value="<?php echo $value['font-family']; ?>">
                <div class="cms_style_selector_bg cms_close_style_selector"></div>
                <div class="cms_style_selector font_select <?php echo $class; ?>_font_select">
                    <a class="cms_close_style_selector" href="#" data-font="" data-text="<?php echo __('Defaultní','cms'); ?>" <?php echo 'data-weights="{\'id\':\'\',\'name\':\'-\'}"'; ?> ><?php echo __('Defaultní','cms'); ?></a>
                    <?php
                    // used fonts
                     if(isset($_SESSION['ve_used_fonts']) && count($_SESSION['ve_used_fonts'])) { ?>
                        <div class="cms_clear"></div>
                        <div class="cms_style_selector_title"><?php echo __('Naposledy používané fonty','cms'); ?></div>
                        <?php 
                        foreach($_SESSION['ve_used_fonts'] as $used_font) {
                            if(in_array($used_font,$cms->fonts)) {
                                echo '<a class="cms_close_style_selector" href="#" data-font="'.$used_font.'"  data-weights="{\'id\':\'normal\',\'name\':\''.__('Normal','cms').'\'},{\'id\':\'bold\',\'name\':\''.__('Bold','cms').'\'}" style="font-family:'.$used_font.';">'.$used_font.'</a>';
                            } else if(is_string($used_font) && isset($cms->google_fonts[$used_font])) {
                                $font=$cms->google_fonts[$used_font];
                                $weights=array();
                                foreach($font['weights'] as $wkey=>$wval) {
                                    $weights[]="{'id':'".$wkey."','name':'".$wval."'}";
                                }
                                echo '<a class="cms_close_style_selector" href="#" data-font="'.$used_font.'" data-weights="'.implode(",",$weights).'">';
                                if(isset($font['img'])) echo '<img src="'.$font['img'].'" alt="'.$used_font.'" />';
                                else echo $used_font;
                                echo '</a>';
                            }
                        } 
                    }
                    ?>
                    <div class="cms_clear"></div>
                    <div class="cms_style_selector_title"><?php echo __('Základní fonty','cms'); ?></div>
                    <?php 
                    // basic fonts
                    foreach($cms->fonts as $font) {
                        echo '<a class="cms_close_style_selector" href="#" data-font="'.$font.'"  data-weights="{\'id\':\'normal\',\'name\':\''.__('Normal','cms').'\'},{\'id\':\'bold\',\'name\':\''.__('Bold','cms').'\'}" style="font-family:'.$font.';">'.$font.'</a>';
                    } 
                    ?>
                    <div class="cms_clear"></div>
                    
                    <?php
                    // custom fonty
                    $custom_fonts=array();
                    foreach($cms->google_fonts as $key=>$font) {     
                        if(isset($font['custom_font'])) {
                            $custom_fonts[$key]=$font;                      
                        }
                    } 
                    if(count($custom_fonts)) {
                      ?>
                      <div class="cms_style_selector_title"><?php echo __('Vlastní google fonty','cms'); ?></div>
                      <?php
                      foreach($custom_fonts as $key=>$font) {     
                              $weights=array();
                              foreach($font['weights'] as $wkey=>$wval) {
                                  $weights[]="{'id':'".$wkey."','name':'".$wval."'}";
                              }
                              echo '<a class="cms_close_style_selector" href="#" data-font="'.$key.'" data-weights="'.implode(",",$weights).'">'.$key.'</a>';                        
                      }
                      ?>
                      <div class="cms_clear"></div>
                      <?php
                    }
                    ?>
                    <div class="cms_style_selector_title"><?php echo __('Google fonty','cms'); ?></div>
                    <?php
                    // google fonty
                    foreach($cms->google_fonts as $key=>$font) {     
                        if(!isset($font['custom_font'])) {
                            $weights=array();
                            foreach($font['weights'] as $wkey=>$wval) {
                                $weights[]="{'id':'".$wkey."','name':'".$wval."'}";
                            }
                            echo '<a class="cms_close_style_selector" href="#" data-font="'.$key.'" data-weights="'.implode(",",$weights).'"><img src="'.$font['img'].'" alt="'.$key.'" /></a>';                        
                        }
                    }
                    ?>
                </div>
            </div>
        <?php
        echo '</div>';
    }
    if(isset($setting['weight'])) {
        echo '<div class="float-setting"><div class="sublabel">'.__('Tloušťka','cms').'</div>';
        echo '<select id="'.$id.'_weight" class="font_weight_select" name="'.$name.'[weight]">';
        $weights=(isset($cms->google_fonts[$value['font-family']]))? $cms->google_fonts[$value['font-family']]['weights'] : $basic_weights;
        if(!$value['font-family']) $weights=array(''=>'-');
        foreach($weights as $key=>$weight) {
            echo '<option '.(($value['weight']==$key)? 'selected="selected"':'').' value="'.$key.'">'.$weight.'</option>';
        }
        echo '</select>';
        echo '</div>';
    }
    if(isset($setting['line-height'])) {
        echo '<div class="float-setting"><div class="sublabel">'.__('Velikost řádků','cms').'</div>';
        echo '<select id="'.$id.'_line_height" class="'.$class.'_line-height" name="'.$name.'[line-height]">';
        echo '<option '.(($value['line-height']=='')? 'selected="selected"':'').' value="">-</option>';
        for($i=0.8;$i<3.1;$i=$i+0.1) {   
            echo '<option '.(($value['line-height']==strval($i))? 'selected="selected"':'').' value="'.$i.'">'.$i.'x</option>';
        }
        echo '</select>';
        echo '</div>';
    }
    if(isset($setting['letter-spacing'])) {
        echo '<div class="float-setting"><div class="sublabel">'.__('Mezery','cms').'</div>';
        echo '<select id="'.$id.'_letter_spacing" class="'.$class.'_letter_spacing" name="'.$name.'[letter-spacing]">';
        echo '<option '.(($value['letter-spacing']=='0')? 'selected="selected"':'').' value="0">0</option>';
        for($i=-3;$i<21;$i=$i+1) {   
            if($i!=0) echo '<option '.(($value['letter-spacing']==strval($i))? 'selected="selected"':'').' value="'.$i.'">'.$i.'px</option>';
        }
        echo '</select>';
        echo '</div>';
    }
    /*
    else {
        $weight=(isset($value['font-family']) && isset($cms->google_fonts[$value['font-family']]))? "400" : "normal"; 
        echo '<input type="hidden" name="'.$name.'[weight]" value="'.$weight.'" />';
    }
    */
    if(isset($setting['align'])) {
        echo '<div class="float-setting"><div class="sublabel">'.__('Zarovnání','cms').'</div>';
        echo '<select id="'.$id.'_align" name="'.$name.'[align]">';
            echo '<option '.(($value['align']=='center')? 'selected="selected"':'').' value="center">'.__('Na střed','cms').'</option>';
            echo '<option '.(($value['align']=='left')? 'selected="selected"':'').' value="left">'.__('Vlevo','cms').'</option>';
            echo '<option '.(($value['align']=='right')? 'selected="selected"':'').' value="right">'.__('Vpravo','cms').'</option>';
        echo '</select>';
        echo '</div>';
    }
    if(isset($setting['color'])) {
        ?>
        <div class="float-setting"><div class="sublabel"><?php echo __('Barva','cms'); ?></div>
        <input id="<?php echo $id; ?>_color" class="cms_text_input cms_color_input <?php echo $class.'_color'; ?>" type="text" name="<?php echo $name; ?>[color]" value="<?php echo $value['color']; ?>" />
        </div>
        <?php
    }
    if(isset($setting['text-shadow'])) {
        ?>
        <div class="float-setting"><div class="sublabel"><?php echo __('Stín','cms'); ?></div>
        <select id="<?php echo $id; ?>_shadow" class="<?php echo $class.'_shadow'; ?>" name="<?php echo $name; ?>[text-shadow]">
            <option <?php if($value['text-shadow']=="") echo 'selected="selected"'; ?> value="none"><?php echo __('Žádný', 'cms'); ?></option>
            <option <?php if($value['text-shadow']=="dark") echo 'selected="selected"'; ?> value="dark"><?php echo __('Tmavý', 'cms'); ?></option>
            <option <?php if($value['text-shadow']=="light") echo 'selected="selected"'; ?> value="light"><?php echo __('Světlý', 'cms'); ?></option>
        </select>
        </div>
        <?php
    }
    ?>
    <div class="cms_clear"></div>
    <?php
    echo '</div>';
}

function field_type_border($field, $meta, $group_id) {
    global $cms;
    if(isset($field['content']['size'])) {
    ?>
    <div class="float-setting"><div class="sublabel"><?php echo __('Tloušťka čáry','cms'); ?></div>
        <?php
        echo '<select name="'.$group_id.'['.$field['id'].'][size]">';
        for($i=0;$i<11;$i++) {   
            echo '<option '.(($meta['size']==$i)? 'selected="selected"':'').' value="'.$i.'">'.$i.'px</option>';
        }
        echo '</select>';
        ?>
    </div>
    <?php } 
    if(isset($field['content']['style'])) {?>
    <div class="float-setting"><div class="sublabel"><?php echo __('Styl ohraničení','cms'); ?></div>
        <select name="<?php echo $group_id.'['.$field['id'].']'; ?>[style]">
            <option <?php if($meta['style']=="solid") echo 'selected="selected"'; ?> value="solid"><?php echo __('Plná čára', 'cms'); ?></option>
            <option <?php if($meta['style']=="dashed") echo 'selected="selected"'; ?> value="dashed"><?php echo __('Čárkovaná čára', 'cms'); ?></option>
            <option <?php if($meta['style']=="dotted") echo 'selected="selected"'; ?> value="dotted"><?php echo __('Tečkovaná čára', 'cms'); ?></option>
        </select>
    </div>
    <?php } 
    if(isset($field['content']['color'])) {?>
    <div class="float-setting"><div class="sublabel"><?php echo __('Barva','cms'); ?></div>
        <input class="cms_text_input cms_color_input" type="text" name="<?php echo $group_id.'['.$field['id'].']'; ?>[color]" value="<?php echo $meta['color']; ?>" />
    </div>
    <?php } ?>
    <div class="cms_clear"></div>
    <?php

}

function field_type_button($field, $meta, $group_id, $tag_id) {
    global $cms, $vePage;
    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    
    $height_p=(isset($content['height_padding']))? $content['height_padding']:'0.5';
    $width_p=(isset($content['width_padding']))? $content['width_padding']:'1.2';

    $selected_set=explode(',',str_replace("'","",$field['options'][$content['style']]));
    $selected=array();

    foreach($selected_set as $sel) {
        if(!empty($sel)) {
            $sel_val=explode('=',$sel); 
            $selected[$sel_val[0]]=$sel_val[1];   
        }
    }
    ?>
    <div id="cms_button_select_<?php echo $group_id.'_'.$field['id']; ?>"  class="cms_button_select_container cms_style_selector_container">
    <?php
    // style
    echo '<style>
        #cms_button_select_'.$group_id.'_'.$field['id'].' .cms_image_select_container .ve_content_button,
        #cms_button_select_'.$group_id.'_'.$field['id'].' .cms_image_select_basic_styles .ve_content_button { 
            '.$vePage->generate_style_atribut($content).' 
        } 
        #cms_button_select_'.$group_id.'_'.$field['id'].' .cms_image_select_container .ve_content_button { 
            padding: '.$height_p.'em '.$width_p.'em '.$height_p.'em '.((isset($field['content']['icon']))?$width_p-0.8:$width_p).'em ;
        } 
        #cms_image_select_'.$group_id.'_'.$field['id'].' .cms_image_select_basic_styles .ve_content_button { 
            font-size: 25px; 
        }
    </style>';
    ?>
    <div id="cms_image_select_<?php echo $group_id.'_'.$field['id']; ?>" class="cms_image_select">
        <div class="cms_image_selected cms_open_style_selector">
            <div class="cms_image_select_container">
            <?php if(isset($field['content']['icon'])) { ?>
                <a class="ve_content_button ve_content_button_forchange ve_content_button_icon ve_content_button_<?php echo $content['style']; ?>" href="#">
                    <span class="ve_but_icon cms_icon_preview_<?php echo $group_id.'_'.$field['id'].'_icon'; ?>"><span class="cms_icon_background cms_icon_background_hide"><?php include($field['content']['icon']['icons'][$content['icon']['icon']].$content['icon']['icon'].".svg"); ?></span></span>
                    <span class="ve_but_text"><?php echo __('Text tlačítka','cms'); ?></span>
                </a>
            <?php } else { ?>
                <a class="ve_content_button ve_content_button_forchange ve_content_button_<?php echo $content['style']; ?>" href="#"><?php echo __('Text tlačítka','cms'); ?></a>
            <?php } ?>
            </div>
            <?php echo '<input type="hidden" id="'.$group_id.'_'.$field['id'].'" class="cms_change_button" name="'.$group_id.'['.$field['id'].'][style]" value="'.$content['style'].'" />'; ?>
            <a class="cms_image_select_arr cms_image_select_oc" href="#"></a>
        </div>
        <div class="cms_style_selector_bg cms_close_style_selector"></div>
        <div id="cms_image_selector_<?php echo $group_id.'_'.$field['id']; ?>" class="cms_style_selector">
            <div class="cms_style_selector_title cms_style_selector_title_first"><?php echo __('Změnit styl tlačítka','cms'); ?></div>
            <div class="cms_image_select_basic_styles">
            <?php
            foreach ($field['options'] as $key=>$but) {
                echo '<div id="cms_is_item_'.$group_id.'_'.$field['id'].'_'.$key.'" class="cms_is_item_button '.(($content['style']==$key)?"cms_is_item_active":"").'">';
                echo '<a class="cms_close_style_selector ve_content_button ve_content_button_forchange ve_content_button_'.$key.'" data-set="'.$but.'" data-value="'.$key.'" data-group="'.$group_id.'_'.$field['id'].'" href="#">'.__('Text tlačítka','cms').'</a>';
                //echo '<input type="radio" id="'.$group_id.'_'.$field['id'].'_'.$key.'" name="'.$group_id.'['.$field['id'].']" value="'.$key.'"'.(($key==$content) ? ' checked="checked"' : '').' />';
                echo '</div>';
            }
            ?>
            </div>
            <?php
            if(isset($_SESSION['ve_used_buttons']) && count($_SESSION['ve_used_buttons'])) { ?>
                <div class="cms_clear"></div>
                <div class="cms_style_selector_title"><?php echo __('Použít styl i nastavení naposledy vytvořeného tlačítka','cms'); ?></div>
                <?php 
                $i=0;
                foreach($_SESSION['ve_used_buttons'] as $used_button) {
                    $but_setting="{";
                    $but_setting.="'font_size':'".$used_button['font']['font-size']."'";
                    $but_setting.=",'font_color':'".$used_button['font']['color']."'";
                    $but_setting.=",'font_family':'".$used_button['font']['font-family']."'";
                    $but_setting.=",'font_weight':'".$used_button['font']['weight']."'";
                    $but_setting.=",'font_shadow':'".$used_button['font']['text-shadow']."'";
                    $but_setting.=",'background_color1':'".$used_button['background_color']['color1']."'";
                    $but_setting.=",'background_color2':'".$used_button['background_color']['color2']."'";
                    $but_setting.=",'height':'".$used_button['height_padding']."'";
                    $but_setting.=",'width':'".$used_button['width_padding']."'";
                    $but_setting.=",'corner':'".$used_button['corner']."'";
                    $but_setting.=",'border_color':'".$used_button['border-color']."'";
                    $but_setting.=",'hover_color1':'".$used_button['hover_color']['color1']."'";
                    $but_setting.=",'hover_color2':'".$used_button['hover_color']['color2']."'";
                    $but_setting.=",'hover_font_color':'".$used_button['hover_font_color']."'";
                    $but_setting.=",'hover_effect':'".$used_button['hover_effect']."'";
                    $but_setting.=",'border_hover_color':'".$used_button['border_hover-color']."'";
                    $but_setting.="}";
                    
                    unset($used_button['icon']);
                    
                    echo $vePage->create_button_styles($used_button, '#used_button_'.$i);
                    echo "<link id='button_set_font' href='https://fonts.googleapis.com/css?family=".str_replace(' ', '+', $used_button['font']['font-family']).":".$used_button['font']['weight']."&subset=latin,latin-ext' rel='stylesheet' type='text/css'>";                   
                    echo '<div id="cms_is_item_'.$group_id.'_'.$field['id'].'_'.$key.'" class="cms_is_item_button '.(($content['style']==$key)?"cms_is_item_active":"").'">';
                    echo '<a id="used_button_'.$i.'" class="cms_close_style_selector ve_content_button ve_content_button_'.$used_button['style'].'" data-butset="'.$but_setting.'" data-set="'.$vePage->list_buttons[$used_button['style']].'" data-value="'.$used_button['style'].'" data-group="'.$group_id.'_'.$field['id'].'" href="#">'.__('Tlačítko','cms').'</a>';
                    echo '</div>';
                    $i++;
                } 
            }
            ?>
            <div class="cms_clear"></div>
        </div>
    </div>
    
        
    <div class="cms_button_setting">
        <ul class="cms_small_tabs">
            <li class="cms_tab ft_bs_<?php echo $field['id']; ?>_tab">
                <a class="active" data-group="ft_bs_<?php echo $field['id']; ?>" href="#select_ft_bs_<?php echo $field['id']; ?>_1"><?php echo __('Tlačítko','cms') ?></a>
            </li>
            <li class="cms_tab ft_bs_<?php echo $field['id']; ?>_tab">
                <a data-group="ft_bs_<?php echo $field['id']; ?>" href="#select_ft_bs_<?php echo $field['id']; ?>_2"><?php echo __('Hover','cms') ?></a>
            </li>
        </ul>
        
        <div id="select_ft_bs_<?php echo $field['id']; ?>_1" class="cms_setting_block_content cms_tab_container ft_bs_<?php echo $field['id']; ?>_container" style="display: block;">
            <div class="label"><?php echo __('Font textu tlačítka','cms'); ?></div>
            <?php
            // text font
            cms_generate_field_font($group_id.'['.$field['id'].'][font]',$group_id.'_'.$field['id'].'_font',$content['font'],$field['content']['font'],'button_font');
            if(isset($field['content']['subtext_font'])) {
            ?>
                <div class="label"><?php echo __('Font podtextu tlačítka','cms'); ?></div>
                <?php
                // subtext font
                cms_generate_field_font($group_id.'['.$field['id'].'][subtext_font]',$group_id.'_'.$field['id'].'_font',(isset($content['subtext_font'])?$content['subtext_font']:array('font-family'=>'')),$field['content']['subtext_font'],'button_subtext_font');
            }
            ?>
            <div class="ve_half_set">
                <div class="label"><?php echo __('Pozadí tlačítka','cms'); ?></div>
                <?php
                //background
                echo '<div class="float-setting"><div class="sublabel">'.__('Počáteční barva pozadí','cms').'</div><input class="cms_text_input cms_color_input cms_change_button button_color1" type="text" name="'.$group_id.'['.$field['id'].'][background_color][color1]" id="'.$group_id.'_'.$field['id'].'_background_color1" value="'.$content['background_color']['color1']. '" /></div>';
                echo '<div class="float-setting"><div class="sublabel">'.__('Koncová barva pozadí','cms').'</div><input class="cms_text_input cms_color_input cms_change_button button_color2" type="text" name="'.$group_id.'['.$field['id'].'][background_color][color2]" id="'.$group_id.'_'.$field['id'].'_background_color2" value="'.$content['background_color']['color2']. '" /></div>';
                echo '<div class="cms_clear"></div>';
            
                ?>
            </div>
            <div class="ve_half_set ve_half_set_r">
                <div class="label"><?php echo __('Zakulacení rohů','cms'); ?></div>
                <?php 
                $corner=(isset($content['corner']))? $content['corner']:'0';
                $script='var container=jQuery(this).closest(".cms_button_select_container");
                              jQuery(".ve_content_button_forchange",container).css("-moz-border-radius",ui.value+"px");
                              jQuery(".ve_content_button_forchange",container).css("-webkit-border-radius",ui.value+"px");
                              jQuery(".ve_content_button_forchange",container).css("-khtml-border-radius",ui.value+"px");
                              jQuery(".ve_content_button_forchange",container).css("border-radius",ui.value+"px");';
                
                cms_generate_field_slider($group_id.'['.$field['id'].'][corner]',$group_id.'_'.$field['id'].'_corner',$corner, array('setting'=>array('min'=>'0','max'=>'90','unit'=>'px')),$script); ?> 
            </div>
            <div class="cms_clear"></div>
            <?php if(isset($field['content']['size'])) { ?>
            <div class="ve_half_set">
                <div class="label"><?php echo __('Výška','cms'); ?></div>
                <?php                              
                $script='var container=jQuery(this).closest(".cms_button_select_container");
                        jQuery(".cms_image_select_container .ve_content_button",container).css("padding-top",ui.value+"em");
                        jQuery(".cms_image_select_container .ve_content_button",container).css("padding-bottom",ui.value+"em");';
                
                cms_generate_field_slider($group_id.'['.$field['id'].'][height_padding]',$group_id.'_'.$field['id'].'_height_padding',$height_p, array('setting'=>array('min'=>'0.3','max'=>'1.5','step'=>'0.1','unit'=>'em')),$script); ?> 
            </div>
            <div class="ve_half_set ve_half_set_r">
                <div class="label"><?php echo __('Šířka','cms'); ?></div>
                <?php 
                $script='var container=jQuery(this).closest(".cms_button_select_container");
                        var leftWidth=ui.value;
                        if(jQuery(".cms_image_select_container .ve_content_button",container).hasClass("ve_content_button_icon")) leftWidth=ui.value-0.8;
                        jQuery(".cms_image_select_container .ve_content_button",container).css("padding-left",leftWidth+"em");
                        jQuery(".cms_image_select_container .ve_content_button",container).css("padding-right",ui.value+"em");';
                
                cms_generate_field_slider($group_id.'['.$field['id'].'][width_padding]',$group_id.'_'.$field['id'].'_width_padding',$width_p, array('setting'=>array('min'=>'0.4','max'=>'3','step'=>'0.1','unit'=>'em')),$script); ?> 
            </div>
            <div class="cms_clear"></div>
            
            
            <?php
            }
            if(isset($field['content']['icon'])) {
            ?>    
                    
                <div class="label"><?php echo __('Ikona','cms'); ?></div>
                <?php
                cms_generate_field_svg_iconselect($group_id.'['.$field['id'].'][icon]',$group_id.'_'.$field['id'].'_icon',$field['content']['icon'],(isset($content['icon'])?$content['icon']:array()));
            }
            ?>
            
    
            <div class="cms_clear"></div>
            
            <div class="cms_bs_border cms_button_setting_optioned <?php if(!isset($selected['border'])) echo 'cms_nodisp'; ?>">
                <div class="label"><?php echo __('Barva ohraničení','cms'); ?></div>
                <input class="button_border cms_text_input cms_color_input cms_change_button" type="text" name="<?php echo $group_id.'['.$field['id'].'][border-color]'; ?>" value="<?php echo $content['border-color']; ?>" />
            </div>
        </div>
        
        <?php // ***** hover ******* ?>
        <div id="select_ft_bs_<?php echo $field['id']; ?>_2" class="cms_setting_block_content cms_tab_container ft_bs_<?php echo $field['id']; ?>_container" style="display: none;">
              <script>
                jQuery(document).ready(function($) {
                    $("#<?php echo $group_id.'_'.$field['id'].'_hover_effect'; ?>").change(
                    function(){ 
                        var value=$(this).val();
                        if(!value) $(".custom_hover_effect_container_<?php echo $group_id.'_'.$field['id']; ?>").show();
                        else $(".custom_hover_effect_container_<?php echo $group_id.'_'.$field['id']; ?>").hide();
                    });
                });
              </script>
              <style>
                  .cms_show_group_<?php echo $tag_id.'_'.$field['show']; ?>:not(.cms_show_group_<?php echo $tag_id.'_'.$field['show']; ?>_<?php echo $meta ?>) {display: none;} 
              </style>
             <div>
                <div class="label"><?php echo __('Efekt po najetí myši','cms'); ?></div>
                <?php 
                $select_efect=array(
                    'options'=>array(
                        array('name'=>__('Zesvětlení','cms'),'value'=>'lighter'),                       
                        array('name'=>__('Zvětšení','cms'),'value'=>'scale'),
                        array('name'=>__('Ztmavení','cms'),'value'=>'darker'), 
                        array('name'=>__('Vlastní','cms'),'value'=>''),                                               
                    )
                );
                echo cms_generate_field_select($group_id.'['.$field['id'].'][hover_effect]',$group_id.'_'.$field['id'].'_hover_effect',isset($content['hover_effect'])?$content['hover_effect']:'', $select_efect); ?>
             </div>
             
             <div class="custom_hover_effect_container_<?php echo $group_id.'_'.$field['id']; ?> <?php if(isset($content['hover_effect']) && $content['hover_effect']) echo 'cms_nodisp'; ?>">
                 <div class="ve_half_set">
                    <div class="label"><?php echo __('Pozadí po najetí myši','cms'); ?></div>
                    <?php
                    //background hover
                    echo '<div class="float-setting"><div class="sublabel">'.__('Počáteční barva pozadí','cms').'</div><input class="cms_text_input cms_color_input cms_change_button button_hover_color1" type="text" name="'.$group_id.'['.$field['id'].'][hover_color][color1]" id="'.$group_id.'_'.$field['id'].'_hover_color1" value="'.((isset($content['hover_color']['color1']))?$content['hover_color']['color1']:''). '" /></div>';
                    echo '<div class="float-setting"><div class="sublabel">'.__('Koncová barva pozadí','cms').'</div><input class="cms_text_input cms_color_input cms_change_button button_hover_color2" type="text" name="'.$group_id.'['.$field['id'].'][hover_color][color2]" id="'.$group_id.'_'.$field['id'].'_hover_color2" value="'.((isset($content['hover_color']['color2']))?$content['hover_color']['color2']:''). '" /></div>';
                    echo '<div class="cms_clear"></div>';            
                    ?>
                </div>
                
                
                <div class="ve_half_set ve_half_set_r">
                    <div class="label"><?php echo __('Barva písma po najetí myši','cms'); ?></div>
                    <?php
                    //background hover
                    echo '<input class="cms_text_input cms_color_input cms_change_button button_hover_font_color" type="text" name="'.$group_id.'['.$field['id'].'][hover_font_color]" id="'.$group_id.'_'.$field['id'].'_hover_font_color" value="'.((isset($content['hover_font_color']))?$content['hover_font_color']:''). '" />';          
                    ?>
                </div>
                <div class="cms_clear"></div>

                <div class="cms_bs_border ve_half_set cms_button_setting_optioned <?php if(!isset($selected['border'])) echo 'cms_nodisp'; ?>">
                    <div class="label"><?php echo __('Barva ohraničení po najetí myši','cms'); ?></div>
                    <input class="button_border_hover cms_text_input cms_color_input cms_change_button" type="text" name="<?php echo $group_id.'['.$field['id'].'][border_hover-color]'; ?>" id="<?php echo $group_id.'_'.$field['id']; ?>_hover_border_color" value="<?php echo $content['border_hover-color']; ?>" />
                </div>
                <div class="cms_clear"></div>
                <?php
                if(isset($field['content']['icon'])) {
                ?>    
                <div class="ve_half_set ve_half_set_r">        
                    <div class="label"><?php echo __('Barva ikony po najetí myši','cms'); ?></div>
                    <input class="button_icon_hover cms_text_input cms_color_input" type="text" name="<?php echo $group_id.'['.$field['id'].'][icon_hover-color]'; ?>" value="<?php echo $content['icon_hover-color']; ?>" />
                </div>
                <?php
                }
                ?>
                <div class="cms_clear"></div>
            </div>
        </div>
    </div>
    </div>
    <?php
}

function field_type_google_map($field, $meta, $group_id, $tag_id) {
    global $cms;
    
    $id=$group_id.'_'.$field['id'];
    $name=$tag_id.'['.$field['id'].']';
    
    $content=($meta)? $meta : $field['content'];
    $zoom=($content['zoom'])? $content['zoom'] : 10;
    
    $gmap_api=get_option('ve_google_api');
    $gmap_api_connected=true;
    
    
    // if is no api key saved
    if(!$gmap_api || !isset($gmap_api['api_key']) || !$gmap_api['api_key']) {
      $gmap_api_connected=false;
      ?>
      <div class="cms_message_box cms_info_box_gray">
      <h3><?php echo __('Použití google map vyžaduje API klíč','cms'); ?></h3>
      <p>1. <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank"><?php echo __('Vygenerujte si svůj api klíč (zdarma).','cms'); ?></a></p>
      <p>2. <?php echo __('Zadejte svůj API klíč do formuláře níže a klikněte na Uložit API klíč','cms'); ?></p>
      <form action="" method="post">
          <input type="text" class="cms_text_input" name="ve_save_google_api_key" placeholder="<?php echo __('Zde vložte svůj API klíč.','cms'); ?>" />
          <input type="submit" class="cms_button" value="<?php echo __('Uložit API klíč','cms'); ?>" />
      </form>
      </div>
      
      <style>
        .cms_show_group_ve_style_google_map, #cmsl_submit_save_element_setting {display: none;}
      </style>

      <?php
    }
    
    
    if(!$gmap_api_connected) echo '<div class="ve_nodisp">';

    ?>
    <div class="set_form_row"> 
        <div class="label"><?php echo __('Adresa','cms'); ?></div>
        <input id="mw_gm_autocomplete" class="cms_text_input" type="text" name="<?php echo $name.'[address]'; ?>" id="<?php echo $id.'_address'; ?>" value="<?php echo $content['address']; ?>" />
        <span class="cms_description"><?php __('Zadejte adresu, kterou chcete na mapě vyznačit.','cms'); ?></span>
        <script>
          var autocomplete;

            autocomplete = new google.maps.places.Autocomplete(
                /** @type {HTMLInputElement} */(document.getElementById('mw_gm_autocomplete')),
                { types: ['geocode'] });
            google.maps.event.addListener(autocomplete, 'place_changed', function() {});
        </script>
    </div>
    <div class="set_form_row"> 
        <div class="label"><?php echo __('Zoom mapy','cms'); ?></div>
        <?php     
      
        cms_generate_field_slider($name.'[zoom]',$id.'_zoom',$zoom, array('setting'=>array('min'=>'0','max'=>'20','unit'=>''))); 
        ?>
    </div>
    <div class="cms_clear"></div>
    <?php
    if(!$gmap_api_connected) echo '</div>';

}

function field_type_events_list($field, $meta, $group_name, $group_id) {
  $args = array(
          'posts_per_page'   => -1,
          'post_type' => 'mw_event',
          'orderby' => 'meta_value_num',
          'meta_key' => 'mw_event_date_start',
          'order' => 'ASC'
  );   
  $items = get_posts($args);
  
  $old=array();
  
  echo '<a href="'.admin_url( 'edit.php?post_type=mw_event').'" class="cms_button_secondary" target="_blank">'.__('Přidat novou akci','cms').'</a>';
  
  echo '<div class="mw_events_setting_list">';
  if(!empty($items)) {

      foreach( $items as $item ){
          $event_date = get_post_meta($item->ID,'mw_event_date_start',true);
          
          $event_setting=get_post_meta($item->ID,'ve_event',true);
          
          if(isset($event_setting['date_end']) && $event_setting['date_end'] && $event_date<=strtotime($event_setting['date_end'])) {
            $date_end=' - '.date( 'j.n.', strtotime($event_setting['date_end']));
          } else $date_end='';
          
          if($event_date && $event_date>current_time('timestamp')) {
              echo '<a class="mw_events_setting_list_item ve_setting_container" href="'.admin_url( 'post.php?post='.$item->ID.'&action=edit').'" target="_blank">';
              echo '<div class="mw_events_setting_list_item_title"><span>'.date('d.m.',$event_date).$date_end.'</span> '.$item->post_title.'</div>';
              echo '<div class="mw_events_setting_list_item_hover">'.__('Upravit', 'cms').'</div>'; 
              echo '<div class="cms_clear"></div></a>';
          } else $old[]=$item;
      }
      
      if(!empty($old)) {
        echo '<div class="label">'.__('Již proběhlé akce','cms').'</div>';
        foreach( $old as $item ){
          
            $event_setting=get_post_meta($item->ID,'ve_event',true);
            
            if(isset($event_setting['date_end']) && $event_setting['date_end'] && $event_date<=strtotime($event_setting['date_end'])) {
              $date_end=' - '.date( 'j.n.', strtotime($event_setting['date_end']));
            } else $date_end='';
          
            $event_date = get_post_meta($item->ID,'mw_event_date_start',true);
            echo '<a class="mw_events_setting_list_item ve_setting_container" href="'.admin_url( 'post.php?post='.$item->ID.'&action=edit').'" target="_blank">';
            echo '<div class="mw_events_setting_list_item_title"><span>'.date('d.m.',$event_date).$date_end.'</span> '.$item->post_title.'</div>';
            echo '<div class="mw_events_setting_list_item_hover">'.__('Upravit', 'cms').'</div>'; 
            echo '<div class="cms_clear"></div></a>';
        }
      }
      
      
  } else {
    echo '<div class="cms_info_box">'.__('Nejsou vytvořené žádné akce, klikněte na tlačítko "Přidat novou akci" a vytvořte první akci. Správa kalendáře akcí se nachází v administraci wordpressu.','cms').'</div>';
  }
  echo '</div>';
  
  
}
