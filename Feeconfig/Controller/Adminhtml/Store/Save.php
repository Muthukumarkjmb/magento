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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Burstonline\Feeconfig\Controller\Adminhtml\Store;
use Burstonline\Feeconfig\Model\FeeconfigFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Author
 */
class Save extends Store
{
    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param AuthorFactory $authorFactory
     * @param Image $imageHelper
     */
    protected $logger;

    public function __construct(
        Context $context,
        Registry $registry,
        FeeconfigFactory $storeFactory,
        \Psr\Log\LoggerInterface $logger,
    ) {
        $this->logger = $logger;
        parent::__construct($context, $registry, $storeFactory);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('store')) {
            $store = $this->storeFactory->create();

            try {
                $this->logger->info('Data: ' . $data["id"]);
                $store = $this->storeFactory->create();
                if (!empty($data['id'])) {
                    $store->load($data['id']);
                    $this->logger->info('Record ID: ' . $store->getId());
                    if (!$store->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('This store record no longer exists.'));
                    }
                }
                
                // Set the data for the model
                $store->setData([
                    'enabled' => $data["enabled"],
                    'title' => $data["title"],
                    'sort_order' => $data["sort_order"],
                    'amount' => $data["amount"],
                    'fee_type' => $data["fee_type"],
                    'application_method' => $data["application_method"],
                    'applies_to' => $data["applies_to"],
                    'mapping' => $data["mapping"]
                ]);
                
                $store->save();
                $this->logger->info('Record saved successfully with ID: ' . $store->getId());

                $this->messageManager->addSuccessMessage(__('The Entry has been saved.'));
                $this->_getSession()->setData('burstonline_feeconfig_store_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('feeconfig/store/edit', ['id' => $store->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('feeconfig/store/index');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Author.'));
            }

            $this->_getSession()->setData('burstonline_feeconfig_store_data', $data);

            $resultRedirect->setPath('feeconfig/store/edit', ['id' => $store->getId(), '_current' => true]);

            return $resultRedirect;
        }
        $resultRedirect->setPath('feeconfig/store/index');

        return $resultRedirect;
    }

    /**
     * @param $author
     * @param $data
     *
     * @return $this
     */
    public function prepareData($store, $data)
    {
        // set data
        if (!empty($data)) {
            $store->addData($data);
        }

        return $this;
    }
}
