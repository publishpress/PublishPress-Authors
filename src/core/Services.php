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

        $container['twig_loader'] = function ($c) {
            return new Twig_Loader_Filesystem(PP_AUTHORS_TWIG_PATH);
        };

        $container['twig'] = function ($c) {
            $twig = new Twig_Environment($c['twig_loader']);

            $function = new Twig_SimpleFunction(
                'settings_fields', function () use ($c) {
                return settings_fields('multiple_authors_options');
            }
            );
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction(
                'nonce_field', function ($context) {
                return wp_nonce_field($context);
            }
            );
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction(
                'submit_button', function () {
                return submit_button();
            }
            );
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction(
                '__', function ($id, $domain = 'publishpress-authors') {
                return __($id, $domain);
            }
            );
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction(
                'do_settings_sections', function ($section) {
                return do_settings_sections($section);
            }
            );
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction(
                'esc_attr', function ($string) {
                return esc_attr($string);
            }
            );
            $twig->addFunction($function);

            $function = new Twig_SimpleFunction(
                'do_shortcode', function ($string) {
                return do_shortcode($string);
            }
            );
            $twig->addFunction($function);

            /**
             * @deprecated 2.2.1 Replaced by the author.avatar attribute, which includes avatar for guest authors.
             */
            $function = new Twig_SimpleFunction(
                'get_avatar', function ($user_email, $size = 35) {
                return get_avatar($user_email, $size);
            }
            );
            $twig->addFunction($function);

            /**
             * @param Twig_Environment $twig
             *
             * @return Twig_Environment
             */
            $twig = apply_filters('pp_authors_twig', $twig);

            return $twig;
        };
    }
}
