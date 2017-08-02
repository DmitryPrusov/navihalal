jQuery(document).ready(function () {

    jQuery("form#contact_us").submit(function () {

        var formData = new FormData(this);
        jQuery("form#contact_us .error").hide();

        jQuery.ajax({
            url: '/wp-admin/admin-post.php?action=contact_us',
            type: 'POST',
            data: formData,
            async: false,
            success: function (data) {
                var data = typeof data == 'string' ? jQuery.parseJSON(data) : data;
                if (data.result === 'success') {
                    jQuery('form#contact_us').trigger("reset");
                    alert('Request sent successfuly, we will contact with you soon!');
                }
                else {
                    for (var key in data.text_error) {
                        jQuery('#' + key + '_error').html('<div style="color:red">' +data.text_error[key] + '</div>').show();
                    }
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
        return false;
    });
});