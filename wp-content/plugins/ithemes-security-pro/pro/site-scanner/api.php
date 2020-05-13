<?php

class ITSEC_Site_Scanner_API {

	const HOST = 'https://itsec-site-scanner.ithemes.com/';
	const ACCEPT = 'application/vnd.site-scanner.ithemes;v=1.0';

	/**
	 * Performs the site scan.
	 *
	 * @param int $site_id The site ID to scan. Accepts 0 to scan the main site in a multisite network.
	 *
	 * @return array|WP_Error
	 */
	public static function scan( $site_id = 0 ) {

		$pid = ITSEC_Log::add_process_start( 'site-scanner', 'scan', compact( 'site_id' ) );

		if ( $site_id && ! is_main_site( $site_id ) ) {
			$scan = self::scan_sub_site( $pid, $site_id );
		} else {
			$scan = self::scan_main_site( $pid );
		}

		$results = $scan['response'];
		$cached  = $scan['cached'];

		ITSEC_Log::add_process_stop( $pid, compact( 'results', 'cached' ) );

		/**
		 * Fires after a site scan has completed.
		 *
		 * @param array $scan
		 * @param int   $site_id
		 */
		do_action( 'itsec_site_scanner_scan_complete', $scan, $site_id );

		if ( $cached ) {
			return $results;
		}

		require_once( dirname( __FILE__ ) . '/util.php' );
		$code = ITSEC_Site_Scanner_Util::get_scan_result_code( $results );

		if ( is_wp_error( $results ) ) {
			ITSEC_Log::add_warning( 'site-scanner', $code, compact( 'results', 'cached' ) );

			return $results;
		}

		if ( 'error' === $code ) {
			ITSEC_Log::add_warning( 'site-scanner', $code, compact( 'results', 'cached' ) );
		} elseif ( 'clean' === $code ) {
			ITSEC_Log::add_notice( 'site-scanner', $code, compact( 'results', 'cached' ) );
		} else {
			ITSEC_Log::add_critical_issue( 'site-scanner', $code, compact( 'results', 'cached' ) );
		}

		return $results;
	}

	/**
	 * Scan the main site.
	 *
	 * @param array $pid
	 *
	 * @return array
	 */
	private static function scan_main_site( array $pid ) {

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = $themes = array();

		list( $wp_version ) = explode( '-', $GLOBALS['wp_version'] );

		foreach ( get_plugins() as $file => $data ) {
			if ( ! empty( $data['Version'] ) ) {
				$plugins[ dirname( $file ) ] = $data['Version'];
			}
		}

		foreach ( wp_get_themes() as $theme ) {
			$themes[ $theme->get_stylesheet() ] = $theme->get( 'Version' );
		}

		$body = array(
			'wordpress' => $wp_version,
			'plugins'   => $plugins,
			'themes'    => $themes,
		);

		return self::make_request( 'api/scan', 'POST', $body, $pid );
	}

	/**
	 * Scan a sub site.
	 *
	 * @param array $pid     The process id for logging.
	 * @param int   $site_id The site ID to scan.
	 *
	 * @return array
	 */
	private static function scan_sub_site( array $pid, $site_id ) {
		return self::make_request( 'api/scan', 'POST', array(
			'scan' => array(
				'url'     => get_home_url( $site_id ),
				'keyPair' => self::generate_key_pair(),
			)
		), $pid );
	}

	/**
	 * Make a request to the site scanner API.
	 *
	 * @param string $route  Route to call.
	 * @param string $method HTTP method to use.
	 * @param array  $body   Data to be encoded as json.
	 * @param array  $pid    Process ID to continue making log updates.
	 *
	 * @return array Array of response and cache status.
	 */
	private static function make_request( $route, $method, array $body, array $pid = null ) {
		require_once( $GLOBALS['ithemes_updater_path'] . '/keys.php' );
		require_once( $GLOBALS['ithemes_updater_path'] . '/packages.php' );

		$json = wp_json_encode( $body );

		$keys = Ithemes_Updater_Keys::get( array( 'ithemes-security-pro' ) );

		if ( empty( $keys['ithemes-security-pro'] ) ) {
			return array(
				'cached'   => false,
				'response' => new WP_Error( 'non_active_license', __( 'iThemes Security Pro is not activated.', 'it-l10n-ithemes-security-pro' ) ),
			);
		}

		$signature = hash_hmac( 'sha1', $json, $keys['ithemes-security-pro'] );

		if ( ! $signature ) {
			return array(
				'cached'   => false,
				'response' => new WP_Error( 'hmac_failed', __( 'Failed to calculate hmac.', 'it-l10n-ithemes-security-pro' ) ),
			);
		}

		$package_details = Ithemes_Updater_Packages::get_full_details();

		if ( empty( $package_details['packages']['ithemes-security-pro/ithemes-security-pro.php']['user'] ) ) {
			return array(
				'cached'   => false,
				'response' => new WP_Error( 'non_active_license', __( 'iThemes Security Pro is not activated.', 'it-l10n-ithemes-security-pro' ) ),
			);
		}

		$user = $package_details['packages']['ithemes-security-pro/ithemes-security-pro.php']['user'];

		$site_url = network_home_url();
		$site_url = preg_replace( '/^https/', 'http', $site_url );
		$site_url = preg_replace( '|/$|', '', $site_url );

		$auth = sprintf( 'X-KeySignature signature="%s" username="%s" site="%s"', $signature, $user, $site_url );

		if ( $pid ) {
			ITSEC_Log::add_process_update( $pid, compact( 'route', 'method', 'json', 'auth' ) );
		}

		$cached    = true;
		$cache_key = 'itsec-site-scanner-' . md5( $route . $method . $signature );

		if ( ( $response = get_site_transient( $cache_key ) ) === false ) {
			$cached   = false;
			$response = self::call_api( $route, array(), array(
				'body'    => $json,
				'method'  => $method,
				'timeout' => 300,
				'headers' => array(
					'Authorization' => $auth,
					'Content-Type'  => 'application/json',
					'Accept'        => self::ACCEPT,
				),
			) );
		}

		if ( is_wp_error( $response ) ) {
			return array(
				'cached'   => $cached,
				'response' => $response,
			);
		}

		if ( ! $cached ) {
			self::maybe_cache( $pid, $cache_key, $response );
		}

		$parsed = self::parse_response_body( $response );

		if ( is_wp_error( $parsed ) ) {
			return array(
				'cached'   => $cached,
				'response' => $parsed,
			);
		}

		if ( ( $response_code = wp_remote_retrieve_response_code( $response ) ) && $response_code >= 400 ) {
			if ( ! is_array( $parsed ) ) {
				return array(
					'cached'   => $cached,
					'response' => new WP_Error( 'invalid_json', __( 'Invalid JSON.', 'it-l10n-ithemes-security-pro' ), wp_remote_retrieve_body( $response ) ),
				);
			}

			return array(
				'cached'   => $cached,
				'response' => new WP_Error(
					isset( $parsed['code'] ) ? $parsed['code'] : 'unknown_error',
					isset( $parsed['message'] ) ? $parsed['message'] : __( 'Unknown Error', 'it-l10n-ithemes-security-pro' )
				),
			);
		}

		return array( 'cached' => $cached, 'response' => $parsed );
	}

