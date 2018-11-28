<?php
/*
Plugin Name: BMLT Versions
Plugin URI: https://wordpress.org/plugins/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: BMLT Authors
Author URI: https://bmlt.app
Version: 1.1.1
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

function bmlt_versions_func($atts = []) {
    extract(shortcode_atts(array(
    'root_server'  =>  '1',
    'wordpress' => '1',
    'drupal' => '1',
    'basic' => '1',
    'crouton' => '1',
    'bread' => '1',
    'yap' => '1'
    ), $atts));

$root_server = sanitize_text_field($root_server);
$wordpress = sanitize_text_field($wordpress);
$drupal = sanitize_text_field($drupal);
$basic = sanitize_text_field($basic);
$crouton = sanitize_text_field($crouton);
$bread = sanitize_text_field($bread);
$yap = sanitize_text_field($yap);

$content = '';
$content .= '<div class="bmlt_versions_div">';
$content .= '<ul class="bmlt_versions_ul">';
    if ($root_server) {
        $content .= '<li class="bmlt_versions_li_root">';
            $content .= '<a href ="https://github.com/bmlt-enabled/BMLT-Root-Server/raw/Release/BMLT-Root-Server.zip">Root Server (zip file) - ' .getRootServerVersion(). '</a>';;
        $content .= '</li>';
    }
    if ($wordpress) {
        $content .= '<li class="bmlt_versions_li_wordpress">';
            $content .= '<a href ="https://wordpress.org/plugins/bmlt-wordpress-satellite-plugin/">WordPress Plugin - ' .getSatelliteBaseClassVersion(). '</a>';
        $content .= '</li>';
    }
    if ($drupal) {
        $content .= '<li class="bmlt_versions_li_drupal">';
            $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-drupal/raw/master/bmlt-drupal7.zip">Drupal 7 Module (zip file) - ' .getBasicSatelliteVersion(). '</a>';
        $content .= '</li>';
    }
    if ($basic) {
        $content .= '<li class="bmlt_versions_li_basic">';
            $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-basic/raw/release/bmlt-basic.zip">Basic Satellite (zip file) - ' .getBasicSatelliteVersion(). '</a>';
        $content .= '</li>';
    }
    if ($crouton) {
        $content .= '<li class="bmlt_versions_li_crouton">';
            $content .= '<a href ="https://wordpress.org/plugins/crouton/">Crouton (Tabbed UI) Plugin - ' .getCroutonVersion(). '</a>';
        $content .= '</li>';
    }
    if ($bread) {
        $content .= '<li class="bmlt_versions_li_bread">';
            $content .= '<a href ="https://wordpress.org/plugins/bread/">Bread (Meeting List Generator) Plugin - ' .getBreadVersion(). '</a>';
        $content .= '</li>';
    }
    if ($yap) {
        $content .= '<li class="bmlt_versions_li_yap">';
            $yap_version = githubLatestReleaseVersion('yap');
            $content .= '<a href ="https://github.com/bmlt-enabled/yap/releases/download/' . $yap_version . '/yap-' . $yap_version . '.zip' .'">Yap (Phone line / zip file) - ' .getYapVersion(). '</a>';
        $content .= '</li>';
    }
$content .= '</ul>';
$content .= '</div>';

return $content;

}

function githubLatestReleaseVersion($repo) {
    $results = wp_remote_get("https://api.github.com/repos/bmlt-enabled/$repo/releases/latest");
    $httpcode = wp_remote_retrieve_response_code( $results );
    $response_message = wp_remote_retrieve_response_message( $results );
    if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
        return 'Problem Connecting to Server!';
    };
    $body = wp_remote_retrieve_body($results);
    $result = json_decode($body, true);
    return $result['name'];
}

function getRootServerVersion() {
    $results = wp_remote_get("https://raw.githubusercontent.com/bmlt-enabled/BMLT-Root-Server/Release/main_server/client_interface/serverInfo.xml");
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

function getBasicSatelliteVersion() {
    $results = wp_remote_get("https://api.github.com/repos/bmlt-enabled/BMLT-Basic/tags");
    $httpcode = wp_remote_retrieve_response_code( $results );
    $response_message = wp_remote_retrieve_response_message( $results );
    if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && ! empty( $response_message )) {
        return 'Problem Connecting to Server!';
    };
    $body = wp_remote_retrieve_body($results);
    $result = json_decode($body, true);
    return $result[0]['name'];
}

// create [bmlt_versions] shortcode
add_shortcode( 'bmlt_versions', 'bmlt_versions_func' );
