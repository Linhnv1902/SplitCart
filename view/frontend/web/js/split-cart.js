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
    'Magento_Checkout/js/action/get-totals',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'mage/translate'
], function ($, getTotalsAction, quote, url) {
    'use strict';

    /**
     * @api
     */
    $.widget('Mageplaza.splitCart', {
        options: {},

        _create: function () {
            $(
                '#shopping-cart-table input.split-cart-check-all[type=checkbox]'
            ).on('change', function () {
                var checked = this.checked,
                    updatedIds = [];

                $('#shopping-cart-table').find('input.split-cart-item-checkbox[type=checkbox]').each(function () {
                    $(this).attr('checked', checked);
                    updatedIds.push($(this).data('item-id'));
                });

                $.ajax({
                    url: url.build('splitcart/cart/updatePost'),
                    data: {
                        item_ids: updatedIds.join(','),
                        action: checked ? 'check' : 'uncheck'
                    },
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        $('body').trigger('processStart');
                    },
                    success: function (res) {
                        if (res.has_error) {
                            $('button[data-role=proceed-to-checkout]').addClass('disabled').attr('disabled', true);
                        } else {
                            $('button[data-role=proceed-to-checkout]').removeClass('disabled').attr('disabled', false);
                        }
                        var deferred = $.Deferred();
                        getTotalsAction([], deferred);
                    }
                }).always(function () {
                    $('body').trigger('processStop');
                });
            });

            $(
                '#shopping-cart-table input.split-cart-item-checkbox[type=checkbox]'
            ).on('change', function () {
                var checked = this.checked,
                    allChecked = true;

                $('#shopping-cart-table').find('input.split-cart-item-checkbox[type=checkbox]').each(function () {
                    if (!this.checked) {
                        allChecked = false;
                    }
                });

                $(
                    '#shopping-cart-table input.split-cart-check-all[type=checkbox]'
                ).attr('checked', allChecked);

                $.ajax({
                    url: url.build('splitcart/cart/updatePost'),
                    data: {
                        item_ids: $(this).data('item-id'),
                        action: checked ? 'check' : 'uncheck'
                    },
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        $('body').trigger('processStart');
                    },
                    success: function (res) {
                        if (res.has_error) {
                            $('button[data-role=proceed-to-checkout]').addClass('disabled').attr('disabled', true);
                        } else {
                            $('button[data-role=proceed-to-checkout]').removeClass('disabled').attr('disabled', false);
                        }
                        var deferred = $.Deferred();
                        getTotalsAction([], deferred);
                    }
                }).always(function () {
                    $('body').trigger('processStop');
                });
            });
        }
    });

    return $.Mageplaza.splitCart;
});
