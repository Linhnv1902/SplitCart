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

use Mageplaza\SplitCart\Helper\Data as Helper;

/**
 * Class DefaultConfigProvider
 * @package Mageplaza\SplitCart\Plugin\Magento\Checkout\Model
 */
class DefaultConfigProvider
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Helper $data
     */
    public function __construct(
        Helper $data
    ) {
        $this->helper = $data;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param $result
     * @return mixed
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        if ($result) {
            $enableSplitCart = $this->helper->isEnabledSplitCart();
            $result["quoteData"]['enableSplitCart'] = $enableSplitCart;
        }

        return $result;
    }
}
