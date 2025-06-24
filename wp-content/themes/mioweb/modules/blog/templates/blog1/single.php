<?php   
global $posts, $blog_module, $vePage; 
get_skin_header();
$blog_setting=get_option('blog_comments');
$comments=get_comments_number();

// post_detail_look
$detail_look=isset($blog_module->appearance['post_detail_look'])? $blog_module->appearance['post_detail_look'] : 1;

// article meta

$article_meta='';
if(!isset($blog_setting['hide']['date'])) $article_meta='<span class="date">'.file_get_contents(__DIR__."/images/date.svg").get_the_date('j.n. Y').'</span>';
$article_meta.='<a class="user" href="'.get_author_posts_url($post->post_author).'">'.file_get_contents(__DIR__."/images/user.svg").get_the_author_meta( 'display_name', $post->post_author).'</a>';
if(isset($blog_setting['show']['visitors'])) $article_meta.='<span class="visitors">'.file_get_contents(__DIR__."/images/visitors.svg").$blog_module->get_visit_number($post->ID).'x</span>'; 
if(isset($blog_setting['comments']['wordpress'])) $article_meta.='<a class="comments" href="'.get_comments_link().'">'.file_get_contents(__DIR__."/images/comments.svg").$comments.' '.(($comments==1)? __('Komentář','cms_blog'): (($comments>1 && $comments<5)? __('Komentáře','cms_blog') : __('Komentářů','cms_blog'))) .'</a>';    


// blog header 1
if($detail_look==1) {
  ?>
  <div id="blog_top_panel" class="single_blog_top_panel single_blog_top_panel_<?php echo $detail_look; ?>">
      <div id="blog_top_panel_container">
            <h1><?php echo get_the_title(); ?></h1>          
      </div>
  </div>
  <?php 
} 

