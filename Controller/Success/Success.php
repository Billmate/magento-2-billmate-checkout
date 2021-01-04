<?php
namespace Billmate\BillmateCheckout\Controller\Success;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use \Magento\Checkout\Model\Session as CheckoutSession;
use Billmate\BillmateCheckout\Model\Order as BillmateOrder;

class Success extends \Billmate\BillmateCheckout\Controller\FrontCore
{

    /**
     * @var PageFactory
     */
	protected $resultPageFactory;

    /**
     * @var \Billmate\BillmateCheckout\Helper\Data
     */
	protected $helper;

    /**
     * @var CheckoutSession
     */
	protected $checkoutSession;

    /**
     * @var \Magento\Framework\Event\Manager
     */
	protected $eventManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Billmate\BillmateCheckout\Model\Order
     */
    protected $orderModel;

    /**
     * @var \Billmate\BillmateCheckout\Model\Api\Billmate
     */
    protected $billmateProvider;

    protected $quoteFactory;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		\Magento\Framework\Event\Manager $eventManager,
		\Billmate\BillmateCheckout\Helper\Data $_helper,
		CheckoutSession $checkoutSession,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Billmate\BillmateCheckout\Model\Order $orderModel,
        \Billmate\BillmateCheckout\Model\Api\Billmate $billmateProvider,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
	) {
	    $this->quoteFactory = $quoteFactory;
		$this->eventManager = $eventManager;
		$this->resultPageFactory = $resultPageFactory;
		$this->checkoutSession = $checkoutSession;
		$this->helper = $_helper;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->successValidator = $successValidator;
        $this->orderModel = $orderModel;
        $this->billmateProvider = $billmateProvider;
		parent::__construct($context);
	}

	public function execute()
    {
        
        
        $this->helper->setSessionData('billmate_checkout_id',null);

        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('\Magento\Framework\Registry');

		try{

            $requestData = $this->getBmRequestData();

            $values = array(
                "number" => $requestData['data']['number']
            );

            $paymentInfo = $this->billmateProvider->getPaymentinfo($values);

            if (!$this->helper->getSessionData('bm-inc-id')) {

                $billmateEmail = ($this->helper->getSessionData('billmate_email')) ? $this->helper->getSessionData('billmate_email') : utf8_decode($paymentInfo['Customer']['Billing']['email']);
                $billmateShipping = ($this->helper->getSessionData('billmate_billing_address')) ? $this->helper->getSessionData('billmate_billing_address') : array_map("utf8_decode", $paymentInfo['Customer']['Billing']);
                $billmateStatus = ($requestData['data']['status']) ? $requestData['data']['status'] : array_map("utf8_decode", $paymentInfo['PaymentData']['status']);
                if (!$billmateShipping) {
                    $billmateShipping = (array_map("utf8_decode", $paymentInfo['Customer']['Shipping'])) ? array_map("utf8_decode", $paymentInfo['Customer']['Shipping']) : array_map("utf8_decode", $paymentInfo['Customer']['Billing']);
                }
                if ($this->helper->getSessionData('billmate_shipping_address')){
                    $billmateShipping = $this->helper->getSessionData('billmate_billing_address');
                }
                $orderData = array(
                    'email' => $billmateEmail,
                    'shipping_address' => $billmateShipping,
                    'payment_method_name' => $paymentInfo['PaymentData']['method_name'],
                    'payment_method_bm_code' => $paymentInfo['PaymentData']['method'],
                    'payment_bm_status' => $billmateStatus,
                );
                
            }
        } catch (\Exception $e){
            $this->helper->addLog([
                'note' => 'Could not redirect customer to store order confirmation page',
                '__FILE__' => __FILE__,
                '__CLASS__' => __CLASS__,
                '__FUNCTION__' => __FUNCTION__,
                '__LINE__' => __LINE__,
                'exception.message' => $e->getMessage(),
                'exception.file' => $e->getFile(),
                'exception.line' => $e->getLine(),
            ]);

            if ($this->orderModel->isOrderSent() == 1) {
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
            }
            $this->helper->clearSession();

            // Execute a cancel transfer
            $values = array(
                "number" => $requestData['data']['number']
            );
            $this->billmateProvider->cancelPayment($values);
            $order = $this->helper->getOrderByIncrementId($this->helper->getSessionData('bm-inc-id'));
            $registry->register('isSecureArea', true);
            $order->delete();
            $registry->unregister('isSecureArea'); 

           return $this->resultRedirectFactory->create()->setPath('billmatecheckout/success/error');
        }

        if (!$this->helper->getSessionData('bm-inc-id')){

            $orderId = $this->orderModel->setOrderData($orderData)->create();
                //if no order id the order will be deleted from Magento and ca in Billmate
                if (!$orderId) {
                    // Execute a cancel transfer
                    $this->helper->clearSession();

                    $values = array(
                        "number" => $requestData['data']['number']
                    );
                    $this->billmateProvider->cancelPayment($values);
                    $order = $this->helper->getOrderByIncrementId($this->helper->getSessionData('bm-inc-id'));
                    $registry->register('isSecureArea', true);
                    $order->delete();
                    $registry->unregister('isSecureArea'); 

                    return $this->resultRedirectFactory->create()->setPath('billmatecheckout/success/error');
                    
                }

            $this->helper->setSessionData('bm_order_id', $orderId);
        }
            $order = $this->helper->getOrderByIncrementId($this->helper->getSessionData('bm-inc-id'));
            $this->registry->register('bm-inc-id', $this->helper->getSessionData('bm-inc-id'));

            $orderId = $order->getId();
                    
            $order->setData(BillmateOrder::BM_INVOICE_ID_FIELD, $requestData['data']['number']);
            $order->save();

            $this->eventManager->dispatch(
                    'checkout_onepage_controller_success_action',
                    ['order_ids' => [$order->getId()]]
            );
            $this->quoteFactory->create()->load($order->getQuoteId())->setIsActive(0)->save();

            $this->checkoutSession->setLastSuccessQuoteId($this->helper->getQuote()->getId());
            $this->checkoutSession->setLastQuoteId($this->helper->getQuote()->getId());
            $this->checkoutSession->setLastOrderId($orderId);

            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
    }
}
