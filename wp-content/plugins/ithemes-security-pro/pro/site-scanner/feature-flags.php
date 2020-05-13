<?php

ITSEC_Lib_Feature_Flags::register_flag( 'site_scanner', array(
	'remote'        => true,
	'label'         => static function () {
		return __( 'Site Scanner', 'it-l10n-ithemes-security-pro' );
	},
	'description'   => static function () {
		return __( 'Enables the iThemes Security Site Scanner which scans for known vulnerabilities in your Plugins, Themes and WordPress Core.', 'it-l10n-ithemes-security-pro' );
	},
	'documentation' => 'https://help.ithemes.com/hc/en-us/articles/360046334433',
) );
