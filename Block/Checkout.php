<?php
namespace Billmate\BillmateCheckout\Block;
 
class Checkout extends \Billmate\BillmateCheckout\Block\Cart
{
    /**
     * @return string
     */
	public function getBillmateCheckoutData()
    {
		return $this->iframeHelper->getIframeData();
	}
}