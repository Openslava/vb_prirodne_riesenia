<?php
function ve_element_mioweb_nav($element, $css_id, $post_id) {
    global $vePage;
    global $post;
    $current=$post_id;
    $campaigns = get_option('campaign_basic');
    $campaign=(isset($campaigns['campaigns']) && isset($campaigns['campaigns'][$element['style']['campaign']]))? $campaigns['campaigns'][$element['style']['campaign']]:null;
    $accessed_pages=array();
    if(isset($campaign['evergreen']) && isset($_COOKIE['mioweb_campaign_access']) && $_COOKIE['mioweb_campaign_access']) {
        $access=unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
        $accessed_pages=explode(",",$access[$element['style']['campaign']]);
    }

    if(isset($element['style']['campaign']) && isset($campaigns['campaigns']) && isset($campaigns['campaigns'][$element['style']['campaign']])) {
        $content=$vePage->print_styles(array('font'=>$element['style']['font']),$css_id.' .mioweb_campaign_menu .mioweb_campaign_menu_item','online');
        $content.=$vePage->print_styles(array('border-color'=>$element['style']['font']['color']),$css_id.' .mioweb_campaign_menu li','online');
        $content.=$vePage->print_styles(array('color'=>$element['style']['color-active']),$css_id.' li.current-menu-item .mioweb_campaign_menu_item,'.$css_id.' ul li a.mioweb_campaign_menu_item:hover','online');
        $content.='<div class="mioweb_campaign_menu mioweb_campaign_menu_'.$element['style']['style'].'"><ul>';
        $i=0;
        $count=count($campaign['page']);
        foreach($campaign['page'] as $page) {
            if($page['page'] && !isset($page['exclude'])) { 
                $wpage=get_post($page['page']);
                $date=strtotime($page['publishdate']['date']." ".$page['publishdate']['hour'].":".$page['publishdate']['minute'].":0");
                
                if(current_user_can('administrator') || (!isset($campaign['evergreen']) && $date<current_time( 'timestamp' )) || (isset($campaign['evergreen']) && isset($access) && ($access[$element['style']['campaign']]=='all' || in_array($page['page'],$accessed_pages)))) {
                    $name=$page['name'];
                    $thumb=$page['thumb'];
                    $tag="a";
                }
                else {
                    $name=($page['csname'])? $page['csname']: $page['name'];
                    $thumb=($page['csthumb'])? $page['csthumb']: $page['thumb'];
                    $tag="div";
                }
                $name=($name)? $name : $wpage->post_title; 
                
                $content.='<li'.(($current==$page['page'])? ' class="current-menu-item"' : '').'><'.$tag.' class="mioweb_campaign_menu_item '.(($i==$count-1)? 'mioweb_campaign_menu_item_last':'').'" href="'.get_permalink($page['page']).'">';
                if($thumb) $content.='<div class="mioweb_campaign_menu_img"><img src="'.home_url().$thumb.'" /></div>';  
                else $content.='<div class="mioweb_campaign_menu_img mioweb_campaign_menu_img_empty"></div>';    
                $content.='<span>'.$name.'</span></'.$tag.'></li>';
                
                $i++;
            }                
        }
           
        $content.='</ul></div>';
        if($i==0) $content.='<div class="cms_error_box admin_feature">'.__('Vybraná kampaň neobsahuje žádné stránky s obsahem zdarma. Nastavte je ve správě kampaní, jinak se nebude menu zobrazovat.','cms_mioweb').'</div>'; 

    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Kampaň neexistuje. Pravděpodobně byla smazána. Vyberte prosím jinou kampaň nebo toto menu kampaně smažte.','cms_mioweb').'</div>';
    else $content='';
    return $content;  
}
function ve_element_se_count($element, $css_id, $post_id, $edit_mode) {
    global $vePage, $apiConnection;
    
    // back compatibility (temporary)
    $element['style']['list']=$apiConnection->repair_content_val($element['style']['list']);
    // back compatibility end
    
    $content='';
    
    $instyle=$vePage->print_styles(array('max-width'=>((isset($element['config']['max_width']))? $element['config']['max_width'] : ''),'font'=>$element['style']['font']),$css_id." .in_element_content");
    if($element['style']['list']['id']) {

        $client=$apiConnection->getClient($element['style']['list']['api']);
        $count=$client->get_list_count($element['style']['list']['id']);
        
        if(isset($element['style']['limit']) && $element['style']['limit']) {
            $count=$element['style']['limit']-$count;
            
            if(isset($element['style']['limit_redirect'])) $url=$vePage->create_link($element['style']['limit_redirect']);
            else $url='';
            
            if($count<=0 && !$vePage->edit_mode && $url) {
                $content.="";
            }
        }
        if($count<0) $count=0;
        $content.='<div class="in_element_content ve_center" '.$instyle.'>'.stripslashes($element['style']['text1']).' <strong class="ve_number_count" data-number="'.number_format($count, 0, ',', ' ').'"><span>0</span></strong> '.stripslashes($element['style']['text2']).'</div>';
        
        if($edit_mode) {
            $content.='<script>
            jQuery(document).ready(function($) { 
                numberAnimationIncrease("'.$css_id.'");
            });</script>';
        }
        
        wp_enqueue_script( 've_waypoints_script' );
        
    } else if($vePage->edit_mode) {
        $content='<div class="cms_error_box admin_feature">'.__('Není vybrán žádný SmartEmailingový seznam. Element proto nelze správně zobrazit.','cms_mioweb').'</div>';
    }
    else $content='';  
    return $content;  
}
function ve_element_campaign_date($element, $css_id, $post_id, $edit_mode) {
    global $vePage;
    
    $content='';
    
    $instyle=$vePage->print_styles(
        array(
            'max-width'=>((isset($element['config']['max_width']))? $element['config']['max_width'] : ''),
            'font'=>$element['style']['font']
        ),$css_id." .in_element_content"
    );
    
    $campaign_id = get_post_meta( $post_id, 'mioweb_campaign',true );
      
    if(isset($_COOKIE['mioweb_campaign_access']) && $campaign_id) {
        $access=unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
        $time=$access['time'][$campaign_id['campaign']];
    } else $time=current_time( 'timestamp' );
    
    if($element['style']['days']==0 && $element['style']['time']) {
      $time=strtotime(Date('d.m.Y',$time).' '.$element['style']['time']);
      if(current_time('timestamp') > $time) {
          $time+=(24*3600); 
      }
        
    } else {
        $time+=$element['style']['days']*3600*24;
    }

    $content.='<div class="in_element_content ve_center" '.$instyle.'>';
    $content.=Date('d. m. Y',$time);
    if($element['style']['time']) $content.=' '.__('v','cms_mioweb').' '.stripslashes($element['style']['time']);
    $content.='</div>';
    
    if(!$campaign_id && $vePage->edit_mode)
          $content.='<div class="cms_error_box admin_feature">'.__('Pokud není datum umístěno na stránce zařazené do kampaně, nebude se odvíjet od vstupu do kampaně ale od vstupu na stránku.','cms_mioweb').'</div>';

    return $content;  
}
