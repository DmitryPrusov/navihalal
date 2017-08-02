jQuery(document).ready(function () {

    jQuery("form#status_form").submit(function (event) {
        event.preventDefault();
        var formData = jQuery(this).serialize();

        jQuery.ajax({
            url: '/wp-admin/admin-post.php?action=status_form',
            type: 'POST',
            data: formData,
            dataType: "json",
            success: function (data) {

                if (data.result === 'success') {
                    alert('Status changed successfully!');
                }
                else {
                    alert('Mistake: nothing changed in database');
                    }
                }
            });
        return false;
    });
});