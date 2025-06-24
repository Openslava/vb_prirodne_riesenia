<?php
function get_template_url_image() {
  return str_replace ( home_url() , '' , get_bloginfo('template_url') );
}

function  ev_init_visual_editor() { 
    global $vePage;
    $vePage->inicialize();    
}

add_action('admin_menu',  'add_visualeditor_template'); 
function add_visualeditor_template() {
    add_meta_box("visualeditor_template_set", __('Šablona','cms'), 'visualeditor_template_selector', 'page', 'side', 'core');   
}

function visualeditor_template_selector($post) {  
    global $cms;  
    $template = get_post_meta( $post->ID, 've_page_template', true );
    if(!$template) $template=array( 'type' => 'page', 'directory' => 'page/1/' ) ; 
    $temp=explode("/", $template['directory']); 
    ?>
    <a title="" class="ve_change_template_but" data-id="<?php echo $post->ID; ?>" href="#"><img width="100%" src="<?php echo get_bloginfo('template_url').$cms->p_templates[$temp[0]]['path'].$temp[1]; ?>/thumb.jpg" alt="" /></a>
    <a class="ve_change_template_but" href="#"><?php echo __('Změnit šablonu stránky','cms_ve'); ?></a>
    <?php
}  

function field_type_visualedit($field, $meta, $group_id, $tagid, $post_id) {
    ?>
    <a class="cms_button_secondary open_window_editor edit_window_editor" data-type="<?php echo $field['content_type']; ?>" data-url="http://localhost/mio-cms/?window_editor=<?php echo $field['content_type']; ?>" data-id="<?php echo $post_id; ?>" href="#"><?php echo $field['button_text']; ?></a>
    <?php
}

function field_type_page_statistics($field, $meta, $group_id, $tagid, $post_id) {
    $con_meta=get_post_meta($post_id,'page_conversion_rate',true);
    if($con_meta && is_array($con_meta)) {
        ?>
        <table class="ve_page_statistic_field ve_inside_setting_table">
                <tr>
                    <th><?php echo __('Stránka','cms_ve'); ?></th>
                    <th><?php echo __('Počet zobrazení stránky','cms_ve'); ?></th>
                    <th><?php echo __('Počet zobrazení cíle','cms_ve'); ?></th>
                    <th><?php echo __('Konverzní poměr','cms_ve'); ?></th>
                </tr>
        <?php
        
        $i=1;
        foreach($con_meta as $id=>$con) {
            if(isset($con['con_target']) && $con['con_target']>0 && isset($con['con_source']) && $con['con_source']>0) $conversion=($con['con_target']/$con['con_source'])*100;
            else $conversion=0;
        
            if($i) $class='class="odd"';
            else $class='';

            ?>
            
                <tr <?php echo $class; ?>>
                    <td>
                        <?php if(get_page($id)) { ?>
                            <a target="_blank" href="<?php echo get_permalink($id); ?>"><?php echo get_the_title($id); ?></a>
                        <?php } else echo __('Stránka byla smazána.','cms_ve'); ?>
                    </td>
                    <td><?php echo (isset($con['con_source']))? $con['con_source']:0; ?></td>
                    <td><?php echo (isset($con['con_target']))? $con['con_target']:0; ?></td>
                    <td><?php echo number_format($conversion,3,',',' ')."%"; ?></td>
                </tr>
            
            <?php
            $i=($i)? 0:1;
        }
        ?>
        </table>
        <button id="ve_reset_page_statistics" class="cms_button_secondary" data-id="<?php echo $post_id; ?>"><?php echo __('Vynulovat výsledky','cms_ve'); ?></button>
        <?php
    } else {
        ?>
            <div><?php echo __('Momentálně nejsou k dispozici žádná data.','cms_ve'); ?></div>
        <?php
    }
}

function ve_reset_page_statistics_ajax() {
    delete_post_meta($_POST['post_id'], 'page_conversion_rate');
    die();
}
add_action('wp_ajax_ve_reset_page_statistics', 've_reset_page_statistics_ajax');

