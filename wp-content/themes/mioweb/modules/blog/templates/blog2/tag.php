<?php
  get_skin_header(); 
  $description=tag_description(); 
  ?>
  <div id="blog_top_panel" class="<?php if(isset($blog_module->top_panel['image'])) echo 'blog_top_panel_wbg' ?>">
    <div id="blog_top_panel_container">
      <h1><?php echo single_cat_title( '', false ); ?></h1>
      <?php 
      if($description) {
          echo '<div class="blog_top_panel_text">'.$description.'</div>';
      } else {
          echo '<div class="blog_top_panel_subtext">'.__('Články pro štítek','cms_blog').' '.single_cat_title( '', false ).'</div>';
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
