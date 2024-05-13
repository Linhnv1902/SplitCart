<?php

namespace Mageplaza\SplitCart\Plugin\Magento\Quote\Model\Quote;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Mageplaza\SplitCart\Helper\Data;

/**
 * Class Item
 * @package Mageplaza\SplitCart\Plugin\Magento\Quote\Model\Quote
 */
class Item
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @param Registry $registry
     * @param HttpContext $httpContext
     * @param Manager $moduleManager
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        Data $data,
        Manager $moduleManager,
        Context $context
    ) {
        $this->registry = $registry;
        $this->helper = $data;
        $this->moduleManager = $moduleManager;
        $this->_request = $context->getRequest();
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @return void
     */
    public function beforeCheckData(
        \Magento\Quote\Model\Quote\Item $subject
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            if ($this->registry->registry('split_cart_error_check_current_quote_item')) {
                $this->registry->unregister('split_cart_error_check_current_quote_item');
            }
            $this->registry->register('split_cart_error_check_current_quote_item', $subject);
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param $quoteItem
     * @return mixed
     */
    public function afterCheckData(
        \Magento\Quote\Model\Quote\Item $subject,
        $quoteItem
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            $referrer = $this->_request->getHeader('referer');
            if ($referrer && strpos($referrer,
                    'repayment') !== false && $this->moduleManager->isEnabled('Mageplaza_Repayment')) {
                return $quoteItem;
            }
            if ($this->registry->registry('split_cart_adding_remaining_items_to_cart')) {
                $quoteItem->setHasError(false);
            }

            if (!$quoteItem->getAvailableToCheckout()) {
                return $quoteItem;
            }

            if ($quoteItem->getHasError()) {
                $quoteItem->setAvailableToCheckout(0);

                $parentItem = $quoteItem->getParentItem();
                if ($parentItem && $quoteItem->getId()) {
                    $parentItem->setAvailableToCheckout(0);
                }

                $itemChildren = $subject->getChildren();
                if ($itemChildren) {
                    foreach ($itemChildren as $itemChild) {
                        $itemChild->setAvailableToCheckout(0);
                    }
                }
            }
        }

        return $quoteItem;
    }
}
