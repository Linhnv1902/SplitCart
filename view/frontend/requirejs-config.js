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

var config = {
    map: {
        '*': {
            splitCart: 'Mageplaza_SplitCart/js/split-cart',
            'Magento_Checkout/js/action/get-totals': 'Mageplaza_SplitCart/js/action/get-totals'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/cart-items': {
                'Mageplaza_SplitCart/js/view/summary/cart-items-mixin': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Mageplaza_SplitCart/js/catalog-add-to-cart-mixin': true
            }
        }
    }
};
