<?php

namespace Billmate\BillmateCheckout\Helper;

/**
 * Class Config
 * @package Billmate\BillmateCheckout\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GENERAL_ENABLE = 'payment/billmate_checkout/general/enable';

    const XML_PATH_GENERAL_PUSHORDEREVENTS = 'payment/billmate_checkout/general/pushorderevents';

    const XML_PATH_GENERAL_BTN = 'payment/billmate_checkout/general/inc_dec_btns';

    const XML_PATH_GENERAL_ATTRIBUTES = 'payment/billmate_checkout/general/show_attributes_cart';

    const XML_PATH_GENERAL_TERMS_URL = 'payment/billmate_checkout/general/terms_url';

    const XML_PATH_GENERAL_PP_URL = 'payment/billmate_checkout/general/privacy_policy_url';

    const XML_PATH_CREDENTIALS_ID = 'payment/billmate_checkout/credentials/billmate_id';

    const XML_PATH_CREDENTIALS_KEY = 'payment/billmate_checkout/credentials/billmate_key';

    const XML_PATH_GENERAL_TESTMODE = 'payment/billmate_checkout/general/testmode';

    const XML_PATH_CHECKOUT_MODE = 'payment/billmate_checkout/general/billmate_checkout_mode';

    const XML_PATH_DEFAULT_SHIPPING = 'payment/billmate_checkout/general/default_shipping';

    const XML_PATH_INVOICE_FEE = 'payment/billmate_checkout/general/invoice_fee';

    const XML_PATH_INVOICE_FEE_TAX = 'payment/billmate_checkout/general/invoice_fee_tax';

    const XML_PATH_APPROVE_STATUS = 'payment/billmate_checkout/general/acceptstatus';

    const XML_PATH_ORDER_COMMENTS = 'payment/billmate_checkout/general/ordercomments';

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
     * @return bool
     */
    public function getShowAttribute()
    {
        return (bool)$this->getConfig(self::XML_PATH_GENERAL_ATTRIBUTES);
    }

    /**
     * @return bool
     */
    public function isCommentsEnabled()
    {
        return (bool)$this->getConfig(self::XML_PATH_ORDER_COMMENTS);
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

    /**
     * @return string
     */
    public function getCheckoutMode()
    {
        return $this->getConfig(self::XML_PATH_CHECKOUT_MODE);
    }

    /**
     * @return string
     */
    public function getApproveStatus()
    {
        return $this->getConfig(self::XML_PATH_APPROVE_STATUS);
    }

    /**
     * @return string
     */
    public function getDefaultShippingMethod()
    {
        return $this->getConfig(self::XML_PATH_DEFAULT_SHIPPING);
    }

    /**
     * @return float
     */
    public function getInvoiceFee()
    {
        return (float)$this->getConfig(self::XML_PATH_INVOICE_FEE);
    }

    /**
     * @return string
     */
    public function getInvoiceTaxClass()
    {
        return $this->getConfig(self::XML_PATH_INVOICE_FEE_TAX);
    }
}
