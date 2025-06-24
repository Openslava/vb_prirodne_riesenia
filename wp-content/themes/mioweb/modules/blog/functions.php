<?php
function blog_post_nav() {
	global $wp_query;
	if ( $wp_query->max_num_pages < 2 )
		return;
	?>
	<nav class="navigation paging-navigation blog-box" role="navigation">
		<div class="nav-links">

			<?php if ( get_next_posts_link() ) : ?>
			<div class="nav-previous"><?php next_posts_link( '<span class="meta-nav">&larr;</span> '.__( 'Starší příspěvky', 'cms_blog' ) ); ?></div>
			<?php endif; ?>

			<?php if ( get_previous_posts_link() ) : ?>
			<div class="nav-next"><?php previous_posts_link( __( 'Novější příspěvky', 'cms_blog' ).' <span class="meta-nav">&rarr;</span>' ); ?></div>
			<?php endif; ?>
      <div class="cms_clear"></div>
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->
	<?php
}

//related_posts function
/*
function print_related_posts($related_posts, $first=false, $exclude=array()) {
              $i=1;
              while( $related_posts->have_posts() ) {  
                  $related_posts->the_post(); 
                  $thumb=(has_post_thumbnail())? true : false; 
                  ?>                     
                  <div class="related_post col col-three <?php if($first && $i==1) echo 'col-first'; ?>">  
                      <a class="related_post_thumb" title="<?php the_title()?>" href="<?php the_permalink()?>"><?php if($thumb) the_post_thumbnail('blog_element'); else echo '<img src="'.BLOG_DIR.'images/blank_image.png" alt="" />'; ?></a>   
                      <h3><a class="related_post_title" title="<?php the_title()?>" href="<?php the_permalink()?>"><?php the_title(); ?></a></h3>                         
                  </div>                  
                  <?php 
                  $exclude[]=get_the_ID();
                  $i++;
              }
              return $exclude;
}
function get_related_posts() {
    global $post; 
          ?>
          <div class="related_posts">
          <h2><?php echo __('Podobné články','cms_blog'); ?></h2>
          <?php  
          $tags = wp_get_post_tags($post->ID);
          $exclude=array( $post->ID );
          $max=3; 
          $first=true;
          if ($tags) {  
              $tag_ids = array();  
              foreach($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;  
              $args=array(  
                  'tag__in' => $tag_ids,  
                  'post__not_in' => $exclude,  
                  'posts_per_page'=>$max, // Number of related posts to display.  
                  'post_type'=>'post'
              );  
              $posts = new wp_query( $args );  
              if($posts->found_posts){
                  $exclude=print_related_posts($posts,$first,$exclude);
                  $max-=$posts->found_posts; 
                  $first=false;   
              }            
          } 

          if($max>0 && $categories = get_the_category( $post->ID )) {
              $categories = get_the_category( $post->ID );
              foreach($categories as $category) $cat_ids[] = $category->term_id; 
              $first_cat = $categories[0]->cat_ID;
              $args = array(
                  'category__in' => $cat_ids,
                  'post__not_in' => $exclude,
                  'posts_per_page' => $max
              );
              $posts = new wp_query( $args );
              print_related_posts($posts,$first); 
          }
          ?>
          <div class="cms_clear"></div>
          </div>
          <?php 
          wp_reset_query(); 
}
*/
function get_related_posts($desc=true) {
    global $post; 
		
		$tags = wp_get_post_tags($post->ID);
		$exclude=array( $post->ID );
		$max=3; 
		$articles=array();
		
		// from same tag
		if ($tags) {  
				$tag_ids = array();  
				
				foreach($tags as $individual_tag) 
						$tag_ids[] = $individual_tag->term_id;  
						
				$args=array(  
						'tag__in' => $tag_ids,  
						'post__not_in' => $exclude,  
						'posts_per_page'=>$max, // Number of related posts to display.  
						'post_type'=>'post'
				);  
				
				$posts = new wp_query( $args ); 
				
				if($posts->found_posts){
						foreach($posts->posts as $article) {
								$exclude[]=$article->ID;
								$articles[]=$article;
						}
						$max-=$posts->found_posts; 
				}      
		} 
		
		// from same category
		if($max>0 && $categories = get_the_category( $post->ID )) {
			
				foreach($categories as $category) 
					$cat_ids[] = $category->term_id; 
				
				$args = array(
						'category__in' => $cat_ids,
						'post__not_in' => $exclude,
						'posts_per_page' => $max
				);
				
				$posts = new wp_query( $args );
				
				if($posts->found_posts){
						foreach($posts->posts as $article) {
								$exclude[]=$article->ID;
								$articles[]=$article;
						}
						$max-=$posts->found_posts; 
				}  
	
		}
		
		// most readed
		if($max>0) {

				$args = array(
						'post__not_in' => $exclude,
						'posts_per_page' => $max
				);
				
				$posts = new wp_query( $args );
				
				if($posts->found_posts){
						foreach($posts->posts as $article) {
								$articles[]=$article;
						}
				}  
	
		}
		
		if(count($articles)) {
				?>
				<div class="related_posts">
				<div class="related_posts_title title_element_container"><?php echo __('Podobné články','cms_blog'); ?></div>
				<?php print_related_posts($articles,$desc); ?>
				<div class="cms_clear"></div>
				</div>
				<?php       
		}    
}
function print_related_posts($related_posts,$desc) {
		$i=1;
		
		foreach( $related_posts as $post ) {  
				$thumb=(has_post_thumbnail($post->ID))? true : false; 
				?>                     
				<div class="related_post col col-three <?php if($i==1) echo 'col-first'; ?>">  
						<a class="related_post_thumb <?php if(!$thumb) echo 'related_post_nothumb'; ?>" title="<?php $post->post_title; ?>" href="<?php the_permalink($post->ID)?>"><?php if($thumb) echo get_the_post_thumbnail($post->ID,'mio_columns_3'); ?></a>   
						<a class="related_post_title title_element_container" title="<?php $post->post_title; ?>" href="<?php the_permalink($post->ID)?>"><?php echo $post->post_title; ?></a>
						<?php 
						if($desc){
						?>
								<p><?php 
								if($post->excerpt) $excerpt=$post->excerpt;
								else $excerpt=wp_strip_all_tags(strip_shortcodes($post->post_content));
								echo wp_trim_words($excerpt,12);  ?></p>   
						<?php 
						}
						?>                      
				</div>                  
				<?php 
				$i++;
		}
              
}

