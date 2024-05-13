<?php


namespace Mageplaza\SplitCart\Plugin\Magento\Quote\Model\Quote;

use Closure;
use Magento\Quote\Model\Quote;
use Mageplaza\SplitCart\Helper\Data;

/**
 * Class Address
 * @package Mageplaza\SplitCart\Plugin\Magento\Quote\Model\Quote
 */
class Address
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
     * @param Quote\Address $subject
     * @param Closure $proceed
     * @return array
     */
    public function aroundGetAllItems(
        Quote\Address $subject,
        Closure $proceed
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        $items = $proceed();

        if ($subject->getQuote()->getIsUpdatingQty() || !$enableSplitCart) {
            return $items;
        }

        $availableItems = [];
        foreach ($items as $item) {
            if (!$item->getId() || $item->getAvailableToCheckout()) {
                $availableItems[] = $item;
            }
        }

        return $availableItems;
    }
}