	/**
	 * Parse the response body out of the response object.
	 *
	 * @param $response
	 *
	 * @return mixed|WP_Error
	 */
	private static function parse_response_body( $response ) {
		$body         = wp_remote_retrieve_body( $response );
		$code         = wp_remote_retrieve_response_code( $response );
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );

		if ( 204 === $code ) {
			return null;
		}

		if ( ! $body ) {
			return new WP_Error( 'empty_response_body', __( 'Empty response body.', 'it-l10n-ithemes-security-pro' ) );
		}

		if ( 'application/json' === $content_type ) {
			$decoded = json_decode( $body, true );

			if ( null === $decoded ) {
				return new WP_Error( 'invalid_json', __( 'Invalid JSON.', 'it-l10n-ithemes-security-pro' ) );
			}

			return $decoded;
		}

		return $body;
	}

	/**
	 * Maybe cache the response if the cache control allows it.
	 *
	 * @param array  $pid
	 * @param string $cache_key
	 * @param array  $response
	 */
	private static function maybe_cache( $pid, $cache_key, $response ) {
		$cache_control = wp_remote_retrieve_header( $response, 'cache-control' );

		if ( ! $cache_control ) {
			return;
		}

		$keywords = array_map( 'trim', explode( ',', $cache_control ) );

		$mapped = array();

		foreach ( $keywords as $keyword ) {
			if ( false === strpos( $keyword, '=' ) ) {
				$mapped[ $keyword ] = true;
			} else {
				list( $key, $value ) = explode( '=', $keyword, 2 );
				$mapped[ $key ] = $value;
			}
		}

		if ( isset( $mapped['max-age'] ) ) {
			ITSEC_Log::add_process_update( $pid, array( 'action' => 'caching-response', $mapped ) );
			set_site_transient( $cache_key, $response, (int) $mapped['max-age'] );
		}
	}

	/**
	 * Call the API.
	 *
	 * @param string $route Route to call.
	 * @param array  $query Query Args.
	 * @param array  $args  Arguments to pass to {@see wp_remote_request()}.
	 *
	 * @return array|WP_Error
	 */
	private static function call_api( $route, $query, $args ) {
		$url = self::HOST . $route;

		if ( $query ) {
			$url = add_query_arg( $query, $url );
		}

		$url  = apply_filters( 'itsec_site_scanner_api_request_url', $url, $route, $query, $args );
		$args = apply_filters( 'itsec_site_scanner_api_request_args', $args, $url, $route, $query );

		return wp_remote_request( $url, $args );
	}

	/**
	 * Generate a public secret key pair for a sub-site site scan.
	 *
	 * @return array
	 */
	public static function generate_key_pair() {
		$public = wp_generate_password( 64, false );
		$secret = wp_generate_password( 64, false );

		ITSEC_Modules::set_setting( 'site-scanner', 'public_key', $public );
		ITSEC_Modules::set_setting( 'site-scanner', 'secret_key', $secret );
		ITSEC_Storage::save();

		return compact( 'public', 'secret' );
	}

	/**
	 * Clear the key pair.
	 */
	public static function clear_key_pair() {
		ITSEC_Modules::set_setting( 'site-scanner', 'public_key', '' );
		ITSEC_Modules::set_setting( 'site-scanner', 'secret_key', '' );
	}

	/**
	 * Get the key pair.
	 *
	 * @return array
	 */
	public static function get_key_pair() {
		return array(
			'public' => ITSEC_Modules::get_setting( 'site-scanner', 'public_key' ),
			'secret' => ITSEC_Modules::get_setting( 'site-scanner', 'secret_key' ),
		);
	}
}
