<?php
function ve_element_member_login($element, $css_id, $post_id) {
    global $member_module, $vePage;   
    $content='';
    $access=false;
    
        if(isset($element['config']['max_width']))
            $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .member_login_form");
        else $style='';
        
        $formstyle=$vePage->print_styles(array(
            'font'=>$element['style']['form-font'],
            'background-color'=>$element['style']['background'],            
        ),$css_id." .member_login_form_row .ve_form_text");
        
 
        $content.=$vePage->create_button_styles($element['style']['button'], $css_id." .ve_content_button");
        $but_class=(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect'])? ' ve_cb_hover_'.$element['style']['button']['hover_effect']:'';
        
        $redirect_url="";
                   
        $members=get_option('member_basic');
        $page_member=get_post_meta($post_id, 'page_member', true);
        $page_section=(isset($page_member['member_page']))? $page_member['member_section']['section']:'';
        $logto=(isset($element['style']['loginto']) && $element['style']['loginto']!='')? $element['style']['loginto']:$page_section;
        if(isset($members['members']) && $logto!=='' && isset($members['members'][$logto]['dashboard'])){
              $dashboard=$members['members'][$logto]['dashboard'];
              $login=$members['members'][$logto]['login']; 
              global $post;    
              $redirect_url=(isset($post->ID) && $post->ID!=$login && $page_section==$logto)? get_permalink($post->ID) : get_permalink($dashboard);
                
              $user = wp_get_current_user(); 
              if($user->ID) { 
                  $user_meta=get_the_author_meta( 'cms_member', $user->ID );
                  $access=(isset($user_meta[$logto]))? true: false;
              }
        }            
        $content.='<form class="member_login_form ve_content_form ve_form_input_style_'.$element['style']['input-style'].'" action="'.wp_login_url().'" method="post" '.$style.'>';

        if($logto=='' && $member_module->edit_mode) $content.='<div class="cms_error_box admin_feature">'.__('Přihlašovací formulář není propojen s žádnou členskou sekcí. Vyberte v nastavení formuláře členskou sekci, do které se má po vyplnění formuláře uživatel přihlásit, nebo zařaďte tuto stránku do členské sekce v Nastavení stránky->Členská stránka.','cms_member').'</div>'; 
        
        else if(empty($members['members']) || empty($members['members'][$logto]) && $member_module->edit_mode) {
            $content.='<div class="cms_error_box admin_feature">'.__('Vybraná členská sekce již neexistuje. Pravděpodobně byla smazána. Zvolte v nastavení elementu jinou.','cms_member').'</div>';
        }
        else if(empty($dashboard) && $member_module->edit_mode) {
            $content.='<div class="cms_error_box admin_feature">'.__('Vybraná členská sekce nemá nastavenou žádnou stránku jako nástěnku. Přihlašovací formulář proto nebude fungovat správně. Nastavte členské sekci nástěnku ve <strong>správě členských sekcí</strong> v horní administrační liště.','cms_member').'</div>';
        }
        // if user is logged in
        if($access && !$member_module->edit_mode) {
            $already=true;
            //if limited member
            if(isset($member_module->user_sections[$logto]['end']) && $member_module->user_sections[$logto]['end']!='') {
                  $end=$member_module->user_sections[$member_module->setting['member_section']['section']]['end'];
                  if(strtotime($end.' '.$member_module->user_sections[$member_module->setting['member_section']['section']]['time'])<current_time( 'timestamp' )) {
                      $already=false; 
                      $content='<div class="member_login_form_logged_box">
                          <p style="color: red;">'.__("Vaše členství v této členské sekci bylo časově omezeno a již vypršelo, proto se nelze přihlásit.","cms_member").'</p>
                      </div>'; 
                  }                 
            }
            
            //else
            if($already) {
                $content='<div class="member_login_form_logged_box">
                    <p>'.__("Už jste přihlášen(a).","cms_member").'</p>
                    <div class="member_login_form_button_row">
                        <a href="'.$redirect_url.'" class="ve_content_button ve_content_button_'.$element['style']['button']['style'].'">'.__("Vstoupit do členské sekce","cms_member").'</a>
                    </div>
                </div>';
            }
        } // if user is not logged in
        else {
            $content.='<div class="member_login_form_row">
                <input class="ve_form_text" '.$formstyle.' type="text" name="log" id="log" value="" placeholder="'. __("Login/E-mail","cms_member").'" />
            </div>
            <div class="member_login_form_row">
                <input class="ve_form_text" '.$formstyle.' type="password" name="pwd" id="pwd" placeholder="'.__("Heslo","cms_member").'" />
            </div>
            <div class="member_login_form_button_row">
                <button type="submit" class="ve_content_button ve_content_button_'.$element['style']['button']['style'].' '.$but_class.'" name="submit" value="1" >'.__("Přihlásit se","cms_member").'</button>
                <input type="hidden" name="redirect_to" value="'.$redirect_url.'" />
                <input type="hidden" name="cms_abort_redirect" value="1" />
            </div>
            <div class="member_login_form_forgot"><a href="'.wp_lostpassword_url().'">'.__("Zapomněli jste heslo?","cms_member").'</a></div>
            </form>';
            /*
            <div class="member_login_form_row">
                <label><input name="rememberme" id="rememberme" value="forever" type="checkbox"> Pamatovat si mě</label>
            </div>
            
            $args = array(
                'redirect' => admin_url(), 
                'form_id' => 'loginform-custom',
                'label_username' => __( 'Username custom text' ),
                'label_password' => __( 'Password custom text' ),
                'label_remember' => __( 'Remember Me custom text' ),
                'label_log_in' => __( 'Log In custom text' ),
                'remember' => true,
                'echo' => false
            );
            $content.=wp_login_form( $args );
            */
        }
    return $content;
}

function ve_element_member_regform($element, $css_id, $post_id) {
        global $member_module, $vePage, $apiConnection;   
        $content='';
        
        if(isset($element['style']['redirect'])) $redirect=$vePage->create_link($element['style']['redirect'],false);
        if(!$redirect) $redirect=get_permalink($post_id);
    
        if(isset($element['config']['max_width']))
            $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .member_login_form");
        else $style='';
        
        $formstyle=$vePage->print_styles(array(
            'font'=>$element['style']['form-font'],
            'background-color'=>$element['style']['background'],            
        ),$css_id." .member_login_form_row .ve_form_text");
        
        $content.=$vePage->create_button_styles($element['style']['button'], $css_id." .member_login_form_button_row button");
        $but_class=(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect'])? ' ve_cb_hover_'.$element['style']['button']['hover_effect']:'';
        
        $members=get_option('member_basic');  
        
        $info=array();
        if(isset($members['members'][$element['style']['reginto']['section']])) {
            $info['name']=$members['members'][$element['style']['reginto']['section']]['name'];   
            $info['id']=$element['style']['reginto']['section']; 
            if(isset($element['style']['reginto'][$element['style']['reginto']['section']]['levels'])) $info['level']=$element['style']['reginto'][$element['style']['reginto']['section']]['levels'];
        } 
        if(isset($element['style']['update'])) $info['update']=1;
        if($element['style']['sendtomail']) $info['email']=$element['style']['sendtomail'];
        if(isset($element['style']['days']) && $element['style']['days']) $info['days']=$element['style']['days'];
        if(isset($element['style']['generate_password'])) $info['generate_password']=1;
        
        // back compatibility (temporary)
        $element['style']['sendtose']=$apiConnection->repair_content_val($element['style']['sendtose']);
        // back compatibility end
        if(isset($element['style']['sendtose']['id']) && $element['style']['sendtose']['id']) $info['se']=$element['style']['sendtose']['id'];     

        $content.='<form action="" class="member_login_form ve_check_form ve_content_form ve_form_input_style_'.$element['style']['input-style'].'" method="post" '.$style.'>';
        
        if(!isset($element['style']['reginto']) && $member_module->edit_mode) $content.='<div class="cms_error_box admin_feature">'.__("Registrační formulář není propojen s žádnou členskou sekcí. Vyberte v nastavení formuláře členskou sekci, do které se má po vyplnění formuláře uživatel registrovat.","cms_member").'</div>';         
        else if((empty($members['members']) || empty($members['members'][$element['style']['reginto']['section']])) && $member_module->edit_mode) {
            $content.='<div class="cms_error_box admin_feature">'.__("Vybraná členská sekce již neexistuje. Pravděpodobně byla smazána. Zvolte v nastavení elementu jinou.","cms_member").'</div>';
        }
        
        if(isset($_GET['mem_registration_error'])) {
            $content.='<div class="member_error">';
            switch ($_GET['mem_registration_error']) {
            case 1:
                $content.=__("Musíte zadat svůj e-mail!","cms_member");
                break;
            case 2:
                $content.=__("Uživatel s touto e-mailovou adresou již existuje!","cms_member");
                break;
            case 3:
                $content.=__("Musíte zadat heslo a potvrzení hesla!","cms_member");
                break;            
            case 4:
                $content.=__("Heslo a potvrzení hesla se neshoduje!","cms_member");
                break;
            case 5:
                $content.=__("Neoprávněná registrace!","cms_member");
                break;
            }
            $content.='</div>';
        }


        $content.='<div class="member_login_form_row">
                <input class="ve_form_text ve_form_required ve_form_email" value="'.((isset($_GET['email']))? urldecode($_GET['email']):'').'" '.$formstyle.' type="text" name="user_email" placeholder="'. __("E-mail","cms_member").'*" />
        </div>';
        if(!isset($element['style']['hide']) || !isset($element['style']['hide']['name'])) {
        $content.='<div class="member_login_form_row">
                <input class="ve_form_text" '.$formstyle.' type="text" name="user_name" placeholder="'.__("Jméno","cms_member").'" />
        </div>
        <div class="member_login_form_row">
                <input class="ve_form_text" '.$formstyle.' type="text" name="user_last_name" placeholder="'.__("Příjmení","cms_member").'" />
        </div>';
        }
        if(!isset($element['style']['generate_password'])) {
        $content.='<div class="member_login_form_row">
                <input class="ve_form_text ve_form_required" '.$formstyle.' type="password" name="user_password" placeholder="'.__("Heslo","cms_member").'*" />
        </div>
        <div class="member_login_form_row">
                <input class="ve_form_text ve_form_required" '.$formstyle.' type="password" name="user_password2" placeholder="'.__("Potvrdit heslo","cms_member").'*" />
        </div>';
        }
        $gdpr=get_option('web_option_gdpr');
        if(isset($element['style']['gdpr_info']) && $element['style']['gdpr_info']) {
            $content.='<div class="mw_field_gdpr_accept">';
            $content.='<input type="hidden" name="gdpr_accept" value="'.$element['style']['gdpr_info'].'" />';
            $content.=$element['style']['gdpr_info'];
            if($element['style']['gdpr_link_text'] && isset($gdpr['gdpr_url']) && $gdpr['gdpr_url']) $content.=' <a href="'.$vePage->create_link($gdpr['gdpr_url']).'" target="_blank">'.$element['style']['gdpr_link_text'].'</a>';
            $content.='</div>';
        }
        $content.='<div class="member_login_form_button_row">
                <button type="submit" class="ve_content_button ve_content_button_'.$element['style']['button']['style'].' '.$but_class.'" name="submit" value="1" >'.__("Registrovat se","cms_member").'</button>
                <input type="hidden" name="member_free_registration" value="'.base64_encode(serialize($info)).'" />
                <input type="hidden" name="member_registration_redirect" value="'.$redirect.'" />
        </div>
        </form>';
        
    return $content;
}

function ve_element_member_download($element, $css_id) { 
    global $vePage; 
    if(isset($element['content']) && is_array($element['content'])) {
        $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .el_mem_download"):"";
              
        $content=$vePage->print_styles_array(array(        
            array(
                'styles'=>array('background-color'=>$element['style']['color']),
                'element'=>$css_id.' a.el_mem_download_icon,'.$css_id.' .el_mem_download_style_4 li',
            ),
            array(
                'styles'=>isset($element['style']['font'])? array('font'=>$element['style']['font']): '',
                'element'=>$css_id.' .el_mem_download_text a',
            ),
            array(
                'styles'=>isset($element['style']['font_text'])? array('font'=>$element['style']['font_text']): '',
                'element'=>$css_id.' .el_mem_download_text p',
            ),
        ));
       
        if($element['style']['style']!=4) $content.=$vePage->print_styles(array('color'=>$element['style']['color']),$css_id.' a','online');  
        $content.='<ul class="in_element_content el_mem_download el_mem_download_style_'.$element['style']['style'].'" '.$instyle.'>';
        
        foreach($element['content'] as $file) {
            $desc=($file['desc']=='')? false : true;
            if($file['file']){
            $content.='<li> 
                <a class="el_mem_download_icon el_mem_download_icon_'.$file['icon'].'" target="_blank" rel="nofollow" href="'.$file['file'].'" download></a>
                <div class="el_mem_download_text '.(($desc)? '':'el_mem_download_notext').'">            
                    <a rel="nofollow" href="'.$file['file'].'" download>'.$file['name'].'</a>
                    '.(($desc)? '<p>'.$file['desc'].'</p>':'').'
                </div>
                <div class="cms_clear"></div> 
            </li>'; 
            }
            else if($vePage->edit_mode) $content.='<div class="cms_error_box admin_feature">'.sprintf(__("U položky %s není zadaný soubor ke stažení.","cms_member"),'<strong>'.$file['name'].'</strong>').'</div>';  
        }
        $content.="</ul>";
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__("Nejsou zadány žádné soubory ke stažení.","cms_member").'</div>';
    else $content='';
    return $content;
}




function ve_element_member_subpages($element, $css_id, $cur_post_id) { 
    global $vePage, $post, $member_module;
    $post_id=$cur_post_id;
    if(isset($element['style']['page']) && $element['style']['page']) $post_id=$element['style']['page'];
    else $post_id=$cur_post_id; 
    
    if(isset($element['config']['max_width']))
        $instyle=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .el_mem_pages");
    else $instyle='';
    
    $args = array(
        'sort_order' => 'ASC',
        'sort_column' => 'menu_order',
        'child_of' => $post_id,
        'parent' => $post_id,
    );   
    $pages = get_pages($args);
    
    // pages on same level
    if(empty($pages) && $element['style']['page']==="") {
        $par=wp_get_post_parent_id( $post_id );
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order',
            'child_of' => $par,
            'parent' => $par,
        );   
        $pages = get_pages($args);
        
    } 
   
                                              
    if(!empty($pages)) {
        $i=1; 
        
        $style=array(
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>$css_id.' a.el_mem_page_title',
            ),
        );       
        if($element['style']['style']==4) {
            $style[]=array(
                'styles'=>array('background-color'=>$element['style']['color']),
                'element'=>$css_id.' .el_mem_pages_style_4 .el_mem_pages_col'
            );
        }
        if(isset($element['style']['image_size']['size']) && $element['style']['image_size']['size']) {
            $style[]=array(
                'styles'=>array('width'=>(97-$element['style']['image_size']['size'])."%"),
                'element'=>$css_id.' .el_mem_pages2 .el_mem_page_text'
            );
            $style[]=array(
                'styles'=>array('width'=>$element['style']['image_size']['size']."%"),
                'element'=>$css_id.' .el_mem_pages2 .el_mem_page_thumb',
            );
        }
        
        $content=$vePage->print_styles_array($style);
        
        $content.='<div class="el_mem_pages el_mem_pages'.(isset($element['style']['structure'])?$element['style']['structure']:'0').' el_mem_pages_cols'.$element['style']['cols'].' el_mem_pages_style_'.$element['style']['style'].' el_mem_pages_stycol_'.$element['style']['style'].'_'.$element['style']['cols'].'" '.$instyle.'>';
        foreach ( $pages as $page ) {
            if($i==1 && $element['style']['cols']>1) $content.='<div class="el_mem_pages_row">';
            $page_setting=get_post_meta($page->ID, 'page_member', true);
            
            $level_access=$member_module->get_level_access($page->ID);
            
            if($level_access || $member_module->edit_mode) {
            
                $evergreentime=(isset($page_setting['evergreen']) && $page_setting['evergreen']>0)? (strtotime($member_module->user->member_registered)+($page_setting['evergreen']*86400)):0;
                $evergreentime=(isset($page_setting['evergreen_datetime']) && $page_setting['evergreen_datetime']['date'])? strtotime($page_setting['evergreen_datetime']['date'].' '.$page_setting['evergreen_datetime']['hour'].':'.$page_setting['evergreen_datetime']['minute']):$evergreentime;
            
                if($evergreentime && $evergreentime>current_time( 'timestamp' )) $ever_access=false;
                else $ever_access=true;
                
                if(!(isset($member_module->member_section['evergreen_show']) && !$ever_access) || $member_module->edit_mode) {  
                    if(isset($element['style']['default_image']) && $element['style']['default_image']) $image='<img src="'.home_url().$element['style']['default_image'].'" alt="" />';
                    else $image=(has_post_thumbnail($page->ID))? get_the_post_thumbnail($page->ID, 'member_page'): '<img src="'.MEMBER_DIR.'images/page_thumb.jpg" alt="" />';
                    
                    if(isset($page_setting['thumbnail']) && $page_setting['thumbnail']) {
                        $page_set_image['image']=$page_setting['thumbnail'];
                        $image=$vePage->generate_image($page_set_image);
                    }
                    
                    if(!isset($element['style']['setting']['hide_comments'])) {
                        $com='<span class="el_mem_page_comments">';
                        if ( $page->comment_count > 4 || $page->comment_count==0 )
        	                   $com.=$page->comment_count.' '.__('Komentářů','cms_blog');
        	              elseif ( $page->comment_count > 1 )
        	                   $com.= $page->comment_count.' '.__('Komentáře','cms_blog');
        	              else 
                            $com.=$page->comment_count.' '.__('Komentář','cms_blog');
                        $com.='</span>';
                    }
                    else $com='';
    
                    $content.='<div class="el_mem_pages_col el_mem_pages_col'.$i.' '.((!$ever_access && !$member_module->edit_mode)?'member_noaccess el_mem_pages_col_noaccess':'').' '.(($cur_post_id==$page->ID)?' el_mem_current_page':'').'">';
                    if(!isset($element['style']['setting']['hide_image'])) {
                        $content.='<a class="el_mem_page_thumb" href="'.get_permalink($page->ID).'">'.$image.'</a>';
                        $text_class="el_mem_page_text";
                    }
                    else $text_class="el_mem_page_text_noimg";
                      
                    $content.='<div class="'.$text_class.'">       
                                <a class="el_mem_page_title" href="'.get_permalink($page->ID).'">'.$page->post_title.'</a>
                                '.$com.'
                                '.(isset($page_setting['description']) && $page_setting['description'] && !isset($element['style']['setting']['hide_desc']) ? '<p>'.$page_setting['description'].'</p>':'').'
                            </div> 
                            <div class="cms_clear"></div>     
                    </div>';
                    $i=($i==$element['style']['cols'])? $i=1 : $i+1; 
                    if($i==1 && $element['style']['cols']>1) $content.='<div class="cms_clear"></div>'; 
                }
                
            }
            if($i==1 && $element['style']['cols']>1) $content.='</div>'; 

        } 
        if($i!=1) $content.='<div class="cms_clear"></div></div>'; 
        $content.='</div><div class="cms_clear"></div>'; 
    }
    else if($member_module->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Tato nebo vybraná stránka nemá žádné podstránky, proto nelze seznam lekcí vypsat. Vyberte stránku, která má podstránky, nebo vytvořte podstránky této stránce.','cms_member').'</div>';
    else $content='';
    
    return $content;
}

function ve_element_member_checklist($element, $css_id, $post_id) { 
    global $vePage, $member_module;
    $user = wp_get_current_user();
    $val=array();
    $content='';
    $page_member=get_post_meta($post_id, 'page_member', true);   
    if(isset($page_member['member_page'])) {
        if(isset($element['style']['use']) && $element['style']['use']=='page') {
            $page_setting=get_post_meta($post_id, 'page_member', true);
            $checklist=isset($page_setting['checklist'])?$page_setting['checklist']:'';
            $db_element_id=$post_id;        
        } 
        else {
            $checklist=$element['content'];
            $db_element_id=$element['style']['checklist'].'_'.$post_id; 
            
            // temporary for delete old meta 19.3.2015
            if(!isset($element['style']['use'])) {
               
                $old_meta=get_user_meta($user->ID, $element['style']['checklist'], true);
                if($old_meta) {
                    $new_meta=get_user_meta( $user->ID, 'checklist', true );
                    $new_meta[$db_element_id]=$old_meta;
                    update_user_meta( $user->ID, 'checklist', $new_meta );
                    delete_user_meta( $user->ID, $element['style']['checklist'] );
                }            
            }
            // end of temporary
                 
        } 
        //print_r($element);
        if(is_array($checklist)) {     
            
            if(!isset($element['style']['icon'])) {
                $element['style']['icon']=array(
                    'size'=>'25',
                    'corner'=>'0',
                    'background'=>'#52a303',
                    'color'=>'#fff',
                    'code'=>'<?xml version="1.0"?\><!DOCTYPE svg  PUBLIC "-//W3C//DTD SVG 1.1//EN"  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd"><svg enable-background="new 0 0 24 24" height="24px" version="1.1" viewBox="0 0 24 24" width="24px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path clip-rule="evenodd" d="M21.652,3.211c-0.293-0.295-0.77-0.295-1.061,0L9.41,14.34  c-0.293,0.297-0.771,0.297-1.062,0L3.449,9.351C3.304,9.203,3.114,9.13,2.923,9.129C2.73,9.128,2.534,9.201,2.387,9.351  l-2.165,1.946C0.078,11.445,0,11.63,0,11.823c0,0.194,0.078,0.397,0.223,0.544l4.94,5.184c0.292,0.296,0.771,0.776,1.062,1.07  l2.124,2.141c0.292,0.293,0.769,0.293,1.062,0l14.366-14.34c0.293-0.294,0.293-0.777,0-1.071L21.652,3.211z" fill-rule="evenodd"/></svg>',
                );
            }               
        
            $meta=get_user_meta($user->ID, 'checklist', true);
            if(isset($meta[$db_element_id])) $val=$meta[$db_element_id];
               
            wp_enqueue_script('member_checklist_script');
            $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .el_mem_download"):"";
            
            $size=$element['style']['icon']['size'];
            $padding=$size*0.4;
            $size_padding=($size+2*$padding);
  
            $content=$vePage->print_styles_array(array(        
                array(
                    'styles'=>isset($element['style']['font_text'])? array('font'=>$element['style']['font_text']): '',
                    'element'=>$css_id.' li',
                ),
                array(
                    'styles'=>array(
                        'padding-left'=>1.3*$size_padding."px",
                        'height'=>$size_padding,
                        'min-height'=>$size_padding,
                    
                    ),
                    'element'=>$css_id.' .label',
                ),
                array(
                    'styles'=>array(
                        'width'=>$size_padding."px",
                        'height'=>$size_padding,
                        'corner'=>$element['style']['icon']['corner'],
                        'padding'=>array('left'=>$padding,'right'=>$padding,'top'=>$padding,'bottom'=>$padding),    
                    ),
                    'element'=>$css_id.' .mem_checklist_checkbox',
                ),
                array(
                    'styles'=>array(
                        'width'=>$size."px",
                        'height'=>$size,
                        'fill'=>$element['style']['icon']['color']
                    ),
                    'element'=>$css_id.' .mem_checklist_checkbox_icon svg',
                ),
                array(
                    'styles'=>array('fill'=>$element['style']['icon']['color']),
                    'element'=>$css_id.' .mem_checklist_checkbox_icon svg path',
                ),
                array(
                    'styles'=>isset($element['style']['font'])? array('font'=>$element['style']['font']): '',
                    'element'=>$css_id.' h2',
                ),
                array(
                    'styles'=>array('border-color'=>$element['style']['icon']['background']),
                    'element'=>$css_id.' li:hover .mem_checklist_checkbox',
                ),
                array(
                    'styles'=>array('background-color'=>$element['style']['icon']['background']),
                    'element'=>$css_id.' .mem_checklist_checkbox_checked',
                ),
            ));
           
            $content.='<form class="in_element_content el_mem_checklist" '.$instyle.'>';
            if($element['style']['title']) $content.='<h2>'.$element['style']['title'].'</h2>';        
            $content.='<input type="hidden" value="checklist" name="element_id" />';
            $content.='<input type="hidden" value="'.$db_element_id.'" name="page_id" />';
            $content.='<input type="hidden" value="'.$user->ID.'" name="element_user_id" />';
            $content.='<ul>';
            foreach($checklist as $key=>$item) {
                $content.='<li> 
                    <div class="label">
                        <div class="mem_checklist_checkbox '.(isset($val[$key])? 'mem_checklist_checkbox_checked':'').'">
                            <div class="mem_checklist_checkbox_icon">'.stripslashes($element['style']['icon']['code']).'</div>
                            <input type="checkbox" '.(isset($val[$key])? 'checked="checked"':'').' name="element_data['.$key.']" />
                        </div>
                        '.$item['text'].'
                    </div>
                </li>';   
            }
            $content.="</ul></form>";
        }
        else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__("Nejsou zadány žádné úkoly.","cms_member").'</div>';
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__("Tento element bude fungovat pouze na členské stránce. Tato stránka není zařazena do žádné členské sekce.","cms_member").'</div>';
    return $content;
}

function ve_element_member_progress($element, $css_id, $post_id) { 
    global $vePage, $member_module;
    $content='';  
    $page_member=get_post_meta($post_id, 'page_member', true);   
    
    if(isset($page_member['member_page'])) {
    
        $user = wp_get_current_user();
        $checklists=get_user_meta($user->ID, 'checklist', true);
        
        $progressfor='page';
        if(isset($element['style']['show'])) $progressfor=$element['style']['show'];
        
        if($progressfor=='page') {        
            $child_of=($element['style']['page']==='')?$post_id:$element['style']['page'];
            
            $parent = get_post($child_of); 
            $pages = get_pages(array('child_of' => $child_of)); 
            $pages[]=$parent;
        } else {

            $member_id=(isset($element['style']['member']) && $element['style']['member']!='')?$element['style']['member']:$page_member['member_page'];
            $all_pages = get_pages( array('meta_key'=>'page_member') );
            $pages=array();
            foreach($all_pages as $page) {  
                $meta=get_post_meta( $page->ID, 'page_member', true ); 
                if(isset($meta['member_page']) && $meta['member_section']['section']==$member_id) {  // add login only if not member page
                    $pages[]=$page;
                }
            }
        }      
        
        $tasks=0;
        $suc=0;
        
        foreach($pages as $page) {
            $page_setting=get_post_meta($page->ID, 'page_member', true);          
            if(isset($page_setting['checklist']) && is_array($page_setting['checklist'])) {
                $tasks+=count($page_setting['checklist']);
                if(isset($checklists[$page->ID]) && is_array($checklists[$page->ID])) $suc+=count($checklists[$page->ID]);
            }
        }
        if($tasks) {
            $element['style']['percent']=round($suc/$tasks*100);
        }
        else {
            $element['style']['percent']=100;
            if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__("Na vybraných stránkách nebyly nalezeny žádné úkoly.","cms_member").'</div>';
        }
        $content.=ve_element_progressbar($element, $css_id, $post_id);

    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__("Tento element bude fungovat pouze na členské stránce. Tato stránka není zařazena do žádné členské sekce.","cms_member").'</div>';
  
    return $content;
}   
function ve_element_member_news($element, $css_id, $post_id) {  
    global $vePage, $member_module;
    $content=$vePage->print_styles_array(array(        
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>$css_id.' .el_mem_news',
            ),
            array(
                'styles'=>array('font'=>$element['style']['font_title']),
                'element'=>$css_id.' .member_new_title',
            ),
    ));
    
    $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .el_mem_news"):"";

    if($element['style']['type']=='all') {
        $num=$element['style']['per_page'];  
        $words=$element['style']['words_all'];
        $count=wp_count_posts( 'member_news' );
        $total=ceil($count->publish/$num);
        if ( !$current_page = get_query_var('paged') )
            $current_page = 1;
        if(is_front_page() && isset($_GET['paged']) && $_GET['paged'])
          $current_page = $_GET['paged'];

        if ( $total > 1 )  {
             
             if( get_option('permalink_structure') ) {
        	     $format = '?paged=%#%';
             } else {
        	     $format = 'page/%#%/';
             }
             $pagination=paginate_links(array(
                  'base'     => get_pagenum_link(1) . '%_%',
                  'format'   => $format,
                  'current'  => $current_page,
                  'total'    => $total,
                  'show_all' => false,
                  'type'     => 'list',
                  'prev_text'     => __('&laquo; novější','cms_member'),
                  'next_text'     => __('starší &raquo;','cms_member')
             ));
        }  
    } else {
        $num=$element['style']['number_news'];  
        $words=$element['style']['words_last'];
        $current_page = 1;
    }

    $args = array(
    	'posts_per_page'   => $num,
    	'post_type'        => 'member_news',
      'offset'        => ($current_page-1)*$num,
    );     
    $news = get_posts( $args );  
    $content.='<div class="in_element_content el_mem_news" '.$instyle.'>';
    
    foreach($news as $new) {

        $short=wp_trim_words( $new->post_content, $words );
        $text=$new->post_content;

        if($words>=str_word_count( wp_strip_all_tags($new->post_content))) {
            $short=$new->post_content;
            $text='';
        } else $short.=' <a class="member_new_show_text" href="#">'.__("Zobrazit celé","cms_member").'</a>';
                 
        $content.='<div class="member_new">';
        $content.='<div class="member_new_date">'.date(get_option('date_format').' '.get_option('time_format'),strtotime($new->post_date)).'</div>';
        $content.='<h3 class="member_new_title">'.$new->post_title.'</h3>';
        $content.='<div class="member_new_short entry_content">'.wpautop($short).'</div>';
        if($text) $content.='<div class="member_new_text entry_content">'.wpautop($text).' <a class="member_new_show_text" href="#">'.__("Skrýt","cms_member").'</a></div>';
        $content.='</div>';
    }
    // page navigation
    if($element['style']['type']=='all' && $total > 1) $content.=$pagination;
    $content.='</div>';
    return $content;
}

function ve_element_member_users($element, $css_id, $post_id) { 
    global $vePage, $member_module;
    
    wp_enqueue_script( 've_lightbox_script' );
    wp_enqueue_style( 've_lightbox_style' );
        
        
    $content='';
    
    $content=$vePage->print_styles_array(array(        
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>$css_id.' .mem_member_list_text',
            ),
            array(
                'styles'=>array('font'=>$element['style']['font_title']),
                'element'=>$css_id.' .mem_member_list_name',
            ),
            array(
                'styles'=>array('background-color'=>$vePage->hex2rgba($element['style']['button_color'],85)),
                'element'=>$css_id.' .mem_member_list_more',
            ),
    ));
    
    $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .el_mem_news"):"";

    // get members
    $args = array (
        'role' => 'member',
        'order' => 'ASC',
        'orderby' => 'display_name',
        'meta_key' => 'mw_hide_member',
        'meta_compare' => 'NOT EXISTS'
    ); 
    
    if(isset($_GET['search_member']) && $_GET['search_member']) {
        $args['search']='*'.esc_attr( $_GET['search_member'] ).'*'; 
        /*
        $args['meta_key'] = 'description';
        $args['meta_value'] = $_GET['search_member'] ;
        $args['meta_compare'] = 'LIKE'; */  
    }
    $wp_user_query = new WP_User_Query($args);
    $all_users = $wp_user_query->get_results();
    $users=array();
    
    
    $count=0;
    foreach($all_users as $user) { 
        //print_r($element['style']['member_section']);
        $add=true;
        if($element['style']['show']==2) {
            $member_id=$element['style']['member_section']['section'];
        
            $add=false;
            $user_member=get_the_author_meta( 'cms_member', $user->ID );


            if(isset($user_member[$member_id]) && $user_member[$member_id]['section']==1) { 
                if(isset($element['style']['member_section'][$member_id])) {
                    $user_levels=(isset($user_member[$member_id]['levels']))?$user_member[$member_id]['levels']:array();
                    foreach($element['style']['member_section'][$member_id]['levels'] as $level_id=>$level) {
                        if(isset($user_levels[$level_id])) $add=true;        
                    }                    
                }
                else $add=true;
           }
        }      
        if($add) {
            $count++;
            $users[]=$user;
        }
    }
    
    // pagination prepare
    
    $num=$element['style']['per_page'];  
    $words=$element['style']['words'];
    $total=ceil($count/$num);   
    if ( !$current_page = get_query_var('paged') )
            $current_page = 1;
    if ( $total > 1 )  {
             
             if( get_option('permalink_structure') ) {
        	     $format = '?paged=%#%';
             } else {
        	     $format = 'page/%#%/';
             }

             $pagination=paginate_links(array(
                  'base'     => get_permalink($post_id).'%_%',
                  'format'   => $format,
                  'current'  => $current_page,
                  'total'    => $total,
                  'show_all' => false,
                  'type'     => 'list',
                  'prev_text'     => file_get_contents(MEMBER_DIR."./images/icons/left.svg", true),
                  'next_text'     => file_get_contents(MEMBER_DIR."./images/icons/right.svg", true)
             ));
    }
    
    // print prepare
    
    if($element[ 'style' ][ 'style' ]==2) $cols = isset( $element[ 'style' ][ 'cols' ] ) ? intval( $element[ 'style' ][ 'cols' ] ): 3;
    else $cols=1;
    
    $rows = array_chunk( $users, $cols );
    $row_classes_map = array(
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five'
    );

    $row_classes = 'col col-' . $row_classes_map[ $cols ];
    
    // print members
    
    $content.='<div class="in_element_content el_mem_member_list el_mem_member_list_'.$element['style']['style'].'" '.$instyle.'>';
    
    $content.='<div class="mem_member_list_head">';
    
        if($element['style']['title']) $content.='<h2>'.$element['style']['title'].'</h2>';
        
        // search form
        $content.='<form action="'.get_permalink($post_id).'" method="GET">';
        $content.='<input type="text" class="mem_search_member_input" name="search_member" value="'.((isset($_GET['search_member']))?$_GET['search_member']:'').'" placeholder="'.__('Hledat','cms_member').'" />';
        $content.='<button type="submit" class="mem_search_member_but">'.file_get_contents(MEMBER_DIR."./images/icons/search.svg", true).'</button>';
        if(isset($_GET['search_member']) && $_GET['search_member']) $content.='<a class="mem_search_member_cancel" href="'.get_permalink($post_id).'" title="'.__('Zrušit vyhledávání','cms_member').'">'.file_get_contents(MEMBER_DIR."./images/icons/close.svg", true).'</a>';
        $content.='</form>';
        
    $content.='<div class="cms_clear"></div></div>';
    
    $i=0;
    if($count>0) {
    $row_num=0;
    $printed_row=0;
        
    foreach( $rows as $row ){      
      if($row_num>=($current_page-1)*$num/$cols && $row_num<$current_page*$num/$cols) {
        $row_class='mem_list_row';
        if($printed_row==0) $row_class.=' mem_list_row_first';
        
        $content .= '<div class="'.$row_class.'">';

        foreach($row as $user) {
          
            $usermeta=get_user_meta($user->ID );
            $member_fields=get_user_meta($user->ID,'member_fields',true);
            $short=wp_trim_words( $usermeta['description'][0], $words );
            $text=$usermeta['description'][0];
            if(strlen($short)==strlen(wp_strip_all_tags( $usermeta['description'][0] ))) {
                $short=$usermeta['description'][0];
            }
            
            $id=$css_id.'_mld_'.$i;
            $custom_fields = get_option('mw_member_user_custom_fields');
            
            $content.='<div class="mem_member_list_item '.$row_classes.'">
                <a class="mem_member_list_image responsive_image open_member_list_detail" href="'.$id.'">
                    '.get_avatar( $user->ID, 180 ).'
                    <span class="mem_member_list_more title_element_container">'.__('Více o mně','cms_member').'</span>  
                </a>            
                <div class="mem_member_list_content">
                    <a class="mem_member_list_name open_member_list_detail" href="'.$id.'"><h2>'.$user->display_name.'</h2></a>
                    '.((isset($member_fields['domain']) && $member_fields['domain'])? '<span class="mem_member_list_domain">'.$member_fields['domain'].'</span>':'').'               
                    <p class="mem_member_list_text">'.$short.'</p>
                    <div class="mem_member_list_contacts">';
                    
                        if($user->user_url) $content.='<a class="mem_member_list_url" href="'.$user->user_url.'" target="_blank">'.$user->user_url.'</a>';
                        
                        $content.='<div class="mem_member_list_socials">';
                    
                        if($user->facebook) $content.='<a class="mem_member_list_social mem_member_list_facebook" href="'.$user->facebook.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/facebook.svg", true).'</a>';
                        if($user->twitter) $content.='<a class="mem_member_list_social mem_member_list_twitter" href="'.$user->twitter.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/twitter.svg", true).'</a>';
                        if($user->google) $content.='<a class="mem_member_list_social mem_member_list_twitter" href="'.$user->google.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/google.svg", true).'</a>';
                        if($user->linkedin) $content.='<a class="mem_member_list_social mem_member_list_linkedin" href="'.$user->linkedin.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/linkedin.svg", true).'</a>';
                        if($user->youtube) $content.='<a class="mem_member_list_social mem_member_list_youtube" href="'.$user->youtube.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/youtube.svg", true).'</a>';
                        
                        $content.='</div>';
           
                    $content.='</div>
                </div>
                <div class="cms_nodisp">
                    <div class="mem_member_list_detail" id="'.str_replace("#","",$id).'">
                        <div class="mem_mld_head">
                            <div class="mem_mld_image">'.get_avatar( $user->ID, 180 ).'</div>
                            <div class="mem_mld_head_info">
                                <h2>'.$user->display_name.'</h2>
                                '.((isset($member_fields['domain']) && $member_fields['domain'])? '<span class="mem_member_list_domain">'.$member_fields['domain'].'</span>':'');
                                
                                $contacts='';                           
                                if(!isset($member_fields['hide_email'])) $contacts.='<div>email: <a class="mem_member_list_email" href="mailto:'.$user->user_email.'"">'.$user->user_email.'</a></div>';
                                if($user->user_url) $contacts.='<div>web: <a class="mem_member_list_url" href="'.$user->user_url.'" target="_blank">'.$user->user_url.'</a></div>';
                                
                                if($contacts) $content.='<div class="mem_member_list_contacts">'.$contacts.'</div>';
                                
                                $socials='';                            
                                if($user->facebook) $socials.='<a class="mem_member_list_social mem_member_list_facebook" href="'.$user->facebook.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/facebook.svg", true).'</a>';
                                if($user->twitter) $socials.='<a class="mem_member_list_social mem_member_list_twitter" href="'.$user->twitter.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/twitter.svg", true).'</a>';
                                if($user->google) $socials.='<a class="mem_member_list_social mem_member_list_twitter" href="'.$user->google.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/google.svg", true).'</a>';
                                if($user->linkedin) $socials.='<a class="mem_member_list_social mem_member_list_linkedin" href="'.$user->linkedin.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/linkedin.svg", true).'</a>';
                                if($user->youtube) $socials.='<a class="mem_member_list_social mem_member_list_youtube" href="'.$user->youtube.'" target="_blank">'.file_get_contents(MEMBER_DIR."./images/icons/youtube.svg", true).'</a>';
                                                           
                                if($socials) $content.='<div class="mem_member_list_social_icons">'.$socials.'</div>';
                                                            
                            $content.='</div>
                        </div>
                        <p>'.$text.'</p>';              
                        
                        if(is_array($custom_fields) && count($custom_fields)) {  
                            
                            $user_custom_fields=get_user_meta($user->ID,'member_custom_field', true);
                                        
                            foreach($custom_fields as $field) {                                
                                if(isset($user_custom_fields[$field['id']]) && $user_custom_fields[$field['id']]) {;                          
                                    $content.='<h3>'.$field['title'].'</h3>';
                                    $content.='<p>'.$user_custom_fields[$field['id']].'</p>';
                                }  
                            }    
                        }
                        
                    $content.='</div>
                </div>
            </div>';
           
          $i++;
        } // end foreach item

        $content .= '<div class="cms_clear"></div></div>';
        $printed_row++;  
      } 
      $row_num++;    
    } // end foreach row
    
       
    } else {
        $content.='<div class="mem_member_list_empty">'.__('Výsledku vyhledávání nikdo neodpovídá','cms_member').'</div>';
    }
    
    $content.='<script>
        jQuery(document).ready(function($) {
            $("'.$css_id.' .open_member_list_detail").colorbox({inline:true,href:$(this).attr("href"),maxWidth:"90%",width:"700px"});
        });
    </script>';
    if($total > 1) $content.='<div class="mw_page_navigation">'.$pagination.'</div>';
    $content.='</div>';
    return $content;
}


function ve_element_members_list($element, $css_id) { 
    global $vePage; 
    
    wp_enqueue_script( 've_lightbox_script' );
    wp_enqueue_style( 've_lightbox_style' );
    
    $user = wp_get_current_user(); 
    if($user->ID) { 
        $user_meta=get_the_author_meta( 'cms_member', $user->ID );                  
    }
    else $user_meta=array();
    
    $content='';
    $members = get_option( 'member_basic' );
    if($members && isset($element['style']['members'])) {
        $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .el_members_list"):"";
              
        $content.=$vePage->print_styles_array(array(        
            array(
                'styles'=>isset($element['style']['font'])? array('font'=>$element['style']['font']): '',
                'element'=>$css_id.' .el_member_section_list_title',
            ),
        ));
        
        $content.=$vePage->create_button_styles($element['style']['button'], '.el_member_section_list_noacc_popup .ve_content_button');
        if(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect']) $but_class=' ve_cb_hover_'.$element['style']['button']['hover_effect'];
        else $but_class='';
        
        $content.='<div class="in_element_content el_member_section_list el_member_section_list_'.$element['style']['style'].'" '.$instyle.'>';
        
        $cols = isset( $element[ 'style' ][ 'cols' ] ) ? intval( $element[ 'style' ][ 'cols' ] ): 3;
        $rows = array_chunk( $element['style']['members'], $cols );
        
        $col_classes_map = array(
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five'
        );
        
        $content.='<script>
            jQuery(document).ready(function($) {
                $("'.$css_id.' .open_mem_sec_noacc_popup").colorbox({inline:true,href:$(this).attr("href"),maxWidth:"90%",width:"600px"});  
            });
            </script>';
        $row_n=0;
        foreach( $rows as $row ){
        
            $content .= '<div class="el_member_section_list_row '.(($row_n==0)?'el_member_section_list_row_last':'').'">';
            $i=0;
            foreach($row as $member) {
                if(isset($members['members'][$member['member']])) {
                
                    $access=(isset($user_meta[$member['member']]) || $vePage->edit_mode)? true: false;
                    
                    $set_mem=$members['members'][$member['member']];
                    $image='<div class="el_member_section_list_empty_image"></div>';
                    $title=$set_mem['name'];

                    if($member['title']) $title=$member['title']; 
                    if($member['image']['imageid']) $image=wp_get_attachment_image( $member['image']['imageid'], 'mio_columns_'.$cols );
                    
                    if(!$access) $content.='<a class="el_member_section_list_item col col-'.$col_classes_map[$cols].' open_mem_sec_noacc_popup" href="'.$css_id.'_'.$i.'_popup">'; 
                    else $content.='<a class="el_member_section_list_item col col-'.$col_classes_map[$cols].'" href="'.get_permalink($set_mem['dashboard']).'">';  
                    $content.='<div class="el_member_section_list_image">';
                    $content.=$image;
                    if(!$access) $content.='<div class="el_member_section_list_noacc"><h2>'.__('Získat přístup','cms_member').'</h2><p>'.__('Do této členské sekce nemáte přístup','cms_member').'</p></div>';
                    $content.='</div>';
                    $content.=($title)? '<h2 class="el_member_section_list_title">'.$title.'</h2>':'';
                    $content.='</a>';
                    
                    $but_set=array(
                        'style'=>$element['style']['button'],
                        'show'=>'',
                        'popup'=>'',
                        'link'=>$member['link'],
                        'text'=>__('Získat přístup','cms_member'),
                        'align'=>'center',
                    );
                    
                    $content.='
                    <div style="display: none;">
                        <div id="'.str_replace('#','',$css_id).'_'.$i.'_popup" class="el_member_section_list_noacc_popup">
                            <small>'.__('Získat přístup do členské sekce','cms_member').'</small>
                            <h2>'.$title.'</h2>
                            <p>'.stripslashes($member['description']).'</p>
                            <a class="ve_content_button ve_content_button_'.$element['style']['button']['style'].' '.$but_class.'" href="'.$vePage->create_link($member['link']).'" target="_blank">'.__('Získat přístup','cms_member').'</a>
                        </div>
                    </div>';
                    
                    $i++; 
                }  
                 
            }
            
            $content.='<div class="cms_clear"></div></div>';
            $row_n++;
        }
        $content.='</div>';

    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__("V tomto elementu nejsou vytvořeny žádné členské sekce.","cms_member").'</div>';

    return $content;
}
