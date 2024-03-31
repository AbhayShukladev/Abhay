define(
    [
        'Abhay_CustomerDiscount/js/view/checkout/summary/custom-discount'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return true;
            }
        });
    }
);
