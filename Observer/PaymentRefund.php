<?php
namespace Billmate\BillmateCheckout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use \Billmate\BillmateCheckout\Model\Payment\BillmateCheckout;

/**
 * Class PaymentRefund
 * @package Billmate\BillmateCheckout\Observer
 */
class PaymentRefund implements ObserverInterface
{
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $payment = $observer->getPayment();
        $paymentMethod = $payment->getMethodInstance();
        if ($paymentMethod->getCode() == BillmateCheckout::PAYMENT_CODE_CHECKOUT) {
            $paymentMethod->refund($payment, 0);
        }
    }
}
