<?php

namespace FluentMail\Includes\Core;

use ArrayAccess;
use FluentMail\Includes\View\View;
use FluentMail\Includes\Core\CoreTrait;
use FluentMail\Includes\Core\Container;
use FluentMail\Includes\Request\Request;
use FluentMail\Includes\Response\Response;

final class Application extends Container
{
    use CoreTrait;

    private $policyNamespace = 'FluentMail\App\Http\Policies';

    private $handlerNamespace = 'FluentMail\App\Hooks\Handlers';

    private $controllerNamespace = 'FluentMail\App\Http\Controllers';

    public function __construct()
    {
        $this->setApplicationInstance();
        $this->registerPluginPathsAndUrls();
        $this->registerFrameworkComponents();
        $this->requireCommonFilesForRequest($this);

        load_plugin_textdomain('fluent-smtp', false, 'fluent-smtp/language/');

        /*
         * We are adding fluent-smtp/fluent-smtp.php at the top to load the wp_mail at the very first
         * There has no other way to load a specific plugin at the first.
         */
        add_filter('pre_update_option_active_plugins', function ($plugins) {
            $index = array_search('fluent-smtp/fluent-smtp.php', $plugins);
            if ($index !== false) {
                if ($index === 0) {
                    return $plugins;
                }
                unset($plugins[$index]);
                array_unshift($plugins, 'fluent-smtp/fluent-smtp.php');
            }
            return $plugins;
        });

        add_action('admin_notices', function () {
            if (!current_user_can('manage_options')) {
                return;
            }

            $settings = get_option('fluentmail-settings');

            if (!$settings || empty($settings['use_encrypt']) || empty($settings['test'])) {
                return;
            }

            $testData = fluentMailEncryptDecrypt($settings['test'], 'd');

            if ($testData == 'test') {
                return;
            }

            ?>
            <div class="notice notice-warning fluentsmtp_urgent is-dismissible">
                <p>
                    FluentSMTP Plugin may not work properly. Looks like your Authentication unique keys and salts are changed. <a href="<?php echo esc_url(admin_url('options-general.php?page=fluent-mail#/connections')); ?>"><b>Reconfigure SMTP Settings</b></a>
                </p>
            </div>
            <?php
        });
    }

    private function setApplicationInstance()
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(__CLASS__, $this);
    }
    private function registerPluginPathsAndUrls()
    {
        // Paths
        $this['path'] = FLUENTMAIL_PLUGIN_PATH;
        $this['path.app'] = FLUENTMAIL_PLUGIN_PATH . 'app/';
        $this['path.hooks'] = FLUENTMAIL_PLUGIN_PATH . 'app/Hooks/';
        $this['path.models'] = FLUENTMAIL_PLUGIN_PATH . 'app/models/';
        $this['path.includes'] = FLUENTMAIL_PLUGIN_PATH . 'includes/';
        $this['path.controllers'] = FLUENTMAIL_PLUGIN_PATH . 'app/Http/controllers/';
        $this['path.views'] = FLUENTMAIL_PLUGIN_PATH . 'app/views/';
        $this['path.admin.css'] = FLUENTMAIL_PLUGIN_PATH . 'assets/admin/css/';
        $this['path.admin.js'] = FLUENTMAIL_PLUGIN_PATH . 'assets/admin/js/';
        $this['path.public.css'] = FLUENTMAIL_PLUGIN_PATH . 'assets/public/css/';
        $this['path.public.js'] = FLUENTMAIL_PLUGIN_PATH . 'assets/public/js/';
        $this['path.assets'] = FLUENTMAIL_PLUGIN_PATH . 'assets/';

        // Urls
        $this['url'] = FLUENTMAIL_PLUGIN_URL;
        $this['url.app'] = FLUENTMAIL_PLUGIN_URL . 'app/';
        $this['url.assets'] = FLUENTMAIL_PLUGIN_URL . 'assets/';
        $this['url.public.css'] = FLUENTMAIL_PLUGIN_URL . 'assets/public/css/';
        $this['url.admin.css'] = FLUENTMAIL_PLUGIN_URL . 'assets/admin/css/';
        $this['url.public.js'] = FLUENTMAIL_PLUGIN_URL . 'assets/public/js/';
        $this['url.admin.js'] = FLUENTMAIL_PLUGIN_URL . 'assets/admin/js/';
        $this['url.assets.images'] = FLUENTMAIL_PLUGIN_URL . 'assets/images/';

    }

    private function registerFrameworkComponents()
    {
        $this->bind('FluentMail\Includes\View\View', function ($app) {
            return new View($app);
        });

        $this->alias('FluentMail\Includes\View\View', 'view');

        $this->singleton('FluentMail\Includes\Request\Request', function ($app) {
            return new Request($app, $_GET, $_POST, $_FILES);
        });

        $this->alias('FluentMail\Includes\Request\Request', 'request');

        $this->singleton('FluentMail\Includes\Response\Response', function ($app) {
            return new Response($app);
        });

        $this->alias('FluentMail\Includes\Response\Response', 'response');
    }

    /**
     * Require all the common files that needs to be loaded on each request
     *
     * @param Application $app [$app is being used inside required files]
     * @return void
     */
    private function requireCommonFilesForRequest($app)
    {
        // Require Application Bindings
        require_once($app['path.app'] . '/Bindings.php');

        // Require Global Functions
        require_once($app['path.app'] . '/Functions/helpers.php');

        // Require Action Hooks
        require_once($app['path.app'] . '/Hooks/actions.php');

        // Require Filter Hooks
        require_once($app['path.app'] . '/Hooks/filters.php');

        // Require Routes
        if (is_admin()) {
            require_once($app['path.app'] . '/Http/routes.php');
        }
    }
}
