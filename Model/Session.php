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

namespace Mageplaza\SplitCart\Model;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Session\ValidatorInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Session
 * @package Mageplaza\SplitCart\Model
 */
class Session extends SessionManager
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param Http $request
     * @param SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param SaveHandlerInterface $saveHandler
     * @param ValidatorInterface $validator
     * @param StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param State $appState
     * @param StoreManagerInterface $storeManager
     * @throws SessionException
     */
    public function __construct(
        Http $request,
        SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        SaveHandlerInterface $saveHandler,
        ValidatorInterface $validator,
        StorageInterface $storage,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        State $appState,
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
    }

    /**
     * @param $splitQuoteId
     *
     * @return StorageInterface
     * @throws NoSuchEntityException
     */
    public function setSplitQuoteId($splitQuoteId)
    {
        return $this->storage->setData($this->_getSplitQuoteIdKey(), $splitQuoteId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSplitQuoteId()
    {
        return $this->getData($this->_getSplitQuoteIdKey());
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getSplitQuoteIdKey()
    {
        return 'split_quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }
}
