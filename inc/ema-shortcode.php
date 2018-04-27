<?php 

defined( 'ABSPATH' ) or die( 'Blank Space' );


final class ShortEmArt {
	private static $instance = null;

	public static function get_instance() {
		if (self::$instance === null) self::$instance = new self();

		return self::$instance;
	}

	private function __construct() {
		$this->wp_hooks();
	}

	/* adding the shortcode registration hook */
	private function wp_hooks() {
        add_filter('pre_get_posts', array($this, 'set_search'), 99);

		add_shortcode('nyheter', array($this, 'shortcode'));
		// add_shortcode('nyheter', array($this, 'shortcode_callback'));
	}

	public function set_search($query) {
        if ($query->is_search) {
	        if (!$query->get('post_type')) $query->set('post_type', array('page', 'post', 'nyheter'));
    	    else $query->set('post_type', array_merge(array('nyheter'), $query->get('post_type')));
		}
	}


	public function shortcode($atts, $content = null) {
		add_action('wp_print_footer_scripts', array($this, 'add_to_footer'));

		$args = [
			'post_type' => 'nyheter',
			'posts_per_page' => 10,
			'orderby' => [
				'menu_order' => 'desc',
				'date' => 'desc'
			]
		];

		if (isset($atts['nr']) && is_numeric($atts['nr'])) $args['posts_per_page'] = $atts['nr'];

		$posts = get_posts($args);


		if (is_active_sidebar('emarticle-widget')) {
			$sidebar = '';
			ob_start();
			dynamic_sidebar( 'emarticle-widget' );
			$sidebar = ob_get_clean();
		}

		// wp_die(print_r($posts, true));
		$html = '<div style="opacity: 0;" class="em-articles-container"><ul class="em-articles-list">';

		$first = true;
		foreach($posts as $post) {
			// title

			// add custom title meta

			$title = sanitize_text_field(get_the_title($post));

			// thumbnail
			$thumbnail = esc_url(get_the_post_thumbnail_url($post, 'full'));

			// url
			$url = esc_url(get_permalink($post));

			// excerpt
			$excerpt = sanitize_text_field(get_the_excerpt($post));


			if ($first) {
				$html .= '<li class="em-articles-listitem em-articles-firstitem">';
				$html .= '<a class="em-articles-link" href="'.$url.'">';
				$html .= '<span class="em-articles-thumbnail-first" style="background-image: url(\''.$thumbnail.'\')"></span>';
				$html .= '<span class="em-articles-title">'.$title.'</span>';
				if (sizeof($excerpt) > 0) $html .= '<span class="em-articles-excerpt">'.$excerpt.'</span>';
				$html .= '</a></li>';
				if (is_active_sidebar('emarticle-widget')) $html .= '<li class="em-articles-listitem em-articles-widget"><ul class="em-articles-widget-list">'.$sidebar.'</ul></li>';
				
				$first = false;
				continue;
			}

			$html .= '<li class="em-articles-listitem">';
			$html .= '<a class="em-articles-link" href="'.$url.'">';
			$html .= '<img class="em-articles-thumbnail" src="'.$thumbnail.'">';
			$html .= '<span class="em-articles-title">'.$title.'</span>';
			if (sizeof($excerpt) > 0) $html .= '<span class="em-articles-excerpt">'.$excerpt.'</span>';
			$html .= '</a></li>';
		}
		$html .= '</ul></div>';

		return $html;
	}

	/* shortcode callback 
	   returns a list of formatted articles
	*/
	public function shortcode_callback($atts, $content = null) {
		// javascript that adds the css link after html parse is complete (for page speed reasons)
		add_action('wp_print_footer_scripts', array($this, 'add_to_footer'));

		// default args for wp_query
		$args = [
			'post_type' => 'nyheter',
			'orderby' => [
				'menu_order' => 'desc',
				'date' => 'desc'
			]
		];

		// if [articles nr=***] is set - return a set number of articles
		if (isset($atts['nr']) && is_numeric($atts['nr'])) $args['posts_per_page'] = intval($atts['nr']);

		$query = new WP_Query($args);

		// data structure for relevant data
		$p = [];
		if ($query->have_posts())
			while ($query->have_posts()) {
				$query->the_post();

				$temp = [
					'title' => get_the_title(),
					'content' => get_the_content(),
					'thumbnail' => get_the_post_thumbnail_url(),
					'excerpt' => get_the_excerpt(),
					'url' => get_permalink()
				];
				array_push($p, $temp);
			}

		// closes the wp_query loop
		wp_reset_postdata();

		// if no articles found
		if (sizeof($p) == 0)
			return '';

		// catches the data stream from sidebar function (because it echos)
		if (is_active_sidebar('emarticle-widget')) {
			$sidebar = '';
			ob_start();
			dynamic_sidebar( 'emarticle-widget' );
			$sidebar = ob_get_clean();
		}

		// article html layout (start invisible because of css is loaded in after html)
		$html = '<div style="opacity: 0;" class="em-articles-container"><ul class="em-articles-list">';

		// special rule for first article and the widget 
		if (sizeof($p) > 0) {
			$html .= '<li class="em-articles-listitem em-articles-firstitem">';
			$html .= '<a class="em-articles-link" href="'.$p[0]['url'].'">';
			$html .= '<span class="em-articles-thumbnail-first" style="background-image: url(\''.$p[0]['thumbnail'].'\')"></span>';
			$html .= '<span class="em-articles-title">'.$p[0]['title'].'</span>';
			if (sizeof($p[0]['excerpt']) > 0) $html .= '<span class="em-articles-excerpt">'.$p[0]['excerpt'].'</span>';
			$html .= '</a></li>';
			if (is_active_sidebar('emarticle-widget')) $html .= '<li class="em-articles-listitem em-articles-widget"><ul class="em-articles-widget-list">'.$sidebar.'</ul></li>';
		}

		// article number 2+++
		for ($i = 1; $i < sizeof($p); $i++) {
			$html .= '<li class="em-articles-listitem">';
			$html .= '<a class="em-articles-link" href="'.$p[$i]['url'].'">';
			$html .= '<img class="em-articles-thumbnail" src="'.$p[$i]['thumbnail'].'">';
			$html .= '<span class="em-articles-title">'.$p[$i]['title'].'</span>';
			if (sizeof($p[$i]['excerpt']) > 0) $html .= '<span class="em-articles-excerpt">'.$p[$i]['excerpt'].'</span>';
			$html .= '</a></li>';
		}

		// end of article html layout
		$html .= '</div>';

		return $html;
	}

	/* ADDING <SCRIPT> that adds <LINK stylesheet> after html parsing */
	public function add_to_footer($name) {
		echo '<script defer>(function () { var o = document.createElement("link");o.setAttribute("rel", "stylesheet");o.setAttribute("href", "'.ARTICLE_PLUGIN_URL.'assets/emart-style.css");document.head.appendChild(o); })();</script>';
		echo '<script defer>(function () { var o = document.createElement("link");o.setAttribute("rel", "stylesheet");o.setAttribute("href", "'.ARTICLE_PLUGIN_URL.'assets/emart-style-mobile.css");o.setAttribute("media", "(max-width:999px)");document.head.appendChild(o); })();</script>';
	}

}