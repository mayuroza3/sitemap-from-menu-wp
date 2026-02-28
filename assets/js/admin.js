jQuery(document).ready(function ($) {
    const $form = $('#sfm-settings-form');
    const $iframe = $('#sfm-preview-frame');

    if (!$form.length || !$iframe.length) return;

    $form.on('change input', 'input, select', debounce(function () {
        updatePreview();
    }, 500));

    function updatePreview() {
        $.ajax({
            url: sfm_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'sfm_render_preview',
                nonce: sfm_admin.nonce,
                form_data: $form.serialize()
            },
            success: function (response) {
                if (response.success && response.data) {
                    $iframe[0].contentWindow.postMessage({ sfm_html: response.data }, '*');
                }
            }
        });
    }

    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                func.apply(context, args);
            }, wait);
        };
    }
});
