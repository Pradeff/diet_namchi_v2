// Use this wrapper to ensure jQuery is ready
(function($) {
    $(document).ready(function() {
        // Handle all form submissions with id="form"
        $('form[id="form"]').on('submit', function(e) {
            var $form = $(this);
            var $submitBtn = $form.find('#submitBtn');
            var $submitText = $form.find('#submitText');
            var $spinner = $form.find('#submitSpinner');

            // Store original text if not already stored
            if (!$submitBtn.data('original-text')) {
                $submitBtn.data('original-text', $submitText.text());
            }

            // Determine if this is an edit form
            var isEdit = $submitBtn.data('original-text').toLowerCase().includes('update');

            // Update button state
            $submitBtn.prop('disabled', true);
            $submitText.text(isEdit ? 'Updating...' : 'Saving...');
            $spinner.removeClass('d-none');
        });

        // Reset button state if page is shown from cache
        $(window).on('pageshow', function(event) {
            if (event.originalEvent.persisted) {
                $('#submitBtn').each(function() {
                    var $btn = $(this);
                    var originalText = $btn.data('original-text');
                    if (originalText) {
                        $btn.prop('disabled', false)
                            .find('#submitText').text(originalText)
                            .end()
                            .find('#submitSpinner').addClass('d-none');
                    }
                });
            }
        });
    });
})(jQuery);
