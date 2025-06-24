<?php

function ve_element_text($element, $css_id) { 
    global $vePage;  

    wp_enqueue_script( 've_lightbox_script' );
    wp_enqueue_style( 've_lightbox_style' );

    $type=(isset($element['style']['style'])) ? $element['style']['style'] : 1;
    if(isset($element['config']['max_width'])) $element['style']['max-width']=$element['config']['max_width'];

    if(isset($element['style']['p-background-color']) && isset($element['style']['style']) && $element['style']['style']==2) 
        $element['style']['background_color'] = $element['style']['p-background-color'];

    $content=$vePage->print_styles_array(array(        
        array(
            'styles'=>$element['style'],
            'element'=>$css_id." .in_element_content",
        ),
    ));


    $size='small';
    if(isset($element['style']['font'])) {
        if($element['style']['font']['font-size']<28) $size='medium';
        else $size='big';    
    }

    if(isset($element['style']['li'])) $class = ' element_text_li'.$element['style']['li'];

    $class .= ' element_text_size_'.$size.' element_text_style_'.$type;
    if(isset($element['style']['li'])) $class.=' element_text_li'.$element['style']['li'];
    
    // shortcode paragraph repair
    $array = array(
            '<p>['    => '[',
            ']</p>'   => ']',
            ']<br />' => ']'
    );
    $element['content'] = strtr( $element['content'], $array );

    $content.='<div class="in_element_content entry_content ve_content_text '.$class.'">';
    $content.=do_shortcode(add_lightbox(stripslashes(wpautop($element['content']))));
    $content.='</div>';
    
    return $content;
}


function ve_element_title($element, $css_id, $post_id) {  
    global $vePage;
    $styles=array();
    $type=(isset($element['style']['style']))? $element['style']['style']:1;
    
    if(isset($element['config']['max_width']) && $element['config']['max_width']) $styles['max-width']=$element['config']['max_width'];
    if($type==2 || $type==3) $styles['background_color']=$element['style']['background-color'];
    if($type==4) $styles['border-bottom']=$element['style']['border'];
    if($type==5){
        $styles['border-bottom']=$element['style']['border'];
        $styles['border-top']=$element['style']['border'];
    }
    
    $style=(!empty($styles))? $vePage->print_styles($styles,$css_id.' .in_element_content'):''; 
    
    if($element['style']['font']['font-size']>50) $vePage->add_style(
        $css_id.' h1,'.$css_id.' h2,'.$css_id.' h3,'.$css_id.' h4,'.$css_id.' h5,'.$css_id.' h6,'.$css_id.' p',
        array('font'=>array('font-size'=>'50')), 
        '640'
    );
    if($element['style']['font']['font-size']>35) $vePage->add_style(
        $css_id.' h1,'.$css_id.' h2,'.$css_id.' h3,'.$css_id.' h4,'.$css_id.' h5,'.$css_id.' h6,'.$css_id.' p',
        array('font'=>array('font-size'=>'35')), 
        '480'
    );
    
    $class='in_element_content ve_title_style_'.$type;
    if($type==3 || $type==6) $class.=' ve_title_'.$element['style']['align'];

    $content='<div class="'.$class.'" '.$style.'>';
    $content.=$vePage->print_styles_array(array(        
        array(
            'styles'=>array('font'=>$element['style']['font']),
            'element'=>$css_id.' h1, '.$css_id.' h2,'.$css_id.' h3,'.$css_id.' h4,'.$css_id.' h5,'.$css_id.' h6,'.$css_id.' p',
        ),
        array(
            'styles'=>($type==6 && $element['style']['decoration-color'])? array('background-color'=>$element['style']['decoration-color']):array(),
            'element'=>$css_id." .ve_title_decoration",
        ),
    ));
    
    // shortcode paragraph repair
    $array = array(
            '<p>['    => '[',
            ']</p>'   => ']',
            ']<br />' => ']'
    );
    $element['content'] = strtr( $element['content'], $array );
    
    $content.=stripslashes(do_shortcode(wpautop($element['content'])));
    
    if($type==6) $content.='<span class="ve_title_decoration"></span>';
    
    $content.='</div>';
    if($type==3 && $element['style']['align']=='right') $content.='<div class="cms_clear"></div>';
    return $content;
}
function ve_element_button($element, $css_id) {
    global $vePage;    
    if(isset($element['config']['max_width'])) {
        $element['style']['button']['width']=$element['config']['max_width'];    
        $element['style']['button']['max-width']=$element['config']['max_width']; 
    }  
    
    $but_set1=array(
        'style'=>$element['style']['button'],
        'show'=>isset($element['style']['show'])? $element['style']['show']:'',
        'popup'=>isset($element['style']['popup'])? $element['style']['popup']:'',
        'link'=>$element['style']['link'],
        'text'=>$element['content'],
        'align'=>$element['style']['align'],
    );
    $content=$vePage->create_button($but_set1, $css_id.' .ve_content_first_button','ve_content_first_button');
    
    if(isset($element['style']['show_but2'])) {
        $but_set2=array(
            'style'=>$element['style']['button2'],
            'show'=>isset($element['style']['show2'])? $element['style']['show2']:'',
            'popup'=>isset($element['style']['popup2'])? $element['style']['popup2']:'',
            'link'=>$element['style']['link2'],
            'text'=>$element['style']['text2'],
            'align'=>$element['style']['align'],
        );
        $content.=$vePage->create_button($but_set2, $css_id.' .ve_content_second_button','ve_content_second_button');
    }
    $content.='<div class="cms_clear"></div>';
    
    return $content;
}

function ve_element_link($element, $css_id) {
    global $vePage;
    if(isset($element['config']['max_width'])) {  
        $element['style']['link']['max-width']=$element['config']['max_width']; 
    }

    $content = '';

    $target=(isset($element['style']['link']['target']) && $element['style']['link']['target']==1)? 'target="_blank"' : "";   

    $inline_style = isset($element['style']['font']) ? $vePage->print_styles(array('font'=>$element['style']['font']), $css_id.' a.ve_content_link','inline') : '';


    if(isset($element['style']['show']) && $element['style']['show']=='popup' && $element['style']['popup']) {
        if($vePage->edit_mode) {
            if(get_post($element['style']['popup'])) {
                $content.=$vePage->popups->create_popup($element['style']['popup']);
                wp_enqueue_script( 've_lightbox_script' );
                wp_enqueue_style( 've_lightbox_style' );
            }
        } else {
            if(get_post($element['style']['popup']))
                $vePage->popups->popups_onpage[$element['style']['popup']]=1; 
        }   
        $content.='';                                     
        $link="#";                
    }
    else $link=$vePage->create_link($element['style']['link']);

    $content .= '<p class="ve_content_link_'.$element['style']['align'].'"><a class="ve_content_link" '.$target.' '.$inline_style.' href="'.$link.'">'.$element['content'].'</a></p>';

    return $content;
}

function ve_element_html($element, $css_id) {
    global $vePage;
    if($element['content']) {
        if(isset($element['config']['max_width'])) {
            $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id.' .in_element_content');
            $content='<div class="in_element_content" '.$style.'>'.stripslashes($element['content']).'</div>';
        }
        else $content=stripslashes($element['content']);
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Není zadán žádný HTML kód.','cms_ve').'</div>';
    else $content="";
    return $content;
}
function ve_element_video($element, $css_id) {
    global $vePage;
    $text=$element['content'];  
    if(isset($element['config']['max_width'])) $element['style']['max-width']=$element['config']['max_width']; 
    
    $tag_id=str_replace('#','',$css_id);
    
    if(isset($element['style']['align']) && $element['style']['align']=='left' && !empty($element['style']['max-width'])) $class='in_element_content_left';
    else if(isset($element['style']['align']) && $element['style']['align']=='right' && !empty($element['style']['max-width'])) $class='in_element_content_right';
    else $class='';
      
    $style=$vePage->print_styles($element['style'],$css_id.' .in_element_content');      
      

    $content='<div class="video_content_fullwidth">';
    if(isset($element['style']['code']) && $element['style']['code']) $content.=stripslashes($element['style']['code']);
    else if($text) {
        $autoplay=(isset($element['style']['setting']['autoplay']))? '1' : '0';
        $showinfo=(isset($element['style']['setting']['showinfo']))? '1' : '0';
        $rel=(isset($element['style']['setting']['rel']))? '1' : '0';
        $controls=(isset($element['style']['setting']['hide_control']))? '0' : '1';
        
        if(isset($element['style']['noclick'])) {
            $autoplay='1';
            $showinfo='0';
            $rel='0';
            $controls='0';
        }
    
        if (strpos($text,'youtube')) {
            if($controls=='1') $controls.='&autohide=1';
            $url=parse_url($text);
            parse_str($url['query'], $atributes);
            $para='?wmode=transparent&enablejsapi=1&rel='.$rel.'&autoplay='.$autoplay.'&showinfo='.$showinfo.'&controls='.$controls;
            $content.='<iframe id="'.$tag_id.'_video" src="//www.youtube.com/embed/'.$atributes['v'].$para.'" frameborder="0" allowfullscreen></iframe>';
        }
        else if(strpos($text,'youtu.be')) {
            if($controls=='1') $controls.='&autohide=1';
            $url=parse_url($text);
            $para='?wmode=transparent&enablejsapi=1&rel='.$rel.'&autoplay='.$autoplay.'&showinfo='.$showinfo.'&controls='.$controls;
            $content.='<iframe id="'.$tag_id.'_video" src="//www.youtube.com/embed'.$url['path'].$para.'" frameborder="0" allowfullscreen></iframe>';
        }
        else if(strpos($text,'vimeo')) {
            $url=parse_url($text);
            $para='?autoplay='.$autoplay.'&title='.$showinfo.'&byline='.$showinfo.'&portrait='.$showinfo;
            if($url['host']=='player.vimeo.com') {
                $iframe_url='//player.vimeo.com'.$url['path'].$para;
            } else {                
                $iframe_url='//player.vimeo.com/video'.$url['path'].$para;                
            }
            $content.='<iframe id="'.$tag_id.'_video" src="'.$iframe_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        }
        else if($vePage->edit_mode) {
            $content.='<div class="cms_error_box admin_feature">'.__('URL stránky s videem není v tomto tvaru podporováno.','cms_ve').'</div>';
        }
    } 
    
    if(isset($element['style']['noclick'])) {
        $content.='<div class="video_element_overlay"></div>';
    }
    
    if($vePage->edit_mode && !$text && !$element['style']['code']) $content.='<span>'.__('V nastavení tohoto elementu zadejte odkaz na své video.','cms_ve').'</span>';
    $content.='</div>';
    
    if(isset($element['style']['popup'])) {        
        wp_enqueue_script( 've_lightbox_script' );
        wp_enqueue_style( 've_lightbox_style' );
        if($element['style']['popup_type']=='button') {
            $but_set=array(
                'style'=>$element['style']['popupbutton'],
                'show'=>'',
                'popup'=>'',
                'link'=>array('link'=>'#'),
                'text'=>$element['style']['button_text'],
                'align'=>isset($element['style']['align'])?$element['style']['align']:'center',
            );
            $link=$vePage->create_button($but_set, $css_id.' .open_lightbox_popup', 'open_lightbox_popup');
            
        } else {
            $content.=$vePage->print_styles_array(array(        
                array(
                    'styles'=>array('width'=>$element['style']['play']['size']."px",'height'=>$element['style']['play']['size']),
                    'element'=>$css_id." a .video_play_button, ".$css_id." a .video_play_button svg",
                ),
                array(
                    'styles'=>array('margin_top'=>'-'.($element['style']['play']['size']/2),'margin_left'=>'-'.($element['style']['play']['size']/2)),
                    'element'=>$css_id." a.element_image .video_play_button",
                ),
                array(
                    'styles'=>array('fill'=>$element['style']['play']['color']),
                    'element'=>$css_id." .video_play_button path",
                ),
            ));
            $img='';
            if(isset($element['style']['align'])) $link_class='element_image_'.$element['style']['align'];
            if($element['style']['image']) {
                $link_class.=' element_image';
                $img='<img src="'.home_url().$element['style']['image'].'" alt="" />';
            } 
            $link='<a class="open_lightbox_popup '.$link_class.'" href="'.$css_id.'_popup">'.$img.'
                <div class="video_play_button">'.stripslashes($element['style']['play']['code']).'</div>
            </a>';
        }
        $content='
        <script>
        jQuery(document).ready(function($) {
            var video_url = $("'.$css_id.'_video").attr("src");
            $("'.$css_id.'_video").attr("src","");
            $("'.$css_id.' .open_lightbox_popup").colorbox({
              inline:true,
              href:"'.$css_id.'_popup",
              maxWidth:"90%",
              width:"800px",
              onClosed: function() {
                  $("'.$css_id.'_video").attr("src","none");
              },
              onComplete: function() {
                  $("'.$css_id.'_video").attr("src",video_url);
              }
            });  
        });
        </script>
        <div class="ve_center">'.$link.'</div>
        <div style="display: none;">
            <div id="'.str_replace('#','',$css_id).'_popup" class="popup_video_container">
                '.$content.'
            </div>
        </div>';
    } else if(isset($element['style']['noclick'])) {
      $user_agent = $_SERVER['HTTP_USER_AGENT']; 
      if ((strpos( $user_agent, 'Safari') !== false && strpos( $user_agent, 'Chrome') == false)){
          wp_enqueue_script('ve_youtube_api');
      
          $content.='<script>
          var player;
          function onYouTubeIframeAPIReady() {
            player = new YT.Player("'.$tag_id.'_video", {
              events: {
                "onReady": function(event) {
                    jQuery("'.$css_id.' .video_element_overlay").click(function() { 
                        event.target.playVideo();
                    });
                }
              }
            });
          }
          </script>';
      }
      
    }
    
    $content='<div class="in_element_content '.$class.' '.(($text || $element['style']['code'])?'':'video_element_novideo').'" '.$style.'>'.$content.'</div><div class="cms_clear"></div>';
    
    return $content;  
}


