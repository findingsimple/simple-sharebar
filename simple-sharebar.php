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

//require_once dirname( __FILE__ ) . '/simple-sharebar-admin.php';

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

	public static function init() {

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles_and_scripts'), 100 );
		
		add_action( 'the_content', array( __CLASS__, 'insert_sharebar') );

	}

	/**
	 * Add qtips2 scripts
	 *
	 * @since 1.0
	 */
	public static function enqueue_styles_and_scripts(){
		
		if ( !is_admin() ) {
		
			if ( get_option('simple_sharebar-toggle-js-include') != 1 )
				wp_enqueue_script( 'simple-sharebar', self::get_url( '/js/simple-sharebar.min.js', __FILE__ ) ,'jquery','1.1',true );
			
			if ( get_option('simple_sharebar-toggle-css-include') != 1 )
				wp_enqueue_style( 'simple-sharebar', self::get_url( '/css/simple-sharebar.min.css', __FILE__ ) );
		
		}
		
	}

	/**
	 * Display the sharebar
	 *
	 * @since 1.0
	 */
	public static function insert_sharebar ( $print = true ) {
	
		global $post;
		
		$sharebar_hide = get_post_meta( $post->ID , 'sharebar_hide' , true );
		
		if( empty( $sharebar_hide ) ) {
					
			$str = '<ul id="sharebar">' . "\n";
			
			$str .= '<li>test</li>';

			$str .= '</ul>' . "\n";
			
			if ( $print )
				echo $str; 
			else 
				return $str;
			
		}
		
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