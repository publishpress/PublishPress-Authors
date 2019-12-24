<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace MultipleAuthors;

use Allex\Core;
use MultipleAuthors\Classes\Legacy\LegacyPlugin;
use Pimple\Container as Pimple;
use Pimple\ServiceProviderInterface;
use PublishPress\EDD_License\Core\Container as EDDContainer;
use PublishPress\EDD_License\Core\Services as EDDServices;
use PublishPress\EDD_License\Core\ServicesConfig as EDDServicesConfig;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;

defined('ABSPATH') or die('No direct script access allowed.');

/**
 * Class Services
 */
class Services implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Pimple $container A container instance
     *
     * @since 1.2.3
     *
     */
    public function register(Pimple $container)
    {
        $container['legacy_plugin'] = function ($c) {
            return new LegacyPlugin();
        };

        $container['module'] = function ($c) {
            $legacyPlugin = $c['legacy_plugin'];

            return $legacyPlugin->multiple_authors;
        };

        $container['module_author_custom_fields'] = function ($c) {
            $legacyPlugin = $c['legacy_plugin'];

            return $legacyPlugin->author_custom_fields;
        };

        $container['LICENSE_KEY'] = function ($c) {
            $key = '';
            if (isset($c['module']->module->options->license_key)) {
                $key = $c['module']->module->options->license_key;
            }

            return $key;
        };

        $container['LICENSE_STATUS'] = function ($c) {
            $status = \MA_Multiple_Authors::LICENSE_STATUS_INVALID;

            if (isset($c['module']->module->options->license_status)) {
                $status = $c['module']->module->options->license_status;
            }

            return $status;
        };

        $container['edd_container'] = function ($c) {
            $config = new EDDServicesConfig();
            $config->setApiUrl(PP_AUTHORS_SITE_URL);
            $config->setLicenseKey($c['LICENSE_KEY']);
            $config->setLicenseStatus($c['LICENSE_STATUS']);
            $config->setPluginVersion(PP_AUTHORS_VERSION);
            $config->setEddItemId(PP_AUTHORS_ITEM_ID);
            $config->setPluginAuthor(PP_AUTHORS_PLUGIN_AUTHOR);
            $config->setPluginFile(PP_AUTHORS_FILE);

            $services = new EDDServices($config);

            $eddContainer = new EDDContainer();
            $eddContainer->register($services);

            return $eddContainer;
        };

        $container['framework'] = function ($c) {
            // The 4th param is there just for backward compatibility with older versions of the Allex framework
            // packed in UpStream (in case it is installed and loaded).
            return new Core(
                PP_AUTHORS_BASENAME,
                PP_AUTHORS_SITE_URL,
                PP_AUTHORS_PLUGIN_AUTHOR,
                ''
            );
        };

        $container['twig_loader'] = function ($c) {
            $loader = new Twig_Loader_Filesystem(PP_AUTHORS_BASE_PATH . 'twig');

            return $loader;
        };

        $container['twig'] = function ($c) {
            $twig = new Twig_Environment($c['twig_loader']);

            $function = new Twig_SimpleFunction('settings_fields', function () use ($c) {
                return settings_fields('multiple_authors_options');
            });
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction('nonce_field', function ($context) {
                return wp_nonce_field($context);
            });
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction('submit_button', function () {
                return submit_button();
            });
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction('__', function ($id) {
                return __($id, 'publishpress-authors');
            });
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction('do_settings_sections', function ($section) {
                return do_settings_sections($section);
            });
            $twig->addFunction($function);

            $function = new \Twig_SimpleFunction('esc_attr', function ($string) {
                return esc_attr($string);
            });
            $twig->addFunction($function);

            $function = new \Twig_SimpleFunction('do_shortcode', function ($string) {
                do_shortcode($string);
            });
            $twig->addFunction($function);

            /**
             * @deprecated 2.2.1 Replaced by the author.avatar attribute, which includes avatar for guest authors.
             */
            $function = new \Twig_SimpleFunction('get_avatar', function ($user_email, $size = 35) {
                return get_avatar($user_email, $size);
            });
            $twig->addFunction($function);

            return $twig;
        };
    }
}