function ve_element_graphic($element, $css_id) { 
    global $vePage;   
    $style=(isset($element['config']['max_width']))? $vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id.' .graphic_element') : '';    
    if($element['style']['style']['itemtype']=="hr") $content='<hr class="graphic_element graphic_element_'.$element['style']['style']['item'].' graphic_element_'.$element['style']['style']['itemtype'].'" '.$style.'>';
    else  $content='<div class="graphic_element graphic_element_'.$element['style']['style']['item'].' graphic_element_'.$element['style']['style']['itemtype'].'" '.$style.'><img src="'.VS_DIR.'images/image_select/'.$element['style']['style']['item'].'.png" alt="" /></div>';
    return $content;
}
function ve_element_image($element, $css_id) {                                                                 
    global $vePage;   
    
    // new id in images compatibility (temporary)
    if(isset($element['style']['image']) && !is_array($element['style']['image']))
        $element['style']['image']=array('image'=>$element['style']['image']);
    if(isset($element['style']['large_image']) && !is_array($element['style']['large_image']))
        $element['style']['large_image']=array('image'=>$element['style']['large_image']);
    // end temporary
   
    if($element['style']['image']['image']){
        $link=false;  
        if(isset($element['config']['max_width']) && !$element['style']['max-width']) $element['style']['max-width']=$element['config']['max_width'];       
        
        //$style=$vePage->print_styles($element['style'],$css_id.' .element_image'); 
        
        $styles=array(
          array(
            'styles'=>$element['style'],
            'element'=>$css_id.' .element_image',
          ),
        );
        if(isset($element['style']['hover_color']) && $element['style']['hover_color']) {
            $styles[]=array(
                'styles'=>array('background-color'=>$vePage->hex2rgba($element['style']['hover_color'],70)),
                'element'=>$css_id." .element_image_overlay_icon_container",
            );
        }
        $content=$vePage->print_styles_array($styles);
        
        $hover_class=(isset($element['style']['hover']) && $element['style']['hover'])? 'image_hover_'.$element['style']['hover']:'';
        
        $content.='<div class="element_image element_image_'.$element['style']['style'].' '.$hover_class.' element_image_'.$element['style']['align'].'">';
        
        $content.='<div class="element_image_container">';
        
        $type="";
        $class="";
        if(!isset($element['style']['click_action'])) {
            if($element['style']['link']['link']) $type="link"; 
            else if(isset($element['style']['large_image']['image']) && $element['style']['large_image']['image']) $type="image"; 
        } else $type=$element['style']['click_action'];
        
        if($type=='link') { 
            $img_title='';
            if(isset($element['style']['image']['imageid'])) {
                $img_title = get_the_title($element['style']['image']['imageid']);          
            } 
            $content.='<a href="'.$vePage->create_link($element['style']['link']).'" '.((isset($element['style']['link']['target']))? 'target="_blank"':'').' '.(($img_title)? 'title="'.$img_title.'"':'').'>'; 
            $link=true;
        }
        else if($type=='image') {
            wp_enqueue_script( 've_lightbox_script' );
            wp_enqueue_style( 've_lightbox_style' );
            if($vePage->edit_mode) $content.='';
            $img=($element['style']['large_image']['image'])? $element['style']['large_image']['image']:$element['style']['image']['image'];
            $content.='<a class="open_lightbox" href="'.home_url().$img.'">';
            $link=true;
            
        }
        else if($type=='alert') {
            $class.='element_image_alert';
            $content.='<script type="text/javascript">
            jQuery(document).ready(function($) {
                $("'.$css_id.' img").click(function(){
                    alert("'.$element['style']['alert'].'");
                });
            });
            </script>';
        
        } if($type=='popup' && $element['style']['popup']) {
            if($vePage->edit_mode) {
                if(get_post($element['style']['popup'])) {               
                    $content.=$vePage->popups->create_popup($element['style']['popup']);
                    wp_enqueue_script( 've_lightbox_script' );
                    wp_enqueue_style( 've_lightbox_style' );
                }
            } else {
                if(get_post($element['style']['popup']))
                    $vePage->popups->popups_onpage[$element['style']['popup']]=1;
            }                 
            $content.='';                       
            $content.='<a class="open_popup" href="#">'; 
            $link=true;  
        }
        
        
        if(isset($element['style']['hover']) && ($element['style']['hover']=='zoom' || $element['style']['hover']=='overlay_icon')) $content.='<div class="element_image_hover_container">';
        $content.=$vePage->generate_image($element['style']['image'], $class);
        if(isset( $element['style']['hover'] ) && $element['style']['hover']=='overlay_icon') {
            $content.='<div class="element_image_overlay_icon_container">'.file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/zoom-in.svg", true).'</div>';
        }
        if(isset($element['style']['hover']) && ($element['style']['hover']=='zoom' || $element['style']['hover']=='overlay_icon')) $content.='</div>';
        
        if($link) $content.='</a>'; 

        $content.='</div>'; // end of element_image_container
        if($element['style']['label']) $content.='<span>'.$element['style']['label'].'</span>';
        
        $content.='</div><div class="cms_clear"></div>';
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Není zadán žádný obrázek.','cms_ve').'</div><div class="ve_empty_image_container"><span></span></div>';
    else $content="";
    return $content;
}

function ve_element_image_gallery($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;
    $output = '';
    
    if(isset($element['config']['max_width']))       
        $wstyle=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id.' .image_gallery_element'); 
    else $wstyle='';
    
    $styles=array(
      array(
          'styles'=>array('font'=>$element[ 'style' ]['font']),
          'element'=>$css_id . " .image_gallery_element__item__caption",
      ),
    );
    if(isset($element['style']['hover_color']) && $element['style']['hover_color']) {
        $styles[]=array(
            'styles'=>array('background-color'=>$vePage->hex2rgba($element['style']['hover_color'],70)),
            'element'=>$css_id." .image_gallery_element_item_thumb .image_gallery_overlay_icon_container",
        );
    }
    
    $output.=$vePage->print_styles_array($styles);

    if( !isset( $element[ 'style' ][ 'image_gallery_items' ] ) || empty( $element[ 'style' ][ 'image_gallery_items' ] ) ){
        if($vePage->edit_mode) {
            $output = '<div class="cms_error_box admin_feature">' . __( 'Nezvolili jste žádné obrázky do galerie.', 'cms_ve' ) . '</div>';
        }
    } else {

    wp_enqueue_script( 'picturefill' );
    wp_enqueue_script( 've_lightbox_script' );
    wp_enqueue_style( 've_lightbox_style' );
    if(isset($element['style']['use_slider'])) {
        wp_enqueue_script( 've_miocarousel_script' );
        wp_enqueue_style( 've_miocarousel_style' );
        if($vePage->is_mobile) $element['style']['cols']=1;
    }                             


    $cols = isset( $element[ 'style' ][ 'cols' ] ) ? intval( $element[ 'style' ][ 'cols' ] ): 4;

    $image_rows = array_chunk( $element[ 'style' ][ 'image_gallery_items' ], $cols );
    $image_row_classes_map = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five'
    );
    
    // col type
    if(isset( $element[ 'style' ][ 'cols_type' ] ) && $element['style']['cols_type']) {
        $col_class=$element[ 'style' ][ 'cols_type' ].'col '.$element[ 'style' ][ 'cols_type' ].'col-';
    } else $col_class='col col-';
    
    $image_row_classes = $col_class . $image_row_classes_map[ $cols ];

    $rel_attr = 'mio_image_gallery_' . substr( md5( serialize( $element[ 'style' ] ) ), 0, 10 ); //unique frontend ID for each gallery on page
    
    $gallery_class='image_gallery_element in_element_content is-theme-' . $element[ 'style' ][ 'gallery_style' ];
    
    
    // hover class
    if(isset( $element[ 'style' ][ 'hover' ] ) && $element['style']['hover']) {
        $gallery_class.=' image_gallery_hover_'.$element[ 'style' ][ 'hover' ];
    }
    
    $carousel_set='';
    if(isset($element['style']['use_slider'])) {
        $gallery_class.=' miocarousel miocarousel_style_1';
        if($element['style']['color_scheme']) $gallery_class.=' miocarousel_'.$element['style']['color_scheme'];
        if(isset($element['style']['off_autoplay'])) $carousel_set.=' data-autoplay="0"';
        if($element['style']['delay']) $carousel_set.=' data-duration="'.$element['style']['delay'].'"';
        if($element['style']['speed']) $carousel_set.=' data-speed="'.$element['style']['speed'].'"';
        if($element['style']['animation'] && $element['style']['animation']!='fade') $carousel_set.=' data-animation="'.$element['style']['animation'].'"';
    }
    
    $thumb_name='mio_columns_c';  
    
    $image_item_class=''; 
    if(isset($element['style']['thumb_name']) && $element['style']['thumb_name']!='mio_columns_c') {
        if($element['style']['thumb_name']=='mio_columns_') $element['style']['thumb_name']='43';
        $image_item_class='mw_image_ratio mw_image_ratio_'.$element['style']['thumb_name'];
    }
    
    $output .= '<div class="'. $gallery_class . '" '.$carousel_set.' '.$wstyle.'>';  
    if(isset($element['style']['use_slider']))
            $output .= '<div class="miocarousel-inner">';

    $row_num=1;
    $max_height=0;
    foreach( $image_rows as $row ){
  
        if(isset($element['style']['use_slider'])) {
            $row_class=' slide';
            if($row_num==1) $row_class.=' active';
        } else {
            $row_class='image_gallery_element__row';
            if($row_num==count($image_rows)) $row_class.=' image_gallery_element__row_last';
        }

        $output .= '<div class="'.$row_class.'">'; 

        foreach( $row as $image ) {
           
           if(substr($image, 0, 4) == 'http') {
              $show_image=$vePage->generate_image(array('image'=>$image));
              $href=$image;
              $caption_html='';
           } else {
                /*
                 * Sizes calculation:
                 *      Mobile - full width images - 100vw
                 *      If between 640px and 970px - image width is column-th fraction of viewport
                 *      Above 970px - Image width is column-th fracion of 970px
                 *
                 * TODO: handle full-width rows, maybe handle smaller image sizes in columned rows
                 */
                $sizes_attr = '(min-width: 640px) ' . ceil( ( 1 / $cols ) * 100 ) . 'vw, (min-width: 970px) ' . ceil( 970 / $cols ) . 'px, 100vw';

                 
                $image_thumb_name=$thumb_name.'1';
                 
                $target = wp_get_attachment_image_src( $image, 'full' );
                $source = wp_get_attachment_image_src( $image, $image_thumb_name);
                $max_height=($source[2]>$max_height)?$source[2]:$max_height;

                $post_data = get_post( $image );
    
                $caption_html = ( !empty( $post_data->post_excerpt ) ) ? '<div class="image_gallery_element__item__caption">' . $post_data->post_excerpt . '</div>' : '';

                $show_image=wp_get_attachment_image( $image, $image_thumb_name, false, array('sizes'=>$sizes_attr) );
                $href=$target[0];
                               
            }
            
            // colored hover with image
            
            if(isset( $element[ 'style' ][ 'hover' ] ) && $element['style']['hover']=='overlay_icon') {
                $show_image.='<div class="image_gallery_overlay_icon_container">'.file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/zoom-in.svg", true).'</div>';
            }
            
            $output .= sprintf( '<a href="%s" class="image_gallery_element__item open_lightbox %s" rel="%s">
                                        <div class="image_gallery_element_item_thumb %s">'.$show_image.'</div>
                                        %s
                                    </a>',
                    $href, $image_row_classes, $rel_attr, $image_item_class, $caption_html );
        };
        

        $output .= '<div class="cms_clear"></div></div>';
        $row_num++;
    };

    if(isset($element['style']['use_slider'])) {
        $output .= '</div>';  //slider end
        $output .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
        $output .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
        if($added) {
            $output .= "<script>
            jQuery(function() {
                function imageLoaded() {
                   counter--; 
                   if( counter === 0 ) {
                        jQuery('".$css_id." .miocarousel').MioCarousel({});
                   }
                }
                var images = jQuery('".$css_id." img');
                var counter = images.length; 

                images.each(function() {
                    if( this.complete ) {
                        imageLoaded.call( this );
                    } else {
                        jQuery(this).one('load', imageLoaded);
                    }
                });
            });
            </script>";
        }
    }
    
    $output .= '</div><div class="cms_clear"></div>';
    }
    return $output;
}

function ve_element_image_text($element, $css_id) { 
    global $vePage; 
    if($element['content'] || $element['style']['title'] || $element['style']['image']) {
    
        $visual_style=(isset($element['style']['visual_style']))? $element['style']['visual_style']:1;
        $text_align=(isset($element['style']['text-align']))? $element['style']['text-align']:'left';
    
        // new id in images compatibility (temporary)
        if(isset($element['style']['image']) && !is_array($element['style']['image']))
            $element['style']['image']=array('image'=>$element['style']['image']);
            
        if(isset($element['config']['max_width'])) $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content");
        else $style='';   
            
        $styles=array(
            array(
                    'styles'=>array('font'=>$element['style']['font']),
                    'element'=>$css_id.' .el_it_text h3',
            ),
            array(
                    'styles'=>array('font'=>isset($element['style']['font_text'])?$element['style']['font_text']:''),
                    'element'=>$css_id.' .el_it_text .entry_content',
            ),
        );
        
        if($visual_style==2) {
            $styles[]=array(
                'styles'=>array('background-color'=>$element['style']['background_color']),
                'element'=>$css_id.' .in_element_image_text',
            );
        } 
        
        $content=$vePage->print_styles_array($styles);
        
        $content.='<div class="in_element_content in_element_image_text in_element_image_text_'.$visual_style.' in_element_image_text_is_'.$element['style']['style'].'" '.$style.'>';

        if($element['style']['image']['image']) $img=$vePage->generate_image($element['style']['image']);
        else $img='<div class="ve_empty_image_container"><span></span></div>';
        
        if($element['style']['style']=='1' || $element['style']['style']=='two') {
            $el_cols='two';
            $el_text_cols='two';
        }
        else if($element['style']['style']=='2' || $element['style']['style']=='three') {
            $el_cols='three';
            $el_text_cols='twothree';
        }
        else if($element['style']['style']=='3' || $element['style']['style']=='four') {
            $el_cols='four';
            $el_text_cols='threefour';
        }
        else if($element['style']['style']=='4' || $element['style']['style']=='five') {
            $el_cols='five';
            $el_text_cols='fourfive';
        }
        else if($element['style']['style']=='twothree') {
            $el_cols='twothree';
            $el_text_cols='three';
        }
        
        $col_class=($visual_style==2)? 'fullcol-'.$el_cols : 'col-'.$el_cols;
        $col_text_class=($visual_style==2)? 'fullcol-'.$el_text_cols : 'col-'.$el_text_cols;
        
        // image
        $col_img='<div class="'.$col_class.' el_it_image">'.$img.'</div>';
        
        // content
        $col_text='<div class="el_it_text '.$col_text_class.' '.(($element['style']['align']=='left')?'el_it_text_second':'el_it_text_first').' ve_'.$text_align.'">';
        
            if($element['style']['title']) $col_text.='<h3>'.stripslashes($element['style']['title']).'</h3>';
        
            if($element['content']) $col_text.='<div class="entry_content">'.do_shortcode(stripslashes($element['content'])).'</div>';
        
            if(isset($element['style']['button_link'])) {
                $link=$vePage->create_link($element['style']['button_link']);
                if($link) {
                    $but_set=array(
                        'style'=>$element['style']['button'],
                        'link'=>$element['style']['button_link'],
                        'target'=>$element['style']['button_link'],
                        'text'=>$element['style']['button_text'],
                    );
                    $col_text.=$vePage->create_button($but_set, $css_id.' .ve_content_button','ve_content_button');
                }
            }
            
        $col_text.='</div>';
        
        if($element['style']['align']=='left') $content.=$col_img.$col_text;
        else $content.=$col_text.$col_img;
        $content.='</div>';
    } else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Není zadán žádný obsah.','cms_ve').'</div>';
    else $content="";
    return $content;
}

function ve_element_wpcomments($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage, $mw_comment_set; 
    $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content"):"";
    
    if(!isset($element['style']['button'])) $element['style']['button']=array('style'=>'x');  

    $content=$vePage->create_button_styles($element['style']['button'], $css_id.' .ve_content_button');
    
    $content.='<div class="in_element_content element_comment_'.$element['style']['style'].'" '.$instyle.'>';
  
    if($added) {
      global $post;
      $post=get_post($post_id);
      query_posts('p='.$post_id);
    }
    
    $mw_comment_set=array(
      'button_style'=>$element['style']['button']['style'],
      'button_hover'=>(isset($element['style']['button']['hover_effect']))? $element['style']['button']['hover_effect'] : 'lighter',
      'comment_style'=>$element['style']['style']
    );
    
    ob_start();
    comments_template('/skin/comments.php');   
    $comments = ob_get_contents();
    ob_end_clean();
    $content.=$comments;
    //$content.=cms_wp_comments($post_id, $element['style']);
    $content.='</div>';
    return $content;
}

