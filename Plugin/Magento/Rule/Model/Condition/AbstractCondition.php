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

namespace Mageplaza\SplitCart\Plugin\Magento\Rule\Model\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Model\Quote\Item;
use Magento\Rule\Model\Condition\Combine;

/**
 * Class AbstractCondition
 * @package Mageplaza\SplitCart\Plugin\Magento\Rule\Model\Condition
 */
class AbstractCondition
{
    /**
     * @param Combine $subject
     * @param AbstractModel $model
     * @param $result
     * @return false|mixed
     */
    public function afterValidate(
        \Magento\Rule\Model\Condition\AbstractCondition $subject,
        $result,
        AbstractModel $model
    ) {
        if ($result && $model instanceof Item && !$model->getAvailableToCheckout()) {
            return false;
        }

        return $result;
    }
}
