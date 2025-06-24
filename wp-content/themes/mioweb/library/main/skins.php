<?php
function get_skin_directory() {
  return get_template_directory().'/skin/';
}
function get_skin_url() {
  return get_bloginfo('template_url').'/skin/';
}
function get_skin_header($name="") {
   $templates = array();
   if ( isset($name) ) $templates[] = "header-{$name}.php";	 
	 $templates[] = 'header.php';	
	 locate_skin_template($templates, true);
}
function get_skin_footer($name="") {
   $templates = array();
   if ( isset($name) ) $templates[] = "footer-{$name}.php";	 
	 $templates[] = 'footer.php';	
	 locate_skin_template($templates, true);	
}
function get_skin_sidebar( $name = null ) {   
    $templates = array();
    if ( isset($name) ) $templates[] = "sidebar-{$name}.php";	 
	  $templates[] = 'sidebar.php';	
	  locate_skin_template($templates, true);	        
}
function get_skin_template_part( $slug, $name = null ) {
    do_action( "get_template_part_{$slug}", $slug, $name );
    $templates = array();
    if ( isset($name) )
        $templates[] = "{$slug}-{$name}.php";
        $templates[] = "{$slug}.php";
    locate_skin_template($templates, true);	
}

// get file name of current post template
function get_skin_template_slug( $post_id = null ) {
    $post = get_post( $post_id );
	  $template = get_post_meta( $post->ID, 'custom_skin_template', true );
    if ( ! $template || 'default' == $template )
	      return '';
	  return $template;
}

function get_skin_stylesheet() {
  echo get_skin_url()."style.css";
}

function locate_skin_template($template_names, $load = false, $require_once = true )
{
    if ( !is_array($template_names) )
        return '';

    $skin_dir = get_skin_directory();

    $located = '';
    $template_name = ''; //For the "after" hook safety. $template_names can be empty.
    foreach ( $template_names as $template_name ) {
        if ( !$template_name )
            continue;
        /**
         * Make possible to use template from other modules. Their definition will have priority.
         * @param $located string Located template file. Can be preserved or modified.
         * @param $template_name string Name of the searched template file.
         * @return string Value of $located, if it should be preserved or custom value.
         * @since 2016-02-19
         */
        $located = apply_filters_ref_array('mw_locate_template', array($located, $template_name));
        if (file_exists($located)) {
            break;
        } elseif (file_exists( $skin_dir . '/' .  $template_name) ) {
            $located =  $skin_dir . '/' . $template_name;
            break;
        } elseif ( file_exists(STYLESHEETPATH . '/' . $template_name)) {
            $located = STYLESHEETPATH . '/' . $template_name;
            break;
        } elseif ( file_exists(TEMPLATEPATH . '/' . $template_name) ) {
            $located = TEMPLATEPATH . '/' . $template_name;
            break;
        }
    }

    /**
     * Make possible to modify selected template by other modules after determination of all possible files.
     * @param $located string Located template file. Can be preserved or modified.
     * @param $template_name string Name of the searched template file.
     * @return string Value of $located, if it should be preserved or custom value.
     * @since 2016-02-19
     */
    $located = apply_filters_ref_array('mw_locate_template_after', array($located, $template_name));

    if ( $load && '' != $located )
        load_template( $located, $require_once );
    
    return $located;
}

add_filter( 'index_template', 'get_skin_index_template' );
function get_skin_index_template($template) {
    $templates = array( 'index.php' );
    return locate_skin_template($templates);
}
add_filter( 'front_page_template', 'get_skin_front_page_template' );
function get_skin_front_page_template($template) {
    $templates = array( 'front-page.php' );
    return locate_skin_template($templates);
}
add_filter( '404_template', 'get_skin_404_template' );
function get_skin_404_template($template) {
    $templates = array( '404.php' );
    return locate_skin_template($templates);
}/**/
add_filter( 'page_template', 'get_skin_page_template' );
function get_skin_page_template() {
	  $id = get_queried_object_id();
	  $template = get_page_template_slug();
	  $skintemplate = get_skin_template_slug();
	  $pagename = get_query_var('pagename');
	
	  if ( ! $pagename && $id ) {
	        $post = get_queried_object();
	        $pagename = $post->post_name;
	  }
	
	  $templates = array();
    if(isset($_GET['window_editor'])) {
        $templates[] = 'window_editor.php';
    }
	  if ( $skintemplate && 0 === validate_file( $skintemplate ) )
	                $templates[] = $skintemplate;
	  if ( $template && 0 === validate_file( $template ) )
	                $templates[] = $template;
	  if ( $pagename )
	                $templates[] = "page-$pagename.php";
	  if ( $id )
	                $templates[] = "page-$id.php";
	  $templates[] = 'page.php';
    
    $templates = apply_filters_ref_array('mw_get_page_template', array($templates,$id));
	
	  return locate_skin_template($templates);
}

add_filter( 'taxonomy_template', 'get_skin_taxonomy_template' );
function get_skin_taxonomy_template() {  
    $term = get_queried_object();
    $templates = array();
    if ( $term ) {
        $taxonomy = $term->taxonomy;
        $templates[] = "taxonomy-$taxonomy-{$term->slug}.php";
        $templates[] = "taxonomy-$taxonomy.php";
    }
    $templates[] = 'taxonomy.php';
    return locate_skin_template($templates);
}
add_filter( 'attachment_template', 'get_skin_attachment_template' );
function get_skin_attachment_template() {
    global $posts;
    $templates=array('attachment.php');
    return locate_skin_template($templates);
}   

