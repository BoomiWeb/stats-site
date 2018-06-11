<?php
/**
 * Theme functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @package WordPress
 * @subpackage boomi-trust
 * @since boomi-trust 1.0.0
 */

/**
 * Set our global variables for theme options.
 *
 * @since boomi-trust 1.0.0
 */
if (!isset($boomi_trust_theme_options))
	$boomi_trust_theme_options=array('option_name' => 'boomi_trust_theme_options');

if (!isset($boomi_trust_theme_options_tabs))
	$boomi_trust_theme_options_tabs=array();

if (!isset($boomi_trust_theme_options_hooks))
	$boomi_trust_theme_options_hooks=array();

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 *
 * @since boomi-trust 1.0.0
 */
function boomi_trust_theme_setup() {
	// Set the content width based on the theme's design and stylesheet //
	$GLOBALS['content_width']=apply_filters('boomi-trust-content-width', 1200);

	/**
	 * add our theme support options
	 */
	$custom_header_args=array(
		'width' => 163,
		'height' => 76,
		'flex-width' => true,
		'flex-height' => true
	);

	$custom_background_args=array(
		'deafult-color' => 'ffffff'
	);

	add_theme_support('automatic-feed-links');
	add_theme_support('custom-header', $custom_header_args);
	add_theme_support('custom-background', $custom_background_args);
	add_theme_support('menus');
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');

	/**
	 * add our image size(s)
	 */
	add_image_size('boomi-trust-navbar-logo', 163, 100, true);

	/**
	 * include bootstrap nav walker
	 */
	include_once(get_template_directory().'/inc/wp_bootstrap_navwalker.php');

	/**
	 * include bootstrap mobile nav walker
	 */
	include_once(get_template_directory().'/inc/mobile_nav_walker.php');

	/**
	 * include our customizer functions
	 */
	include_once(get_template_directory().'/inc/customizer.php');

	/**
	 * include theme meta page
	 * allows users to hook and filter into the default meta tags in the header
	 */
	include_once(get_template_directory().'/inc/theme-meta.php');
	
	// register our navigation area
	register_nav_menus( array(
		'primary' => __('Primary Menu','boomi-trust'),
	) );

	/**
	 * This theme styles the visual editor to resemble the theme style
	 */
	add_editor_style('inc/css/editor-style.css');

}
add_action('after_setup_theme','boomi_trust_theme_setup');

/**
 * Enqueue scripts and styles.
 *
 * @since boomi-trust 1.0.0
 */
function boomi_trust_theme_scripts() {
	global $wp_scripts;

	// enqueue our scripts for bootstrap, slider and theme
	wp_enqueue_script('jquery');
	wp_enqueue_script('bootstrap-script', get_template_directory_uri().'/inc/js/bootstrap.js', array('jquery'), '3.3.7', true);
	wp_enqueue_script('jquery-actual-script', get_template_directory_uri().'/inc/js/jquery.actual.js', array('jquery'), '1.0.16', true);
	wp_enqueue_script('boomi-trust-theme-script', get_template_directory_uri().'/inc/js/theme.js', array('jquery'), '1.0.2', true);

	if ( is_singular() ) :
		wp_enqueue_script( 'comment-reply' );
	endif;

	/**
	 * Load our IE specific scripts for a range of older versions:
	 * <!--[if lt IE 9]> ... <![endif]-->
	 * <!--[if lte IE 8]> ... <![endif]-->
	*/
	// HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries //
	wp_register_script('html5shiv-script', get_template_directory_uri().'/inc/js/html5shiv.js', array(), '3.7.3-pre');
	wp_register_script('respond-script', get_template_directory_uri().'/inc/js/respond.js', array(), '1.4.2');

	$wp_scripts->add_data('html5shiv-script', 'conditional', 'lt IE 9');
	$wp_scripts->add_data('respond-script', 'conditional', 'lt IE 9');

	// enqueue font awesome and our main stylesheet
	wp_enqueue_style('roboto-google-font', 'https://fonts.googleapis.com/css?family=Roboto:400,500,700');
	wp_enqueue_style('bootstrap-style', get_template_directory_uri().'/inc/css/bootstrap.css', array(), '4.6.3');
	wp_enqueue_style('boomi-trust-theme-style', get_stylesheet_uri());
}
add_action('wp_enqueue_scripts','boomi_trust_theme_scripts');

