<?php
/**
* @package Emarticle
*/

/*
Plugin Name: EM Article
Description: Articles
Version: 1.0.3
*/

defined( 'ABSPATH' ) or die( 'Blank Space' );

// constant for plugin location
define( 'ARTICLE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once 'emregart.php';
require_once 'emshortart.php';

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'article_flush_rewrites' );

/* flusing rewrite rules when activating the plugin */
function article_flush_rewrites() {
	// call your CPT registration function here (it should also be hooked into 'init')
	$regemart = RegEmArt::get_instance();
	$regemart->register_post_type();

	flush_rewrite_rules();
}

add_action('plugins_loaded', 'init_emarticle');

function init_emarticle() {
	RegEmArt::get_instance();
	ShortEmArt::get_instance();
}
