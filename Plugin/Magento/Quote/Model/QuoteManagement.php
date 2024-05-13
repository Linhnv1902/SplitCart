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

namespace Mageplaza\SplitCart\Plugin\Magento\Quote\Model;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\SplitCart\Helper\Data;

/**
 * Class QuoteManagement
 * @package Mageplaza\SplitCart\Plugin\Magento\Quote\Model
 */
class QuoteManagement
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ToOrderItemConverter
     */
    private $quoteItemToOrderItem;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param CartRepositoryInterface $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param QuoteFactory $quoteFactory
     * @param Data $data
     */
    public function __construct(
        ToOrderItemConverter $quoteItemToOrderItem,
        CartRepositoryInterface $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory,
        Data $data
    ) {
        $this->helper = $data;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param $proceed
     * @param $customerId
     * @return int
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function aroundCreateEmptyCartForCustomer(
        \Magento\Quote\Model\QuoteManagement $subject,
        $proceed,
        $customerId
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$enableSplitCart) {
            return $proceed($customerId);
        }
        $storeId = $this->storeManager->getStore()->getStoreId();
        $quote = $this->createCustomerCart($customerId, $storeId);

        try {
            $this->quoteRepository->save($quote);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__("The quote can't be created."));
        }

        return (int)$quote->getId();
    }

    /**
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param $proceed
     * @param QuoteEntity $quote
     * @return array
     */
    protected function aroundResolveItems(
        \Magento\Quote\Model\QuoteManagement $subject,
        $proceed,
        QuoteEntity $quote
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$enableSplitCart) {
            return $proceed($quote);
        }
        $orderItems = [];
        foreach ($quote->getAllItems() as $quoteItem) {
            $itemId = $quoteItem->getId();

            if (!empty($orderItems[$itemId]) || !$quoteItem->getAvailableToCheckout()) {
                continue;
            }

            $parentItemId = $quoteItem->getParentItemId();
            /** @var Item $parentItem */
            if ($parentItemId && !isset($orderItems[$parentItemId])) {
                $orderItems[$parentItemId] = $this->quoteItemToOrderItem->convert(
                    $quoteItem->getParentItem(),
                    ['parent_item' => null]
                );
            }
            $parentItem = isset($orderItems[$parentItemId]) ? $orderItems[$parentItemId] : null;
            $orderItems[$itemId] = $this->quoteItemToOrderItem->convert($quoteItem, ['parent_item' => $parentItem]);
        }

        return array_values($orderItems);
    }

    /**
     * Creates a cart for the currently logged-in customer.
     *
     * @param int $customerId
     * @param int $storeId
     * @return Quote Cart object.
     * @throws CouldNotSaveException The cart could not be created.
     */
    public function createCustomerCart($customerId, $storeId)
    {
        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $customer = $this->customerRepository->getById($customerId);
            /** @var Quote $quote */
            $quote = $this->quoteFactory->create();
            $quote->setStoreId($storeId);
            $quote->setCustomer($customer);
            $quote->setCustomerIsGuest(0);
        }
        return $quote;
    }

}
