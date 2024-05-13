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

namespace Mageplaza\SplitCart\Setup\Patch\Data;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Quote\Setup\QuoteSetupFactory;

/**
 * Class AvailableToCheckoutAttribute
 * @package Mageplaza\SplitCart\Setup\Patch\Data
 */
class AvailableToCheckoutAttribute implements DataPatchInterface, PatchVersionInterface
{
    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $quoteSetup->addAttribute('quote_item', 'available_to_checkout', [
            'type' => Table::TYPE_SMALLINT,
            'visible' => true,
            'default' => '1'
        ]);
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0.0';
    }
}
