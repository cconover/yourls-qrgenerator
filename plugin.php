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
		// Trigger the QR code generator if YOURLS doesn't recognize the URL pattern
		yourls_add_action( 'loader_failed', array( $this, 'generateQR' ) );
		
		// Do not allow a direct call to the plugin file
		if ( !defined( 'YOURLS_ABSPATH' ) ) {
			die();
		}
	} // End __construct()
	
	public function generateQR ( $request ) {
		// Check for cURL and GD on the server
		if ( function_exists( 'curl_version' ) && function_exists ( 'imagecreatefromstring' ) ) {
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
				// If the keyword exists, generate and display the QR code
				if( yourls_is_shorturl( $keyword ) ) {
					// URL to send to the QR code generator
					$url = urlencode( YOURLS_SITE . '/' . $keyword . '?ref=qr' );
					// Google QR Code API URL
					$generateurl = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chld=' . $ecl . '&chl=' . $url;
				
					// Initialize cURL
					$ch = curl_init( $generateurl );
					curl_setopt( $ch, CURLOPT_HEADER, 0 );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $ch, CURLOPT_BINARYTRANSFER,1 );
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
				
					// Fetch QR code and close cURL session
					$qrdata = curl_exec( $ch );
					curl_close( $ch );
					
					// Generate image from data retrived from Google API
					$qrimg = imagecreatefromstring( $qrdata );
					$result = $qrimg;
					imagedestroy( $qrimg );
				}
			}
		}
		// If cURL is installed, but not GD
		elseif ( function_exists( 'curl_version' ) && !function_exists( 'imagecreatefromstring' ) ) {
			$result = 'Your server does not have GD installed, which is required to use this plugin.';
		}
		// If GD is installed, but not cURL
		elseif ( function_exists( 'imagecreatefromstring' ) && function_exists( 'curl_version' ) ) {
			$result = 'Your server does not have cURL installed, which is required to use this plugin.';
		}
		else {
			$result = 'Your server does not have cURL or GD installed, both of which are required to use this plugin.';
		}
		
		// Display the result
		echo $result;
	} // End generateQR()
} // End cconover_qrcode

// Create new QR code object
$qrcode = new cconover_qrcode;
?>