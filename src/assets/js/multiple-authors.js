/**
 * @package   MultipleAuthors
 * @author    PublishPress <help@publishpress.com>
 * @copyright Copyright (C) 2018 PublishPress. All rights reserved.
 * @license   GPLv2 or later
 * @since     1.0.0
 */
jQuery(document).ready(function ($) {
    // Copied from ExtJS.
    window.htmlEnDeCode = (function () {
        var charToEntityRegex, entityToCharRegex, charToEntity, entityToChar;

        function resetCharacterEntities() {
            charToEntity = {};
            entityToChar = {};
            // add the default set
            addCharacterEntities({
                "&amp;": "&",
                "&gt;": ">",
                "&lt;": "<",
                "&quot;": '"',
                "&#39;": "'"
            });
        }

        function addCharacterEntities(newEntities) {
            var charKeys = [],
                entityKeys = [],
                key,
                echar;
            for (key in newEntities) {
                echar = newEntities[key];
                entityToChar[key] = echar;
                charToEntity[echar] = key;
                charKeys.push(echar);
                entityKeys.push(key);
            }
            charToEntityRegex = new RegExp("(" + charKeys.join("|") + ")", "g");
            entityToCharRegex = new RegExp(
                "(" + entityKeys.join("|") + "|&#[0-9]{1,5};" + ")",
                "g"
            );
        }

        function htmlEncode(value) {
            var htmlEncodeReplaceFn = function (match, capture) {
                return charToEntity[capture];
            };

            return !value
                ? value
                : String(value).replace(charToEntityRegex, htmlEncodeReplaceFn);
        }

        function htmlDecode(value) {
            var htmlDecodeReplaceFn = function (match, capture) {
                return capture in entityToChar
                    ? entityToChar[capture]
                    : String.fromCharCode(parseInt(capture.substr(2), 10));
            };

            return !value
                ? value
                : String(value).replace(entityToCharRegex, htmlDecodeReplaceFn);
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
    function authorsSelect2(selector) {
        selector.each(function () {
            var authorsSearch = $(this).ppma_select2({
                placeholder: $(this).data("placeholder"),
                allowClear: true,
                ajax: {
                    url:
                        window.ajaxurl +
                        "?action=authors_search&nonce=" +
                        $(this).data("nonce"),
                    dataType: "json",
                    data: function (params) {
                        var ignored = [];
                        selector
                            .closest("div")
                            .find(".authors-list input")
                            .each(function () {
                                ignored.push($(this).val());
                            });
                        return {
                            q: params.term,
                            ignored: ignored
                        };
                    }
                }
            });
            authorsSearch.on("ppma_select2:select", function (e) {
                var template = wp.template("authors-author-partial");
                $(".authors-list").append(
                    window.htmlEnDeCode.htmlDecode(template(e.params.data))
                );
                authorsSearch.val(null).trigger("change");
                handleUsersAuthorField();
            });
        });
    }

    function hasSelectedOnlyGuests(parent) {
        if (typeof parent === 'undefined' || parent.length == 0) {
            parent = $('.authors-list');
        }

        let selectedAuthors = $(parent).find('li');
        let guestAuthorsCount = 0;

        for (let i = 0; i < selectedAuthors.length; i++) {
            if ($(selectedAuthors.get(i)).data('is-guest') == 1) {
                guestAuthorsCount++;
            }
        }

        return guestAuthorsCount === selectedAuthors.length;
    }

    function handleUsersAuthorField(parent) {
        let $authorsUserField = $('#publishpress-authors-user-author-wrapper');
        if (hasSelectedOnlyGuests(parent)) {
            $authorsUserField.show();
        } else {
            $authorsUserField.hide();
        }
    }

    function authorsUserSelect2(selector) {
        selector.each(function () {
            var authorsSearch = $(this).ppma_select2({
                placeholder: $(this).data("placeholder"),
                allowClear: true,
                ajax: {
                    url:
                        window.ajaxurl +
                        "?action=authors_users_search&nonce=" +
                        $(this).data("nonce"),
                    dataType: "json",
                    data: function (params) {
                        return {
                            q: params.term,
                            ignored: []
                        };
                    }
                }
            });
        });
    }

    function authorsUserSlugSelect2(selector) {
        selector.each(function () {
            var authorsSearch = $(this).ppma_select2({
                placeholder: $(this).data("placeholder"),
                allowClear: true,
                ajax: {
                    url:
                        window.ajaxurl +
                        "?action=authors_filter_authors_search&field=slug&nonce=" +
                        $(this).data("nonce"),
                    dataType: "json",
                    data: function (params) {
                        return {
                            q: params.term,
                            ignored: []
                        };
                    }
                }
            });
        });
    }
    
    if ($("body").hasClass("post-php") || $("body").hasClass("post-new-php")) {
        authorsSelect2($(".authors-select2.authors-search"));
        authorsUserSelect2($('.authors-user-search'));
        sortedAuthorsList($(".authors-current-user-can-assign"));
        handleUsersAuthorField();
    }

    if ($("body").hasClass("edit-php")) {
        authorsUserSlugSelect2($('.authors-user-slug-search'));
    }

    /****************
     * Quick Edit
     ****************/
    $(document).on('click', '.editinline', function () {
        var postId = $(this)
            .closest('tr')
            .attr('id')
            .replace('post-', '')
            .trim();

        var timeoutFn = setTimeout(function () {
            var $quickEditTr = $('#edit-' + postId);
            var $select = $quickEditTr.find('.authors-select2.authors-search');
            var $authorsList = $quickEditTr.find('.authors-current-user-can-assign');
            var $usersList = $quickEditTr.find('.authors-user-search');

            authorsSelect2($select);
            authorsUserSelect2($usersList);
            $authorsList.empty();

            $('#post-' + postId)
                .find('td.column-authors > a.author_name')
                .each(function () {
                    var listItemTmpl = wp.template("authors-author-partial");

                    $authorsList.append(
                        window.htmlEnDeCode.htmlDecode(
                            listItemTmpl({
                                'display_name': $(this).data('author-display-name'),
                                'id': $(this).data('author-term-id'),
                                'is_guest': $(this).data('author-is-guest')
                            })
                        )
                    );
                });

            sortedAuthorsList($authorsList);
            $select.val(null).trigger('change');
            handleUsersAuthorField();

            clearTimeout(timeoutFn);
        }, 50);
    });

    /**************
     * Bulk edit
     *************/
    $("#wpbody").on("click", "#doaction, #doaction2", function () {
        var action = $(this).is("#doaction")
            ? $("#bulk-action-selector-top").val()
            : $("#bulk-action-selector-bottom").val();
        if (action === "edit") {
            authorsSelect2($("#bulk-edit .authors-select2.authors-search"));
            sortedAuthorsList($("#bulk-edit .authors-current-user-can-assign"));
            authorsUserSelect2($("#bulk-edit .authors-user-search"));
            handleUsersAuthorField();
            $("#bulk-edit .authors-current-user-can-assign")
                .find("li")
                .each(function () {
                    $(this).remove();
                });
        }
    });

    // Apply ajax request on bulk edit.
    $(document).on("click", "#bulk_edit", function () {
        // define the bulk edit row
        var $bulk_row = $("#bulk-edit");

        // get the selected post ids that are being edited
        var $post_ids = new Array();
        $bulk_row
            .find("#bulk-titles")
            .children()
            .each(function () {
                var new_id = Number($(this)
                    .attr("id")
                    .replace(/^(ttle)/i, ""));
                if (new_id > 0) {
                    $post_ids.push(new_id);
                }
            });
        
        if (!$post_ids.length) {
            $bulk_row
                .find("#bulk-titles .ntdelitem .button-link")
                .each(function () {
                    var new_id = Number($(this)
                        .attr("id")
                        .replace(/\D/g,''));
                    if (new_id > 0) {
                        $post_ids.push(new_id);
                    }
                });
        }

        // get the data
        var selectedAuthors = [];
        $bulk_row.find(".authors-list input").each(function () {
            selectedAuthors.push(parseInt($(this).val()));
        });

        var selectedFallbackUser = $('#publishpress-authors-user-author-select').val();

        // save the data
        $.ajax({
            url: ajaxurl,
            type: "POST",
            async: false,
            cache: false,
            data: {
                action: "save_bulk_edit_authors",
                post_ids: $post_ids, // and these are the 2 parameters we're passing to our function
                authors_ids: selectedAuthors,
                fallback_author_user: selectedFallbackUser,
                bulkEditNonce: bulkEditNonce.nonce
            }
        });
    });

    function sortedAuthorsList(selector) {
        selector.sortable().on("click", ".author-remove", function () {
            var el = $(this);
            el.closest("li").remove();
            handleUsersAuthorField($(this).parent('.authors-list'));
        });
    }

    $(".authors-select2-user-select").each(function () {
        $(this).ppma_select2({
            allowClear: true,
            placeholder: $(this).attr("placeholder"),
            ajax: {
                url:
                    window.ajaxurl +
                    "?action=authors_users_search&nonce=" +
                    $(this).data("nonce"),
                dataType: "json",
                data: function (params) {
                    return {
                        q: params.term
                    };
                }
            }
        });
    });

    $(".author-image-field-wrapper").each(function () {
        var frame,
            target = $(this), // Your meta box id here
            deleteImgLink = target.find(".select-author-image-field"),
            delImgLink = target.find(".delete-author-image-field"),
            imgContainer = target.find(".author-image-field-container"),
            imgIdInput = target.find(".author-image-field-id");

        deleteImgLink.on("click", function (event) {
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
                    type: "image"
                }
            });
            frame.on("select", function () {
                var attachment = frame
                    .state()
                    .get("selection")
                    .first()
                    .toJSON();
                var attachment_src =
                    "undefined" === typeof attachment.sizes.thumbnail
                        ? attachment.url
                        : attachment.sizes.thumbnail.url;
                var imgEl = $("<img />");
                imgEl.attr("src", attachment_src);
                imgContainer.append(imgEl);
                imgIdInput.val(attachment.id);
                deleteImgLink.addClass("hidden");
                delImgLink.removeClass("hidden");
            });

            frame.open();
        });

        delImgLink.on("click", function (event) {
            event.preventDefault();
            imgContainer.html("");
            deleteImgLink.removeClass("hidden");
            delImgLink.addClass("hidden");
            imgIdInput.val("");
        });
    });
    //==================================================================

    // Fix the admin menu selection for Authors.
    // phpcs:disable
    if (
        (window.location.pathname === "/wp-admin/edit-tags.php" ||
            window.location.pathname === "/wp-admin/term.php") &&
        window.location.search.search("taxonomy=author") === 1
    ) {
        $("#menu-posts")
            .removeClass("wp-has-current-submenu wp-menu-open")
            .addClass("wp-not-current-submenu");
        $("#menu-posts > a")
            .removeClass("wp-current-submenu wp-has-current-submenu wp-menu-open")
            .addClass(
                "wp-not-current-submenu wp-menu-open open-if-no-js menu-top-first"
            );
        $("#toplevel_page_" + MultipleAuthorsStrings.menu_slug)
            .removeClass("wp-not-current-submenu")
            .addClass(
                "wp-has-current-submenu wp-menu-open toplevel_page_" +
                MultipleAuthorsStrings.menu_slug
            );
        $("#toplevel_page_" + MultipleAuthorsStrings.menu_slug + " > a")
            .removeClass("wp-not-current-submenu")
            .addClass(
                "wp-has-current-submenu wp-menu-open open-if-no-js menu-top-first"
            );
    }
    // phpcs:enable

    var $mappedUser = $('select[name="authors-user_id"]');
    var $slug = $("#slug");

    // Add action to the Mapped User field in the Author form.
    if ($mappedUser.length > 0) {
        // Disable the slug field if there is a mapped user.
        $slug.attr("disabled", $mappedUser.val() !== "");

        // Fix the order of fields
        $(
            $slug
                .parent()
                .parent()
                .before($mappedUser.parent().parent())
        );

        $mappedUser.on("change", function (event) {
            var selected = $mappedUser.val();

            // Update the status of the slug field
            $slug.attr("disabled", $mappedUser.val() !== "");

            if (selected === "") {
                return;
            }

            $.getJSON(
                MultipleAuthorsStrings.ajax_get_author_data_url,
                {
                    user_id: selected
                },
                function (data) {
                    var fields = ["first_name", "last_name", "user_email", "user_url"];

                    $.each(fields, function (i, item) {
                        var $field = $('input[name="authors-' + item + '"]');
                        if ($field.val() === "") {
                            $field.val(data[item]);
                        }
                    });

                    var $field = $('textarea[name="authors-description"]');
                    if ($field.val() === "") {
                        $field.val(data.description);
                    }

                    // Slug always change to be in sync
                    $slug.val(data.slug);
                }
            );
        });
    }

    // Add action to the Mapped User Field in the New Author form.
    $mappedUser = $(".taxonomy-author .authors-select2-user-select");

    if ($mappedUser.length > 0) {
        $mappedUser.on("change", function () {
            if ($("#tag-name").val() == "") {
                $("#tag-name").val(
                    $mappedUser[0].options[$mappedUser[0].selectedIndex].text
                );
            }
        });
    }

    // Reset the field after the form was submitted.
    $("#submit").click(function (event) {
        window.setTimeout(function () {
            $mappedUser.val("").trigger("change");
            $("#tag-name").focus();
        }, 1000);

        return true;
    });

    /**
     * Displays a confirmation popup before clicking on the restore authors buttons.
     */

    var buttons = [
        "#create_post_authors",
        "#create_role_authors",
        "#delete_mapped_authors",
        "#delete_guest_authors"
    ];
    var msg;

    $.each(buttons, function (index, item) {
        $(item).click(function (event) {
            msg = "confirm_" + item.replace("#", "");
            if (confirm(MultipleAuthorsStrings[msg])) {
                return true;
            }

            event.preventDefault();
            return false;
        });
    });

    if ($('body').hasClass('taxonomy-author')) {
        /**
         * Add tab class to author editor's tr without tab
         *
         * This will add general tab class to 'Name' and Author URL
         * or any tab that's rendered by default or third party
         *  without tab attribute
         */
        $('form#edittag tr.form-field:not(.ppma-tab-content)')
            .addClass('ppma-tab-content ppma-general-tab')
            .attr('data-tab', 'general');
        
        /**
         * Add view link to author url field
         */
        $('form#edittag tr.form-field #slug').after('<a href="' + MultipleAuthorsStrings.term_author_link + '" class="button-secondary" target="_blank">' + MultipleAuthorsStrings.view_text + '</a>');
    }

    /**
     * Author editor tab switch
     */
    $(document).on('click', '.ppma-editor-tabs a', function (event) {

        event.preventDefault();

        var clicked_tab = $(this).attr('data-tab');

        //remove active class from all tabs
        $('.ppma-editor-tabs a').removeClass('nav-tab-active');
        //add active class to current tab
        $(this).addClass('nav-tab-active');

        //hide all tabs contents
        $('.ppma-tab-content').hide();
        //show this current tab contents
        $('.ppma-' + clicked_tab + '-tab').show();

        // Make sure the description field is hidden. It was being displayed after navigating throw tabs.
        $('.form-field.term-description-wrap').hide();
    });

    /**
     * Author image avatar source option toggle
     */
    $(document).on('click', 'input[name="authors-avatar-options"]', function () {
        var clicked_element = $(this);

        if (clicked_element.val() === 'custom_image') {
            clicked_element.closest('tr').find('.author-image-field-wrapper').show();
        } else {
            //trigger image remove action
            clicked_element.closest('tr').find('.delete-author-image-field').trigger('click');
            //hide image field wrapper
            clicked_element.closest('tr').find('.author-image-field-wrapper').hide();
        }
    });

    /**
     * Switch focus to general email from image tab handler
     */
    $(document).on('click', '.ppma-image-general-author-focus', function (event) {
        event.preventDefault();
        //triger click on general tab
        $('.ppma-editor-tabs a[data-tab="general"]').trigger('click');
        //set focus on email field
        $('input[name="authors-user_email"]').focus();
    });

    //process a request to validate author mapped user.
    $('body.taxonomy-author form#edittag').submit(function (event) {

        var $mappedUser = $('select[name="authors-user_id"]').val();
        var $authorSlug = $('input[name="slug"]').val();
        var $termId = $('input[name="tag_ID"]').val();
        var $form = $(this);

        $('.author-response-notice').remove();

        event.preventDefault();

        //prepare ajax data
        var data = {
            action: "mapped_author_validation",
            author_id: $mappedUser,
            author_slug: $authorSlug,
            term_id: $termId,
            nonce: MultipleAuthorsStrings.mapped_author_nonce,
        };

        if ($('.author-loading-spinner').length === 0) {
            $('.edit-tag-actions input[type="submit"]').after('<div class="author-loading-spinner spinner is-active" style="float: none;"></div>');
        }

        $('.author-loading-spinner').addClass('is-active');

        $.post(ajaxurl, data, function (response) {
            if (response.status === 'error') {
                $('.edit-tag-actions').after('<div class="author-response-notice notice notice-error" style="margin-top: 10px;"><p> ' + response.content + ' </p></div>');
                $('.author-loading-spinner').removeClass('is-active');
            } else {
                $form.unbind('submit').submit();
            }
        });

    });

    //change submit button to enable slug generation on custom button click
    if ($('body.taxonomy-author form#addtag #submit').length > 0) {
        $('body.taxonomy-author form#addtag #submit').hide();
        $('body.taxonomy-author form#addtag #submit').after('<input type="button" id="author-submit" class="button button-primary" value="' + $('body.taxonomy-author form#addtag #submit').val() + '">');
    }

    //generate author slug when adding author.
    $(document).on('click', 'body.taxonomy-author form#addtag #author-submit', function (event) {

        var $authorName = $('input[name="tag-name"]').val();
        var $form = $(this).closest('form#addtag');

        event.preventDefault();

        //prepare ajax data
        var data = {
            action: "handle_author_slug_generation",
            author_name: $authorName,
            nonce: MultipleAuthorsStrings.generate_author_slug_nonce,
        };

        $form.find('.spinner').addClass('is-active');

        $.post(ajaxurl, data, function (response) {
            $form.find('.spinner').removeClass('is-active');
            if (response.author_slug) {
                $('input[name="slug"]').val(response.author_slug);
                $('body.taxonomy-author form#addtag #submit').trigger('click');
            } else {
                $('body.taxonomy-author form#addtag #submit').trigger('click');
            }
        });

    });

    /**
     * Settings shortcode copy to clipboard
     */
    $(document).on('click', '.ppma-copy-clipboard', function (event) {
        //get the text field
        var shortcode_input = event.target.closest('.ppma-settings-shortcodes-shortcode').querySelector('.shortcode-field');
        //select the text field
        shortcode_input.select();
        shortcode_input.setSelectionRange(0, 99999); /* For mobile devices */
        //copy the text inside the text field
        navigator.clipboard.writeText(shortcode_input.value);
        //update tooltip notification
        event.target.closest('.ppma-settings-shortcodes-shortcode')
            .querySelector('.ppma-copy-clipboard span')
            .innerHTML = event.target.closest('.ppma-settings-shortcodes-shortcode').querySelector('.ppma-copy-clipboard span')
                .getAttribute('data-copied');
    });

    /**
     * Copy to clipboard copied text change
     */
    $(document).on('mouseleave', '.ppma-copy-clipboard', function (event) {
        //update tooltip text
        event.target.closest('.ppma-settings-shortcodes-shortcode')
            .querySelector('.ppma-copy-clipboard span')
            .innerHTML = event.target.closest('.ppma-settings-shortcodes-shortcode').querySelector('.ppma-copy-clipboard span')
                .getAttribute('data-copy');
    });

    /**
     * Author profile edit active class for when 
     * user is editing own profile.
     * 
     * i.) Remove active class from main author 
     * profile if it has one .
     * 
     * ii.) Add active class to new author profile
     * link
     */
    if ($('body').hasClass('own-profile-edit')) {
        var main_menu   = $("#toplevel_page_ppma-authors");
        var profile_menu = $("li[class*=' toplevel_page_term?taxonomy=author&tag_ID']");

        //remove active from main author menu
        main_menu
            .addClass('wp-not-current-submenu')
            .removeClass('wp-has-current-submenu')
            .removeClass('wp-menu-open')
            .removeClass('current')
            .find('ul li.current')
            .removeClass('current');
        
        //add class to user author menu
        profile_menu
            .removeClass('wp-not-current-submenu')
            .addClass('current');
        
        profile_menu
            .find('a')
            .removeClass('wp-not-current-submenu')
            .addClass('current');
             
    }

});

if (typeof console === "undefined") {
    var console = {};
    console.log = console.error = function () {
    };
}
