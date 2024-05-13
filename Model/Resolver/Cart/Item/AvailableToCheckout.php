<?php

namespace Mageplaza\SplitCart\Model\Resolver\Cart\Item;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item;

/**
 * Class AvailableToCheckout
 * @package Mageplaza\SplitCart\Model\Resolver\Cart\Item
 */
class AvailableToCheckout implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($value['model'])) {
            return null;
        }

        /**
         * @var Item $item
         */
        $item = $value['model'];

        return $item->getData('available_to_checkout');
    }
}
