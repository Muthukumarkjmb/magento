<?php

namespace Epi\ShippingRestriction\Plugin;

use Magento\Framework\Phrase;
use Magento\Framework\Exception\ValidatorException;
use \Magento\Framework\Session\SessionManagerInterface;
use \Magento\Checkout\Model\Session;
use \Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\StateException;
use \Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Region\Collection  as RegionCollection;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\OrderFactory;

class ShippingInformationManagementPlugin
{    
    protected $checkoutSession;
	protected $orderFactory;
	protected $messageManager;
    protected $layoutFactory;
    protected $merchantSession;
    protected $regionCollection;
    protected $_countryFactory;
    protected $scopeConfig;
    protected $resourceConnection;
    public function __construct(
        ManagerInterface $messageManager,
        SessionManagerInterface $merchantSession,
        Session $checkoutSession,
        CountryFactory $countryFactory,
        RegionCollection $regionCollection,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        OrderFactory $orderFactory,
    ) {

        $this->messageManager = $messageManager;
        $this->merchantSession = $merchantSession;
        $this->checkoutSession = $checkoutSession;
        $this->_countryFactory = $countryFactory;
        $this->regionCollection = $regionCollection;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->orderFactory = $orderFactory;
    }

    private function getCountryname($countryCode){    
        $country = $this->_countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    private function getFormattedMerchantAddress() {
        $street = $this->merchantSession->getMaddress();
        $city = $this->merchantSession->getMcity();
        $state = $this->merchantSession->getMstate();
        // $country = $this->getCountryname($this->merchantSession->getMcountryId());
        $country = "United States";
        $postcode = $this->merchantSession->getMzip();

        return $street. " " .$city. " " .$state. " " .$country. " " .$postcode;

    }

    private function getFormattedShippingAddress($addressInformation) {
        $shippingAddress = $addressInformation->getShippingAddress();

        $street = "";
        foreach ($shippingAddress->getStreet() as $value) {
            $street .= " ".$value;
        }

        $city = $shippingAddress->getCity();
        $state = $shippingAddress->getRegion();
        $country = $this->getCountryname($shippingAddress->getCountryId());
        $postcode = $shippingAddress->getPostcode();

        return $street. " " .$city. " " .$state. " " .$country. " " .$postcode;

    }

    private function getGoogleApiKey() {
        $googleapikey = $this->scopeConfig->getValue('cms/advance_options/google_api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $googleapikey;
    }

    public function getDistance($addressFrom, $addressTo, $unit = ''){
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippingRestrction.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // Google API key
        $apiKey = $this->getGoogleApiKey();

        
        // Change address format
        $formattedAddrFrom = str_replace(' ', '+', $addressFrom);
        $formattedAddrTo = str_replace(' ', '+', $addressTo);

        
        // Geocoding API request with start address
        $geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$apiKey);
        $outputFrom = json_decode($geocodeFrom);
        if(!empty($outputFrom->error_message)){
            return $outputFrom->error_message;
        }
        
        // Geocoding API request with end address
        $geocodeTo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$apiKey);
        $outputTo = json_decode($geocodeTo);
        if(!empty($outputTo->error_message)){
            return $outputTo->error_message;
        }
        
        // Get latitude and longitude from the geodata
        $latitudeFrom = $outputFrom->results[0]->geometry->location->lat;
        $longitudeFrom = $outputFrom->results[0]->geometry->location->lng;
        $latitudeTo = $outputTo->results[0]->geometry->location->lat;
        $longitudeTo = $outputTo->results[0]->geometry->location->lng;
        
        $logger->info('latitudeFrom -> ' .print_r($latitudeFrom, true));
        $logger->info('longitudeFrom -> ' .print_r($longitudeFrom, true));
        $logger->info('latitudeTo -> ' .print_r($latitudeTo, true));
        $logger->info('longitudeTo -> ' .print_r($longitudeTo, true));

        // Calculate distance between latitude and longitude
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        
        // Convert unit and return distance
        $unit = strtoupper($unit);
        if($unit == "K"){
            # in Kilometers
            return round($miles * 1.609344, 2);
        }elseif($unit == "M"){
            # in meters
            return round($miles * 1609.344, 2);
        }else{
            # in miles
            return round($miles, 2);
        }
    }

    public function isZipcodeBelongsToCity($addressInformation){
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippingRestrction.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $address = $addressInformation->getShippingAddress();
        $postcode = $address->getPostcode();
        $country = $this->getCountryname($address->getCountryId());
        $city = $address->getCity();
        $state = $address->getRegion();
        $apiKey = $this->getGoogleApiKey();
        $shippingMethod=$addressInformation->getShippingMethodCode();

        $getPostcodeDetails = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$postcode.'&sensor=true&key='.$apiKey.'&callback=initAutocomplete&libraries=places&v=weekly');
        $getDecodedPostcodeDetails = json_decode($getPostcodeDetails);
        if(!empty($getDecodedPostcodeDetails->error_message)){
            return $getDecodedPostcodeDetails->error_message;
        }
        $logger->info('addressFrom -> ' .print_r($getDecodedPostcodeDetails, true));
        // $logger->info('result -> ' .print_r($getDecodedPostcodeDetails->results[0]->address_components, true));
        if ($getDecodedPostcodeDetails->status == "ZERO_RESULTS") {
            throw new StateException(__("Please enter a valid zipcode."));
        }
        
        $address_components = $getDecodedPostcodeDetails->results[0]->address_components;

        $zipToState = '';

        foreach ($address_components as $obj) {
            if ($obj->types[0] == 'administrative_area_level_1') {
                $zipToState = $obj->long_name;
            }
        }

        return $zipToState == $state;

    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId int
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * 
     */
    public function beforeSaveAddressInformation($subject, $cartId, $addressInformation)
    {   
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippingRestrction.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('<------- Shipping Restriction ------->');
        // $this->messageManager->addError(__('Hello! Kuch to hoga sayad'));
        $shippingMethod=$addressInformation->getShippingMethodCode();
        if($shippingMethod != 'amstorepickup'){
            if (!$this->isZipcodeBelongsToCity($addressInformation)){
                throw new StateException(__("Cannot deliver to locations outside of OH. Please enter a delivery address within OH."));
            }
            $shippingAddress = $addressInformation->getShippingAddress();
    
            // $addressFrom = $this->getFormattedMerchantAddress();
            $addressTo = $this->getFormattedShippingAddress($addressInformation);
            $state = $shippingAddress->getRegion();
            $usStates = $this->scopeConfig->getValue('shipping/custom_shipping/us_states', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if (empty($usStates)) {
                // Handle the case when the configuration value is empty
                $logger->info('No valid US states configured in the system.');
                $validStates = ['Ohio'];
            }else{
                $validStates = explode(',', $usStates);
            }
            if(!in_array($state,$validStates)) {
                $this->messageManager->addErrorMessage(
                    __("Cannot deliver to locations outside of OH. Please enter a delivery address within OH.")
                );
    
                // Throw an exception to prevent the process from moving forward
                throw new StateException(
                    __("Cannot deliver to locations outside of OH. Please enter a delivery address within OH.")
                );
            }
            
            $logger->info('validStates -> ' .print_r($validStates, true));
            $logger->info('addressTo -> ' .print_r($addressTo, true));
        }
        
    }
}
