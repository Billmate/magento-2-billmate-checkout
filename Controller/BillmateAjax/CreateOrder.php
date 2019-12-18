<?php
namespace Billmate\BillmateCheckout\Controller\BillmateAjax;

use Magento\Framework\App\Action\Context;

class CreateOrder extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
	protected $resultJsonFactory;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
	protected $helper;

    /**
     * @var \Billmate\BillmateCheckout\Model\Order
     */
    protected $orderModel;

    /**
     * CreateOrder constructor.
     *
     * @param Context                                          $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Billmate\BillmateCheckout\Helper\Data           $_helper
     */
	public function __construct(Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Billmate\BillmateCheckout\Helper\Data $_helper,
        \Billmate\BillmateCheckout\Model\Order $orderModel
	) {
		$this->resultJsonFactory = $resultJsonFactory;
		$this->helper = $_helper;
        $this->orderModel = $orderModel;
		parent::__construct($context);
	}

    /**
     * @return $this
     */
	public function execute()
    {
		if ($this->getRequest()->getParam('status') == 'Step2Loaded') {
			if ($this->helper->getSessionData('billmate_email')){
                $orderData = array(
					'email' => $this->helper->getSessionData('billmate_email'),
					'shipping_address' => $this->helper->getSessionData('billmate_billing_address'),
					'items'=>array()
				);
				$quote = $this->helper->getQuote();
				$allItems = $this->helper->getItems();

				foreach ($allItems as $item) {
					$orderData['items'][] = [
						'product_id' => $item->getSku(),
						'qty' => $item->getQty(),
						'price' => $item->getPrice()
					];
				}
				$orderId = $this->orderModel->setOrderData($orderData)->create();
                $this->helper->setSessionData('bm_order_id', $orderId);
				$this->helper->setSessionData('last_success_quote_id', $quote->getId());
				$this->helper->setSessionData('last_quote_id', $quote->getId());
				$this->helper->setSessionData('last_order_id', $orderId);

                $result = $this->resultJsonFactory->create();
				return $result->setData('checkout/onepage/success');
			}
		}
	}
}