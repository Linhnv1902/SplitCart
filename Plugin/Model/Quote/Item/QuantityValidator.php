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

namespace Mageplaza\SplitCart\Plugin\Model\Quote\Item;

use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\SplitCart\Helper\Data as Helper;

/**
 * Class QuantityValidator
 * @package Mageplaza\SplitCart\Plugin\Model
 */
class QuantityValidator
{
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
        Helper $data
    ) {
        $this->registry = $registry;
        $this->helper = $data;
    }

    /**
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject
     * @param $proceed
     * @param Observer $observer
     * @return mixed
     */
    public function aroundValidate(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject,
        $proceed,
        Observer $observer
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$enableSplitCart) {
            return $proceed($observer);
        }
        /** @var $quoteItem Item */
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem->getAvailableToCheckout()) {
            return;
        }

        $results = $proceed($observer);

        if ($quoteItem instanceof Item && $quoteItem->getHasError()) {
            $quoteItem->setAvailableToCheckout(0);

            $parentItem = $quoteItem->getParentItem();
            if ($parentItem && $parentItem->getId()) {
                $parentItem->setAvailableToCheckout(0);
            }

            $itemChildren = $quoteItem->getChildren();
            if ($itemChildren) {
                foreach ($itemChildren as $itemChild) {
                    $itemChild->setAvailableToCheckout(0);
                }
            }
        }

        return $results;
    }

    /**
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject
     * @param $result
     * @param Observer $observer
     * @return mixed
     */
    public function afterValidate(
        \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject,
        $result,
        Observer $observer
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            /** @var $quoteItem Item */
            $quoteItem = $observer->getEvent()->getItem();
            if ($this->registry->registry('split_cart_adding_remaining_items_to_cart')) {
                $quoteItem->setHasError(false);
            }
        }

        return $result;
    }
}
