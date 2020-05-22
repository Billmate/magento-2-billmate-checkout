<?php

namespace Billmate\BillmateCheckout\Controller\Comment;

class Comment extends \Magento\Framework\App\Action\Action
{

    protected $cart;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(){
        try {
            $quote = $this->cart->getQuote();
            $quote->setData('order_comment',$_POST['comment']);
            $quote->save();
        }
        catch (\Exception $e){}
    }
}
