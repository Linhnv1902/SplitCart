define([
    'jquery',
    'mage/translate',
    'jquery/ui',
], function ($, $t,alert) {
    'use strict';

    return function (widget) {
        console.log('catalog-add-to-cart-mixin');

        $.widget('mage.catalogAddToCart', widget, {
            disableAddToCartButton: function(form) {
                var action = form.attr('action');
                if (action && action.indexOf('buynow') !== -1) {
                    var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Buy' +
                            ' Now...'),
                        addToCartButton = $(form).find('.action.buynow');

                    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                    addToCartButton.find('span').text(addToCartButtonTextWhileAdding);
                    addToCartButton.prop('title', addToCartButtonTextWhileAdding);
                } else {
                    this._super(form);
                }
            }
        });

        return $.mage.catalogAddToCart;
    }
});
