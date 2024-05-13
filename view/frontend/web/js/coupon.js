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

define(['jquery', 'Magento_Checkout/js/action/get-totals', 'Magento_Checkout/js/model/quote', 'uiComponent', 'ko', 'Magento_Ui/js/modal/confirm', 'mage/url'], function ($, getTotalsAction, quote, Component, ko, confirmation, url) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Mageplaza_SplitCart/coupon', couponCode: '', hasCouponCode: false
        }, initialize: function () {
            this._super();
            this.observe('couponCode hasCouponCode');
            $('body').on('loadCoupon', function (e, response) {
                this.couponCode(response.coupon_code);
                this.hasCouponCode(!!response.coupon_code);
            }.bind(this));

            var couponCode = quote.totals() && quote.totals().coupon_code;
            if (couponCode) {
                this.couponCode(couponCode);
                this.hasCouponCode(true);
            }
        },

        applyCouponCode: function () {
            $.ajax({
                url: url.build('checkout/cart/couponPost'), data: {
                    coupon_code: $('#coupon').val()
                }, type: 'POST', dataType: 'json', beforeSend: function () {
                    $('body').trigger('processStart');
                }, success: function (res) {
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                }
            }).always(function () {
                $('body').trigger('processStop');
            });
        }, cancelCouponCode: function () {
            $.ajax({
                url: url.build('checkout/cart/couponPost'), data: {
                    remove: 1
                }, type: 'POST', dataType: 'json', beforeSend: function () {
                    $('body').trigger('processStart');
                }, success: function (res) {
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);
                }
            }).always(function () {
                $('body').trigger('processStop');
            });
        }
    });
});
