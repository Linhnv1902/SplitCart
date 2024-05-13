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

namespace Mageplaza\SplitCart\Plugin\Magento\Checkout\Controller\Cart;

use Exception;
use Magento\Checkout\Helper\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Psr\Log\LoggerInterface;
use Mageplaza\SplitCart\Helper\Data;

/**
 * Class CouponPost
 * @package Mageplaza\SplitCart\Plugin\Magento\Checkout\Controller\Cart
 */
class CouponPost extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CouponFactory
     */
    protected $couponFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param CouponFactory $couponFactory
     * @param CartRepositoryInterface $quoteRepository
     * @param Data $data
     */
    public function __construct(
        Context $context,
        CouponFactory $couponFactory,
        CartRepositoryInterface $quoteRepository,
        Data $data
    ) {
        $this->_objectManager = $context->getObjectManager();
        $this->messageManager = $context->getMessageManager();
        $this->resultFactory = $context->getResultFactory();
        $this->couponFactory = $couponFactory;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $data;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\CouponPost $subject
     * @param $proceed
     * @return Json|(Json&ResultInterface)|Redirect|ResultInterface
     */
    public function aroundExecute(
        \Magento\Checkout\Controller\Cart\CouponPost $subject,
        $proceed
    ) {
        $enableSplitCart = $this->helper->isEnabledSplitCart();
        if (!$enableSplitCart) {
            return $proceed();
        }
        $couponCode = $subject->getRequest()->getParam('remove') == 1
            ? ''
            : trim($subject->getRequest()->getParam('coupon_code'));

        $cartQuote = $subject->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $subject->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= Cart::COUPON_CODE_MAX_LENGTH;

            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }

            if ($codeLength) {
                $escaper = $this->_objectManager->get(Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $result = [
                            'success' => true,
                            'message' => $this->messageManager->getMessages()
                        ];
                        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJson->setData($result);
                        return $resultJson;
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $result = [
                            'success' => false,
                            'message' => $this->messageManager->getMessages()
                        ];
                        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJson->setData($result);
                        return $resultJson;

                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $this->messageManager->addSuccessMessage(
                            __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $result = [
                            'success' => true,
                            'message' => $this->messageManager->getMessages()
                        ];
                        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJson->setData($result);
                        return $resultJson;
                    } else {
                        $this->messageManager->addErrorMessage(
                            __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        );
                        $result = [
                            'success' => false,
                            'message' => $this->messageManager->getMessages()
                        ];
                        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                        $resultJson->setData($result);
                        return $resultJson;
                    }
                }
            } else {
                $this->messageManager->addSuccessMessage(__('You canceled the coupon code.'));
                $result = [
                    'success' => true,
                    'message' => $this->messageManager->getMessages()
                ];
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($result);
                return $resultJson;
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot apply the coupon code.'));
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }

        return $this->_goBack();
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