function ve_element_seform($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage, $apiConnection; 
    $content='';
    $custom=false;
    
    if($element['style']['form-style']==2) {
        $element['style']['button']['height']=$element['style']['button']['font']['font-size']*2.2;
        $element['style']['button']['height_padding']=0.5;
    } 
    
    $instyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content"):"";

    // back compatibility (temporary)
    if(!is_array($element['content'])) {
        $old_content=$element['content'];
        $element['content']=array();
        $element['content']['id']=$old_content;  
    }  
    if(!isset($element['content']['api'])) $element['content']['api']='se';
    // end temporary
    
    if((!isset($element['style']['type']) || $element['style']['type']=="smartemailing") && isset($element['content']['id']) && $element['content']['id']) { 
        wp_enqueue_script('ve_se_email_corrector');
        $form=$apiConnection->get_form($element['content'], $vePage->edit_mode);
        if ($form != false)
            $content.=$apiConnection->print_form($element['content']['api'],$element,$form, $css_id, $added);
        else
            $content.='<div>'.__('Při získávání formuláře došlo k chybě.', 'cme_ve').'</div>';

    } else if((!isset($element['style']['type']) || $element['style']['type']=="html") && isset($element['style']['html']) && $element['style']['html']) {

       $content.=stripslashes($element['style']['html']); 
        
    } else if(isset($element['style']['type']) && (($element['style']['type']=="custom" && isset($element['style']['custom_form'])) || ($element['style']['type']=="custom_url" && isset($element['style']['custom_form_url'])))) {
        $form = array();
        $custom=true;
        
        if($element['style']['type']=='custom'){
            if(isset($_GET['custom_form_error'])) {
                $content.='<div class="mw_input_error_text">';
                switch ($_GET['custom_form_error']) {
                    case 'norequired':
                        $content.=__('Nejsou vyplněny všechny povinné pole.','cms_ve');
                        break;
                    case 'hidden_field':
                        $content.=__('Neprošels ochranou proti botům.','cms_ve');
                        break;
                    case 'time':
                        $content.=__('Formulář byl odeslán příliš rychle (ochrana proti botům).','cms_ve');
                        break;
                }
                $content.='</div>';
            }
          
            $use_form=$element['style']['custom_form'];
            $form['url']='';
            $thx_url=$vePage->create_link($element['style']['thx_url'],false);
            
            $form['fields']['customform_subject']=array(
                    'label'=>'',
                    'fieldname'=>'ve_customform_subject',
                    'defaultfield'=>'',
                    'content'=>$element['style']['subject'],
                    'customfield_type'=>'hidden',   
                    'required'=>'',             
            );
            $form['fields']['customform_url']=array(
                    'label'=>'',
                    'fieldname'=>'ve_customform_url',
                    'defaultfield'=>'',
                    'content'=>(($thx_url)?$thx_url:get_permalink( $post_id )),
                    'customfield_type'=>'hidden', 
                    'required'=>'',               
            );
            $form['fields']['customform_structure']=array(
                    'label'=>'',
                    'fieldname'=>'ve_customform_structure',
                    'defaultfield'=>'',
                    'content'=>base64_encode(serialize(array('form'=>$element['style']['custom_form'], 'email'=>$element['style']['email']))),
                    'customfield_type'=>'hidden', 
                    'required'=>'',               
            );
            $form['fields']['customform_email']=array(
                    'label'=>'',
                    'fieldname'=>'ve_customform_email',
                    'defaultfield'=>'',
                    'content'=>'',
                    'customfield_type'=>'antispam', 
                    'required'=>'',               
            );
            $form['fields']['customform_time_sended']=array(
                    'label'=>'',
                    'fieldname'=>'ve_sended_time',
                    'defaultfield'=>'',
                    'content'=>current_time( 'timestamp' ),
                    'customfield_type'=>'hidden', 
                    'required'=>'',               
            );
            
            
        } else {
            $use_form=$element['style']['custom_form_url'];
            $form['url']=$element['style']['url'];
        }
        $form['submit']=__('Odeslat', 'cms_ve');
        foreach($use_form as $key=>$field) {
            $label=$field['title'];
            if($field['type']=="agree" && $field['agree_link'] && $field['agree_link_text']) {
                $label.=' <a href="'.$field['agree_link'].'">'.$field['agree_link_text'].'</a>';
            }
            $form['fields']['field_'.$key]=array(
                'label'=>$label,
                'fieldname'=>(isset($field['name']) && $field['name'])?$field['name']:'ve_custom_form_field_'.$key,
                'defaultfield'=>'',
                'content'=>$field['content'],
                'required'=>isset($field['required'])?'1':'',
                'customfield_type'=>$field['type'],             
            ); 
            if(isset($field['email'])) $form['fields']['field_'.$key]['email']=1;
            if(isset($use_form[$key]['subitems'])) {
                foreach($use_form[$key]['subitems'] as $f_key=>$f_val) {
                  if($f_val['text']) {
                    $form['fields']['field_'.$key]['options']['item'][$f_key]['id']=$f_val['text'];
                    $form['fields']['field_'.$key]['options']['item'][$f_key]['name']=$f_val['text'];
                    $form['fields']['field_'.$key]['options']['item'][$f_key]['order']=$f_key;
                  }
                }
            } else $form['fields']['field_'.$key]['options']=array(); 
        } 
                
    } else {   
    // default for templates
        $custom=true;
        
        $form = array();
        $form['url']='';
        $form['submit']=__( 'Odeslat', 'cms_ve' );
        $form['fields']['df_emailaddress']=array(
            'label'=>__('Vložte svůj e-mail','cms_ve'),
            'fieldname'=>'cms_email',
            'defaultfield'=>'',
            'required'=>'',
        );  
        if($vePage->edit_mode) $content.='<div class="cms_error_box admin_feature">'.__('Formulář není funkční. Propojte jej s jedním z podporovaných e-mail marketingových nástrojů a vyberte formulář, který chcete použít, nebo vytvořte svůj vlastní formulář.','cms_ve').'</div>';      
    }
    
    // print custom form
    if($custom) $content.=$vePage->print_form($element,$form, $css_id);

    
    
    
    if(isset($element['style']['popup'])) {        
        wp_enqueue_script( 've_lightbox_script' );
        wp_enqueue_style( 've_lightbox_style' );
        
        $but_content='';
        
        if(!isset($element['style']['popup_type'])) {
            if(isset($element['style']['text_link'])) $type='link';
            else $type='button';
        }
        else $type=$element['style']['popup_type'];
        
        if($type=='link') {
            $popbutstyle=$vePage->print_styles(array('font'=>$element['style']['link_font']),$css_id." .open_lightbox_form");
            $but_class='';
            $but_content='<a '.$popbutstyle.' class="open_lightbox_form '.$but_class.'" href="#">'.(isset($element['style']['link_text'])? $element['style']['link_text']:$element['style']['popup_text']).'</a>';
        } else if($type=='image') {
            $but_content='<a class="open_lightbox_form element_image element_image_'.(isset($element['style']['align'])?$element['style']['align']:'center').'" href="#"><img src="'.$vePage->get_image_url($element['style']['image']).'" alt="" /></a>';
        } else {

            $content.=$vePage->create_button_styles($element['style']['popupbutton'], $css_id." .open_lightbox_form");

            $but_class='ve_content_button ve_content_button_'.$element['style']['popupbutton']['style'].' ve_content_button_'.(isset($element['style']['align'])?$element['style']['align']:'center');
            if(isset($element['style']['popupbutton']['hover_effect']) && $element['style']['popupbutton']['hover_effect']) $but_class.=' ve_cb_hover_'.$element['style']['popupbutton']['hover_effect'];
            $but_content='<a  class="open_lightbox_form '.$but_class.'" href="#">'.$element['style']['popup_text'].'</a>';
        }
        $content='
        <script>
        jQuery(document).ready(function($) {         
            $("'.$css_id.' .open_lightbox_form").colorbox({inline:true,href:"'.$css_id.'_form",width:"90%",maxWidth:"600px"});
        });
        </script>
        <div class="ve_center">'.$but_content.'<div class="cms_clear"></div></div>
        <div style="display: none;">
            <div id="'.str_replace('#','',$css_id).'_form" class="popup_form_container">
                '.($element['style']['popup_title']? '<p class="popup_form_title title_element_container">'.$element['style']['popup_title'].'</p>':'').'
                '.((isset($element['style']['textinpopup']) && $element['style']['textinpopup'])? '<p class="popup_form_text">'.nl2br(stripslashes($element['style']['textinpopup'])).'</p>':'').'
                '.$content.'
            </div>
        </div>';
        
        $content.=$vePage->create_button_styles($element['style']['button'], $css_id."_form .ve_form_button_row button");
    } else $content.=$vePage->create_button_styles($element['style']['button'], $css_id." .ve_form_button_row button");
    
    $content='<div class="in_element_content" '.$instyle.'>'.$content.'</div>';
    
    return $content;
}

function ve_element_contactform($element, $css_id) { 
    global $vePage; 
    //print_r($element['style']);
    if(isset($element['config']['max_width'])) $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .ve_contact_form"); 
    else $style='';
    
    if(isset($element['config']['max_width'])) $element['style']['max-width']=$element['config']['max_width'];
    
    $form_style=isset($element['style']['form-appearance'])? $element['style']['form-appearance']:3;
    
    $content=$vePage->print_styles_array(array(        
        array(
            'styles'=>array(
                'font'=>$element['style']['form-font'],
                'background-color'=>$element['style']['background'],
            ),
            'element'=>$css_id." .ve_contact_form_row input, ".$css_id." .ve_contact_form_row textarea",
        ),
    ));        
    $content.=$vePage->create_button_styles($element['style']['button'], $css_id." .ve_contact_form_buttonrow button");
    $but_class=(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect'])? ' ve_cb_hover_'.$element['style']['button']['hover_effect']:'';
    
    
    
    $content.='<form action="" method="post" class="ve_check_contact_form in_element_content ve_content_form ve_contact_form ve_contact_form_'.$form_style.' ve_form_input_style_'.$element['style']['form-style'].'" '.$style.'>
        <div class="cms_nodisp"><input type="text" name="send_email" value="" /></div>
        <div class="cms_nodisp"><input type="text" name="contact_sended" value="'.current_time( 'timestamp' ).'" /></div>
        <div class="ve_contact_form_row ve_contact_form_row_half"><input type="text" name="contact_name" placeholder="'.__('Jméno','cms_ve').'" /></div>
        <div class="ve_contact_form_row ve_contact_form_row_half_r"><input class="ve_form_required ve_form_email" type="text" name="contact_email" placeholder="'.__('E-mail (povinný)','cms_ve').'" /></div>';
    if(!isset($element['style']['hide']['phone'])) $content.='<div class="ve_contact_form_row cms_clear"><input type="text" name="contact_phone" placeholder="'.__('Telefon','cms_ve').'" /></div>';
    $content.='<div class="ve_contact_form_row cms_clear"><textarea class="ve_form_required" name="contact_text" rows="4" placeholder="'.__('Zpráva','cms_ve').'"></textarea></div>';
    
    $gdpr=get_option('web_option_gdpr');
    if($gdpr && $gdpr['contact_form_info']) {
        $content.='<div class="mw_field_gdpr_accept">';
        $content.='<input type="hidden" name="gdpr_accept" value="'.$gdpr['contact_form_info'].'" />';
        $content.=$gdpr['contact_form_info'];
        if($gdpr['contact_form_link_text'] && isset($gdpr['gdpr_url']) && $gdpr['gdpr_url']) $content.=' <a href="'.$vePage->create_link($gdpr['gdpr_url']).'" target="_blank">'.$gdpr['contact_form_link_text'].'</a>';
        $content.='</div>';
    }
    
    $content.='<div class="ve_contact_form_buttonrow"><button class="ve_content_button ve_content_button_'.$element['style']['button']['style'].$but_class.'" type="submit" >'.$element['style']['button_text'].'<span></span></button></div>    
        <input type="hidden" name="data" value="'.base64_encode(serialize(array('email'=>$element['style']['email']))).'" />
    </form>';

    return $content;
}

function ve_element_menu($element, $css_id, $post_id) {
    global $vePage;
  
    $content='<div class="menu_element_type'.$element['style']['style'].'">';
    if(isset($element['style']['font'])) {
        if(isset($element['style']['font']['color'])) $element['style']['font']['color'].=' !important';
        $content.=$vePage->print_styles(array('font'=>$element['style']['font']),$css_id." li a",'online');
    }
    else $content.=$vePage->print_styles(array('font'=>array('color'=>'#444 !important')),$css_id." li a",'online');
    
    if(isset($element['style']['title_font'])) $content.=$vePage->print_styles(array('font'=>$element['style']['title_font']),$css_id." .menu_element_title",'online');
     
    if($element['style']['style']==3) $content.=$vePage->print_styles(array('background-color'=>$element['style']['color-active'],'color'=>'#fff !important'),$css_id.' li > a:hover,'.$css_id.' li.current_page_item > a','online');
    else if($element['style']['style']!=5) $content.=$vePage->print_styles(array('color'=>$element['style']['color-active'].'  !important'),$css_id.' li > a:hover, #content-container '.$css_id.' li.current_page_item > a','online');
    if($element['style']['title']) $content.='<div class="menu_element_title">'.stripslashes($element['style']['title']).'</div>';
    else if($element['style']['style']==5) $content.='<div class="menu_top"></div>';
    if($element['style']['type']=='subpage') {
        $parent=($element['style']['page'])? $element['style']['page']: $post_id;
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order',
            'echo' => 0,
            'title_li'=>'',
            'child_of' => $parent,
        );   
        $content.='<ul class="menu">'.wp_list_pages($args).'</ul>';
        /* 
        $pages = get_pages($args);
        if(!empty($pages)) {
            $content.='<ul>';
            foreach ( $pages as $page ) { 
                $content.='<a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a></li>';
            }
            $content.='</ul>';
        }
        
        else if($vePage->edit_mode) $content.='<div class="cms_info_box">Tato nebo vybraná stránka neobsahuje žádné podstránky!</div>';
        */
    } else {
        if(isset($element['style']['menu']) && $element['style']['menu']) $content.=wp_nav_menu( array( 'menu' => $element['style']['menu'],'echo'=>false ) );
        else if($vePage->edit_mode) $content.='<div class="cms_error_box admin_feature">'.__('Není vybráno žádné menu!','cms_ve').'</div>';
    }         
    if($element['style']['style']==5) $content.='<div class="menu_bottom"></div>';
    $content.='</div>';
    return $content;  
}

function ve_element_twocols($element, $css_id, $post_id, $edit_mode) {
    $css_id=str_replace("#","",$css_id);
    global $vePage;
    
    if(isset($element['config']['max_width'])) $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .ve_contact_form"); 
    else $style='';
    
    $content='<div class="subcol-replace in_element_twocols" '.$style.'>';
    // col 1
    $content.='<div class="subcol subcol-first subcol-two sortable-col">';
    if(isset($element['content'][0]) && is_array($element['content'][0])) {
        $i=0;
        foreach($element['content'][0] as $subelement) {
            $content.=$vePage->generate_element($subelement, $css_id."_0_".$i,$post_id, $edit_mode);
            $i++;
        } 
    }
    if($edit_mode) $content.=$vePage->generate_new_element_but(1);
    // col 2
    $content.='</div><div class="subcol subcol-last subcol-two sortable-col">';
    if(isset($element['content'][1]) && is_array($element['content'][1])) {
    $i=0;
    foreach($element['content'][1] as $subelement) {
        $content.=$vePage->generate_element($subelement, $css_id."_1_".$i,$post_id, $edit_mode);
        $i++;
    } 
    }
    if($edit_mode) $content.=$vePage->generate_new_element_but(1);
    $content.='</div><div class="cms_clear"></div></div>';
    return $content;
  
}

