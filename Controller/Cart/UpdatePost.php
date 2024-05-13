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

namespace Mageplaza\SplitCart\Controller\Cart;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdatePost
 * @package Mageplaza\SplitCart\Controller\Cart
 */
class UpdatePost extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * Sales quote repository
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param Cart $cart
     * @param CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        Cart $cart,
        CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $status = false;
        $hasError = true;

        try {
            $cartQuote = $this->cart->getQuote();
            $itemIds = $this->getRequest()->getParam('item_ids');
            $itemIds = explode(',', $itemIds);
            $action = $this->getRequest()->getParam('action', 'uncheck');
            $availableToCheckout = $action === 'check' ? 1 : 0;

            foreach ($itemIds as $itemId) {
                if ($itemId) {
                    $this->updateItemAvailableToCheckout($itemId, $cartQuote, $availableToCheckout);
                }
            }

            $this->quoteRepository->save($cartQuote);

            $status = true;
            $message = __('Your cart has been updated.');

            $shippingAddressItems = $cartQuote->getShippingAddress()->getAllItems();
            $hasError = $cartQuote->getHasError() || !count($shippingAddressItems);
        } catch (LocalizedException $e) {
            $message = __($e->getMessage());
        } catch (Exception $e) {
            $message = __('Your cart cannot be updated.');
        }

        $result = [
            'success' => $status,
            'message' => $message,
            'has_error' => !!$hasError
        ];

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);

        return $resultJson;
    }

    /**
     * Updates item qty for the specified cart
     *
     * @param int $itemId
     * @param Quote $cart
     * @param int $availableToCheckout
     */
    private function updateItemAvailableToCheckout(int $itemId, Quote $cart, int $availableToCheckout)
    {
        $cartItem = $cart->getItemById($itemId);
        if ($cartItem) {
            $cartItem->setAvailableToCheckout($availableToCheckout);
        }
    }
}
