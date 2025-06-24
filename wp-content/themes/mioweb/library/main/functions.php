<?php    
      
if ( ! function_exists( 'load_child_theme' ) ) {
    function load_child_theme() {
    }
}

// Custom post_types
// ******************************************************************************************

function add_custom_post_types() {
    global $cms;
    if($cms->p_types) {
        foreach ($cms->p_types as $type) {                                                 
            register_post_type($type['name'], $type['args']);
        }
    }
}


// Custom fields
// ******************************************************************************************

                             
function page_add_meta() {
    global $cms;
    foreach($cms->p_sets as $id => $meta) {
        if(isset($meta['include'])) {
            foreach($meta['include'] as $type) {
               add_meta_box($meta['id'], $meta['title'], 'meta_show_box', $type, $meta['context'], $meta['priority'], array( 'meta_id' => $id));   
            }
        }
        else {          
            add_meta_box($meta['id'], $meta['title'], 'meta_show_box', 'page', $meta['context'], $meta['priority'], array( 'meta_id' => $id));
            add_meta_box($meta['id'], $meta['title'], 'meta_show_box', 'post', $meta['context'], $meta['priority'], array( 'meta_id' => $id));
            if($cms->p_types) { 
                foreach($cms->p_types as $type) {  
                   if(!$meta['exclude'] || !in_array($type['name'], $meta['exclude']))
                      add_meta_box($meta['id'], $meta['title'], 'meta_show_box', $type['name'], $meta['context'], $meta['priority'], array( 'meta_id' => $id));   
                }
            }
        }
    }
}

function meta_show_box($post,$metabox) {
    global $cms;
    $id=$cms->p_sets[$metabox['args']['meta_id']]['id'];
    echo '<input type="hidden" name="admin_save_nonce" value="', wp_create_nonce('admin_save_nonce'), '" />'; //becose of this save hook save meta boxes only from admin
    show_page_set($cms->p_set[$id], $id, $post);
}