function ve_element_box($element, $css_id, $post_id, $edit_mode) {
    global $vePage;
    
    if(isset($element['style']['background-color']))
        $element['style']['background_color']=array('color1'=>$element['style']['background-color']);
    
    $style=$vePage->print_styles(array(
        'background_color'=>(isset($element['style']['background_color']))? $element['style']['background_color']:'',
        'max-width'=>(isset($element['config']['max_width']))? $element['config']['max_width']:'',
        'background_image'=>(isset($element['style']['background_image']))? $element['style']['background_image']:'',
        'border'=>$element['style']['border'],
        'corner'=>$element['style']['corner'],   
        'box-shadow'=>(isset($element['style']['box-shadow']))? $element['style']['box-shadow']:'', 
    ),$css_id." .ve_content_block");

    
    
    $styles=array(
        array(
            'styles'=>array('font'=>$element['style']['font'],'padding'=>(isset($element['style']['padding']))? $element['style']['padding']:''),
            'element'=>$css_id.' .ve_content_block_content',
        ),
        array(
            'styles'=>(isset($element['style']['link-color']))? array('color'=>$element['style']['link-color']) : '',
            'element'=>$css_id.' a',
        ),
        array(
            'styles'=>(isset($element['style']['font']['color']))? array('color'=>$element['style']['font']['color']) : '',
            'element'=>$css_id.' h2,'.$css_id.' h1,'.$css_id.' h3,'.$css_id.' h4,'.$css_id.' h5,'.$css_id.' h6,'.$css_id.' .title_element_container',
        ),
        array(
            'styles'=>(isset($element['style']['title']) && $element['style']['title'])? array('font'=>$element['style']['title-font'], 'background_color'=>$element['style']['title_bg'], 'border-bottom'=>(isset($element['style']['title_border'])? $element['style']['title_border']:'')) : '',
            'element'=>$css_id.' h2.ve_content_block_title',
        ),
    );
    
    $class='';
    // color cover for image  
    if (!(isset($element['style']['background_setting']) && $element['style']['background_setting']!='image') && isset($element['style']['background_image']['image']) && $element['style']['background_image']['image'] && isset($element['style']['background_image']['cover']) && isset($element['style']['background_image']['color_filter'])) {
        $styles[] = array(
            'styles' => array('background-color' => $element['style']['background_image']['overlay_color'],'opacity'=>$element['style']['background_image']['overlay_transparency']),
            'element' => $css_id.' .ve_colored_background:before',
        );
        $class='ve_colored_background';   
    }
    
    $content='';  
    $content.='<div class="subcol subcol-first ve_content_block sortable-col '.$class.'" '.$style.'>';
    $content.=$vePage->print_styles_array($styles);
    
    if(isset($element['style']['title']) && $element['style']['title']) 
        $content.='<h2 class="ve_content_block_title">'.stripslashes($element['style']['title']).'</h2>';
    
    $content.='<div class="ve_content_block_content subcol-replace">';
    if(isset($element['content'][0]) && is_array($element['content'][0])) {

        $css_id=str_replace("#","",$css_id);
        $i=0;
        foreach($element['content'][0] as $subelement) {
            $content.=$vePage->generate_element($subelement, $css_id."_0_".$i,$post_id, $edit_mode);
            $i++;
        } 
    }
    
    if($edit_mode) $content.=$vePage->generate_new_element_but(1);
    $content.='</div>';
    $content.='</div>';

    return $content;  
}

// Countdown

function ve_element_countdown($element, $css_id, $post_id) {
    global $vePage;
    
    wp_enqueue_script('ve_countdown_script');
    wp_enqueue_style('ve_countdown_style');
    
    $content="";
    
    $content=$vePage->print_styles_array(array(
        array(
            'styles'=>array(
                'font'=>array(
                    'weight'=>$element['style']['font']['weight'],
                    'font-size'=>$element['style']['font']['font-size'],
                    'font-family'=>$element['style']['font']['font-family'],
                ),
            ),
            'element'=>$css_id.' .ve_countdown',
        ),
        array(
            'styles'=>array(
                'color'=>isset($element['style']['font']['color'])?$element['style']['font']['color']:'',
            ),
            'element'=>$css_id.' .ve_countdown .position',
        ),
        array(
            'styles'=>array(
                'color'=>(isset($element['style']['font']['color']) && ($element['style']['style']==3 || $element['style']['style']==4 || $element['style']['style']==5 || $element['style']['style']==7))? $element['style']['font']['color']:'',
            ),
            'element'=>$css_id.' .ve_countdown .position_title,'.$css_id.' .ve_countdown .position_before',
        ),
        array(
            'styles'=>array(
                'color'=>(isset($element['style']['font-text']['color']) && $element['style']['font-text']['color'])? $element['style']['font-text']['color']:'',
            ),
            'element'=>$css_id.' .ve_countdown .position_title,'.$css_id.' .ve_countdown .position_before',
        ),
        array(
            'styles'=>array(
                'color'=>($element['style']['style']==5)? $element['style']['font']['color']:$element['style']['background-color'],
            ),
            'element'=>$css_id.' .ve_countdown .count_time:after',
        ),
    ));
       
    
    if(($element['style']['style']==1 || $element['style']['style']==2) && $element['style']['background-color']) $content.=$vePage->print_styles(array('background-color'=>$element['style']['background-color'],'background-image'=>'none'),$css_id.' .ve_countdown .digit','online');
    else if($element['style']['background-color'] && $element['style']['style']!=7 && $element['style']['style']!=5) $content.=$vePage->print_styles(array('background-color'=>$element['style']['background-color']),$css_id.' .ve_countdown .count_time','online');
    
    
    if($element['content']['date']=='00000') {
        $time=strtotime('today midnight');
        $h=23;
        $m=59;
    }
    else {
        $time=strtotime($element['content']['date']);
        $h=$element['content']['hour'];
        $m=$element['content']['minute'];
    }
    
    if((isset($element['style']['evergreen_days']) && $element['style']['evergreen_days']!="") ||
      (isset($element['style']['evergreen_minutes']) && $element['style']['evergreen_minutes']!="") ||
      (isset($element['style']['evergreen_hours']) && $element['style']['evergreen_hours']!="")) {
          
        if(isset($_COOKIE['mioweb_campaign_access'])) {
            $campaign_id = get_post_meta( $post_id, 'mioweb_campaign',true );
            $access=unserialize(stripslashes($_COOKIE['mioweb_campaign_access']));
            $time=$access['time'][$campaign_id['campaign']];
        } else $time=current_time( 'timestamp' );

        $time+=($element['style']['evergreen_days']*3600*24);
        $h=0;
        $m=0; 
        
        if(isset($element['style']['evergreen_hours']) && $element['style']['evergreen_hours']!="")
            $h=$element['style']['evergreen_hours'];
            
        if(isset($element['style']['evergreen_minutes']) && $element['style']['evergreen_minutes']!="")
            $m=$element['style']['evergreen_minutes'];

        if(isset($element['style']['evergreen_start']) && $element['style']['evergreen_start']=='enter') {
            $time+=($h*3600+$m*60);  
            $h=Date('H',$time);
            $m=Date('i',$time);
        } else if(isset($element['style']['evergreen_start']) && $element['style']['evergreen_start']=='start') {
            $time=strtotime(Date('d.m.Y',$time));
            $time+=($h*3600+$m*60);  

            if(current_time('timestamp') > $time) {
                $time+=(24*3600); 
            }
        } else {
            $time+=(23*3600+59*60); 
        }
               
    }
    
    if(isset($element['style']['redirect'])) $redirect_link=$vePage->create_link($element['style']['redirect']);
    else $redirect_link='';
    
    $content.='<div class="ve_countdown ve_countdown_'.$element['style']['style'].'"></div>'.
    '<script>'.
    'jQuery(function(){'.
        'var ts = new Date('.Date('Y',$time).', '.(Date('m',$time)-1).', '.Date('d',$time).', '.$h.', '.$m.');'.
  
        'jQuery("'.$css_id.' .ve_countdown").countdown({'.
            'timestamp : ts,'.
            ((isset($element['style']['text_before']) && $element['style']['text_before'] && $element['style']['style']==7)? 'text_before : "'.$element['style']['text_before'].'",':'' ).
            'callback : function(days, hours, minutes, seconds){
                if(days==0 && hours==0 && minutes==0 && seconds==0){
                	'.(($redirect_link && !$vePage->edit_mode)?'window.location.replace("'.$redirect_link.'");':'').' 
                }
            
            }'.
        '});'.
    '});'.
    '</script>';
    return $content;
}

// Testimonials

