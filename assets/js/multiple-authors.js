/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */
jQuery(document).ready(function ($) {
    // Copied from ExtJS.
    window.htmlEnDeCode = (function () {
        var charToEntityRegex,
            entityToCharRegex,
            charToEntity,
            entityToChar;

        function resetCharacterEntities () {
            charToEntity = {};
            entityToChar = {};
            // add the default set
            addCharacterEntities({
                '&amp;': '&',
                '&gt;': '>',
                '&lt;': '<',
                '&quot;': '"',
                '&#39;': '\''
            });
        }

        function addCharacterEntities (newEntities) {
            var charKeys = [],
                entityKeys = [],
                key, echar;
            for (key in newEntities) {
                echar = newEntities[key];
                entityToChar[key] = echar;
                charToEntity[echar] = key;
                charKeys.push(echar);
                entityKeys.push(key);
            }
            charToEntityRegex = new RegExp('(' + charKeys.join('|') + ')', 'g');
            entityToCharRegex = new RegExp('(' + entityKeys.join('|') + '|&#[0-9]{1,5};' + ')', 'g');
        }

        function htmlEncode (value) {
            var htmlEncodeReplaceFn = function (match, capture) {
                return charToEntity[capture];
            };

            return (!value) ? value : String(value).replace(charToEntityRegex, htmlEncodeReplaceFn);
        }

        function htmlDecode (value) {
            var htmlDecodeReplaceFn = function (match, capture) {
                return (capture in entityToChar) ? entityToChar[capture] : String.fromCharCode(parseInt(capture.substr(2), 10));
            };

            return (!value) ? value : String(value).replace(entityToCharRegex, htmlDecodeReplaceFn);
        }

        resetCharacterEntities();

        return {
            htmlEncode: htmlEncode,
            htmlDecode: htmlDecode
        };
    })();

    //==================================================================
    /**
     * Based on Bylines.
     */
    $('.authors-select2.authors-search').each(function () {
        var authorsSearch = $(this).ppma_select2({
            placeholder: $(this).data('placeholder'),
            allowClear: true,
            ajax: {
                url: window.ajaxurl + '?action=authors_search&nonce=' + $(this).data('nonce'),
                dataType: 'json',
                data: function (params) {
                    var ignored = [];
                    $('.authors-list input').each(function () {
                        ignored.push($(this).val());
                    });
                    return {
                        q: params.term,
                        ignored: ignored
                    };
                }
            }
        });
        authorsSearch.on('select2:select', function (e) {
            var template = wp.template('authors-author-partial');
            $('.authors-list').append(window.htmlEnDeCode.htmlDecode(template(e.params.data)));
            authorsSearch.val(null).trigger('change');
        });
    });
    $('.authors-list.authors-current-user-can-assign').sortable()
        .on('click', '.author-remove', function () {

            var el = $(this);
            el.closest('li').remove();
        });

    $('.authors-select2-user-select').each(function () {
        $(this).ppma_select2({
            allowClear: true,
            placeholder: $(this).attr('placeholder'),
            ajax: {
                url: window.ajaxurl + '?action=authors_users_search&nonce=' + $(this).data('nonce'),
                dataType: 'json',
                data: function (params) {
                    return {
                        q: params.term
                    };
                }
            }
        });
    });

    $('.author-image-field-wrapper').each(function () {
        var frame,
            target = $(this), // Your meta box id here
            deleteImgLink = target.find('.select-author-image-field'),
            delImgLink = target.find('.delete-author-image-field'),
            imgContainer = target.find('.author-image-field-container'),
            imgIdInput = target.find('.author-image-field-id');

        deleteImgLink.on('click', function (event) {
            event.preventDefault();

            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                // title: 'title',
                // button: {
                //     text: 'select'
                // },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            frame.on('select', function () {
                var attachment = frame.state().get('selection').first().toJSON();
                var attachment_src = ('undefined' === typeof attachment.sizes.thumbnail ? attachment.url : attachment.sizes.thumbnail.url);
                var imgEl = $('<img />');
                imgEl.attr('src', attachment_src);
                imgContainer.append(imgEl);
                imgIdInput.val(attachment.id);
                deleteImgLink.addClass('hidden');
                delImgLink.removeClass('hidden');
            });

            frame.open();
        });

        delImgLink.on('click', function (event) {
            event.preventDefault();
            imgContainer.html('');
            deleteImgLink.removeClass('hidden');
            delImgLink.addClass('hidden');
            imgIdInput.val('');
        });

    });
    //==================================================================

    // Fix the admin menu selection for Authors.
    if ((window.location.pathname === '/wp-admin/edit-tags.php' || window.location.pathname === '/wp-admin/term.php')
        && window.location.search.search('taxonomy=author') === 1) {

        $('#menu-posts')
            .removeClass('wp-has-current-submenu wp-menu-open')
            .addClass('wp-not-current-submenu');
        $('#menu-posts > a')
            .removeClass('wp-current-submenu wp-has-current-submenu wp-menu-open')
            .addClass('wp-not-current-submenu wp-menu-open open-if-no-js menu-top-first');
        $('#toplevel_page_' + MultipleAuthorsStrings.menu_slug)
            .removeClass('wp-not-current-submenu')
            .addClass('wp-has-current-submenu wp-menu-open toplevel_page_' + MultipleAuthorsStrings.menu_slug);
        $('#toplevel_page_' + MultipleAuthorsStrings.menu_slug + ' > a')
            .removeClass('wp-not-current-submenu')
            .addClass('wp-has-current-submenu wp-menu-open open-if-no-js menu-top-first');
    }

    var $mappedUser = $('select[name="authors-user_id"]');
    var $slug = $('#slug');

    // Add action to the Mapped User field in the Author form.
    if ($mappedUser.length > 0) {
        // Disable the slug field if there is a mapped user.
        $slug.attr('disabled', $mappedUser.val() !== '');

        // Fix the order of fields
        $($slug.parent().parent().before($mappedUser.parent().parent()));

        $mappedUser.on('change', function (event) {
            var selected = $mappedUser.val();

            // Update the status of the slug field
            $slug.attr('disabled', $mappedUser.val() !== '');

            if (selected === '') {
                return;
            }

            $.getJSON(
                MultipleAuthorsStrings.ajax_get_author_data_url,
                {
                    'user_id': selected
                },
                function (data) {
                    var fields = [
                        'first_name',
                        'last_name',
                        'user_email',
                        'user_url'
                    ];

                    $.each(fields, function (i, item) {
                        var $field = $('input[name="authors-' + item + '"]');
                        if ($field.val() === '') {
                            $field.val(data[item]);
                        }
                    });

                    var $field = $('textarea[name="authors-description"]');
                    if ($field.val() === '') {
                        $field.val(data.description);
                    }

                    // Slug always change to be in sync
                    $slug.val(data.slug);
                }
            );
        });
    }

    // Add action to the Mapped User Field in the New Author form.
    $mappedUser = $('.taxonomy-author .authors-select2-user-select');

    if ($mappedUser.length > 0) {
        $mappedUser.on('change', function () {
            if ($('#tag-name').val() == '') {
                $('#tag-name').val($mappedUser[0].options[$mappedUser[0].selectedIndex].text);
            }
        });
    }

    // Reset the field after the form was submitted.
    $('#submit').click(function (event) {
        window.setTimeout(function () {
            $mappedUser.val('').trigger('change');
            $('#tag-name').focus();
        }, 1000);

        return true;
    });

    /**
     * Displays a confirmation popup before clicking on the restore authors buttons.
     */

    var buttons = [
        '#create_post_authors',
        '#create_role_authors',
        '#delete_mapped_authors',
        '#delete_guest_authors',
    ];
    var msg;

    $.each(buttons, function (index, item) {
        $(item).click(function (event) {
            msg = 'confirm_' + item.replace('#', '');
            if (confirm(MultipleAuthorsStrings[msg])) {
                return true;
            }

            event.preventDefault();
            return false;
        });
    });
});

if (typeof (console) === 'undefined') {
    var console = {};
    console.log = console.error = function () {};
}
