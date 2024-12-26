<?php
namespace Burstonline\Categoryhome\Controller\Featured;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;

class Pagination extends Action
{
    protected $resultPageFactory;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $page = (int) $this->getRequest()->getParam('p', 1);
        $formKey = (int) $this->getRequest()->getParam('formKey');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->addHandle('star_products');
        $block = $resultPage->getLayout()->getBlock('star.products.list');

        if ($block) {
            try {
                $html = $block->toHtml();

                $result = $this->resultJsonFactory->create();
                $result->setData(['success' => true, 'html' => $html]);

                return $result;
            } catch (\Exception $e) {
                $result = $this->resultJsonFactory->create();
                $result->setData(['success' => false, 'error' => $e->getMessage()]);
                return $result;
            }
        }

        $result = $this->resultJsonFactory->create();
        $result->setData(['success' => false, 'error' => 'Block "star.products.list" not found']);
        return $result;
    }
}