function boomi_font_awesome() {
    echo '<script defer src="https://use.fontawesome.com/releases/v5.0.13/js/all.js" integrity="sha384-xymdQtn1n3lH2wcu0qhcdaOpQwyoarkgLVxC/wZ5q7h9gHtxICrpcaSUfygqZGOe" crossorigin="anonymous"></script>';
}
add_action('wp_head', 'boomi_font_awesome');

/**
 * Display an optional post thumbnail.
 *
 * Wraps the post thumbnail in an anchor element on index
 * views, or a div element when on single views.
 *
 * @since boomi-trust 1.0.0
 * @based on twentyfourteen
 *
 * @return void
*/
function boomi_trust_theme_post_thumbnail($size='full') {
	global $post;

	$html=null;
	$attr=array(
		'class' => 'img-responsive'
	);

	if (post_password_required() || !has_post_thumbnail())
		return;

	if (is_singular()) :
		$html.='<div class="post-thumbnail">';
			$html.=get_the_post_thumbnail($post->ID,$size,$attr);
		$html.='</div>';
	else :
		$html.='<a class="post-thumbnail" href="'.get_permalink($post->ID).'">';
			$html.=get_the_post_thumbnail($post->ID,$size,$attr);
		$html.='</a>';
	endif;

	$image=apply_filters('boomi_trust_theme_post_thumbnail', $html, $size, $attr);

	echo $image;
}

/**
 * Print HTML with meta information for the current post-date/time and author.
 *
 * @since boomi-trust 1.0.0
 * @based on twentyfourteen
 *
 * @return void
 */
function boomi_trust_theme_posted_on() {
	$html=null;

	if ( is_sticky() && is_home() && ! is_paged() ) :
		$html='<span class="featured-post"><span class="glyphicon glyphicon-pushpin"></span>' . __( 'Sticky', 'koksijde' ) . '</span>';
	elseif (!is_sticky()) : 	// Set up and print post meta information. -- hide date if sticky
		$html='<span class="entry-date"><span class="glyphicon glyphicon-time"></span><a href="'.get_permalink().'" rel="bookmark"><time class="entry-date" datetime="'.get_the_date('c').'">'.get_the_date().'</time></a></span>';
	else :
		$html='<span class="byline"><span class="glyphicon glyphicon-user"></span><span class="author vcard"><a class="url fn n" href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'" rel="author">'.get_the_author().'</a></span></span>';
	endif;

	echo apply_filters('boomi_trust_theme_posted_on', $html);
}

/**
 * Display navigation to next/previous set of posts when applicable.
 *
 * @since boomi-trust 1.0.0
 * @based on twentyfourteen
 *
 * @return void
 */
function boomi_trust_theme_paging_nav() {
	// Don't print empty markup if there's only one page.
	if ( $GLOBALS['wp_query']->max_num_pages < 2 ) {
		return;
	}

	$html=null;
	$paged        = get_query_var( 'paged' ) ? intval( get_query_var( 'paged' ) ) : 1;
	$pagenum_link = html_entity_decode( get_pagenum_link() );
	$query_args   = array();
	$url_parts    = explode( '?', $pagenum_link );

	if ( isset( $url_parts[1] ) ) {
		wp_parse_str( $url_parts[1], $query_args );
	}

	$pagenum_link = remove_query_arg( array_keys( $query_args ), esc_url($pagenum_link) );
	$pagenum_link = trailingslashit( $pagenum_link ) . '%_%';

	$format  = $GLOBALS['wp_rewrite']->using_index_permalinks() && ! strpos( $pagenum_link, 'index.php' ) ? 'index.php/' : '';
	$format .= $GLOBALS['wp_rewrite']->using_permalinks() ? user_trailingslashit( 'page/%#%', 'paged' ) : '?paged=%#%';

	// Set up paginated links.
	$links = paginate_links( array(
		'base'     => $pagenum_link,
		'format'   => $format,
		'total'    => $GLOBALS['wp_query']->max_num_pages,
		'current'  => $paged,
		'mid_size' => 1,
		'add_args' => array_map( 'urlencode', $query_args ),
		'prev_text' => __( '&laquo; Previous', 'koksijde' ),
		'next_text' => __( 'Next &raquo;', 'koksijde' ),
	) );

	if ( $links ) :
		$html.='<nav class="navigation paging-navigation" role="navigation">';
			$html.='<div class="pagination loop-pagination">';
				$html.=$links;
			$html.='</div><!-- .pagination -->';
		$html.='</nav><!-- .navigation -->';
	endif;

	echo apply_filters('boomi_trust_paging_nav', $html, $links);
}

