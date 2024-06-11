<?php 
global $ppma_custom_settings;

$section_content = get_ppma_section_content($context['options_group_name']);
if (is_array($ppma_custom_settings)) {
    $wrapper_class = 'custom-settings';

    $parts = explode('<input type="hidden" id="', $section_content);
    $modifiedHtml = array_shift($parts);
    foreach ($parts as $part) {
        list($idPart, $restPart) = explode('" />', $part, 2);
        $hiddenInputTag = '<input type="hidden" id="' . $idPart . '" />';
        if ($idPart !== 'ppma-tab-author-pages') {
            // Add style="display: none;" to the table following this hidden input
            $restPart = str_replace(
                '<table class="form-table" role="presentation">',
                '<table class="form-table" role="presentation" style="display: none;">',
                $restPart
            );
        }

        // Reconstruct the modified HTML content
        $modifiedHtml .= $hiddenInputTag . $restPart;
    }

    $section_content = $modifiedHtml;
} else {
    $wrapper_class = '';
}
?>
<?php settings_fields('multiple_authors_options'); ?>
<div class="ppma-settings-wrap <?php echo esc_attr($wrapper_class); ?>">
    <?php echo $section_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php wp_nonce_field('edit-publishpress-settings'); ?>

<input type="hidden" name="multiple_authors_module_name[]" value="<?php echo esc_attr($context['module_name']); ?>"/>
<input type="hidden" name="action" value="update"/>

<script>
    jQuery(function ($) {
        $(".chosen-select").chosen({
            'width': '95%'
        });
    });
</script>
