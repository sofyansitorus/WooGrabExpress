(function ($) {

    "use strict";

    $(document).ready(function () {

        // Try show settings modal on settings page.
        if (woograbexpress_params.show_settings) {
            setTimeout(function () {
                var wooGrabExpressAdded = false;
                var methods = $(document).find('.wc-shipping-zone-method-type');
                for (var i = 0; i < methods.length; i++) {
                    var method = methods[i];
                    if ($(method).text() == 'WooGrabExpress') {
                        $(method).closest('tr').find('.row-actions .wc-shipping-zone-method-settings').trigger('click');
                        wooGrabExpressAdded = true;
                        return;
                    }
                }
                if (!wooGrabExpressAdded) {
                    $(document).find('.wc-shipping-zone-add-method').trigger('click');
                }
            }, 300);
        }
    });

})(jQuery);