(function ($) {
    'use strict';

    $(window).load(function () {
        var color_pickers_data = [];

        if ($('body').hasClass('post-type-ppma_boxes') && $(".publishpress-author-box-editor").length > 0) {
            /**
             * set editor screen to one column
             */
            //$('#screen-options-wrap .columns-prefs-1 input').trigger('click');
            //$('#screen-options-wrap .columns-prefs').hide();

            /**
             * color picker init
             */
            $(".pp-editor-color-picker").each(function (e, i) {
                var picker_id = $(this).attr("id");
                var $pickr_variable;
                $pickr_variable = 'pickr_' + e;
                $pickr_variable = Pickr.create(
                    {
                        el: '.' + picker_id,
                        container: 'body',
                        theme: 'nano',
                        closeOnScroll: true,
                        padding: 8,
                        inline: false,
                        autoReposition: false,
                        sliders: 'h',
                        disabled: false,
                        comparison: false,
                        defaultRepresentation: 'HEX',
                        position: 'bottom-end',
                        adjustableNumbers: true,

                        default: $(this).val(),

                        swatches: [
                            'rgba(244, 67, 54, 1)',
                            'rgba(233, 30, 99, 0.95)',
                            'rgba(156, 39, 176, 0.9)',
                            'rgba(103, 58, 183, 0.85)',
                            'rgba(63, 81, 181, 0.8)',
                            'rgba(33, 150, 243, 0.75)',
                            'rgba(3, 169, 244, 0.7)',
                            'rgba(0, 188, 212, 0.7)',
                            'rgba(0, 150, 136, 0.75)',
                            'rgba(76, 175, 80, 0.8)',
                            'rgba(139, 195, 74, 0.85)',
                            'rgba(205, 220, 57, 0.9)',
                            'rgba(255, 235, 59, 0.95)',
                            'rgba(255, 193, 7, 1)'
                        ],

                        components: {
                            preview: true,
                            opacity: true,
                            hue: true,
                            interaction: {
                                hex: true,
                                rgba: true,
                                hsla: false,
                                hsva: false,
                                cmyk: false,
                                input: true,
                                clear: true,
                                save: true
                            },
                        },
                        i18n: {
                            'ui:dialog': 'color picker dialog',
                            'btn:toggle': 'toggle color picker dialog',
                            'btn:swatch': 'color swatch',
                            'btn:last-color': 'use previous color',
                            'btn:save': authorBoxesEditor.btnSave,
                            'btn:cancel': authorBoxesEditor.btnCancel,
                            'btn:clear': authorBoxesEditor.btnClear,
                            'aria:btn:save': 'save and close',
                            'aria:btn:cancel': 'cancel and close',
                            'aria:btn:clear': 'clear and close',
                            'aria:input': 'color input field',
                            'aria:palette': 'color selection area',
                            'aria:hue': 'hue selection slider',
                            'aria:opacity': 'selection slider'
                        },
                    }).on('change', (color, instance) => {
                        let hidden = document.getElementById(picker_id);
                        hidden.value = color.toHEXA().toString();
                        hidden.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                        hidden.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
                    }).on('clear', instance => {
                        let hidden = document.getElementById(picker_id);
                        hidden.value = 'transparent';
                        hidden.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
                        hidden.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
                    }).on('save', (color, instance) => {
                        $pickr_variable.hide();
                    });
                color_pickers_data[picker_id] = $pickr_variable;

            });
        }

        /**
         * boxes editor tab switch
         */
        $(document).on('click', '.ppma-author-box-editor-tabs a', function (event) {

            event.preventDefault();

            var clicked_tab = $(this).attr('data-tab');

            //remove active class from all tabs
            $('.ppma-author-box-editor-tabs a').removeClass('active');
            //add active class to current tab
            $(this).addClass('active');

            //hide all tabs contents
            $('.ppma-boxes-editor-tab-content').hide();
            //generate export data if it's export tab
            if (clicked_tab === 'export') {
                generateEditorExportData();
            }
            //show this current tab contents
            $('.ppma-' + clicked_tab + '-tab').show();
        });

        /**
         * range slider and input change
         */
        $(document).on('input', '.pp-editor-range, .pp-editor-range-input', function (event) {
            if ($(this).hasClass('pp-editor-range')) {
                $(this).closest('.input').find('.pp-editor-range-input').val($(this).val());
            } else {
                $(this).closest('.input').find('.pp-editor-range').val($(this).val());
            }
        });

        /**
         * Export copy to clipboard
         */
        $(document).on('click', '.ppma-editor-copy-clipboard', function (event) {
            event.preventDefault();
            //get the text field
            var data_input = event.target.closest('.input').querySelector('#' + $(this).attr('data-target_input'));
            //select the text field
            data_input.select();
            data_input.setSelectionRange(0, 99999); /* For mobile devices */
            //copy original text to clipboard
            navigator.clipboard.writeText(data_input.value);
            //show notification
            $(this).closest('.input').find(".ppma-editor-copied-to-clipboard").show().delay(2000).fadeOut('slow');
        });

        /**
         * Import data
         */
        $(document).on('click', '.ppma-editor-data-import', function (event) {
            event.preventDefault();
            var import_value = $('#import_action').val();
            if (import_value) {
                try {
                    import_value = atob(import_value);
                  } catch(e) {
                    $(".ppma-editor-data-imported").css('color', 'red').html($(this).attr('data-invalid')).show().delay(2000).fadeOut('slow');
                    return;
                }
                try {
                    import_value = JSON.parse(import_value);
                  } catch(e) {
                    $(".ppma-editor-data-imported").css('color', 'red').html($(this).attr('data-invalid')).show().delay(2000).fadeOut('slow');
                    return;
                }

                var editor_values = import_value;
                var key = '';
                for (key in editor_values) {
                    var value = editor_values[key];
                    var field  = $('[name="' + key + '"]');
                    field.val(value);
                    
                    if (field.attr('type') === 'checkbox') {
                        if (Number(value) > 0) {
                            field.prop('checked', true);
                        } else {
                            field.prop('checked', false);
                        }
                        field.val(1);
                    }
                    
                    if (field.hasClass('pp-editor-color-picker')) {
                        var field_picker = color_pickers_data[key];
                        field_picker.setColor(value, true);
                    }
                }

                setTimeout(
                    function() {
                        $('#import_action').val('');
                        $('#avatar_show').trigger('change');
                }, 1000);

            } else {
                $(".ppma-editor-data-imported").css('color', 'red').html($(this).attr('data-invalid')).show().delay(2000).fadeOut('slow');
            }
        });

        /**
         * Generate editor template
         */
        $(document).on('click', '.ppma-editor-generate-template', function (event) {
            event.preventDefault();
            $('.ppma-editor-generate-template').attr('disabled', true);
            $('.author-editor-loading-spinner').addClass('is-active');

            //prepare ajax data
            var editor_values = getAllEditorFieldsValues();
            var data = {
                action: "author_boxes_editor_get_template",
                editor_data: $.extend({}, editor_values),
                nonce: authorBoxesEditor.nonce,
            };
            $.post(ajaxurl, data, function (response) {
                var status  = response.status;
                var content = response.content;
                $('.ppma-editor-generate-template').attr('disabled', false);
                $('.author-editor-loading-spinner').removeClass('is-active');
                if (status === 'success') {
                    var template_content = content.replaceAll('</?php', '<?php');
                    $('#template_action').val(template_content);
                    $('.ppma-editor-template-generated').css('color', 'green').show().delay(2000).fadeOut('slow');
                } else {
                    $('.ppma-editor-template-generated').css('color', 'red').html(content).show().delay(2000).fadeOut('slow');
                }
            });

        });

        /**
         * editor live changes
         */
        $(document).on('change input keyup', '.ppma-author-box-editor-fields .input input, .ppma-author-box-editor-fields .input textarea, .ppma-author-box-editor-fields .input select', function () {
            var current_field = $(this);
            var current_field_name = current_field.attr('name');

            //update wrapper class with new name
            var box_wrapper_class = ' ' + $('#box_tab_custom_wrapper_class').val();
            var prev_layout_wrapper_classes = $('.pp-multiple-authors-boxes-wrapper').attr('data-original_class');
            $('.pp-multiple-authors-boxes-wrapper').attr('class', prev_layout_wrapper_classes + box_wrapper_class);

            var title_html_tag = $('#title_html_tag').val();
            var title_text = $('#title_text').val();

            //update title based on show/hide title
            if (current_field_name === 'show_title') {
                if (current_field.is(':checked')) {
                    if ($('.pp-multiple-authors-boxes-wrapper .box-header-title').length > 0) {
                        $('.pp-multiple-authors-boxes-wrapper .box-header-title').show();
                    } else {
                        $('.pp-multiple-authors-boxes-wrapper').prepend('<' + title_html_tag + ' class="widget-title box-header-title">' + title_text + '</' + title_html_tag + '>');
                    }
                } else {
                    $('.pp-multiple-authors-boxes-wrapper .box-header-title').hide();
                }
                return;
            }

            //update title / title html tag
            if (current_field_name === 'title_html_tag' || current_field_name === 'title_text') {
                //remove previous title with tag if exist
                $('.pp-multiple-authors-boxes-wrapper .box-header-title').remove();
                //create new one with updated data
                $('.pp-multiple-authors-boxes-wrapper').prepend('<' + title_html_tag + ' class="widget-title box-header-title">' + title_text + '</' + title_html_tag + '>');
                //make sure title is hidden if not set to show
                if (!$('#show_title').is(':checked')) {
                    $('.pp-multiple-authors-boxes-wrapper .box-header-title').hide();
                }
                return;
            }

            //update name html tag
            if (current_field_name === 'name_html_tag') {
                var name_html_tag = $('#name_html_tag').val();
                $(".pp-author-boxes-name").replaceWith(function () {
                    return "<" + name_html_tag + " class='pp-author-boxes-name multiple-authors-name'>" + this.innerHTML + "</" + name_html_tag + ">";
                });
                return;
            }

            //update bio html tag
            if (current_field_name === 'author_bio_html_tag') {
                //this only matter if bio is displayed
                if ($('#author_bio_show').is(':checked')) {
                    var author_bio_html_tag = $('#author_bio_html_tag').val();
                    $(".pp-author-boxes-description").replaceWith(function () {
                        return "<" + author_bio_html_tag + " class='pp-author-boxes-description multiple-authors-description'>" + this.innerHTML + "</" + author_bio_html_tag + ">";
                    });
                }
                return;
            }

            //update recent posts html tag
            if (current_field_name === 'author_recent_posts_html_tag') {
                //this only matter if recent posts is displayed
                if ($('#author_recent_posts_show').is(':checked')) {
                    var author_recent_posts_html_tag = $('#author_recent_posts_html_tag').val();
                    $(".pp-author-boxes-recent-posts-item").replaceWith(function () {
                        return "<" + author_recent_posts_html_tag + " class='pp-author-boxes-recent-posts-item'>" + this.innerHTML + "</" + author_recent_posts_html_tag + ">";
                    });
                }
                return;
            }

            //get editor field values
            var editor_values = getAllEditorFieldsValues();

            //update avatar size
            if (current_field_name === 'avatar_size') {
                //this only matter if avatar is displayed
                if ($('#avatar_show').is(':checked')) {
                    $('.pp-author-boxes-avatar img').attr('width', current_field.val()).attr('height', current_field.val());
                    generateEditorPreviewStyles(editor_values);
                }
                return;
            }

            var post_refresh_trigger = [
                'author_recent_posts_show',
                'author_recent_posts_title_show',
                'author_recent_posts_empty_show',
                'author_recent_posts_limit',
                'author_recent_posts_orderby',
                'author_recent_posts_order'
            ];
            var bio_refresh_trigger = [
                'author_bio_show',
                'author_bio_limit'
            ];
            var avatar_refresh_trigger = [
                'avatar_show'
            ];
            var meta_refresh_trigger = [
                'meta_show',
                'meta_view_all_show',
                'meta_email_show',
                'meta_site_link_show'
            ];
            var force_refresh = false;

            if (post_refresh_trigger.includes(current_field_name) || bio_refresh_trigger.includes(current_field_name) || avatar_refresh_trigger.includes(current_field_name) || meta_refresh_trigger.includes(current_field_name)) {
                force_refresh = true;
            }

            generateEditorPreview(editor_values, force_refresh);
        });

        /**
         * Function for generating editor preview
         * @param {*} editor_values
         * @param {*} force_refresh
         */
        function generateEditorPreview(editor_values, force_refresh) {
            //send ajax to refresh needed part
            if (force_refresh) {
                $('.pp-multiple-authors-boxes-wrapper').html('<div class="author-boxes-loading-spinner spinner is-active" style="float: none;"></div>');
                $('.pp-author-boxes-editor-preview-styles').remove();

                //prepare ajax data
                var data = {
                    action: "author_boxes_editor_get_preview",
                    editor_data: $.extend({}, editor_values),
                    author_term_id: authorBoxesEditor.author_term_id,
                    post_id: authorBoxesEditor.post_id,
                    nonce: authorBoxesEditor.nonce,
                };
                $.post(ajaxurl, data, function (response) {
                    $('.pp-author-boxes-editor-preview-styles').remove();
                    $('.pp-multiple-authors-boxes-wrapper').replaceWith(response.content);
                    generateEditorPreviewStyles(editor_values);
                });
            }
            generateEditorPreviewStyles(editor_values);
        }

        /**
         * Return editor fields values
         * @param {*} populate
         */
        function getAllEditorFieldsValues() {
            var editor_values = [];
            var input_value = '';
            $('.ppma-author-box-editor-fields .input input, .ppma-author-box-editor-fields .input textarea, .ppma-author-box-editor-fields .input select').each(function () {
                var excluded_input = ['export_action', 'import_action', 'template_action'];
                if (!excluded_input.includes($(this).attr('name'))) {
                    if ($(this).attr('type') === 'checkbox') {
                        input_value = ($(this).is(':checked')) ? '1' : '';
                    } else {
                        input_value = $(this).val();
                    }
                    editor_values[$(this).attr('name')] = input_value;
                }
            });

            return editor_values;
        }

        /**
         * Generate and populate export data
         * @param {*} populate
         * @returns
         */
        function generateEditorExportData(populate = true) {
            var editor_values = getAllEditorFieldsValues();
                editor_values = Object.assign({}, editor_values);
                editor_values = JSON.stringify(editor_values);
                editor_values = btoa(editor_values);
            if (populate) {
                $('#export_action').val(editor_values);
            } else {
                return editor_values;
            }
        }

        /**
         * Function for generating editor preview styles
         * @param {*} editor_values
         */
        function generateEditorPreviewStyles(editor_values) {
            var editor_preview_styles = '';
            var key = '';
            var post_id = authorBoxesEditor.post_id;

            if (Number(editor_values.avatar_show) === 0) {
                editor_preview_styles += '.pp-multiple-authors-layout-boxed ul li > div:nth-child(1) {flex: 1 !important;}';
            }
            for (key in editor_values) {
                var value = editor_values[key];
                switch (key) {
                    //title styles
                    case 'title_bottom_space':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { margin-bottom: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'title_size':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { font-size: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'title_line_height':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { line-height: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'title_weight':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { font-weight: ' + value + ' !important; } ';
                        }
                      break;
                    case 'title_transform':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { text-transform: ' + value + ' !important; } ';
                        }
                      break;
                    case 'title_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { font-style: ' + value + ' !important; } ';
                        }
                    case 'title_decoration':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { text-decoration: ' + value + ' !important; } ';
                        }
                      break;
                    case 'title_alignment':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { text-align: ' + value + ' !important; } ';
                        }
                      break;
                    case 'title_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .box-header-title { color: ' + value + ' !important; } ';
                        }
                      break;
                    //avatar styles
                    case 'avatar_size':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-avatar img { width: ' + value + 'px !important; height: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'avatar_border_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-avatar img { border-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'avatar_border_width':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-avatar img { border-width: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'avatar_border_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-avatar img { border-color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'avatar_border_radius':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-avatar img { border-radius: ' + value + '% !important; } ';
                        }
                      break;
                    //name styles
                    case 'name_size':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { font-size: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'name_line_height':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { line-height: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'name_weight':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { font-weight: ' + value + ' !important; } ';
                        }
                      break;
                    case 'name_transform':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { text-transform: ' + value + ' !important; } ';
                        }
                      break;
                    case 'name_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { font-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'name_decoration':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { text-decoration: ' + value + ' !important; } ';
                        }
                      break;
                    case 'name_alignment':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name { text-align: ' + value + ' !important; } ';
                        }
                      break;
                    case 'name_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-name a { color: ' + value + ' !important; } ';
                        }
                      break;
                    //bio styles
                    case 'author_bio_size':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { font-size: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'author_bio_line_height':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { line-height: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'author_bio_weight':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { font-weight: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_bio_transform':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { text-transform: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_bio_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { font-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_bio_decoration':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { text-decoration: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_bio_alignment':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { text-align: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_bio_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-description { color: ' + value + ' !important; } ';
                        }
                      break;
                    //meta styles
                    case 'meta_size':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a span { font-size: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'meta_line_height':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a span { line-height: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'meta_weight':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a span { font-weight: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_transform':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a span { text-transform: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a span { font-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_decoration':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a span { text-decoration: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_alignment':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta { text-align: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_background_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a { background-color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a { color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'meta_link_hover_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-meta a:hover { color: ' + value + ' !important; } ';
                        }
                      break;
                    //recent posts styles
                    case 'author_recent_posts_title_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-title { color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_title_border_bottom_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-title { border-bottom-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_title_border_width':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-title { border-width: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'author_recent_posts_title_border_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-title { border-color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_size':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { font-size: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'author_recent_posts_line_height':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { line-height: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'author_recent_posts_weight':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { font-weight: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_transform':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { text-transform: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { font-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_decoration':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { text-decoration: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_alignment':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item { text-align: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item a { color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'author_recent_posts_icon_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-author-boxes-recent-posts-item span.dashicons { color: ' + value + ' !important; } ';
                        }
                      break;
                    //box layout styles
                    case 'box_layout_margin_top':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { margin-top: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_margin_bottom':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { margin-bottom: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_margin_left':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { margin-left: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_margin_right':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { margin-right: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_padding_top':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { padding-top: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_padding_bottom':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { padding-bottom: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_padding_left':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { padding-left: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_padding_right':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { padding-right: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_border_style':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { border-style: ' + value + ' !important; } ';
                        }
                      break;
                    case 'box_layout_border_width':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { border-width: ' + value + 'px !important; } ';
                        }
                      break;
                    case 'box_layout_border_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { border-color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'box_layout_box_width':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { width: ' + value + '% !important; } ';
                        }
                      break;
                    case 'box_layout_background_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { background-color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'box_layout_color':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { color: ' + value + ' !important; } ';
                        }
                      break;
                    case 'box_layout_shadow_color':
                        var shadow_color = editor_values['box_layout_shadow_color'];
                        var shadow_horizontal_offset = editor_values['box_layout_shadow_horizontal_offset'];
                        shadow_horizontal_offset = shadow_horizontal_offset ? shadow_horizontal_offset : 0;
                        var shadow_vertical_offset = editor_values['box_layout_shadow_vertical_offset'];
                        shadow_vertical_offset = shadow_vertical_offset ? shadow_vertical_offset : 0;
                        var shadow_blur = editor_values['box_layout_shadow_blur'];
                        shadow_blur = shadow_blur ? shadow_blur : 0;
                        var shadow_speed = editor_values['box_layout_shadow_speed'];
                        shadow_speed = shadow_speed ? shadow_speed : 0;
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { box-shadow: ' + shadow_horizontal_offset + 'px ' + shadow_vertical_offset + 'px ' + shadow_blur + 'px ' + shadow_speed + 'px ' + shadow_color + ' !important; } ';
                        }
                      break;
                    case 'box_layout_border_radius':
                        if (value) {
                            editor_preview_styles += '.pp-multiple-authors-boxes-wrapper.box-post-id-' + post_id + ' .pp-multiple-authors-boxes-li { border-radius: ' + value + 'px !important; } ';
                        }
                      break;
                    // custom css style
                    case 'box_tab_custom_css':
                        if (value) {
                            editor_preview_styles += value;
                        }
                }
            }
            $('.pp-author-boxes-editor-preview-styles style').html(editor_preview_styles);
        }

    });

})(jQuery);
