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
use PublishPress\Pimple\Container as Pimple;
use PublishPress\Pimple\ServiceProviderInterface;
use MultipleAuthors\View;

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

        $container['view'] = function ($c) {
            return new View();
        };
    }
}