function ve_element_testimonials($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;
    $class='';
    $carousel_set='';
    $element_style='';
    $slider_style='';
    
    if(isset($element['style']['testimonials'])){
        
        
        if(isset($element['style']['use_slider'])) {
            wp_enqueue_script( 've_miocarousel_script' );
            wp_enqueue_style( 've_miocarousel_style' );
            if($vePage->is_mobile) $element['style']['cols']='one';
            
            $class.=' miocarousel miocarousel_style_1';
            if($element['style']['color_scheme']) $class.=' miocarousel_'.$element['style']['color_scheme'];
            if(isset($element['style']['off_autoplay'])) $carousel_set.=' data-autoplay="0"';
            if($element['style']['delay']) $carousel_set.=' data-duration="'.$element['style']['delay'].'"';
            if($element['style']['speed']) $carousel_set.=' data-speed="'.$element['style']['speed'].'"';
            if($element['style']['animation'] && $element['style']['animation']!='fade') $carousel_set.=' data-animation="'.$element['style']['animation'].'"';
            
            $slider_style=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .miocarousel-inner"):"";
        } else {
            $element_style=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content"):"";
        }   
        
        $content=$vePage->print_styles_array(array(
            array(
                'styles'=>array(
                    'font'=>$element['style']['font'],
                ),
                'element'=>$css_id." blockquote p",
            ),
            array(
                'styles'=>array(
                    'font'=>(isset($element['style']['font-author']))? $element['style']['font-author']:'',
                    'opacity'=>(isset($element['style']['font-author']) && $element['style']['font-author']['color'])? 100:'',
                ),
                'element'=>$css_id." .ve_content_testimonial_name",
            ),
        ));
        $content.='<div class="in_element_content '.$class.' ve_element_testimonial_style_'.$element['style']['style'].'" '.$element_style.' '.$carousel_set.'>';
        
        if(isset($element['style']['use_slider']))
            $content .= '<div class="miocarousel-inner" '.$slider_style.'>';
        
        switch ($element['style']['cols']) {
            case 'one':
                $cols=1;
                break;
            case 'two':
                $cols=2;
                break;
            case 'three':
                $cols=3;
                break;
            case 'four':
                $cols=4;
                break;
        }
            
        $el_rows = array_chunk( $element['style']['testimonials'], $cols );
            
        $i=1;
        $row_num=1;
            
        foreach( $el_rows as $row ){
  
                if(isset($element['style']['use_slider'])) {
                    $row_class=' slide';
                    if($row_num==1) $row_class.=' active';
                } else {
                    $row_class='testimonial_row';
                    if($row_num==1) $row_class.=' testimonial_row_first';
                }
        
                $content .= '<div class="'.$row_class.'">';      
        
                foreach( $row as $testimonial ) {
                
                    // new id in images compatibility (temporary)
                    if(isset($testimonial['image']) && !is_array($testimonial['image']))
                        $testimonial['image']=array('image'=>$testimonial['image']);
                    // end temporary
            
                    $content.='<blockquote class="elcol col-'.$element['style']['cols'].' '.(($i==1)?'col-first':'').' ve_content_testimonial ve_content_testimonial_'.$element['style']['style'].' '.(($testimonial['image']['image'])? 've_content_testimonial_'.$element['style']['style'].'_wimg':'').'">';       
                    $content.='<p>'.stripslashes($testimonial['text']).'<span></span></p>';
                    $content.='<div class="ve_content_testimonial_author">';                                   
                    
                    if($testimonial['image']['image']) {
                        $image=(substr($testimonial['image']['image'], 0, 4)=='http')?$testimonial['image']['image']:home_url().$testimonial['image']['image'];
                        $content.='<div class="ve_content_testimonial_img_container"><img src="'.$image.'" alt="" /></div>';
                    }
                    
                    $content.='<div class="ve_content_testimonial_name"><span class="ve_content_testimonial_author_name">'.stripslashes($testimonial['name']).'</span><span class="ve_content_testimonial_company">'.stripslashes($testimonial['company']).'</span></div><div class="cms_clear"></div></div>';
                    $content.='</blockquote>';
                    if($i==$cols) {
                           $content.='<div class="cms_clear"></div>'; 
                           $i=1;
                        }
                        else $i++;
                }
                
                $content.='<div class="cms_clear"></div></div>';
                
                $row_num++;
        }
        
        if(isset($element['style']['use_slider'])) {
            $content .= '</div>';  //slider end
            $content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
            $content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
            if($added) {
            $content .= "";
            }
        }
        
        $content .= '</div><div class="cms_clear"></div>';
        
        
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Reference jsou prázdné. Zadejte nějakou referenci nebo element smažte.','cms_ve').'</div>';
    else $content="";
    return $content;
}

// Features

function ve_element_features($element, $css_id, $post_id) { 
    global $vePage; 
    wp_enqueue_style('font_icon_style');

    $content=$vePage->print_styles_array(array(
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>"#wrapper ".$css_id." h3, #cboxWrapper ".$css_id." h3",
            ),
            array(
                'styles'=>isset($element['style']['font_text'])? array('font'=>$element['style']['font_text']):"",
                'element'=>"#wrapper ".$css_id." p, #cboxWrapper ".$css_id." p",
            ),
        ));
    
    if(isset($element['config']['max_width']))
        $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_features_element");
    else $style='';
    
    
    
    $content.='<div class="in_element_content in_features_element in_features_element_'.$element['style']['style'].'" '.$style.'>';                                          
    if(!empty($element['style']['features'])) {
    
        switch ($element['style']['cols']) {
        case 'one':
            $cols=1;
            break;
        case 'two':
            $cols=2;
            break;
        case 'three':
            $cols=3;
            break;
        case 'four':
            $cols=4;
            break;
        case 'five':
            $cols=5;
            break;
        }
        
        $i=1;
        $j=1;
        
        $bg_color=($element['style']['style']!=3 && $element['style']['style']!=1)? $element['style']['background-color'] : '';
        
        foreach ( $element['style']['features'] as $feature) { 
        
                if(!isset($feature['icon']['tab']) || $feature['icon']['tab']=='icon') $img='icon';
                else $img='image';
        
                if($element['style']['style']==3 && $img=='icon')
                    $cstyle=$vePage->print_styles(array('min-height'=>$feature['icon']['size'], 'padding_left'=>$feature['icon']['size']+30),$css_id." .in_features_element .elcol");
                else $cstyle='';
                
                $content.='<div class="elcol feature_col_'.$img.' col-'.$element['style']['cols'].' '.(($i==1)?'col-first':'').' '.(($j>$cols)?'feature_row':'').'" '.$cstyle.'>';
                
                $link=(isset($feature['link']))? $vePage->create_link($feature['link']):'';
                
                if($link) {
                    $target=(isset($feature['link']['target']) && $feature['link']['target']==1)? 'target="_blank"' : "";  
                    $link='<a href="'.$link.'" '.$target.'>';
                    $link_close='</a>';
                }
                else {
                    $target='';
                    $link='';
                    $link_close='';
                }
                

                if($img=='icon')
                    $content.=$link.'<i style="font-size: '.$feature['icon']['size'].'px; '.(($feature['icon']['color'])? 'color: '.$feature['icon']['color'].';':'').' '.(($bg_color)? 'background-color: '.$bg_color:'').'" class="icon-'.$feature['icon']['icon'].'"></i>'.$link_close;
                else if($feature['icon']['image']) {
                    // new id in images compatibility (temporary)
                    if(isset($feature['icon']['image']) && !is_array($feature['icon']['image']))
                        $feature['icon']['image']=array('image'=>$feature['icon']['image']);
                        /*
                        if(isset($feature['icon']['image']['imageid'])) {
                            $image_attr=wp_get_attachment_metadata( $feature['icon']['image']['imageid'] );
                            $image_style=$vePage->print_styles(array('width'=>$image_attr['width']),$css_id." .in_features_element")
                        }
                        */
                        //print_r($feature['icon']['image']);
                    $content.='<div class="feature_image">'.$link.$vePage->generate_image($feature['icon']['image']).$link_close.'</div>';
                } 
                $content.='<div class="feature_text">';    
                if($feature['title']) $content.=$link.'<h3>'.stripslashes($feature['title']).'</h3>'.$link_close;
                if($feature['text']) $content.='<p>'.stripslashes($feature['text']).'</p>';
                if(isset($element['style']['show_button'])) {
                    $but_set=array(
                        'style'=>$element['style']['button'],
                        'show'=>isset($element['style']['show'])? $element['style']['show']:'',
                        'link'=>$feature['link'],
                        'text'=>$feature['button_text'],
                        'align'=>'center'
                    );
                    $content.=$vePage->create_button($but_set, $css_id);
                }
                $content.='</div><div class="cms_clear"></div></div>';
                if($i==$cols) {
                   $content.='<div class="cms_clear"></div>'; 
                   $i=1;
                }
                else $i++;
                $j++;
        }
        $content.='<div class="cms_clear"></div></div>';

 
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Seznam vlastností je prázdný. Zadejte nějaké vlastnosti nebo tento element smažte.','cms_ve').'</div>';
    else $content='';
    
    return $content;
}

// Peoples

    function ve_element_peoples($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage; 
    $class='';
    $element_style='';
    $slider_style='';
    $carousel_set='';
    
    $content=$vePage->print_styles_array(array(
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>"#wrapper ".$css_id." .ve_people_name",
            ),
            array(
                'styles'=>array('font'=>(isset($element['style']['font_position'])? $element['style']['font_position']:'')),
                'element'=>"#wrapper ".$css_id." .ve_people_position",
            ),
            array(
                'styles'=>isset($element['style']['font_text'])? array('font'=>$element['style']['font_text']):"",
                'element'=>"#wrapper ".$css_id." p",
            ),
        ));
    

    if(isset($element['style']['use_slider'])) {
            wp_enqueue_script( 've_miocarousel_script' );
            wp_enqueue_style( 've_miocarousel_style' );
            if($vePage->is_mobile) $element['style']['cols']='one';
            
            $class.=' miocarousel miocarousel_style_1';
            if($element['style']['color_scheme']) $class.=' miocarousel_'.$element['style']['color_scheme'];
            if(isset($element['style']['off_autoplay'])) $carousel_set.=' data-autoplay="0"';
            if($element['style']['delay']) $carousel_set.=' data-duration="'.$element['style']['delay'].'"';
            if($element['style']['speed']) $carousel_set.=' data-speed="'.$element['style']['speed'].'"';
            if($element['style']['animation'] && $element['style']['animation']!='fade') $carousel_set.=' data-animation="'.$element['style']['animation'].'"';
            
            $slider_style=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .miocarousel-inner"):"";
    } else {
            $element_style=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_people_element"):"";
    }  
        
    
    $content.='<div class="in_element_content in_people_element in_people_element_'.$element['style']['style'].' '.$class.'" '.$element_style.' '.$carousel_set.'>';
        
    if(isset($element['style']['use_slider']))
        $content .= '<div class="miocarousel-inner" '.$slider_style.'>';
                                         
    if(!empty($element['style']['peoples'])) {
    
        switch ($element['style']['cols']) {
        case 'one':
            $cols=1;
            break;
        case 'two':
            $cols=2;
            break;
        case 'three':
            $cols=3;
            break;
        case 'four':
            $cols=4;
            break;
        case 'five':
            $cols=5;
            break;
        }
        
        $el_rows = array_chunk( $element['style']['peoples'], $cols );
            
        $i=1;
        $row_num=1;
        
        foreach( $el_rows as $row ){
  
            if(isset($element['style']['use_slider'])) {
                    $row_class=' slide';
                    if($row_num==1) $row_class.=' active';
            } else {
                    $row_class='people_row';
                    if($row_num==1) $row_class.=' people_row_first';
            }
        
            $content .= '<div class="'.$row_class.'">';
        
            foreach ( $row as $item) { 
        
                // new id in images compatibility (temporary)
                if(isset($item['image']) && !is_array($item['image']))
                    $item['image']=array('image'=>$item['image']);
                // end temporary
        
                $link=$vePage->create_link($item['link']);
        
                if($link) {                
                    $el1='<a class="ve_people_link ve_responsive_image" href="'.$link.'" '.(isset($item['link']['target'])? 'target="_blank"':'' ).'>';
                    $el2='</a>';
                }
                else  {
                    $el1='<span class="ve_responsive_image">';
                    $el2='</span>';
                } 

                if($item['image']['image']) $img=$vePage->get_image_url($item['image']['image']);
                else $img=get_bloginfo('template_url').'/modules/visualeditor/images/content/person.png';

                $content.='<div class="elcol col-'.$element['style']['cols'].' '.(($i==1)?'col-first':'').'">';
                $content.='<div class="in_people_col">';
                $content.='<div class="ve_content_people_img_container">'.$el1.'<img src="'.$img.'" alt="'.stripslashes($item['title']).'" />'.$el2.'</div><div class="ve_people_text">';                  
                if($item['title']) $content.='<div class="ve_people_name title_element_container">'.stripslashes($item['title']).'</div>';
                if($item['position']) $content.='<div class="ve_people_position">'.stripslashes($item['position']).'</div>';
                if($item['text']) $content.='<p>'.stripslashes($item['text']).'</p>';
                $content.='</div></div></div>';
                if($i==$cols) {
                   $content.='<div class="cms_clear"></div>'; 
                   $i=1;
                }
                else $i++;
            }
            $content.='<div class="cms_clear"></div></div>';

            $row_num++;
        }
        
        if(isset($element['style']['use_slider'])) {
            $content .= '</div>';  //slider end
            $content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
            $content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
            if($added) {
            $content .= "";
            }
        }
        
        $content .= '</div><div class="cms_clear"></div>';
 
    }                             
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Seznam osob je prázdný. Vložte nějaké údaje nebo tento element smažte.','cms_ve').'</div>';
    else $content='';
    
    return $content;
}

// Price list

function ve_element_pricelist($element, $css_id, $post_id) { 
    global $vePage; 
    
    $content='';
    $empty=false;
    
    if(isset($element['config']['max_width']))
        $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_pricelist_element");
    else $style='';
    
    // rows table
    if(isset($element['style']['pricelist_type']) && $element['style']['pricelist_type']=='rows') {
        if(!empty($element['style']['row_pricelist'])) {
            $content=$vePage->print_styles_array(array(
                array(
                    'styles'=>array('font'=>$element['style']['font_title']),
                    'element'=>$css_id." .mw_table .ve_row_pricelist_title, ".$css_id." .mw_table .ve_row_pricelist_price span",
                ),
                array(
                    'styles'=>array('font'=>$element['style']['font']),
                    'element'=>$css_id." .mw_table .ve_row_pricelist_price span",
                ),
                array(
                    'styles'=>array('font'=>$element['style']['font_description']),
                    'element'=>$css_id." .mw_table .ve_row_pricelist_desc",
                ),
            ));
            $content.='<table class="mw_table mw_table_pricelist in_element_content in_pricelist_element mw_table_style_'.$element['style']['row_table_style'].'" '.$style.'>';
            $i=0;
            foreach( $element['style']['row_pricelist'] as $row ){
                $content.='<tr '.(($i==0)?'class="even"':'').'>
                    <td>
                        <strong class="ve_row_pricelist_title ve_element_title">'.$row['title'].'</strong>
                        '.($row['text']?'<span class="ve_row_pricelist_desc">'.$row['text'].'</span>':'').'
                    </td>
                    <td class="ve_row_pricelist_price">
                        <span class="ve_element_title">'.str_replace(' ','&nbsp;',$row['price']).'</span>
                    </td>
                </tr>';
                $i=($i==0)? 1:0;
            }
    
            $content .= '</table>';
    
        }
    }
    // cols table
    else {
        $colclass_pre=($element['style']['style']==1 || $element['style']['style']==2)? 'full':'';
        $colclass=$colclass_pre."col";
        
        switch (count($element['style']['pricelist'])) {
            case 1:
                $colclass.=' '.$colclass_pre.'col-one';
                break;
            case 2:
                $colclass.=' '.$colclass_pre.'col-two';
                break;
            case 3:
                $colclass.=' '.$colclass_pre.'col-three';
                break;
            case 4:
                $colclass.=' '.$colclass_pre.'col-four';
                break;
            case 5:
                $colclass.=' '.$colclass_pre.'col-five';
                break;
            case 6:
                $colclass.=' '.$colclass_pre.'col-six';
                break;
            case 7:
                $colclass.=' '.$colclass_pre.'col-seven';
                break;
            case 8:
                $colclass.=' '.$colclass_pre.'col-eight';
                break;
            case 9:
                $colclass.=' '.$colclass_pre.'col-nine';
                break;
            case 10:
                $colclass.=' '.$colclass_pre.'col-ten';
                break;
        }
                                                 
        if(!empty($element['style']['pricelist'])) {
            $content=$vePage->print_styles_array(array(
                array(
                    'styles'=>array(
                        'color'=>(isset($element['style']['text_color']) && ($element['style']['style']==3 || $element['style']['style']==4))?$element['style']['text_color']:'',
                        'background-color'=>(isset($element['style']['background_color']) && $element['style']['style']==3)? $element['style']['background_color']:'',
                    ),
                    'element'=>$css_id." .in_pricelist_element .pricelist_col",
                ),
                array(
                    'styles'=>array('font'=>$element['style']['font']),
                    'element'=>$css_id." .pricelist_price",
                ),
                array(
                    'styles'=>(isset($element['style']['font_title']))? array('font'=>$element['style']['font_title']):'',
                    'element'=>$css_id." .pricelist_title",
                ),
                array(
                    'styles'=>(isset($element['style']['font_features']))? array('font'=>$element['style']['font_features']):'',
                    'element'=>$css_id." .pricelist_feature",
                ),
                array(
                    'styles'=>(isset($element['style']['font_description']))? array('font'=>$element['style']['font_description']):'',
                    'element'=>$css_id." .pricelist_description",
                ),
                array(
                    'styles'=>(isset($element['style']['popular_color']))? array('background-color'=>$element['style']['popular_color']) : '',
                    'element'=>$css_id.' .pricelist_popular_text,'.$css_id.' .in_pricelist_element_2 .pricelist_col_popular .pricelist_title',
                ),
                array(
                    'styles'=>(isset($element['style']['popular_color']))? array('border-color'=>$element['style']['popular_color']) : '',
                    'element'=>$css_id.' .in_pricelist_element_1 .pricelist_col_popular .pricelist_head, '.$css_id.' .in_pricelist_element_2 .pricelist_col_popular',
                ),
            ));
            // button style
            $content.=$vePage->create_button_styles($element['style']['button'], $css_id.' .ve_content_button');
            if(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect']) $but_class=' ve_cb_hover_'.$element['style']['button']['hover_effect'];
            else $but_class='';
        
            $content.='<div class="in_element_content in_pricelist_element in_pricelist_element_'.$element['style']['style'].'" '.$style.'>';     
            $i=1;        
            foreach ( $element['style']['pricelist'] as $item) {
                    $content.='<div class="pricelist_col pricelist_col_'.$i.' '.$colclass.' '.(isset($item['popular'])? 'pricelist_col_popular':'').'">';
                        $content.='<div class="pricelist_head">';
                            if($item['title']) $content.='<div class="pricelist_title">'.stripslashes($item['title']).'</div>';
                            if($item['sale_price']) $content.='<div class="pricelist_sale_price">'.$item['sale_price'].'</div>';
                            $content.='<div class="pricelist_price">'.$item['price'].'</div>';
                            if($item['per']) $content.='<div class="pricelist_per">'.$item['per'].'</div>';
                            
                            if(isset($item['popular'])) {
                                $popular_text= (isset($item['popular_text']))? stripslashes($item['popular_text']) : __('NEJPRODÁVANĚJŠÍ','cms_ve');
                                $content.='<div class="pricelist_popular_text">'.$popular_text.'</div>';
                            }
                            
                        $content.='</div>';
                        if(!empty($item['features'])) {
                            $content.='<div class="pricelist_features">';
                                foreach($item['features'] as $feature) {
                                    $content.='<div class="pricelist_feature">'.stripslashes($feature['text']).'</div>';
                                }                
                            $content.='</div>';
                        }
                        
                        $target=(isset($item['link']['target']))? 'target="_blank"' : "";  
                        
                        $content.='<div class="pricelist_button"><a class="ve_content_button ve_content_button_'.$element['style']['button']['style'].' '.$but_class.'" href="'.$vePage->create_link($item['link']).'" '.$target.'>'.stripslashes($item['button_text']).'</a></div>';
                        if($item['text']) $content.='<div class="pricelist_description">'.stripslashes($item['text']).'</div>';
                    $content.='</div>';
            
                    $i++;
            }
            $content.='<div class="cms_clear"></div>';
            $content.='</div>';
        }
        else $emtpy=true;
    }
    
    if($empty && $vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Ceník je prázdný. Vložte nějaké údaje nebo tento element smažte.','cms_ve').'</div>';
    
    
    return $content;  
}


// Bullets
// back compatibility (temporary)
function ve_element_classic_bullets($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    return ve_element_bullets($element, $css_id, $post_id, $edit_mode, $added, $row_set);
}
// back compatibility end
/*
function ve_element_bullets3($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;  
    if(isset($element['style']['bullets'])){ 
        $titles=false;
        foreach($element['style']['bullets'] as $bullet) { 
            if(isset($bullet['title']) && $bullet['title']) $titles=true;
        }
    
        $estyle=(isset($element['config']['max_width']))? $vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content"):"";
        
        if(isset($element['style']['custom_image']) && !is_array($element['style']['custom_image']))
            $element['style']['custom_image']=array('image'=>$element['style']['custom_image']);
        
        $bstyle=$vePage->print_styles(array('background-color'=>$element['style']['bullet_color'],'background-image'=>(($element['style']['custom_image']['image'])? home_url().$element['style']['custom_image']['image'] : '' )),$css_id." .bullet_image span");
                                     
        if($element['style']['type']=='decimal') {
            $class='element_decimal_bullets element_decimal_bullets_type'.$element['style']['style_decimal'];
            if($element['style']['style_decimal']==1 || $element['style']['style_decimal']==3) $class.=" element_bullets_1";
            else $class.=" element_bullets_2";
        }
        else {
            $class='element_image_bullets element_image_bullets_type'.$element['style']['style_image'].' element_image_bullets_type_'.$element['style']['style_image'].'_'.$element['style']['icon'];
            if($element['style']['style_image']==1 || $element['style']['style_image']==3) $class.=" element_bullets_1";
            else $class.=" element_bullets_2";
        }
        if(!$titles) $class.=" notitle_bullets";
        
        if(isset($element['style']['text_font'])) $tstyle=$vePage->print_styles(array('font'=>$element['style']['text_font']),$css_id." .bullet_text");
        else $tstyle='';
        if(isset($element['style']['title_font'])) $titstyle=$vePage->print_styles(array('font'=>$element['style']['title_font']),$css_id." .bullet_text h2");
        else $titstyle='';
    
        $content='<ul class="in_element_content '.$class.' " '.$estyle.'>';
       
        $i=($element['style']['start_number'])? $element['style']['start_number'] : 1;
        foreach($element['style']['bullets'] as $bullet) {
            $bullet_image='<span '.$bstyle.'>'.(($element['style']['type']=='decimal')? $i : '').'</span>';            
            $content.='<li>';
            $content.='<div class="bullet_image">'.$bullet_image.'</div>';
            $content.='<div class="bullet_text" '.$tstyle.'>';
            if(isset($bullet['title']) && $bullet['title']) $content.='<h2 '.$titstyle.'>'.stripslashes($bullet['title']).'</h2>';
            $content.=stripslashes($bullet['text']).'</div>';            
            $content.='</li>';
            $i++;
        }
        $content.='</ul>';
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Seznam je prázdný. Přidejte odrážky nebo element smažte.','cms_ve').'</div>';
    else $content="";
    return $content;
}
*/
function ve_element_bullets($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;
    
    if(isset($element['style']['bullets']) && !empty($element['style']['bullets'])) {  
        // back compatibility
        if(!isset($element['style']['style'])) {
            if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='1') || ($element['style']['type']=='image' && $element['style']['style_image']=='1')) {
                $element['style']['style']='2';
                $element['style']['size']='40';
                $element['style']['space']='30';
                $element['style']['title_font']['font-size']='35';
            }
            else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='2') || ($element['style']['type']=='image' && $element['style']['style_image']=='2')) {
                $element['style']['style']='2';
                $element['style']['size']='20';
                $element['style']['space']='15';
            }
            else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='3') || ($element['style']['type']=='image' && $element['style']['style_image']=='3')) {
                $element['style']['style']='1';
                $element['style']['size']='40';
                $element['style']['space']='30';
                $element['style']['title_font']['font-size']='35';
            }
            else if(($element['style']['type']=='decimal' && $element['style']['style_decimal']=='4') || ($element['style']['type']=='image' && $element['style']['style_image']=='4')) {
                $element['style']['style']='1';
                $element['style']['size']='20';
                $element['style']['space']='15';
            }
            if(isset($element['style']['custom_image']) && isset($element['style']['custom_image']['image']) && $element['style']['custom_image']['image']) $element['style']['type']='own_image';
            
            if($element['style']['icon']=='1') $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/right2.svg", true);
            else if($element['style']['icon']=='2') $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/check1.svg", true);
            else if($element['style']['icon']=='3') $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/right1.svg", true);
            else if($element['style']['icon']=='4') $element['style']['bullet_icon']['code']=file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/right3.svg", true);
        }
        // back compatibility end
        if($element['style']['style']=='4' && $element['style']['type']!='own_image') {
            $height=($element['style']['text_font']['font-size'])? round($element['style']['text_font']['font-size']*1.6) : 24;
            $height_title=($element['style']['title_font']['font-size'])? $element['style']['title_font']['font-size']*1.2 : 24;
        }
        else {
          $height=2*$element['style']['size'];
          $height_title=2*$element['style']['size'];
        }
        
        $styles=array(
            array(
                'styles'=>array(
                    'font'=>array('font-size'=>$element['style']['size']),
                    'width'=>(2*$element['style']['size']).'px',
                    'height'=>$height,
                ),
                'element'=>$css_id." .bullet_icon",
            ),
            array(
                'styles'=>array(
                    'height'=>$height_title,
                ),
                'element'=>$css_id." .mw_bullet_item_wtitle .bullet_icon",
            ),
            array(
                'styles'=>array(
                    'margin_bottom'=>$element['style']['space'],
                ),
                'element'=>$css_id." .mw_element_bullets li",
            ),
            array(
                'styles'=>array(
                    'width'=>$element['style']['size'].'px',
                    'height'=>$element['style']['size'],
                ),
                'element'=>$css_id." .bullet_icon svg",
            ),
            array(
                'styles'=>array('font'=>(isset($element['style']['text_font']))? $element['style']['text_font'] : array()),
                'element'=>$css_id." .bullet_text"
            ),
            array(
                'styles'=>array('font'=>(isset($element['style']['title_font']))? $element['style']['title_font'] : array()),
                'element'=>$css_id." .bullet_text_title"
            ),
        );
        
        if($element['style']['style']=='1' || $element['style']['style']=='2') {
            $styles[]=array(
                'styles'=>array(
                    'background-color'=>$element['style']['bullet_color'],
                ),
                'element'=>$css_id." .bullet_icon",
            );
        } else if($element['style']['style']=='3' || $element['style']['style']=='5') {
            $styles[]=array(
                'styles'=>array(
                    'fill'=>$element['style']['bullet_color'],
                ),
                'element'=>$css_id." .bullet_icon svg",
            );
            $styles[]=array(
                'styles'=>array(
                    'border-color'=>$element['style']['bullet_color'],
                    'color'=>$element['style']['bullet_color'],
                ),
                'element'=>$css_id." .bullet_icon",
            );
        } else if($element['style']['style']=='4') {
            $styles[]=array(
                'styles'=>array(
                    'fill'=>$element['style']['bullet_color'],
                ),
                'element'=>$css_id." .bullet_icon svg",
            );
            $styles[]=array(
                'styles'=>array(
                    'color'=>$element['style']['bullet_color'],
                ),
                'element'=>$css_id." .bullet_icon",
            );
        } 
        
        if(isset($element['config']['max_width'])){
            $styles[]=array(
                'styles'=>array(
                    'max-width'=>$element['config']['max_width']
                ),
                'element'=>$css_id." .in_element_content",
            );
        
        } 
        
        $content=$vePage->print_styles_array($styles);
        
        $class='mw_element_bullets mw_element_bullets_'.$element['style']['style'];
        if($element['style']['bullet_color']=='#ffffff') $class.=' mw_element_bullets_white';
        
        if($element['style']['type']=='own_image') {
            $bullet_icon=$vePage->generate_image($element['style']['custom_image']);
            $class.=' mw_element_bullets_ownimage';
        } else $bullet_icon=stripslashes($element['style']['bullet_icon']['code']);
        
        $content.='<ul class="in_element_content '.$class.'">';
       
        $i=($element['style']['start_number'])? $element['style']['start_number'] : 1;

        foreach($element['style']['bullets'] as $bullet) {
            $bullet_icon=($element['style']['type']=='decimal')? $i : $bullet_icon;            
            $bullet_class='mw_bullet_item mw_bullet_item_'.$i;
            if(isset($bullet['title']) && $bullet['title']) $bullet_class.=' mw_bullet_item_wtitle';
            $content.='<li class="'.$bullet_class.'">';
            $content.='<div class="bullet_icon">'.$bullet_icon.'</div>';
            $content.='<div class="bullet_text">';
            if(isset($bullet['title']) && $bullet['title']) $content.='<div class="bullet_text_title title_element_container">'.stripslashes($bullet['title']).'</div>';
            $content.=stripslashes($bullet['text']).'</div>';            
            $content.='</li>';
            
            $i++;
        }
        $content.='</ul>';
            
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Nejsou vybrané žádné sociální sítě.','cms_ve').'</div>';
    else $content='';
    
    return $content;
}

