<?php

namespace Billmate\BillmateCheckout\Helper;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GENERAL_ENABLE = 'payment/billmate_checkout/billmate_configs/general/enable';
    const XML_PATH_GENERAL_PUSHORDEREVENTS = 'payment/billmate_checkout/billmate_configs/general/pushorderevents';
    const XML_PATH_GENERAL_BTN = 'payment/billmate_checkout/billmate_configs/general/inc_dec_btns';
    const XML_PATH_GENERAL_ATTRIBUTES = 'payment/billmate_checkout/billmate_configs/general/show_attributes_cart';
    const XML_PATH_GENERAL_TERMS_URL = 'payment/billmate_checkout/billmate_configs/general/terms_url';
    const XML_PATH_GENERAL_PP_URL = 'payment/billmate_checkout/billmate_configs/general/privacy_policy_url';
    const XML_PATH_CREDENTIALS_ID = 'payment/billmate_checkout/billmate_configs/credentials/billmate_id';
    const XML_PATH_CREDENTIALS_KEY = 'payment/billmate_checkout/billmate_configs/credentials/billmate_key';
    const XML_PATH_GENERAL_TESTMODE = 'payment/billmate_checkout/billmate_configs/general/testmode';

    /**
     * @param $config_path
     *
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return boolean
     */
    public function getEnable()
    {
        return (bool)$this->getConfig(self::XML_PATH_GENERAL_ENABLE);
    }

    /**
     * @return boolean
     */
    public function getBtnEnable()
    {
        return (bool)$this->getConfig(self::XML_PATH_GENERAL_BTN);
    }

    /**
     * @return int
     */
    public function getBillmateId()
    {
        return $this->getConfig(self::XML_PATH_CREDENTIALS_ID);
    }

    /**
     * @return string
     */
    public function getBillmateSecret()
    {
        return $this->getConfig(self::XML_PATH_CREDENTIALS_KEY);
    }

    /**
     * @return boolean
     */
    public function getTestMode()
    {
        return $this->getConfig(self::XML_PATH_GENERAL_TESTMODE);
    }

    /**
     * @return bool
     */
    public function getPushEvents()
    {
        return (bool)$this->getConfig(self::XML_PATH_GENERAL_PUSHORDEREVENTS);
    }

    /**
     * @return int
     */
    public function getShippingTaxClass()
    {
        return $this->getConfig('tax/classes/shipping_tax_class');
    }

    /**
     * @return mixed
     */
    public function getShowAttribute()
    {
        return (bool)$this->getConfig(self::XML_PATH_GENERAL_ATTRIBUTES);
    }

    /**
     * @return string
     */
    public function getTermsURL()
    {
        return $this->getConfig(self::XML_PATH_GENERAL_TERMS_URL);
    }

    /**
     * @return string
     */
    public function getPPURL()
    {
        return $this->getConfig(self::XML_PATH_GENERAL_PP_URL);
    }
}
