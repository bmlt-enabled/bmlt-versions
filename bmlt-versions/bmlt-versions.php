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


/**
 * 
 * 
 * https://codex.wordpress.org/Settings_API
 * 
 */

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
                add_shortcode('bmlt_versions_simple_sidebar', array(&$this, "bmltVersionsSimpleSidebarFunc"));
            }
        }

        public function bmltVersionsOptionsPage()
        {
            add_menu_page('BMLT Versions', 'BMLT Versions', 'administrator', 'bmlt-versions', 'bmltVersionsOptionsPage');
        }

        public function enqueueFrontendFiles()
        {
            wp_enqueue_style('bmlt-versions-css', plugins_url('css/bmlt-versions.css', __FILE__), false, '1.0.1', false);
        }

        public function bmltVersionsRegisterSettings()
        {
            register_setting('bmltVersionsGroup', 'bmltVersionsGithubApiKey', 'bmltVersionsCallback');
            register_setting('bmltVersionsGroup', 'bmltRootServer', 'bmltVersionsCallback');
            register_setting('bmltVersionsGroup', 'bmltCrouton', 'bmltVersionsCallback');
            register_setting('bmltVersionsGroup', 'bmltBread', 'bmltVersionsCallback');
            register_setting('bmltVersionsGroup', 'bmltYap', 'bmltVersionsCallback');
        }

        //     add_settings_section('bmlt_versions_settings', 'BMLT Versions Settings', 'bmltVersionsCallback', 'bmlt-versions');

      
        // add_settings_fields('bmltVersionsGithubApiKey', 'Github API', 'bmltVersionsCallback', 'bmlt_versions_settings' );
        // add_option('bmltRootServer');
        // add_option('bmltCrouton');
        // add_option('bmltBread');
        // add_option('bmltYap');
        
        
        public function bmltVersionsAdminOptionsPage()
        {
            ?>
            <div>
                <h2>BMLT Versions</h2>
                <form method="post" action="options.php">
                    <?php settings_fields('bmltVersionsGroup'); ?>
                    <?php do_settings_sections('bmltVersionsGroup'); ?>
                    <table>
                        <tr valign="top">
                            <th scope="row"><label for="bmltVersionsGithubApiKey">GitHub API Token</label></th>
                            <td><input type="text" id="bmltVersionsGithubApiKey" name="bmltVersionsGithubApiKey" value="<?php echo get_option('bmltVersionsGithubApiKey'); ?>" /></td>
                        </tr>

                    <h3>Documentation Links</h3>
                    
                        <tr valign="top">
                            <th scope="row"><label for="bmltRootServer">Root Server Documentation</label></th>
                            <td>
                            <input type="text" id="bmltRootServer" name="bmltRootServer" value="<?php echo get_option('bmltRootServer'); ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="bmltCrouton">Crouton Documentation</label></th>
                            <td>
                            <input type="text" id="bmltCrouton" name="bmltCrouton" value="<?php echo get_option('bmltCrouton'); ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="bmltBread">Bread Documentation</label></th>
                            <td>
                            <input type="text" id="bmltBread" name="bmltBread" value="<?php echo get_option('bmltBread'); ?>"/>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="bmltYap">Yap Documentation</label></th>
                            <td>
                            <input type="text" id="bmltYap" name="bmltYap" value="<?php echo get_option('bmltYap'); ?>"/>
                            </td>
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

        // Simple Sidebar Shortcode
        public function bmltVersionsSimpleSidebarFunc($atts = [])
        {
            $args = shortcode_atts(
                array(
                    'root_server'        => '1',
                    'crouton'            => '1',
                    'bread'              => '1',
                    'yap'                => '1',
                ),
                $atts
            );

            $root_server = sanitize_text_field($args['root_server']);
            $crouton = sanitize_text_field($args['crouton']);
            $bread = sanitize_text_field($args['bread']);
            $yap = sanitize_text_field($args['yap']);

            $crouton_link = get_option('bmltCrouton');
            $root_server_link = get_option('bmltRootServer');
            $yap_Link = get_option('bmltYap');
            $bread_link = get_option('bmltBread');


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
                $content .= 'Latest Release <br> ' . $rootServer_version .  $rootServer_date . '</strong>';
                $content .= '</li>';
                $content .= '<li class="bmlt_versions_li">';
                $content .= '<a href ="https://github.com/bmlt-enabled/bmlt-root-server" id="bmlt_versions_release">View On Github</a></strong>';
                $content .= '</li>';
                $content .= '<li class="bmlt_versions_li">';
                $content .= '<a href="' . $root_server_link . '">View Documentation</a>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }

            if ($crouton) {
                $content .= '<div class="bmlt_versions_div github">';
                $content .= '<ul class="bmlt_versions_ul">';
                $content .= '<li class="bmlt_versions_li" id="bmlt-versions-crouton">';
                $crouton_response = $this->githubLatestReleaseInfo('crouton');
                $crouton_version = $this->githubLatestReleaseVersion($crouton_response);
                $crouton_date = $this->githubLatestReleaseDate($crouton_response);
                $content .= '<strong>Crouton (Tabbed UI)</strong><br>';
                $content .= $this->githubReleaseDescription('crouton') . '<br><br>';
                $content .= 'Latest Release <br> ' . $crouton_version .  $crouton_date . '</strong>';
                $content .= '</li>';
                $content .= '<li class="bmlt_versions_li">';
                $content .= '<a href ="https://github.com/bmlt-enabled/crouton" id="bmlt_versions_release">View On Github</a></strong>';
                $content .= '</li>';
                $content .= '<li class="bmlt_versions_li">';
                $content .= '<a href="' . $crouton_link . '">View Documentation</a>';
                $content .= '</li>';
                $content .= '</ul>';
                $content .= '</div>';
            }



            // if ($crouton) {
            //     $content .= '<div class="bmlt_versions_div wordpress">';
            //     $content .= '<ul class="bmlt_versions_ul">';
            //     $crouton_response = $this->githubLatestReleaseInfo('crouton');
            //     $crouton_date = $this->githubLatestReleaseDate($crouton_response);
            //     $content .= '<li class="bmlt_versions_li" id="bmlt-versions-crouton">';
            //     $content .= '<strong>Crouton (Tabbed UI)</strong><br>';
            //     // $content .= $this->githubReleaseDescription('crouton') . '<br><br>';
            //     $content .= 'Latest Release : <strong><a href ="https://wordpress.org/plugins/crouton/" id="bmlt_versions_release" >' .$crouton_date. '</a></strong>';
            //     $content .= '</li>';
            //     $content .= '<li class="bmlt_versions_li">';
            //     $content .= '<a href="' . $crouton_link . '">View Documentation</a>';
            //     $content .= '</li>';
            //     $content .= '</ul>';
            //     $content .= '</div>';
            // }
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

