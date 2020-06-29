<?php
namespace Billmate\BillmateCheckout\Model\Quote\Total;

use \Billmate\BillmateCheckout\Model\Payment\Handling\Invoice as BmInvoice;

/**
 * Class InvoiceFee
 * @package Billmate\BillmateCheckout\Model\Quote\Total
 */
class InvoiceFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const BM_METHOD_CODE = 1;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Config
     */
    protected $helperData;

    /**
     * @var \Billmate\BillmateCheckout\Model\Handling\Invoice
     */
    private $bmInvoiceHandler;

    /**
     * InvoiceFee constructor.
     *
     * @param \Billmate\BillmateCheckout\Helper\Config          $helperData
     * @param \Billmate\BillmateCheckout\Model\Handling\Invoice $bmInvoiceHandler
     */
    public function __construct(
       \Billmate\BillmateCheckout\Helper\Config $helperData,
       BmInvoice $bmInvoiceHandler
    ) {
        $this->helperData = $helperData;
        $this->bmInvoiceHandler = $bmInvoiceHandler;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $methodBmCode = $quote->getPayment()->getAdditionalInformation('payment_method_bm_code');
        $invoiceFeeValue = $this->getInvoiceFeeValue();
        if ($methodBmCode == self::BM_METHOD_CODE && $invoiceFeeValue) {
            $total->setTotalAmount(BmInvoice::BM_INVOICE_FEE_CODE, $invoiceFeeValue);
            $total->setBaseTotalAmount(BmInvoice::BM_INVOICE_FEE_CODE, $invoiceFeeValue);
            $total->setBmInvoiceFee($invoiceFeeValue);
            $quote->setBmInvoiceFee($invoiceFeeValue);
        }
        return $this;
    }

    protected function getHandlingFeeValue()
    {
        $handlingFee = $this->iframeHelper->getInvoiceFeeHandling();
        return $handlingFee['amount'] + $handlingFee['tax_amount'];
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $fee = $quote->getBmInvoiceFee();
        $result = [];
        if ($fee) {
            $result = [
                'code' => BmInvoice::BM_INVOICE_FEE_CODE,
                'title' => $this->getLabel(),
                'value' => $fee
            ];
        }
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Billmate Invoice Fee');
    }

    /**
     * @return mixed
     */
    protected function getInvoiceFeeValue()
    {
        $handlingData = $this->bmInvoiceHandler->getFeeData();
        return ($handlingData['amount'] + $handlingData['tax_amount']);
    }
}
