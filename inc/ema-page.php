<?php 
/*
	Populating Article Page - Admin Version
*/

defined( 'ABSPATH' ) or die( 'Blank Space' );


final class EmArtPager {
	/* SINGLETON */
	private static $instance = null;
	public static function get_instance() {

		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->public_wp_hooks();

		if (is_admin()) $this->wp_hooks();
	}

	private function wp_hooks() {
		// meta boxes for articles 
		add_action('add_meta_boxes', array($this, 'add_meta_desc'));
		add_action('add_meta_boxes', array($this, 'add_meta_title'));
		add_action('add_meta_boxes', array($this, 'add_meta_struc'));

		// save meta for this type (becasue of nonce/save function checks for a specific set of data)
		add_action('save_post', array($this, 'savemeta'));
	}

	private function public_wp_hooks() {
		// custom title, meta descriptiong
		add_action('wp_head', array($this, 'add_head'));

		// structured data
		add_action('wp_footer', array($this, 'add_footer'));
	}

	public function add_meta_desc() {
		add_meta_box('em-article-description', 'Meta Description', array($this, 'add_meta_desc_callback'), 'article');
	}

	public function add_meta_desc_callback() {
		wp_nonce_field( basename(__FILE__), 'em_nonce' );
		echo '<textarea style="width: 100%; height: 5em;" name="emartdesc">'.$this->getmeta('emartdesc').'</textarea>';
	}

	/* <TITLE> */
	public function add_meta_title() {
		add_meta_box('em-article-title', 'Custom Title', array($this, 'add_meta_title_callback'), 'article');
	}
	public function add_meta_title_callback() {
		echo '<input type="text" style="width: 100%" name="emarttitle" value="'.$this->getmeta('emarttitle').'">';
	}

	/* <SCRIPT> structured data */
	public function add_meta_struc() {
		add_meta_box('em-article-struc', 'Structured Data (no script tags)', array($this, 'add_meta_struc_callback'), 'article');
	}
	public function add_meta_struc_callback() {
		// if data is json-eligble - then pretty print it
		if (json_decode($this->getmeta('emartstruc'))) 	echo '<textarea style="width: 100%; height: 20em;" name="emartstruc">'.json_encode(json_decode($this->getmeta('emartstruc')), JSON_PRETTY_PRINT).'</textarea>';
		else 											echo '<textarea style="width: 100%; height: 20em;" name="emartstruc">'.$this->getmeta('emartstruc').'</textarea>';
	}

	/* helper function for getting meta data */
	private function getmeta($m) {
		global $post;
		$meta = get_post_meta($post->ID, $m);

		if (isset($meta[0])) 	return $meta[0];
		else 					return ''; // '' -> false
	}

	/* SAVE FUNCTION FOR ARTICLES */
	public function savemeta($postid) {

		// security - user permissions and nonce
		if ( ! current_user_can( 'edit_posts' )) return;
		if ( ! isset($_POST['em_nonce'])) return;
		if ( ! wp_verify_nonce( $_POST['em_nonce'], basename(__FILE__))) return;

		// meta data to save
		$meta = ['emartdesc', 'emarttitle', 'emartstruc'];

		foreach ($meta as $value)
			if (isset($_POST[$value])) update_post_meta($postid, $value, sanitize_text_field($_POST[$value]));
	}

	/* ADDING APPROPIATE META INFO TO WP_HEAD */
	public function add_head() {
		global $post;
		if ($post && $post->post_type == 'article') {
			echo $this->getmeta('emartdesc') ? '<meta name="description" content="'.$this->getmeta('emartdesc').'">' : '';
			echo $this->getmeta('emarttitle') ? '<title>'.$this->getmeta('emarttitle').'</title>' : '';
		}
	}

	/* ADDING APPROPIATE META INFO TO WP_FOOTER */
	public function add_footer() {
		global $post;
		if ($post && $post->post_type == 'article') echo json_decode($this->getmeta('emartstruc')) ? '<script type="application/ld+json">'.json_encode(json_decode($this->getmeta('emartstruc'))).'</script>' : '';
	}
}
