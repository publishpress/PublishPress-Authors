jQuery(document).ready(function ($) {

    /**
     * Author index layout alphabet filter
     */
    $(document).on('click', '.author-index-navigation .page-item', function (e) {
        e.preventDefault();
        var current_button = $(this);
        var target_group   = current_button.attr('data-item');
        
        //remove active class from all nav link
        $('.author-index-navigation .page-item').removeClass('active');
        //add active class to current nav link
        current_button.addClass('active');
        //hide all group
        $('.author-index-group').hide();
        //show targetted group
        if (target_group === 'all') {
            $('.author-index-group').show();
        } else {
            $('.author-index-group-' + target_group).show();
        }
    });
});