/* Multipage
************************************************************************** */

function field_type_multipageselect($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");

    ?>
    <div id="ve_multipageselect_container">
        <?php
        $i=0;
        $pages = get_pages(array('post_status'=>'publish'));
        if(is_array($content)) {            
            foreach($content as $key=>$page) {
                ?>
                <div id="ve_multipageselect_<?php echo $i; ?>" class="ve_item_multipageselect">
                    <?php ve_generate_pageselect($group_id.'['.$field['id'].'][]',$group_id.'_'.$field['id'].'_'.$i,$pages,$page); ?>
                </div>
                <?php
                $i++;
            }
        }
        ?>   
    </div>
    <button id="ve_add_multipage" class="cms_button_secondary" data-id="<?php echo $i; ?>" data-name="<?php echo $group_id.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Přidat stránku','cms_ve'); ?></button>
    <?php
}
function field_type_pagecheck($field, $meta, $group_name, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");
    
    $pages=get_pages();
    
    foreach($pages as $page) {
        ?>
        <div>
            <input type="checkbox" name="<?php echo $group_name.'['.$field['id'].']['.$page->ID.']'; ?>" id="<?php echo $group_id.'_'.$field['id'].'_'.$page->ID; ?>" value="<?php echo $page->ID; ?>">
            <label for="<?php echo $group_id.'_'.$field['id'].'_'.$page->ID; ?>"><?php echo $page->post_title ?></label>
        </div>
        <?php
    }

}
function ve_generate_pageselect($name,$id,$pages,$page) {    
    ?>
    <a class="ve_delete_select" href="#" title="<?php echo __('Smazat stránku','cms_ve'); ?>"></a>
    <?php
    Cms::select_page($pages, $page, $name,$id);
    
}

function ve_generate_multipageselect_ajax() {
    $pages = get_pages(array('post_status'=>'publish'));
    ve_generate_pageselect($_POST['tagname'].'[]',$_POST['tagid'].'_'.$_POST['id'],$pages,'');
    die();
}
add_action('wp_ajax_ve_generate_multipageselect_ajax', 've_generate_multipageselect_ajax');

/* Testimonials 
************************************************************************** */

function field_type_testimonials($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");

    ?>
    <div id="ve_testimonials_container" class="ve_sortable_items">
        <?php
        $i=0;
        if(is_array($content)) {            
            foreach($content as $key=>$testimonial) {
                ?>
                <div id="ve_testimonial_<?php echo $i; ?>" class="ve_item_container ve_setting_container ve_sortable_item">
                    <?php ve_generate_testimonial($group_id.'['.$field['id'].']['.$i.']',$group_id.'_'.$field['id'].'_'.$i,$testimonial); ?>
                </div>
                <?php
                $i++;
            }
        }
        ?>   
    </div>
    <button id="ve_add_testimonial" class="cms_button_secondary" data-id="<?php echo $i; ?>" data-name="<?php echo $group_id.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Přidat referenci','cms_ve'); ?></button>
    <?php
}
function ve_generate_testimonial($name,$id,$testimonial) {    
    ?>
    <div class="ve_item_head">
        <span class="ve_sortable_handler"></span>
        <?php echo stripslashes($testimonial['name']); ?>
        <a class="ve_delete_testimonial ve_delete_setting" href="#" title="<?php echo __('Smazat referenci','cms_ve'); ?>"></a>
    </div>
    <div class="ve_item_body <?php if($testimonial['name']=='') echo 've_item_body_v'; ?>">
        <div class="label"><?php echo __('Text reference','cms_ve'); ?></div>
        <?php echo cms_generate_field_textarea($name.'[text]',$id.'_text',(isset($testimonial['text']))? stripslashes($testimonial['text']) : ''); ?>
        <div class="label"><?php echo __('Jméno','cms_ve'); ?></div>
        <?php echo cms_generate_field_text($name.'[name]',$id.'_name',(isset($testimonial['name']))? stripslashes($testimonial['name']) : ''); ?>
        <div class="label"><?php echo __('Firma/Pozice','cms_ve'); ?></div>
        <?php echo cms_generate_field_text($name.'[company]',$id.'_company',(isset($testimonial['company']))? stripslashes($testimonial['company']): ''); ?>
        <div class="label"><?php echo __('Fotografie','cms_ve'); ?></div>
        <?php cms_generate_field_upload($name.'[image]',$id.'_image',(isset($testimonial['image']))? stripslashes($testimonial['image']): ''); ?>
    </div>
    <?php
}