function show_page_set($meta_set, $set_id, $post, $single=0) {
    // Use nonce for verification
    $meta_set=allowed_sets($meta_set, $post->post_type, $post->ID);
    echo '<input type="hidden" name="meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />'; 
    if(!$single) write_settabs($meta_set, $set_id);  
    $i=1;  
    foreach ($meta_set as $set) { 
        if(!$single || $single==$set['id']) {
            if(!$single) echo '<div id="'.$set['id'].'" class="cms_setting_block_content cms_tab_content_count_'.count($meta_set).' cms_tab_content cms_tab_content-'.$i.'  cms_set_'.$set_id.'_container">';  
            if(isset($set['info'])) echo '<div class="cms_setting_block_info"> <div class="cms_info_box_gray">'.$set['info'].'</div></div>';                         
            $meta = get_post_meta($post->ID, $set['id'], true);
            write_meta($set['fields'], $meta, $set['id'], $set['id'], $post->ID);   
            if(!$single) echo '</div>';
            $i++;
        }
    }
    
}
function allowed_sets($meta_set, $post_type, $post_id) {
    $new_set=array();
    if($post_id) {
        $meta_template=get_post_meta($post_id, 've_page_template', true);    
        if(!$meta_template) $meta_template=array ( 'type' => 'page', 'directory' => 'page/1/' );
        $template=$meta_template['directory'];   
    } else $template='';
    foreach($meta_set as $meta) {
        if(isset($meta['include']) && $meta['include']) {
            if (in_array($post_type, $meta['include'])) $new_set[]=$meta; 
        } else if(isset($meta['for_templates']) && $meta['for_templates']) {
            if (in_array($template, $meta['for_templates'])) $new_set[]=$meta;
        }
        else $new_set[]=$meta;
    }
    return $new_set;  
}
function write_settabs($meta_set, $set_id) {
  
  if(count($meta_set)>1) {
      echo '<ul class="cms_tabs cms_page_setting_subtabs cms_set_'.$set_id.'_tab">';
      $i=1;  
      foreach ($meta_set as $set) { 
            $class=($i==1)? "active" : "";   
            echo '<li><a href="#'.$set['id'].'" class="'.$class.'" data-group="cms_set_'.$set_id.'">';     
            echo $set['title'];
            echo '</a></li>';
            $i++;
        }
      echo '</ul>';
  }
}
function write_meta($fields, $meta, $tagname, $tagid, $post_id='', $type='setting', $array_id='') {  

    $editor=0;
 
    foreach($fields as $field) {
    
        $title='';    
        if(isset($field['name']) && $field['name']) $title=$field['name']; 
        if(isset($field['title']) && $field['title']) $title=$field['title']; 
        
        $show_class=(isset($field['show_group'])
          ? ' cms_show_group_'.$tagid.'_'.$field['show_group'].' '
          . ( isset($field['show_val'])
            ? ' cms_show_group_'.$tagid.'_'.$field['show_group'].'_'
              . implode(' cms_show_group_'.$tagid.'_'.$field['show_group'].'_',
              explode(',',$field['show_val']))
            :''
          )
          : ''
        );
        $html_after = isset($field['html_after']) ? $field['html_after'] : '';
        
        if(isset($field['class'])) $show_class.=' '.$field['class'];  

        if($field['type']=='group') {
        ?>
            <div class="<?php echo $show_class ?>">
                    <?php write_meta($field['setting'], $meta,  $tagname, $tagid, $post_id, $type, $array_id); ?>
            </div>
        <?php
        
        // TOGGLE GROUP
        
        } else if($field['type']=='toggle_group') {  
     
            if(isset($field['checkbox'])) {
                $show_class.=' mw_toggle_group_checkbox';    
                $open=(isset($meta[$field['id']]))? true: false;                  
                if(isset($field['invert'])) {
                    $open=!$open; 
                }                
            } else $open=false;
                                  
            if(isset($field['open']) || $open) $show_class.=' mw_toggle_group_open'; 
    
            ?>
            <div class="mw_toggle_group <?php echo $show_class ?>">                
                <a class="mw_toggle_group_head" href="#">
                    <?php 
                    if(isset($field['checkbox'])) {
                        echo '<span class="mw_toggle_group_toopen mw_toggle_group_false"></span><span class="mw_toggle_group_toclose mw_toggle_group_true"></span>';
                        echo '<input type="checkbox" name="'.$tagname.'['.$field['id'].']" value="1" '.((isset($meta[$field['id']]))?'checked="checked"':'').'/>';
                    }
                    else {
                        echo '<span class="mw_toggle_group_toopen mw_toggle_group_plus"></span><span class="mw_toggle_group_toclose mw_toggle_group_minus"></span>';
                    }
                    ?>
                    <?php echo $field['title']; ?>
                </a>
                <div class="mw_toggle_group_content">
                    <?php write_meta($field['setting'], $meta,  $tagname, $tagid, $post_id, $type, $array_id); ?>
                </div>
                <?php echo $html_after; $html_after='';?>
            </div>
        <?php
        
        // TABS
        
        } else if($field['type']=='tabs') {
            
            if(count($field['tabs'])>1) {            
                if(isset($field['style'])) $show_class.='cms_tabs_style_'.$field['style'];
            
                echo '<div class="mw_setting_tabs '.$show_class.'">';
                echo '<ul class="cms_tabs cms_small_tabs cms_small_tabs_setting ">';  
                $i=1;
                $active='';
                foreach($field['tabs'] as $id=>$set_tab) {                
                    if(isset($meta[$field['id']])) {
                        if($meta[$field['id']]==$id) $active=$id;
                    } else if($i==1) {
                        $active=$id;
                    }
                    
                    echo '<li class="cms_tab element_set_'.$field['id'].$array_id.'_tab">
                        <a href="#select_element_setting_'.$id.$array_id.'" data-group="element_set_'.$field['id'].$array_id.'" '.(($active==$id)? 'class="active"': '').'>'.$set_tab['name'].'</a>
                        <input id="select_element_setting_'.$id.$array_id.'_radio" type="radio" name="'.$tagname.'['.$field['id'].']" value="'.$id.'" '.(($active==$id)? 'checked="checked"': '').'>
                    </li>';
                    $i++;
                }
                echo '</ul><div class="clear"></div>';
                
                foreach($field['tabs'] as $id=>$set_tab) {
            
                    echo '<div id="select_element_setting_'.$id.$array_id.'" class="cms_small_tabs_content cms_tab_container element_set_'.$field['id'].$array_id.'_container '.(($active==$id)? 'cms_tab_container_active': '').'">';
                    write_meta($set_tab['setting'], $meta,  $tagname, $tagid, $post_id, $type, $array_id);
                    echo '</div>';
    
                }
                echo $html_after; $html_after = '';
                echo '</div>';
            } else {
                foreach($field['tabs'] as $id=>$set_tab) {
            
                    write_meta($set_tab['setting'], $meta,  $tagname, $tagid, $post_id, $type, $array_id);

    
                }
            }

            
        } else if($field['type']=='more') {
        ?>
            <div class="<?php echo $show_class ?>">
                <div class="set_form_row">
                    <a class="cms_more_setting" data-group="<?php echo $field['id']; ?>" href="#"><?php echo $field['title']; ?></a>
                </div>
                <div class="cms_more_group_<?php echo $field['id']; ?>" style="display:none;">
                    <?php write_meta($field['setting'], $meta,  $tagname, $tagid, $post_id, $type); ?>
                </div>
                <?php echo $html_after; $html_after = ''; ?>
            </div>
        <?php

            // MULTIELEMENT

        } else if($field['type']=='multielement') {
            $content=null;
            if($type=='setting') $content=(isset($meta[$field['id']]))?$meta[$field['id']]:array();
            else if(isset($meta['style'][$field['id']])) $content=$meta['style'][$field['id']];
            // Load field value with custom storage of value
            cms_load_customized_field_value($post_id, $field, $content);
            ?>
            <div id="<?php echo $tagid.'_'.$field['id']; ?>" class="<?php echo $show_class ?>">
                <div class="ve_multielement_container set_form_row ve_sortable_items">
                    <?php
                    if($title) {
                        echo '<div class="label">'.$title;
                        if(isset($field['tooltip'])) echo ' <span class="cms_toggle_tooltip">[?]<div class="cms_tooltip">'.$field['tooltip'].'</div></span>';
                        echo '</div>';
                    }

                    $i=0;
                    if(isset($content) && is_array($content)) {
                        foreach($content as $key=>$fd) {
                            ?>
                            <div class="ve_multielement-<?php echo $i; ?> ve_item_container ve_setting_container ve_sortable_item">
                                <?php
                                $id=$tagid.'_'.$field['id'].'_'.$i;
                                $f_name=$tagname.'['.$field['id'].']['.$i.']';
                                generate_multielement($f_name,$id,$field['setting'], array('style'=>$fd)); ?>
                            </div>
                            <?php
                            $i++;
                        }
                    }
                    ?>
                </div>
                <button class="ve_add_multielement cms_button_secondary"
                        data-id="<?php echo $i; ?>" data-name="<?php echo $tagname.'['.$field['id'].']'; ?>"  data-tagid="<?php echo $tagid.'_'.$field['id']; ?>"
                        data-set="<?php echo base64_encode(serialize($field['setting'])); ?>"
                ><?php echo $field['texts']['add']; ?></button>
                <?php echo $html_after; $html_after = ''; ?>

            </div>
            <?php
        } else if($field['type']=='title') {     
            echo '<div class="set_form_row '.$show_class.'"><h4>'.$field['name'].'</h4></div>';        
        } else {     
            echo '<div class="set_form_row '.$show_class.'">';        
                        
            if($title) {
                echo '<div class="label">'.$title;
                if(isset($field['tooltip'])) echo ' <span class="cms_toggle_tooltip">[?]<div class="cms_tooltip">'.$field['tooltip'].'</div></span>';                    
                echo '</div>'; 
            }
            if($type=='setting') {
                $content=(isset($meta[$field['id']]))? $meta[$field['id']]:null;
                $tagn=$tagname;
            }
            else {
                if(isset($field['id']) && $field['id']=='content') {
                    $content=(isset($meta['content']))? $meta['content']: null;
                    $tagn='ve';
                }
                else {
                    $content=(isset($meta['style'][$field['id']]))? $meta['style'][$field['id']]: null;
                    $tagn=$tagname;
                }
                if(isset($field['type']) && $field['type']=='editor') $editor=1;
            } 
            if($field['type']=='row_set') {
                foreach($field['setting'] as $subfield) {
                    echo '<div class="float-setting row-set-float-setting">';
                    if($type=='setting') $content=(isset($meta[$subfield['id']]))? $meta[$subfield['id']]:null;
                    else $content=(isset($meta['style'][$subfield['id']]))? $meta['style'][$subfield['id']]: null;
                    echo '<div class="sublabel">'.$subfield['title'].'</div>';
                    call_user_func_array("field_type_".$subfield['type'], array($subfield, $content, $tagn, $tagid, $post_id, $array_id));
                    echo '</div>'; 
                }
                echo '<div class="cms_clear"></div>';
            }  
            else {
                
                // Load field value with custom storage of value
                cms_load_customized_field_value($post_id, $field, $content);
                call_user_func_array("field_type_".$field['type'], array($field, $content, $tagn, $tagid, $post_id, $array_id));
            }    

            if(isset($field['desc'])) echo '<span class="cms_description">'.$field['desc'].'</span>';
            echo $html_after; $html_after = '';

            echo '</div>';  
        }

        echo $html_after;
    }
  
    if($type!='setting') echo '<input type="hidden" id="setting_editor_enabled" name="edtor_enabled" value="'.$editor.'" />';
}