function ve_element_numbers($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;
    $content='';
    $count=0;
    
    if(isset($element['style']['numbers'])) {
        $instyle=$vePage->print_styles(array('max-width'=>((isset($element['config']['max_width']))? $element['config']['max_width'] : '')),$css_id." .in_element_content");
        $styles=array(
            array(
                    'styles'=>array('font'=>$element['style']['number_font']),
                    'element'=>'#content '.$css_id.' .ve_number_count',
            ),
            array(
                    'styles'=>array('font'=>$element['style']['text_font']),
                    'element'=>$css_id.' .ve_number_text',
            ),
        );
    
        $content.=$vePage->print_styles_array($styles);
        
        switch ($element['style']['cols']) {
            case 'one':
                $cols=1;
                break;
            case 'two':
                $cols=2;
                break;
            case 'three':
                $cols=3;
                break;
            case 'four':
                $cols=4;
                break;
            case 'five':
                $cols=5;
                break;
        }
    
        $i=1;
        $j=1;
        $content.='<div class="in_element_content ve_element_number ve_element_number'.$element['style']['style'].'" '.$instyle.'>';
        foreach($element['style']['numbers'] as $num) {       
            if($num['type']=='custom') 
                $count=$num['number'];
            else if($num['se']) {               
                global $apiConnection;                
                
                // back compatibility (temporary)
                $num['se']=$apiConnection->repair_content_val($num['se']);
                // back compatibility end
                if(isset($num['se']['id']) && $num['se']['id']) { 
                    $client=$apiConnection->getClient($num['se']['api']);
                    $count=$client->get_list_count($num['se']['id']);
    
                    if($count<0) $count=0; 
                }         
            } 
            
            $content.='<div class="elcol col-'.$element['style']['cols'].' '.(($i==1)?'col-first':'').' '.(($j>$cols)?'feature_row':'').'">';        
            $content.='<div class="ve_number_count title_element_container" data-number="'.number_format($count, 0, ',', ' ').'"><span>0</span>'.$num['unit'].'</div>';
            if($num['title']) $content.='<div class="ve_number_text">'.$num['title'].'</div>';
            $content.='</div>';
            
            if($i==$cols) {
                           $content.='<div class="cms_clear"></div>'; 
                           $i=1;
                        }
                        else $i++;
                        $j++;
        }
        $content.='<div class="cms_clear"></div>'; 
        $content.='</div>';
        
        
        if($edit_mode || ($vePage->edit_mode && strpos($css_id, 'popup'))) {
            $content.='<script>
            jQuery(function(){
                numberAnimationIncrease("'.$css_id.'");
            });</script>';
        }
        
        wp_enqueue_script( 've_waypoints_script' );
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Element je prázdný. Přidejte čísla nebo element smažte.','cms_ve').'</div>';
    return $content;  
}

function ve_element_progressbar($element, $css_id, $post_id) { 
    global $vePage;
    
    $instyle=$vePage->print_styles(array('max-width'=>((isset($element['config']['max_width']))? $element['config']['max_width'] : '')),$css_id." .in_element_content");
    
    $percent=intval($element['style']['percent']);
    if($percent>100) $percent=100;
    
    $styles=array(
        array(
                'styles'=>array('min-width'=>$percent.'%','background-color'=>$element['style']['color1']),
                'element'=>$css_id.' .ve_progressbar_prog',
        ),
        array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>$css_id.' .ve_progressbar',
        ),
    );
    if($element['style']['style']!=3) {
        $styles[]=array(
            'styles'=>array('background-color'=>$element['style']['color2']),
            'element'=>$css_id.' .ve_progressbar_bg',
        );
    } else {
        $styles[]=array(
            'styles'=>array('border-color'=>$element['style']['color1']),
            'element'=>$css_id.' .ve_progressbar_bg',
        );
    }
    $content=$vePage->print_styles_array($styles);
    
    $text1='';
    $text2=$element['style']['text'];
    $text3=$percent.'%';     
    if($element['style']['style']==1) {
        $text1=$element['style']['text'].' <strong>'.$percent.'</strong>%';
        $text2='';
        $text3='';
    } else if($element['style']['style']==4) {
        $text1=$element['style']['text'];
        $text2='';
        $text3='<strong>'.$percent.'</strong>%';
    } else if($element['style']['style']==6) {
        $text2=$element['style']['text'].' <strong>'.$percent.'</strong>%';
        $text1='';
        $text3='';
    }
    
    
    $content.='<div class="in_element_content ve_progressbar '.(isset($element['style']['rounded'])?'ve_progressbar_rounded':'').' ve_progressbar_'.$element['style']['style'].'" '.$instyle.'>';
    if($text1) $content.='<div class="ve_progressbar_text">'.$text1.'</div>';
    $content.='<div class="ve_progressbar_bg">';
    $content.='<div class="ve_progressbar_prog">';    
    if($text2) $content.='<span class="ve_progressbar_text">'.$text2.'</span>';
    if($text3) $content.='<span class="ve_progressbar_percentage">'.$text3.'</span>';
    $content.='</div></div></div>';

    return $content;
}

