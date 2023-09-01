(function ($) {
  "use strict";

  jQuery(document).ready(function ($) {
    $("body.post-type-ppmacf_field .wrap table #the-list").sortable({
      items: "> tr",
      cursor: "move",
      axis: "y",
      update: function (event, ui) {
        $('.author-fields-order-response').remove();
        
        var posts_order = $(this).sortable("toArray");
        //prepare ajax data
        var data = {
          action: "author_custom_fields_save_order",
          posts_order: posts_order,
          nonce: authorCustomFields.nonce,
        };
        $.post(ajaxurl, data, function (response) {
          var status = response.status;
          var status_message = response.content;
          $("ul.subsubsub").before(
            '<div class="author-fields-order-response is-dismissible notice notice-' +
              status +
              '"><p> ' +
              status_message +
              " </p></div>"
          );
        });
      },
      sort: function (e, ui) {
          // Fix the issue with the width of the line while draging, due to the hidden column
          ui.placeholder.find('td').each(function (key, value) {
              if (ui.helper.find('td').eq(key).is(':visible')) $(this).show();
              else $(this).hide();
          });
      }
    });
  });
})(jQuery);
