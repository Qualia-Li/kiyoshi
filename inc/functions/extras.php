<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * @package kiyoshi
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @since 1.0.0
 * @return array
 */
function kiyoshi_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}

/**
 * Schema type
 * @since 1.0.0
 * @return string schema itemprop type
 */
function kiyoshi_html_tag_schema() {
	$schema 	= 'http://schema.org/';
	$type 		= 'WebPage';

	// Is single post
	if ( is_singular( 'post' ) ) {
		$type 	= 'Article';
	}

	// Is author page
	elseif ( is_author() ) {
		$type 	= 'ProfilePage';
	}

	// Is search results page
	elseif ( is_search() ) {
		$type 	= 'SearchResultsPage';
	}

	echo 'itemscope="itemscope" itemtype="' . esc_attr( $schema ) . esc_attr( $type ) . '"';
}

if ( ! function_exists( 'kiyoshi_fonts_url' ) ) {

  /**
   * Register Google web fonts.
   *
   * @since 1.0.0
   * @return string Google fonts URL for the theme.
   */
  function kiyoshi_fonts_url() {
      
    $fonts_url = '';
    $fonts     = array();
    $subsets   = 'latin,latin-ext';

    /* translators: If there are characters in your language that are not supported by Lato, translate this to 'off'. Do not translate into your own language. */
    if ( 'off' !== esc_html_x( 'on', 'Lato font: on or off', 'kiyoshi' ) ) {
      $fonts[] = 'Lato:300,300italic,900,700,400,400italic';
    }

    if ( $fonts ) {
      $fonts_url = add_query_arg( array(
        'family' => urlencode( implode( '|', $fonts ) ),
        'subset' => urlencode( $subsets ),
      ), 'https://fonts.googleapis.com/css' );
    }
    return $fonts_url;

  }

}

function kiyoshi_categorized_blog() {
	/**
	 * Returns true if a blog has more than 1 category.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	if ( false === ( $all_the_cool_cats = get_transient( 'kiyoshi_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,

			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'kiyoshi_categories', $all_the_cool_cats );
	}

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so kiyoshi_categorized_blog should return true.
		return true;
	} else {
		// This blog has only 1 category so kiyoshi_categorized_blog should return false.
		return false;
	}
}

function kiyoshi_category_transient_flusher() {
	/**
	 * Flush out the transients used in kiyoshi_categorized_blog.
	 * @since 1.0.0
	 */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'kiyoshi_categories' );
}
add_action( 'edit_category', 'kiyoshi_category_transient_flusher' );
add_action( 'save_post',     'kiyoshi_category_transient_flusher' );

if ( version_compare( $GLOBALS['wp_version'], '4.1', '<' ) ) :
		/**
		 * Filters wp_title to print a neat <title> tag based on what is being viewed.
		 *
		 * @param string $title Default title text for current view.
		 * @param string $sep Optional separator.
		 * @return string The filtered title.
		 */
				 function verysimplestart_wp_title( $title, $sep ) {
				if ( is_feed() ) {
						return $title;
				}

				global $page, $paged;

				// Add the blog name
				$title .= get_bloginfo( 'name', 'display' );

				// Add the blog description for the home/front page.
				$site_description = get_bloginfo( 'description', 'display' );
				if ( $site_description && ( is_home() || is_front_page() ) ) {
						$title .= " $sep $site_description";
				}

				// Add a page number if necessary:
				if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
						$title .= " $sep " . sprintf( __( 'Page %s', 'verysimplestart' ), max( $paged, $page ) );
				}

				return $title;
		}
		add_filter( 'wp_title', 'verysimplestart_wp_title', 10, 2 );

		/**
		 * Title shim for sites older than WordPress 4.1.
		 *
		 * @link https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
		 * @todo Remove this function when WordPress 4.3 is released.
		 */
		function verysimplestart_render_title() {
				?>
				<title><?php wp_title( '|', true, 'right' ); ?></title>
				<?php
		}
		add_action( 'wp_head', 'verysimplestart_render_title' );
endif;
