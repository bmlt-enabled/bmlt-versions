<?php
/*
Plugin Name: BMLT Versions
Plugin URI: https://github.com/bmlt-enabled/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: BMLT Authors
Author URI: https://bmlt.app
Version: 1.5.0
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
            if (is_admin()) {
                // Back end
                add_action("admin_menu", array(&$this, "bmltVersionsOptionsPage"));
                add_action("admin_init", array(&$this, "bmltVersionsRegisterSettings"));
            } else {
                // Front end
                add_action("wp_enqueue_scripts", array(&$this, "enqueueFrontendFiles"));
                add_shortcode('bmlt_versions', array(&$this, "bmltVersionsFunc"));
                add_shortcode('bmlt_versions_simple', array(&$this, "bmltVersionsSimpleFunc"));
            }
        }

        public function enqueueFrontendFiles()
        {
            wp_enqueue_style('bmlt-versions-css', plugins_url('css/bmlt-versions.css', __FILE__), false, '1.0.1', false);
        }

        public function bmltVersionsRegisterSettings()
        {
            add_option('bmltVersionsGithubApiKey', 'Github API Key.');
            register_setting('bmltVersionsOptionGroup', 'bmltVersionsGithubApiKey', 'bmltVersionsCallback');
        }

        public function bmltVersionsOptionsPage()
        {
            add_options_page('BMLT Versions', 'BMLT Versions', 'manage_options', 'bmlt-versions', array(
                &$this,
                'bmltVersionsAdminOptionsPage'
            ));
        }
        public function bmltVersionsAdminOptionsPage()
        {
            ?>
            <div>
                <h2>BMLT Versions</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('bmltVersionsOptionGroup'); ?>
                    <table>
                        <tr valign="top">
                            <th scope="row"><label for="bmltVersionsGithubApiKey">GitHub API Token</label></th>
                            <td><input type="text" id="bmltVersionsGithubApiKey" name="bmltVersionsGithubApiKey" value="<?php echo get_option('bmltVersionsGithubApiKey'); ?>" /></td>
                        </tr>
                    </table>
                    <?php  submit_button(); ?>
                </form>
            </div>
            <?php
        }

        public function bmltVersions()
        {
            $this->__construct();
        }

        public function bmltVersionsSimpleFunc($atts = [])
        {
            $argsSimple = shortcode_atts(
                array(
                    'root_server'        => '1',
                    'wordpress'          => '1',
                    'drupal'             => '1',
                    'basic'              => '1',
                    'crouton'            => '1',
                    'bread'              => '1',
                    'yap'                => '1',
                    'tabbed_map'         => '1',
                    'meeting_map'        => '1',
                    'list_locations'     => '1',
                    'upcoming_meetings'  => '1',
                    'contacts'           => '1',
                    'temporary_closures' => '1'
                ),
                $atts
            );

            $root_server = sanitize_text_field($argsSimple['root_server']);
            $wordpress = sanitize_text_field($argsSimple['wordpress']);
            $drupal = sanitize_text_field($argsSimple['drupal']);
            $basic = sanitize_text_field($argsSimple['basic']);
            $crouton = sanitize_text_field($argsSimple['crouton']);
            $bread = sanitize_text_field($argsSimple['bread']);
            $yap = sanitize_text_field($argsSimple['yap']);
            $tabbed_map = sanitize_text_field($argsSimple['tabbed_map']);
            $meeting_map = sanitize_text_field($argsSimple['meeting_map']);
            $list_locations = sanitize_text_field($argsSimple['list_locations']);
            $upcoming_meetings = sanitize_text_field($argsSimple['upcoming_meetings']);
            $contacts = sanitize_text_field($argsSimple['contacts']);
            $temporary_closures = sanitize_text_field($argsSimple['temporary_closures']);

            $rootServer_version = $this->githubLatestReleaseVersion('bmlt-root-server');

            $content = '';
            if ($root_server) {
                $content .= '<div class="bmlt_versions_simple_div">';
                $content .= 'Current version of BMLT:  ';
                $content .= $rootServer_version;
                $content .= '</div>';
            }

            return $content;
        }
        public function bmltVersionsFunc($atts = [])
        {
            $args = shortcode_atts(
                array(
                    'root_server'        => '1',
                    'wordpress'          => '1',
                    'drupal'             => '1',
                    'basic'              => '1',
                    'crouton'            => '1',
                    'bread'              => '1',
                    'yap'                => '1',
                    'tabbed_map'         => '1',
                    'meeting_map'        => '1',
                    'list_locations'     => '1',
                    'upcoming_meetings'  => '1',
                    'contacts'           => '1',
                    'temporary_closures' => '1'
                ),
                $atts
            );

            $root_server = sanitize_text_field($args['root_server']);
            $wordpress = sanitize_text_field($args['wordpress']);
            $drupal = sanitize_text_field($args['drupal']);
            $basic = sanitize_text_field($args['basic']);
            $crouton = sanitize_text_field($args['crouton']);
            $bread = sanitize_text_field($args['bread']);
            $yap = sanitize_text_field($args['yap']);
            $tabbed_map = sanitize_text_field($args['tabbed_map']);
            $meeting_map = sanitize_text_field($args['meeting_map']);
            $list_locations = sanitize_text_field($args['list_locations']);
            $upcoming_meetings = sanitize_text_field($args['upcoming_meetings']);
            $contacts = sanitize_text_field($args['contacts']);
            $temporary_closures = sanitize_text_field($args['temporary_closures']);

            $content = '';
            if ($root_server) {
                $content .= '<div class="bmlt_versions_div github">';
                $content .= '<ul class="bmlt_versions_ul">';
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-root">';
                $rootServer_response = $this->githubLatestReleaseInfo('bmlt-root-server');
                $rootServer_version = $this->githubLatestReleaseVersion($rootServer_response);
                $rootServer_date = $this->githubLatestReleaseDate($rootServer_response);
                $content .= '<strong>Root Server</strong><br>';
                $content .= $this->githubReleaseDescription('bmlt-root-server') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/bmlt-root-server/releases/download/' . $rootServer_version . '/bmlt-root-server.zip" id="bmlt_versions_release">' . $rootServer_date . '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($wordpress) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $wordpress_response = $this->githubLatestReleaseInfo('bmlt-wordpress-satellite-plugin');
                $wordpress_date = $this->githubLatestReleaseDate($wordpress_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-wordpress">';
                $content .= '<strong>WordPress Plugin</strong><br>';
                $content .= $this->githubReleaseDescription('bmlt-wordpress-satellite-plugin') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bmlt-wordpress-satellite-plugin/" id="bmlt_versions_release">' . $wordpress_date . '</a></strong><br>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($drupal) {
                $content .= '<div class="bmlt_versions_div drupal">';
                $content .= '<ul class="bmlt_versions_ul">';
                $drupal_response = $this->githubLatestReleaseInfo('bmlt-drupal');
                $drupal_date = $this->githubLatestReleaseDate($drupal_response);
                $drupal_version = $this->githubLatestReleaseVersion($drupal_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-drupal">';
                $content .= '<strong>Drupal 7 Module</strong><br>';
                $content .= $this->githubReleaseDescription('bmlt-drupal') . '<br><br>';
                $content .= 'Latest Release : ' . '<strong><a href ="https://github.com/bmlt-enabled/bmlt-drupal/releases/download/' . $drupal_version . '/bmlt-drupal7.zip" id="bmlt_versions_release">' . $drupal_date . '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($basic) {
                $content .= '<div class="bmlt_versions_div github">';
                $content .= '<ul class="bmlt_versions_ul">';
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-basic">';
                $basic_response = $this->githubLatestReleaseInfo('bmlt-basic');
                $basic_version = $this->githubLatestReleaseVersion($basic_response);
                $basic_date = $this->githubLatestReleaseDate($basic_response);
                $content .= '<strong>Basic Satellite</strong><br>';
                $content .= $this->githubReleaseDescription('bmlt-basic') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/bmlt-basic/releases/download/' . $basic_version . '/bmlt-basic.zip" id="bmlt_versions_release">' . $basic_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($crouton) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $crouton_response = $this->githubLatestReleaseInfo('crouton');
                $crouton_date = $this->githubLatestReleaseDate($crouton_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-crouton">';
                $content .= '<strong>Crouton (Tabbed UI)</strong><br>';
                $content .= $this->githubReleaseDescription('crouton') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/crouton/" id="bmlt_versions_release" >' .$crouton_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($bread) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $bread_response = $this->githubLatestReleaseInfo('bread');
                $bread_date = $this->githubLatestReleaseDate($bread_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-bread">';
                $content .= '<strong>Bread (Meeting List Generator)</strong><br>';
                $content .= $this->githubReleaseDescription('bread') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bread/" id="bmlt_versions_release" >' .$bread_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($tabbed_map) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $bmlt_tabbed_map_response = $this->githubLatestReleaseInfo('bmlt_tabbed_map');
                $bmlt_tabbed_map_date = $this->githubLatestReleaseDate($bmlt_tabbed_map_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-tabbed_map">';
                $content .= '<strong>Tabbed Map</strong><br>';
                $content .= $this->githubReleaseDescription('bmlt_tabbed_map') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bmlt-tabbed-map/" id="bmlt_versions_release">' .$bmlt_tabbed_map_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($meeting_map) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-meeting_map">';
                $bmlt_meeting_response = $this->githubLatestReleaseInfo('bmlt-meeting-map');
                $bmlt_meeting_map_date = $this->githubLatestReleaseDate($bmlt_meeting_response);
                $content .= '<strong>Meeting Map</strong><br>';
                $content .= $this->githubReleaseDescription('bmlt-meeting-map') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bmlt-meeting-map/" id="bmlt_versions_release">' .$bmlt_meeting_map_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($list_locations) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $list_locations_response = $this->githubLatestReleaseInfo('list-locations-bmlt');
                $list_locations_date = $this->githubLatestReleaseDate($list_locations_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-list-locations">';
                $content .= '<strong>List Locations</strong><br>';
                $content .= $this->githubReleaseDescription('list-locations-bmlt') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/list-locations-bmlt/" id="bmlt_versions_release">' .$list_locations_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($upcoming_meetings) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $upcoming_meetings_response = $this->githubLatestReleaseInfo('upcoming-meetings-bmlt');
                $upcoming_meetings_date = $this->githubLatestReleaseDate($upcoming_meetings_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-upcoming-meetings">';
                $content .= '<strong>Upcoming Meetings</strong><br>';
                $content .= $this->githubReleaseDescription('upcoming-meetings-bmlt') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/upcoming-meetings-bmlt/" id="bmlt_versions_release">' .$upcoming_meetings_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }
            if ($contacts) {
                $content .= '<div class="bmlt_versions_div wordpress">';
                $content .= '<ul class="bmlt_versions_ul">';
                $contacts_response = $this->githubLatestReleaseInfo('upcoming-meetings-bmlt');
                $contacts_date = $this->githubLatestReleaseDate($contacts_response);
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-contacts">';
                $content .= '<strong>Contacts</strong><br>';
                $content .= $this->githubReleaseDescription('contacts-bmlt') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/contacts-bmlt/" id="bmlt_versions_release">' . $contacts_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }

            if ($yap) {
                $content .= '<div class="bmlt_versions_div github">';
                $content .= '<ul class="bmlt_versions_ul">';
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-yap">';
                $yap_response = $this->githubLatestReleaseInfo('yap');
                $yap_version = $this->githubLatestReleaseVersion($yap_response);
                $yap_release_date = $this->githubLatestReleaseDate($yap_response);
                $content .= '<strong>Yap</strong><br>';
                $content .= $this->githubReleaseDescription('yap') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/yap/releases/download/' . $yap_version . '/yap-' . $yap_version . '.zip' . '" id="bmlt_versions_release">' . $yap_release_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }

            if ($temporary_closures) {
                $content .= '<div class="bmlt_versions_div github">';
                $content .= '<ul class="bmlt_versions_ul">';
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-temporary-closures">';
                $temporary_closures_response = $this->githubLatestReleaseInfo('temporary-closures-bmlt');
                $temporary_closures_version = $this->githubLatestReleaseVersion($temporary_closures_response);
                $temporary_closures_release_date = $this->githubLatestReleaseDate($temporary_closures_response);
                $content .= '<strong>Yap</strong><br>';
                $content .= $this->githubReleaseDescription('temporary-closures-bmlt') . '<br><br>';
                $content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/temporary-closures-bmlt/releases/download/' . $temporary_closures_version . '/temporary-closures-bmlt.zip' . '" id="bmlt_versions_release">' . $temporary_closures_release_date. '</a></strong>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }

            return $content;
        }

        public function githubLatestReleaseInfo($repo)
        {
            $results = $this->get("https://api.github.com/repos/bmlt-enabled/$repo/releases/latest");
            $httpcode = wp_remote_retrieve_response_code($results);
            $response_message = wp_remote_retrieve_response_message($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
                return 'Problem Connecting to Server!';
            }
            $body = wp_remote_retrieve_body($results);
            $result = json_decode($body, true);
            return $result;
        }

        public function githubLatestReleaseVersion($result)
        {
            return $result['tag_name'];
        }

        public function githubLatestReleaseDate($result)
        {
            $releaseDate = date("m-d-Y", strtotime($result['published_at']));
            $versionDate = $result['tag_name'] . ' (' . $releaseDate . ')';
            return $versionDate;
        }

        public function githubReleaseDescription($repo)
        {
            $results = $this->get("https://api.github.com/repos/bmlt-enabled/$repo");
            $httpcode = wp_remote_retrieve_response_code($results);
            $response_message = wp_remote_retrieve_response_message($results);
            if ($httpcode != 200 && $httpcode != 302 && $httpcode != 304 && !empty($response_message)) {
                return 'Problem Connecting to Server!';
            }
            $body = wp_remote_retrieve_body($results);
            $result = json_decode($body, true);
            $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
            $description = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $result['description']);
            return $description;
        }

        public function get($url, $cookies = null)
        {
            $gitHubApiKey = get_option('bmltVersionsGithubApiKey');

            $args = array(
                'timeout' => '120',
                'headers' => array(
                    'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0) +bmltVersions',
                    'Authorization' => "token $gitHubApiKey"
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
