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

    // Check if 'store' data is being posted
    if ($data = $this->getRequest()->getPost('store')) {
        // Create an empty store model instance
        $store = $this->storeFactory->create();

        try {
            $this->logger->info('Data: ' . $data["id"]);

            // If an ID is provided, load the existing store record
            if (!empty($data['id'])) {
                $store->load($data['id']);  // Load the store by its ID
                $this->logger->info('Loaded Record ID: ' . $store->getId());

                // Check if the store with the given ID exists
                if (!$store->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('This store record no longer exists.'));
                }
            }

            // Log the ID of the store (whether it's new or existing)
            $this->logger->info('Store ID before saving: ' . $store->getId());

            // Set the data for the model (new or existing store)
            $store->addData([
                'name' => $data["name"],
                'email' => $data["email"],
                'message' => $data["message"]
            ]);
            

            // Save the store (it will update an existing record if ID is loaded, or create a new record if no ID)
            $store->save();

            // Log the ID of the store after saving
            $this->logger->info('Store saved with ID: ' . $store->getId());

            // Add a success message to the session
            $this->messageManager->addSuccessMessage(__('The Entry has been saved.'));
            $this->_getSession()->setData('burstonline_feeconfig_store_data', false);

            // Redirect based on the 'back' parameter (edit or index)
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
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Store.'));
        }

        // If error occurs, retain the data
        $this->_getSession()->setData('burstonline_feeconfig_store_data', $data);

        // Redirect to the edit page
        $resultRedirect->setPath('feeconfig/store/edit', ['id' => $store->getId(), '_current' => true]);

        return $resultRedirect;
    }

    // Redirect to the store index if no data is posted
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
