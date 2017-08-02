jQuery(document).ready(function () {

    jQuery("form#halal_form").submit(function () {

        var formData = new FormData(this);
        jQuery("form#halal_form .form-error").html('').hide();


        jQuery.ajax({
            url: '/wp-admin/admin-post.php?action=contact_form',
            type: 'POST',
            data: formData,
            async: false,
            success: function (data) {
                var data = typeof data == 'string' ? jQuery.parseJSON(data) : data;
                if (data.result === 'success') {
                    jQuery('form#halal_form').trigger("reset");
                    alert('certificate form sent successfuly');
                }
                else {
                    for (var key in data.text_error) {
                        jQuery('#' + key + '_error').html('<div style="color:red">' +data.text_error[key] + '</div>').show();
                    }

                    jQuery('form#halal_form *').filter(':input').each(function(){

                        if (jQuery('#' + jQuery(this).attr( "name" ) + '_error').html() === '') {
                            jQuery(this).addClass("form-error::before");
                        }
                        else {
                            jQuery(this).addClass("form-error::after");
                        }
                    });

                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
        return false;
    });
});