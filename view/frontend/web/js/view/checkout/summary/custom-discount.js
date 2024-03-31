define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/totals',
    'Magento_Customer/js/model/customer',
    'Magento_Catalog/js/price-utils',
    'mage/url'
], function ($, Component, quote, totals, customer, priceUtils, urlBuilder) {
    "use strict";

    // Declare global variable to hold the formatted price
    var customDiscountFormattedPrice = null;

    return Component.extend({
        defaults: {
            template: 'Abhay_CustomerDiscount/checkout/summary/custom-discount'
        },
        totals: quote.getTotals(),
        isDisplayedCustomdiscountTotal: function () {
            return true;
        },
        getCustomdiscountTotal: function () {
            var self = this;

            // Get the base URL
            var baseUrl = urlBuilder.build('');

            // Make AJAX request to fetch the custom discount value from cache
            $.ajax({
                url: baseUrl + 'abhay_customerdiscount/index/getCustomDiscount',
                type: 'POST',
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    if (response.custom_discount !== null) {
                        console.log(response.custom_type);
                        console.log(response);
                        if (response.custom_type == 'percentage') {
                            // Format as percentage
                            customDiscountFormattedPrice = response.custom_discount+' %';
                        } else {
                            // Format as fixed amount
                            customDiscountFormattedPrice = priceUtils.formatPrice(response.custom_discount, quote.getPriceFormat());
                        }
                    } else {
                        // Custom discount value not found in cache
                        console.log('Custom discount value not found in cache');
                        customDiscountFormattedPrice = null; // or any default value if needed
                    }
                },
                error: function () {
                    console.log('Error occurred while fetching custom discount value from cache');
                    customDiscountFormattedPrice = null; // or any default value if needed
                },
                async: false // Synchronous AJAX request to ensure the formatted price is set before returning
            });

            // Return the formatted price from the global variable
            return customDiscountFormattedPrice;
        }
    });
});