/** Update preloaded meta of a field by a custom loader or use the value from a customized storage location.
 * @param int $post_id ID of post where the value relies to.
 * @param array $field Field definition
 * @param mixed $meta  Value that is preloaded. If custom loading should be performed, this value will be updated inplace.
 */
function cms_load_customized_field_value($post_id, $field, &$meta) {
    if(isset($field['save']) && isset($field['id']) && !empty($field['id'])) {
        $fieldId = $field['id'];
        if($field['save']=='post') {
            $post=get_post($post_id);
            $meta = $post->$fieldId;
        } elseif($field['save']=='option') {
            $meta = get_option($fieldId, true);
        } elseif($field['save']=='post_meta') {
            $meta = get_post_meta($post_id, $fieldId, true);
        }
    }
    // Custom value loader
    if(isset($field['loadhook']) && is_callable($field['loadhook'])) {
        $fnc = $field['loadhook'];
        $fnc($post_id, $field, $meta);
    }
}

function generate_multielement($name, $id, $setting, $content) {
    $title='';        
    if(isset($content['style'])) {    
        $title_id=0;    
          
        if(is_array($content['style'])) {
            $title=array_values($content['style']);
            $title=$title[0];
        }  
        
        if(isset($content['style']['icon'])) {
          $title='<span class="ve_item_head_icon">'.stripslashes($content['style']['icon']['code']).'</span>';
        }
        
        if(isset($content['style']['title'])) $title=stripslashes($content['style']['title']);
        if(!$title) $title=(isset($content['style']['name']))? stripslashes($content['style']['name']):"";
        if(!$title) $title=(isset($content['style']['text']))? stripslashes($content['style']['text']):"";

        if(is_array($title)) {            
            if(isset($title['imageid'])) {
                $title_id=$title['imageid'];
            } else {
                $title='';
            }
        }
        if(isset($content['style']['product_id']) && $content['style']['product_id']) {
            $title_id=$content['style']['product_id'];
        } else if(isset($content['style']['slider_content']) && $content['style']['slider_content']) {
            $title_id=$content['style']['slider_content'];
        }
        if($title_id) {
            $post=get_post( $title_id );
            if($post)
                $title=$post->post_title;
        }
    }
    ?>
    <div class="ve_item_head">
        <span class="ve_sortable_handler"></span>
        <?php echo $title; ?>
        <a class="ve_delete_setting" href="#" title="<?php echo __('Smazat','cms_ve'); ?>"></a>
    </div>
    <div class="ve_item_body <?php if($title=='') echo 've_item_body_v'; ?>">
        <?php
           write_meta($setting, $content, $name, $id, '', 've');
        ?>
    </div>
    <?php
}
function generate_multielement_ajax () {
    generate_multielement($_POST['tagname'].'['.$_POST['id'].']', $_POST['tagid'].'_'.$_POST['id'], unserialize(base64_decode($_POST['setting'])), array());
    die();
}
add_action('wp_ajax_cms_generate_multielement', 'generate_multielement_ajax');


