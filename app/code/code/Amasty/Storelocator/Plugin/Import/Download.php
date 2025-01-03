<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Store Locator for Magento 2
 */

namespace Amasty\Storelocator\Plugin\Import;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Download
 */
class Download
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    private $reader;
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $resultRedirectFactory;
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    private $resultRawFactory;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $requestInterface;

    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        $this->reader = $reader;
        $this->readFactory = $readFactory;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->requestInterface = $requestInterface;
    }

    public function afterExecute(
        $subject,
        $result
    ) {
        if ($this->requestInterface->getParam('filename') == 'amasty_storelocator') {
            $fileName = $this->requestInterface->getParam('filename') . '.csv';
            $moduleDir = $this->reader->getModuleDir('', 'Amasty_Storelocator');
            $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
            $directoryRead = $this->readFactory->create($moduleDir);
            $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

            if (!$directoryRead->isFile($filePath)) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $this->messageManager->addErrorMessage(__('There is no sample file for this entity.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/import');
                return $resultRedirect;
            }

            $fileSize = isset($directoryRead->stat($filePath)['size'])
                ? $directoryRead->stat($filePath)['size'] : null;

            $this->fileFactory->create(
                $fileName,
                null,
                DirectoryList::VAR_DIR,
                'application/octet-stream',
                $fileSize
            );

            /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
            $resultRaw = $this->resultRawFactory->create();
            $resultRaw->setContents($directoryRead->readFile($filePath));
            return $resultRaw;
        }
        
        return $result;
    }
}
