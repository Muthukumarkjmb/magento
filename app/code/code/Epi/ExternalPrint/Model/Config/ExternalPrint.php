<?php

namespace Epi\ExternalPrint\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class ExternalPrint
{
    const XML_PATH_ENABLED = 'external_print/general/enabled';
    const XML_PATH_LOCATION_ID = 'external_print/general/location_id';
    const XML_PATH_BASE_URL = 'external_print/general/base_url';
    const XML_PATH_ENDPOINT_URL = 'external_print/general/endpoint_url';

    protected $scopeConfig;
    protected $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Check if the external print feature is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED);
    }

    public function getLocationId($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_LOCATION_ID,
            $scopeCode ?: ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseUrl($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BASE_URL,
            $scopeCode ?: ScopeInterface::SCOPE_STORE
        );
    }

    public function getEndpointUrl($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENDPOINT_URL,
            $scopeCode ?: ScopeInterface::SCOPE_STORE
        );
    }

    public function saveConfig($data)
    {
        $scope = $this->request->getParam('scope', ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
        $scopeId = $this->request->getParam('scope_id', null);


        $this->scopeConfig->setValue(
            self::XML_PATH_ENABLED,
            $data['enabled'],
            $scope,
            $scopeId
        );

        $this->scopeConfig->setValue(
            self::XML_PATH_LOCATION_ID,
            $data['location_id'],
            $scope,
            $scopeId
        );

        $this->scopeConfig->setValue(
            self::XML_PATH_BASE_URL,
            $data['base_url'],
            $scope,
            $scopeId
        );

        $this->scopeConfig->setValue(
            self::XML_PATH_ENDPOINT_URL,
            $data['endpoint_url'],
            $scope,
            $scopeId
        );
    }
}