function ve_generate_testimonial_ajax() {
    //echo $_POST['name'][$_POST['id']];
    $item=array(
        'name'=>'',
        'text'=>'',
        'company'=>'',
        'image'=>'',
    );
    ve_generate_testimonial($_POST['tagname'].'['.$_POST['id'].']',$_POST['tagid'].'_'.$_POST['id'],$item);
    die();
}
add_action('wp_ajax_ve_generate_testimonial_ajax', 've_generate_testimonial_ajax');

/* Bullets - used by checklist element
************************************************************************** */

function field_type_bullets($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");
    ?>
    <div id="ve_items_container" class="ve_sortable_items">
        <?php
        $i=0;
        if(is_array($content)) {            
            foreach($content as $key=>$bullet) {
                ?>
                <div id="ve_item_<?php echo $i; ?>" class="ve_item_container ve_setting_container ve_sortable_item">
                    <?php ve_generate_bullet($group_id.'['.$field['id'].']['.$i.']',$group_id.'_'.$field['id'].'_'.$i,$bullet); ?>
                </div>
                <?php
                $i++;
            }
        }
        ?>   
    </div>
    <button id="ve_add_bullet" class="cms_button_secondary" data-id="<?php echo $i; ?>" data-setting="<?php echo $field['setting']; ?>" data-name="<?php echo $group_id.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Přidat odrážku','cms_ve'); ?></button>
    <?php
}
function ve_generate_bullet($name,$id,$bullet) {    
    ?>
    <div class="ve_item_head">
        <span class="ve_sortable_handler"></span>
        <?php 
        if(isset($bullet['title']) && $bullet['title']) $title=stripslashes($bullet['title']);
        else if(isset($bullet['text']) && $bullet['text']) $title=wp_trim_words(stripslashes($bullet['text']),10);
        else $title="";
        
        echo $title; 
        
        ?>
        <a class="ve_delete_bullet ve_delete_setting" href="#" title="<?php echo __('Smazat odrážku','cms_ve'); ?>"></a>
    </div>
    <div class="ve_item_body <?php if($title=='') echo 've_item_body_v'; ?>">
        <?php if(isset($bullet['title'])) { ?>
        <div class="label"><?php echo __('Nadpis','cms_ve'); ?></div>
        <?php echo cms_generate_field_text($name.'[title]',$id.'_title',(isset($bullet['title']))? stripslashes($bullet['title']) : ''); ?>
        <?php } ?>
        <div class="label"><?php echo __('Text','cms_ve'); ?></div>
        <?php echo cms_generate_field_textarea($name.'[text]',$id.'_text',(isset($bullet['text']))? stripslashes($bullet['text']) : ''); ?>
    </div>
    <?php
}

function ve_generate_bullet_ajax() {
    //echo $_POST['name'][$_POST['id']];
    if(isset($_POST['setting']) && $_POST['setting']=='classic') {
        $item=array(
            'text'=>'',
        );
    } else {
        $item=array(
            'title'=>'',
            'text'=>'',
        );
    }
    ve_generate_bullet($_POST['tagname'].'['.$_POST['id'].']',$_POST['tagid'].'_'.$_POST['id'],$item);
    die();
}
add_action('wp_ajax_ve_generate_bullet_ajax', 've_generate_bullet_ajax');

