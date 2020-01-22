<?php
/*
Plugin Name: BMLT Versions
Plugin URI: https://wordpress.org/plugins/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: BMLT Authors
Author URI: https://bmlt.app
Version: 1.2.2
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

if (!class_exists("bmltVersions")) {
        // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
        // phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
    class bmltVersions
        // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
        // phpcs:enable Squiz.Classes.ValidClassName.NotCamelCaps
    {

        public function __construct()
        {
                add_shortcode('bmlt_versions', array(
                    &$this,
                    "bmltVersionsFunc"
                ));
        }

        public function bmltVersions()
        {
            $this->__construct();
        }

        public function bmltVersionsFunc($atts = [])
        {
            extract(shortcode_atts(array(
                'root_server'       => '1',
                'wordpress'         => '1',
                'drupal'            => '1',
                'basic'             => '1',
                'crouton'           => '1',
                'bread'             => '1',
                'yap'               => '1',
                'tabbed_map'        => '1',
                'meeting_map'       => '1',
                'list_locations'    => '1',
                'upcoming_meetings' => '1',
                'contacts'          => '1'
            ), $atts));

            $root_server = sanitize_text_field($root_server);
            $wordpress = sanitize_text_field($wordpress);
            $drupal = sanitize_text_field($drupal);
            $basic = sanitize_text_field($basic);
            $crouton = sanitize_text_field($crouton);
            $bread = sanitize_text_field($bread);
            $yap = sanitize_text_field($yap);
            $tabbed_map = sanitize_text_field($tabbed_map);
            $meeting_map = sanitize_text_field($meeting_map);
            $list_locations = sanitize_text_field($list_locations);
            $upcoming_meetings = sanitize_text_field($upcoming_meetings);
            $contacts = sanitize_text_field($contacts);

            $content = '';
            $content .= '<div class="bmlt_versions_div">';
            $content .= '<ul class="bmlt_versions_ul">';
            if ($root_server) {
                $content .= '<li class="bmlt_versions_li_root">';
                $rootServer_version = $this->githubLatestReleaseVersion('bmlt-root-server');
                $rootServer_date = $this->githubLatestReleaseDate('bmlt-root-server');
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-root-server/releases/download/' . $rootServer_version . '/bmlt-root-server.zip">Root Server (zip file) - ' . $rootServer_date . '</a>';
                $content .= '</li>';
            }
            if ($wordpress) {
                $wordpress_date = $this->githubLatestReleaseDate('bmlt-wordpress-satellite-plugin');
                $content .= '<li class="bmlt_versions_li_wordpress">';
                $content .= '<a href ="https://wordpress.org/plugins/bmlt-wordpress-satellite-plugin/">WordPress Plugin - ' . $wordpress_date . '</a>';
                $content .= '</li>';
            }
            if ($drupal) {
                $drupal_date = $this->githubLatestReleaseDate('bmlt-drupal');
                $content .= '<li class="bmlt_versions_li_drupal">';
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-drupal/raw/master/bmlt-drupal7.zip">Drupal 7 Module (zip file) - ' . $drupal_date . '</a>';
                $content .= '</li>';
            }
            if ($basic) {
                $content .= '<li class="bmlt_versions_li_basic">';
                $basic_version = $this->githubLatestReleaseVersion('bmlt-basic');
                $basic_date = $this->githubLatestReleaseDate('bmlt-basic');
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-basic/releases/download/' . $basic_version . '/bmlt-basic.zip">Basic Satellite (zip file) - ' . $basic_date . '</a>';
                $content .= '</li>';
            }
            if ($crouton) {
                $crouton_date = $this->githubLatestReleaseDate('crouton');
                $content .= '<li class="bmlt_versions_li_crouton">';
                $content .= '<a href ="https://wordpress.org/plugins/crouton/">Crouton (Tabbed UI) Plugin - ' . $crouton_date . '</a>';
                $content .= '</li>';
            }
            if ($bread) {
                $bread_date = $this->githubLatestReleaseDate('bread');
                $content .= '<li class="bmlt_versions_li_bread">';
                $content .= '<a href ="https://wordpress.org/plugins/bread/">Bread (Meeting List Generator) Plugin - ' . $bread_date . '</a>';
                $content .= '</li>';
            }
            if ($tabbed_map) {
                $bmlt_tabbed_map_date = $this->githubLatestReleaseDate('bmlt_tabbed_map');
                $content .= '<li class="bmlt_versions_li_tabbed_map">';
                $content .= '<a href ="https://wordpress.org/plugins/bmlt-tabbed-map/">Tabbed Map Plugin - ' . $bmlt_tabbed_map_date . '</a>';
                $content .= '</li>';
            }
            if ($meeting_map) {
                $content .= '<li class="bmlt_versions_li_meeting_map">';
                $bmlt_meeting_map_version = $this->githubLatestReleaseVersion('bmlt-meeting-map');
                $bmlt_meeting_map_date = $this->githubLatestReleaseDate('bmlt-meeting-map');
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-meeting-map/releases/download/' . $bmlt_meeting_map_version . '/bmlt-meeting-map.zip' . '">Meeting Map Plugin - ' . $bmlt_meeting_map_date . '</a>';
                $content .= '</li>';
            }
            if ($list_locations) {
                $list_locations_date = $this->githubLatestReleaseDate('list-locations-bmlt');
                $content .= '<li class="bmlt_versions_li_list_locations">';
                $content .= '<a href ="https://wordpress.org/plugins/list-locations-bmlt/">List Locations Plugin - ' . $list_locations_date . '</a>';
                $content .= '</li>';
            }
            if ($upcoming_meetings) {
                $upcoming_meetings_date = $this->githubLatestReleaseDate('upcoming-meetings-bmlt');
                $content .= '<li class="bmlt_versions_li_upcoming_meetings">';
                $content .= '<a href ="https://wordpress.org/plugins/upcoming-meetings-bmlt/">Upcoming Meetings Plugin - ' . $upcoming_meetings_date . '</a>';
                $content .= '</li>';
            }
            if ($contacts) {
                $contacts_date = $this->githubLatestReleaseDate('contacts-bmlt');
                $content .= '<li class="bmlt_versions_li_contacts">';
                $content .= '<a href ="https://wordpress.org/plugins/contacts-bmlt/">Contacts Plugin - ' . $contacts_date . '</a>';
                $content .= '</li>';
            }
            if ($yap) {
                $content .= '<li class="bmlt_versions_li_yap">';
                $yap_version = $this->githubLatestReleaseVersion('yap');
                $yap_release_date = $this->githubLatestReleaseDate('yap');
                $content .= '<a href ="https://github.com/bmlt-enabled/yap/releases/download/' . $yap_version . '/yap-' . $yap_version . '.zip' . '">Yap (Phone line / zip file) - ' . $yap_release_date . '</a>';
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
            return $result['tag_name'];
        }

        public function githubLatestReleaseDate($repo)
        {
            $results = $this->get("https://api.github.com/repos/bmlt-enabled/$repo/releases/latest");
            $httpcode = wp_remote_retrieve_response_code($results);
            $response_message = wp_remote_retrieve_response_message($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
                return 'Problem Connecting to Server!' . $httpcode ;
            }
            $body = wp_remote_retrieve_body($results);
            $result = json_decode($body, true);
            $releaseDate = date("m-d-Y", strtotime($result['published_at']));
            $versionDate = ' (' . $releaseDate . ') - ' . $result['tag_name'];
            return $versionDate;
        }

        public function get($url, $cookies = null)
        {
            $args = array(
                'timeout' => '120',
                'headers' => array(
                    'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bmltVersions',
                    'Authorization' => 'token API_KEY_HERE'
                ),
                'cookies' => isset($cookies) ? $cookies : null
            );

            return wp_remote_get($url, $args);
        }
    }
}

if (class_exists("bmltVersions")) {
    $bmltVersions_instance = new bmltVersions();
}