// blog header 2
if($detail_look==2) { 
?>
<div class="single_blog_title_container" style="background-image: url('<?php echo get_the_post_thumbnail_url($post->ID); ?>');">
    <div class="single_blog_title_overlay"></div>
    <div class="single_blog_title_container_inner row_fix_width">
          <h1><?php echo get_the_title(); ?></h1>   
          <?php echo '<div class="single_title_meta">'.$article_meta.'</div>'; ?>      
    </div>
    
</div>
<?php 
} 
?>

 	<div id="blog-container">
    <div id="blog-content">
      <?php
      if($detail_look==3 || $detail_look==4 || $detail_look==5) {
        echo '<div class="single_blog_title_incontent">';
        if($detail_look!=4) echo '<h1>'.get_the_title().'</h1>';
        if ( has_post_thumbnail( $post->ID ) && $detail_look!=5) {
            echo '<div class="responsive_image single_block_article_image">';
            echo get_the_post_thumbnail( $post->ID , 'mio_columns_c1');
            if($detail_look==4) echo '<h1>'.get_the_title().'</h1>';
            echo '</div>';
        } else if($detail_look==4) echo '<h1>'.get_the_title().'</h1>';
        echo '<div class="article_meta">'.$article_meta.'<div class="cms_clear"></div></div>'; 
        echo '</div>';
      }
      ?>
      <div class="blog-box blog-singlebox article-detail">
          <?php 
          while ( have_posts() ) : the_post();
          
              // article meta inside page
              if($detail_look==1) {
                echo '<div class="article_meta">'.$article_meta.'<div class="cms_clear"></div></div>'; 
              }
              
              if(isset($blog_setting['show_share']) && isset($blog_setting['show_share']['is_saved']))
                  unset($blog_setting['show_share']['is_saved']);
              
              if(isset($blog_setting['show_share']) && !empty($blog_setting['show_share'])) {
              ?>
                  <div class="in_share_element in_share_element_1 blog_share_buttons blog_share_buttons_top">
                      <?php if(isset($blog_setting['show_share']['facebook'])) { 
                          $share=(isset($blog_setting['show_share']['facebook_share']))? 'true':'false';
                          ?>
                          <div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="<?php echo $share; ?>"></div>
                  
                      <?php 
                      } else if(isset($blog_setting['show_share']['facebook_share'])) {
                            ?>
                            <div class="fb-share-button" data-href="<?php the_permalink(); ?>" data-layout="button_count"></div>
                            <?php 
                      }   
                      if(isset($blog_setting['show_share']['twitter'])) { ?>
                          <div class="twitter-like"><a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php the_permalink(); ?>'" data-count="horizontal" data-lang="cs">Tweet</a>
                          </div>
                          <?php 
                      }
                      if(isset($blog_setting['show_share']['google'])) { ?>
                          <div class="g-like"><div class="g-plusone" data-size="medium" data-href="<?php the_permalink(); ?>"></div></div>
                          <script type="text/javascript">
                            (function() {
                              var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                              po.src = 'https://apis.google.com/js/platform.js';
                              var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                            })();
                          </script>
                      <?php } ?>
                  </div>
          <?php              
          }
          ?>
              <div class="entry_content blog_entry_content element_text_li<?php echo $blog_module->appearance['li']; ?>">
                  <?php 
                  the_content(); 
                  do_action('cms_singleloop'); 
                  ?>                 
              </div>
              
              <?php
              echo get_the_tag_list('<div class="single_tags">'.__('Tagy:','cms_blog').' ','','</div>');
              
              if(isset($blog_setting['show_share']) && !empty($blog_setting['show_share'])) {
              ?>
              <div class="in_share_element in_share_element_1 blog_share_buttons">
                  <?php if(isset($blog_setting['show_share']['facebook'])) { ?>
                      <div class="fb-like" data-href="<?php the_permalink(); ?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="<?php echo $share; ?>"></div>
              
                  <?php 
                  } else if(isset($blog_setting['show_share']['facebook_share'])) {
                        ?>
                        <div class="fb-share-button" data-href="<?php the_permalink(); ?>" data-layout="button_count"></div>
                        <?php 
                  }   
                  if(isset($blog_setting['show_share']['twitter'])) { ?>
                      <div class="twitter-like"><a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php the_permalink(); ?>'" data-count="horizontal" data-lang="cs">Tweet</a>
                      </div>
                      <?php 
                  }
                  if(isset($blog_setting['show_share']['google'])) { ?>
                      <div class="g-like"><div class="g-plusone" data-size="medium" data-href="<?php the_permalink(); ?>"></div></div>
                      <script type="text/javascript">
                        (function() {
                          var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
                          po.src = 'https://apis.google.com/js/platform.js';
                          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
                        })();
                      </script>
                  <?php } ?>
              </div>
              <?php              
              }
              
    		endwhile;
        
        if(!isset($blog_setting['hide']['autorbox'])) {
        ?>
        <div class="author-box">
            <div class="author_photo"><?php echo get_avatar( get_the_author_meta( 'ID' ), 60 ); ?></div>
            <div class="author_box_content">
                  <a class="author_name title_element_container" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php echo get_the_author(); ?></a>
                  <div class="author_box_description"><?php echo get_the_author_meta( 'description' ); ?></div>
                  <?php 
                  $web=get_the_author_meta( 'user_url' );
                  $facebook=get_the_author_meta( 'facebook' );
                  $google=get_the_author_meta( 'google' );
                  $twitter=get_the_author_meta( 'twitter' );
                  $linkedin=get_the_author_meta( 'linkedin' );
                  $youtube=get_the_author_meta( 'youtube' );
                  if($web || $facebook || $google || $twitter || $linkedin || $youtube) {
                    echo '<div class="author_box_links">';
                      if($web) echo '<a class="author_web" target="_blank" href="'.$web.'" title="'.__('Webová stránka','cms_blog').'">'.__('Webová stránka','cms_blog').'</a>';
                      if($google) echo '<a class="author_google" target="_blank" href="'.$google.'" title="'.__('Google+','cms_blog').'">'.__('Google+','cms_blog').'</a>'; 
                      if($facebook) echo '<a class="author_facebook" target="_blank" href="'.$facebook.'" title="'.__('Facebook','cms_blog').'">'.__('Facebook','cms_blog').'</a>';                         
                      if($twitter) echo '<a class="author_twitter" target="_blank" href="'.$twitter.'" title="'.__('Twitter','cms_blog').'">'.__('Twitter','cms_blog').'</a>';    
                      if($youtube) echo '<a class="author_youtube" target="_blank" href="'.$youtube.'" title="'.__('YouTube','cms_blog').'">'.__('YouTube','cms_blog').'</a>';    
                      if($linkedin) echo '<a class="author_linkedin" target="_blank" href="'.$linkedin.'" title="'.__('LinkedIn','cms_blog').'">'.__('LinkedIn','cms_blog').'</a>'; 
                    echo '</div>';
                  }
                  ?>
            </div>
            <div class="cms_clear"></div>
      </div>
      <?php 
      }   
      
      if(isset($blog_setting['content_after_post']) && $blog_setting['content_after_post']) {
        $args=array(
            'key'=>'content_after_post', 
            'option'=>'blog_comments'
        );
        echo $vePage->weditor->weditor_content($blog_setting['content_after_post'], $args);
      }
    
      if(!isset($blog_setting['hide']['related_posts'])) {
        $desc=isset($blog_setting['hide']['related_posts_text'])? false : true;
        get_related_posts($desc);
      } 
      
      ?>

      <div id="blog_comments_container"><?php $blog_module->print_blog_comments(); ?></div> 
      </div>
    </div>

      <?php get_blog_sidebar('blog'); ?>

    <div class="cms_clear"></div>
  </div>
<?php
get_skin_footer(); 
?>
