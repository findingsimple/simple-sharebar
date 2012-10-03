<?php

if ( ! class_exists( 'Simple_Sharebar_Admin' ) ) {

/**
 * So that themes and other plugins can customise the text domain, the Simple_Sharebar_Admin should
 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package Simple Sharebar
 * @since 1.0
 */
function simple_initialize_sharebar_admin() {
	Simple_Sharebar_Admin::init();
}
add_action( 'init', 'simple_initialize_sharebar_admin', -1 );


class Simple_Sharebar_Admin {

	public static function init() {  

		/* create custom plugin settings menu */
		add_action( 'admin_menu',  __CLASS__ . '::simple_sharebar_create_menu' );

	}

	public static function simple_sharebar_create_menu() {

		//create new top-level menu
		add_options_page( 'Sharebar Settings', 'Simple Sharebar', 'administrator', 'simple_sharebar', __CLASS__ . '::simple_sharebar_settings_page' );

		//call register settings function
		add_action( 'admin_init',  __CLASS__ . '::register_mysettings' );

	}


	public static function register_mysettings() {
	
		$page = 'simple_sharebar-settings'; 

		// General settings
		
		add_settings_section( 
			'simple_sharebar-general', 
			'General Settings',
			__CLASS__ . '::simple_sharebar_general_callback',
			$page
		);
		
		add_settings_field(
			'simple_sharebar-title',
			'Title',
			__CLASS__ . '::simple_sharebar_title_callback',
			$page,
			'simple_sharebar-general'
		);
		
		add_settings_field(
			'simple_sharebar-toggle-auto-insert',
			'Auto insert vertical sharebar',
			__CLASS__ . '::simple_sharebar_toggle_auto_insert_callback',
			$page,
			'simple_sharebar-general'
		);
		
		add_settings_field(
			'simple_sharebar-toggle-auto-insert-horizontal',
			'Auto insert horizontal sharebar',
			__CLASS__ . '::simple_sharebar_toggle_auto_insert_horizontal_callback',
			$page,
			'simple_sharebar-general'
		);

		// Includes settings
		
		add_settings_section( 
			'simple_sharebar-includes', 
			'CSS and JS Includes',
			__CLASS__ . '::simple_sharebar_includes_callback',
			$page
		);
		
		add_settings_field(
			'simple_sharebar-toggle-css-include',
			'Toggle CSS enqueue in Head',
			__CLASS__ . '::simple_sharebar_toggle_css_include_callback',
			$page,
			'simple_sharebar-includes'
		);
		
		add_settings_field(
			'simple_sharebar-toggle-js-include',
			'Toggle JS enqueue in Footer',
			__CLASS__ . '::simple_sharebar_toggle_js_include_callback',
			$page,
			'simple_sharebar-includes'
		);

		//register our settings
		
		register_setting( $page, 'simple_sharebar-title' );
		register_setting( $page, 'simple_sharebar-toggle-auto-insert' );
		register_setting( $page, 'simple_sharebar-toggle-auto-insert-horizontal' );

		register_setting( $page, 'simple_sharebar-toggle-css-include' );
		register_setting( $page, 'simple_sharebar-toggle-js-include' );

	}

	public static function simple_sharebar_settings_page() {
	
		$page = 'simple_sharebar-settings'; 
	
	?>
	<div class="wrap">
	
		<div id="icon-options-general" class="icon32"><br /></div><h2>Simple Sharebar Settings</h2>
		
		<?php settings_errors(); ?>
	
		<form method="post" action="options.php">
			
			<?php settings_fields( $page ); ?>
			
			<?php do_settings_sections( $page ); ?>
		
			<p class="submit">
				<input type="submit" class="button-primary" value="Save Changes" />
			</p>
		
		</form>
		
	</div>
	
	<?php 
	} 
	
	// General Settings Callbacks

	public static function simple_sharebar_general_callback() {
		
		//do nothing
		
	}
	
	public static function simple_sharebar_title_callback() {
	
		$title = ( get_option('simple_sharebar-title') ) ? esc_attr( get_option('simple_sharebar-title') ) : '';

		echo '<input name="simple_sharebar-title" type="text" id="simple_sharebar-title" class="regular-text" value="'. $title . '"  /> Title displayed before the social buttons (Blank for no title)';
		
	}
	
	public static function simple_sharebar_toggle_auto_insert_callback() {
	
		echo '<input name="simple_sharebar-toggle-auto-insert" id="simple_sharebar-toggle-auto-insert" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_sharebar-toggle-auto-insert'), false ) . ' /> Auto insert vertical sharebar before <code>the_content();</code>';
		
	}

	public static function simple_sharebar_toggle_auto_insert_horizontal_callback() {
	
		echo '<input name="simple_sharebar-toggle-auto-insert-horizontal" id="simple_sharebar-toggle-auto-insert-horizontal" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_sharebar-toggle-auto-insert-horizontal'), false ) . ' /> Auto insert horizontal sharebar after <code>the_content();</code>';
		
	}	
	
	// Includes Settings Callbacks
	
	public static function simple_sharebar_includes_callback() {
		
		echo '<p>Use the checkboxes below to toggle whether or not include the minified sharebar css and js. You may want to include within an existing stylesheet or js file for performance reasons.</p>';
		
	}

	public static function simple_sharebar_toggle_css_include_callback() {
	
		echo '<input name="simple_sharebar-toggle-css-include" id="simple_sharebar-toggle-css-include" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_sharebar-toggle-css-include'), false ) . ' /> Do <strong>not</strong> include CSS in <code>&lt;head&gt;</code>';
		
	}
	
	public static function simple_sharebar_toggle_js_include_callback() {
	
		echo '<input name="simple_sharebar-toggle-js-include" id="simple_sharebar-toggle-js-include" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_sharebar-toggle-js-include'), false ) . ' /> Do <strong>not</strong> include JS before <code>&lt;/body&gt;</code>';
		
	}


}

}


