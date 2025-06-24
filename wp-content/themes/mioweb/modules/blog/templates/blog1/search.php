<?php
  get_skin_header(); ?>
  <div id="blog_top_panel"><div id="blog_top_panel_container"><h1><?php echo __('Výsledek vyhledávání','cms_blog'); ?></h1></div></div>
 	<div id="blog-container">
    <div id="blog-content">
      <?php get_blog_part( 'content', 'loop' ); ?>
      <div class="cms_clear"></div> 

    </div>

    <?php get_blog_sidebar('blog'); ?>

    <div class="cms_clear"></div>
  </div>


<?php get_skin_footer(); ?>
