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

namespace Mageplaza\SplitCart\Plugin\Magento\Checkout;

use Magento\Checkout\Controller\Cart\Add;
use Magento\Framework\Json\Helper\Data;

/**
 * Class QuantityValidator
 * @package Mageplaza\SplitCart\Plugin\Model
 */
class  CustomBuyNow extends Add
{

    /**
     * @param Add $subject
     * @param $result
     *
     */
    public function afterExecute(
        Add $subject,
        $result
    ) {
        if(!count($this->_objectManager->get(Data::class)->jsonDecode($result->getContent())) && $subject->getRequest
            ()->getParam('action') === 'buynow'){
            return $this->goBack('checkout');
        }
        return $result;
    }

}
