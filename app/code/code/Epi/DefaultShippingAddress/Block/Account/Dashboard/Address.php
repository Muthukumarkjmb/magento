<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Epi\DefaultShippingAddress\Block\Account\Dashboard;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class to manage customer dashboard addresses section
 *
 * @api
 * @since 100.0.2
 */
class Address extends \Magento\Framework\View\Element\Template
{   

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $_addressConfig;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomerAddress
     */
    protected $currentCustomerAddress;

    /**
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Helper\Session\CurrentCustomerAddress $currentCustomerAddress
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Helper\Session\CurrentCustomerAddress $currentCustomerAddress,
        \Magento\Customer\Model\Address\Config $addressConfig,
        Mapper $addressMapper,
        array $data = []
    ) {
        $this->addressRepository=$addressRepository;
        $this->currentCustomer = $currentCustomer;
        $this->currentCustomerAddress = $currentCustomerAddress;
        $this->_addressConfig = $addressConfig;
        parent::__construct($context, $data);
        $this->addressMapper = $addressMapper;
    }

    /**
     * Get the logged in customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * HTML for Shipping Address
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getPrimaryShippingAddressHtml()
    {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/address.log');
            $this->logger = new \Zend_Log();
            $this->logger->addWriter($writer);


            try {
                $address = $this->currentCustomerAddress->getDefaultShippingAddress();

            } catch (NoSuchEntityException $e) {
                return __('You have not set a default shipping address.');
            }
          
           if($address){
               $address_array=$this->addressMapper->toFlatArray($address);
               $this->logger->info('this is the information from the session storage'.print_r($_SESSION['default']['mphone'],true));
               $storePhoneNumber =$_SESSION['default']['mphone'];
               $customerNumber = $address_array['telephone'];
               
               if ($storePhoneNumber == $customerNumber){
                    // deleting the address if the default address is same as store address.
                    $this->addressRepository->deleteById($address_array['id']);

                    //getting billing address.
                    $shippingAddress = $this->currentCustomerAddress->getDefaultBillingAddress();
                    $shippingAddress_array=$this->addressMapper->toFlatArray($shippingAddress );
                    $this->logger->info('this is the billing address'.print_r($shippingAddress_array,true));

                    //Set billing address as default shipping address.
                    $addressId =$shippingAddress_array['id'];
                    $customerId= $shippingAddress_array['customer_id'];
                    $defaultShippingAddress =$this->addressRepository->getById($addressId)->setCustomerId($customerId);
                    $defaultShippingAddress->setIsDefaultShipping(true);
                    $this->addressRepository->save($defaultShippingAddress);
    

                return $this->_getAddressHtml($shippingAddress);
               }

                return $this->_getAddressHtml($address);
            }
           else{
                 return __('You have not set a default shipping address.');
            }
           
    }

    /**
     * HTML for Billing Address
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getPrimaryBillingAddressHtml()
    {
        try {
            $address = $this->currentCustomerAddress->getDefaultBillingAddress();
        } catch (NoSuchEntityException $e) {
            return $this->escapeHtml(__('You have not set a default billing address.'));
        }

        if ($address) {
            return $this->_getAddressHtml($address);
        } else {
            return $this->escapeHtml(__('You have not set a default billing address.'));
        }
    }

    /**
     * Get Primary Shipping Address Edit Url
     *
     * @return string
     */
    public function getPrimaryShippingAddressEditUrl()
    {
        if (!$this->getCustomer()) {
            return '';
        } else {
            $address = $this->currentCustomerAddress->getDefaultShippingAddress();
            $addressId = $address ? $address->getId() : null;
            return $this->_urlBuilder->getUrl(
                'customer/address/edit',
                ['id' => $addressId]
            );
        }
    }

    /**
     * Get Primary Billing Address Edit Url
     *
     * @return string
     */
    public function getPrimaryBillingAddressEditUrl()
    {
        if (!$this->getCustomer()) {
            return '';
        } else {
            $address = $this->currentCustomerAddress->getDefaultBillingAddress();
            $addressId = $address ? $address->getId() : null;
            return $this->_urlBuilder->getUrl(
                'customer/address/edit',
                ['id' => $addressId]
            );
        }
    }

    /**
     * Get Address Book Url
     *
     * @return string
     */
    public function getAddressBookUrl()
    {
        return $this->getUrl('customer/address/');
    }

    /**
     * Render an address as HTML and return the result
     *
     * @param AddressInterface $address
     * @return string
     */
    protected function _getAddressHtml($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->_addressConfig->getFormatByCode('html')->getRenderer();
        return $renderer->renderArray($this->addressMapper->toFlatArray($address));
    }
}
