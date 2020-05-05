<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 *
 * @deprecated 3.2.5-beta.8
 */

if (!class_exists('Multiple_authors_iterator')) {
    /**
     * @deprecated 1.0.0 We are keeping for backward compatibility
     */
    class Multiple_authors_iterator extends Authors_Iterator
    {
    }
}

require_once __DIR__ . '/src/functions/template-tags.php';
