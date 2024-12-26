<?php

namespace Epi\ExternalPrint\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Epi\ExternalPrint\Model\Config\ExternalPrint;

class SaveExternalPrintConfig extends Action
{
    protected $resultRedirectFactory;
    protected $externalPrintConfig;

    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        ExternalPrint $externalPrintConfig
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->externalPrintConfig = $externalPrintConfig;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!empty($data)) {
            $this->externalPrintConfig->saveConfig($data);
            $this->messageManager->addSuccessMessage(__('Configuration has been saved.'));
        } else {
            $this->messageManager->addErrorMessage(__('Failed to save configuration.'));
        }

        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('adminhtml/system_config/edit', ['section' => 'external_print']);

        return $redirectResult;
    }
}
