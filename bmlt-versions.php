<?php

/*
Plugin Name: BMLT Versions
Plugin URI: https://github.com/bmlt-enabled/bmlt-versions/
Description: A simple content generator to display the versions and links of the various BMLT components. Add [bmlt_versions] to a page or a post to generate the list.
Author: bmlt-enabled
Author URI: https://bmlt.app
Version: 1.9.0
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
        add_option('bmltVersionsGithubApiKey', '');
        register_setting('bmltVersionsOptionGroup', 'bmltVersionsGithubApiKey');

        foreach ($this->docOptions() as $key => $field) {
            add_option($key, $field['default']);
            register_setting('bmltVersionsOptionGroup', $key);
        }
    }

    private function docOptions()
    {
        return [
            'serverDoc'   => ['label' => 'Server Documentation', 'default' => ''],
            'croutonDoc'  => ['label' => 'Crouton Documentation', 'default' => ''],
            'crumbDoc'    => ['label' => 'Crumb Documentation', 'default' => 'https://crumb.bmlt.app/'],
            'yapDoc'      => ['label' => 'Yap Documentation', 'default' => ''],
            'breadDoc'    => ['label' => 'Bread Documentation', 'default' => ''],
            'workflowDoc' => ['label' => 'BMLT Workflow Documentation', 'default' => ''],
        ];
    }

    public function bmltVersionsOptionsPage()
    {
        add_options_page('BMLT Versions', 'BMLT Versions', 'manage_options', 'bmlt-versions', [$this, 'bmltVersionsAdminOptionsPage']);
    }
    public function bmltVersionsAdminOptionsPage()
    {
        $rows = ['bmltVersionsGithubApiKey' => 'GitHub API Token'];
        foreach ($this->docOptions() as $key => $field) {
            $rows[$key] = $field['label'];
        }
        ?>
            <div>
                <h2>BMLT Versions</h2>
                <p>You must activate a GitHub personal access token to use this plugin. Instructions can be found here <a href="https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token">https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token</a>.</p>
                <p>Links for documentation are optional and only configured for [bmlt_versions_simple]. You can find all the documentations pages here <a href="https://bmlt.app">https://bmlt.app</a>. If inputs are left blank, "View Documentation" link will not display on the front end</p>
                <form method="post" action="options.php">
                    <?php settings_fields('bmltVersionsOptionGroup'); ?>
                    <table>
                        <?php foreach ($rows as $key => $label) : ?>
                            <tr valign="top">
                                <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                                <td><input type="text" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr(get_option($key)); ?>" /></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            <?php
    }

    public function bmltVersionsSimpleFunc($atts = [])
    {
        $args = shortcode_atts(
            [
                'server' => '1',
                'crouton'     => '1',
                'crumb'       => '1',
                'bread'       => '1',
                'yap'         => '1',
                'workflow'    => '1',
                'sort_by'     => 'date'
            ],
            $atts
        );

        $products = [
            'server' => [
                'github_name' => 'bmlt-server',
                'display_name' => 'BMLT Server',
                'docs_option_name' => 'serverDoc',
                'download_url' => 'https://github.com/bmlt-enabled/bmlt-server/releases/',
                'github_url' => 'https://github.com/bmlt-enabled/bmlt-server'
            ],
            'crouton' => [
                'github_name' => 'crouton',
                'display_name' => 'Crouton',
                'docs_option_name' => 'croutonDoc',
                'download_url' => 'https://wordpress.org/plugins/crouton/',
                'github_url' => 'https://github.com/bmlt-enabled/crouton'
            ],
            'crumb' => [
                'github_name' => 'crumb',
                'display_name' => 'Crumb',
                'docs_option_name' => 'crumbDoc',
                'download_url' => 'https://wordpress.org/plugins/crumb/',
                'github_url' => 'https://github.com/bmlt-enabled/crumb'
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
                $version = $response['tag_name'] ?? '';
                $date = $response['published_at'] ?? '';
                $docs = get_option($product['docs_option_name']);

                $releases[] = [
                    'content' => $this->generateSimpleHtmlContent($product, $version, $date, $docs),
                    'name'    => $product['display_name'],
                    'date'    => strtotime($date),
                ];
            }
        }

        usort($releases, function ($a, $b) use ($args) {
            return $args['sort_by'] === 'name'
                ? strnatcasecmp($a['name'], $b['name'])
                : $b['date'] <=> $a['date'];
        });

        $output = '';
        foreach ($releases as $release) {
            $output .= $release['content'];
        }
        return $output;
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
            'server' => '1',
            'wordpress' => '0',
            'drupal' => '0',
            'basic' => '0',
            'crouton' => '1',
            'crumb' => '1',
            'crumb_drupal' => '0',
            'crumb_joomla' => '0',
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
            'server' => ['display_name' => 'BMLT Server', 'name' => 'bmlt-server', 'source' => 'github'],
            'yap' => ['display_name' => 'Yap', 'name' => 'yap', 'source' => 'github'],
            'wordpress' => ['display_name' => 'Wordpress Satellite', 'name' => 'bmlt-wordpress-satellite-plugin', 'source' => 'wordpress'],
            'drupal' => ['display_name' => 'Drupal Satellite', 'name' => 'bmlt-drupal', 'source' => 'drupal'],
            'basic' => ['display_name' => 'Basic Satellite', 'name' => 'bmlt-basic', 'source' => 'github'],
            'crouton' => ['display_name' => 'Crouton', 'name' => 'crouton', 'source' => 'wordpress'],
            'crumb' => ['display_name' => 'Crumb', 'name' => 'crumb', 'source' => 'wordpress'],
            'crumb_drupal' => ['display_name' => 'Crumb Drupal', 'name' => 'crumb-drupal', 'source' => 'drupal'],
            'crumb_joomla' => ['display_name' => 'Crumb Joomla', 'name' => 'crumb-joomla', 'source' => 'joomla'],
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
                $version = $response['tag_name'] ?? '';
                $date = $response['published_at'] ?? '';
                $description = $this->githubReleaseDescription($repo['gh_name'] ?? $repo['name']);
                $formattedDate = date("m-d-Y", strtotime($date));

                $downloadURL = '';
                switch ($repo['source']) {
                    case 'drupal':
                    case 'joomla':
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

                $releases[] = ['content' => $content, 'name' => $repo['name'], 'date' => strtotime($date)];
            }
        }

        usort($releases, function ($a, $b) use ($args) {
            return $args['sort_by'] === 'name'
                ? strnatcasecmp($a['name'], $b['name'])
                : $b['date'] <=> $a['date'];
        });

        $output = '';
        foreach ($releases as $release) {
            $output .= $release['content'];
        }
        return $output;
    }

    public function githubLatestReleaseInfo($repo)
    {
        $results = $this->get("https://api.github.com/repos/bmlt-enabled/$repo/releases/latest");
        if (!in_array(wp_remote_retrieve_response_code($results), [200, 302, 304], true)) {
            return [];
        }
        return json_decode(wp_remote_retrieve_body($results), true) ?? [];
    }

    public function githubReleaseDescription($repo)
    {
        $results = $this->get("https://api.github.com/repos/bmlt-enabled/$repo");
        if (!in_array(wp_remote_retrieve_response_code($results), [200, 302, 304], true)) {
            return '';
        }
        $description = json_decode(wp_remote_retrieve_body($results), true)['description'] ?? '';
        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
        return preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $description);
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
