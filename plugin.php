<?php
/*
Plugin Name: QR Code Generator
Plugin URI: https://christiaanconover.com/yourls-qrgenerator?ref=yourls-qrplugin-name
Description: Add .qr to the end of a short URL to display QR code (e.g. <tt>sho.rt/keyword.qr</tt>).<br />Support for customization parameters.  Visit the <a href="https://christiaanconover.com/yourls-qrgenerator?ref=yourls-qrplugin-desc" target="_blank">plugin page</a> for full details.
Version: 0.2.0
Author: Christiaan Conover
Author URI: https://christiaanconover.com/?ref=yourls-qrplugin-author
*/

// Plugin class
class cconover_qrcode {
	function __construct() {
		// Trigger if the loader doesn't recognize the pattern
		yourls_add_action( 'loader_failed', array( $this, 'generateQR' ) );
	}
	
	public function generateQR ( $request ) {
		// Make Regex pattern for keyword
		$pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );
	
		// If valid QR code size is provided, use it.  If not, default to 200px square
		if ( isset( $_GET['s'] ) && is_numeric( $_GET['s'] ) && ( $_GET['s'] > 0 && $_GET['s'] <= 540 ) ) {
			$size = $_GET['s'];
		}
		else {
			$size = 200;
		}
	
		// Set Error Correction Level
		if ( isset($_GET['ecl'] ) && ( strtoupper( $_GET['ecl'] ) == 'L' || strtoupper( $_GET['ecl'] ) == 'M' || strtoupper( $_GET['ecl'] ) == 'Q' || strtoupper( $_GET['ecl'] ) == 'H' ) ) {
			$ecl = strtoupper( $_GET['ecl'] );
		}
		else {
			$ecl = 'M';
		}
	
		// Identify short URL using keyword pattern
		if( preg_match( "@^([$pattern]+)\.qr?/?$@", $request[0], $matches ) ) {
			// Validate that the keyword exists
			$keyword = yourls_sanitize_keyword( $matches[1] );
			// If the keyword exists, display the QR code
			if( yourls_is_shorturl( $keyword ) ) {
				header( 'Location: https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chld=' . $ecl . '&chl=' . YOURLS_SITE . '/' . $keyword . '?ref=qr' );
				exit;
			}
		}
	}
}

// Create new QR code object
$qrcode = new cconover_qrcode;
?>