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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Burstonline\Feeconfig\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Burstonline\Feeconfig\Model\FeeconfigFactory;

/**
 * Class Author
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Store extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Burstonline_Feeconfig::stores';

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var StoreFactory
     */
    public $storeFactory;

    /**
     * Author constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param StoreFactory $storeFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FeeconfigFactory $storeFactory
    ) {
        $this->storeFactory = $storeFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|\Mageplaza\Blog\Model\Author
     */
    public function initStore($register = false)
    {
        $storeId = (int)$this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\Author $author */
        $store = $this->storeFactory->create();
        if ($storeId) {
            $store->load($storeId);
            if (!$store->getId()) {
                $this->messageManager->addErrorMessage(__('This fee detail no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('burstonline_feeconfig_', $store);
        }

        return $store;
    }
}
