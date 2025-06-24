<?php
  get_skin_header(); ?>
  <div id="blog_top_panel" class="<?php if(isset($blog_module->top_panel['image'])) echo 'blog_top_panel_wbg' ?>"><div id="blog_top_panel_container"><h1><?php echo __('Kategorie:','cms_blog').' '.single_cat_title( '', false ); ?></h1></div></div>
 	<div id="blog-container">
    <div id="blog-content">
      <?php $description=category_description(); 
      if($description) { ?>
          <div class="blog-box blog-description-box">
              <p><?php echo $description; ?></p>
          </div>
      <?php } ?>
      <?php get_blog_part( 'content', 'loop' ); ?>
      <div class="cms_clear"></div> 

    </div>

      <?php get_blog_sidebar('blog'); ?>

    <div class="cms_clear"></div>
  </div>


<?php get_skin_footer(); ?>
