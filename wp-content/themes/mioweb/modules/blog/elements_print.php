<?php

// Recent posts

function ve_element_recent_posts($element, $css_id) {
    global $vePage;

    
    $bstyle=(isset($element['config']['max_width'])) ?$vePage->print_styles(array('max-width'=>$element['config']['max_width']),$css_id." .in_element_content"):"";

    $content=$vePage->print_styles_array(array(
            array(
                'styles'=>array('font'=>$element['style']['font']),
                'element'=>"#content ".$css_id." h3 a",
            ),
            array(
                'styles'=>isset($element['style']['font_text'])? array('font'=>$element['style']['font_text']):'',
                'element'=>"#content ".$css_id.", #content ".$css_id." .blog_recent_post a",
            ),
        ));
    
    $content.='<div class="in_element_content in_recent_posts_element in_recent_posts_element_'.$element['style']['style'].(!isset($element['style']['show']['images'])?' in_recent_posts_element_noimg':'').'" '.$bstyle.'>';
    $number=($element['style']['number'])? $element['style']['number']: 3;
    $excerpt_words=(isset($element['style']['excerpt_words']) && $element['style']['excerpt_words'])? $element['style']['excerpt_words']: 17;
    
    $category='';
    if(isset($element['style']['category'])) $category=$element['style']['category'];

    if (isset($element['style']['type']) && $element['style']['type'] =='most_viewed_posts') {
        $args = array(
            'posts_per_page' => $number,
            'post_type' => 'post',
            'category' => $category,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
            'meta_key' => 'mioweb_post_visited',
            'orderby' => 'meta_value_num',
            'order' => 'DESC',
        );
    } else {
        $args = array(
            'posts_per_page' => $number,
            'post_type' => 'post',
            'category' => $category,
            'post_status' => 'publish',
            'ignore_sticky_posts' => true,
        );
    }

    $rposts = get_posts($args);
    
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
    
    $thumb_size='mio_columns_'.$cols;
    
    $post_rows = array_chunk( $rposts, $cols );
    
    foreach ( $post_rows as $rposts) {
    
    $content .= '<div class="mw_element_row">'; 

    foreach ( $rposts as $rpost) {
             
        if($element['style']['style']==4) {
            $content.='<div class="blog_recent_post"><a href="'.get_permalink($rpost->ID).'" title="'.esc_attr( $rpost->post_title).'">'.esc_attr( $rpost->post_title).'</a></div>';
        } else {
            if(!isset($element['style']['show']['images'])) {    
                $thumb=(has_post_thumbnail($rpost->ID))? get_the_post_thumbnail($rpost->ID,$thumb_size) : '<img src="'.BLOG_DIR.'images/blank_image.png" alt="'.esc_attr( $rpost->post_title).'" />';
                $thumb='<a class="recent_post_thumb" href="'.get_permalink($rpost->ID).'" title="'.esc_attr( $rpost->post_title).'">'.$thumb.'</a>';
            } else $thumb='';
            if(!isset($element['style']['show']['excerpt'])) {       
                $excerpt=($rpost->post_excerpt)? $rpost->post_excerpt : do_shortcode(stripslashes($rpost->post_content));
                $excerpt=($excerpt)? '<p class="recent_post_excerpt">'.wp_trim_words($excerpt,$excerpt_words).'</p>' : '';
            } else $excerpt='';
            
            $but_text=(isset($element['style']['but_text']))? $element['style']['but_text'] : __('Celý článek','cms_blog');
            
            $content.='<div class="elcol col-'.$element['style']['cols'].'">
                '.$thumb.'
                <div class="recent_post_content">
                    <h3><a href="'.get_permalink($rpost->ID).'" title="'.esc_attr( $rpost->post_title).'">'.esc_attr( $rpost->post_title).'</a></h3>
                    '.$excerpt.'
                    '.((!isset($element['style']['show']['more']))? '<a class="recent_post_more" href="'.get_permalink($rpost->ID).'">'.$but_text.'</a>':'').' 
                </div>
                <div class="cms_clear"></div>
            </div>';
        }
        
    }
    $content.='<div class="cms_clear"></div></div>';
    }


    $content.='</div>';
    return $content;
}
