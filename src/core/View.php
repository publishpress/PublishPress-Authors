<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors;

use Exception;

/**
 * @package PublishPress\Core
 */
class View
{
    const FILE_EXTENSION = '.html.php';

    /**
     * @throws Exception
     */
    public function render($view, $context = [], $views_path = null)
    {
        $view = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $view);

        if (is_null($views_path)) {
            $views_path = PP_AUTHORS_VIEWS_PATH;
        }

        $view_path = $this->get_view_path($view, $views_path);

        if (! is_readable($view_path)) {
            
            error_log('PublishPress Authors: View is not readable: ' . $view);

            return '';
        }

        ob_start();
        include $view_path;

        return ob_get_clean();
    }

    protected function get_view_path($view, $views_path)
    {
        return $views_path . '/' . $view . self::FILE_EXTENSION;
    }
}
