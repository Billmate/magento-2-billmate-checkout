<?php

namespace Billmate\BillmateCheckout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class SaveData extends AbstractDataAssignObserver{

    protected $order;
    protected $quote;

    public function __construct(
        \Magento\Sales\Model\Order $order,
        \Magento\Quote\Model\Quote $quote
    ){
        $this->order = $order;
        $this->quote = $quote;
    }

    public function execute(Observer $observer){
        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        foreach ($orderIds as $orderId){
            $order = $this->order->load($orderId);
            $quote = $this->quote->loadByIdWithoutStore($order->getQuoteId());
            $comment = $quote->getData('order_comment');
            $order->addStatusHistoryComment($comment);
            $order->save();
        }
    }
}