function meta_cms_template_selector($post,$metabox) {
    $custom_template = get_post_meta( $post->ID, 'custom_skin_template', true );
    $page_templates=$metabox['args']['templates'];
    ?>

    	<select name="custom_skin_template" id="custom_skin_template">
    		<option value="default"
    			<?php
    				if ( ! $custom_template ) {
    					echo "selected='selected'";
    				}
    			?>><?php echo __( 'Defaultní šablona' ); ?></option>
    		<?php foreach( $page_templates as $name => $filename ) { ?>
    			<option 
    				value='<?php echo $filename; ?>'
    				<?php
    					if ( $custom_template == $filename ) {
    						echo 'selected="selected"';
    					}
    				?>><?php echo $name; ?></option>
    		<?php } ?>
    	</select>
    
      <?php   
}

add_action('save_post', 'cms_save_data');

// Saving Custom fields
// ******************************************************************************************
    
function cms_save_data($post_id) {
    global $cms;
    if($cms->is_save_disabled()) {
        mwlog('cms', "saving sets SKIPPED for [$post_id], saving is disabled", MWLL_DEBUG, 'save');
        return;
    }

    $cms->is_saving = true;
    try {// verify nonce
        if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], basename(__FILE__))) {
            return $post_id;
        }
        // check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        // check permissions
        if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        // save custom fields
        if (isset($_POST['admin_save_nonce']) && wp_verify_nonce($_POST['admin_save_nonce'], 'admin_save_nonce')) {
            $cms->save_sets($post_id);
        }

        $cms->is_saving = false;
    } catch (Exception $e) {
        $cms->is_saving = false;
        throw $e;
    }
}

// Admin pages
// ******************************************************************************************



function register_page_setting() {
  global $cms;
  foreach($cms->page_set_groups as $keygroups=>$setgroups) {
      foreach($setgroups as $value) {
        register_setting( $keygroups, $value['id'] );   
      }
  }
}


