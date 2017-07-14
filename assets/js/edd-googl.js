jQuery(document).ready(function ($) {
    $('#edd-googl-update-all-button').click(function() {
        var $this = $(this);

        $this.prop('disabled', true);
        $this.text('Updating...');
        $('<span class="spinner is-active" style="float: none; margin-top: 0;"></span>').insertAfter($this);

        $.ajax({
            url: ajaxurl,
            data: {
                action: 'edd_googl_update_all'
            },
            success: function() {
                $this.prop('disabled', false);
                $this.text('All Shortlinks Updated!');
                $this.next('.spinner').remove();
            },
            error: function() {
                $this.prop('disabled', false);
                $this.text('Something Failed!');
                $this.next('.spinner').remove();
            }
        });
    });
});