/**
 * Display navigation to next/previous post when applicable.
 *
 * @since boomi-trust 1.0.0
 * @based on twentyfourteen
 *
 * @return void
 */
function boomi_trust_theme_post_nav() {
	$html=null;

	// Don't print empty markup if there's nowhere to navigate.
	$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
	$next     = get_adjacent_post( false, '', false );

	if ( ! $next && ! $previous )
		return;

	$html.='<nav class="navigation post-navigation" role="navigation">';
		$html.='<div class="nav-links">';

			if ( is_attachment() ) :
				$html.=previous_post_link( __('<div class="published-in"><span class="meta-nav">Published In:</span> %link</div>', 'koksijde'), '%title' );
			else :
				$html.=previous_post_link( __('<div class="prev-post"><span class="meta-nav">Previous Post:</span> %link</div>', 'koksijde'), '%title' );
				$html.=next_post_link( __('<div class="next-post"><span class="meta-nav">Next Post:</span> %link</div>', 'koksijde'), '%title' );
			endif;

		$html.='</div><!-- .nav-links -->';
	$html.='</nav><!-- .navigation -->';

	echo apply_filters('boomi_trust_post_nav', $html, $next, $previous);
}

/**
 * boomi_trust_display_meta_description function.
 *
 * a custom function to display a meta description for our site pages
 *
 * @access public
 * @return void
 */
function boomi_trust_display_meta_description() {
	global $post;

	$title=null;

	if (isset($post->post_title))
		$title=$post->post_title;

	if ( is_single() ) :
		return apply_filters('boomi_trust_display_meta_description', single_post_title('', false));
	else :
		return apply_filters('boomi_trust_display_meta_description', $title.' - '.get_bloginfo('name').' - '.get_bloginfo('description'));
	endif;

	return false;
}

/**
 * boomi_trust_header_markup function.
 * 
 * @access public
 * @return void
 */
function boomi_trust_header_markup() {
	$html=null;
	
	if (get_header_image()) :
		$html.='<div class="boomi-trust-header-image">';
			$html.='<a href="'.site_url('/').'"><img src="'.get_header_image().'" height="'.get_custom_header()->height.'" width="'.get_custom_header()->width.'" alt="" /></a>';
		$html.='</div>';
	endif;
	
	echo $html;
}

/**
 * boomi_trust_mobile_navigation_setup function.
 *
 * checks if we have an active mobile menu
 * if active mobile, sets it, if not, default to primary
 *
 * @access public
 * @return void
 */
function boomi_trust_mobile_navigation_setup() {
	$html=null;

	if (has_nav_menu('mobile')) :
		$location='mobile';
	else :
		$location='primary';
	endif;

	$location=apply_filters('boomi_trust_mobile_navigation_setup_location', $location);

	if ($location=='primary' && !has_nav_menu($location))
		return false;

	$html.='<div id="boomi-trust-mobile-nav" class="collapse boomi-trust-menu hidden-sm hidden-md hidden-lg">';

		$html.=wp_nav_menu(array(
			'theme_location' => $location,
			'container' => 'div',
			'container_class' => 'panel-group navbar-nav',
			'container_id' => 'accordion',
			'echo' => false,
			'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
			'walker' => new koksijdeMobileNavWalker()
		));

	$html.='</div><!-- .boomi-trust-theme-mobile-menu -->';

	echo apply_filters('boomi_trust_mobile_navigation', $html);
}

/**
 * boomi_trust_secondary_navigation_setup function.
 *
 * if our secondary menu is set, this shows it
 *
 * @access public
 * @return void
 */
