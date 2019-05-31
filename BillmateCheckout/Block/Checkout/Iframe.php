<?php
namespace Billmate\BillmateCheckout\Block\Checkout;

class Iframe extends \Billmate\BillmateCheckout\Block\Cart
{
    /**
     * @return string
     */
    public function getBillmateCheckoutData()
    {
        return $this->iframeHelper->getIframeData();
    }
}