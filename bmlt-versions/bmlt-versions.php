<?php
/*
Plugin Name: BMLT Versions
Plugin URI: https://wordpress.org/plugins/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: BMLT Authors
Author URI: https://bmlt.app
Version: 1.1.5
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

if (!class_exists("BmltVersions")) {
    class BmltVersions
    {

        public function __construct()
        {
                add_shortcode('bmlt_versions', array(
                    &$this,
                    "bmltVersionsFunc"
                ));
        }

        public function BmltVersions()
        {
            $this->__construct();
        }

        public function bmltVersionsFunc($atts = [])
        {
            extract(shortcode_atts(array(
                'root_server' => '1',
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
                $rootServer_version = $this->githubLatestReleaseVersion('bmlt-root-server');
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-root-server/releases/download/' . $rootServer_version . '/bmlt-root-server.zip">Root Server (zip file) - ' . $rootServer_version . '</a>';
                $content .= '</li>';
            }
            if ($wordpress) {
                $content .= '<li class="bmlt_versions_li_wordpress">';
                $content .= '<a href ="https://wordpress.org/plugins/bmlt-wordpress-satellite-plugin/">WordPress Plugin - ' . $this->getWordpressPluginLatestVersion('bmlt-wordpress-satellite-plugin') . '</a>';
                $content .= '</li>';
            }
            if ($drupal) {
                $content .= '<li class="bmlt_versions_li_drupal">';
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-drupal/raw/master/bmlt-drupal7.zip">Drupal 7 Module (zip file) - ' . $this->getBasicSatelliteVersion() . '</a>';
                $content .= '</li>';
            }
            if ($basic) {
                $content .= '<li class="bmlt_versions_li_basic">';
                $basic_version = $this->githubLatestReleaseVersion('bmlt-basic');
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-basic/releases/download/' . $basic_version . '/bmlt-basic.zip">Basic Satellite (zip file) - ' . $basic_version . '</a>';
                $content .= '</li>';
            }
            if ($crouton) {
                $content .= '<li class="bmlt_versions_li_crouton">';
                $content .= '<a href ="https://wordpress.org/plugins/crouton/">Crouton (Tabbed UI) Plugin - ' . $this->getWordpressPluginLatestVersion('crouton') . '</a>';
                $content .= '</li>';
            }
            if ($bread) {
                $content .= '<li class="bmlt_versions_li_bread">';
                $content .= '<a href ="https://wordpress.org/plugins/bread/">Bread (Meeting List Generator) Plugin - ' . $this->getWordpressPluginLatestVersion('bread', 'bmlt-meeting-list') . '</a>';
                $content .= '</li>';
            }
            if ($yap) {
                $content .= '<li class="bmlt_versions_li_yap">';
                $yap_version = $this->githubLatestReleaseVersion('yap');
                $content .= '<a href ="https://github.com/bmlt-enabled/yap/releases/download/' . $yap_version . '/yap-' . $yap_version . '.zip' . '">Yap (Phone line / zip file) - ' . $yap_version . '</a>';
                $content .= '</li>';
            }
            $content .= '</ul>';
            $content .= '</div>';

            return $content;
        }

        public function githubLatestReleaseVersion($repo)
        {
            $results = $this->get("https://api.github.com/repos/bmlt-enabled/$repo/releases/latest");
            $httpcode = wp_remote_retrieve_response_code($results);
            $response_message = wp_remote_retrieve_response_message($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
                return 'Problem Connecting to Server!';
            }
            $body = wp_remote_retrieve_body($results);
            $result = json_decode($body, true);
            return $result['name'];
        }

        public function getWordpressPluginLatestVersion($repo, $file = null)
        {
            if ($file == null) {
                $file = $repo;
            }
            $results = $this->get("https://plugins.svn.wordpress.org/$repo/trunk/$file.php");
            $httpcode = wp_remote_retrieve_response_code($results);
            $response_message = wp_remote_retrieve_response_message($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
                return 'Problem Connecting to Server!';
            }
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

        public function getBasicSatelliteVersion()
        {
            $results = $this->get("https://api.github.com/repos/bmlt-enabled/BMLT-Basic/tags");
            $httpcode = wp_remote_retrieve_response_code($results);
            $response_message = wp_remote_retrieve_response_message($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
                return 'Problem Connecting to Server!';
            }
            $body = wp_remote_retrieve_body($results);
            $result = json_decode($body, true);
            return $result[0]['name'];
        }

        public function get($url, $cookies = null)
        {
            $args = array(
                'timeout' => '120',
                'headers' => array(
                    'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +BmltVersions'
                ),
                'cookies' => isset($cookies) ? $cookies : null
            );

            return wp_remote_get($url, $args);
        }
    }
}

if (class_exists("BmltVersions")) {
    $BmltVersions_instance = new BmltVersions();
}
