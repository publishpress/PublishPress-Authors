<?php settings_fields('multiple_authors_options'); ?>
<?php do_settings_sections($context['options_group_name']); ?>
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
