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

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBmInvoiceId()
    {
        return $this->getInfo()->getOrder()->getData(
            BillmateOrder::BM_INVOICE_ID_FIELD
        );
    }

    /**
     * @return bool
     */
    public function isTestPayment()
    {
        $order = $this->getInfo()->getOrder();
        if ($order) {
            return (bool)$order->getData(BillmateOrder::BM_TEST_MODE_FLAG);
        }

        return false;
    }
}
