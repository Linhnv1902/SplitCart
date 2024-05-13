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

namespace Mageplaza\SplitCart\Plugin\Magento\Checkout\Block\Onepage;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SplitCart\Helper\Data;

/**
 * Class Link
 * @package Mageplaza\SplitCart\Plugin\Magento\Checkout\Block\Onepage
 */
class Link
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @param Data $data
     * @param Session $checkoutSession
     */
    public function __construct(
        Data $data,
        Session $checkoutSession
    ) {
        $this->helper = $data;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * @param \Magento\Checkout\Block\Onepage\Link $subject
     * @param $result
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterIsDisabled(
        \Magento\Checkout\Block\Onepage\Link $subject,
        $result
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($result && !$enableSplitCart) {
            return $result;
        }

        $cartQuote = $this->_checkoutSession->getQuote();
        $shippingAddressItems = $cartQuote->getShippingAddress()->getAllItems();

        return $cartQuote->getHasError() || !count($shippingAddressItems);
    }
}