// Facebook
function ve_element_share($element, $css_id, $post_id) {
    global $vePage;
    $estyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .element_bullets_container"):"";
    $url=$vePage->create_link($element['content'],false);
    $url=($url)? $url : get_permalink($post_id);
    
    if($element['style']['scheme']==2) {
        $g_style='tall';
        $t_style='vertical';
        $f_style='box_count';
    }
    else {
        $g_style="medium";
        $t_style='horizontal';
        $f_style='button_count';
    }
    
    $content='<div class="in_element_content in_share_element in_share_element_'.$element['style']['scheme'].'" '.$estyle.'>';
    //facebook
    if(isset($element['style']['show']['facebook'])) $content.='<div class="fb-like" data-href="'.$url.'" data-layout="'.$f_style.'" data-action="like" data-show-faces="false" data-share="false"></div>';
    //twitter
    if(isset($element['style']['show']['twitter'])) $content.='<div class="twitter-like"><a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$url.'" data-count="'.$t_style.'" data-lang="cs">Tweet</a>
        </div>';
    //google+
    if(isset($element['style']['show']['google'])) $content.='<div class="g-like"><div class="g-plusone" data-size="'.$g_style.'" data-href="'.$url.'"></div></div>
      <script type="text/javascript">
        (function() {
          var po = document.createElement(\'script\'); po.type = \'text/javascript\'; po.async = true;
          po.src = \'https://apis.google.com/js/platform.js\';
          var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(po, s);
        })();
      </script>';
      
    $content.="</div>";
    return $content;
  
}

function ve_element_like($element, $css_id, $post_id) {
    global $vePage;
    $faces=(isset($element['style']['setting']['faces']))? 'true' : 'false';
    $share=(isset($element['style']['setting']['share']))? 'true' : 'false';
    
    $url=$vePage->create_link($element['content'],false);
    $url=($url)? $url:get_permalink($post_id);
    
    $content='';
    if($element['style']['align']=='center') $content.='<div class="ve_center">';
    $content.='<div class="fb-like ve_center" data-href="'.$url.'" data-width="450" data-colorscheme="'.$element['style']['scheme'].'" data-layout="'.$element['style']['layout'].'" data-action="like" data-show-faces="'.$faces.'" data-share="'.$share.'"></div>';
    if($element['style']['align']=='center') $content.='</div>';
    return $content;
  
}
function ve_element_fac_share($element, $css_id, $post_id) {
    global $vePage;
    
    $url=$vePage->create_link($element['content'],false);
    $url=($url)? $url:get_permalink($post_id);
    
    if($element['style']['align']=='right') $class="in_element_content_right";
    else $class='';
    
    $content='';
    if($element['style']['align']=='center') $content.='<div class="ve_center">';
    if($element['style']['appearance']=='classic' && $element['style']['layout']!='4') {        
        $content.='<div class="fb-share-button '.$class.'" data-href="'.$url.'" data-layout="'.$element['style']['layout'].'" data-mobile-iframe="true"></div>';       
    } else if($element['style']['appearance']=='button') {

        $content.=$vePage->create_button_styles($element['style']['button'], $css_id.' .ve_content_button');
        if(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect']) $but_class=' ve_cb_hover_'.$element['style']['button']['hover_effect'];
        else $but_class='';
        
        $content.='<a class="ve_content_button ve_content_button_icon ve_content_button_'.$element['style']['align'].' ve_content_button_'.$element['style']['button']['style'].' '.$but_class.'" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($url).'">
        <span class="ve_but_icon">'.stripslashes($element['style']['button']['icon']['code']).'</span>
        <span class="ve_but_text">'.$element['style']['button_text'].'</span></a>';
    
    } else {
        $text='<img src="'.((substr($element['style']['image'], 0, 4)=='http')?$element['style']['image']:home_url().$element['style']['image']).'" alt="" />';
        $content.='<a href="https://www.facebook.com/sharer/sharer.php?u='.urlencode($url).'" target="_blank">'.$text.'</a>';
    }
    if($element['style']['align']=='center') $content.='</div>';
    $content.='<div class="cms_clear"></div>';
    return $content;
  
}
function ve_element_fcomments($element, $css_id, $post_id) {
    global $vePage;
    if(isset($element['style']['width'])) $width=$element['style']['width']; 
    else $width='550';
    if(wp_is_mobile()) {
      $width='100%';
    }
    
    $style=$vePage->print_styles(array('width'=>$width.'px'),$css_id." .in_element_fcomments");
    
    $url=$vePage->create_link($element['content'],false);
    $url=($url)? $url:get_permalink($post_id);
    
    
    $content='<div class="in_element_content in_element_fcomments" '.$style.'>';
    $content.=cms_facebook_comments($url, $element['style']['per_page'],$element['style']['scheme'],$width);
    $content.='</div>';
    return $content;
}
function ve_element_likebox($element, $css_id) {   
    global $vePage;
    if($element['content']) {
        if(isset($element['config']['max_width'])) $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content");
        else $style='';
        
        $faces=(isset($element['style']['setting']['faces']))? 'faces' : 'true';
        $cover=(isset($element['style']['setting']['cover']))? 'true' : 'false';
        $cta=(isset($element['style']['setting']['cta']))? 'true' : 'false';
        $header=(isset($element['style']['setting']['header']))? 'true' : 'false';
        $tabs = (isset($element['style']['tabs'])?implode(', ', $element['style']['tabs']):'');
        $content='
            <div class="in_element_content in_element_likebox" '.$style.'>
                <div class="fb-page" data-href="'.$element['content'].'" data-height="'.$element['style']['height'].'" data-width="'.$element['style']['height'].'"
                data-tabs="'.$tabs.'" data-hide-cover="'.$cover.'" data-show-facepile="'.$faces.'" data-hide-cta="'.$cta.'" adapt_container_width="true" data-small-header="'.$header.'"></div>
            </div>';
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Like box nelze vykreslit. Musíte zadat URL facebookové stránky.','cms_ve').'</div>';
    else $content="";
    return $content;
}

// FAPI

function ve_element_fapi($element, $css_id, $post_id, $edit_mode) {
    global $vePage;
    if(isset($element['config']['max_width'])) $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content");
    else $style='';
    
    if($element['content']) {
    
        $user = wp_get_current_user(); 
        if($user->ID) {
          $login=get_option('ve_connect_fapi');
          if (!class_exists('FAPIClient')) {
              require_once FAPI_API;  
          }
          if(is_fapi_connected($login['connection']['login'], $login['connection']['password']))
              $clientDetails = FapiHelpers::getClientFields($login['connection']['login'], $login['connection']['password'], $user->user_email);
          else $clientDetails = null;

        } else {
        	$clientDetails = null;
        }
        
        if(isset($element['style']['button']['hover_effect']) && $element['style']['button']['hover_effect']) $but_class=' ve_form_cb_hover_'.$element['style']['button']['hover_effect'];
        else $but_class='';
        
        $content='<div class="in_element_content in_element_fapi_form in_element_fapi_form_'.$element['style']['form-style'].' '.$but_class.'" '.$style.'>';
        
        if($edit_mode) {
            $content.='Načítám formulář...<script type="text/javascript">
                jQuery(document).ready(function($) {
        
                            var $target = $("'.$css_id.' .in_element_content");
        
                    				var old = window.document.write;
                    				window.document.write = function(html) {
                    					$target.html(html);
                    					window.document.write = old;';
                   if($clientDetails) {          
                              $content.='var clientDetails = '. FapiHelpers::escapeJs($clientDetails) .'
                          		if (clientDetails) {
                          			var $form = $("#frm-showUserForm");
                          			var inputNames = ["name", "surname", "email", "mobil", "street", "city", "postcode", "company", "ic", "dic"];
                          			for (var i in inputNames) {
                          				var name = inputNames[i];
                          				var value = clientDetails[name];
                          				if (value !== undefined) {
                          					$form.find("[name=" + name + "]").val(value);
                          				}
                          			}
                          		}';
                  }
                    				$content.='};
                            
                    				$target.html(\''.str_replace('/','\/',stripslashes($element['content'])).'\');
        
                });
          
          	</script>';
    
        } else {
            if($clientDetails) {          
                          $content.='<script type="text/javascript">
                              jQuery(document).ready(function($) {
                              var clientDetails = '. FapiHelpers::escapeJs($clientDetails) .'
                          		if (clientDetails) {
                          			var $form = $("#frm-showUserForm");
                          			var inputNames = ["name", "surname", "email", "mobil", "street", "city", "postcode", "company", "ic", "dic"];
                          			for (var i in inputNames) {
                          				var name = inputNames[i];
                          				var value = clientDetails[name];
                          				if (value !== undefined) {
                          					$form.find("[name=" + name + "]").val(value);
                          				}
                          			}
                          		}});
          
          	           </script>';
            }
            $content.=stripslashes($element['content']);
        }
        $content.='</div>';

        $styles=array(
            array(
                'styles'=>(isset($element['style']['font_title']))? array('font'=>$element['style']['font_title']) : '',
                'element'=>$css_id.' .form_container_title',
            ),
            array(
                'styles'=>array('font'=>$element['style']['font_text'],'background-color'=>($element['style']['background-color'])),
                'element'=>$css_id.' .form_container,'.$css_id.' .fapi-form-submit',
            )
        );

        $content.=$vePage->create_button_styles($element['style']['button'], $css_id.' #frm-submit');

        $content.=$vePage->print_styles_array($styles);
        
        
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Není vybrán žádný prodejní formulář. Pro správné vykreslení formuláře zvolte v nastavení tohoto elementu, který formulář chcete na stránce zobrazit.','cms_ve').'</div>';
    else $content="";
    return $content;
    
}  


function ve_element_variable_content($element, $css_id) {
    global $vePage; 
    
    $content='';
    if($element['content']) {
        $layer=$vePage->get_layer($element['content'], 've_elvar');  
        $var=$layer[0]['content'][0]['content'];
        $i=0;
        foreach($var as $content_key=>$code) {      
            $new_css_id=str_replace('#element_','',$css_id).'_'.$i;
            if(!$vePage->is_mobile || !isset($code['config']['mobile_visibility']))  $content.=$vePage->generate_element($code, str_replace('#','',$new_css_id), '', false, 'var'.$element['content'].'_');
            $i++;
        } 
    }
    else $content='<div class="cms_error_box admin_feature">'.__('Není vybrán žádný předdefinovaný obsah.','cms_ve').'</div>';
    
    return $content;  
}

function ve_element_faq($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;
    $content='';

    if(isset($element['style']['faqs'])) {
        $clickable = (isset($element['style']['clickable']) ? intval($element['style']['clickable']) : 0);
        $bg_color = ($element['style']['style']==2?$element['style']['background-color']:'');

        $instyle=$vePage->print_styles(array('max-width'=>((isset($element['config']['max_width']))? $element['config']['max_width'] : '')),$css_id." .in_element_content");
        $answer_padding=($element['style']['question_font']['font-size'])? $element['style']['question_font']['font-size']*1.2:'';
        $styles=array(
            array(
                'styles'=>array('font'=>$element['style']['question_font']),
                'element'=>'#wrapper '.$css_id.' h2.ve_faq_question',
            ),
            array(
                'styles'=>array('border-color'=>$element['style']['question_font']['color']),
                'element'=>$css_id.' .in_faq_element_clickable h2.ve_faq_question::after',
            ),
            array(
                'styles'=>array('font'=>$element['style']['answer_font'], 'padding-left'=>$answer_padding.'px', 'padding-right'=>$answer_padding.'px', 'padding-bottom'=>$answer_padding.'px'),
                'element'=>$css_id.' .ve_faq_answer',
            ),
        );

        if ($element['style']['style']==2) {
            $styles[] = array(
                'styles'=>array('background-color'=>$bg_color),
                'element' => $css_id." .faq_item",
            );
        }
        if ($element['style']['style']==3) {
            $styles[] = array(
                'styles'=>array('padding-top'=>$answer_padding.'px'),
                'element' => $css_id.' .ve_faq_answer',
            );
        }


        $onclick = (($clickable)?'onclick="faqClick(this, \''.$css_id.'\');"':'');

        $content.=$vePage->print_styles_array($styles);

        switch ($element['style']['cols']) {
            case 'one':
                $cols=1;
                break;
            case 'two':
                $cols=2;
                break;
            case 'three':
                $cols=3;
                break;
            case 'four':
                $cols=4;
                break;
            case 'five':
                $cols=5;
                break;
        }

        $el_rows = array_chunk( $element['style']['faqs'], $cols );
            
        
        $row_num=1;
        
        $content.='<div class="in_element_content in_faq_element in_faq_element_'.$element['style']['style'].' '.(($clickable)?'in_faq_element_clickable':'').'"'.$instyle.'>';
        
        foreach( $el_rows as $row ){
        
            $content .= '<div class="faq_row faq_row_'.$element['style']['cols'].'">';
        
            $i=1;
            foreach($row as $faq) {
    
                $content.='<div class="elcol col-'.$element['style']['cols'].' '.(($i==1)?'col-first':'').' faq_item">';
                
                $content.='<h2 class="ve_faq_question '.(($clickable)?'ve_faq_question_close':'').'" '.$onclick.'>'.stripslashes($faq['question']).'</h2>';   
                $content.='<div class="ve_faq_answer '.(($clickable)?'ve_nodisp':'').'">'.stripslashes($faq['answer']).'</div>';
                $content.='</div>';
    
                $i++;
    
    
            }
    
            $content.='<div class="cms_clear"></div></div>';
        }
        $content.='</div>';

    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Element je prázdný. Přidejte otázky nebo element smažte.','cms_ve').'</div>';   
    return $content;
}

function ve_element_catalog($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;
    
    if(isset($element['style']['use_slider'])) {
        wp_enqueue_script( 've_miocarousel_script' );
        wp_enqueue_style( 've_miocarousel_style' );
        if($vePage->is_mobile) $element['style']['cols']=1;
    }   
    
    $styles=array(
        array(
            'styles'=>isset($element['style']['font_color'])? array('font'=>array('color'=>$element['style']['font_color'])):"",
            'element'=>$css_id." .mw_element_item_price, ".$css_id." .mw_element_item_description, ".$css_id." h3",
        ),
        array(
            'styles'=>array('font'=>$element['style']['font_title']),
            'element'=>$css_id." h3",
        ),
        array(
            'styles'=>array('font'=>(isset($element['style']['font_description'])? $element['style']['font_description']:'')),
            'element'=>$css_id." .mw_element_item_description",
        ),
        array(
            'styles'=>isset($element['style']['font_price'])? array('font'=>array('color'=>$element['style']['font_price']['color'].'!important','font-size'=>$element['style']['font_price']['font-size'])):"",
            'element'=>$css_id." .mw_element_item_price",
        ),
    );
    
    if(isset($element['style']['hover_color']) && $element['style']['hover_color'])
      $styles[]=array(
          'styles'=>array('background-color'=>$vePage->hex2rgba($element['style']['hover_color'],80)),
          'element'=>$css_id." .mw_element_item_image_hover",
      );
    
    $content=$vePage->print_styles_array($styles);
    
    if($element['style']['item_type']=='subpage') {
        if(isset($element['style']['page']) && $element['style']['page']) $post_id=$element['style']['page'];
        
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'menu_order',
            'child_of' => $post_id,
            'parent' => $post_id,
        );   
        $items = get_pages($args);
                
    } else $items=$element['style']['items'];
    
    

    if(!empty($items)) {
    
        $cols=(isset($element['style']['cols']) && $element['style']['cols'])? $element['style']['cols'] : 3;

        $rows = array_chunk( $items, $cols );

        $i=1;
        
        $catalog_class='in_element_content mw_element_items mw_element_items_style_'.$element['style']['style'];
        $carousel_set='';
        if(isset($element['style']['use_slider'])) {
            $catalog_class.=' miocarousel miocarousel_style_1';
            if($element['style']['color_scheme']) $catalog_class.=' miocarousel_'.$element['style']['color_scheme'];
            if(isset($element['style']['off_autoplay'])) $carousel_set.=' data-autoplay="0"';
            if($element['style']['delay']) $carousel_set.=' data-duration="'.$element['style']['delay'].'"';
            if($element['style']['speed']) $carousel_set.=' data-speed="'.$element['style']['speed'].'"';
            if($element['style']['animation'] && $element['style']['animation']!='fade') $carousel_set.=' data-animation="'.$element['style']['animation'].'"';
        }
        
        $content .= '<div class="'. $catalog_class . '" '.$carousel_set.'>';  
        if(isset($element['style']['use_slider']))
                $content .= '<div class="miocarousel-inner">';

        foreach( $rows as $row ){

            if(isset($element['style']['use_slider'])) {
                $row_class=' slide';
                if($i==1) $row_class.=' active';
            } else {
                $row_class='mw_element_row';
                if(count($rows)==$i) $row_class.=' mw_element_row_last';
            }

            $content .= '<div class="'.$row_class.'">';
            
            $text_align=(isset($element['style']['text_align']))? $element['style']['text_align']:'left';
            
            $img_col_size=(isset($element['style']['image_size']))? $element['style']['image_size'] : 2;

            foreach ($row as $item) {
              
                $thumb_name=($vePage->is_mobile || $added || (isset($row_set['type']) && $row_set['type']=='full'))? 'mio_columns_1' :'mio_columns_'.$cols;
                $cols_style=(isset($element['style']['cols_type']))? $element['style']['cols_type']:'';
                $hover_style=(isset($element['style']['hover']))? $element['style']['hover']:'';
                if($element['style']['item_type']=='subpage') {
                    
                    $args=array(
                        'style'=>$element['style']['style'],
                        'cols'=>$cols,
                        'cols_style'=>$cols_style,
                        'hover_style'=>$hover_style,
                        'link'=>get_permalink($item->ID),
                        'imageid'=>get_post_thumbnail_id( $item->ID ),
                        'thumb'=>$thumb_name,
                        'title'=>$item->post_title,
                        'description'=>$item->post_excerpt,
                        'align'=>$text_align,
                        'img_col_size'=>$img_col_size,
                    );
                
                } else {
                
                    $args=array(
                        'style'=>$element['style']['style'],
                        'cols'=>$cols,
                        'cols_style'=>$cols_style,
                        'hover_style'=>$hover_style,
                        'link'=>$vePage->create_link($item['link']),
                        'target'=>isset($item['link']['target'])? true:false,
                        'imageid'=>$item['image']['imageid'],
                        'image'=>$item['image']['image'],
                        'thumb'=>$thumb_name,
                        'title'=>$item['title'],
                        'subtitle'=>isset($item['subtitle'])? $item['subtitle']:'',
                        'description'=>$item['description'],
                        'price'=>$item['price'],
                        'align'=>$text_align,
                        'img_col_size'=>$img_col_size,
                    );
                    
                    if($element['style']['style']=='1') {
                      $args['image_hover']=true;
                      $args['image_hover_link']=true;
                      $args['image_hover_content']='<h3>'.$item['title'].'</h3>';
                    }

                }
                
                $content.=$vePage->generate_element_item($args);

            }
            $content.='<div class="cms_clear"></div></div>';
            
            $i++;

        }

        if(isset($element['style']['use_slider'])) {
            $content .= '</div>';  //slider end
            $content .= '<div class="mc_arrow_container mc_arrow_container-left"><span></span></div>';
            $content .= '<div class="mc_arrow_container mc_arrow_container-right"><span></span></div>';
            if($added) {
                $content .= "";
            }
        }

        $content .= '</div>';

    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Katalog je prázdný, vložte nějaké položky nebo element smažte.','cms_ve').'</div>';
    else $content='';

    return $content;
}

function ve_element_table($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;
    
    $content=$vePage->print_styles_array(array(
        array(
            'styles'=>array('font'=>$element['style']['font']),
            'element'=>$css_id." .mw_table",
        ),
        array(
            'styles'=>array('width'=>$element['style']['width']['size'].$element['style']['width']['unit']),
            'element'=>$css_id." .mw_table th",
        ),
    ));

    if(!empty($element['style']['lines'])) {
    
        $content.='<table class="mw_table mw_table_style_'.$element['style']['style'].'">';
        $i=0;
        foreach( $element['style']['lines'] as $row ){
            $content.='<tr '.(($i==0)?'class="even"':'').'><th>'.stripslashes($row['title']).'</th><td>'.stripslashes($row['text']).'</td></tr>';
            $i=($i==0)? 1:0;
        }

        $content .= '</table>';

    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Tabulka je prázdná. Vložte nějaké údaje nebo tento element smažte.','cms_ve').'</div>';
    else $content='';

    return $content;
}
function ve_element_google_map($element, $css_id, $post_id, $edit_mode, $added) {
    global $vePage;
    
    wp_enqueue_script('ve_google_maps');
    
    $id='mw_gmap_'.str_replace('#','',$css_id);

    $content='<div class="mw_google_map_container" id="'.$id.'" data-setting=\'{';
    if($element['style']['map_setting']['address']) $content.='"address":"'.$element['style']['map_setting']['address'].'",';
    $content.='"zoom":'.$element['style']['map_setting']['zoom'].',';
    $content.='"scrollwheel":'.(!isset($element['style']['setting']['scrollwheel'])? 'false':'true');
    $content.='}\'';
    $content.='style="width:100%; height:'.$element['style']['height'].'px;"></div>';
    
    if($added) {
        $content.='
        <script>
        var setting = {
          address : "'.$element['style']['map_setting']['address'].'",
          zoom : '.$element['style']['map_setting']['zoom'].',
          scrollwheel : '.(!isset($element['style']['setting']['scrollwheel'])? 'false':'true').',
        };
        initialize_google_map("'.$id.'", setting);
        </script>';
        
        $vePage->google_map_api=get_option('ve_google_api');
    }
    
    if($vePage->edit_mode) {
      $content.='<div class="cms_error_box admin_feature ve_nodisp" id="'.$id.'_error">'.__('Adresa nenalezena. Zadejte platnou adresu.','cms_ve').'</div>';
    
      if(!$vePage->google_map_api || !isset($vePage->google_map_api['api_key']) || !$vePage->google_map_api['api_key']) 
          $content.='<div class="cms_error_box admin_feature">'.__('Pro správné fungování google mapy je potřeba v nastavení elementu zadat vlastní API klíč.','cms_ve').'</div>';
    }
    
    return $content;
}

function ve_element_event_calendar($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;
    
    $styles=array(
        array(
            'styles'=>isset($element['style']['font_color'])? array('font'=>array('color'=>$element['style']['font_color'])):"",
            'element'=>$css_id." .mw_element_item_price, ".$css_id." .mw_element_item_description, ".$css_id." h3",
        ),
        array(
            'styles'=>array('font'=>$element['style']['font_title']),
            'element'=>$css_id." h3",
        ),
        array(
            'styles'=>array('font'=>(isset($element['style']['font_description'])? $element['style']['font_description']:'')),
            'element'=>$css_id." .mw_element_item_description",
        ),
        array(
            'styles'=>isset($element['style']['font_price'])? array('font'=>array('color'=>$element['style']['font_price']['color'].'!important','font-size'=>$element['style']['font_price']['font-size'])):"",
            'element'=>$css_id." .mw_element_item_price",
        ),
    );
    
    if(isset($element['style']['hover_color']) && $element['style']['hover_color'])
      $styles[]=array(
          'styles'=>array('background-color'=>$vePage->hex2rgba($element['style']['hover_color'],80)),
          'element'=>$css_id." .mw_element_item_image_hover",
      );
    
    $content=$vePage->print_styles_array($styles);
    /*
    $args = array(
            'posts_per_page'   => -1,
            'post_type' => 'mw_event',
            'meta_query' => array(
                array(
                    'key' => 'mw_event_date_start',
                    'value' => current_time('timestamp'),
                    'meta_compare' => '<'
                ),
            ),
            'orderby' => 'meta_value_num',
            'meta_key' => 'mw_event_date_start',
            'order' => 'ASC',
    );   
    $items = get_posts($args);
    */
    
    $num=(isset($element['style']['num']) && $element['style']['num'])? $element['style']['num'] : -1;
    
    $orderby=(isset($element['style']['show']) && $element['style']['show']=='<')? 'DESC' : 'ASC';
    
    $query_args=array(
      'post_type'=>'mw_event',
      'posts_per_page'=>$num,
      'meta_key' => 'mw_event_date_start',
      'orderby'=>array(
        'mw_event_date_start' => $orderby
      ),
    );
    
    if(isset($element['style']['show']) && $element['style']['show']) {
        $query_args['meta_query'] = array(
            'mw_event_date_start' => array(
              'key' => 'mw_event_date_start',
              'value' => current_time('timestamp'),
              'compare' => $element['style']['show'],
            ),  
        );
    }
    
    $pages = new WP_Query( $query_args );
    $items=$pages->posts;

    if(!empty($items)) {
    
        $cols=(isset($element['style']['cols']) && $element['style']['cols'])? $element['style']['cols'] : 3;
        if($vePage->is_mobile) $cols=1;
        
        $hide_image=false;
        if(isset($element['style']['hide_image']) && ($element['style']['style']=='3' || $element['style']['style']=='7' || $element['style']['style']=='4' || $element['style']['style']=='6')) {
          $hide_image=true;
        }
        $show_description=true;
        if(isset($element['style']['hide_description']) && ($element['style']['style']=='3' || $element['style']['style']=='7' || $element['style']['style']=='4' || $element['style']['style']=='6')) {
          $show_description=false;
        }

        $rows = array_chunk( $items, $cols );

        $i=1;
        
        $catalog_class='in_element_content mw_element_items mw_element_items_style_'.$element['style']['style'];
        
        $content .= '<div class="'. $catalog_class . '">';  

        foreach( $rows as $row ){

            $row_class='mw_element_row';
            if(count($rows)==$i) $row_class.=' mw_element_row_last';

            $content .= '<div class="'.$row_class.'">';
            
            $text_align=(isset($element['style']['text_align']))? $element['style']['text_align']:'left';
            $img_col_size=(isset($element['style']['image_size']))? $element['style']['image_size'] : 2;

            foreach ($row as $item) {
              
                $event_date = get_post_meta($item->ID,'mw_event_date_start',true);
              
                $thumb_name=($vePage->is_mobile || $added || (isset($row_set['type']) && $row_set['type']=='full'))? 'mio_columns_1' :'mio_columns_'.$cols;
                $cols_style=(isset($element['style']['cols_type']))? $element['style']['cols_type']:'';
                $hover_style=(isset($element['style']['hover']))? $element['style']['hover']:'';
                
                $event_setting=get_post_meta($item->ID,'ve_event',true);
                
                if(isset($event_setting['date_end']) && $event_setting['date_end'] && $event_date<=strtotime($event_setting['date_end'])) {
                  $date_end=' - '.date( 'j.n.', strtotime($event_setting['date_end']));
                } else $date_end='';
                
                $subtitle='';
                if($event_date) $subtitle.='<span class="mw_event_subtitle_date">'.file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/calendar.svg", true).date( 'j.n.', $event_date ).$date_end.'</span>';
                if(isset($event_setting['where']) && $event_setting['where']) $subtitle.='<span class="mw_event_subtitle_where">'.file_get_contents(get_template_directory() ."/modules/visualeditor/images/icons/map.svg", true).$event_setting['where'].'</span>';

                $args=array(
                    'style'=>$element['style']['style'],
                    'cols'=>$cols,
                    'cols_style'=>$cols_style,
                    'hover_style'=>$hover_style,
                    'link'=>$vePage->create_link($event_setting['event_page']),
                    'target'=>(isset($event_setting['event_page']['target']))? true : false,
                    'imageid'=>get_post_thumbnail_id( $item->ID ),
                    'thumb'=>$thumb_name,
                    'title'=>$item->post_title,
                    'description'=>$item->post_excerpt,
                    'align'=>$text_align,
                    //'price'=>'500 Kč',
                    'subtitle'=>$subtitle,
                    'img_col_size'=>$img_col_size,
                    'hide_image'=>$hide_image,
                    'show_description'=>$show_description,
                );
                
                $content.=$vePage->generate_element_item($args);

            }
            $content.='<div class="cms_clear"></div></div>';
            
            $i++;

        }

        $content .= '</div>';

    }
    else $content='<div class="mw_element_items_info_box">'.__('Momentálně nejsou k dispozici žádné akce.','cms_ve').'</div>';

    return $content;
}

function ve_element_social_icons($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;
    
    if(!$element['style']['hover_color'] && $element['style']['color']) $element['style']['hover_color']=$vePage->shiftColor($element['style']['color'],0.8);
    
    if(isset($element['style']['socials']) && !empty($element['style']['socials'])) {  
        $styles=array(
            array(
                'styles'=>array(
                    'font'=>array('font-size'=>$element['style']['size']),
                    'width'=>$element['style']['size'].'px',
                    'height'=>$element['style']['size'],
                    'margin_right'=>$element['style']['space'],
                ),
                'element'=>$css_id." .mw_social_icon_bg",
            ),
            array(
                'styles'=>array(
                    'width'=>$element['style']['size'].'px',
                    'height'=>$element['style']['size'],
                ),
                'element'=>$css_id." .mw_social_icon_bg svg",
            ),
        );
        
        if($element['style']['style']=='1' || $element['style']['style']=='2') {
            $styles[]=array(
                'styles'=>array(
                    'background-color'=>$element['style']['color'],
                ),
                'element'=>$css_id." .mw_social_icon_bg",
            );
            $styles[]=array(
                'styles'=>array(
                    'background-color'=>$element['style']['hover_color'],
                ),
                'element'=>$css_id." .mw_social_icon_bg:hover",
            );
            if($element['style']['hover_color'] && $element['style']['color']=="#ffffff") {
                $styles[]=array(
                    'styles'=>array(
                        'fill'=>'#fff',
                    ),
                    'element'=>$css_id." .mw_social_icon_bg:hover svg",
                );
            }
        } else if($element['style']['style']=='3' || $element['style']['style']=='4') {
            $styles[]=array(
                'styles'=>array(
                    'fill'=>$element['style']['color'],
                ),
                'element'=>$css_id." .mw_social_icon_bg svg",
            );
            $styles[]=array(
                'styles'=>array(
                    'fill'=>$element['style']['hover_color'],
                ),
                'element'=>$css_id." .mw_social_icon_bg:hover svg",
            );
            $styles[]=array(
                'styles'=>array(
                    'border-color'=>$element['style']['color'],
                ),
                'element'=>$css_id." .mw_social_icon_bg",
            );
            $styles[]=array(
                'styles'=>array('border-color'=>$element['style']['hover_color']),
                'element'=>$css_id." .mw_social_icon_bg:hover",
            );
        } 
        
        $content=$vePage->print_styles_array($styles);
        
        $class='mw_social_icons_container mw_social_icons_container_'.$element['style']['style'];
        if($element['style']['color']=='#ffffff') $class.=' mw_social_icons_container_white';
        $class.=' ve_'.$element['style']['align'];
        
        $content.='<div class="'.$class.'">';
        
        foreach($element['style']['socials'] as $item) {
          
            $link=$item['link'];
            
            if($link) {
                $start='a href="'.$link.'" target="_blank"';
                $end='a';
            }
            else {
                $start='i';
                $end='i';
            }

            $content.='<'.$start.' class="mw_social_icon_bg '.$class.'">'.stripslashes($item['icon']['code']).'</'.$end.'>';
            
        }
        $content.='</div>';
    }
    else if($vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.__('Nejsou vybrané žádné sociální sítě.','cms_ve').'</div>';
    
    return $content;
}
function ve_element_social_sprinters($element, $css_id, $post_id, $edit_mode, $added, $row_set) {
    global $vePage;
    $content='';
    
    if($element['style']['code']) {
      
      $code=str_replace('sprinte.rs/','',$element['style']['code']);
      $err_message='';
      
      $url='https://socialsprinters.com/aa/api_ss/getAppUrl.php?short_code='.$code.'&access_token=f5hg4k5e4a545h4fs5a';
      $response = wp_remote_post( $url, array(
          	'method' => 'POST',
          	'timeout' => 45,
          	'redirection' => 5,
          	'httpversion' => '1.1',
          	'blocking' => true,
          	'headers' => array(),
      ));   

      $return = wp_remote_retrieve_body($response);   
      if($return=='bad_access_token') {
          $err_message=__('Zadaný zkrácený odkaz sprinte.rs je neplatný.','cms_ve');
      } 
      else if($return=='not_found') {
          $err_message=__('Nebyla nalezena žádná aplikace Social Sprinters.','cms_ve');
      } 
      else {
          
          $style='';
          if(isset($element['config']['max_width'])) {
              $style=$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id.' .in_element_content');
          }
        
          $content='<div class="in_element_content in_element_content_social_sprinters" '.$style.'>';
          wp_enqueue_script('ve_social_sprinters');
          $content.='<iframe id="ss_iframe_'.$code.'" src="'.$return.'" width="100%" scrolling="no"></iframe>'
          .'<script type="text/javascript">'
          .'jQuery(document).ready(function($) { iFrameResize(); });'
          .'</script>';
          $content.='</div>';
      } 
      
    }
    else $err_message=__('Není zadán žádný zkrácený odkaz sprinte.rs.','cms_ve');
    
    if($err_message && $vePage->edit_mode) $content='<div class="cms_error_box admin_feature">'.$err_message.'</div>';
    
    return $content;
}
