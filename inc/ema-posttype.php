<?php 

defined( 'ABSPATH' ) or die( 'Blank Space' );


require_once 'ema-page.php';

/**
 * 
 */
final class RegEmArt {
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}

	public function wp_hooks() {
		/* registering the new post type article */
		add_action('init', array($this, 'register_post_type'));

		// adding menu order column to article posts admin page
		add_filter('manage_nyheter_posts_columns', array($this, 'my_columns'));
		add_action('manage_nyheter_posts_custom_column',  array($this, 'my_show_columns'));
		add_filter( 'manage_edit-nyheter_sortable_columns', array($this, 'sort_column'));

		add_filter('emtheme_sitemap_post_type', array($this, 'add_sitemap'));

		// registering widget area
		add_action('widgets_init', array($this, 'register_widget'));

		// activating feature image for posts (theme setting)
		add_theme_support( 'post-thumbnails' );

		// init the article page admin version
		EmArtPager::get_instance();
	}

	public function add_sitemap($data) {

		array_push($data, 'article');

		return $data;
	}

	/**
	 * Registers a new post type
	 * @uses $wp_post_types Inserts new post type object into the list
	 *
	 * @param string  Post type key, must not exceed 20 characters
	 * @param array|string  See optional args description above.
	 * @return object|WP_Error the registered post type object, or an error object
	 */
	public function register_post_type() {
		
		$labels = array(
			'name'               => __( 'Nyheter', 'text-domain' ),
			'singular_name'      => __( 'Nyhet', 'text-domain' ),
			'add_new'            => _x( 'Add New Nyhet', 'text-domain', 'text-domain' ),
			'add_new_item'       => __( 'Add New Nyhet', 'text-domain' ),
			'edit_item'          => __( 'Edit Nyhet', 'text-domain' ),
			'new_item'           => __( 'New Nyhet', 'text-domain' ),
			'view_item'          => __( 'View Nyhet', 'text-domain' ),
			'search_items'       => __( 'Search Nyheter', 'text-domain' ),
			'not_found'          => __( 'No Nyheter found', 'text-domain' ),
			'not_found_in_trash' => __( 'No Nyheter found in Trash', 'text-domain' ),
			'parent_item_colon'  => __( 'Parent Nyhet:', 'text-domain' ),
			'menu_name'          => __( 'Nyheter', 'text-domain' ),
		);
	
		$args = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'description'         => 'description',
			'taxonomies'          => array('category'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 26,
			'menu_icon' 		  => '',
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'supports'            => array(
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				// 'custom-fields',
				// 'trackbacks',
				// 'comments',
				'revisions',
				'page-attributes',
				'post-formats'
			),
		);
	
		register_post_type('nyheter', $args);
		// add_post_type_support( 'nyheter', 'post-formats');
	}

	/* adding order column and its name */
	public function my_columns($columns) {
	    $columns['menu_order'] = 'Order';
	    return $columns;
	}

	/* filter for adding menu order to page columns */
	public function my_show_columns($name) {
		if ($name == 'menu_order') {
		    global $post;
            echo $post->menu_order;
		}
	}

	/* hook function for front-end ordering by "order" */
	public function sort_column($columns) {
		$columns['menu_order'] = 'menu_order';
		return $columns;
	}

	/* register a widget */
	public function register_widget() {
		register_sidebar(array( 
			'name' => 'Nyhet Plugin',
			'id' => 'emarticle-widget',
			'description' => 'If active, widget will be nyhet #2 in the nyhet\'s list.'
		));
	}
}