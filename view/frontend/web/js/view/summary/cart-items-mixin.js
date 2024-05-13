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

define(['underscore'], function (_) {
    'use strict';

    var mixin = {
        /**
         * Returns cart items qty
         *
         * @returns {Number}
         */
        getItemsQty: function () {
            var items = this.items(),
                itemsQty = 0;

            if (items && items.length > 0) {
                _.each(
                    items,
                    function (item) {
                        itemsQty += item.qty;
                    }.bind(this)
                );
            }

            return parseFloat(itemsQty);
        },

        /**
         * Returns count of cart line items
         *
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            return parseInt(this.items().length, 10);
        },

        /**
         * Set items to observable field
         *
         * @param {Object} items
         */
        setItems: function (items) {
            var newItems = [];

            if (items && items.length > 0) {
                _.each(
                    items,
                    function (item) {
                        if (item.extension_attributes && item.extension_attributes.available_to_checkout === 1) {
                            newItems.push(item);
                        }
                    }.bind(this)
                );
                newItems = newItems.slice(
                    parseInt(-this.maxCartItemsToDisplay, 10)
                );
            }

            this.items(newItems);
        }
    };

    return function (target) {
        var enableSplitCart = window.checkoutConfig.quoteData.enableSplitCart;
        if (!enableSplitCart) {
            return target;
        }
        return target.extend(mixin);
    };
});
