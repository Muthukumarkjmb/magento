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

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Burstonline\Feeconfig\Controller\Adminhtml\Store;
use Burstonline\Feeconfig\Model\FeeconfigFactory;

/**
 * Class Delete
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Delete extends Store
{
    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AuthorFactory $authorFactory
     * @param PostFactory $postFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FeeconfigFactory $feeFactory
    ) {
        parent::__construct($context, $coreRegistry, $feeFactory);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->feeFactory->create()
                    ->load($id)
                    ->delete();

                $this->messageManager->addSuccessMessage(__('The Entry has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('burstonline_feeconfig/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('Entry to delete was not found.'));
        }

        $resultRedirect->setPath('burstonline_feeconfig/*/');

        return $resultRedirect;
    }
}
