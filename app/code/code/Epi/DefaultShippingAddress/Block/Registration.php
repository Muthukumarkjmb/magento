<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epi\DefaultShippingAddress\Block;

use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 100.0.2
 */
class Registration extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Address\Validator
     */
    protected $addressValidator;

    /**
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Order\Address\Validator $addressValidator
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
      
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Address\Validator $addressValidator,     
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->registration = $registration;
        $this->accountManagement = $accountManagement;
        $this->orderRepository = $orderRepository;
        $this->addressValidator = $addressValidator;       
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current email address
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEmailAddress()
    {
        return $this->checkoutSession->getLastRealOrder()->getCustomerEmail();
    }

    /**
     * Retrieve account creation url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getCreateAccountUrl()
    {
        return $this->getUrl('checkout/account/delegateCreate');
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {   
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/validateAddresses.log');
        $loggg = new \Zend_Log();
        $loggg->addWriter($writer);

        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderFactory->create()->load($orderId);
        $orderDetails = $order->getData();
        $loggg->info(print_r($orderDetails,true));
        
        if($order->getCustomerIsGuest() && $orderDetails['shipping_method']=='amstorepickup_amstorepickup' && $this->accountManagement->isEmailAvailable($this->getEmailAddress())){
               $loggg->info('this is done by guest');
               return parent::toHtml();
        }

        if ($this->customerSession->isLoggedIn()
            || !$this->registration->isAllowed()
            || !$this->accountManagement->isEmailAvailable($this->getEmailAddress())
            || !$this->validateAddresses()
        ) {
            return '';
        }
        return parent::toHtml();
    }

    /**
     * Validate order addresses
     *
     * @return bool
     */
    protected function validateAddresses()
    {   

        $order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            $result = $this->addressValidator->validateForCustomer($address);
            if (is_array($result) && !empty($result)) {
                return false;
                
            }
        }
        return true;
    }
}