/* Simple feature
************************************************************************** */

function field_type_simple_feature($field, $meta, $group_id, $group_name) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:'');
    $feature_fields=(isset($field['fields']))? implode(',',$field['fields']):'text';
    $name=$group_id;
    $id=$group_name;
    
    $add_but_text=isset($field['text_add'])? $field['text_add'] : __('Přidat vlastnost','cms_ve');
    
    ?>
    <div class="ve_items_feature_container">
            <div class="ve_features_container">
            <?php 
            $i=0;
            if(!empty($content)) {                
                foreach($content as $key=>$feature) {
                    ?>
                    <div class="ve_item_feature_<?php echo $i; ?> ve_item_feature_container">                    
                        <?php ve_generate_simple_feature($name.'[features]['.$i.']',$id.'_features_'.$i,$feature,$feature_fields); ?>
                    </div>
                    <?php
                    $i++;
                }
            }
            ?>
            </div>
            <button class="ve_add_simple_feature cms_button_secondary" data-fields="<?php echo $feature_fields; ?>" data-id="<?php echo $i; ?>" data-name="<?php echo $name; ?>"  data-tagid="<?php echo $id; ?>"><?php echo $add_but_text; ?></button>
    </div> 
    <?php

}
function ve_generate_simple_feature($name,$id,$feature,$feature_fields) {
   $fields=explode(',',$feature_fields);
   if(in_array('text',$fields)) echo '<input class="cms_text_input" placeholder="'.__('Vlastnost','cms_ve').'" type="text" name="'.$name.'[text]" id="'.$id.'_text" value="'.htmlspecialchars(stripslashes($feature['text'])).'" />'; 
   if(in_array('price',$fields)) echo '<input class="cms_text_input" placeholder="'.__('Cena','cms_ve').'" type="text" name="'.$name.'[price]" id="'.$id.'_price" value="'.htmlspecialchars(stripslashes($feature['price'])).'" />';
   if(in_array('content',$fields)) echo '<input class="cms_text_input" placeholder="'.__('Obsahuje','cms_ve').'" type="text" name="'.$name.'[content]" id="'.$id.'_content" value="'.htmlspecialchars(stripslashes($feature['content'])).'" />';
   if(in_array('answer',$fields)) echo '<input class="cms_text_input" placeholder="'.__('Odpověď','cms_ve').'" type="text" name="'.$name.'[answer]" id="'.$id.'_answer" value="'.htmlspecialchars(stripslashes($feature['answer'])).'" />';
   if(in_array('right_answer',$fields)) echo '<input type="checkbox" name="'.$name.'[right_answer]" id="'.$id.'_right_answer" value="1" '.(isset($feature['right_answer'])?'checked="checked"':'').' /><label for="'.$id.'_right_answer">'.__('Správná odpověď','cms_ve').'</label>';
   
   echo '<a class="ve_delete_feature" href="#" title="'.__('Smazat?','cms_ve').'"></a>';
}
function ve_generate_simple_feature_ajax() {
    $item=array(
        'text'=>'',
        'price'=>''
    );
    ve_generate_simple_feature($_POST['tagname'].'[features]['.$_POST['id'].']',$_POST['tagid'].'_features_'.$_POST['id'],$item,$_POST['fields']);
    die();
}
add_action('wp_ajax_ve_generate_simple_feature_ajax', 've_generate_simple_feature_ajax');

