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

namespace Mageplaza\SplitCart\Logger;

use Exception;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Handler
 * @package Mageplaza\SmsNotification\Logger
 */
class Handler extends Base
{
    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Client constructor.
     * @param DriverInterface $filesystem
     * @param TimezoneInterface $localeDate
     * @param null $filePath
     * @param null $fileName
     * @throws Exception
     */
    public function __construct(
        DriverInterface $filesystem,
        TimezoneInterface $localeDate,
        $filePath = null,
        $fileName = null
    ) {
        $this->_localeDate = $localeDate;
        $fileName = '/var/log/splitcart/splitcart_' . $this->getTimeStamp() . '.log';
        parent::__construct($filesystem, $filePath, $fileName);
    }

    /**
     * @return string
     */
    public function getTimeStamp()
    {
        $currentDate = $this->_localeDate->date();
        return $currentDate->format('Ymd');
    }
}