function get_blog_directory() {
    global $blog_module;
    return $blog_module->template_directory.$blog_module->templates[$blog_module->template]['folder'].'/';
}
function get_blog_url() {
    global $blog_module;
    return $blog_module->template_path.$blog_module->templates[$blog_module->template]['folder'].'/';
}
add_filter( 'home_template', 'get_skin_home_template' );
function get_skin_home_template($template) {
    if(isset($_GET['window_editor'])) {
        $templates[] = 'window_editor.php';
        return locate_skin_template($templates);
    } else {
        $templates = array( 'home.php', 'index.php' );   
        return locate_blog_template($templates); 
    } 
}

add_filter( 'date_template', 'get_skin_date_template' );
function get_skin_date_template($template) {
    $templates = array( 'date.php' );
    return locate_blog_template($templates);
}
add_filter( 'search_template', 'get_skin_search_template' );
function get_skin_search_template($template) {
    $templates = array( 'search.php' );
    return locate_blog_template($templates);
}

add_filter( 'archive_template', 'get_skin_archive_template' );
function get_skin_archive_template($template) {
    $post_types = array_filter( (array) get_query_var( 'post_type' ) );
  	$templates = array();
    if ( count( $post_types ) == 1 ) {
        $post_type = reset( $post_types );
        $templates[] = "archive-{$post_type}.php";
    }
    $templates[] = 'archive.php';
    return locate_blog_template($templates);
}
add_filter( 'single_template', 'get_skin_single_template' );
function get_skin_single_template($template) {
    $object = get_queried_object();
    $skintemplate = get_skin_template_slug();
    $templates = array();
    if ( $skintemplate && 0 === validate_file( $skintemplate ) )
	      $templates[] = $skintemplate;
    if ( $object )
        $templates[] = "single-{$object->post_type}.php";
    $templates[] = "single.php";   
    return locate_blog_template($templates);
}
add_filter( 'author_template', 'get_skin_author_template' );
function get_skin_author_template() {
  $author = get_queried_object();
  $templates = array();
  if ( $author ) {
    $templates[] = "author-{$author->user_nicename}.php";
    $templates[] = "author-{$author->ID}.php";
  }
  $templates[] = 'author.php';
  return locate_blog_template($templates);
}

add_filter( 'category_template', 'get_skin_category_template' );
function get_skin_category_template() { 
    $category = get_queried_object();
    $templates = array();
    if ( $category ) {
        $templates[] = "category-{$category->slug}.php";
        $templates[] = "category-{$category->term_id}.php";
    }
    $templates[] = 'category.php';
    return locate_blog_template($templates);
}

add_filter( 'tag_template', 'get_skin_tag_template' );
function get_skin_tag_template() {
    $tag = get_queried_object();
    $templates = array();
    if ( $tag ) {
        $templates[] = "tag-{$tag->slug}.php";
        $templates[] = "tag-{$tag->term_id}.php";
    }
    $templates[] = 'tag.php';
    return locate_blog_template($templates);
}
add_filter( 'comments_template', 'get_skin_comments_file' );
function get_skin_comments_file() {
    return get_blog_directory().'/comments.php';
}

