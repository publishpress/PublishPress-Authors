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
        //add_filter('removable_query_args', [$this, 'removableQueryArgs']);
    }

    public function removableQueryArgs($args) {

        if (!isset($_GET['page']) || $_GET['page'] !== self::MENU_SLUG) {
            return $args;
        }
        
        return array_merge(
            $args,
            [
                'action',
                'author_pages',
                '_wpnonce'
            ]
        );

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
            ]
        ];

        $this->print_default_header($ppma_custom_settings['modules'][$this->module_name]);

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

                    <?php if (!empty($custom_text)) : ?>
                        <?php echo esc_html($custom_text); ?>
                    <?php endif; ?>
                </h2>

            </header>
        <?php
    }

    /**
     * Author Pages callback
     *
     * @return void
     */
    public function manageAuthorPages2() {

        ?>

        <div class="wrap">

            <h1 class="wp-heading-inline"><?php esc_html_e('Author Pages', 'publishpress-authors'); ?></h1>
            <?php
                if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
                    echo '<span class="subtitle">';
                    printf(
                        /* translators: %s: Search query. */
                        esc_html__( 'Search results for: %s' ),
                        '<strong>' . esc_html( wp_unslash( $_REQUEST['s'] ) ) . '</strong>'
                    );
                    echo '</span>';
                }
            ?>
            <hr class="wp-header-end">
            <div id="ajax-response"></div>


            <form class="search-form wp-clearfix" method="get">
                <?php $this->author_pages_table->search_box(esc_html__('Search Author Pages', 'publishpress-authors'), 'author-pages'); ?>
            </form>

            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">  
                        <div class="form-wrap">
                            <h2><?php esc_html_e('Add Author Category', 'publishpress-authors'); ?></h2>
                            <form id="addauthorcategory" method="post" action="#" class="validate">
                                <div class="form-field form-required category-name-wrap">
                                    <label for="category-name"><?php esc_html_e( 'Singular Name', 'publishpress-authors' ); ?> <span class="required">*</span></label>
                                    <input name="category-name" id="category-name" type="text" value="" size="40" required autocomplete="off" />
                                    <p id="category-name-description"><?php esc_html_e('Enter the Author Category name when it\'s a single author', 'publishpress-authors'); ?></p>
                                </div>
                                <div class="form-field form-required category-plural-name-wrap">
                                    <label for="category-plural-name"><?php esc_html_e( 'Plural Name', 'publishpress-authors' ); ?> <span class="required">*</span></label>
                                    <input name="category-plural-name" id="category-plural-name" type="text" value="" size="40" required autocomplete="off" />
                                    <p id="category-plural-description"><?php esc_html_e('Enter the Author Category name when there are more than 1 author', 'publishpress-authors'); ?></p>
                                </div>
                                <div class="form-field category-schema-property-wrap">
                                    <label for="category-schema-property"><?php esc_html_e( 'Schema Property', 'publishpress-authors' ); ?></label>
                                    <input name="category-schema-property" id="category-schema-property" type="text" value="" size="40" autocomplete="off" />
                                    <p id="category-plural-description"><?php printf(
                                        esc_html__(
                                            'For example, when this value is set to reviewedBy, all users under this category will be added to post reviewedBy property. You can read more %1$s in this guide.%2$s',
                                            'publishpress-authors'
                                        ),
                                        '<a target="_blank" href="https://publishpress.com/knowledge-base/author-pages-schema/">',
                                        '</a>'
                                    ); ?></p>
                                </div>
                                <div class="form-field category-enabled-category-wrap">
                                    <label for="category-enabled-category">
                                        <input name="category-enabled-category" id="category-enabled-category" type="checkbox" value="1" checked />
                                        <?php esc_html_e( 'Enable Category', 'publishpress-authors' ); ?>
                                    </label>
                                </div>
                            <p class="submit">
                                <?php submit_button( __('Add New Author Category', 'publishpress-authors'), 'primary', 'submit', false ); ?>
                                <span class="spinner"></span>
                            </p>
                            </form>
                        </div>
                    </div>
                </div><!-- /col-left -->

                <div id="col-right">
                    <div class="col-wrap">
                        <form action="<?php echo esc_url(add_query_arg('', '')); ?>" method="post">
                            <?php $this->author_pages_table->display(); ?>
                        </form>
                    </div>
                </div><!-- /col-right -->
            </div><!-- /col-container -->

        </div><!-- /wrap -->
        <?php $this->author_pages_table->inline_edit(); ?>
        <?php
    }
}
