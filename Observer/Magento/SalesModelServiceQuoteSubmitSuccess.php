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

namespace Mageplaza\SplitCart\Observer\Magento;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Mageplaza\SplitCart\Helper\Data;
use Mageplaza\SplitCart\Logger\Logger;
use Mageplaza\SplitCart\Model\Session as SplitCartSession;

/**
 * Class SalesModelServiceQuoteSubmitSuccess
 * @package Mageplaza\SplitCart\Observer\Magento
 */
class SalesModelServiceQuoteSubmitSuccess implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartItemInterface[]
     */
    private $items = [];

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CartItemInterfaceFactory
     */
    private $cartItemFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SplitCartSession
     */
    private $splitCartSession;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CartItemInterfaceFactory $cartItemFactory
     * @param CartManagementInterface $cartManagement
     * @param GuestCartManagementInterface $guestCartManagement
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Registry $registry
     * @param CollectionFactory $productCollectionFactory
     * @param Logger $logger
     * @param Data $data
     * @param SplitCartSession $splitCartSession
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CartItemInterfaceFactory $cartItemFactory,
        CartManagementInterface $cartManagement,
        GuestCartManagementInterface $guestCartManagement,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Registry $registry,
        CollectionFactory $productCollectionFactory,
        Logger $logger,
        Data $data,
        SplitCartSession $splitCartSession
    ) {
        $this->cartItemFactory = $cartItemFactory;
        $this->cartManagement = $cartManagement;
        $this->guestCartManagement = $guestCartManagement;
        $this->cartRepository = $cartRepository;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->registry = $registry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->logger = $logger;
        $this->helper = $data;
        $this->splitCartSession = $splitCartSession;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function execute(
        Observer $observer
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if ($enableSplitCart) {
            try {
                /** @var Quote $quote */
                $quote = $observer->getEvent()->getQuote();

                $remainingItems = [];

                foreach ($quote->getAllItems() as $item) {
                    if (!$item->getAvailableToCheckout()) {
                        $remainingItems[] = $item;
                    }
                }

                if ($remainingItems) {
                    // Save current quote
                    $quote->setIsActive(false);
                    $this->cartRepository->save($quote);

                    $customerId = $quote->getCustomerId();

                    if ($customerId) {
                        $cartId = $this->cartManagement->createEmptyCartForCustomer($customerId);
                        $cart = $this->cartRepository->get($cartId);
                    } else {
                        $quoteMaskedId = $this->guestCartManagement->createEmptyCart();

                        $quoteIdMask = $this->quoteIdMaskFactory->create();
                        $quoteIdMask->load($quoteMaskedId, 'masked_id');
                        $cartId = $quoteIdMask->getQuoteId();
                        $this->checkoutSession->setQuoteId($cartId);
                        $cart = $this->checkoutSession->getQuote();

                        $this->splitCartSession->setSplitQuoteId($cart->getId());
                    }
                    // Bypass cart and item error check for adding remaining cart items to cart
                    $this->registry->register('split_cart_adding_remaining_items_to_cart', true);
                    $this->addItemsToCart($remainingItems);

                    $cart->setItems($this->items);
                    $this->cartRepository->save($cart);
                    if ($cart instanceof DataObject) {
                        $cart->setData('totals_collected_flag', false);
                    }
                }
            } catch (Exception $e) {
                $this->logger->debug(__($e->getMessage()));
            }
        }
    }

    /**
     * Add collections of order items to cart.
     *
     * @param ItemCollection $orderItems
     * @return void
     * @throws LocalizedException
     */
    private function addItemsToCart($orderItems): void
    {
        $orderItemProductIds = [];
        $orderItemsByProductId = $orderItems;

        foreach ($orderItems as $item) {
            if (!$item->getAvailableToCheckout()) {
                if ($item->getParentItem() === null) {
                    $orderItemProductIds[] = $item->getProductId();
                    $orderItemsByProductId[$item->getProductId()][$item->getId()] = $item;
                }
            }
        }

        $products = $this->getOrderProducts($orderItemProductIds);

        // compare founded products and throw an error if some product not exists
        $productsNotFound = array_diff($orderItemProductIds, array_keys($products));
        if (!empty($productsNotFound)) {
            foreach ($productsNotFound as $productId) {
                $this->logger->debug(__('Could not find a product with ID "%1"', $productId));
            }
        }

        foreach ($orderItems as $item) {
            if (!isset($products[$item->getProductId()])) {
                continue;
            }
            $product = $products[$item->getProductId()];
            if (!$product) {
                $this->logger->debug(__('Could not find a product with ID "%1"', $productId));
            }
            $this->addItemToCart($item, $product);
        }
    }

    /**
     * Get order products by store id and order item product ids.
     *
     * @param int[] $orderItemProductIds
     * @return array
     * @throws LocalizedException
     */
    private function getOrderProducts(array $orderItemProductIds): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($orderItemProductIds)
            ->addStoreFilter()
            ->addAttributeToSelect('*')
            ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner')
            ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner')
            ->addOptionsToResult();

        return $collection->getItems();
    }

    /**
     * Adds order item product to cart.
     *
     * @param CartItemInterface $orderItem
     * @param $product
     * @return SalesModelServiceQuoteSubmitSuccess
     */
    private function addItemToCart($orderItem, $product)
    {
        /** @var CartItemInterface $cartItem */
        $cartItem = $this->cartItemFactory->create();
        $cartItem->setSku($product->getSku());
        $cartItem->setName($orderItem->getName());
        $cartItem->setQty($orderItem->getQty());
        $cartItem->setPrice($orderItem->getPrice());
        $cartItem->setAvailableToCheckout(1);
        $cartItem->setProductType($orderItem->getProductType());

        if ($orderItem->getProductOption()) {
            $cartItem->setProductOption($orderItem->getProductOption());
        }

        $this->items[] = $cartItem;
        return $this;
    }
}