//row height setting
function field_type_row_height($field, $meta, $group_id, $tagid) {  

    $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
    
    $id=$tagid.'_'.$field['id'];
    $name=$group_id.'['.$field['id'].']';
    
    echo '<script>
            jQuery(document).ready(function($) {
                $("#'.$id.'_full_height").change(function(){
                    $(".row_full_height_setting").toggle();
                });
                $("#'.$id.'_arrow").change(function(){
                    $(".row_full_height_arrow_setting").toggle();
                });
            });
    </script>';
    
    ?>
    <input value="1" type="checkbox" name="<?php echo $name.'[full_height]'; ?>" id="<?php echo $id.'_full_height'; ?>" <?php echo (isset($content['full_height']) ? 'checked="checked"' : ''); ?> />
    <label for="<?php echo $id.'_full_height'; ?>"><?php echo __('Roztáhnout řádek přes celou obrazovku.', 'cms_ve'); ?></label>
    
    <div class="row_full_height_setting <?php if(!isset($content['full_height'])) echo 'cms_nodisp'; ?>" >
        <div>
            <input value="1" type="checkbox" name="<?php echo $name.'[noheader]'; ?>" id="<?php echo $id.'_noheader'; ?>" <?php echo (isset($content['noheader']) ? 'checked="checked"' : ''); ?> />
            <label for="<?php echo $id.'_noheader'; ?>"><?php echo __('Odečíst výšku hlavičky', 'cms_ve'); ?></label>
        </div>
        <div>
            <input value="1" type="checkbox" name="<?php echo $name.'[centered_content]'; ?>" id="<?php echo $id.'_centered_content'; ?>" <?php echo (isset($content['centered_content']) ? 'checked="checked"' : ''); ?> />
            <label for="<?php echo $id.'_centered_content'; ?>"><?php echo __('Zarovnat obsah na vertikální střed řádku.', 'cms_ve'); ?></label>
        </div>
        <div>
            <input value="1" type="checkbox" name="<?php echo $name.'[arrow]'; ?>" id="<?php echo $id.'_arrow'; ?>" <?php echo (isset($content['arrow']) ? 'checked="checked"' : ''); ?> />
            <label for="<?php echo $id.'_arrow'; ?>"><?php echo __('Zobrazit na spodní části řádku šipku pro odskrolování na obsah níže.', 'cms_ve'); ?></label>
        </div>
        <div class="row_full_height_arrow_setting <?php if(!isset($content['arrow'])) echo 'cms_nodisp'; ?>">
            <div class="label"><?php echo __('Barva šipky', 'cms_ve'); ?></div>
            <?php 
            cms_generate_field_select(
                $name.'[arrow_color]',
                $id.'_arrow_color',
                $content['arrow_color'],
                array(
                    'options' => array(
                        array('name' => 'Světlé', 'value' => '#fff'),
                        array('name' => 'Tmavé', 'value' => '#000'),
                    )
                )
            ); 
            ?>
        </div>
    </div>
    
<?php    
}


/* Custom form
************************************************************************** */

