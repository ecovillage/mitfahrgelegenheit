<?php
	/*
	Plugin Name: Mitfahrgelegenheit
	Plugin URI: https://www.bessermitfahren.de/api
	Description: Plugin zur Einbindung von Suchergebnissen für Mitfahrgelegenheiten
	Author: Philipp Roggan
	Version: 1.0.6
	Author URI: https://www.bessermitfahren.de/vision
	Text Domain: bmf-api
	License: WTFPL
	License URI: http://www.wtfpl.net/txt/copying/
	*/

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'Directly access this file you can not!' );
	}
	define( 'BMF_ID', 'mitfahrgelegenheit/mitfahrgelegenheit.php' );

	require_once( plugin_dir_path(__FILE__) . 'functions.php');

	function bmf_plugin_activate(){
		$options = bmf_get_options();
		$bmf_api_key = bmf_get_api_key();
		if ($bmf_api_key && strlen($bmf_api_key) > 10){
			$options['api_key'] = $bmf_api_key;
			update_option( 'de.bessermitfahren.options', $options );
		}
	}
	register_activation_hook( __FILE__, 'bmf_plugin_activate' );

	function bmf_uninstall(){
		$options = bmf_get_options();
		$options['agb_accepted'] = false;
		update_option( 'de.bessermitfahren.options', $options );
	}
	register_uninstall_hook( __FILE__, 'bmf_uninstall' );
	register_deactivation_hook( __FILE__, 'bmf_uninstall' );


	function bmf_plugin_options() {
		require( plugin_dir_path( __FILE__ ) . 'options.php' );
	}
	function bmf_plugin_menu() {
		add_options_page( 'Mitfahrgelegenheit', 'Mitfahrgelegenheit', 'manage_options', BMF_OPTIONS, 'bmf_plugin_options' );
	}
	add_action( 'admin_menu', 'bmf_plugin_menu' );


// List
	function bmf_list( $atts ) {
		// get settings from db
		$options        = bmf_get_options();
		$bmf_api_key    = ( isset( $options ) && isset( $options['api_key'] ) ) ? $options['api_key'] : false;
		$bmf_agb        = ( isset( $options ) && isset( $options['agb_accepted'] ) ) ? (bool)$options['agb_accepted'] : false;
		$bmf_place_from = ( isset( $options ) && isset( $options['place_from'] ) ) ? $options['place_from'] : '';
		$bmf_place_to   = ( isset( $options ) && isset( $options['place_to'] ) ) ? $options['place_to'] : '';
		$bmf_extra_style= ( isset( $options ) && isset( $options['extra_style'] ) ) ? $options['extra_style'] : '';
		// overwrite places if set directly
		$bmf_place_to   = ( isset( $atts['to'] ) ) ? $atts['to'] : $bmf_place_to;
		$bmf_place_from = ( isset( $atts['from'] ) ) ? $atts['from'] : $bmf_place_from;
		// if is allready set up, add shortcode content
		if ( $bmf_api_key && $bmf_agb ) {
			$bmf_url    = BMF_URL;
			$bmf_text   = 'Mitfahrgelegenheiten - Mitfahrzentrale';
			$bmf_config = '{api:"' . $bmf_api_key . '", wordpress: "' . get_bloginfo('version') . '"}';
			if ( ! $bmf_place_from && $bmf_place_to ) {
				$bmf_url .= 'nach/' . strtolower( urlencode( $bmf_place_to ) ) . '/mitfahrgelegenheiten/';
				$bmf_text   = 'Mitfahrgelegenheiten nach ' . ucfirst( $bmf_place_to ) . ' - Mitfahrzentrale';
				$bmf_config = '{api:"' . $bmf_api_key . '", wordpress: "' . get_bloginfo( 'version' ) . '",to:"' . $bmf_place_to . '"}';
			}
			if ( $bmf_place_from && $bmf_place_to ) {
				$bmf_url .= strtolower( urlencode( $bmf_place_from ) ) . '/' . strtolower( urlencode( $bmf_place_to ) ) . '/mitfahrgelegenheiten/';
				$bmf_text   = 'Mitfahrgelegenheiten von ' . ucfirst( $bmf_place_from ) . ' nach ' . ucfirst( $bmf_place_to ) . ' - Mitfahrzentrale';
				$bmf_config = '{api:"' . $bmf_api_key . '", wordpress: "' . get_bloginfo( 'version' ) . '",to:"' . $bmf_place_to . '",from:"' . $bmf_place_from . '"}';
			}
			if ( $bmf_place_from && ! $bmf_place_to ) {
				$bmf_url .= strtolower( urlencode( $bmf_place_from ) ) . '/mitfahrzentrale/';
				$bmf_text   = 'Mitfahrzentrale ' . $bmf_place_from . ' - Mitfahrgelegenheit';
				$bmf_config = '{api:"' . $bmf_api_key . '", wordpress: "' . get_bloginfo( 'version' ) . '",from:"' . $bmf_place_from . '"}';

			}
			$bmf_shortcode = '<script type="text/javascript" src="' . BMF_API . '"></script>';
			$bmf_shortcode .= '<style type="text/css">' . $bmf_extra_style . '</style>';
			$bmf_shortcode .= '<span id="bmflink">lade <a href="' . $bmf_url . '">' . $bmf_text . '</a>.</span>';
			$bmf_shortcode .= '<script type="text/javascript">bmf.init(' . $bmf_config . ')</script>';
			return $bmf_shortcode;
		} else {
			return '<p>' . __('Das Mitfahrgelegenheiten Plugin muss erst im Backend eingerichtet werden...') . '</p>';
		}
	}
	add_shortcode( "bmf_list", "bmf_list" );

	function bmf_add_quicktag() {

		if ( wp_script_is( 'quicktags' ) ) {
			?>
			<script type="text/javascript">
				QTags.addButton('bmf_add_list', 'BMF', '[bmf_list]', '', '', 'Fügt den Shortcode für die Mitfahrgelegenheiten ein', 200);
			</script>
			<?php
		}

	}
	add_action( 'admin_print_footer_scripts', 'bmf_add_quicktag' );


	function bmf_plugin_shortcode_button_init() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' ) {
			return;
		}
		add_filter( "mce_external_plugins", "bmf_plugin_register_tinymce_plugin" );
		add_filter( 'mce_buttons', 'bmf_plugin_add_tinymce_button' );
	}
	function bmf_plugin_register_tinymce_plugin( $plugin_array ) {
		$plugin_array['bmf_plugin_button'] = plugins_url( '/shortcode.js', __FILE__ );
		return $plugin_array;
	}
	function bmf_plugin_add_tinymce_button( $buttons ) {
		$buttons[] = "bmf_plugin_button";
		return $buttons;
	}
	add_action( 'admin_init', 'bmf_plugin_shortcode_button_init' );
