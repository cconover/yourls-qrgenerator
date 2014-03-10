<?php
/*
Plugin Name: QR Code Generator
Plugin URI: https://christiaanconover.com/yourls-qrgenerator?ref=yourls-qrplugin-name
Description: Add .qr to the end of a short URL to display QR code (e.g. <tt>sho.rt/keyword.qr</tt>).<br />Support for customization parameters.  Visit the <a href="https://christiaanconover.com/yourls-qrgenerator?ref=yourls-qrplugin-desc" target="_blank">plugin page</a> for full details.
Version: 0.2.0
Author: Christiaan Conover
Author URI: https://christiaanconover.com/?ref=yourls-qrplugin-author
*/

// Trigger the QR code generator if YOURLS doesn't recognize the URL pattern
yourls_add_action( 'loader_failed', array( $this, 'generateqr' ) );

// Do not allow a direct call to the plugin file
if ( ! defined( 'YOURLS_ABSPATH' ) ) {
	die();
}

// Require PHP QR Code library
require_once( 'phpqrcode.php' );
	
function generateqr ( $request ) {
	// Make Regex pattern for keyword
	$pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );
	// Identify short URL using keyword pattern
	if( preg_match( "@^([$pattern]+)\.qr?/?$@", $request[0], $matches ) ) {
		// Validate that the keyword exists
		$keyword = yourls_sanitize_keyword( $matches[1] );
		// If the keyword exists, generate and display the QR code
		if( yourls_is_shorturl( $keyword ) ) {
			// URL to send to the QR code generator
			$url = urlencode( YOURLS_SITE . '/' . $keyword . '?ref=qr' );
			
			// Generate and display QR code
			QRCode::png( $url );
		}
	}
} // End generateqr()
?>