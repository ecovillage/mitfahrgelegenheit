<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die( 'Directly access this file you can not!' );
	}

	define( 'BMF_OPTIONS', 'bmf-mfg' );
	define( 'BMF_SETTINGS_PAGE', admin_url() . 'options-general.php?page=' . BMF_OPTIONS );
	define( 'BMF_URL', 'https://www.bessermitfahren.de/' );
	define( 'BMF_API', BMF_URL . 'api.js' );
	define( 'BMF_MSG', '<div class="notice notice-warning is-dismissible" id="bmf_needs_configuration"><p>Mitfahrgelegenheiten wurde installiert. Du solltest es noch <a href="' . BMF_SETTINGS_PAGE . '">konfigurieren</a>.</p></div>');

	function bmf_get_api_key() {
		$ret = '';
		$protocol = ( ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) ? 'https://' : 'http://';
		$data = array(
				'cls'        => 'api_page',
				'action'     => 'get_api_key',
				'wordpress'  => get_bloginfo( 'version' ),
				'email' => get_bloginfo( 'admin_email' ),
				'url'        => $protocol . $_SERVER['HTTP_HOST']
		);
		if ( ini_get( 'allow_url_fopen' ) ) {
			$ret = file_get_contents( 'https://www.bessermitfahren.de?' . http_build_query( $data ) );
		} else {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, 'https://www.bessermitfahren.de?' . http_build_query( $data ) );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			if ( ! curl_errno( $ch ) ) {
				$ret = curl_exec( $ch );
			}
			curl_close( $ch );
		}
		return $ret;
	}

	function bmf_get_options() {
		return get_option(
				'de.bessermitfahren.options',
				array(
						'place_from'   => 'Berlin',
						'place_to'     => '',
						'agb_accepted' => false,
						'api_key'      => false,
						'extra_style'  => ''
				)
		);
	}

	function bmf_admin_notice() {
		$options = bmf_get_options();
		if ((!$options['agb_accepted'] || ! $options['api_key']) &&  current_user_can( 'manage_options' ) ) {
			echo BMF_MSG;
		}
	}
	add_action( 'admin_notices', 'bmf_admin_notice' );

	function bmf_settings_link( $links ) {
		$settings_link = '<a href="' . BMF_SETTINGS_PAGE . '">Einstellungen</a>';
		array_unshift( $links, $settings_link );
		$info_link = '<a href="' . BMF_URL . 'vision" target="_blank">Vision</a>';
		array_push( $links, $info_link );
		return $links;
	}
	add_filter( "plugin_action_links_" . BMF_ID, 'bmf_settings_link' );
