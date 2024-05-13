<?php
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
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SplitCart
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SplitCart\Plugin\Magento\Quote\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\SplitCart\Helper\Data;

/**
 * Class Quote
 * @package TMageplaza\SplitCart\Plugin\Magento\Quote\Model
 */
class Quote
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * QuantityValidator constructor.
     *
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry,
        Data $data
    ) {
        $this->registry = $registry;
        $this->helper = $data;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param $result
     * @return bool|null
     */
    public function afterHasItems(
        \Magento\Quote\Model\Quote $subject,
        $result
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$result || !$enableSplitCart) {
            return $result;
        }

        $shippingAddressItems = $subject->getShippingAddress()->getAllItems();

        return count($shippingAddressItems) > 0;
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param $proceed
     * @param $type
     * @param $origin
     * @param $code
     * @param $message
     * @param $additionalData
     * @return mixed|void
     */
    public function aroundAddErrorInfo(
        \Magento\Quote\Model\Quote $subject,
        $proceed,
        $type = 'error',
        $origin = null,
        $code = null,
        $message = null,
        $additionalData = null
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            $currentQuoteItem = $this->registry->registry('split_cart_error_check_current_quote_item');
            if ($currentQuoteItem && !$currentQuoteItem->getAvailableToCheckout()) {
                return $subject;
            }
        }

        return $proceed($type, $origin, $code, $message, $additionalData);
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param Product $product
     * @param $processMode
     * @return void
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject,
        Product $product,
        $processMode
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            $isRemainingItems = $this->registry->registry('split_cart_adding_remaining_items_to_cart');
            if ($isRemainingItems) {
                $product->setIsSalable(true);
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote $subject
     * @param $result
     * @return bool|mixed
     */
    public function afterIsVirtual(
        \Magento\Quote\Model\Quote $subject,
        $result
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$result && $enableSplitCart) {
            $isVirtual = true;
            $countItems = 0;
            foreach ($subject->getItemsCollection() as $_item) {
                /* @var $_item Item */
                if ($_item->isDeleted() || $_item->getParentItemId()) {
                    continue;
                }
                $countItems++;
                if (!$_item->getProduct()->getIsVirtual() && $_item->getData('available_to_checkout') == 1) {
                    $isVirtual = false;
                    break;
                }
            }

            return $countItems == 0 ? $result : $isVirtual;
        }

        return $result;
    }
}
