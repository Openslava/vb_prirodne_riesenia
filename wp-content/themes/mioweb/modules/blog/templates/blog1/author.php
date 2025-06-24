<?php
  get_skin_header();?>
  <div id="blog_top_panel"><div id="blog_top_panel_container"><h1><?php echo __('Autor','cms_blog').' '.get_the_author(); ?></h1></div></div>
 	<div id="blog-container">
    <div id="blog-content">
      <div class="blog-box blog-author-box">
            <div class="author_photo"><?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?></div>
            <div class="author_box_content">
                  <h2><?php echo __('O autorovi','cms_blog'); ?></h2>
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
      <?php get_blog_part( 'content', 'loop' ); ?>
      <div class="cms_clear"></div>

    </div>

      <?php get_blog_sidebar('blog'); ?>

    <div class="cms_clear"></div>
  </div>


<?php get_skin_footer(); ?>