function field_type_customform($field, $meta, $group_id) {
    $content=($meta!="") ? $meta : ((isset($field['content']))? $field['content']:"");

    if($field['setting']['type']!='url') {
        echo '<style>.ve_items_container_'.$group_id.'_'.$field['id'].' .ve_formitem_name_container {display:none;}</style>';
    }
    ?>
    <div id="ve_items_container" class="ve_sortable_items ve_items_container_<?php echo $group_id.'_'.$field['id']; ?>">
        <?php
        $i=0;
        if(is_array($content)) {            
            foreach($content as $key=>$row) {
                ?>
                <div id="ve_item_<?php echo $i; ?>" class="ve_item_container ve_setting_container ve_sortable_item">                    
                    <?php ve_generate_formitem($group_id.'['.$field['id'].']['.$i.']',$group_id.'_'.$field['id'].'_'.$i,$i,$row); ?>
                </div>
                <?php
                $i++;
            }
        }
        ?>   
    </div>
    <button class="ve_add_formitem cms_button_secondary" data-id="<?php echo $i; ?>" data-name="<?php echo $group_id.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $group_id.'_'.$field['id']; ?>"><?php echo __('Přidat formulářové pole','cms_ve'); ?></button>
    <?php
}
function ve_generate_formitem($name,$id,$order,$item) {    
    ?>
    <div class="ve_item_head">
        <span class="ve_sortable_handler"></span>
        <?php echo stripslashes($item['title']); ?>
        <a class="ve_delete_formitem ve_delete_setting" href="#" title="<?php echo __('Smazat formulářové pole','cms_ve'); ?>"></a>
    </div>
    <div class="ve_item_body <?php if($item['title']=='') echo 've_item_body_v'; ?>">
        <div class="label"><?php echo __('Popisek pole','cms_ve'); ?></div>
        <?php echo cms_generate_field_text($name.'[title]',$id.'_title',(isset($item['title']))? stripslashes($item['title']) : ''); ?>
        <div class="ve_item_chfield"><?php echo cms_generate_field_checkbox($name.'[required]',$id.'_required',(isset($item['required']))? $item['required'] : '', __('Povinné pole','cms_ve')); ?></div>
        <div class="ve_item_chfield"><?php echo cms_generate_field_checkbox($name.'[email]',$id.'_email',(isset($item['email']))? $item['email'] : '', __('Emailová adresa','cms_ve')); ?></div>
        <div class="ve_formitem_name_container">
            <div class="label"><?php echo __('Název pole','cms_ve'); ?>*</div>
            <?php echo cms_generate_field_text($name.'[name]',$id.'_name',(isset($item['name']))? stripslashes($item['name']) : ''); ?>
        </div>
        <div class="label"><?php echo __('Typ pole','cms_ve'); ?></div>
        <select class="formitem_select_type" id="<?php echo $name.'_type' ?>" name="<?php echo $name.'[type]' ?>">
            <option value="text" <?php if($item['type']=='text') echo 'selected="selected"' ?>><?php echo __('Jednořádkové textové pole (text)','cms_ve'); ?></option> 
            <option value="textarea"<?php if($item['type']=='textarea') echo 'selected="selected"' ?>><?php echo __('Víceřádkové textové pole (textarea)','cms_ve'); ?></option> 
            <option value="select"<?php if($item['type']=='select') echo 'selected="selected"' ?>><?php echo __('Výběr jedné možnosti z přednastavených hodnot v roletce (select)','cms_ve'); ?></option> 
            <option value="checkbox"<?php if($item['type']=='checkbox') echo 'selected="selected"' ?>><?php echo __('Výběr více možností v seznamu zatrhávacích polí (seznam check boxů)','cms_ve'); ?></option> 
            <option value="radio"<?php if($item['type']=='radio') echo 'selected="selected"' ?>><?php echo __('Výběr jedné položky z přednastavených hodnot v seznamu přepínačů (radio)','cms_ve'); ?></option>     
            <option value="password"<?php if($item['type']=='password') echo 'selected="selected"' ?>><?php echo __('Heslo (password)','cms_ve'); ?></option>         
            <option value="agree"<?php if($item['type']=='agree') echo 'selected="selected"' ?>><?php echo __('Souhlas (zaškrtávátko)','cms_ve'); ?></option>      
        </select> 
        <div class="formitem_content <?php if($item['type']=='select' || $item['type']=='checkbox' || $item['type']=='radio' || $item['type']=='agree') echo 'cms_nodisp'; ?>">
            <div class="label"><?php echo __('Obsah pole','cms_ve'); ?></div>
            <?php echo cms_generate_field_text($name.'[content]',$id.'_content',(isset($item['content']))? stripslashes($item['content']) : ''); ?>
        </div>
        <div class="formitem_subitems <?php if($item['type']=='text' || $item['type']=='textarea' || $item['type']=='agree' || $item['type']=='password') echo 'cms_nodisp'; ?>">
            <div class="label"><?php echo __('Možnosti','cms_ve'); ?></div>
            <div class="ve_items_feature_container ve_items_feature_container_<?php echo $id; ?>">
                <div class="ve_items_feature_container_<?php echo $order; ?>">
                <?php 
                $i=0;
                if(!empty($item['subitems'])) {                
                    foreach($item['subitems'] as $key=>$subitem) {
                        ?>
                        <div id="ve_item_feature_<?php echo $order.'_'.$i; ?>" class="ve_item_feature_container">                    
                            <?php ve_generate_formitem_item($name.'[subitems]['.$i.']',$id.'_subitems_'.$i,$subitem); ?>
                        </div>
                        <?php
                        $i++;
                    }
                }
                ?>
                </div>
                <button class="ve_add_formitem_subitem cms_button_secondary" data-itemid="<?php echo $order; ?>" data-id="<?php echo $i; ?>" data-name="<?php echo $name; ?>"  data-tagid="<?php echo $id; ?>"><?php echo __('Přidat možnost','cms_ve'); ?></button>
            </div>        
        </div>
        <div class="formitem_agree <?php if($item['type']=='select' || $item['type']=='checkbox' || $item['type']=='radio' || $item['type']=='text' || $item['type']=='textarea') echo 'cms_nodisp'; ?>">
            <div class="label"><?php echo __('Text odkazu','cms_ve'); ?></div>
            <?php echo cms_generate_field_text($name.'[agree_link_text]',$id.'_agree_link_text',(isset($item['agree_link_text']))? stripslashes($item['agree_link_text']) : ''); ?>
            <div class="label"><?php echo __('Odkaz','cms_ve'); ?></div>
            <?php echo cms_generate_field_text($name.'[agree_link]',$id.'_agree_link',(isset($item['agree_link']))? stripslashes($item['agree_link']) : ''); ?>
        </div>
         
    </div> 
    <?php
}

