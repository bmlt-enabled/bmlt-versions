<?php
/*
Plugin Name: BMLT Versions 
Plugin URI: https://github.com/bmlt-enabled/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: BMLT Authors
Author URI: https://bmlt.app
Version: 1.7.0
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
            add_option('rootServerDoc', 'Root Server Documentation Link');
            add_option('croutonDoc', 'Crouton Documentation Link');
            add_option('yapDoc', 'Yap Documentation Link');
            add_option('breadDoc', 'Bread Documentation Link');
            add_option('bmltVersionsGithubApiKey', 'Github API Key.');
            register_setting('bmltVersionsOptionGroup', 'bmltVersionsGithubApiKey', 'bmltVersionsCallback');
            register_setting('bmltVersionsOptionGroup', 'rootServerDoc', 'bmltVersionsCallback');
            register_setting('bmltVersionsOptionGroup', 'croutonDoc', 'bmltVersionsCallback');
            register_setting('bmltVersionsOptionGroup', 'yapDoc', 'bmltVersionsCallback');
            register_setting('bmltVersionsOptionGroup', 'breadDoc', 'bmltVersionsCallback');
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
                <p>You must activate a github personal access token to use this plugin. Instructions can be found here <a herf="https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token">https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token</a>.</p>
                <p>Links for documentation are optional and only configured for [bmlt_versions_simple]. You can find all the the documentations pages here <a href="https://bmlt.app">https://bmlt.app</a>. If inputs are left blank, "View Documentation" link will not display on the front end</p>
                <form method="post" action="options.php">
                    <?php settings_fields('bmltVersionsOptionGroup'); ?>
                    <table>
                        <tr valign="top">
                            <th scope="row"><label for="bmltVersionsGithubApiKey">GitHub API Token</label></th>
                            <td><input type="text" id="bmltVersionsGithubApiKey" name="bmltVersionsGithubApiKey" value="<?php echo get_option('bmltVersionsGithubApiKey'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="rootServerDoc">Root Server Documentation</label></th>
                            <td><input type="text" id="rootServerDoc" name="rootServerDoc" value="<?php echo get_option('rootServerDoc'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="croutonDoc">Crouton Documentation</label></th>
                            <td><input type="text" id="croutonDoc" name="croutonDoc" value="<?php echo get_option('croutonDoc'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="yapDoc">Yap Documentation</label></th>
                            <td><input type="text" id="yapDoc" name="yapDoc" value="<?php echo get_option('yapDoc'); ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="breadDoc">Bread Documentation</label></th>
                            <td><input type="text" id="breadDoc" name="breadDoc" value="<?php echo get_option('breadDoc'); ?>" /></td>
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
            $args = shortcode_atts(
                array(
                    'root_server'        => '1',
                    'crouton'            => '1',
                    'bread'              => '1',
                    'yap'                => '1',
                    'sort_by'            => 'date'
                ),
                $atts
            );

            $root_server = sanitize_text_field($args['root_server']);
            $crouton = sanitize_text_field($args['crouton']);
            $bread = sanitize_text_field($args['bread']);
            $yap = sanitize_text_field($args['yap']);
            $sort_by = sanitize_text_field($args['sort_by']);

            $rootServerDocs = get_option('rootServerDoc');
            $croutonDocs = get_option('croutonDoc');
            $breadDocs = get_option('breadDoc');
            $yapDocs = get_option('yapDoc');

            $content = '';
            $releases = [];
            if ($root_server) {
                $root_server_content = '<div class="bmlt_versions_simple_div root-server">';
                $root_server_content .= '<ul class="bmlt_versions_ul">';
                $root_server_content .= '<li class="bmlt_versions_li" id="bmlt-versions-root">';
                $rootServer_response = $this->githubLatestReleaseInfo('bmlt-root-server');
                $rootServer_version = $this->githubLatestReleaseVersion($rootServer_response);
                $rootServer_date = $this->githubLatestReleaseDate($rootServer_response);
                $rootServer_date_ver = $rootServer_version . ' (' . date("m-d-Y", strtotime($rootServer_date)) . ')';
                $root_server_content .= '<strong>Root Server</strong>';
                $root_server_content .= '</li>';
                $root_server_content .= '<li class="bmlt_versions_li">';
                $root_server_content .= '<strong>Latest Release</br></strong>'. $rootServer_version;
                $root_server_content .= '</li>';
                $root_server_content .= '<li class="bmlt_versions_li">';
                $root_server_content .= '<strong>Release Date</br></strong>'. date("m-d-Y", strtotime($rootServer_date));
                $root_server_content .= '</li>';
                if(!empty($rootServerDocs)) {
                $root_server_content .= '<li class="bmlt_versions_li">';
                $root_server_content .= '<a href="'. $rootServerDocs .'">View Documentation</a>';
                $root_server_content .= '</li>';
                }
                $root_server_content .= '<li class="bmlt_versions_li">';
                $root_server_content .= '<a href="https://github.com/bmlt-enabled/bmlt-root-server" target="_blank">View On Github</a>';
                $root_server_content .= '</li>';
                $root_server_content .= '</ul>';
                $root_server_content .= '</div>';
                $releases[0]['content'] = $root_server_content;
                $releases[0]['name'] = "root-server";
                $releases[0]['date'] = strtotime($rootServer_date);
                $releases[0]['version'] = $rootServer_version;
            }

            if ($crouton) {
                $crouton_content = '<div class="bmlt_versions_simple_div crouton">';
                $crouton_content .= '<ul class="bmlt_versions_ul">';
                $crouton_content .= '<li class="bmlt_versions_li" id="crouton">';
                $crouton_response = $this->githubLatestReleaseInfo('crouton');
                $crouton_version = $this->githubLatestReleaseVersion($crouton_response);
                $crouton_date = $this->githubLatestReleaseDate($crouton_response);
                $crouton_date_ver = $crouton_version . ' (' . date("m-d-Y", strtotime($crouton_date)) . ')';
                $crouton_content .= '<strong>Crouton</strong>';
                $crouton_content .= '</li>';
                $crouton_content .= '<li class="bmlt_versions_li">';
                $crouton_content .= '<strong>Latest Release</br></strong>'. $crouton_version;
                $crouton_content .= '</li>';
                $crouton_content .= '<li class="bmlt_versions_li">';
                $crouton_content .= '<strong>Release Date</br></strong>'. date("m-d-Y", strtotime($crouton_date));
                $crouton_content .= '</li>';
                if(!empty($croutonDocs)) {
                $crouton_content .= '<li class="bmlt_versions_li">';
                $crouton_content .= '<a href="'. $croutonDocs .'">View Documentation</a>';
                $crouton_content .= '</li>';
                }
                $crouton_content .= '<li class="bmlt_versions_li">';
                $crouton_content .= '<a href="https://github.com/bmlt-enabled/crouton" target="_blank">View On Github</a>';
                $crouton_content .= '</li>';
                $crouton_content .= '</ul>';
                $crouton_content .= '</div>';
                $releases[4]['content'] = $crouton_content;
                $releases[4]['name'] = "crouton";
                $releases[4]['date'] = strtotime($crouton_date);
                $releases[4]['version'] = $crouton_version;
            }

            if ($bread) {
                $bread_content = '<div class="bmlt_versions_simple_div bread">';
                $bread_content .= '<ul class="bmlt_versions_ul">';
                $bread_content .= '<li class="bmlt_versions_li" id="bread">';
                $bread_response = $this->githubLatestReleaseInfo('bread');
                $bread_version = $this->githubLatestReleaseVersion($bread_response);
                $bread_date = $this->githubLatestReleaseDate($bread_response);
                $bread_date_ver = $bread_version . ' (' . date("m-d-Y", strtotime($bread_date)) . ')';
                $bread_content .= '<strong>Bread</strong>';
                $bread_content .= '</li>';
                $bread_content .= '<li class="bmlt_versions_li">';
                $bread_content .= '<strong>Latest Release</br></strong>'. $bread_version;
                $bread_content .= '</li>';
                $bread_content .= '<li class="bmlt_versions_li">';
                $bread_content .= '<strong>Release Date</br></strong>'. date("m-d-Y", strtotime($bread_date));
                $bread_content .= '</li>';
                if(!empty($breadDocs)) {
                $bread_content .= '<li class="bmlt_versions_li">';
                $bread_content .= '<a href="'. $breadDocs .'">View Documentation</a>';
                $bread_content .= '</li>';
                }
                $bread_content .= '<li class="bmlt_versions_li">';
                $bread_content .= '<a href="https://github.com/bmlt-enabled/bread" target="_blank">View On Github</a>';
                $bread_content .= '</li>';
                $bread_content .= '</ul>';
                $bread_content .= '</div>';
                $releases[5]['content'] = $bread_content;
                $releases[5]['name'] = "bread";
                $releases[5]['date'] = strtotime($bread_date);
                $releases[5]['version'] = $bread_version;
            }

            if ($yap) {
                $yap_content = '<div class="bmlt_versions_simple_div yap">';
                $yap_content .= '<ul class="bmlt_versions_ul">';
                $yap_content .= '<li class="bmlt_versions_li" id="yap">';
                $yap_response = $this->githubLatestReleaseInfo('yap');
                $yap_version = $this->githubLatestReleaseVersion($yap_response);
                $yap_date = $this->githubLatestReleaseDate($yap_response);
                $yap_date_ver = $yap_version . ' (' . date("m-d-Y", strtotime($yap_date)) . ')';
                $yap_content .= '<strong>Yap</strong>';
                $yap_content .= '</li>';
                $yap_content .= '<li class="bmlt_versions_li">';
                $yap_content .= '<strong>Latest Release</br></strong>'. $yap_version;
                $yap_content .= '</li>';
                $yap_content .= '<li class="bmlt_versions_li">';
                $yap_content .= '<strong>Release Date</br></strong>'. date("m-d-Y", strtotime($yap_date));
                $yap_content .= '</li>';
                if(!empty($yapDocs)) {
                $yap_content .= '<li class="bmlt_versions_li">';
                $yap_content .= '<a href="'. $yapDocs .'">View Documentation</a>';
                $yap_content .= '</li>';
                }
                $yap_content .= '<li class="bmlt_versions_li">';
                $yap_content .= '<a href="https://github.com/bmlt-enabled/yap" target="_blank">View On Github</a>';
                $yap_content .= '</li>';
                $yap_content .= '</ul>';
                $yap_content .= '</div>';
                $releases[12]['content'] = $yap_content;
                $releases[12]['name'] = "yap";
                $releases[12]['date'] = strtotime($yap_date);
                $releases[12]['version'] = $yap_version;
            }

            if ($sort_by == "name") {
                usort($releases, function ($a, $b) {
                    return strnatcasecmp($a['name'], $b['name']);
                });
            } else {
                usort($releases, function ($a, $b) {
                    return strnatcasecmp($b['date'], $a['date']);
                });
            }

            foreach ($releases as $release) {
                echo $release['content'];
            }
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
                    'temporary_closures' => '1',
                    'sort_by'            => 'date'
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
            $sort_by = sanitize_text_field($args['sort_by']);

            $content = '';
            $releases = [];
            if ($root_server) {
                $root_server_content = '<div class="bmlt_versions_div github">';
                $root_server_content .= '<ul class="bmlt_versions_ul">';
                $root_server_content .= '<li class="bmlt_versions_li" id="bmlt-versions-root">';
                $rootServer_response = $this->githubLatestReleaseInfo('bmlt-root-server');
                $rootServer_version = $this->githubLatestReleaseVersion($rootServer_response);
                $rootServer_date = $this->githubLatestReleaseDate($rootServer_response);
                $rootServer_date_ver = $rootServer_version . ' (' . date("m-d-Y", strtotime($rootServer_date)) . ')';
                $root_server_content .= '<strong>Root Server</strong><br>';
                $root_server_content .= $this->githubReleaseDescription('bmlt-root-server') . '<br><br>';
                $root_server_content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/bmlt-root-server/releases/download/' . $rootServer_version . '/bmlt-root-server.zip" id="bmlt_versions_release">' . $rootServer_date_ver . '</a></strong>';
                $root_server_content .= '</li>';
                $root_server_content .= '</ul>';
                $root_server_content .= '</div>';
                $releases[0]['content'] = $root_server_content;
                $releases[0]['name'] = "root-server";
                $releases[0]['date'] = strtotime($rootServer_date);
                $releases[0]['version'] = $rootServer_version;
            }
            if ($wordpress) {
                $wordpress_content = '<div class="bmlt_versions_div wordpress">';
                $wordpress_content .= '<ul class="bmlt_versions_ul">';
                $wordpress_response = $this->githubLatestReleaseInfo('bmlt-wordpress-satellite-plugin');
                $wordpress_date = $this->githubLatestReleaseDate($wordpress_response);
                $wordpress_version = $this->githubLatestReleaseVersion($wordpress_response);
                $wordpress_date_ver = $wordpress_version . ' (' . date("m-d-Y", strtotime($wordpress_date)) . ')';
                $wordpress_content .= '<li class="bmlt_versions_li" id="bmlt-versions-wordpress">';
                $wordpress_content .= '<strong>WordPress Plugin</strong><br>';
                $wordpress_content .= $this->githubReleaseDescription('bmlt-wordpress-satellite-plugin') . '<br><br>';
                $wordpress_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bmlt-wordpress-satellite-plugin/" id="bmlt_versions_release">' . $wordpress_date_ver . '</a></strong><br>';
                $wordpress_content .= '</li>';
                $wordpress_content .= '</ul>';
                $wordpress_content .= '</div>';
                $releases[1]['content'] = $wordpress_content;
                $releases[1]['name'] = "wordpress-satellite-plugin";
                $releases[1]['date'] = strtotime($wordpress_date);
                $releases[1]['version'] = $wordpress_version;
            }
            if ($drupal) {
                $drupal_content = '<div class="bmlt_versions_div drupal">';
                $drupal_content .= '<ul class="bmlt_versions_ul">';
                $drupal_response = $this->githubLatestReleaseInfo('bmlt-drupal');
                $drupal_date = $this->githubLatestReleaseDate($drupal_response);
                $drupal_version = $this->githubLatestReleaseVersion($drupal_response);
                $drupal_date_ver = $drupal_version . ' (' . date("m-d-Y", strtotime($drupal_date)) . ')';
                $drupal_content .= '<li class="bmlt_versions_li" id="bmlt-versions-drupal">';
                $drupal_content .= '<strong>Drupal 7 Module</strong><br>';
                $drupal_content .= $this->githubReleaseDescription('bmlt-drupal') . '<br><br>';
                $drupal_content .= 'Latest Release : ' . '<strong><a href ="https://github.com/bmlt-enabled/bmlt-drupal/releases/download/' . $drupal_version . '/bmlt-drupal7.zip" id="bmlt_versions_release">' . $drupal_date_ver . '</a></strong>';
                $drupal_content .= '</li>';
                $drupal_content .= '</ul>';
                $drupal_content .= '</div>';
                $releases[2]['content'] = $drupal_content;
                $releases[2]['name'] = "drupal";
                $releases[2]['date'] = strtotime($drupal_date);
                $releases[2]['version'] = $drupal_version;
            }
            if ($basic) {
                $basic_content = '<div class="bmlt_versions_div github">';
                $basic_content .= '<ul class="bmlt_versions_ul">';
                $basic_content .= '<li class="bmlt_versions_li" id="bmlt-versions-basic">';
                $basic_response = $this->githubLatestReleaseInfo('bmlt-basic');
                $basic_version = $this->githubLatestReleaseVersion($basic_response);
                $basic_date = $this->githubLatestReleaseDate($basic_response);
                $basic_date_ver = $basic_version . ' (' . date("m-d-Y", strtotime($basic_date)) . ')';
                $basic_content .= '<strong>Basic Satellite</strong><br>';
                $basic_content .= $this->githubReleaseDescription('bmlt-basic') . '<br><br>';
                $basic_content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/bmlt-basic/releases/download/' . $basic_version . '/bmlt-basic.zip" id="bmlt_versions_release">' . $basic_date_ver. '</a></strong>';
                $basic_content .= '</li>';
                $basic_content .= '</ul>';
                $basic_content .= '</div>';
                $releases[3]['content'] = $basic_content;
                $releases[3]['name'] = "basic";
                $releases[3]['date'] = strtotime($basic_date);
                $releases[3]['version'] = $basic_version;
            }
            if ($crouton) {
                $crouton_content = '<div class="bmlt_versions_div wordpress">';
                $crouton_content .= '<ul class="bmlt_versions_ul">';
                $crouton_response = $this->githubLatestReleaseInfo('crouton');
                $crouton_date = $this->githubLatestReleaseDate($crouton_response);
                $crouton_version = $this->githubLatestReleaseVersion($crouton_response);
                $crouton_date_ver = $crouton_version . ' (' . date("m-d-Y", strtotime($crouton_date)) . ')';
                $crouton_content .= '<li class="bmlt_versions_li" id="bmlt-versions-crouton">';
                $crouton_content .= '<strong>Crouton (Tabbed UI)</strong><br>';
                $crouton_content .= $this->githubReleaseDescription('crouton') . '<br><br>';
                $crouton_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/crouton/" id="bmlt_versions_release" >' .$crouton_date_ver. '</a></strong>';
                $crouton_content .= '</li>';
                $crouton_content .= '</ul>';
                $crouton_content .= '</div>';
                $releases[4]['content'] = $crouton_content;
                $releases[4]['name'] = "crouton";
                $releases[4]['date'] = strtotime($crouton_date);
                $releases[4]['version'] = $crouton_version;
            }
            if ($bread) {
                $bread_content = '<div class="bmlt_versions_div wordpress">';
                $bread_content .= '<ul class="bmlt_versions_ul">';
                $bread_response = $this->githubLatestReleaseInfo('bread');
                $bread_date = $this->githubLatestReleaseDate($bread_response);
                $bread_version = $this->githubLatestReleaseVersion($bread_response);
                $bread_date_ver = $bread_version . ' (' . date("m-d-Y", strtotime($bread_date)) . ')';
                $bread_content .= '<li class="bmlt_versions_li" id="bmlt-versions-bread">';
                $bread_content .= '<strong>Bread (Meeting List Generator)</strong><br>';
                $bread_content .= $this->githubReleaseDescription('bread') . '<br><br>';
                $bread_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bread/" id="bmlt_versions_release" >' .$bread_date_ver. '</a></strong>';
                $bread_content .= '</li>';
                $bread_content .= '</ul>';
                $bread_content .= '</div>';
                $releases[5]['content'] = $bread_content;
                $releases[5]['name'] = "bread";
                $releases[5]['date'] = strtotime($bread_date);
                $releases[5]['version'] = $bread_version;
            }
            if ($tabbed_map) {
                $tabbed_map_content = '<div class="bmlt_versions_div wordpress">';
                $tabbed_map_content .= '<ul class="bmlt_versions_ul">';
                $tabbed_map_response = $this->githubLatestReleaseInfo('bmlt_tabbed_map');
                $tabbed_map_date = $this->githubLatestReleaseDate($tabbed_map_response);
                $tabbed_map_version = $this->githubLatestReleaseVersion($tabbed_map_response);
                $tabbed_map_date_ver = $tabbed_map_version . ' (' . date("m-d-Y", strtotime($tabbed_map_date)) . ')';
                $tabbed_map_content .= '<li class="bmlt_versions_li" id="bmlt-versions-tabbed_map">';
                $tabbed_map_content .= '<strong>Tabbed Map</strong><br>';
                $tabbed_map_content .= $this->githubReleaseDescription('bmlt_tabbed_map') . '<br><br>';
                $tabbed_map_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bmlt-tabbed-map/" id="bmlt_versions_release">' .$tabbed_map_date_ver. '</a></strong>';
                $tabbed_map_content .= '</li>';
                $tabbed_map_content .= '</ul>';
                $tabbed_map_content .= '</div>';
                $releases[6]['content'] = $tabbed_map_content;
                $releases[6]['name'] = "tabbed-map";
                $releases[6]['date'] = strtotime($tabbed_map_date);
                $releases[6]['version'] = $tabbed_map_version;
            }
            if ($meeting_map) {
                $meeting_map_content = '<div class="bmlt_versions_div wordpress">';
                $meeting_map_content .= '<ul class="bmlt_versions_ul">';
                $meeting_map_content .= '<li class="bmlt_versions_li" id="bmlt-versions-meeting_map">';
                $meeting_map_response = $this->githubLatestReleaseInfo('bmlt-meeting-map');
                $meeting_map_date = $this->githubLatestReleaseDate($meeting_map_response);
                $meeting_map_version = $this->githubLatestReleaseVersion($meeting_map_response);
                $meeting_map_date_ver = $meeting_map_version . ' (' . date("m-d-Y", strtotime($meeting_map_date)) . ')';
                $meeting_map_content .= '<strong>Meeting Map</strong><br>';
                $meeting_map_content .= $this->githubReleaseDescription('bmlt-meeting-map') . '<br><br>';
                $meeting_map_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/bmlt-meeting-map/" id="bmlt_versions_release">' .$meeting_map_date_ver. '</a></strong>';
                $meeting_map_content .= '</li>';
                $meeting_map_content .= '</ul>';
                $meeting_map_content .= '</div>';
                $releases[7]['content'] = $meeting_map_content;
                $releases[7]['name'] = "meeting-map";
                $releases[7]['date'] = strtotime($meeting_map_date);
                $releases[7]['version'] = $meeting_map_version;
            }
            if ($list_locations) {
                $list_locations_content = '<div class="bmlt_versions_div wordpress">';
                $list_locations_content .= '<ul class="bmlt_versions_ul">';
                $list_locations_response = $this->githubLatestReleaseInfo('list-locations-bmlt');
                $list_locations_date = $this->githubLatestReleaseDate($list_locations_response);
                $list_locations_version = $this->githubLatestReleaseVersion($list_locations_response);
                $list_locations_date_ver = $list_locations_version . ' (' . date("m-d-Y", strtotime($list_locations_date)) . ')';
                $list_locations_content .= '<li class="bmlt_versions_li" id="bmlt-versions-list-locations">';
                $list_locations_content .= '<strong>List Locations</strong><br>';
                $list_locations_content .= $this->githubReleaseDescription('list-locations-bmlt') . '<br><br>';
                $list_locations_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/list-locations-bmlt/" id="bmlt_versions_release">' .$list_locations_date_ver. '</a></strong>';
                $list_locations_content .= '</li>';
                $list_locations_content .= '</ul>';
                $list_locations_content .= '</div>';
                $releases[8]['content'] = $list_locations_content;
                $releases[8]['name'] = "list-locations";
                $releases[8]['date'] = strtotime($list_locations_date);
                $releases[8]['version'] = $list_locations_version;
            }
            if ($upcoming_meetings) {
                $upcoming_meetings_content = '<div class="bmlt_versions_div wordpress">';
                $upcoming_meetings_content .= '<ul class="bmlt_versions_ul">';
                $upcoming_meetings_response = $this->githubLatestReleaseInfo('upcoming-meetings-bmlt');
                $upcoming_meetings_date = $this->githubLatestReleaseDate($upcoming_meetings_response);
                $upcoming_meetings_version = $this->githubLatestReleaseVersion($upcoming_meetings_response);
                $upcoming_meetings_date_ver = $upcoming_meetings_version . ' (' . date("m-d-Y", strtotime($upcoming_meetings_date)) . ')';
                $upcoming_meetings_content .= '<li class="bmlt_versions_li" id="bmlt-versions-upcoming-meetings">';
                $upcoming_meetings_content .= '<strong>Upcoming Meetings</strong><br>';
                $upcoming_meetings_content .= $this->githubReleaseDescription('upcoming-meetings-bmlt') . '<br><br>';
                $upcoming_meetings_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/upcoming-meetings-bmlt/" id="bmlt_versions_release">' .$upcoming_meetings_date_ver. '</a></strong>';
                $upcoming_meetings_content .= '</li>';
                $upcoming_meetings_content .= '</ul>';
                $upcoming_meetings_content .= '</div>';
                $releases[9]['content'] = $upcoming_meetings_content;
                $releases[9]['name'] = "upcoming-meetings";
                $releases[9]['date'] = strtotime($upcoming_meetings_date);
                $releases[9]['version'] = $upcoming_meetings_version;
            }
            if ($contacts) {
                $contacts_content = '<div class="bmlt_versions_div wordpress">';
                $contacts_content .= '<ul class="bmlt_versions_ul">';
                $contacts_response = $this->githubLatestReleaseInfo('upcoming-meetings-bmlt');
                $contacts_date = $this->githubLatestReleaseDate($contacts_response);
                $contacts_version = $this->githubLatestReleaseVersion($contacts_response);
                $contacts_date_ver = $contacts_version . ' (' . date("m-d-Y", strtotime($contacts_date)) . ')';
                $contacts_content .= '<li class="bmlt_versions_li" id="bmlt-versions-contacts">';
                $contacts_content .= '<strong>Contacts</strong><br>';
                $contacts_content .= $this->githubReleaseDescription('contacts-bmlt') . '<br><br>';
                $contacts_content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/contacts-bmlt/" id="bmlt_versions_release">' . $contacts_date_ver. '</a></strong>';
                $contacts_content .= '</li>';
                $contacts_content .= '</ul>';
                $contacts_content .= '</div>';
                $releases[10]['content'] = $contacts_content;
                $releases[10]['name'] = "contacts";
                $releases[10]['date'] = strtotime($contacts_date);
                $releases[10]['version'] = $contacts_version;
            }
            if ($temporary_closures) {
                $temporary_closures_content = '<div class="bmlt_versions_div wordpress">';
                $temporary_closures_content .= '<ul class="bmlt_versions_ul">';
                $temporary_closures_content .= '<li class="bmlt_versions_li" id="bmlt-versions-temporary-closures">';
                $temporary_closures_response = $this->githubLatestReleaseInfo('temporary-closures-bmlt');
                $temporary_closures_date = $this->githubLatestReleaseDate($temporary_closures_response);
                $temporary_closures_version = $this->githubLatestReleaseVersion($temporary_closures_response);
                $temporary_closures_date_ver = $temporary_closures_version . ' (' . date("m-d-Y", strtotime($temporary_closures_date)) . ')';
                $temporary_closures_content .= '<strong>Temporary Closures</strong><br>';
                $temporary_closures_content .= $this->githubReleaseDescription('temporary-closures-bmlt') . '<br><br>';
                $temporary_closures_content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/temporary-closures-bmlt/releases/download/' . $temporary_closures_version . '/temporary-closures-bmlt.zip' . '" id="bmlt_versions_release">' . $temporary_closures_date_ver. '</a></strong>';
                $temporary_closures_content .= '</li>';
                $temporary_closures_content .= '</ul>';
                $temporary_closures_content .= '</div>';
                $releases[11]['content'] = $temporary_closures_content;
                $releases[11]['name'] = "temporary-closures";
                $releases[11]['date'] = strtotime($temporary_closures_date);
                $releases[11]['version'] = $temporary_closures_version;
            }

            if ($yap) {
                $yap_content = '<div class="bmlt_versions_div github">';
                $yap_content .= '<ul class="bmlt_versions_ul">';
                $yap_content .= '<li class="bmlt_versions_li" id="bmlt-versions-yap">';
                $yap_response = $this->githubLatestReleaseInfo('yap');
                $yap_version = $this->githubLatestReleaseVersion($yap_response);
                $yap_date = $this->githubLatestReleaseDate($yap_response);
                $yap_date_ver = $yap_version . ' (' . date("m-d-Y", strtotime($yap_date)) . ')';
                $yap_content .= '<strong>Yap</strong><br>';
                $yap_content .= $this->githubReleaseDescription('yap') . '<br><br>';
                $yap_content .= 'Latest Release : <strong><a href ="https://github.com/bmlt-enabled/yap/releases/download/' . $yap_version . '/yap-' . $yap_version . '.zip' . '" id="bmlt_versions_release">' . $yap_date_ver. '</a></strong>';
                $yap_content .= '</li>';
                $yap_content .= '</ul>';
                $yap_content .= '</div>';
                $releases[12]['content'] = $yap_content;
                $releases[12]['name'] = "yap";
                $releases[12]['date'] = strtotime($yap_date);
                $releases[12]['version'] = $yap_version;
            }
            if ($sort_by == "name") {
                usort($releases, function ($a, $b) {
                    return strnatcasecmp($a['name'], $b['name']);
                });
            } else {
                usort($releases, function ($a, $b) {
                    return strnatcasecmp($b['date'], $a['date']);
                });
            }

            foreach ($releases as $release) {
                echo $release['content'];
            }
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
            return json_decode($body, true);
        }

        public function githubLatestReleaseVersion($result)
        {
            return $result['tag_name'];
        }

        public function githubLatestReleaseDate($result)
        {
            return $result['published_at'];
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
            return preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $result['description']);
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
