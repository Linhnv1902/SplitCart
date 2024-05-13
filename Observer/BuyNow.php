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
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SplitCart\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Class QuoteSubmitSuccess
 * @package Mageplaza\Osc\Observer
 */
class BuyNow implements ObserverInterface
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * Constructor
     *
     * @param RedirectInterface $redirect
     */
    public function __construct(
        RedirectInterface $redirect,
        Cart $cart
    ) {
        $this->redirect = $redirect;
        $this->cart = $cart;

    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote $quote */
        $product =$observer->getEvent()->getProduct();
        if ($product->getPattern()){
            $this->updateItemAvailableToCheckout($product->getPattern());
        }else{
            $this->updateItemAvailableToCheckout($product->getId());
        }

    }

    /**
     * @param $productId
     *
     * @return void
     * @throws \Exception
     */
    private function updateItemAvailableToCheckout($productId)
    {
        $cart = $this->cart->getQuote();
        foreach ($cart->getItems() as $item) {
            if($item->getParentItemId()){
                continue;
            }else {
                $item->setAvailableToCheckout(1);
                if ($item->getProductId() !== $productId) {
                    $item->setAvailableToCheckout(0);
                }
            }
        }
         $cart->save();
    }

}