function create_page() {
  global $cms;
  $slug=$_GET['page'];
  $subpages=array();
  if(count($cms->subpages)) {    
        foreach($cms->subpages as $page) {                 
            if($page['parent_slug']==$cms->subpages[$slug]['parent_slug']) 
                $subpages[]=$page; 
            if($slug==$page['menu_slug']) 
                $currentp=$page['parent_slug'];
        }
  }
?>
<div class="wrap"> 

  <h1>
      <?php 
      if(count($subpages)) echo $cms->pages[$currentp]['menu_title'];
      else $cms->pages[$slug]['menu_title']; 
      ?>
  </h1>
  <br>
  <?php 
  
  
  if($cms->valid_license()) {
      if(isset($_GET['settings-updated']) && $_GET['settings-updated']==true) {
          echo '<div class="cms_confirm_box">'.__('Nastavení bylo uloženo', 'cms').'</div>';
      }  

      /*
      <div class="subpage_nav">
      <?php
      /*    
      if(count($subpages)) { 
              <ul>
                
                 
                    foreach($subpages as $page) { 
                         <li><a <?php if($slug==$page['menu_slug']) echo 'class="active"';  href="admin.php?page=<?php echo $page['menu_slug']; "><?php echo $page['menu_title'];   </a></li> 
                    }
                
              </ul>
              <div class="cms_clear"></div>
         } 
       
        
        
        </div>
         */
        settings_fields( $slug ); 
        
        if($slug=='member_option' || $slug=='appearancemember_option') {
            $meta=get_option('member_basic');
            if(isset($meta['members'])) {
            
                $content=$meta['members'];
                
                ?>
                <div id="member_select_member_container" class="multisetting_select_container">
                    <select id="member_select" class="<?php if(!is_array($content)) echo 'cms_nodisp'; ?> member_input_member_name">
                         <?php 
                         $first_id=0;
                         $i=0;
                         foreach($content as $id=>$member) { 
                            echo '<option value="'.$id.'">'.$member['name'].'</option>';
                            $newid=$id+1;
                            if($i==0) $first_id=$id;
                            $i++;
                         }
                         
                         ?>
                    </select>
                    <div id="member_section_api_key">
                        <?php 
                        $api=get_option('member_api'); 
                        if(!isset($api['token']) || !$api['token']) {
                            $api['token']=wp_generate_password(24,false);
                            update_option('member_api',$api);
                        }
                        echo __('API key','cms_member').': <strong>'.$api['token'].'</strong>';
                        ?>
                    </div>
                </div>
                <div id="member_add_new_container" class="cms_nodisp">
                    <input class="cms_text_input member_input_member_name" type="text" id="member_add_new_member_name" placeholder="<?php echo __('Zadejte název nové členské sekce','cms_member'); ?>" />
                    <button id="member_save_member" class="cms_button_secondary" data-id="<?php echo $newid; ?>" data-name=""  data-tagid=""><?php echo __('Vytvořit členskou sekci','cms_member'); ?></button>
                    <button id="member_storno_new_member" class="cms_button_secondary"><?php echo __('Storno','cms_member'); ?></button>
                </div>
                <?php
            }
            else echo '<div class="cms_error_box">'.__('Nejsou vytvořeny žádné členské sekce. Členské sekce lze vytvářet na webu v nastavení členských sekcí','cms_member').'</div>';
        }
        ?>
        <div id="cms_option_container">
        <form method="post" action="options.php">
            <?php 
            settings_fields( $slug );
            
            if($slug=='member_option' || $slug=='appearancemember_option') {
                if(isset($meta['members'])) {
                    $m=0;
                    foreach($content as $id=>$member) { 
                        write_setting_array_blok($slug, $id, $m);
                        $m++;
                    }
                    ?>
                    <div class="cms_save_button_area"><input id="submit" class="cms_button" type="submit" value="<?php echo __('ULOŽIT ZMĚNY', 'cms'); ?>" name="submit"></div>
                    <?php
                }
            }
            else write_setting_blok($slug); ?>
       </form> 
       </div> 
    <?php } else get_license_form(); ?>
    

</div>
<?php } 

