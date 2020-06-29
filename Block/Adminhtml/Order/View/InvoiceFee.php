<?php
namespace Billmate\BillmateCheckout\Block\Adminhtml\Order\View;

use \Billmate\BillmateCheckout\Model\Payment\Handling\Invoice as BmInvoice;

/**
 * Class InvoiceFee
 * @package Billmate\BillmateCheckout\Block\Adminhtml\Order\View
 */
class InvoiceFee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * InvoiceFee constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Model\Currency                $currency
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getSource();

        if (!($this->getSource()->getBmInvoiceFee() > 0)) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => BmInvoice::BM_INVOICE_FEE_CODE,
                'value' => $this->getSource()->getBmInvoiceFee(),
                'label' => __('Billmate Invoice Fee'),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
