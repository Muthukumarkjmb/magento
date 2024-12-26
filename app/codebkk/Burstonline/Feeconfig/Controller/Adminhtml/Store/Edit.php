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

namespace Burstonline\Feeconfig\Controller\Adminhtml\Store;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Burstonline\Feeconfig\Controller\Adminhtml\Store;
use Burstonline\Feeconfig\Model\FeeconfigFactory;

/**
 * Class Edit
 * @package Mageplaza\Blog\Controller\Adminhtml\Author
 */
class Edit extends Store
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FeeconfigFactory $feeconfigFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeeconfigFactory $feeconfigFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context, $registry, $feeconfigFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        /** @var Burstonline\Feeconfigg\Model\Feeconfig $author */
        $store = $this->initStore();

        if (!$store) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }

        /** Set entered data if was error when we do save */
        /*$data = $this->_session->getData('mageplaza_blog_author_data', true);
        if (!empty($data)) {
            $author->addData($data);
        }*/

        $this->coreRegistry->register('burstonline_feeconfig_store', $store);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Burstonline_Feeconfig::store');
        $resultPage->getConfig()->getTitle()->set(__('Entries Management'));

        $resultPage->getConfig()->getTitle()->prepend($store->getId() ? $store->getName() : __('New Entries'));

        return $resultPage;
    }
}