function write_setting_array_blok($slug, $id, $m) {
  global $cms;

  if(count($cms->page_set_groups[$slug])) { 
       echo '<div id="member_section_'.$id.'" class="member_section '.(($m==0)? 'mw_array_setting_section_v':'').'">';        
                  ?>
                  <div class="cms_option_submenu">
                      <ul class="cms_tabs cms_option_group_<?php echo $id; ?>_tab">
                          <?php
                          $i=1;
                          foreach($cms->page_set_groups[$slug] as $value) {
                              ?>
                              <li><a class="<?php echo ($i==1)? "active": ""; ?>" data-group="cms_option_group_<?php echo $id; ?>" href="#<?php echo $value['id'].'_'.$id; ?>"><?php echo $value['name']; ?><span></span></a></li>
                              <?php
                              $i++;
                          } 
                          ?>  
                      </ul>
                      <div class="cms_clear"></div>
                  </div>
                  <div class="cms_option_content">
                      <?php
                      $i=1;
                      foreach($cms->page_set_groups[$slug] as $value) { ?>
                          <div class="cms_option_group_<?php echo $id; ?>_container cms_setting_block_content cms_tab_content cms_tab_content-<?php echo $i; ?> cms_option_group_<?php echo $id; ?>_container_<?php echo $i; ?>" id="<?php echo $value['id'].'_'.$id; ?>">
                             <?php if(isset($value['info'])) echo '<div class="cms_setting_block_info"> <div class="cms_info_box_gray">'.$value['info'].'</div></div>'; ?> 
                             <?php 
                             $meta=get_option($value['id']);    
                                                                   
                             write_meta($cms->page_set[$value['id']],$meta['members'][$id],$value['id'].'[members]['.$id.']',$value['id'].'_members_'.$id,'','setting',$id); 
                             ?> 
                          </div>
                      <?php $i++; } ?>
                      
                  </div>
                  <div class="cms_clear"></div>
                  <?php 
        echo '</div>';                 
    }

}


function write_setting_blok($slug) {
  global $cms;
        ?>
        
        

            <?php 
              if(count($cms->page_set_groups[$slug])) {         
                  ?>
                  <div id="cms_option_submenu">
                  <ul class="cms_tabs cms_option_group_tab" >
                      <?php
                      $i=1;
                      foreach($cms->page_set_groups[$slug] as $value) {
                          ?>
                          <li><a class="<?php echo ($i==1)? "active": ""; ?>" data-group="cms_option_group" href="#<?php echo $value['id']; ?>"><?php echo $value['name']; ?><span></span></a></li>
                          <?php
                          $i++;
                      } 
                      ?>  
                  </ul>
                  <div class="cms_clear"></div>
                  </div>
                  <div id="cms_option_content">
                      <?php
                      $i=1;
                      foreach($cms->page_set_groups[$slug] as $value) { ?>
                          <div class="cms_option_group_container cms_setting_block_content cms_option_group_container_<?php echo $i; ?>" id="<?php echo $value['id']; ?>">
                             <?php if(isset($value['info'])) echo '<div class="cms_setting_block_info"> <div class="cms_info_box_gray">'.$value['info'].'</div></div>'; ?> 
                             <?php 
                             $meta=get_option($value['id']); 
                             write_meta($cms->page_set[$value['id']],$meta,$value['id'],$value['id']); 
                             ?> 
                          </div>
                      <?php $i++; } ?>
                      <div class="cms_save_button_area"><input id="submit" class="cms_button" type="submit" value="<?php echo __('ULOŽIT ZMĚNY', 'cms'); ?>" name="submit"></div>
                  </div>
                  <div class="cms_clear"></div>
                  <?php 
                  
              }
              else { ?>
                  <div class="cms_single_option_group_container">
                      <?php 
                      $meta=get_option($slug); 
                      write_meta($cms->page_set[$slug],$meta,$slug,$slug); 
                      ?>
                  </div>
                  <div class="cms_save_button_area">
                  <input id="submit" class="cms_button" type="submit" value="<?php echo __('ULOŽIT ZMĚNY', 'cms'); ?>" name="submit">
                  </div>
              <?php } ?>
               
        
        
        <?php
}

function get_license_form() {
    global $cms;
    ?>
    <form method="post" action="options.php">
        <?php settings_fields( 'web_option' ); ?>
        <div class="cms_setting_block_content" style="background: #fff;">
            <div class="cms_error_box"><?php echo __('Nastavení není dostupné, protože není zadáno vaše licenční číslo. Prosím, zadejte jej k ověření. Po zadání platného licenčního čísla se nastavení zpřístupní. Tuto akci provedete jen jednou.', 'cms'); ?></div>
            <?php 
            $meta=get_option('web_option_license'); 
            write_meta($cms->page_set['web_option_license'],$meta,'web_option_license','web_option_license'); 
            ?>
            <div class="cms_save_button_area">
                <input id="submit" class="cms_button" type="submit" value="<?php echo __('Uložit licenční číslo', 'cms'); ?>" name="submit">
            </div>
        </div>
    </form>
    <?php
}


// COMMENTS
// **********************************************************************

