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

namespace Mageplaza\SplitCart\Plugin\Magento\Checkout\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\SplitCart\Helper\Data;
use Mageplaza\SplitCart\Model\Session as SplitCartSession;

/**
 * Class Session
 * @package Mageplaza\SplitCart\Plugin\Magento\Checkout\Model
 */
class Session
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SplitCartSession
     */
    private $splitCartSession;

    /**
     * @param Data $helper
     * @param SplitCartSession $splitCartSession
     */
    public function __construct(
        Data $helper,
        SplitCartSession $splitCartSession
    ) {
        $this->helper = $helper;
        $this->splitCartSession = $splitCartSession;
    }

    /**
     * @param \Magento\Checkout\Model\Session $subject
     * @param $process
     * @return mixed|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundClearQuote(
        \Magento\Checkout\Model\Session $subject,
        $process
    ) {
        $isClearSplitOrder = $subject->getQuote()->getId() === $this->splitCartSession->getSplitQuoteId();
        if ($this->helper->isEnabledSplitCart() && $isClearSplitOrder) {
            return;
        }

        return $process();
    }
}
