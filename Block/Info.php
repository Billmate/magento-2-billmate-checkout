<?php
namespace Billmate\BillmateCheckout\Block;

use Billmate\BillmateCheckout\Model\Order as BillmateOrder;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Billmate_BillmateCheckout::payment/info.phtml';
    /**
     * @return string
     */
    public function getMethodDescription()
    {
        $bmPaymentData = $this->getInfo()->getAdditionalInformation(
            BillmateOrder::BM_ADDITIONAL_INFO_CODE
        );
        if ($bmPaymentData) {
            return $bmPaymentData;
        }

        return '';
    }
}