function approve_comments () {
    $comment_status = 'approve';
    $comment_id = intval($_POST['comment_approve_id']);
    wp_set_comment_status( $comment_id, $comment_status );
    echo true;
    die();
}
if( is_admin() ) add_action('wp_ajax_approve_comments', 'approve_comments'); 


function cms_facebook_comments($url, $perpage='10', $scheme="light", $width="550" ) { 
    return '<div class="fb-comments" data-href="'.$url.'" data-numposts="'.$perpage.'" data-colorscheme="'.$scheme.'" data-width="'.$width.'"></div>';
}


function cms_add_upload_mimes($mimes) {
    $mimes = array_merge($mimes, array(
        'epub|mobi' => 'application/octet-stream'
    ));
    return $mimes;
}
add_filter('upload_mimes', 'cms_add_upload_mimes');


// License
// **********************************************************************

function cms_get_modules() {
    global $cms;
    $modules=array('cms');
    foreach($cms->modules as $module) {
      if($module['module']!='visualeditor' && $module['license']) $modules[]=$module['module'];
    }
    return $modules;
}

function cms_check_licence($licence) {

    $url = LICENSE_SERVER . 'license/activate';
    
    $url .= '/?url='.get_home_url();
    $url .= '&serial_number='.$licence;

/*
    $body = array(
      'serial_number' => $licence,
      'url' => get_home_url(),
      'statistics' => mw_get_statistics()
    );
*/
    
    $response = wp_remote_post( $url, array(
        	'method' => 'GET',
        	'timeout' => 45,
        	'redirection' => 5,
        	'httpversion' => '1.1',
        	'blocking' => true,
        	'headers' => array(),
    ));   
          
    $newstatus=array();
    $newstatus['license']=$licence;
    
    $return = json_decode(wp_remote_retrieve_body($response));   
    //print_r($return);

    if ( is_wp_error( $response ) || !isset($return->status) || (isset($return->error) && $return->error->status=='error' && $return->error->message !== "Parameter serial_number can not be empty.")) {

        $modules=get_option('cms_license_modules');
        $try=get_option('cms_license_try');
        if(!empty($modules) && (!$try || $try>time())) {
            $newstatus['code']='success';
            if(!$try) update_option('cms_license_try',time() + (7 * 24 * 60 * 60));
        }
        else {
            $newstatus['code']='error';
        }
        
    } else {
        
        $newstatus['code']=($return->status=='activation-already-match')? 'success' : $return->status;
        if (isset($return->modules)) {
            $modules = (array)$return->modules;
        } else {
            $modules = null;
        }
        
        $mw_licence_info=(array)$return;
        
        update_option('mw_licence_info',$mw_licence_info);
        update_option('cms_license_modules',$modules);
        delete_option('cms_license_try');
    }
    set_transient( 'cms_license', $newstatus, 24 * HOUR_IN_SECONDS );
    return $newstatus['code']; 
}

function init_check_license($licence) {   
    $status=get_transient('cms_license');
    $modules=get_option('cms_license_modules');
    
    if(!($modules)) {
        cms_check_licence($licence);
        $status=get_transient('cms_license');
    }
    if(!isset($status['license']) || $licence!=$status['license'] || $status['code']!='success') {
        $status='';
    }  

    if(!$status) {   
        cms_check_licence($licence);
    } 
}

