<?php
class mwEventCalendar {
  
function __construct(){

    add_action( 'init', array($this, 'register_post_type') );
  
}    
function register_post_type() {
  $labels = array(
    'name'               => __( 'Kalendář akci', 'cms_ve' ),
    'singular_name'      => __( 'Akce', 'cms_ve' ),
    'menu_name'          => __( 'Kalendář akcí', 'cms_ve' ),
    'name_admin_bar'     => __( 'Kalendář akcí', 'cms_ve' ),
    'add_new'            => __( 'Přidat akci', 'cms_ve' ),
    'add_new_item'       => __( 'Přidat novou událost', 'cms_ve' ),
    'new_item'           => __( 'Nová událost', 'cms_ve' ),
    'edit_item'          => __( 'Upravit událost', 'cms_ve' ),
    'view_item'          => __( 'Zobrazit událost', 'cms_ve' ),
    'all_items'          => __( 'Všechny události', 'cms_ve' ),
    'search_items'       => __( 'Hledat událost', 'cms_ve' ),
    'parent_item_colon'  => ':',
    'not_found'          => __( 'Událost nenalezena', 'cms_ve' ),
    'not_found_in_trash' => __( 'Událost nenalezena', 'cms_ve' )
  );

  $args = array(
    'labels'             => $labels,
    'public'             => false,
    'publicly_queryable' => false,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'mw_events' ),
    'capability_type'    => 'post',
    'has_archive'        => false,
    'hierarchical'       => false,
    'menu_position'      => 24,
    'supports'           => array( 'title', 'thumbnail' )
  );

  register_post_type( 'mw_event', $args );
}


}
$mw_event_calendar = new mwEventCalendar;
