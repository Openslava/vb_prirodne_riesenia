<?php
global $posts,$blog_module, $post, $vePage;

echo "<script>
jQuery(document).ready(function($) {  
	var height=0;
	var a;
	$('.same_height_blog_row').each(function(){
		$('.article', this).each(function(){
			if($(this).height()>height) height=$(this).height();
		});
		
		$('.article', this).each(function(){
			a=$(this).find('.article_body');
			a.height(a.height()+(height-$(this).height()));
		});
		height=0;
	});
});

</script>";


if(have_posts()) {
	
	echo '<div class="blog_articles_container'.(isset($blog_module->appearance['masonry'])?' mw_masonry_container':'').'">';
	
	// cols
	$cols=1;
	if($blog_module->appearance['post_look'] == 3 && !wp_is_mobile()) {
		$cols = isset($blog_module->appearance['blog_sidebar'])? 2:3;
	}
	
	// article class
	$article_class='';
	if($cols > 1) $article_class='article_col article_col_'.$cols;
	
	// mansory
	if(isset($blog_module->appearance['masonry'])) {
		echo '<div class="mw_masonry_gutter"></div>';
		$article_class.=' mw_masonry_col';
	}
	
	$i=0;
	while (have_posts()) : the_post();  
	 			$i++;
				if(($i % $cols) == 1 && $cols>1 && !isset($blog_module->appearance['masonry'])) echo '<div class="same_height_blog_row">';
  			echo cms_write_post($blog_module->appearance['post_look'], $cols, $article_class, $i);
				if(($i % $cols) == 0 && $cols>1 && !isset($blog_module->appearance['masonry'])) echo '</div>';
				
  endwhile;
	if(($i % $cols) != 0 && $cols>1 && !isset($blog_module->appearance['masonry'])) echo '</div>';
	
	echo '</div>';
} 
else if(is_search()) { ?>
      <div class="post-empty blog-box"><?php echo __('Zadanému řetězci neodpovídá žádný článek.','cms_blog'); ?></div>
    <?php } else  { ?>
      <div class="post-empty blog-box"><?php echo __('V této kategorii se nenachází žádný článek.','cms_blog'); ?></div>
    <?php
}   
do_action('cms_postloop');
wp_reset_query();
wp_reset_postdata();

blog_post_nav();


function cms_write_post($type=1, $cols=1, $article_class='', $num) {
    global $post,$blog_module;
    
    $setting=get_option('blog_comments');
		
		// article format
    $post_format=get_post_format();
    if($post_format=='quote' || $post_format=='video') $type=1;
    
		// thumbnail size
    $thumb=(has_post_thumbnail())? true : false;
    if($type==2) $thumb_size='blog_medium';
		else if($cols > 1) $thumb_size='mio_columns_c3'; 
		else $thumb_size='mio_columns_c1';  

		
		// artcile class
		if(($num % $cols) == 1) $article_class.=' article_col_first';
    
    $comments=get_comments_number();
		
		// article
		
    echo '<div id="article_'.$post->ID.'" class="article '.$article_class.' '.((!$thumb)?'article_nothumb':'').' article_type_'.$type.'">';  
    
    // video format
    if($post_format=='video') {
        preg_match( '/(<iframe.*?src="(.*?youtube.*?)".*?<\/iframe>)/', get_the_content(), $matches );
        if ( $matches ) {
          echo '<div class="video_content_fullwidth"><iframe src="'.$matches[2].'" frameborder="0" allowfullscreen></iframe></div>';
        } else {
            preg_match( '/(<iframe.*?src="(.*?vimeo.*?)".*?<\/iframe>)/', get_the_content(), $matches );
            if ( $matches ) {
              echo '<div class="video_content_fullwidth"><iframe src="'.$matches[2].'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div>';
            }
        }
    }
    //standard format    
    else if($thumb && $post_format!='quote') echo '<a href="'.get_permalink($post->ID).'" class="thumb">'.get_the_post_thumbnail($post->ID,$thumb_size).'</a>';
    
    if ( $post_format=='quote'  ) {
        $quote=get_the_content();
		    preg_match( '/<blockquote>(.*?)<\/blockquote>/', $quote, $matches );
    		if ( !empty( $matches ) ) {
    			$quote=$matches[1];
    	  }
        echo '<div class="article_body"><h2><a href="'.get_permalink().'" rel="bookmark" title="'.$quote.'"><blockquote><span>&quot;</span>'.$quote.'<span>&bdquo;</span></blockquote></a></h2></div>';    
    }
    else {
        echo '<div class="article_body">
            <h2><a href="'.get_permalink().'" rel="bookmark" title="'.get_the_title().'">'.get_the_title().'</a></h2>         
            <p class="excerpt">'.get_the_excerpt().'</p>';  
             
						
						// article button
						if(isset($blog_module->appearance['show_button'])) 
								echo '<a class="ve_content_button ve_content_button_1 article_button_more" href="'.get_permalink().'">'.__('Celý článek','cms_blog').'</a>';
						
								if(isset($setting['show_share_list']['facebook'])) { 
		                  $share=(isset($setting['show_share_list']['facebook_share']))? 'true':'false';
		                  ?>
		                  <div class="fb-like" data-href="<?php the_permalink($post->ID); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="<?php echo $share; ?>"></div>
		                  <?php 
		            } else if(isset($setting['show_share_list']['facebook_share'])) {
		                  ?>
		                  <div class="fb-share-button" data-href="<?php the_permalink($post->ID); ?>" data-layout="button_count"></div>
		                  <?php 
		            }  
						
        echo '</div>'; 
        echo '<div class="cms_clear"></div>';
    }
    echo '<div class="article_meta">';
        if(!isset($setting['hide']['date'])) echo '<span class="date">'.file_get_contents(__DIR__."/images/date.svg").get_the_date('j.n. Y').'</span>';
        echo '<a class="user" href="'.get_author_posts_url($post->post_author).'">'.file_get_contents(__DIR__."/images/user.svg").get_the_author().'</a>';
        if(isset($setting['show']['visitors'])) echo '<span class="visitors">'.file_get_contents(__DIR__."/images/visitors.svg").$blog_module->get_visit_number($post->ID).'x</span>';
        if(isset($setting['comments']['wordpress'])) echo '<a class="comments" href="'.get_comments_link().'">'.file_get_contents(__DIR__."/images/comments.svg").$comments.' <span>'.(($comments==1)? __('Komentář','cms_blog'): (($comments>1 && $comments<5)? __('Komentáře','cms_blog') : __('Komentářů','cms_blog'))).'</span></a>';
        echo '<div class="cms_clear"></div>
    </div>';  
    echo $blog_module->edit_post_bar($post->ID);       
    echo '</div>';
    //return $content;  
}
