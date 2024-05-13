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
namespace Mageplaza\SplitCart\Plugin\Magento\Checkout\Block\Cart;

use Mageplaza\SplitCart\Helper\Data;

/**
 * Class Coupon
 * @package Mageplaza\SplitCart\Plugin\Magento\Checkout\Block\Cart
 */
class Coupon
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
     * @param \Magento\Checkout\Block\Cart\Coupon $subject
     * @return void
     */
    public function beforeToHtml(
        \Magento\Checkout\Block\Cart\Coupon $subject
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            $subject->setTemplate('Mageplaza_SplitCart::cart/coupon.phtml');
        }
    }
}
