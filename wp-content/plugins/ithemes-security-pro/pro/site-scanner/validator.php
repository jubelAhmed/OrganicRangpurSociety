<?php

class ITSEC_Site_Scanner_Validator extends ITSEC_Validator {
	public function get_id() {
		return 'site-scanner';
	}

	protected function sanitize_settings() {
		$this->sanitize_setting( 'string', 'public_key', __( 'Public Key', 'it-l10n-ithemes-security-pro' ) );
		$this->sanitize_setting( 'string', 'secret_key', __( 'Secret Key', 'it-l10n-ithemes-security-pro' ) );
		$this->sanitize_setting( 'array', 'vulnerabilities', __( 'Vulnerabilities', 'it-l10n-ithemes-security-pro' ) );
	}
}

ITSEC_Modules::register_validator( new ITSEC_Site_Scanner_Validator() );
