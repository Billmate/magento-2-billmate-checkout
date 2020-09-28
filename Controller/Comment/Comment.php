<?php

namespace Billmate\BillmateCheckout\Controller\Comment;

/**
 * Class Comment
 * @package Billmate\BillmateCheckout\Controller\Comment
 */
class Comment extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
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
    public function execute()
    {
       $comment = $this->getRequest()->getParam('comment');
        try {
            if ($comment) {
                $quote = $this->cart->getQuote();
                $quote->setData(
                    'order_comment',
                    $comment
                );
                $quote->save();
            }
        }
        catch (\Exception $e){
            $this->helper->clearSession();

            $this->resultRedirectFactory->create()->setPath('billmatecheckout/success/error');
        }
    }
}
