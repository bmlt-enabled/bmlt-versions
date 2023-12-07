<?php

/*
Plugin Name: BMLT Versions
Plugin URI: https://github.com/bmlt-enabled/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: bmlt-enabled
Author URI: https://bmlt.app
Version: 1.8.1
Install: Drop this directory into the "wp-content/plugins/" directory and activate it.
*/
/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

    // phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class BmltVersions
        // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace
{
    private static $instance = null;

    public function __construct()
    {
        add_action('init', [$this, 'pluginSetup']);
    }

    public function pluginSetup()
    {
        if (is_admin()) {
            add_action("admin_menu", [$this, "bmltVersionsOptionsPage"]);
            add_action("admin_init", [$this, "bmltVersionsRegisterSettings"]);
        } else {
            add_action("wp_enqueue_scripts", [$this, "enqueueFrontendFiles"]);
            add_shortcode('bmlt_versions', [$this, "bmltVersionsFunc"]);
            add_shortcode('bmlt_versions_simple', [$this, "bmltVersionsSimpleFunc"]);
        }
    }

    public function enqueueFrontendFiles()
    {
        wp_enqueue_style('bmlt-versions-css', plugins_url('css/bmlt-versions.css', __FILE__), false, '1.0.1', false);
    }

    public function bmltVersionsRegisterSettings()
    {
        $options = [
            'rootServerDoc' => 'Root Server Documentation Link',
            'croutonDoc' => 'Crouton Documentation Link',
            'yapDoc' => 'Yap Documentation Link',
            'breadDoc' => 'Bread Documentation Link',
            'bmltVersionsGithubApiKey' => 'Github API Key.',
            'workflowDoc' => 'BMLT Workflow Documentation Link'
        ];

        foreach ($options as $key => $value) {
            add_option($key, $value);
            register_setting('bmltVersionsOptionGroup', $key, 'bmltVersionsCallback');
        }
    }

    public function bmltVersionsOptionsPage()
    {
        add_options_page('BMLT Versions', 'BMLT Versions', 'manage_options', 'bmlt-versions', [$this, 'bmltVersionsAdminOptionsPage']);
    }
    public function bmltVersionsAdminOptionsPage()
    {
        ?>
            <div>
                <h2>BMLT Versions</h2>
                <p>You must activate a GitHub personal access token to use this plugin. Instructions can be found here <a href="https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token">https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token</a>.</p>
                <p>Links for documentation are optional and only configured for [bmlt_versions_simple]. You can find all the documentations pages here <a href="https://bmlt.app">https://bmlt.app</a>. If inputs are left blank, "View Documentation" link will not display on the front end</p>
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
                        <tr valign="top">
                            <th scope="row"><label for="workflowDoc">BMLT Workflow Documentation</label></th>
                            <td><input type="text" id="workflowDoc" name="workflowDoc" value="<?php echo get_option('workflowDoc'); ?>" /></td>
                        </tr>

                    </table>
                <?php  submit_button(); ?>
                </form>
            </div>
            <?php
    }

    public function bmltVersionsSimpleFunc($atts = [])
    {
        $content = '';
        $args = shortcode_atts(
            [
                'root_server' => '1',
                'crouton'     => '1',
                'bread'       => '1',
                'yap'         => '1',
                'workflow'    => '1',
                'sort_by'     => 'date'
            ],
            $atts
        );

        $products = [
            'root_server' => [
                'github_name' => 'bmlt-root-server',
                'display_name' => 'Root Server',
                'docs_option_name' => 'rootServerDoc',
                'download_url' => 'https://github.com/bmlt-enabled/bmlt-root-server/releases/',
                'github_url' => 'https://github.com/bmlt-enabled/bmlt-root-server'
            ],
            'crouton' => [
                'github_name' => 'crouton',
                'display_name' => 'Crouton',
                'docs_option_name' => 'croutonDoc',
                'download_url' => 'https://wordpress.org/plugins/crouton/',
                'github_url' => 'https://github.com/bmlt-enabled/crouton'
            ],
            'bread' => [
                'github_name' => 'bread',
                'display_name' => 'Bread',
                'docs_option_name' => 'breadDoc',
                'download_url' => 'https://wordpress.org/plugins/bread/',
                'github_url' => 'https://github.com/bmlt-enabled/bread'
            ],
            'yap' => [
                'github_name' => 'yap',
                'display_name' => 'Yap',
                'docs_option_name' => 'yapDoc',
                'download_url' => 'https://github.com/bmlt-enabled/yap/releases/',
                'github_url' => 'https://github.com/bmlt-enabled/yap'
            ],
            'workflow' => [
                'github_name' => 'bmlt-workflow',
                'display_name' => 'BMLT Workflow',
                'docs_option_name' => 'workflowDoc',
                'download_url' => 'https://wordpress.org/plugins/bmlt-workflow/',
                'github_url' => 'https://github.com/bmlt-enabled/bmlt-workflow'
            ]
        ];

        $releases = [];

        foreach ($products as $key => $product) {
            if ($args[$key]) {
                $response = $this->githubLatestReleaseInfo($product['github_name']);
                $version = $this->githubLatestReleaseVersion($response);
                $date = $this->githubLatestReleaseDate($response);
                $docs = get_option($product['docs_option_name']);

                $content = $this->generateSimpleHtmlContent($product, $version, $date, $docs);
                $releases[] = [
                    'content' => $content,
                    'name'    => $product['display_name'],
                    'date'    => strtotime($date),
                    'version' => $version
                ];
            }
        }

        if ($args['sort_by'] == "name") {
            usort($releases, function ($a, $b) {
                return strnatcasecmp($a['name'], $b['name']);
            });
        } else {
            usort($releases, function ($a, $b) {
                return strnatcasecmp($b['date'], $a['date']);
            });
        }
        foreach ($releases as $release) {
            $content .= $release['content'];
        }

        return $content;
    }

    private function generateSimpleHtmlContent($product, $version, $date, $docs)
    {
        $html = '<div class="bmlt_versions_simple_div ' . $product['github_name'] . '">';
        $html .= '<ul class="bmlt_versions_ul">';
        $html .= '<li class="bmlt_versions_li"><strong>' . $product['display_name'] . '</strong></li>';
        $html .= '<li class="bmlt_versions_li"><strong>Latest Release</br></strong>' . $version . '</li>';
        $html .= '<li class="bmlt_versions_li"><strong>Release Date</br></strong>' . date("m-d-Y", strtotime($date)) . '</li>';
        if (!empty($docs)) {
            $html .= '<li class="bmlt_versions_li"><a href="' . $docs . '">View Documentation</a></li>';
        }
        $html .= '<li class="bmlt_versions_li"><a href="' . $product['github_url'] . '" target="_blank">View On Github</a></li>';
        $html .= '<li class="bmlt_versions_li"><a href="' . $product['download_url'] . $version . '" id="bmlt_versions_release">Download Latest Release</a></li>';
        $html .= '</ul>';
        $html .= '</div>';

        return $html;
    }

    public function bmltVersionsFunc($atts = [])
    {
        $defaults = [
            'root_server' => '1',
            'wordpress' => '0',
            'drupal' => '0',
            'basic' => '0',
            'crouton' => '1',
            'bread' => '1',
            'workflow' => '1',
            'yap' => '1',
            'tabbed_map' => '1',
            'meeting_map' => '1',
            'list_locations' => '1',
            'upcoming_meetings' => '1',
            'contacts' => '1',
            'temporary_closures' => '1',
            'sort_by' => 'date'
        ];

        $args = shortcode_atts($defaults, $atts);
        foreach ($args as $key => $value) {
            $args[$key] = sanitize_text_field($value);
        }

        $repositories = [
            'root_server' => ['display_name' => 'BMLT Root Server', 'name' => 'bmlt-root-server', 'source' => 'github'],
            'yap' => ['display_name' => 'Yap', 'name' => 'yap', 'source' => 'github'],
            'wordpress' => ['display_name' => 'Wordpress Satellite', 'name' => 'bmlt-wordpress-satellite-plugin', 'source' => 'wordpress'],
            'drupal' => ['display_name' => 'Drupal Satellite', 'name' => 'bmlt-drupal', 'source' => 'drupal'],
            'basic' => ['display_name' => 'Basic Satellite', 'name' => 'bmlt-basic', 'source' => 'github'],
            'crouton' => ['display_name' => 'Crouton', 'name' => 'crouton', 'source' => 'wordpress'],
            'bread' => ['display_name' => 'Bread', 'name' => 'bread', 'source' => 'wordpress'],
            'workflow' => ['display_name' => 'Workflow', 'name' => 'bmlt-workflow', 'source' => 'wordpress'],
            'tabbed_map' => ['display_name' => 'Tabbed Map', 'name' => 'bmlt-tabbed-map', 'gh_name' => 'bmlt_tabbed_map', 'source' => 'wordpress'],
            'meeting_map' => ['display_name' => 'Meeting Map', 'name' => 'bmlt-meeting-map', 'source' => 'wordpress'],
            'list_locations' => ['display_name' => 'List Locations', 'name' => 'list-locations-bmlt', 'source' => 'wordpress'],
            'upcoming_meetings' => ['display_name' => 'Upcoming Meetings', 'name' => 'upcoming-meetings-bmlt', 'source' => 'wordpress'],
            'contacts' => ['display_name' => 'Contacts', 'name' => 'contacts-bmlt', 'source' => 'wordpress'],
            'temporary_closures' => ['display_name' => 'Temporary Closures', 'name' => 'temporary-closures-bmlt', 'source' => 'wordpress']
        ];

        $releases = [];

        foreach ($repositories as $key => $repo) {
            if ($args[$key]) {
                $response = $this->githubLatestReleaseInfo($repo['gh_name'] ?? $repo['name']);
                $version = $this->githubLatestReleaseVersion($response);
                $date = $this->githubLatestReleaseDate($response);
                $description = $this->githubReleaseDescription($repo['gh_name'] ?? $repo['name']);
                $formattedDate = date("m-d-Y", strtotime($date));

                $downloadURL = '';
                switch ($repo['source']) {
                    case 'drupal':
                    case 'github':
                        $downloadURL = "https://github.com/bmlt-enabled/{$repo['name']}/releases/{$version}";
                        break;
                    case 'wordpress':
                        $downloadURL = "https://wordpress.org/plugins/{$repo['name']}/";
                        break;
                }

                $content = "<div class=\"bmlt_versions_div {$repo['source']}\">";
                $content .= "<ul class=\"bmlt_versions_ul\">";
                $content .= "<li class=\"bmlt_versions_li\" id=\"bmlt-versions-{$key}\">";
                $content .= "<strong>" . ucfirst($repo['display_name']) . "</strong><br>";
                $content .= "$description<br><br>";
                $content .= "Latest Release : <strong><a href=\"{$downloadURL}\" id=\"bmlt_versions_release\">{$version} ({$formattedDate})</a></strong>";
                $content .= "</li></ul></div>";

                $releases[] = ['content' => $content, 'name' => $repo['name'], 'date' => strtotime($date), 'version' => $version];
            }
        }

        $sortKey = ($args['sort_by'] == 'name') ? 'name' : 'date';
        usort($releases, function ($a, $b) use ($sortKey) {
            return ($sortKey == 'date') ? strnatcasecmp($b[$sortKey], $a[$sortKey]) : strnatcasecmp($a[$sortKey], $b[$sortKey]);
        });
        $rel = '';
        foreach ($releases as $release) {
            $rel .= $release['content'];
        }
        return $rel;
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
        return $result['tag_name'] ?? '';
    }

    public function githubLatestReleaseDate($result)
    {
        return $result['published_at'] ?? '';
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

        $args = [
            'timeout' => '120',
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0 +bmltVersions',
                'Authorization' => "token $gitHubApiKey"
            ],
            'cookies' => $cookies ?? null
        ];

        return wp_remote_get($url, $args);
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

BmltVersions::getInstance();
