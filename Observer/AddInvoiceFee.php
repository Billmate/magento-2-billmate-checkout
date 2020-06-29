<?php
namespace Billmate\BillmateCheckout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use \Billmate\BillmateCheckout\Model\Payment\Handling\Invoice as BmInvoice;

/**
 * Class AddInvoiceFee
 * @package Billmate\BillmateCheckout\Observer
 */
class AddInvoiceFee implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $bmInvoiceFee = $quote->getBmInvoiceFee();
        if (!$bmInvoiceFee) {
            return $this;
        }
        $order = $observer->getOrder();
        $order->setData(BmInvoice::BM_INVOICE_FEE_CODE, $bmInvoiceFee);

        return $this;
    }
}
