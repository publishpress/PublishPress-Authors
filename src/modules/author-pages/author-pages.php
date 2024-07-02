<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2018 PublishPress
 *
 * This file is part of PublishPress Authors
 *
 * PublishPress Authors is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

use MultipleAuthors\Classes\Legacy\Module;
use MultipleAuthors\Capability;
use MultipleAuthors\Factory;

/**
 * class MA_Author_Pages
 */
class MA_Author_Pages extends Module
{

    public $module_name = 'author_pages';

    public $viewsPath;

    public $view;

    const MENU_SLUG = 'ppma-author-pages';

    /**
     * Construct the MA_Multiple_Authors class
     */
    public function __construct()
    {
        $this->viewsPath = PP_AUTHORS_MODULES_PATH . '/settings/views';


        $this->module_url = $this->get_module_url(__FILE__);

        parent::__construct();

        // Register the module with PublishPress
        $args = [
            'title' => __('Author Pages', 'publishpress-authors'),
            'short_description' => __(
                'Add support for author pages.',
                'publishpress-authors'
            ),
            'extended_description' => __(
                'Add support for author pages.',
                'publishpress-authors'
            ),
            'module_url' => $this->module_url,
            'icon_class' => 'dashicons dashicons-edit',
            'slug' => 'author-pages',
            'default_options' => [
                'enabled' => 'on',
            ],
            'options_page' => false,
            'autoload' => true,
        ];

        // Apply a filter to the default options
        $args['default_options'] = apply_filters('MA_Author_Pages_default_options', $args['default_options']);

        $legacyPlugin = Factory::getLegacyPlugin();

        $this->module = $legacyPlugin->register_module($this->module_name, $args);

        parent::__construct();
    }

    /**
     * Initialize the module. Conditionally loads if the module is enabled
     */
    public function init()
    {
        add_action('multiple_authors_admin_submenu', [$this, 'adminSubmenu'], 50);
    }

    /**
     * Add the admin submenu.
     */
    public function adminSubmenu()
    {

        // Add the submenu to the PublishPress menu.
        $hook = add_submenu_page(
            \MA_Multiple_Authors::MENU_SLUG,
            esc_html__('Author Pages', 'publishpress-authors'),
            esc_html__('Author Pages', 'publishpress-authors'),
            Capability::getManageOptionsCapability(),
            self::MENU_SLUG,
            [$this, 'manageAuthorPages'],
            11
        );
    }

    public function author_pages_tabs() {
        $tabs = [
            '.ppma-author-pages-tab-general'            => esc_html__('General', 'publishpress-authors'),
            '.ppma-author-pages-tab-layout'             => esc_html__('Layout', 'publishpress-authors'),
            '.ppma-author-pages-tab-author-bio'         => esc_html__('Author Bio', 'publishpress-authors'),
            '.ppma-author-pages-tab-author-page-title'  => esc_html__('Author Page Title', 'publishpress-authors'),
            '.ppma-author-pages-tab-posts'              => esc_html__('Posts', 'publishpress-authors'),
        ];

        return $tabs;
    }

    public function manageAuthorPages()
    {
        global $ppma_custom_settings;

        $legacyPlugin = Factory::getLegacyPlugin();

        $requested_module     = $legacyPlugin->get_module_by('settings_slug', MA_Modules_Settings::SETTINGS_SLUG . '-settings');
        $display_text         = '';

        // If there's been a message, let's display it
        $message = false;

        if (isset($_REQUEST['message'])) {
            $message = sanitize_text_field($_REQUEST['message']);
        }

        if ($message && isset($requested_module->messages[$message])) {
            $display_text .= '<div class="is-dismissible notice notice-info"><p>' . esc_html($requested_module->messages[$message]) . '</p></div>';
        }

        // If there's been an error, let's display it
        $error = false;

        if (isset($_REQUEST['error'])) {
            $error = sanitize_text_field($_REQUEST['error']);
        }

        if ($error && isset($requested_module->messages[$error])) {
            $display_text .= '<div class="is-dismissible notice notice-error"><p>' . esc_html($requested_module->messages[$error]) . '</p></div>';
        }

        $ppma_custom_settings = [
            'modules' => [
                $this->module_name => $this->module
            ],
            'class_names' => [
                $this->module_name => 'MA_Author_Pages'
            ],
            'tabs' => $this->author_pages_tabs(),
            'active_tabs' => '.ppma-author-pages-tab-general',
        ];

        $page_description = esc_html__('Please note this feature will not work for all themes.', 'publishpress-authors') . ' <a target="_blank" href="https://publishpress.com/knowledge-base/author-pages-troubleshooting/">'.  esc_html__('Click here for more details.', 'publishpress-authors') .'</a>';

        $this->print_default_header($ppma_custom_settings['modules'][$this->module_name], $page_description);

        // Get module output
        ob_start();
        $configure_callback    = $requested_module->configure_page_cb;

        if ( ! empty($configure_callback)) {
            $requested_module_name = $requested_module->name;

            $legacyPlugin->$requested_module_name->$configure_callback();
            $module_output = ob_get_clean();
        }

        echo $this->view->render( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            'settings',
            [
                'modules'        => (array)$ppma_custom_settings['modules'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                'settings_slug'  => esc_html($this->module_name),
                'slug'           => esc_html(MA_Modules_Settings::SETTINGS_SLUG),
                'module_output'  => $module_output, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                'sidebar_output' => '',
                'text'           => esc_html($display_text),
                'show_sidebar'   => false,
                'show_tabs'      => false,
            ],
            $this->viewsPath
        );
    }

    /**
     *
     */
    public function print_default_header($current_module, $custom_text = null)
    {
        $display_text = '';

        // If there's been a message, let's display it
        $message = false;

        if (isset($_REQUEST['message'])) {
            $message = sanitize_text_field($_REQUEST['message']);
        }

        if ($message && isset($current_module->messages[$message])) {
            $display_text .= '<div class="is-dismissible notice notice-info"><p>' . esc_html($current_module->messages[$message]) . '</p></div>';
        }

        // If there's been an error, let's display it
        $error = false;

        if (isset($_REQUEST['error'])) {
            $error = sanitize_text_field($_REQUEST['error']);
        }

        if ($error && isset($current_module->messages[$error])) {
            $display_text .= '<div class="is-dismissible notice notice-error"><p>' . esc_html($current_module->messages[$error]) . '</p></div>';
        }
        ?>

        <div class="publishpress-admin pressshack-admin-wrapper wrap">
            <header>
                <h1 class="wp-heading-inline"><?php echo esc_html($current_module->title); ?></h1>

                <?php echo !empty($display_text) ? $display_text : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php // We keep the H2 tag to keep notices tied to the header?>
                <h2>
                    <?php if ($current_module->short_description && empty($custom_text)): ?>
                        <?php //echo esc_html($current_module->short_description); ?>
                    <?php endif; ?>
                </h2>

                <?php if (!empty($custom_text)) : ?>
                    <p class="description"><?php echo $custom_text;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                <?php endif; ?>

            </header>
        <?php
    }
}