function ve_generate_formitem_item($name,$id,$content) {
   echo '<input class="cms_text_input" type="text" name="'.$name.'[text]" id="'.$id.'_text" value="'.htmlspecialchars(stripslashes($content['text'])).'" />'; 
   echo '<a class="ve_delete_subitem" href="#" title="'.__('Smazat','cms_ve').'"></a>';
}

function ve_generate_formitem_ajax() {
    $item=array(
        'title'=>'',
        'required'=>'',
        'content'=>'',
        'name'=>'',
        'type'=>'text',
        'subitems'=>array(),
    );
    ve_generate_formitem($_POST['tagname'].'['.$_POST['id'].']',$_POST['tagid'].'_'.$_POST['id'],$_POST['id'],$item);
    die();
}
add_action('wp_ajax_ve_generate_formitem_ajax', 've_generate_formitem_ajax');

function ve_generate_formitem_item_ajax() {
    $item=array(
        'text'=>'',
    );
    ve_generate_formitem_item($_POST['tagname'].'[subitems]['.$_POST['id'].']',$_POST['tagid'].'_subitems_'.$_POST['id'],$item);
    die();
}
add_action('wp_ajax_ve_generate_formitem_item_ajax', 've_generate_formitem_item_ajax');

// Gallery lightbox
function my_get_attachment_link($html) { 
  $pattern ="/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
  $replacement = '<a$1href=$2$3.$4$5 class="open_lightbox" rel="gallery"$6>';
  return preg_replace($pattern, $replacement, $html);
} 
add_filter('wp_get_attachment_link', 'my_get_attachment_link', 10, 1);

function add_lightbox($content) {
    $pattern ="/<a(.*?)href=('|\")(.*?).(bmp|gif|jpeg|jpg|png)('|\")(.*?)>/i";
    $replacement = '<a$1href=$2$3.$4$5 class="open_lightbox"$6>';
    return preg_replace($pattern, $replacement, $content);
}

// Paste plain text in editor
function plainpaste_tinymce_settings($settings)
{
    $settings['paste_text_sticky'] = 'true';
    $settings['setup'] = 'function(ed) { ed.onInit.add(function(ed) { ed.pasteAsPlainText = true; }); }';

    return $settings;
}
add_filter('tiny_mce_before_init','plainpaste_tinymce_settings');



 