function cms_check_license_code($licence) {
 
    $status=get_transient('cms_license');
    $returnCode=$status['code'];

    if($licence) {
    switch ( $returnCode ) {
    	case 'success':
    		echo '<div class="cms_confirm_box">'.__('Licenční číslo je platné.', 'cms').'</div>';
    		break;
    
    	case 'not-found':
    		echo '<div class="cms_error_box">'.__('Neplatné licenční číslo.', 'cms').'</div>';
    		break;
    
    	case '-1':
    		echo '<div class="cms_error_box">'.__('Platnost této zkušební licence již vypršela. Pokud se vám šablona líbí a chcete v tvorbě webu pokračovat, <strong><a target=\"_blank\" href=\"http://www.mioweb.cz/objednavka/\">kupte si plnou verzi MioWeb šablony zde.</a></strong>.', 'cms').'</div>';
    		break;
        
      case 'already-activated':
      	echo '<div class="cms_error_box">'.__('Zadané licenční číslo je již rezervováno pro jinou doménu. Zkuste zadat jiné nebo toto licenční číslo uvolněte.', 'cms').'</div>';
      	break;
        
      case 'domain-name-already-taken':
        echo '<div class="cms_error_box">'.__('Pro tuto doménu je již aktivovaná jiná licence. Prosím uvolněte ji.', 'cms').'</div>';
        break;
    
    	case 'hosting-domain-not-match':
    		echo '<div class="cms_error_box">'.__('Neplatný klíč pro tento hosting.', 'cms').'</div>';
    		break;
        
      case 'hosting-not-found':
      	echo '<div class="cms_error_box">'.__('Hosting pro toto licenční číslo byl již smazán. Licenční číslo je neplatné.', 'cms').'</div>';
      	break;
      
      case 'hosting-have-extra-license':
        echo '<div class="cms_error_box">'.__('Pro tento hosting je už nastavena jiná doživotní licence.', 'cms').'</div>';
        break;
    
      case 'error':
    		echo '<div class="cms_error_box">'.__('Nedaří se spojit s licenčním serverem. Pravděpodobně došlo k jeho dočasnému výpadku. Zkuste se, prosím, přihlásit později. Webové stránky fungují pro vaše návštěvníky samozřejmě dále.', 'cms').'</div>';
    		break;
    
    	default:
    		echo '<div class="cms_error_box">'.__('Nastala neznámá chyba:', 'cms').' ' . $return->status.'</div>';
    		break;
    }
    }
}

    function mw_get_statistics() {
        
        $statistics=array();
        
        $cur_theme = wp_get_theme();
        $users_count=count_users();
        
        $statistics['mioweb_version']=$cur_theme->version;
        $statistics['wp_version']=get_bloginfo('version');
        $statistics['language']=get_bloginfo('language');
        
        // blog
        // **************************************************************
        
        $statistics['blog_posts_num']=wp_count_posts( 'post' )->publish; 
        
        // web
        // **************************************************************
        
        $statistics['page_num']=wp_count_posts( 'page' )->publish; 
        
        $installed_web=get_option('ve_installed_web');
        if($installed_web)
            $statistics['installed_web']=$installed_web['web_theme'];
            
        
        $em_connection=get_option('ve_connect_se');
        //print_r($em_connection);
        if($em_connection && isset($em_connection['connection'])) {
            $statistics['se_connect']=$em_connection['connection']['status'];
        } else $statistics['se_connect']=0;
          
        $f_connection=get_option('ve_connect_fapi');
        if($f_connection && isset($f_connection['connection'])) {
            $statistics['fapi_connect']=$f_connection['connection']['status'];
        } else $statistics['fapi_connect']=0;
        
        // shop
        // **************************************************************
        
        $created=get_option('mw_eshop_created');
        if($created) {
                $statistics['shop_created']=1; 
                $statistics['products_num']=count(get_posts( array('post_type'=>'mwproduct','posts_per_page'=>-1 ))); //wp_count_posts( 'mwproduct' )->publish;
                $statistics['variants_num']=count(get_posts( array('post_type'=>'mwvariant','posts_per_page'=>-1 )));
                
                $orders=get_posts( array('post_type'=>'mworder','posts_per_page'=>-1 ));
                $statistics['orders_num']=count($orders);
                $statistics['ordered_sum']=0;
                foreach($orders as $order) {
                    $price=0;
                    $om=get_post_meta($order->ID,'mwshop_order',true);
                    foreach($om['gateOrderData']['dataOrder']['items'] as $item) {
                        
                        $price+=$item['price_czk'];
                    }
                    $statistics['ordered_sum']+=$price;                     
                }
        } else $statistics['shop_created']=0;  
        
        // member
        // **************************************************************
        
        $members=get_option('member_basic');
        if($members && isset($members['members'])){
            $statistics['members_num']=count($members['members']);
            if(isset($users_count['avail_roles']) && isset($users_count['avail_roles']['member'])) {
                $statistics['members_users']=$users_count['avail_roles']['member'];
            }
            else $statistics['members_users']=0;
        } else {
            $statistics['members_num']=0;
            $statistics['members_users']=0;
        } 
        
        // campaigns
        // **************************************************************
        
        $campaigns=get_option('campaign_basic');
        $statistics['campaigns_num']=($campaigns) ? count($campaigns['campaigns']) : 0;          
        
        // plugins 
        // **************************************************************
        
        $installed_plugins = get_option('active_plugins');
        $statistics['plugins_num']=count($installed_plugins);
        if ( ! function_exists( 'get_plugins' ) ) {
        	require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( function_exists( 'get_plugins' ) ) { 
            $all_plugins = get_plugins();
            //print_r($all_plugins);
            $install_names=array();
            foreach($installed_plugins as $plug) {
              //print_r($plug);
              if(isset($all_plugins[$plug])) $install_names[]=$all_plugins[$plug]['Name'];
            }
            $statistics['installed_plugins']=implode(',',$install_names);
        } 
        return $statistics;  
    }
   
