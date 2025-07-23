jQuery(document).ready(function($) {
    // Handle dismissible remote notices
    $(document).on('click', '.wizewpph-remote-notice .notice-dismiss', function () {
        const noticeId = $(this).closest('.wizewpph-remote-notice').data('notice-id');
        const nonce = wizemamo_admin_data.nonce; // passed from PHP via wp_localize_script

        $.post(ajaxurl, {
            action: 'wizewpph_dismiss_notice',
            notice_id: noticeId,
            _ajax_nonce: nonce
        });
    });
});
