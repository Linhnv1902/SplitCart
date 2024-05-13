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
namespace Mageplaza\SplitCart\Plugin\Magento\Framework\View\Element;

use Mageplaza\SplitCart\Helper\Data as Helper;

/**
 * Class QuantityValidator
 * @package Mageplaza\SplitCart\Plugin\Model
 */
class Template
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
     * @param \Magento\Framework\View\Element\Template $subject
     * @param $result
     * @return mixed|string
     */
    public function aftergetTemplate(
        \Magento\Framework\View\Element\Template $subject,
        $result
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$enableSplitCart) {
            if ($result == "Mageplaza_SplitCart::js.phtml") {
                return "";
            }
        }
        return $result;
    }
}
