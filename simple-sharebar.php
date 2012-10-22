<?php
/*
Plugin Name: Simple Sharebar
Plugin URI: http://plugins.findingsimple.com
Description: Easily insert a sharebar.
Version: 1.0
Author: Finding Simple (Jason Conroy & Brent Shepherd)
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2012  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once dirname( __FILE__ ) . '/simple-sharebar-admin.php';

if ( ! class_exists( 'Simple_Sharebar' ) ) :

/**
 * So that themes and other plugins can customise the text domain, the Simple_Sharebar
 * should not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package Simple Sharebar
 * @since 1.0
 */
function initialize_sharebar(){
	Simple_Sharebar::init();
}
add_action( 'init', 'initialize_sharebar', -1 );

/**
 * Plugin Main Class.
 *
 * @package Simple Sharebar
 * @since 1.0
 */
class Simple_Sharebar {

	static $defaults = array( 
		'swidth' => '65', 
		'minwidth' => '768',
		'position' => 'left',
		'topOffset' => '10',
		'leftOffset' => '20',
		'rightOffset' => '10'
	);

	public static function init() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles_and_scripts'), 100 );

		add_filter( 'the_content', array( __CLASS__, 'auto_display_sharebar' ) );

		add_filter ( 'simple_sharebar_services', array( __CLASS__, 'sharebar_facebook' ), 10, 2 ); 
		add_filter ( 'simple_sharebar_services', array( __CLASS__, 'sharebar_twitter' ), 20, 2  ); 
		add_filter ( 'simple_sharebar_services', array( __CLASS__, 'sharebar_google_plus_one' ), 30, 2  ); 
		add_filter ( 'simple_sharebar_services', array( __CLASS__, 'sharebar_pinterest' ), 40, 2  ); 

	}

	/**
	 * Add sharebar scripts
	 *
	 * @since 1.0
	 */
	public static function enqueue_styles_and_scripts(){
		
		if ( !is_admin() ) {
		
			if ( get_option('simple_sharebar-toggle-js-include') != 1 )
				wp_enqueue_script( 'simple-sharebar', self::get_url( '/js/simple-sharebar.min.js', __FILE__ ) ,'jquery','1.1',true );
			
			if ( get_option('simple_sharebar-toggle-css-include') != 1 )
				wp_enqueue_style( 'simple-sharebar', self::get_url( '/css/simple-sharebar.min.css', __FILE__ ) );
			
			$args = apply_filters( 'simple_sharebar_defaults', self::$defaults );
			
			// will only localize if the simple-sharebar script is enqueued
			wp_localize_script( 'simple-sharebar', 'sharebar_args', $args );
		
		}
		
	}

	/**
	 * Auto display sharebar/s if selected
	 *
	 * @since 1.0
	 */
	public static function auto_display_sharebar( $content ){
				
		if ( get_option('simple_sharebar-toggle-auto-insert') == 1 )
			$content = self::display_sharebar ( true , false ) . $content;
		
		if ( get_option('simple_sharebar-toggle-auto-insert-horizontal') == 1 )
			$content = $content . self::display_sharebar ( false , false );

		return $content;
		
	}

	/**
	 * Display the sharebar
	 *
	 * @since 1.0
	 */
	public static function display_sharebar ( $vert = true , $print = true, $id = false ) {
				
		global $post;
		
		// Swap to specified post
		if ( $id ) {
					
			query_posts( array( 'post_type' => 'any', 'p' => $id ) );
			
			if ( have_posts() )	
				the_post();
			
		}
	
		$sharebar_hide = get_post_meta( $post->ID , 'sharebar_hide' , true );
		
		$title = get_option('simple_sharebar-title');
		
		$args = array(
			'url' => Simple_Sharebar::get_share_url(),
			'title' => Simple_Sharebar::get_share_title(),
			'description' => Simple_Sharebar::get_share_description(),
			'image' => Simple_Sharebar::get_share_image()
		);
				
		$services = apply_filters( 'simple_sharebar_services', $services = array() , $args );

		if( empty( $sharebar_hide ) && !empty( $services ) ) {
			
			if ( $vert ) {

				$list = '<div class="simple-sharebar vertical">' . "\n";
			
				if ( $title )
					$list .= '<h4>' . $title . '</h4>';
					
				$list .= '<ul class="simple-sharebar-wrapper">' . "\n";
				
				foreach ( $services as $service ) {
							
					$list .= '<li class="' . $service['slug'] . '-container" >' . $service['vert'] . '</li>';
				
				}
			
			} else {

				$list = '<div class="simple-sharebar horizontal">' . "\n";

				$list .= '<ul class="simple-sharebar-wrapper">' . "\n";
				
				foreach ( $services as $service ) {
							
					$list .= '<li class="' . $service['slug'] . '-container">' . $service['horz'] . '</li>';
				
				}			
			
			}
						
			$list .= '</ul>' . "\n";
			
			$list .= '</div>' . "\n";
			
		}
		
		// reset postdata if needed
		if ( $id )
			wp_reset_query();
		
		if ( $list ) {
		
			if ( $print )	
				echo $list; 
			else 
				return $list;
				
		}
		
	}
	
	
	public static function sharebar_facebook( $services , $args ) {
	
		$service = array(
			'name' => 'Facebook Like Button',
			'slug' => 'facebook',
			'vert' => '<div class="fb-like" data-href="' . $args['url'] . '" data-send="false" data-layout="box_count" data-width="450" data-show-faces="false"></div>',
			'horz' => '<div class="fb-like" data-href="' . $args['url'] . '" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div>'
		);
		
		$services['facebook'] = apply_filters( 'simple_sharebar_facebook', $service, $args );	
							
		return $services;	
	
	}
		
	public static function sharebar_twitter( $services , $args ) {

		$service = array(
			'name' => 'Twitter Share Button',
			'slug' => 'twitter',
			'vert' => '<a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $args['url'] . '" data-text="' . $args['description'] . '" data-dnt="true" data-count="vertical">Tweet</a>',
			'horz' => '<a href="https://twitter.com/share" class="twitter-share-button" data-url="' . $args['url'] . '" data-text="' . $args['description'] . '" data-dnt="true" data-count="horizontal">Tweet</a>'
		);
		
		$services['twitter'] = apply_filters( 'simple_sharebar_twitter', $service, $args );	
							
		return $services;	
	
	}

	public static function sharebar_google_plus_one( $services , $args ) {

		$service = array(
			'name' => 'Google +1 Button',
			'slug' => 'google_plus_one',
			'vert' => '<div class="g-plusone" data-href="' . $args['url'] . '" data-size="tall" ></div>',
			'horz' => '<div class="g-plusone" data-href="' . $args['url'] . '" data-size="medium" ></div>'
		);
		
		$services['google_plus_one'] = apply_filters( 'simple_sharebar_google_plus_one', $service, $args );	
							
		return $services;	
	
	}
	
	public static function sharebar_pinterest( $services , $args ) {
	
		$service = array(
			'name' => 'Pinterest "Pin It" Button',
			'slug' => 'pinterest',
			'vert' => '<a href="http://pinterest.com/pin/create/button/?url=' . urlencode( $args['url'] ) . '&amp;media=' . urlencode( $args['image'] ) . '&amp;description=' . urlencode( $args['description'] ) . '" class="pin-it-button" count-layout="vertical"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>',
			'horz' => '<a href="http://pinterest.com/pin/create/button/?url=' . urlencode( $args['url'] ) . '&amp;media=' . urlencode( $args['image'] ) . '&amp;description=' . urlencode( $args['description'] ) . '" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>'
		);
		
		$services['pinterest'] = apply_filters( 'simple_sharebar_pinterest', $service, $args );	
							
		return $services;	
	
	}
	
	/**
	 * Get correct title
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @since 1.0
	 */	
	public static function get_share_title() {
	
			global $post;
	
			if (is_singular()) {
				$title = get_the_title();
			} else if(is_search()) {
				$title = 'Search';
			} else if(is_category()) {
				$title =  'Archive for ' . single_cat_title("", false) ;
			} else if(is_tag()) {
				$title = 'Archive for ' . single_tag_title("", false) ;
			} else if(is_tax()) {
				$title = 'Archive for ' . single_term_title("", false) ;
			} else if(is_author()) {
				$id = get_query_var( 'author' );
				$title = 'Archive for ' .  get_the_author_meta( 'display_name', $id );
			} else if(is_date()) {
				$title = 'Archives by date' ;
			} else if(is_post_type_archive()) {
				$title = post_type_archive_title("", false);
			} else if(is_archive()) {
				$title = 'Archives';
			} else if(is_404()) {
				$title = 'Page Not Found';
			} else if(is_home()) {
				$title = get_bloginfo('name');
			} else if(is_admin()) {
				$title = get_the_title( $post->ID ); //displays the default value in the admin area
			}
	
			return $title = apply_filters( 'simple_sharebar_share_title', $title );
	
	}

	/**
	 * Get correct description
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @since 1.0
	 */		
	public static function get_share_description() {
	
		global $post;
	
		if(!empty($post->post_excerpt)){
			$description = $post->post_excerpt;
		} if (is_front_page() || is_archive() || is_home() || is_search()) {	
			$description = trim(strip_shortcodes(strip_tags( get_bloginfo( 'description' ))));
		} else {
			$description = trim(strip_shortcodes(strip_tags($post->post_content)));
		}
	
		$pos0 = strpos($description, '.')+1;
		$pos0 = ($pos0 === false) ? strlen($description) : $pos0;
		$pos = strpos(substr($description,$pos0),'.');
		if ($pos < 0 || $pos === false) {
			$pos = strlen($description);
		} else {
			$pos = $pos + $pos0;
		}
		$description = str_replace("\n","",substr($description, 0 , $pos));
		$description = str_replace("\r","",$description);
		$description = str_replace("\"","'",$description);
		$description = nl2br($description);
	 
		return $description = apply_filters( 'simple_sharebar_share_description', $description );
		
	}


	/**
	 * Get correct url
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @since 1.0
	 */	
	public static function get_share_url() {
	
		global $post;

		if (is_singular()) {
			$url = get_permalink();
		} else if(is_search()) {
			$search = get_query_var('s');
			$url = get_bloginfo('url') . "/search/". $search;
		} else if(is_category()) {
			$url =  get_category_link( get_queried_object() );
		} else if(is_tag()) {
			$url = get_tag_link( get_queried_object() );
		} else if(is_tax()) {
			$url = get_term_link( get_queried_object() );
		} else if(is_author()) {
			$id = get_query_var( 'author' );
			$url = get_author_posts_url( $id );
		} else if(is_year()) {
			$url = get_year_link( get_query_var('year') );
		} else if(is_month()) {
			$url = get_month_link( get_query_var('year') , get_query_var('monthnum') );
		} else if(is_day()) {
			$url = get_day_link( get_query_var('year') , get_query_var('monthnum') , get_query_var('day') );
		} else if(is_post_type_archive()) {
			$url = get_post_type_archive_link( get_query_var('post_type') );
		} else if(is_home()) {
			$url = get_bloginfo('url');
			$url = preg_replace("~^https?://[^/]+$~", "$0/", $url); //trailing slash
		} else if( is_admin() ) {
			$url = get_permalink( $post->ID ); //displays the default value in the admin area
		}

		return $url = apply_filters( 'simple_sharebar_share_url', $url );

	}
	
	/**
	 * Get correct image
	 *
	 * @author Jason Conroy <jason@findingsimple.com>
	 * @since 1.0
	 */			
	public static function get_share_image() {
	
		global $post;
		
		$image = '';
		
		if ( function_exists( 'get_the_image') ) {
		
			$image_array = get_the_image( array( 'format' => 'array', 'image_scan' => true , 'size' => 'full', 'default_image' => get_option('simple_facebook-default-image') ) ); 
						
			if ( !empty( $image_array['src'] )  )
				$image = $image_array['src'];
		
		} elseif ( has_post_thumbnail() ) {
					
			$image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

			if ( $image_array )			
				$image = $image_array[0];
		
		} else {
		
			$image = get_option('simple_facebook-default-image');
		
		}
		
		return $title = apply_filters( 'simple_sharebar_share_image', $image );
	
	}
	
	
	/**
	 * Helper function to get the URL of a given file. 
	 * 
	 * As this plugin may be used as both a stand-alone plugin and as a submodule of 
	 * a theme, the standard WP API functions, like plugins_url() can not be used. 
	 *
	 * @since 1.0
	 * @return array $post_name => $post_content
	 */
	public static function get_url( $file ) {

		// Get the path of this file after the WP content directory
		$post_content_path = substr( dirname( __FILE__ ), strpos( __FILE__, basename( WP_CONTENT_DIR ) ) + strlen( basename( WP_CONTENT_DIR ) ) );

		// Return a content URL for this path & the specified file
		return content_url( $post_content_path . $file );
	}	
	
}

endif;