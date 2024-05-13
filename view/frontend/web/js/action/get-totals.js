/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SplitCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'mage/storage',
    'Magento_Checkout/js/model/totals'
], function ($, quote, resourceUrlManager, errorProcessor, storage, totals) {
    'use strict';

    return function (callbacks, deferred) {
        deferred = deferred || $.Deferred();
        totals.isLoading(true);

        return storage.get(
            resourceUrlManager.getUrlForCartTotals(quote),
            false
        ).done(function (response) {
            var proceed = true;

            totals.isLoading(false);

            if (callbacks.length > 0) {
                $.each(callbacks, function (index, callback) {
                    proceed = proceed && callback();
                });
            }

            if (proceed) {
                quote.setTotals(response);
                deferred.resolve();
            }
        }).fail(function (response) {
            totals.isLoading(false);
            deferred.reject();
            errorProcessor.process(response);
        }).always(function (response) {
            totals.isLoading(false);
            $('body').trigger('loadCoupon', response);
        });
    };
});
