<?php

class ITSEC_Site_Scanner_Util {

	/**
	 * Get the log code for a scan result.
	 *
	 * @param array|WP_Error $results
	 *
	 * @return string
	 */
	public static function get_scan_result_code( $results ) {
		if ( is_wp_error( $results ) ) {
			if ( 'internal_server_error' === $results->get_error_code() ) {
				return 'scan-failure-server-error';
			}
			if (
				'http_request_failed' === $results->get_error_code() &&
				false !== strpos( $results->get_error_message(), 'cURL error 52:' )
			) {
				return 'scan-failure-server-error';
			}

			return 'scan-failure-client-error';
		}

		$codes = array();

		if ( ! empty( $results['entries']['malware'] ) ) {
			$codes[] = 'found-malware';
		}

		foreach ( $results['entries']['blacklist'] as $blacklist ) {
			if ( 'blacklisted' === $blacklist['status'] ) {
				$codes[] = 'on-blacklist';
				break;
			}
		}

		if ( ! empty( $results['entries']['vulnerabilities'] ) ) {
			$codes[] = 'vulnerable-software';
		}

		if ( $codes ) {
			if ( $results['errors'] ) {
				$codes[] = 'has-error';
			}

			return implode( '--', $codes );
		}

		if ( $results['errors'] ) {
			return 'error';
		}

		return 'clean';
	}

	public static function get_scan_code_description( $code ) {
		switch ( $code ) {
			case 'scan-failure-server-error':
			case 'scan-failure-client-error':
			case 'error':
				return esc_html__( 'Scan Error', 'it-l10n-ithemes-security-pro' );
			case 'clean':
				return esc_html__( 'Clean', 'it-l10n-ithemes-security-pro' );
			default:
				return wp_sprintf( '%l', self::translate_findings_code( $code ) );
		}
	}

	public static function translate_findings_code( $code ) {
		$part_labels = array();

		if ( is_string( $code ) ) {
			$parts = explode( '--', $code );
		} else {
			$parts = $code;
		}

		foreach ( $parts as $part ) {
			switch ( $part ) {
				case 'found-malware':
					$part_labels[] = esc_html__( 'Found Malware', 'it-l10n-ithemes-security-pro' );
					break;
				case 'on-blacklist':
					$part_labels[] = esc_html__( 'On Blacklist', 'it-l10n-ithemes-security-pro' );
					break;
				case 'vulnerable-software':
					$part_labels[] = esc_html__( 'Vulnerable Software', 'it-l10n-ithemes-security-pro' );
					break;
				case 'has-error':
					$part_labels[] = esc_html__( 'Scan Error', 'it-l10n-ithemes-security-pro' );
					break;
				default:
					$part_labels[] = $part;
					break;
			}
		}

		return $part_labels;
	}
}
