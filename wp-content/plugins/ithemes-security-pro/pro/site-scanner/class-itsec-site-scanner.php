<?php

class ITSEC_Site_Scanner {

	public function run() {
		add_filter( 'itsec_pre_malware_scanner_scan', array( $this, 'scan' ), 10, 2 );
		add_filter( 'itsec_pre_malware_scanner_get_html', array( $this, 'get_html' ), 10, 3 );
		add_filter( 'itsec_pre_malware_scanner_send_scheduled_results', array( $this, 'send_email' ), 10, 2 );
		add_action( 'init', array( $this, 'handle_verification_request' ) );
		add_action( 'itsec_register_highlighted_logs', array( $this, 'register_highlight' ) );
		add_action( 'itsec_site_scanner_scan_complete', array( $this, 'extract_vulnerabilities_from_scan' ), 10, 2 );
	}

	public function scan( $_, $site_id ) {
		if ( ! ITSEC_Core::is_licensed() ) {
			return $_;
		}

		ITSEC_Lib::load( 'feature-flags' );

		if ( ! ITSEC_Lib_Feature_Flags::is_enabled( 'site_scanner' ) ) {
			return $_;
		}

		if ( ! class_exists( 'ITSEC_Site_Scanner_API' ) ) {
			require_once dirname( __FILE__ ) . '/api.php';
		}

		return ITSEC_Site_Scanner_API::scan( $site_id );
	}

	public function get_html( $_, $results, $show_error_details ) {
		if ( is_array( $results ) && ! isset( $results['entries'] ) ) {
			return $_;
		}

		if ( ! class_exists( 'ITSEC_Site_Scanner_Template' ) ) {
			require_once dirname( __FILE__ ) . '/template.php';
		}

		return ITSEC_Site_Scanner_Template::get_html( $results, $show_error_details );
	}

	public function send_email( $_, $results ) {
		if ( is_array( $results ) && ! isset( $results['entries'] ) ) {
			return $_;
		}

		require_once( dirname( __FILE__ ) . '/util.php' );
		$code = ITSEC_Site_Scanner_Util::get_scan_result_code( $results );

		if ( 'clean' === $code ) {
			return false;
		}

		require_once( dirname( __FILE__ ) . '/mail.php' );

		$nc = ITSEC_Core::get_notification_center();

		$mail = $nc->mail();
		$mail->set_subject( ITSEC_Site_Scanner_Mail::get_scan_subject( $code ) );
		$mail->set_recipients( $nc->get_recipients( 'malware-scheduling' ) );

		$mail->add_header(
			esc_html__( 'Site Scan', 'it-l10n-ithemes-security-pro' ),
			sprintf(
				esc_html__( 'Site Scan for %s', 'it-l10n-ithemes-security-pro' ),
				'<b>' . ITSEC_Lib::date_format_i18n_and_local_timezone( time(), get_option( 'date_format' ) ) . '</b>'
			)
		);
		ITSEC_Site_Scanner_Mail::format_scan_body( $mail, $results );
		$mail->add_footer();

		$nc->send( 'malware-scheduling', $mail );

		return true;
	}

	public function handle_verification_request() {
		if ( ! isset( $_POST['itsec_action'] ) || 'verify_site_scan' !== $_POST['itsec_action'] ) {
			return;
		}

		require_once dirname( __FILE__ ) . '/api.php';

		$key_pair = ITSEC_Site_Scanner_API::get_key_pair();

		if ( ! isset( $_POST['secret'] ) ) {
			echo wp_json_encode( array(
				'error' => 'secret_field_missing',
			) );
			die;
		}

		if ( ! $key_pair['secret'] || ! hash_equals( $key_pair['secret'], $_POST['secret'] ) ) {
			echo wp_json_encode( array(
				'error' => 'invalid_secret',
			) );
			die;
		}

		echo wp_json_encode( array(
			'public' => $key_pair['public']
		) );
		die;
	}

	public function register_highlight() {
		ITSEC_Lib_Highlighted_Logs::register_dynamic_highlight( 'site-scanner-report', array(
			'module' => 'site-scanner',
			'type'   => 'critical-issue',
		) );
	}

	public function extract_vulnerabilities_from_scan( $scan, $site_id ) {
		if ( $site_id && ! is_main_site( $site_id ) ) {
			return; // Vulnerabilities aren't checked on sub site scans.
		}

		$results = $scan['response'];
		$cached  = $scan['cached'];

		if ( $cached || is_wp_error( $results ) ) {
			return;
		}

		$vulnerabilities = array();

		if ( ! empty( $results['entries']['vulnerabilities'] ) ) {
			foreach ( $results['entries']['vulnerabilities'] as $vulnerability ) {
				foreach ( $vulnerability['issues'] as $i => $issue ) {
					$vulnerability['issues'][ $i ] = array(
						'title'    => $issue['title'],
						'fixed_in' => $issue['fixed_in'],
					);
				}

				$vulnerabilities[] = $vulnerability;
			}
		}

		$existing = ITSEC_Modules::get_setting( 'site-scanner', 'vulnerabilities' );

		if ( $existing !== $vulnerabilities ) {
			ITSEC_Modules::set_setting( 'site-scanner', 'vulnerabilities', $vulnerabilities );

			/**
			 * Fires when the detected software vulnerabilities have changed.
			 *
			 * @param array $vulnerabilities The new vulnerabilities set.
			 * @param array $existing        The existing vulnerabilities.
			 */
			do_action( 'itsec_software_vulnerabilities_changed', $vulnerabilities, $existing );
		}
	}
}
