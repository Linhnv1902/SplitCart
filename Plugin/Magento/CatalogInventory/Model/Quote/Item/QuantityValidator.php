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

namespace Mageplaza\SplitCart\Plugin\Magento\CatalogInventory\Model\Quote\Item;

use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Item;
use Mageplaza\SplitCart\Helper\Data as Helper;

/**
 * Class QuantityValidator
 * @package Mageplaza\SplitCart\Plugin\Magento\CatalogInventory\Model\Quote
 */
class QuantityValidator
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Registry $registry
     * @param Helper $data
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
