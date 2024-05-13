<?php
namespace Mageplaza\SplitCart\Plugin\Magento\Checkout\Block\Cart\Item;

use Mageplaza\SplitCart\Helper\Data;

/**
 * Class Renderer
 * @package Mageplaza\SplitCart\Plugin\Magento\Checkout\Block\Cart\Item
 */
class Renderer
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
     * @param \Magento\Checkout\Block\Cart\Item\Renderer $subject
     */
    public function beforeToHtml(
        \Magento\Checkout\Block\Cart\Item\Renderer $subject
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            $subject->setTemplate('Mageplaza_SplitCart::cart/item/default.phtml');
        }
    }
}
