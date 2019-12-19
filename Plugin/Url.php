<?php
namespace Billmate\BillmateCheckout\Plugin;

class Url {

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $configHelper;

    /**
     * Url constructor.
     *
     * @param \Billmate\BillmateCheckout\Helper\Config $configHelper
     */
    public function __construct(\Billmate\BillmateCheckout\Helper\Config $configHelper)
    {
        $this->configHelper = $configHelper;
    }

    /**
     * @param $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetCheckoutUrl($subject,$result)
    {
        if ($this->configHelper->getEnable()) {
            return $subject->getUrl('billmatecheckout');
        }
        return $result;
    }
}