function boomi_trust_secondary_navigation_setup() {
	$html=null;

	if (!has_nav_menu('secondary'))
		return false;

	$html.='<div class="collapse navbar-collapse secondary-menu">';
		$html.=wp_nav_menu(array(
			'theme_location' => 'secondary',
			'container' => false,
			'menu_class' => 'nav navbar-nav pull-right secondary',
			'echo' => false,
			'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
			'walker' => new wp_bootstrap_navwalker()
		));
	$html.='</div> <!-- .secondary-menu -->';

	echo apply_filters('boomi_trust_secondary_navigation', $html);
}

/**
 * boomi_trust_back_to_top function.
 *
 * @access public
 * @return void
 */
function boomi_trust_back_to_top() {
	$html=null;

	$html.='<a href="#0" class="boomi-trust-back-to-top"></a>';

	echo apply_filters('boomi_trust_back_to_top', $html);
}
add_action('wp_footer', 'boomi_trust_back_to_top');

/**
 * boomi_trust_wp_parse_args function.
 *
 * Similar to wp_parse_args() just a bit extended to work with multidimensional arrays
 *
 * @access public
 * @param mixed &$a
 * @param mixed $b
 * @return void
 */
function boomi_trust_wp_parse_args(&$a,$b) {
	$a = (array) $a;
	$b = (array) $b;
	$result = $b;

	foreach ( $a as $k => &$v ) {
		if ( is_array( $v ) && isset( $result[ $k ] ) ) {
			$result[ $k ] = boomi_trust_wp_parse_args( $v, $result[ $k ] );
		} else {
			$result[ $k ] = $v;
		}
	}

	return $result;
}

/**
 * boomi_trust_get_excerpt_by_id function.
 *
 * @access public
 * @param string $post (default: '')
 * @param int $length (default: 10)
 * @param string $tags (default: '<a><em><strong>')
 * @param string $extra (default: '...')
 * @return void
 */
function boomi_trust_get_excerpt_by_id($post='', $length=10, $tags='<a><em><strong>', $extra='...') {
 	// if post is id, get the post, if it's the object we are ok, else bail //
	if (is_int($post)) :
		$post = get_post($post);
	elseif (!is_object($post)) :
		return false;
	endif;

	// check for excerpt and return that, else grab the post content //
	if (has_excerpt($post->ID)) :
		$the_excerpt = $post->post_excerpt;
		return apply_filters('the_content', $the_excerpt);
	else :
		$the_excerpt = $post->post_content;
	endif;

	$the_excerpt = strip_shortcodes(strip_tags($the_excerpt), $tags); // remove shortcodes and tags
	$the_excerpt = preg_split('/\b/', $the_excerpt, $length * 2+1); // do our length (words)
	$excerpt_waste = array_pop($the_excerpt); // grab the "excerpt"
	$the_excerpt = implode($the_excerpt); // convert our array of words to an actual exceprt
	$the_excerpt .= $extra; // append the extra

	return apply_filters('the_content', $the_excerpt);
}

/**
 * boomi_trust_theme_get_image_id_from_url function.
 *
 * @access public
 * @param mixed $image_url
 * @return void
 */
function boomi_trust_theme_get_image_id_from_url($image_url) {
	global $wpdb;

	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $image_url ));

	return $attachment[0];
}

/**
 * boomi_trust_array_recursive_diff function.
 *
 * @access public
 * @param mixed $aArray1
 * @param mixed $aArray2
 * @return void
 */
function boomi_trust_array_recursive_diff($aArray1, $aArray2) {
  $aReturn = array();

  foreach ($aArray1 as $mKey => $mValue) {
    if (array_key_exists($mKey, $aArray2)) {
      if (is_array($mValue)) {
        $aRecursiveDiff = koksijde_array_recursive_diff($mValue, $aArray2[$mKey]);
        if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
      } else {
        if ($mValue != $aArray2[$mKey]) {
          $aReturn[$mKey] = $mValue;
        }
      }
    } else {
      $aReturn[$mKey] = $mValue;
    }
  }
  return $aReturn;
}

/**
 * boomi_trust_page_slug function.
 * 
 * @access public
 * @param int $post_id (default: 0)
 * @return void
 */
function boomi_trust_page_slug($post_id=0) {
	if (!$post_id)
		return null;
		
	$post=get_post($post_id);
	
	return $post->post_name;
}