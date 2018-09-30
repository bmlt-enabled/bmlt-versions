<?php
/*
Plugin Name: BMLT Versions
Plugin URI: https://bmlt.magshare.net
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: BMLT Authors
Author URI: https://bmlt.magshare.net
Version: 1.0.2
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

function bmlt_versions_func( $atts ) {
	
$content .= '<div class="bmlt_versions_div">';
$content .= '<ul class="bmlt_versions_ul">';
	$content .= '<li class="bmlt_versions_li_root">';
		$content .= '<a href ="https://github.com/LittleGreenViper/BMLT-Root-Server/raw/Release/BMLT-Root-Server.zip">Root Server (zip file) - ' .getRootServerVersion(). '</a>';;
	$content .= '</li>';
	$content .= '<li class="bmlt_versions_li_wordpress">';
		$content .= '<a href ="https://wordpress.org/plugins/bmlt-wordpress-satellite-plugin/">WordPress Plugin - ' .getSatelliteBaseClassVersion(). '</a>';
	$content .= '</li>';
	$content .= '<li class="bmlt_versions_li_drupal">';
		$content .= '<a href ="https://bitbucket.org/bmlt/bmlt-drupal/downloads/bmlt-drupal7.zip">Drupal 7 Module (zip file) - ' .getSatelliteBaseClassVersion(). '</a>';
	$content .= '</li>';
	$content .= '<li class="bmlt_versions_li_basic">';
		$content .= '<a href ="https://bitbucket.org/bmlt/bmlt-basic/downloads/bmlt-basic.zip">Basic Satellite (zip file) - ' .getSatelliteBaseClassVersion(). '</a>';
	$content .= '</li>';
	$content .= '<li class="bmlt_versions_li_crouton">';
		$content .= '<a href ="https://wordpress.org/plugins/crouton/">Crouton (Tabbed UI) Plugin - ' .getCroutonVersion(). '</a>';
	$content .= '</li>';
	$content .= '<li class="bmlt_versions_li_bread">';
		$content .= '<a href ="https://wordpress.org/plugins/bread/">Bread (Meeting List Generator) Plugin - ' .getBreadVersion(). '</a>';
	$content .= '</li>';
	$content .= '<li class="bmlt_versions_li_yap">';
		$content .= '<a href ="https://github.com/radius314/yap/tree/' .getYapVersion(). '">Yap (Phone line) - ' .getYapVersion(). '</a>';
	$content .= '</li>';
$content .= '</ul>';
$content .= '</div>';

return $content;

}

function getRootServerVersion() {
	$results = wp_remote_get("https://raw.githubusercontent.com/LittleGreenViper/BMLT-Root-Server/Release/main_server/client_interface/serverInfo.xml");
	$httpcode = wp_remote_retrieve_response_code( $results );
	$response_message = wp_remote_retrieve_response_message( $results );
	if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
		return 'Problem Connecting to Server!';
	};
	$body = wp_remote_retrieve_body($results);
	$results = simplexml_load_string($body);
	$results = json_encode($results);
	$results = json_decode($results,true);
	$results = $results['serverVersion']['readableString'];
	return $results;
}

function getSatelliteBaseClassVersion() {
	$results = wp_remote_get("https://plugins.svn.wordpress.org/bmlt-wordpress-satellite-plugin/trunk/bmlt-wordpress-satellite-plugin.php");
	$httpcode = wp_remote_retrieve_response_code( $results );
	$response_message = wp_remote_retrieve_response_message( $results );
	if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
		return 'Problem Connecting to Server!';
	};
	$body = wp_remote_retrieve_body($results);
	$lines = explode("\n", $body);
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, 'Version:') !== false) {
			$pieces = explode(":", $line);
			return trim($pieces[1]);
		} 
	}
	return -1;
}

function getCroutonVersion() {
	$results = wp_remote_get("https://plugins.svn.wordpress.org/crouton/trunk/crouton.php");
	$httpcode = wp_remote_retrieve_response_code( $results );
	$response_message = wp_remote_retrieve_response_message( $results );
	if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
		return 'Problem Connecting to Server!';
	};
	$body = wp_remote_retrieve_body($results);
	$lines = explode("\n", $body);
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, 'Version:') !== false) {
			$pieces = explode(":", $line);
			return trim($pieces[1]);
		} 
	}
	return -1;
}

function getBreadVersion() {
	$results = wp_remote_get("https://plugins.svn.wordpress.org/bread/trunk/bmlt-meeting-list.php");
	$httpcode = wp_remote_retrieve_response_code( $results );
	$response_message = wp_remote_retrieve_response_message( $results );
	if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
		return 'Problem Connecting to Server!';
	};
	$body = wp_remote_retrieve_body($results);
	$lines = explode("\n", $body);
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, 'Version:') !== false) {
			$pieces = explode(":", $line);
			return trim($pieces[1]);
		} 
	}
	return -1;
}

function getYapVersion() {
	$results = wp_remote_get("https://raw.githubusercontent.com/radius314/yap/master/functions.php");
	$httpcode = wp_remote_retrieve_response_code( $results );
	$response_message = wp_remote_retrieve_response_message( $results );
	if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
		return 'Problem Connecting to Server!';
	};
	$body = wp_remote_retrieve_body($results);
	$lines = explode("\n", $body);
	foreach ($lines as $lineNumber => $line) {
		if (strpos($line, 'version = ') !== false) {
			if (preg_match('/"([^"]+)"/', $line, $result)) {
    			return trim($result[1]);   
			}
		} 
	}
	return -1;
}

// create [bmlt_versions] shortcode
add_shortcode( 'bmlt_versions', 'bmlt_versions_func' );
