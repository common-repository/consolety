function consolety_start_export(offset, total, skipped) {

    var data = {
        'action': 'consolety_export',
        'date': jQuery('#consolety_datepicker').val(),
        'offset': offset,
        'total': total,
        'skipped': skipped
    };

    console.log(data);
    jQuery.post(ajaxurl, data, function (response) {
        console.log(response);
        var r = jQuery.parseJSON(response);
        console.log(r);
        jQuery('#consolety_progress_block').show();
        jQuery('.consolety_bar_progress,#consolety_progressbar,.consolety_posts_skipped').show();
        jQuery('#consolety_done,#consolety_error_export').hide();
        var bar = jQuery('#consolety_progressbar');
        bar.attr('value', r.offset);
        bar.attr('max', r.total);
        jQuery('#consolety_exported_val').text(r.offset);
        jQuery('#consolety_exported_total').text(r.total);
        if (r.offset > 0 && r.total === 0) {
            jQuery('#consolety_error_export').text('Starting from the selected date, there are no available posts available for export! To use our system, you need to export at least one post.').show();
        } else if (r.error !== null) {
            jQuery('#consolety_progress_block').hide();
            jQuery('#consolety_error_export').show().text(r.message);
            return false;
        }
        console.log('total:' + r.total);
        console.log('offset:' + r.offset);
        console.log('skipped:' + r.skipped);

        if (r.offset < r.total) {
            consolety_start_export(r.offset, r.total, r.skipped);
        } else {

            jQuery('.consolety_bar_progress').hide();
            jQuery('.next-step').show();
            jQuery('#consolety_done').show();
        }
    });
}

jQuery(document).ready(function ($) {
    $('.consolety-styles-color').wpColorPicker();
    $('#consolety-select-all').click(function (event) {
        if (this.checked) {
            jQuery('.consolety-categories-box').hide();
        } else {
            jQuery('.consolety-categories-box').show();
        }
    });


});
jQuery('#tabs').ready(function ($) {
    var h = window.location.hash.split('#')[1];
    switch (h) {
        case "details":
        case "links":
        case "main":
        case "design":
            jQuery('.consolety .tabs .tab-' + h).prop('checked', true);
            break;
    }
});


function flush_my_posts_at_consolety() {


    var data = {
        'action': 'flush_consolety'
    };

    jQuery.post(ajaxurl, data, function (data) {
        alert('Finished!');
    });
}


function save_settings(posts_export = false) {
    var categories = false;
    var post_types = false;
    var data = {
        'action': 'save_categories',
        'categories[]': [],
        'consolety_post_types[]': [],
        'consolety_no_sync': jQuery("#consolety_no_sync").prop('checked'),
        'all_categories': false
    };
    if (jQuery('#consolety-select-all').prop('checked') === false) {
        jQuery("input[name='consolety_seo_categories[]']:checked").each(function () {
            data['categories[]'].push(jQuery(this).val());
            categories = true;
        });
    } else {
        data['all_categories'] = true;
        categories = true;
    }

    jQuery("input[name='consolety_post_types[]']:checked").each(function () {
        data['consolety_post_types[]'].push(jQuery(this).val());
        post_types = true;
    });
    if (!categories) {
        alert('Please select at least 1 category');
        return false;
    }
    if (!post_types) {
        alert('Please select at least 1 Post type');
        return false;
    }
    console.log(data);
    jQuery.post(ajaxurl, data, function (data) {
        if (posts_export === true && data) {
            consolety_start_export(0, 0);
        }
    });
    set_notification('success','Settings were updated',10000);
}

function export_posts() {
    save_settings(true);
}

function save_settings_skip() {
    save_settings();
    location.href = location.href + '&step=3'
}

function set_notification(status, message, duration) {
    var div = jQuery('<div class="notice notice-' + status + ' is-dismissible">' +
        '<p>' + message + '</p>' +
        '</div>');
    jQuery('#consolety-notification-block').append(div);
    div.show(1).delay(duration).hide(1, function () {
        jQuery(this).remove();
    });
}
function export_post(post_id){
    var data = {
        'action': 'consolety_export_single',
        'post_id': post_id
    };

    console.log(data);
    jQuery.post(ajaxurl, data, function (response) {
        var r = jQuery.parseJSON(response);
        console.log(r);
        if(!r.error){
            alert('Post exported successfully.');
        }else{
            alert('Post export error: '+r.message)
        }

    });
}