function get_blog_sidebar( $name = null ) {   
    $templates = array();
    if ( isset($name) ) $templates[] = "sidebar-{$name}.php";	 
	  $templates[] = 'sidebar.php';	
	  locate_blog_template($templates, true);	        
}
function get_blog_part( $slug, $name = null ) {
    $templates = array();
    $name = (string) $name;
	  if ( '' !== $name )
	        $templates[] = "{$slug}-{$name}.php";

	  $templates[] = "{$slug}.php";
	
	  locate_blog_template($templates, true, false);
}

function locate_blog_template($template_names, $load = false, $require_once = true )
{
    if ( !is_array($template_names) )
        return '';    
   
    $located = '';
   
    $skin_dir = get_blog_directory();
   
    foreach ( $template_names as $template_name ) {
        if ( !$template_name )
            continue;
        /**
         * Make possible to use different file from other modules.
         * @param $located string Located template file. Can be preserved or modified.
         * @param $template_name string Name of the searched template file.
         * @return string Value of $located, if it should be preserved or custom value.
         * @since 2016-02-19
         */
        $located = apply_filters_ref_array('mw_locate_template', array($located, $template_name));
        if (file_exists($located)) {
            break;
        } else
        if ( file_exists( $skin_dir . '/' .  $template_name) ) {
            $located =  $skin_dir . '/' . $template_name;
            break;
        }
        else if ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
            $located = STYLESHEETPATH . '/' . $template_name;
            break;
        } else if ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
            $located = TEMPLATEPATH . '/' . $template_name;
            break;
        } 
    }
   
    if ( $load && '' != $located )
        load_template( $located, $require_once );

    return $located;
}

function field_type_blog_selectpage($field, $meta, $group_id) { 
  global $cms;

  $pages = get_pages(array('post_status'=>'publish,private,draft'));
  
  $on_front=get_option( 'show_on_front');
  ?>
      <script>
      jQuery(document).ready(function($) {
          $(".cms_radio_container_<?php echo $group_id.'_'. $field['id']; ?> input").change(
          function(){ 
              var value=$(this).val();
              $(".cms_show_group_blogpage").hide();
              $(".cms_show_group_blogpage_"+value).show();
          });
      });
      </script>
      <style>
        .cms_show_group_blogpage:not(.cms_show_group_blogpage_<?php echo $on_front; ?>) {display: none;} 
      </style>
    <?php 
    foreach ($field['options'] as $key=>$option) {
        echo '<div class="cms_radio_container cms_radio_container_',$group_id,'_', $field['id'],'"><input type="radio" id="',$group_id,'_', $field['id'],'_',$key,'" name="',$group_id,'[',$field['id'],'][show_on_front]" value="',$key,'"', ($key==$on_front) ? ' checked="checked"' : '', ' />';
        echo '<label for="',$group_id,'_', $field['id'],'_',$key,'"> ',$option, '</label></div>';
    }
    echo '<div class="cms_clear"></div>';
  
  
  if(get_option( 'show_on_front' )=='page') $blog_page=get_option('page_for_posts');
  else $blog_page='';
  echo '<div class="cms_show_group_blogpage cms_show_group_blogpage_page">';
  $cms->select_page($pages, $blog_page, $group_id.'['.$field['id'].'][page_for_posts]',$group_id.'_'.$field['id'],'',' - '.__('Vyberte stránku blogu.','cms_blog').' - ');
  echo '</div>';
}

function field_type_blogselect($field, $meta, $group_id,$tagid) { 
  global $blog_module;
  
  $content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: '');
	
	if(isset($field['show'])) { ?>
		<script>
		jQuery(document).ready(function($) {
				$("#cms_image_selector_<?php echo $tagid.'_'.$field['id']; ?> a").click(
				function(){ 
						var value=$(this).attr('data-value');
						$(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>").hide();
						$(".cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_"+value).show();
				});
		});
		</script>
		<style>
			.cms_show_group_<?php echo $tagid.'_'.$field['show']; ?> {display: none;} 
			.cms_show_group_<?php echo $tagid.'_'.$field['show']; ?>_<?php echo $content ?> {display: table-row;}   
		</style>
	<?php }

  $options=array();          
  foreach($blog_module->templates as $key=>$template) {
      $options[$key]=$template['thumb'];  
  }          
  cms_generate_field_imageselect($group_id.'['.$field['id'].']',$group_id.'_'.$field['id'],$options,$content);
}

function field_type_category_select($field, $meta, $group_name, $group_id) { 
  
	$content=(isset($meta)) ? $meta : ((isset($field['content']))? $field['content']: 0);


	$items = get_categories( array('taxonomy' => 'category', 'hide_empty'=>0 )); 
	$options = array();
	$options[]= array(
				'value'=>'',
				'name'=>__('- Všechny kategorie -','cms_blog')
    );
	foreach ($items as $val) {
			$options[]= array(
				'value'=>$val->term_id,
				'name'=>$val->name
      );
	}
	$field['options']=$options;

	cms_generate_field_select(
		$group_name.'['.$field['id'].']',
		$group_id.'_'.$field['id'],
		$content, $field);
}
