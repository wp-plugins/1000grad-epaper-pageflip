<?php

/*
  Plugin Name: 1000grad ePaper pageflip
  Plugin URI:
  Version: v1.2.1
  Author: 1000Grad Digital Leipzig
  Description: This plugin is deprecated! Please install our new one: 1000°ePaper!
 */


error_reporting(0);

require_once 'php/class_epaper_db.php';
require_once 'php/class_epaper_lib.php';


if ( class_exists( 'EPaper_db' ) ) {
    $ep_db = new EPaper_db();
}

// creating db on activation of plugin
if ( isset( $ep_db ) ) {
    add_action( "activate_1000grad-epaper-pageflip/1000grad-epaper-pageflip.php", array( &$ep_db, "create_table" ) );
}

/*
 * *****************************************************************************
 */


if ( class_exists( 'EPaper_lib' ) ) {
    $ep_lib = new EPaper_lib();
}

function generateMainNavi()
{
    global $ep_lib;

    if ( !isset( $ep_lib ) ) {
        return;
    }

    $ep_plugin_page = add_menu_page( '1000 ePaper', '1000 ePaper', 9, "epaperManager", array( &$ep_lib, 'init' ) );
    
    // adding js and css only to plugin admin page
    add_action( "admin_print_scripts-$ep_plugin_page", array( &$ep_lib, 'enqueue_js_admin' ) );
    add_action( "admin_print_styles-$ep_plugin_page", array( &$ep_lib, 'enqueue_css_admin' ) );
    
    //adding css and javscript to post and edit article menu
    add_action('admin_enqueue_scripts',array( &$ep_lib, 'enqueue_js_admin_edit_post' ));

}

if ( isset( $ep_lib ) ) {

    // Actions

    /*
     * add scripts and styles to queue
     */

    // load scripts and styles only after their requirement on pages
    // 'the_posts' hook gets triggered before 'wp_head' hook
    add_filter( 'the_posts', array( &$ep_lib, 'conditionally_add_scripts_and_styles' ) );

   # add_action( 'wp_print_scripts', array( &$ep_lib, 'enqueue_js' ) );
    add_action( 'admin_print_styles', array( &$ep_lib, 'enqueue_admin_css' ) );
    #add_action( 'wp_print_styles', array( &$ep_lib, 'enqueue_css' ) );


    add_action( 'admin_menu', 'generateMainNavi' );

    // add_action( "admin_enqueue_scripts", array( &$ep_lib, 'includeStylesheet' ), 10 );
    // add metabox to editor
    add_action( 'add_meta_boxes', array( &$ep_lib, 'addEpaperMetaBox' ) );

    // Filters


    /*
     * add shortcode option to editor
     */

    add_shortcode( 'epaper', array( &$ep_lib, 'epaperShortcode' ) );
}

