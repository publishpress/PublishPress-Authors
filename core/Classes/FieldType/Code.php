<?php

namespace MultipleAuthors\Classes\FieldType;

class Code
{
    public static function addHooks()
    {
        add_action('cmb2_render_code', [__CLASS__, 'render']);
        add_filter('cmb2_sanitize_code', [__CLASS__, 'sanitize'], 10, 2);
    }

    /**
     * @param $overrideValue
     * @param $value
     *
     * @return mixed
     */
    public static function sanitize($overrideValue, $value)
    {
        // Strip script and iframe tags.
        $value = preg_replace('#<(script|iframe)[^>]*>[^<]*<\/(script|iframe)[^>]*>#', '', $value);

        // Strip script from attributes
        $value = preg_replace('#on[a-z]+=\s*[\'"]?.*[\'"]?(.*>)#i', '$1', $value);

        $overrideValue = $value;

        return $value;
    }

    /**
     * @param array $field
     */
    public static function render($field = [])
    {
        $id = $field->args['id'] . '_editor';

        $layoutCode = $field->value;
        if (empty($layoutCode)) {
            $layoutCode = file_get_contents(PP_AUTHORS_BASE_PATH . 'twig/author_layout/default.twig');
        }
        ?>
        <textarea id="<?php echo $field->args['id']; ?>" name="<?php echo $field->args['id']; ?>"
                  style="display: none;"><?php echo $field->value; ?></textarea>
        <div id="<?php echo $id; ?>" name="<?php echo $id; ?>"
             style="position: relative; width: 100%; height: 800px;"></div>

        <script src="<?php echo PP_AUTHORS_ASSETS_URL . '/lib/ace/ace.js'; ?>?v=<?php echo PP_AUTHORS_VERSION; ?>"
                type="text/javascript"
                charset="utf-8"></script>
        <script src="<?php echo PP_AUTHORS_ASSETS_URL . '/lib/ace/mode-twig.js'; ?>?v=<?php echo PP_AUTHORS_VERSION; ?>"
                type="text/javascript"
                charset="utf-8"></script>
        <script>
            jQuery(function ($) {
                $('label[for="<?php echo $field->args['id']; ?>"]').parent().css('display', 'block').css('width', '100%');
                $('#<?php echo $field->args['id']; ?>').parent().css('float', 'none').css('width', '100%');

                var TwigMode = ace.require('ace/mode/twig').Mode;
                var editor = ace.edit('<?php echo $id; ?>');
                editor.session.setMode(new TwigMode());
                editor.setTheme('ace/theme/monokai');
                editor.session.setValue(<?php echo json_encode($layoutCode, JSON_HEX_TAG | JSON_HEX_QUOT); ?>);

                // Move the code from the editor to the textarea on save.
                var textarea = $('#<?php echo $field->args['id']; ?>');
                textarea.closest('form').submit(function () {
                    textarea.val(editor.getSession().getValue());
                });
            });
        </script>
        <?php

        if ( ! empty($field->args['desc'])) {
            ?>
            <p class="cmb2-metabox-description"><?php echo $field->args['desc']; ?></p>
            <?php
        }
    }
}
