<?php
  get_skin_header();
  $desc=get_the_author_meta( 'description' );
  
  ?>
  <div id="blog_top_panel">
    <div id="blog_top_panel_container">
      <div class="blog_top_author_title">
          <?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?>
          <small><?php echo __('Články autora','cms_blog'); ?></small>
          <h1><?php echo get_the_author(); ?></h1>
      </div>
      <?php 
      if($desc) echo '<div class="blog_top_author_desc">'.$desc.'</div>';
      
      $web=get_the_author_meta( 'user_url' );
      $facebook=get_the_author_meta( 'facebook' );
      $google=get_the_author_meta( 'google' );
      $twitter=get_the_author_meta( 'twitter' );
      $linkedin=get_the_author_meta( 'linkedin' );
      $youtube=get_the_author_meta( 'youtube' );
      if($web || $facebook || $google || $twitter || $linkedin || $youtube) {
        echo '<div class="blog_top_author_links">';
          if($web) echo '<a class="author_web" target="_blank" href="'.$web.'" title="'.__('Webová stránka','cms_blog').'">'.file_get_contents(__DIR__.'/images/www.svg', true).'</a>';
          if($google) echo '<a class="author_google" target="_blank" href="'.$google.'" title="'.__('Google+','cms_blog').'">'.file_get_contents(__DIR__.'/images/google_plus.svg', true).'</a>'; 
          if($facebook) echo '<a class="author_facebook" target="_blank" href="'.$facebook.'" title="'.__('Facebook','cms_blog').'">'.file_get_contents(__DIR__.'/images/facebook.svg', true).'</a>';                         
          if($twitter) echo '<a class="author_twitter" target="_blank" href="'.$twitter.'" title="'.__('Twitter','cms_blog').'">'.file_get_contents(__DIR__.'/images/twitter.svg', true).'</a>';    
          if($youtube) echo '<a class="author_youtube" target="_blank" href="'.$youtube.'" title="'.__('YouTube','cms_blog').'">'.file_get_contents(__DIR__.'/images/youtube.svg', true).'</a>';    
          if($linkedin) echo '<a class="author_linkedin" target="_blank" href="'.$linkedin.'" title="'.__('LinkedIn','cms_blog').'">'.file_get_contents(__DIR__.'/images/linkedin.svg', true).'</a>'; 
        echo '</div>';
      }
      ?>
    </div>
  </div>
 	<div id="blog-container">
    <div id="blog-content">
      
      <?php get_blog_part( 'content', 'loop' ); ?>
      <div class="cms_clear"></div>

    </div>

      <?php get_blog_sidebar('blog'); ?>

    <div class="cms_clear"></div>
  </div>


<?php get_skin_footer(); ?>
