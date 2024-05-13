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

namespace Mageplaza\SplitCart\Plugin\Magento\Quote\Model\Cart\Totals;

use Mageplaza\SplitCart\Helper\Data;

/**
 * Class ItemConverter
 * @package Mageplaza\SplitCart\Plugin\Magento\Quote\Model\Cart\Totals
 */
class ItemConverter
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->helper = $data;
    }

    /**
     * @param \Magento\Quote\Model\Cart\Totals\ItemConverter $subject
     * @param $result
     * @param $item
     * @return mixed
     */
    public function afterModelToDataObject(
        \Magento\Quote\Model\Cart\Totals\ItemConverter $subject,
        $result,
        $item
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            $extension = $result->getExtensionAttributes();
            $extension->setAvailableToCheckout($item->getAvailableToCheckout());

            $result->setExtensionAttributes($extension);
        }
        return $result;
    }
}
