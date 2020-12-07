jQuery(function ($) {
    // Tabs
    $hiddenFields = $('input[id^="ppma-tab-"]');

    $hiddenFields.each(function () {
        var $this = $(this);
        var $wrapper = $this.next('table');
        $wrapper.attr('id', $this.attr('id'));
        $this.remove();

        if ($wrapper.attr('id') !== 'ppma-tab-general') {
            $wrapper.hide();
        }
    });

    var $tabsWrapper = $('#publishpress-authors-settings-tabs');
    $tabsWrapper.find('li').click(function (e) {
        e.preventDefault();
        $tabsWrapper.children('li').filter('.nav-tab-active').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        var panel = $(this).find('a').first().attr('href');

        $('table[id^="ppma-"]').hide();
        $(panel).show();
    });

    $('.default-authors-select2').ppma_select2({
        placeholder: $(this).data("placeholder"),
        allowClear: true,
        ajax: {
            url:
                window.ajaxurl +
                "?action=authors_search&nonce=" +
                $('.default-authors-select2').data("nonce"),
            dataType: "json",
            data: function(params) {
                var ignored = [];
                $('.default-authors-select2')
                    .closest("div")
                    .find(".authors-list input")
                    .each(function() {
                        ignored.push($(this).val());
                    });
                return {
                    q: params.term,
                    ignored: ignored
                };
            }
        }
    });
});
